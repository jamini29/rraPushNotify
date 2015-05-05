<?php

// sqli part - need to move to classes as well !!!
function get_langs($db) {
    $langs=array();
    $sql="select * from `language_list`";
    $result=$db->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) { $langs[$row['language_list_id']]=$row; }
    $db->free_result($result);
    return $langs;
}
function get_apps($db) {
    $apps=array();
    $sql="select * from `app_list`";
    $result=$db->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) { $apps[$row['app_list_id']]=$row; }
    $db->free_result($result);
    return $apps;
}
function get_camp($db,$cId) {
    $sql="select * from `push_campaign_list` ".
            "where `push_campaign_list_id`='{$db->prepare($cId)}'";
    $row=$db->query($sql, true);
    return $row;
}

function get_camp_langs($db, $cId) {
    $cLangs=array();
    $sql="select * from `push_campaign_list_languages` ".
        "where `push_campaign_list_id`='{$db->prepare($cId)}'";
    $result=$db->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) { $cLangs[$row['push_campaign_list_languages_id']]=$row; }
    $db->free_result($result);
    return $cLangs;
}
function get_camp_apps($db, $cId) {
    $cApps=array();
    $sql="select * from `push_campaign_list_app` ".
        "where `push_campaign_list_id`='{$db->prepare($cId)}'";
    $result=$db->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) { $cApps[$row['push_campaign_list_app_id']]=$row; }
    $db->free_result($result);
    return $cApps;
}
function get_camp_conds($db, $cId) {
    $cConds=array();
    $sql="select * from `push_campaign_list_conditions` ".
        "where `push_campaign_list_id`='{$db->prepare($cId)}'";
    $result=$db->query($sql);
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) { $cConds[$row['push_campaign_list_conditions_id']]=$row; }
    $db->free_result($result);
    return $cConds;
}
function get_camps_data($db, $cIds) {
    $data=array();
    foreach($cIds as &$camp_id) {
        $data[$camp_id]['langs']=get_camp_langs($db, $camp_id);
        $data[$camp_id]['apps']=get_camp_apps($db, $camp_id);
        $data[$camp_id]['conds']=get_camp_conds($db, $camp_id);
        $data[$camp_id]['camp']=get_camp($db, $camp_id);
    }
    return $data;
}

// huge list - memory allocation failure possible !!!
function list_message_targets($db, $cData, $langs_in) {
  $list=array();
    
  $apps_IDS_ARR=array();
  foreach($cData['apps'] as &$it) array_push($apps_IDS_ARR, $it['app_list_id']);
  $apps_IDS=count($apps_IDS_ARR) ? implode(', ', $apps_IDS_ARR) : "'0'";
  $langs_a_prep=array();
  $camp_langs_all=0;
  $camp_langs_selected=$cData['langs'];
  foreach($camp_langs_selected as $key => $item) {
    $langs_a_prep[$langs_in[$item['language_list_id']]['language_code']]=$item['push_message'];
    if($item['language_list_id'] == 1) {
      $camp_langs_all=1;
      unset($camp_langs_selected[$key]);
    }
  }
  $langs_a=array();
  foreach($camp_langs_selected as &$it) { array_push($langs_a, "'".$langs_in[$it['language_list_id']]['language_code']."'"); }
  $langs_c=implode(', ', $langs_a);
  $cond_have_IDS_ARR=array();
  $cond_nhave_IDS_ARR=array();
  foreach ($cData['conds'] as &$item) {
    if($item['have']) array_push($cond_have_IDS_ARR, $item['app_list_id']);
    else array_push($cond_nhave_IDS_ARR, $item['app_list_id']);
  }
            
  $sql="select `pid`,`gmtoffset`,`lang` from `apns_devices` ".
       "where `applistid` in (".$apps_IDS.") ".($camp_langs_all ? "" : " and `lang` in (".$langs_c.")")." and `status`='active' ";
  if(count($cond_have_IDS_ARR))
    foreach($cond_have_IDS_ARR as $haveID) $sql.=" and ".$haveID." in (`haveappidlist`) ";
  if(count($cond_nhave_IDS_ARR))
    foreach($cond_nhave_IDS_ARR as $nhaveID) $sql.=" and ".$nhaveID." not in (`haveappidlist`) ";
  $result=$db->query($sql);
  $i=0;
  while($row=$result->fetch_array(MYSQLI_ASSOC)) {
    $i++; if($i%1000==0) echo $i."\n";
    array_push($list, array(
                            'pid' => $row['pid'],  
                            'utctimestamp' => ($cData['camp']['planned_ts']-$row['gmtoffset']),
                            'alert' => (isset($langs_a_prep[$row['lang']]) ? $langs_a_prep[$row['lang']] : $langs_a_prep['ANY'] ),
                           )
              );
  }
  $db->free_result($result);
  return $list;
}

