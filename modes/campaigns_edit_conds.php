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
         $p_update_add_conds,
         $p_app_list_have) and
         $p_new_app_list_id!='' and
         $p_new_app_list_id!=0 and
         ($p_app_list_have==0 or
         $p_app_list_have==1)) {
    $camp_conds=get_list_where('push_campaign_list_conditions','push_campaign_list_id',$p_push_campaign_list_id);
    $camp_conds_key_exists=array();
    foreach($camp_conds as &$camp_conds_item) { array_push($camp_conds_key_exists, $camp_conds_item->app_list_id);  }
    if(!in_array($p_new_app_list_id,$camp_conds_key_exists)) {
        $query="insert into `push_campaign_list_conditions` set ".
               "`push_campaign_list_id`='".$p_push_campaign_list_id."', ".
               "`have`='".$p_app_list_have."', ".
               "`app_list_id`='".$p_new_app_list_id."'";
        mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
        update_camp_affected($p_push_campaign_list_id);
    }
} elseif(isset($p_push_campaign_list_cond_id,
               $p_update_delete_conds)) {
    $query="delete from `push_campaign_list_conditions` ".
           "where `push_campaign_list_conditions_id`='".$p_push_campaign_list_cond_id."'";
    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    update_camp_affected($p_push_campaign_list_id);
}

echo "<div class='container'>";
if(isset($p_push_campaign_list_id) and
   (isset($p_edit_conds) or isset($p_update_delete_conds) or isset($p_update_add_conds))) {
    $campaign=get_list_item_by_id('push_campaign_list',$p_push_campaign_list_id);
    echo "<div class='jcommont' style='width: 800'>";
    echo "<h1>Planned Reach: ".$campaign->planned_reach."</h1>";
    echo "<table>";
    echo "<thead><tr>";
    echo "<th>Have<br>App</th>";
    echo "<th>Don't have<br>App</th>";
    echo "<th>&nbsp;</th>";
    echo "</tr></thead>";
    $camp_conds=get_list_where('push_campaign_list_conditions','push_campaign_list_id',$p_push_campaign_list_id);
    // conditionss in campaign already
    $camp_conds_yes_key_exists=array();
    $camp_conds_key_exists=array();
    foreach($camp_conds as &$camp_conds_item) {
        array_push($camp_conds_key_exists, $camp_conds_item->app_list_id);
    }
    foreach($camp_conds as &$camp_conds_item) {
        echo "<tr><form method='post'>";
        echo "<td>";
        if($camp_conds_item->have == 1) {
            echo $apps[$camp_conds_item->app_list_id]->app_name;
        }
        echo "</td>";
        echo "<td>";
        if($camp_conds_item->have == 0) {
            echo $apps[$camp_conds_item->app_list_id]->app_name;
        }
        echo "</td>";
        echo "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>";
        echo "<input type='hidden' name='push_campaign_list_cond_id' value='".
             $camp_conds_item->push_campaign_list_conditions_id."'>";
        echo "<td width='50'><input type='submit' name='update_delete_conds' value='Delete'></td>";
        echo "</form></tr>";
    }
    echo "<tr><form method='post'>";
    echo "<td><div class='styled'><select name='new_app_list_id'>";
    echo "<option value='0'>- Select App -</option>";
    foreach($apps as &$apps_item) {
        if(!in_array($apps_item->app_list_id,$camp_conds_key_exists)) {
            echo "<option value='".$apps_item->app_list_id."'>".$apps_item->app_name."</option>"; 
        }
    }
    echo "</select></div></td>";
    echo "<td></td>";
    echo "<input type='hidden' name='app_list_have' value='1'>";
    echo "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>";
    echo "<td width='50'><input type='submit' name='update_add_conds' value='Add'></td></form></tr>";

    echo "<tr><form method='post'>";
    echo "<td></td>";
    echo "<td><div class='styled'><select name='new_app_list_id'>";
    echo "<option value='0'>- Select App -</option>";
    foreach($apps as &$apps_item) {
        if(!in_array($apps_item->app_list_id,$camp_conds_key_exists)) {
            echo "<option value='".$apps_item->app_list_id."'>".$apps_item->app_name."</option>";
        }
    }
    echo "</select></div></td>";
    echo "<input type='hidden' name='app_list_have' value='0'>";
    echo "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>";
    echo "<td width='50'><input type='submit' name='update_add_conds' value='Add'></td></form></tr>";



    echo "</table></div>";
    echo "<form method='post' action='index.php?mode=campaigns_edit'>".
         "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>".
         "<div style='width: 600; text-align: right; padding-top: 10;'>".
         "<input type='submit' name='edit' value='Done'>".
         "</div>".
         "</form>";
}
echo "</div>";
