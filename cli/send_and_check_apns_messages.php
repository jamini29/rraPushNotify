<?php
$options=getopt("w:");
$waitTime=isset($options['w']) ? $options['w'] : 0;
sleep($waitTime);
$s=time();
define("ROOTDIR", dirname(__FILE__));
chdir(ROOTDIR."/..");

$lockfile=fopen("cli/log/send_and_check_apns_messages_w".$waitTime.".lock", "w");
if (!flock($lockfile, LOCK_EX|LOCK_NB)) { // try to get exclusive lock, non-blocking
  //echo "Another instance of send_and_check_apns_messages_w".$waitTime." is running\n";
  die($s."\tAnother instance of send_and_check_apns_messages_w".$waitTime." is running\n");
}

$ini=parse_ini_file("main.ini", true);

include 'cli/inc_apns.php';
include 'cli/inc_db.php';
include 'cli/inc_common.php';

$db = new DbConnect(
        $ini['database']['hostname'],
        $ini['database']['username'], 
        $ini['database']['password'], 
        $ini['database']['database']);
$db->show_errors();

$apps=get_apps($db);
foreach($apps as &$app_item) {
    $argc=array(
        'task' => 'fetch',
        'appid'=> $app_item['appid_ios'],
        'cert' => 'certs/cer/ios/'.$app_item['appid_ios'].'.pem',
            );
    $apns = new APNS($db,$argc,$argc['cert'],$argc['cert']);
}
$sql="select now() as now";
if($result = $db->query($sql, true)) {
  echo $result['now']." delay ".$waitTime." sec\n";
}
// check and set actual status for campaign
if($waitTime==57) { // do update once per 1 hour - dummy process at 57 secs
  $sql="select `push_campaign_list_id` from `push_campaign_list` ".
          "where `campaign_status`='sheduled' and `messages_created`>0";
  $result=$db->query($sql);
  $camp2chk_ids=array();
  while ($row = $result->fetch_array(MYSQLI_ASSOC)) array_push($camp2chk_ids,$row['push_campaign_list_id']);
  $db->free_result($result);
  foreach($camp2chk_ids as &$campid) {
    $messages2clear=array();
    // prepare to clear messages for uninstalled devices
    $sql="SELECT apns_messages.pid FROM apns_messages inner join apns_devices on apns_messages.fk_device=apns_devices.pid where ".
            "`apns_messages`.`status`='queued' and ".
            "`apns_devices`.`status`='uninstalled' and ".
            "`apns_messages`.`campid`='{$db->prepare($campid)}' and ".
            "`apns_messages`.`delivery` <= (NOW() + INTERVAL 1 HOUR)";
    $result=$db->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) array_push($messages2clear,$row['pid']);
    // prepare to clear messages wich bounced to send for a long time - coused by db or scripts timeouts and etc.
    $sql="SELECT apns_messages.pid FROM apns_messages where ".
            "`apns_messages`.`status`='queued' and ".
            "`apns_messages`.`inprocess`='1' and ".
            "`apns_messages`.`campid`='{$db->prepare($campid)}' and ".
            "`apns_messages`.`delivery` <= (NOW() + INTERVAL 6 HOUR)";
    $result=$db->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) array_push($messages2clear,$row['pid']);
    // clear messages
    if(count($messages2clear)) {
      echo "clear ".count($messages2clear)." messages for uninstalled devices or bounced sending messages\n";
      $sql="update apns_messages set status='failed' ".
             "where `pid` in (".implode(',',$messages2clear).")";
      $db->query($sql);
    }

    // set actual campaign status
    $sql="select sum(if(`status`='queued',1,0)) as 'queued', sum(if(`status`='delivered',1,0)) as 'delivered' from `apns_messages` ".
            "where campid='{$db->prepare($campid)}'";
    $row=$db->query($sql, true);
    echo $row['delivered']."\t".$row['queued']."\n";
    if($row['delivered']) {
      $sql="update `push_campaign_list` ".
             "set ".(!$row['queued'] ? "`campaign_status`='completed', " : "")." `actual_reach`='{$db->prepare($row['delivered'])}' ".
             "where `push_campaign_list_id`='{$db->prepare($campid)}'";
      $db->query($sql);
    }
  }
}
$db->close();
echo "spend ".(time()-$s)." secs.\n";

