<?php


// edit part
if(isset($p_app_list_id,
         $p_edit_update)
    and
    isset($p_appid_ios,$p_appid_android)
) {
    $apps=get_list('app_list');
    
    $appidexists_ios=array();
    $appidexists_android=array();
    foreach($apps as &$app_item) {
        if($app_item->ios and $app_item->app_list_id != $p_app_list_id) { array_push($appidexists_ios, $app_item->appid_ios); }
        if($app_item->android and $app_item->app_list_id != $p_app_list_id) { array_push($appidexists_android, $app_item->appid_android); }
    }
    $errors = array();
    if(!strlen($p_app_name)) { array_push($errors, "'App name' empty"); }
    if(!strlen($p_appid_ios) and !strlen($p_appid_android)) { array_push($errors, "Even one platform must be defined"); }
    if (strlen($p_appid_ios) and in_array($p_appid_ios, $appidexists_ios)) {
        array_push($errors, "iOS 'AppId' used already");
    }
    if (strlen($p_appid_android) and in_array($p_appid_android, $appidexists_android)) {
        array_push($errors, "Android 'AppId' used already");
    }
    if (strlen($p_appid_ios) and !file_exists(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$p_appid_ios.".pem")) {
        array_push($errors, "iOS 'AppCertificate' not found");
    } elseif (!openssl_x509_check_private_key(file_get_contents(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$p_appid_ios.".pem"),file_get_contents(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$p_appid_ios.".pem"))) {
        array_push($errors, "iOS 'AppCertificate' Private Key does not corresponds to the Certificate");
    }
    if (strlen($p_appid_android) and !file_exists(ROOTDIR."/".$ini['global']['certs_dir_android']."/".$p_appid_android.".pem")) {
        array_push($errors, "Android 'AppCertificate' not found");
    }
    if (count($errors) == 0) {
        $isios=0;
        $isandroid=0;
        $appcer_ios='';
        $appcer_android='';
        if(strlen($p_appid_ios)) { $isios=1; $appcer_ios=$p_appid_ios.".pem"; }
        if(strlen($appcer_android)) { $isandroid=1; $appcer_android=$p_appid_ios.".pem"; }
        
        $query="update `app_list` set ".
                "  `app_name`='".$p_app_name."'".
                ", `ios`='".$isios."'".
                ", `appid_ios`='".$p_appid_ios."'".
                ", `appcer_ios`='".$appcer_ios."'".
                ", `android`='".$isandroid."'".
                ", `appid_android`='".$p_appid_android."'".
                ", `appcer_android`='".$appcer_android."'".
                "where `app_list_id`='".$p_app_list_id."'";
        mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
        header("Location: index.php?mode=apps");
    } else {
        jalert($errors);
    }
// add part
} elseif(isset($p_app_name, $p_add_update)
    and
    isset($p_appid_ios,$p_appid_android)
) {
  $apps=get_list('app_list');
    
  $appidexists_ios=array();
  $appidexists_android=array();
  foreach($apps as &$app_item) {
    if($app_item->ios) { array_push($appidexists_ios, $app_item->appid_ios); }
    if($app_item->android) { array_push($appidexists_android, $app_item->appid_android); }
  }
  $errors = array();
  $minorerrors = array();
  if(!strlen($p_app_name)) { array_push($errors, "'App name' empty"); }
  if(!strlen($p_appid_ios) and !strlen($p_appid_android)) { array_push($errors, "Even one platform must be defined"); }
  if(strlen($p_appid_ios) and in_array($p_appid_ios, $appidexists_ios)) {
    array_push($errors, "iOS 'AppId' used already");
  }
  if(strlen($p_appid_android) and in_array($p_appid_android, $appidexists_android)) {
    array_push($errors, "Android 'AppId' used already");
  }
  if(strlen($p_appid_ios) and !file_exists(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$p_appid_ios.".pem")) {
    array_push($minorerrors, "iOS 'AppCertificate' not found");
  } elseif (!openssl_x509_check_private_key(file_get_contents(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$p_appid_ios.".pem"),file_get_contents(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$p_appid_ios.".pem"))) {
    array_push($minorerrors, "iOS 'AppCertificate' Private Key does not corresponds to the Certificate");
  }
  if(strlen($p_appid_android) and !file_exists(ROOTDIR."/".$ini['global']['certs_dir_android']."/".$p_appid_android.".pem")) {
    array_push($minorerrors, "Android 'AppCertificate' not found");
  }
  if(count($errors) == 0) {
    $isios=0;
    $isandroid=0;
    $appcer_ios='';
    $appcer_android='';
    if(strlen($p_appid_ios)) { $isios=1; $appcer_ios=$p_appid_ios.".pem"; }
    if(strlen($appcer_android)) { $isandroid=1; $appcer_android=$p_appid_ios.".pem"; }
    $query="insert into `app_list` set ".
           "  `app_name`='".$p_app_name."'".
           ", `ios`='".$isios."'".
           ", `appid_ios`='".$p_appid_ios."'".
           ", `appcer_ios`='".$appcer_ios."'".
           ", `android`='".$isandroid."'".
           ", `appid_android`='".$p_appid_android."'".
           ", `appcer_android`='".$appcer_android."'";
    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    $app_list_id=mysql_insert_id($link);
    if(count($minorerrors) == 0) header("Location: index.php?mode=apps");
    else {
      jalert($minorerrors);
      header("Location: index.php?mode=apps_edit&app_list_id=".$app_list_id);
    }   
  } else jalert(array_merge($errors,$minorerrors));
}    


