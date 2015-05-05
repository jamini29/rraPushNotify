<?php

function get_list($table) {
    global $link;
    $id=$table."_id";
    $query = "select * from `".$table."` order by `".$id."` asc";
    $result = mysql_query($query,$link) or dielog("Cannot execute query: ".mysql_error());
    $ret=array();
    while($row_obj = mysql_fetch_object($result)) { $ret[$row_obj->$id]=$row_obj; }
    return $ret;
}

function get_list_item_by_id($table,$id) {
    global $link;
    $query = "select * from `".$table."` where `".$table."_id`='".$id."'";
    $result = mysql_query($query,$link) or dielog("Cannot execute query: ".mysql_error());
    $ret=mysql_fetch_object($result);
    return $ret;
}

function get_list_where($table,$where_id,$where_value) {
    global $link;
    $id=$table."_id";
    $query = "select * from `".$table."` where `".$where_id."`='".$where_value."' order by `".$id."` asc";
    $result = mysql_query($query,$link) or dielog("Cannot execute query: ".mysql_error());
    $ret=array();
    while($row_obj = mysql_fetch_object($result)) { $ret[$row_obj->$id]=$row_obj; }
    return $ret;
}

function get_cer_files_in_dir($dir) {
    $files_cer=array();
    if($dir_cer = opendir($dir)) {
        while(false !== ($entry = readdir($dir_cer))) {
            if ($entry != "." && $entry != ".." && substr($entry, strrpos($entry,".")+ 1) == 'cer') {
                array_push($files_cer, $entry);
            }
        }
        closedir($dir_cer);
    }
    return $files_cer;
}

function prepare_register_data($inarr, $appids=array(), $supportedlangs=array('en')) {
    $lang=isset($inarr['value']['langCode']) ? $inarr['value']['langCode'] : 'en'; // init by langCode or 'en'
    if(isset($inarr['value']['preferedLangCodes'])) {
      $pref_l=array_map(function($a) { return substr($a,0,2);},array_map('trim', explode(",",$inarr['value']['preferedLangCodes'])));
      foreach ($pref_l as &$lan) {if(in_array($lan,$supportedlangs)) { $lang=$lan; break; }} // first preffered in supported
    }
    $prepared=array(
        'task'                  =>'register',
        'appid'                 =>$inarr['value']['appId'],         //ok = appId
        'customchannel'         =>$inarr['value']['customChannel'], //ok = customChannel
        'pushidentificator'     =>str_replace(array('<','>',' '),'',$inarr['value']['pushIdentificator']),      //ok
        'pushbadge'             =>'disabled',   // do not use pushbadge - queueMessage alert remarked
        'pushalert'             =>'enabled',    // this is only we need
        'pushsound'             =>'enabled',    // just for in future use
        'lang'                  =>$lang, 
        'gmtoffset'             =>isset($inarr['value']['secondsFromGMT']) ? $inarr['value']['secondsFromGMT'] : '-18000',  // timezone offest
        'docid'                 =>$inarr['value']['id'],    // just for debug in strange cases
        'dt2compare'            =>isset($inarr['key']) ? $inarr['key'] : array_fill(0, 5, 0),   // just simlify search for newer record
        'applistid'             =>isset($appids[$inarr['value']['appId']]) ? $appids[$inarr['value']['appId']] : 0, // map id - hz
    );
//    echo "langCode=".(isset($inarr['value']['langCode']) ? $inarr['value']['langCode'] : "XZ")."\n";
//    echo "preferedLangCodes=".(isset($inarr['value']['preferedLangCodes']) ? $inarr['value']['preferedLangCodes'] : "XZ")."\n";
//    echo "lang=". $lang."\n";
    return $prepared;
}

function normalize_langCode($lc='en') {
  $lang_exeptions=array('jp' => 'ja',);
  $l=substr(trim($lc),0,2);
  return (isset($l) and isset($lang_exeptions[$l])) ? $lang_exeptions[$l] : $l;
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
                                               (isset($inarr['value']['preferedLangCodes']) ? $inarr['value']['preferedLangCodes'] : 'en')), 
        'gmtoffset'             =>isset($inarr['value']['secondsFromGMT']) ? $inarr['value']['secondsFromGMT'] : '-18000',  // timezone offest
        'docid'                 =>$inarr['id'],    // just for debug in strange cases
        'ts2compare'            =>isset($inarr['key']) ? $inarr['key'] : 0,   // just simlify search for newer record
        'applistid'             =>isset($appids[$inarr['value']['appId']]) ? $appids[$inarr['value']['appId']] : 0, // map id - hz
    );
    return $prepared;
}