function list_message_targets_reduced($db, $cData, $langs_in) {
  $list=array();
    
  $apps_IDS_ARR=array();
  foreach($cData['apps'] as &$it) array_push($apps_IDS_ARR, $it['app_list_id']);
  $apps_IDS=count($apps_IDS_ARR) ? implode(', ', $apps_IDS_ARR) : "0";
//  $langs_a_prep=array();
  $camp_langs_all=0;
  $camp_langs_selected=$cData['langs'];
  foreach($camp_langs_selected as $key => $item) {
//    $langs_a_prep[$langs_in[$item['language_list_id']]['language_code']]=$item['push_message'];
    if($item['language_list_id'] == 1) {
      $camp_langs_all=1;
      unset($camp_langs_selected[$key]);
    }
  }
  $langs_a=array();
  foreach($camp_langs_selected as &$it) { array_push($langs_a, "'".$langs_in[$it['language_list_id']]['language_code']."'"); }
  $langs_c=implode(', ', $langs_a);
  $cond_have_IDS_ARR=array();
  $cond_nhave_IDS_ARR=array();
  foreach ($cData['conds'] as &$item) {
    if($item['have']) array_push($cond_have_IDS_ARR, $item['app_list_id']);
    else array_push($cond_nhave_IDS_ARR, $item['app_list_id']);
  }
            
  $sql="select `pid` as apns_devices_pid,`gmtoffset`,`lang` from `apns_devices` ".
       "where `applistid` in (".$apps_IDS.") ".($camp_langs_all ? "" : " and `lang` in (".$langs_c.")")." and `status`='active' ";
  if(count($cond_have_IDS_ARR))
    foreach($cond_have_IDS_ARR as $haveID) $sql.=" and ".$haveID." in (`haveappidlist`) ";
  if(count($cond_nhave_IDS_ARR))
    foreach($cond_nhave_IDS_ARR as $nhaveID) $sql.=" and ".$nhaveID." not in (`haveappidlist`) ";
  $result=$db->query($sql);
  $i=0;
  while($row=$result->fetch_array(MYSQLI_ASSOC)) {
    $i++; if($i%1000==0) echo $i."\n";
    array_push($list, array(
                            'pid' => $row['pid'],
                            'gmtoffset' => $row['gmtoffset'],
                            'lang' => $row['lang'],
                           )
              );
//    array_push($list, array(
//                            'pid' => $row['pid'],  
//                            'utctimestamp' => ($cData['camp']['planned_ts']-$row['gmtoffset']),
//                            'alert' => (isset($langs_a_prep[$row['lang']]) ? $langs_a_prep[$row['lang']] : $langs_a_prep['ANY'] ),
//                           )
//              );
  }
  $db->free_result($result);
  return $list;
}

