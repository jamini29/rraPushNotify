<?php
$s=time();
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

//include "push_functions.php";
$db = new DbConnect(
        $ini['database']['hostname'],
        $ini['database']['username'], 
        $ini['database']['password'], 
        $ini['database']['database']);
$db->show_errors();

$cb_link = new Couchbase(
        $ini['couchdb']['hostname'].":".$ini['couchdb']['port'],
        $ini['couchdb']['username'],
        $ini['couchdb']['password'],
        $ini['couchdb']['database']);

$sql="select ifnull(max(lastkeyts),0) as 'lastkeyts', `lastkeydocid` ".
     "from `sync_profiles_log` ".
     "where sync_success and sync_profiles_count!=0";
$lastsyncts=0;
$lastkeydocid='';
if($result = $db->query($sql, true)) {
  $lastsyncts=$result['lastkeyts'];
  $lastkeydocid=isset($result['lastkeydocid']) ? $result['lastkeydocid'] : '';
}

// init log record

$sql="insert into `sync_profiles_log` set `sync_ts`=UTC_TIMESTAMP";
$db->query($sql);
$current_sync_id=$db->insert_id();


$q="select `app_list_id`, `appid_ios` from `app_list`";
$result=$db->query($q);
$applistids=array();
while ($row = $result->fetch_array(MYSQLI_ASSOC)) { $applistids[$row['appid_ios']]=$row['app_list_id']; }

$q="select `language_code` from `language_list` where `language_list_id`!=1"; // all but ANY
$result=$db->query($q);
$supportedlangs=array();
while ($row = $result->fetch_array(MYSQLI_ASSOC)) { array_push($supportedlangs, $row['language_code']); }

$i=0;
$page_length=1000;
$params=array('limit' => $page_length, ); //'inclusive_end' => true, 'stale' => 'update_after');
if($lastsyncts and !$ini['global']['fullsync']) {
  $params['startkey']=$lastsyncts-1; //-600;
}
$last_page=false;
do {
  $last_pushIdentificator_list=array();
  //echo "do with params: 'limit'=".$params['limit']." ".(isset($params['startkey']) ? "'startkey'=".$params['startkey'] : "")." ".(isset($params['startkey_docid']) ? "'startkey_docid'=".$params['startkey_docid'] : "")."\n";
  $cb_result = $cb_link->view('current_status', 'profiles_by_ts', $params);
  if(isset($cb_result, $cb_result['total_rows'], $cb_result['rows'])) {
    if(count($cb_result['rows']) < $page_length) $last_page=true;
    $last_key=array_pop(array_keys($cb_result['rows']));
    foreach($cb_result['rows'] as $key => $res_item) {
      if($key != $last_key or $last_page) { // do what you want
        if(array_key_exists($res_item['value']['pushIdentificator'],$last_pushIdentificator_list)) unset($last_pushIdentificator_list[$res_item['value']['pushIdentificator']]); // unset older record
        $last_pushIdentificator_list[$res_item['value']['pushIdentificator']] = prepare_register_data_ts($res_item,$applistids,$supportedlangs);
      } else {
        $params['startkey']=$res_item['key'];
        $params['startkey_docid']=$res_item['id'];
      }
    }
  } else $last_page=true; // just finish even db view works strange

  // register devices part for current page
  foreach($last_pushIdentificator_list as &$args) {
    $apns = new APNS($db, $args);
  }
  // update part for current page - if fails - then can be started from last successfull key
  $sql="update `sync_profiles_log` ".
     "set `sync_profiles_count`=sync_profiles_count+".count($last_pushIdentificator_list).", ".
     "`sync_success`='1', ".
     "`lastkeyts`='".$params['startkey']."', ".
     "`lastkeydocid`='".(isset($params['startkey_docid']) ? $params['startkey_docid'] : "")."' ".
     "where `sync_profiles_log_id`='".$current_sync_id."'";
  $db->query($sql);
  echo "log_id: ".$current_sync_id."\t".date('c').
         " page#".str_pad($i,5,' ',STR_PAD_LEFT).
         " profiles ".str_pad(($i * $page_length + count($cb_result['rows'])),8,' ',STR_PAD_LEFT)." of ".$cb_result['total_rows'].
         " returned: ".str_pad(sizeof($cb_result['rows']),3,' ',STR_PAD_LEFT).
         " to_prep: ".str_pad(sizeof($last_pushIdentificator_list),3,' ',STR_PAD_LEFT).
         " startts: ".$params['startkey']."\n";
  $i++;
  unset($last_pushIdentificator_list);
} while(!$last_page);

// update have app list
echo "log_id: ".$current_sync_id."\t".date('c')." try update\n";
$que="select `customchannel`, group_concat(`applistid` separator ',') as haveappidlist from `apns_devices` where `status`='active' group by `customchannel`";
$result=$db->query($que);
$updatedch=0;
while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
  $query_upd="update `apns_devices` set `haveappidlist`='".$row['haveappidlist']."' where `customchannel`='".$row['customchannel']."' and `status`='active'";
  $db->query($query_upd);
}
echo "spend ".(time()-$s)." sec.\n";
$db->close();