//    $query="insert into `language_list` set ".
//           "`language_code`='".$_POST['language_code']."', ".
//           "`language_name`='".$_POST['language_name']."', ".
//           "`language_name_localized`='".$_POST['language_name_localized']."'";
//    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
//    header("Location: index.php?mode=lang");
//} elseif(isset($_POST['language_list_id'], $_POST['language_code'], $_POST['language_name'], $_POST['language_name_localized'], $_POST['update_edit'])) {
//    $query="update `language_list` set ".
//           "`language_code`='".$_POST['language_code']."', ".
//           "`language_name`='".$_POST['language_name']."', ".
//           "`language_name_localized`='".$_POST['language_name_localized']."' ".
//           "where `language_list_id`='".$_POST['language_list_id']."'";
//    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
//    header("Location: index.php?mode=lang");
//} elseif(isset($_POST['language_list_id'], $_POST['update_delete'])) {
//    $query="delete from `language_list` where `language_list_id`='".$_POST['language_list_id']."'";
//    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
//    header("Location: index.php?mode=lang");
//}

$apps=get_list('app_list');

echo "<div class='container'>";
echo "<pre>";
print_r($_POST);
echo "</pre>";
if((isset($p_app_list_id) and (isset($p_edit) or isset($p_edit_update)))
   or
   isset($g_app_list_id)
  ) {
    $tITLE="Edit Application";
    $p_app_list_id=isset($p_app_list_id) ? $p_app_list_id : $g_app_list_id;
    
    $app=get_list_item_by_id('app_list', $p_app_list_id);
    echo "<div class='jcommont'><table>";
    echo "<thead><tr><th>App name</th><th>Platform</th><th>AppId</th>".
         "<th>AppCertificate</th>".
         "<th>&nbsp;</th></tr></thead>";
    echo "<form action='index.php?mode=apps_edit' method='post'>";
    echo "<tbody><tr>";
    echo "<td rowspan=2><input type='text' name='app_name' value='";
        if(isset($p_app_name)) { echo $p_app_name; } else { echo $app->app_name; }
    echo "'></dt>";
    echo "<td>iOS</td>";
    echo "<td><input type='text' name='appid_ios' value='".(isset($p_appid_ios) ? $p_appid_ios : $app->appid_ios)."'></dt>";
    echo "<td>";
    $appId_ios=isset($p_appid_ios) ? $p_appid_ios : $app->appid_ios;
    if(!file_exists(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$appId_ios.".pem")) {
      $link_name="<font color='red'>Certificate not found</font></a>";
    } else {
      $file_content=file_get_contents(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$appId_ios.".pem");
      $file_date=filemtime(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$appId_ios.".pem");
      $link_name='';
      if(!openssl_x509_check_private_key($file_content,$file_content)) {
        $link_name="<font color='red'>Certificate Error</font></a>";
      } else {
        $cer_arr=openssl_x509_parse($file_content, false);
        if($cer_arr['validTo_time_t']<time()-7*86400 and $cer_arr['validTo_time_t']>time()) {
          $link_name="<font color='red'>ValidTo: ".date("Y-m-d",$cer_arr['validTo_time_t'])."</font></a>&nbsp;(created&nbsp;".date("Y-m-d H:i:s", $file_date).")";
        } elseif($cer_arr['validTo_time_t']<time()) {
          $link_name="<font color='red'><b>NotValid: ".date("Y-m-d",$cer_arr['validTo_time_t'])."</b></font></a>&nbsp;(created&nbsp;".date("Y-m-d H:i:s", $file_date).")";
        } else {
          $link_name="<font color='green'>".date("Y-m-d",$cer_arr['validTo_time_t'])."</font></a>&nbsp;(created&nbsp;".date("Y-m-d H:i:s", $file_date).")";
        }
      }
    }
    echo "<a href='index.php?mode=apps_edit_certificate_ios&app_list_id=".$p_app_list_id."'>".$link_name;
    
    //<input type='file' name='appcer_ios' value='";
    //    if(isset($p_appcer_ios)) { echo $p_appcer_ios; } else { echo $app->appcer_ios; }
    echo "</td>";
    echo "<td rowspan=2><input type='submit' name='edit_update' value='Update'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>Android</td>";
    echo "<td><input type='text' name='appid_android' value='";
        if(isset($p_appid_android)) { echo $p_appid_android; } else { echo $app->appid_android; }
    echo "'></dt>";
    echo "<td>";
    //<input type='text' name='appcer_android' value='";
    //    if(isset($p_appcer_android)) { echo $p_appcer_android; } else { echo $app->appcer_android; }
    echo "</td>";
    echo "</tr>";
    echo "<input type='hidden' name='app_list_id' value='".$p_app_list_id."'>";
    echo "</form>";
    echo "</tbody></table></div>";
  }
  elseif(isset($p_add) or isset($p_add_update)) {
    $tITLE="Add Application";
    
//    $app=get_list_item_by_id('app_list', $p_app_list_id);
    echo "<div class='jcommont'><table>";
    echo "<thead><tr><th>App name</th><th>Platform</th><th>AppId</th>".
         "<th>AppCertificate</th>".
         "<th>&nbsp;</th></tr></thead>";
    echo "<form action='index.php?mode=apps_edit' method='post'>";
    echo "<tbody><tr>";
    echo "<td rowspan=2><input type='text' name='app_name' value='".(isset($p_app_name) ? $p_app_name : "")."'></dt>";
    echo "<td>iOS</td>";
    echo "<td><input type='text' name='appid_ios' value='".(isset($p_appid_ios) ? $p_appid_ios : "")."'></dt>";
    echo "<td>";
    $appId_ios=isset($p_appid_ios) ? $p_appid_ios : "";
    if($appId_ios!="") {
      if(!file_exists(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$appId_ios.".pem")) {
        $link_name="<font color='red'>Certificate not found</font></a>";
      } else {
        $file_content=file_get_contents(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$appId_ios.".pem");
        $file_date=filemtime(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$appId_ios.".pem");
        $link_name='';
        if(!openssl_x509_check_private_key($file_content,$file_content)) {
          $link_name="<font color='red'>Certificate Error</font></a>";
        } else {
          $cer_arr=openssl_x509_parse($file_content, false);
          if($cer_arr['validTo_time_t']<time()-7*86400 and $cer_arr['validTo_time_t']>time()) {
            $link_name="<font color='red'>ValidTo: ".date("Y-m-d",$cer_arr['validTo_time_t'])."</font></a>&nbsp;(created&nbsp;".date("Y-m-d H:i:s", $file_date).")";
          } elseif($cer_arr['validTo_time_t']<time()) {
            $link_name="<font color='red'><b>NotValid: ".date("Y-m-d",$cer_arr['validTo_time_t'])."</b></font></a>&nbsp;(created&nbsp;".date("Y-m-d H:i:s", $file_date).")";
          } else {
            $link_name="<font color='green'>".date("Y-m-d",$cer_arr['validTo_time_t'])."</font></a>&nbsp;(created&nbsp;".date("Y-m-d H:i:s", $file_date).")";
          }
        }
      }
      echo "<a href='index.php?mode=apps_edit_certificate_ios&app_id_ios=".$p_appid_ios."&parent=add'>".$link_name;
    }
    echo "</td>";
    echo "<td rowspan=2><input type='submit' name='add_update' value='Add'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>Android</td>";
    echo "<td><input type='text' name='appid_android' value=''></td>";
    echo "<td>";
    echo "</td>";
    echo "</tr>";
//    echo "<input type='hidden' name='app_list_id' value='".$p_app_list_id."'>";
    echo "</form>";
    echo "</tbody></table></div>";
  }
  
  
  
  
  
  
echo "</div>";