function get_prefLang($langC='en', $preferedLangCodes='en') {
  $langCode=normalize_langCode($langC);
  $supported=array('en','fr','de','es','it','pt','ru','ko','zh','ja');
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

function app_c_from_camp_apps($arr,$app_list) {
    $ret=array(
        'ios' => array(),
        'android' => array(),
    );
    foreach($ret as &$camp_app_item) {
        if($camp_app_item->ios) {
            array_push($ret['ios'], $app_list[$camp_app_item->app_list_id]->appid_ios);
        }
        if($camp_app_item->android) {
            array_push($ret['android'], $app_list[$camp_app_item->app_list_id]->appid_android);
        }
    }
    return $ret;
}


function update_camp_affected($campaign_id) {
    global $link;
    $rez=affected_devices_count($campaign_id);
    $query="update `push_campaign_list` ".
           "set `planned_reach`='".$rez['apns_count']."' ".
           "where `push_campaign_list_id`='".$campaign_id."'";
    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
}

//
//function affected_devices_list($campaign_id) {
//    $list=array(
//        'apns_count' => 0,
//        'apns_devices' => array(), // ios recepients array
//// android recepients must be added in future
//    );
//    global $link;
//
//    $apps=get_list('app_list');
//    $langs=get_list('language_list');
//
//    $camp_apps=get_list_where('push_campaign_list_app','push_campaign_list_id',$campaign_id);
//    $camp_langs=get_list_where('push_campaign_list_languages','push_campaign_list_id',$campaign_id);
//    $camp_conds=get_list_where('push_campaign_list_conditions','push_campaign_list_id',$campaign_id);
//    
//    $apps_c=count($camp_apps) ? implode(', ',array_map(function($o) { global $apps; return "'".$apps[$o->app_list_id]->appid_ios."'"; }, $camp_apps)) : "'just-dummy-if-no-apps-selected'";
//    
//    $camp_langs_all=0;
//    $camp_langs_selected=$camp_langs;
//    foreach($camp_langs_selected as $key => $item) {
//        if($item->language_list_id == 1) {
//            $camp_langs_all=1;
//            unset($camp_langs_selected[$key]);
//        }
//    }
//    $langs_c=implode(', ',array_map(function($o) { global $langs; return "'".$langs[$o->language_list_id]->language_code."'"; }, $camp_langs_selected));
//    
//    
//    $cond_have=array();
//    $cond_nhave=array();
//    foreach ($camp_conds as &$item) {
//        if($item->have) {
//            array_push($cond_have, $item->app_list_id);
//        } else {
//            array_push($cond_nhave, $item->app_list_id);
//        }
//    }
//    $cond_c_have=implode(', ',array_map(function($o) { global $apps; return "'".$apps[$o]->appid_ios."'"; }, $cond_have));
//    $cond_c_nhave=implode(', ',array_map(function($o) { global $apps; return "'".$apps[$o]->appid_ios."'"; }, $cond_nhave));
//    
//    $query="select `pid`,`appid`,`pushidentificator`,`lang`,`gmtoffset` from `apns_devices` ".
//        "where `appid` in (".$apps_c.") ".
//        ($camp_langs_all ? "" : " and `lang` in (".$langs_c.")").
//        " and `status`='active' ";
//    if(count($cond_have) or count($cond_nhave)) {
//        $query.=" and `customchannel` in ".
//                "(select `chans`.`chan_id` from (".
//                "select `chans_stat`.`customchannel` as `chan_id`, ".
//                (count($cond_have) ? "sum(if(`chans_stat`.`appid` in (".$cond_c_have."),1,0))" : "0" )." as `summ_in`, ".
//                (count($cond_nhave) ? "sum(if(`chans_stat`.`appid` in (".$cond_c_nhave."),1,0))" : "0" )." as `summ_out` ".
//                "from `apns_devices` as `chans_stat` ".
//                "left outer join `apns_devices` on `apns_devices`.`customchannel`=`chans_stat`.`customchannel` ".
//                "where `apns_devices`.`appid` in (".$apps_c.") and `chans_stat`.`status`='active' ".
//                "group by `chans_stat`.`customchannel`) as `chans` ".
//                "where `chans`.`summ_in`=".count($cond_have)." and `chans`.`summ_out`=0)";
//    }
//    $result = mysql_query($query,$link) or dielog("Cannot execute query: ".mysql_error());
//    while($row = mysql_fetch_assoc($result)) {
//        array_push($list['apns_devices'],$row);
//    }
//    $list['apns_count']=count($list['apns_devices']);
//    return $list;
////    return $query;
//}


//function affected_devices_count($campaign_id) {
//    $list=array(
//        'apns_count' => 0,
//        'apns_devices' => array(), // ios recepients array
//// android recepients must be added in future
//    );
//    global $link;
//    $camp_apps=get_list_where('push_campaign_list_app','push_campaign_list_id',$campaign_id);
//    $camp_langs=get_list_where('push_campaign_list_languages','push_campaign_list_id',$campaign_id);
//    $camp_conds=get_list_where('push_campaign_list_conditions','push_campaign_list_id',$campaign_id);
//    $apps_c=count($camp_apps) ? implode(', ',array_map(function($o) { global $apps; return "'".$apps[$o->app_list_id]->appid_ios."'"; }, $camp_apps)) : "'just-dummy-if-no-apps-selected'";
//
//    $camp_langs_all=0;
//    $camp_langs_selected=$camp_langs;
//    foreach($camp_langs_selected as $key => $item)
//      if($item->language_list_id == 1) {
//        $camp_langs_all=1;
//        unset($camp_langs_selected[$key]);
//      }
//    $langs_c=implode(', ',array_map(function($o) { global $langs; return "'".$langs[$o->language_list_id]->language_code."'"; }, $camp_langs_selected));
//    
//    $cond_have=array();
//    $cond_nhave=array();
//    foreach ($camp_conds as &$item) {
//      if($item->have) array_push($cond_have, $item->app_list_id);
//      else array_push($cond_nhave, $item->app_list_id);
//    }
//    $cond_c_have=implode(', ',array_map(function($o) { global $apps; return "'".$apps[$o]->appid_ios."'"; }, $cond_have));
//    $cond_c_nhave=implode(', ',array_map(function($o) { global $apps; return "'".$apps[$o]->appid_ios."'"; }, $cond_nhave));
//    
//    $query="select `pid`,`appid`,`pushidentificator`,`lang`,`gmtoffset` from `apns_devices` ".
//        "where `appid` in (".$apps_c.") ".($camp_langs_all ? "" : " and `lang` in (".$langs_c.")")." and `status`='active' ";
//    if(count($cond_have) or count($cond_nhave)) {
//        $query.=" and `customchannel` in ".
//                "(select `chans`.`chan_id` from ("."select `chans_stat`.`customchannel` as `chan_id`, ".
//                (count($cond_have) ? "sum(if(`chans_stat`.`appid` in (".$cond_c_have."),1,0))" : "0" )." as `summ_in`, ".
//                (count($cond_nhave) ? "sum(if(`chans_stat`.`appid` in (".$cond_c_nhave."),1,0))" : "0" )." as `summ_out` ".
//                "from `apns_devices` as `chans_stat` ".
//                "left outer join `apns_devices` on `apns_devices`.`customchannel`=`chans_stat`.`customchannel` ".
//                "where `apns_devices`.`appid` in (".$apps_c.") and `chans_stat`.`status`='active' ".
//                "group by `chans_stat`.`customchannel`) as `chans` where `chans`.`summ_in`=".count($cond_have)." and `chans`.`summ_out`=0)";
//    }
//    $result = mysql_query($query,$link) or dielog("Cannot execute query: ".mysql_error());
//    while($row = mysql_fetch_assoc($result)) $list['apns_count']++;
//    return $list;
//}

function affected_devices_count($campaign_id) {
  $list=array(
      'apns_count' => 0,
      'apns_devices' => array(), // ios recepients array
// android recepients must be added in future
  );
  global $link;
  $camp_apps=get_list_where('push_campaign_list_app','push_campaign_list_id',$campaign_id);
  $camp_langs=get_list_where('push_campaign_list_languages','push_campaign_list_id',$campaign_id);
  $camp_conds=get_list_where('push_campaign_list_conditions','push_campaign_list_id',$campaign_id);

  $apps_IDS_ARR=array();
  foreach($camp_apps as &$item) array_push($apps_IDS_ARR, $item->app_list_id);
  $apps_IDS=count($apps_IDS_ARR) ? implode(', ', $apps_IDS_ARR) : "'0'";
  $camp_langs_all=0;
  $camp_langs_selected=$camp_langs;
  foreach($camp_langs_selected as $key => $item)
    if($item->language_list_id == 1) {
      $camp_langs_all=1;
      unset($camp_langs_selected[$key]);
    }
  $langs_c=implode(', ',array_map(function($o) { global $langs; return "'".$langs[$o->language_list_id]->language_code."'"; }, $camp_langs_selected));
  $cond_have_IDS_ARR=array();
  $cond_nhave_IDS_ARR=array();
  foreach ($camp_conds as &$item) {
    if($item->have) array_push($cond_have_IDS_ARR, $item->app_list_id);
    else array_push($cond_nhave_IDS_ARR, $item->app_list_id);
  }

  $query="select count(`pid`) as apns_count from `apns_devices` ".
      "where `applistid` in (".$apps_IDS.") ".($camp_langs_all ? "" : " and `lang` in (".$langs_c.")")." and `status`='active' ";
  if(count($cond_have_IDS_ARR))
    foreach($cond_have_IDS_ARR as $haveID) $query.=" and ".$haveID." in (`haveappidlist`) ";
  if(count($cond_nhave_IDS_ARR))
    foreach($cond_nhave_IDS_ARR as $nhaveID) $query.=" and ".$nhaveID." not in (`haveappidlist`) ";
  $result = mysql_query($query,$link) or dielog("Cannot execute query: ".mysql_error());
  $row=mysql_fetch_assoc($result);
  $list['apns_count']=$row['apns_count'];
  return $list;
}

function affected_devices_list($campaign_id) {
  $list=array(
      'apns_count' => 0,
      'apns_devices' => array(), // ios recepients array
// android recepients must be added in future
  );
  global $link;
  $camp_apps=get_list_where('push_campaign_list_app','push_campaign_list_id',$campaign_id);
  $camp_langs=get_list_where('push_campaign_list_languages','push_campaign_list_id',$campaign_id);
  $camp_conds=get_list_where('push_campaign_list_conditions','push_campaign_list_id',$campaign_id);

  $apps_IDS_ARR=array();
  foreach($camp_apps as &$item) array_push($apps_IDS_ARR, $item->app_list_id);
  $apps_IDS=count($apps_IDS_ARR) ? implode(', ', $apps_IDS_ARR) : "'0'";
  $camp_langs_all=0;
  $camp_langs_selected=$camp_langs;
  foreach($camp_langs_selected as $key => $item)
    if($item->language_list_id == 1) {
      $camp_langs_all=1;
      unset($camp_langs_selected[$key]);
    }
  $langs_c=implode(', ',array_map(function($o) { global $langs; return "'".$langs[$o->language_list_id]->language_code."'"; }, $camp_langs_selected));
  echo $langs_c."<br/>";
  $cond_have_IDS_ARR=array();
  $cond_nhave_IDS_ARR=array();
  foreach ($camp_conds as &$item) {
    if($item->have) array_push($cond_have_IDS_ARR, $item->app_list_id);
    else array_push($cond_nhave_IDS_ARR, $item->app_list_id);
  }

  $query="select `pid`,`appid`,`pushidentificator`,`lang`,`gmtoffset` from `apns_devices` ".
      "where `applistid` in (".$apps_IDS.") ".($camp_langs_all ? "" : " and `lang` in (".$langs_c.")")." and `status`='active' ";
  if(count($cond_have_IDS_ARR)) {
    foreach($cond_have_IDS_ARR as $haveID) {
      $query.=" and ".$haveID." in (`haveappidlist`) ";
    }
  }
  if(count($cond_nhave_IDS_ARR)) {
    foreach($cond_nhave_IDS_ARR as $nhaveID) {
      $query.=" and ".$nhaveID." not in (`haveappidlist`) ";
    }
  }
  $result = mysql_query($query,$link) or dielog("Cannot execute query: ".mysql_error());
  while($row = mysql_fetch_assoc($result)) array_push($list['apns_devices'],$row);
  $list['apns_count']=count($list['apns_devices']);
  return $list;
}






























function dtarr_comp_first_less($arr1,$arr2) {
    return
        array_sum(
            array_map(
                function($a, $b, $c) { return (($a - $b) * pow(10, $c)); },
                $arr1, $arr2, range(10, 0, 2)
            )
        ) < 0
        ? true : false;
}

function is_dev($name) {
    global $ini;
    $suffix='';
    if($ini['global']['develop']) { $suffix="dev_"; }
    return $suffix.$name;
}

function cli_dielog($message) {
    error_log($message."\n",3, 'cli.err');
}