function list_and_queue_messages($db, $cData, $langs_in, $APNS) {
    
  $apps_IDS_ARR=array();
  foreach($cData['apps'] as &$it) array_push($apps_IDS_ARR, $it['app_list_id']);
  $apps_IDS=count($apps_IDS_ARR) ? implode(', ', $apps_IDS_ARR) : "'0'";
  $langs_a_prep=array();
  $camp_langs_all=0;
  $camp_langs_selected=$cData['langs'];
  foreach($camp_langs_selected as $key => $item) {
    $langs_a_prep[$langs_in[$item['language_list_id']]['language_code']]=$item['push_message'];
    if($item['language_list_id'] == 1) {
      $camp_langs_all=1;
      unset($camp_langs_selected[$key]);
    }
  }
  $langs_a=array();
  foreach($camp_langs_selected as &$it) { array_push($langs_a, "'".$langs_in[$it['language_list_id']]['language_code']."'"); }
  $langs_c=implode(', ', $langs_a);
  $cond_have_IDS_ARR=array();
  $cond_nhave_IDS_ARR=array();
  foreach ($cData['conds'] as &$item) {
    if($item['have']) array_push($cond_have_IDS_ARR, $item['app_list_id']);
    else array_push($cond_nhave_IDS_ARR, $item['app_list_id']);
  }
            
  $sql="select `pid`,`gmtoffset`,`lang`, `customchannel` from `apns_devices` ".
       "where `applistid` in (".$apps_IDS.") ".($camp_langs_all ? "" : " and `lang` in (".$langs_c.")")." and `status`='active' ";
  if(count($cond_have_IDS_ARR))
    foreach($cond_have_IDS_ARR as $haveID) $sql.=" and ".$haveID." in (`haveappidlist`) ";
  if(count($cond_nhave_IDS_ARR))
    foreach($cond_nhave_IDS_ARR as $nhaveID) $sql.=" and ".$nhaveID." not in (`haveappidlist`) ";
  $result=$db->query($sql);
  $i=0;
  while($row=$result->fetch_array(MYSQLI_ASSOC)) {
    $i++; if($i%10000==0) echo $i."\n";
//    array_push($list, array(
//                            'pid' => $row['pid'],  
//                            'utctimestamp' => ($cData['camp']['planned_ts']-$row['gmtoffset']),
//                            'alert' => (isset($langs_a_prep[$row['lang']]) ? $langs_a_prep[$row['lang']] : $langs_a_prep['ANY'] ),
//                           )
//              );
    $t=array(
              '26652de35dac46589d15867a982ebe06',
              '9afb4b5b2ce34cf5a4630cc4157db776',
              '7874900f130b45939a24d48b2fe1f2b0',
              'bd7f6a95deaa4fd6a47efb5ddf8ce590',
              '7bd2af827f7d4839a936bb4b2780324e',
              'df17ee93f9ed465fa0fb6dd0bfdc67c2',
              '3820e8366c4546e3ac79069b35797e7e',
              '412e6db40c1748dcb005cc1c3a22a319',
              '3afd17488759481498d9078f948acfbe',
              'cede5dd7f74945d49e387e2306ecef27',
//              'f570866a8492436db7437b912d53d0f4',
//              '4dce9ea4a22b4c9a960df9d30b6425a4',
            ); // just filter tes pushes
//    if(in_array($row['customchannel'],$t)) {
    // just to not send strange message if campaign langs leak !
    if(isset($langs_a_prep[$row['lang']]) or isset($langs_a_prep['ANY'])) {
      $APNS->newMessage($row['pid'],($cData['camp']['planned_ts']-$row['gmtoffset']),NULL,$cData['camp']['push_campaign_list_id']);
      $APNS->addMessageAlert(isset($langs_a_prep[$row['lang']]) ? $langs_a_prep[$row['lang']] : $langs_a_prep['ANY'] );
      $APNS->queueMessage();
    }
//    }
  }
  $db->free_result($result);
  return $i;
}



// temporary - need to depricate
function is_dev($name) {
    global $ini;
    $suffix='';
    if($ini['global']['develop']) { $suffix="dev_"; }
    return $suffix.$name;
}

function prepare_register_data_ts($inarr, $appids=array(), $supportedlangs=array('en')) {
  $prepared=array(
      'task'                  =>'register',
      'appid'                 =>$inarr['value']['appId'],         //ok = appId
      'customchannel'         =>$inarr['value']['customChannel'], //ok = customChannel
      'pushidentificator'     =>str_replace(array('<','>',' '),'',$inarr['value']['pushIdentificator']),      //ok
      'pushbadge'             =>'disabled',   // do not use pushbadge - queueMessage alert remarked
      'pushalert'             =>'enabled',    // this is only we need
      'pushsound'             =>'enabled',    // just for in future use
      'lang'                  =>get_prefLang((isset($inarr['value']['langCode']) ? $inarr['value']['langCode'] : 'en'),
                                             (isset($inarr['value']['preferedLangCodes']) ? $inarr['value']['preferedLangCodes'] : 'en'),
                                              $supportedlangs), 
      'gmtoffset'             =>isset($inarr['value']['secondsFromGMT']) ? $inarr['value']['secondsFromGMT'] : '-18000',  // timezone offest
      'docid'                 =>$inarr['id'],    // just for debug in strange cases
      'profilets'            =>isset($inarr['key']) ? $inarr['key'] : 0,   // just simlify search for newer record
      'applistid'             =>isset($appids[$inarr['value']['appId']]) ? $appids[$inarr['value']['appId']] : 0, // map id - hz
  );
  return $prepared;
}

function get_prefLang($langC='en', $preferedLangCodes='en', $supported=array('en','fr','de','es','it','pt','ru','ko','zh','ja')) {
  $langCode=normalize_langCode($langC);
  $appl=(in_array(substr($langCode,0,2), $supported)) ? substr($langCode,0,2) : 'en';
  $pref='en';
  foreach(array_map('trim',  explode(',', $preferedLangCodes)) as $try_lang) {
    if(in_array(substr($try_lang,0,2), $supported)) {
      $pref=substr($try_lang,0,2);
      break;
    }
  }
  return ($appl !== 'en') ? $appl : $pref;
}

function normalize_langCode($lc='en') {
  $lang_exeptions=array('jp' => 'ja',);
  $l=substr(trim($lc),0,2);
  return (isset($l) and isset($lang_exeptions[$l])) ? $lang_exeptions[$l] : $l;
}
