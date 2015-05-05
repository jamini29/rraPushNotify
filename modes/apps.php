<?php

//if($DEBUG) {
//    echo "<pre>";
//    print_r($_POST);
//    //$i=0;
//    //while($i++ < 100) { echo ":)\n"; }
//    echo "</pre>";
//}
//    $query = "select * from `app_list` order by `app_list_id` asc";
//    $result = mysql_query($query,$link) or dielog("Cannot execute query: ".mysql_error());
//    $apps=array();
//    while($row_obj = mysql_fetch_object($result)) { array_push($apps, $row_obj); }

$tITLE="Manage Applications";

$apps=get_list('app_list');

function cmp_by_app_name($a, $b) { return strcmp($a->app_name, $b->app_name); }
usort($apps, "cmp_by_app_name");
echo "<div class='container'>";
function ok_on_checked($status) { if(strlen($status)) { return '&#9830;'; } }
echo "<div class='jcommont' style='width: 800'><table>";
echo "<thead><tr>";
echo "<th>App name</th>";
echo "<th>Platform</th>";
echo "<th>AppId</th>";
echo "<th>AppCertificate</th>";
echo "<th>&nbsp;</th>";
echo "</tr></thead>";
echo "<tbody>";

foreach ($apps as &$app)
{
    echo "<tr>";
    echo "<td rowspan=2>".$app->app_name."</td>";
    echo "<td>iOS</td>";
    $file_ios='';
    if($app->ios) {
        echo "<td>".$app->appid_ios."</dt>";
        echo "<td>"; //.ok_on_checked($app->appcer_ios).
        if(!file_exists(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$app->appid_ios.".pem")) {
          echo "<font color='red'><b>Certificate not found</b></font>";
        } else {
          $file_ios=file_get_contents(ROOTDIR."/".$ini['global']['certs_dir_ios']."/".$app->appid_ios.".pem");
          if(!openssl_x509_check_private_key($file_ios,$file_ios)) {
            echo "<font color='red'><b>Certificate Error</b></font>";
          } else {
            $cer_arr=openssl_x509_parse($file_ios, false);
            if($cer_arr['validTo_time_t']<time()-14*86400 and $cer_arr['validTo_time_t']>time()) {
              echo "<font color='red'>ValidTo: ".date("Y-m-d",$cer_arr['validTo_time_t'])."</font>";
            } elseif($cer_arr['validTo_time_t']<time()) {
              echo "<font color='red'><b>NotValid: ".date("Y-m-d",$cer_arr['validTo_time_t'])."</b></font>";
            } else {
              echo "<font color='green'>ValidTo: ".date("Y-m-d",$cer_arr['validTo_time_t'])."</font>";
            }
          }
        }
      echo "</dt>";
    } else {
        echo "<td colspan=2></td>";
    }
    echo "<form action='index.php?mode=apps_edit' method='post'>".
            "<input type='hidden' name='app_list_id' value='".$app->app_list_id."'>";
    echo "<td rowspan=2><input type='submit' name='edit' value='Edit'></td>";
    echo "</form>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>Android</td>";
    if($app->android) {
        echo "<td>".$app->appid_android."</dt><td>".ok_on_checked($app->appcer_android)."</dt>";
    } else {
        echo "<td colspan=2></td>";
    }
    echo "</tr>";
}
echo "<tr style='background-color: E1EEF4;'><td colspan='4'>&nbsp;</td><form action='index.php?mode=apps_edit' method='post'><td><input type='submit' name='add' value='Add'></td></form></tr>";
echo "</tbody>";
echo "</table></div>";
echo "</div>";
?>

