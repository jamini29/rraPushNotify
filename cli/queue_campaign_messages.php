<?php
//echo(dirname(__FILE__));
chdir(dirname(__FILE__)."/..");
$ini=parse_ini_file("main.ini", true);
$DEBUG=$ini['global']['debug'];
// all dates in UTC => prepare
$UTC = new DateTimeZone("UTC");

date_default_timezone_set('UTC');
setlocale(LC_ALL, 'ru_RU.UTF-8');


//include "functions.php";
include 'cli/inc_apns.php';
include 'cli/inc_db.php';
include 'cli/inc_common.php';

//$link = mysql_connect($ini['database']['hostname'], $ini['database']['username'], $ini['database']['password'], 0, 131072)
//  or cli_dielog("Cannot connect to MySQL: ".mysql_error());
//mysql_select_db($ini['database']['database']) or cli_dielog("Cannot select database '".$ini['database']['database']."'");
//mysql_query("set names utf8;", $link);
//mysql_query("set time_zone = '+00:00';", $link);
$db = new DbConnect(
        $ini['database']['hostname'],
        $ini['database']['username'], 
        $ini['database']['password'], 
        $ini['database']['database']);
$db->show_errors();
$apns = new APNS($db);

//$apps=get_apps($db);
$langs=get_langs($db);

// let's begin:
// get list campaigns ids to add messages in queue
$sql="select `push_campaign_list_id` from `push_campaign_list` ".
     "where `campaign_status`='sheduled' and `messages_created`=0";
$result=$db->query($sql);
$camp_ids=array();
while ($row = $result->fetch_array(MYSQLI_ASSOC)) { array_push($camp_ids,$row['push_campaign_list_id']); }
$db->free_result($result);
$campsD=get_camps_data($db, $camp_ids);
foreach($campsD as $campId => $campData) {
//    foreach(list_message_targets($db, $campData, $langs) as &$messageData) {
//        $apns->newMessage($messageData['pid'],$messageData['utctimestamp'],NULL,$campId);
//        $apns->addMessageAlert($messageData['alert']);
//        $apns->queueMessage();
//    }

  // lang => message array for sending
//  $langs_a_prep=array();
//  foreach($campData['langs'] as $key => $item) $langs_a_prep[$langs[$item['language_list_id']]['language_code']]=$item['push_message'];
  $msg_count=list_and_queue_messages($db, $campData, $langs, $apns);
//  foreach(list_message_targets_reduced($db, $campData, $langs) as &$messageData) {
//    // just to not send strange message if campaign langs leak !
//    if(isset($langs_a_prep[$messageData['lang']]) or isset($langs_a_prep['ANY'])) {
//      $apns->newMessage($messageData['pid'],($campData['camp']['planned_ts']-$messageData['gmtoffset']),NULL,$campId);
//      $apns->addMessageAlert(isset($langs_a_prep[$messageData['lang']]) ? $langs_a_prep[$messageData['lang']] : $langs_a_prep['ANY']);
//      $apns->queueMessage();
//    }
//  }
  
  $sql="select count(`campid`) as 'created' from `apns_messages` where `campid`='{$db->prepare($campId)}'";
  $row=$db->query($sql, true);
  if($row['created']) {
    $sql="update `push_campaign_list` set messages_created='{$db->prepare($row['created'])}' where `push_campaign_list_id`='{$db->prepare($campId)}'";
    $db->query($sql);
  }
  echo "campaign_id=".$campId."\treturn count=".$msg_count."\tdbcount=".$row['created']."\n";
}
$db->close();


