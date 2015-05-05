<?php
//if($DEBUG) {
//    echo "<pre>";
//    print_r($_POST);
//    echo "</pre>";
//}

$apps=get_list('app_list');
$langs=get_list('language_list');

// add part
if(isset($p_push_campaign_list_id,
         $p_new_app_list_id,
         $p_update_add_apps) and
         $p_new_app_list_id!='' and
         $p_new_app_list_id!=0) {
    $camp_apps=get_list_where('push_campaign_list_app','push_campaign_list_id',$p_push_campaign_list_id);
    $camp_apps_key_exists=array();
    foreach($camp_apps as &$camp_apps_item) { array_push($camp_apps_key_exists, $camp_apps_item->app_list_id);  }
    if(!in_array($p_new_app_list_id,$camp_apps_key_exists)) {
        $query="insert into `push_campaign_list_app` set ".
               "`push_campaign_list_id`='".$p_push_campaign_list_id."', ".
               "`app_list_id`='".$p_new_app_list_id."'";
        mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    }
    update_camp_affected($p_push_campaign_list_id);
} elseif(isset($p_push_campaign_list_app_id,
               $p_update_delete_apps)) {
    $query="delete from  `push_campaign_list_app` ".
           "where `push_campaign_list_app_id`='".$p_push_campaign_list_app_id."'";
    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    update_camp_affected($p_push_campaign_list_id);
}

echo "<div class='container'>";
if(isset($p_push_campaign_list_id) and
   (isset($p_edit_apps) or isset($p_update_delete_apps) or isset($p_update_add_apps))) {
    $campaign=get_list_item_by_id('push_campaign_list',$p_push_campaign_list_id);
    echo "<div class='jcommont' style='width: 600'>";
    echo "<h1>Planned Reach: ".$campaign->planned_reach."</h1>";
    echo "<table>";
    echo "<thead><tr>";
    echo "<th>App name</th>";
    echo "<th>&nbsp;</th>";
    echo "</tr></thead>";
    $camp_apps=get_list_where('push_campaign_list_app','push_campaign_list_id',$p_push_campaign_list_id);
    // apps in campaign already
    $camp_apps_key_exists=array();
    foreach($camp_apps as &$camp_apps_item) { array_push($camp_apps_key_exists, $camp_apps_item->app_list_id);  }
    $i=0;
    foreach($camp_apps as &$camp_apps_item) {
        echo "<tr><form method='post'>";
        echo "<td>".$apps[$camp_apps_item->app_list_id]->app_name."</td>";
        echo "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>";
        echo "<input type='hidden' name='push_campaign_list_app_id' value='".
             $camp_apps_item->push_campaign_list_app_id."'>";
        echo "<td width='50'><input type='submit' name='update_delete_apps' value='Delete'></td>";
        echo "</form></tr>";
    }
    echo "<tr><form method='post'>";
    echo "<td><div class='styled'><select name='new_app_list_id' style='width: 24em;'>";
    echo "<option value='0'>- Select App -</option>";
    foreach($apps as &$apps_item) {
        if(!in_array($apps_item->app_list_id,$camp_apps_key_exists)) {
            echo "<option value='".$apps_item->app_list_id."'>".$apps_item->app_name."</option>"; 
        }
    }
    echo "</select></div></td>";
    echo "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>";
    echo "<td width='50'><input type='submit' name='update_add_apps' value='Add'></td></form></tr>";
    echo "</table></div>";
    echo "<form method='post' action='index.php?mode=campaigns_edit'>".
         "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>".
         "<div style='width: 600; text-align: right; padding-top: 10;'>".
         "<input type='submit' name='edit' value='Done'>".
         "</div>".
         "</form>";
}
echo "</div>";

