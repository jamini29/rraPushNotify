<?php

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

$db = new DbConnect(
        $ini['database']['hostname'],
        $ini['database']['username'], 
        $ini['database']['password'], 
        $ini['database']['database']);
$db->show_errors();

$apps=get_apps($db);
foreach($apps as &$app_item) {
  echo "Check '".$app_item['appid_ios']."'\n";
  $certfilename="certs/cer/ios/".$app_item['appid_ios'].".pem";
  if(is_readable($certfilename)) {
    
    $ctx = stream_context_create();
    stream_context_set_option($ctx, 'ssl', 'local_cert', $certfilename);
//		stream_context_set_option($ctx, 'ssl', 'passphrase', $this->apnsData[$development]['passphrase']);
    $ssl_stream=stream_socket_client('tls://gateway.push.apple.com:2195', $error, $errorString, 100, (STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT), $ctx);
    if(!$ssl_stream){
			echo "--- Failed to connect using cert file '".$certfilename."' for '".$app_item['appid_ios']."'\n";
			unset($ssl_stream);
		} else {
      fclose($ssl_stream);
			unset($ssl_stream);
      echo "Cert file '".$certfilename."' for '".$app_item['appid_ios']."' is correct\n";
    }
  } else echo "Cannot find or read file '".$certfilename."'\n";
}

