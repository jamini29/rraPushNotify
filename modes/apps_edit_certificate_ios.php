<?php

function der2pem($der_data) {
   $pem = chunk_split(base64_encode($der_data), 64, "\n");
   $pem = "-----BEGIN CERTIFICATE-----\n".$pem."-----END CERTIFICATE-----\n";
   return $pem;
}
$apps=get_list('app_list');

if(!isset($g_app_list_id) and !isset($p_app_list_id)) {
  header("Location: index.php?mode=logout");
}
$p_app_list_id=isset($p_app_list_id) ? $p_app_list_id : $g_app_list_id;
if(!isset($apps[$p_app_list_id])) {
  header("Location: index.php?mode=logout");
}
$app_id_ios=$apps[$p_app_list_id]->appid_ios;

$errors=array();
if(!empty($_FILES) and isset($p_subm, $app_id_ios)) {
  $cer_pem = der2pem(file_get_contents($_FILES['cer_file']['tmp_name']));

  if(!count($errors)) {
    $cer_arr=openssl_x509_parse($cer_pem, false);
    if($app_id_ios !== $cer_arr['subject']['userId']) {
      array_push($errors, "AppId '".$app_id_ios."' does not corresponds certificate UserId '".$cer_arr['subject']['userId']."'");
    } else {
      $out_pem_filename=ROOTDIR."/certs/cer/ios/".$cer_arr['subject']['userId'].".pem";
    }
  }
  if(!count($errors) and !openssl_pkcs12_read(file_get_contents($_FILES['p12_file']['tmp_name']), $p12_arr, $p_p12_read_pass)) {
    array_push($errors, "The Private Key file or protecting password is not valid");
  }
  if(!count($errors) and !openssl_x509_check_private_key($cer_pem,$p12_arr['pkey'])) {
    array_push($errors, "The Private Key does not corresponds to the Certificate");
  }
  if(!count($errors) and !openssl_pkey_export($p12_arr['pkey'], $key_pem, "", array('private_key_type' => 'OPENSSL_KEYTYPE_RSA', 'encrypt_key' => false))) {
    array_push($errors, "The Private Key export error");
  }
  if(!count($errors)) {
    if(!count($errors) and !file_put_contents($out_pem_filename,$cer_pem)) {
      array_push($errors, "The Certificate write error");
    }
    if(!count($errors) and !file_put_contents($out_pem_filename,$key_pem, FILE_APPEND)) {
      array_push($errors, "The Private Key write error");
    }
    if(substr(sprintf('%o', fileperms($out_pem_filename)), -3) > 644) {
      chmod($out_pem_filename, 0644);
    }
  } 
  if(!count($errors)) {
    header("Location: index.php?mode=apps_edit&app_list_id=".$p_app_list_id);
  } else {
    jalert($errors);
  }
}
$tITLE="Add Application APNS Sertificate";
echo "<div class='container'>";
echo "<div class='jcommont' style='width: 600'><table>";
echo "<form method='post' enctype='multipart/form-data'>";
echo "<thead><tr><th colspan=2>".$app_id_ios." Certificate</th></thead>";
echo "<tbody>";
echo "<tr><td>The Certificate file (.cer)</td><td><input type='file' name='cer_file'></td></tr>";
echo "<tr><td>The Private Key file (.p12)</td><td><input type='file' name='p12_file'></td></tr>";
echo "<tr><td>The Private Key password</td><td><input type='text' name='p12_read_pass'></td></tr>";
echo "<tr><td>&nbsp;</td><td><input type='submit' name='subm' value='Update'></td></tr>";
echo "<input type='hidden' name='app_list_id' value='".$p_app_list_id."'>";
echo "</form>";

echo "</tbody></table>";
echo "</div>";

?>