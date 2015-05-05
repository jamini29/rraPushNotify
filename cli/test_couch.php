<?php

chdir(dirname(__FILE__)."/..");
$ini=parse_ini_file("main.ini", true);
$DEBUG=$ini['global']['debug'];
// all dates in UTC => prepare
$UTC = new DateTimeZone("UTC");

date_default_timezone_set('UTC');
setlocale(LC_ALL, 'ru_RU.UTF-8');

include 'cli/inc_apns.php';
include 'cli/inc_db.php';

$db = new DbConnect(
        $ini[($ini['global']['develop'] ? "dev_": "") ."database"]['hostname'],
        $ini[($ini['global']['develop'] ? "dev_": "") ."database"]['username'], 
        $ini[($ini['global']['develop'] ? "dev_": "") ."database"]['password'], 
        $ini[($ini['global']['develop'] ? "dev_": "") ."database"]['database']);
$db->show_errors();

$cb_link = new Couchbase(
        $ini[($ini['global']['develop'] ? "dev_": "") ."couchdb"]['hostname'].":".$ini[($ini['global']['develop'] ? "dev_": "") ."couchdb"]['port'],
        $ini[($ini['global']['develop'] ? "dev_": "") ."couchdb"]['username'],
        $ini[($ini['global']['develop'] ? "dev_": "") ."couchdb"]['password'],
        $ini[($ini['global']['develop'] ? "dev_": "") ."couchdb"]['database']);

$sql="select * ".
        "from `apns_devices` ".
        "where `appid` in ('com.grinasys.runningforweightloss', 'com.grinasys.runningforweightlosspro', 'com.grinasys.reunningforweightlossipad') and status='active'";
$customchannels_arr=array();
if($result = $db->query($sql)){
  if($result->num_rows){
    while($row = $result->fetch_array(MYSQLI_ASSOC)){
//      echo $row['customchannel']."\n";
      array_push($customchannels_arr, $row['customchannel']);
    }
  }
}
//      $cb_result = $cb_link->view(($ini['global']['develop'] ? "dev_" : "") ."current_status", 'rapp_events_by_customchannel', array('keys' => $customchannels_arr));
      $cb_result = $cb_link->view(($ini['global']['develop'] ? "dev_" : "") ."current_status", 'get_RAPP_TRAINING_LOG_id', array('keys' => $customchannels_arr));
      if(isset($cb_result, $cb_result['total_rows'], $cb_result['rows'])) {
        foreach($cb_result['rows'] as &$res_item) {
          var_dump($res_item);
        }
      }
      echo "--".$cb_result['total_rows']."--".count($cb_result['rows'])."--\n";
