<?php

echo "<div class='container'>";

$tITLE="Call couchdb by ID";

echo "<div class='jcommont' style='width: 600'><table>";
echo "<thead><tr>";
echo "<th>ID</th>";
echo "<th>show '_sync'</th>";
echo "<th>&nbsp;</th>";
echo "</tr></thead>";
echo "<tr><form method='post'>";
echo "<td><input type='text' name='docid' value='".(isset($p_docid) ? $p_docid : "")."' style='width: 32em;'/></td>";
echo "<td width='100'><input type='checkbox' name='syncinc' style='position: relative; left: 0;'/></td>";
echo "<td width='50'><input type='submit' name='call' value='Call'></td>";
echo "</form></tr>";
echo "</table></div>";

if(isset($p_docid, $p_call)) {
  $cb = new Couchbase('104.236.154.101:8091', 'production_final', 'Zv82FdQfaC', 'production_final');
  $result = $cb->get($p_docid);
  if(isset($result)) {
    $toshow=json_decode($result,true);
    if(!isset($p_syncinc)) unset($toshow['_sync']);
    echo "<pre>";
    print_r($toshow);
    echo "</pre>";
  }
}




//$result = $cb->view("dev_current_status", "status", array('startkey' => $last_dt_arr));
//if(isset($result) and $result['total_rows']) {
//    echo $result['total_rows'];
//    print_r($result);
//    foreach($result['rows'] as &$result_item) {
//        print_r($result_item);
//        //print_r(prepare_profile_data($result_item));
//    }
//}
//var_dump($result);
//print_r($result);

//$result = $cb->get('034F670A-0D2A-437B-98F1-66FA398B27A4');

//$result = $cb->get('08050FBA-0872-4CBA-9546-0BDB3BE73B6D');
//
////echo "<pre>";
////print_r(json_decode($result,true));
////echo "</pre>";
//
//$custchnls=array();
//array_push($custchnls, json_decode($result,true)['customChannels'][0]);
//print_r($custchnls);
//$result = $cb->view("dev_current_status", "advert_by_customchannel", array('keys' => $custchnls));
//if(isset($result) and $result['total_rows']) {
//  echo "<pre>";
//  echo $result['total_rows']."\n";
//    foreach($result['rows'] as &$result_item) {
//        print_r($result_item);
//    }
//  echo "</pre>";
//}
//echo "<pre>";
//print_r(json_decode($result,true));
//echo "</pre>";
//$custom_channel=json_decode($result,true)['customChannels'][0];
//print $custom_channel;

//advert_by_customchannel

//advertIdentificator



//echo "</plaintext>finish test\n</pre>";
echo "</div>";

?>