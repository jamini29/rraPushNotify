<?php
header('HTTP/1.1 405 not allowed', true, 405);
echo http_response_code();
//die(header("Location: /404.php", true, 302)); 
//$out_pem_filename="/home/jamini/projects/push_notify/certs/cer/ios/com.grinasys.fitnessfree.pem.old";
//$test=file_get_contents($out_pem_filename);
//$t3=openssl_x509_check_private_key($test,$test);
//
//echo "<div class='container'>";
//echo "<pre>";
//echo "---\n";
////  var_dump($t3);
//echo "</pre>";
//echo "</div>";
?>