<?php

$apps=get_list('app_list');
$langs=get_list('language_list');
$UTC = new DateTimeZone("UTC");
// add part
if(isset($p_update_add,
         $p_campaign_name,
         $p_campaign_goal,
         $p_planned_ts
        )) {
    $errors = array();
    if (!strlen($p_campaign_name)) { array_push($errors, "Name is empty"); }
    if (!strlen($p_planned_ts)) { array_push($errors, "Date Time is not set"); }
    if (count($errors) == 0) {
           $query="insert into `push_campaign_list` set ".
              "`campaign_name`='".htmlspecialchars($p_campaign_name,ENT_QUOTES)."', ".
              "`campaign_goal`='".htmlspecialchars($p_campaign_goal,ENT_QUOTES)."', ".
              "`planned_ts`=unix_timestamp('".$p_planned_ts."') ";
       mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
       $p_push_campaign_list_id=mysql_insert_id($link);
       $query="insert into `push_campaign_list_languages` set ".
              "`push_campaign_list_id`='".$p_push_campaign_list_id."', ".
              "`language_list_id`='1'";
       mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    } else {
        jalert($errors);
    }
//    header("Location: index.php?mode=lang");
} elseif(isset($p_copy,
               $p_push_campaign_list_id_orig)) {
    $query="insert into `push_campaign_list` (`campaign_name`, `campaign_goal`) ".
           "select concat(`campaign_name`, ' (copy)'), `campaign_goal` from `push_campaign_list` ".
           "where `push_campaign_list_id`='".$p_push_campaign_list_id_orig."'";
    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    $p_push_campaign_list_id=mysql_insert_id($link);
    $query="insert into `push_campaign_list_app` (`push_campaign_list_id`, `app_list_id`) ".
           "select '".$p_push_campaign_list_id."', `app_list_id` from `push_campaign_list_app` ".
           "where `push_campaign_list_id`='".$p_push_campaign_list_id_orig."'";
    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    $query="insert into `push_campaign_list_conditions` (`push_campaign_list_id`, `have`, `app_list_id`) ".
           "select '".$p_push_campaign_list_id."', `have`, `app_list_id` from `push_campaign_list_conditions` ".
           "where `push_campaign_list_id`='".$p_push_campaign_list_id_orig."'";
    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    $query="insert into `push_campaign_list_languages` (`push_campaign_list_id`, `language_list_id`, `push_message`) ".
           "select '".$p_push_campaign_list_id."', `language_list_id`, `push_message` from `push_campaign_list_languages` ".
           "where `push_campaign_list_id`='".$p_push_campaign_list_id_orig."'";
    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    update_camp_affected($p_push_campaign_list_id);
// EDIT part
} elseif(isset($p_push_campaign_list_id,
               $p_campaign_name,
               $p_campaign_goal,
//               $p_planned_ts,
               $p_update_edit)
               ) {
    $errors = array();
    if (!strlen($p_campaign_name)) { array_push($errors, "Name is empty"); }
//    if (!strlen($p_planned_ts)) { array_push($errors, "Date Time is not set"); }
    if (count($errors) == 0) {
        $query="update `push_campaign_list` set ".
               "`campaign_name`='".htmlspecialchars($p_campaign_name,ENT_QUOTES)."', ".
               "`campaign_goal`='".htmlspecialchars($p_campaign_goal,ENT_QUOTES)."' ".
    //           "`planned_ts`=unix_timestamp(convert_tz('".$p_planned_ts']."',@@global.time_zone,'+00:00')) ".
//               "`planned_ts`=unix_timestamp('".$p_planned_ts."') ".
               "where `push_campaign_list_id`='".$p_push_campaign_list_id."'";
        mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    } else {
        jalert($errors);
    }
} elseif(isset($p_push_campaign_list_id,
               $p_planned_ts,
               $p_update_edit_ts)
               ) {
    $errors = array();
    if (!strlen($p_planned_ts)) { array_push($errors, "Date Time is not set"); }
    if (count($errors) == 0) {
        $query="update `push_campaign_list` set ".
    //           "`planned_ts`=unix_timestamp(convert_tz('".$p_planned_ts']."',@@global.time_zone,'+00:00')) ".
               "`planned_ts`=unix_timestamp('".$p_planned_ts."') ".
               "where `push_campaign_list_id`='".$p_push_campaign_list_id."'";
        mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    } else {
        jalert($errors);
    }

//push_campaign_switch_status
} elseif(isset($p_push_campaign_list_id,
               $p_push_campaign_switch_status,
               $p_update_edit)) {
    $errors = array();
    if($p_push_campaign_switch_status==='on') {
        $campaign=get_list_item_by_id('push_campaign_list',$p_push_campaign_list_id);
        $camp_apps=get_list_where('push_campaign_list_app','push_campaign_list_id',$p_push_campaign_list_id);
//        $camp_conds=get_list_where('push_campaign_list_conditions','push_campaign_list_id',$p_push_campaign_list_id);
        $camp_langs=get_list_where('push_campaign_list_languages','push_campaign_list_id',$p_push_campaign_list_id);
        if(count($camp_apps)==0) { array_push($errors, "No 'Where Apps' selected"); }
        if(count($camp_langs)==0) { array_push($errors, "No 'Languages' selected"); }
        foreach($camp_langs as &$camp_langs_item) {
            if($camp_langs_item->push_message === '') {
                array_push($errors, "Language '".$langs[$camp_langs_item->language_list_id]->language_name."' has empty Push message");
            }
        }

        $date_now = new DateTime('now', $UTC);
//        if($date_now->getTimestamp() >= $campaign->planned_ts) { array_push($errors, "'Date Time' in past"); }
        
        $query="update `push_campaign_list` set ".
               "`campaign_status`='sheduled' ".
               "where `push_campaign_list_id`='".$p_push_campaign_list_id."' ".
               "and `campaign_status`='off'";
    } elseif($p_push_campaign_switch_status==='off') {
        $query="update `push_campaign_list` set ".
               "`campaign_status`='off' ".
               "where `push_campaign_list_id`='".$p_push_campaign_list_id."' ".
               "and `campaign_status`='sheduled'";
    }

    if (count($errors) == 0) {
        mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    } else {
        jalert($errors);
    }
} elseif(isset($p_push_campaign_list_id,
               $p_update_delete)) {
    $query="update `push_campaign_list` set `deleted_ts`=1 where `push_campaign_list_id`='".$p_push_campaign_list_id."'";
    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    header("Location: index.php?mode=campaigns");
}

echo "<div class='container'>";
if( isset($p_push_campaign_list_id) and
    (   isset($p_copy) or
        isset($p_edit) or
        isset($p_update_edit) or
        isset($p_update_edit_ts) or
        isset($p_update_add)
    )
) {
    $campaign=get_list_item_by_id('push_campaign_list',$p_push_campaign_list_id);
    $camp_apps=get_list_where('push_campaign_list_app','push_campaign_list_id',$p_push_campaign_list_id);
    $camp_conds=get_list_where('push_campaign_list_conditions','push_campaign_list_id',$p_push_campaign_list_id);
    $camp_langs=camp_langs_sort(get_list_where('push_campaign_list_languages','push_campaign_list_id',$p_push_campaign_list_id));

    echo "<div class='jcommont' style='width: 800'>";
    echo "<h1>Planned Reach: ".$campaign->planned_reach."</h1>";
    echo "<table>";
    echo "<thead><tr>";
    echo "<th colspan=4>Campaign edit</th>";
    echo "</tr></thead>";
    echo "<form method='post'>".
         "<input type='hidden' name='push_campaign_list_id' value='".$campaign->push_campaign_list_id."'>";
    echo "<tr><td>Name</td>".
         "<td><input type='text' name='campaign_name' value='";
    if(isset($p_campaign_name)) { echo $p_campaign_name; } else { echo $campaign->campaign_name; } echo "'></td>".
         "<td rowspan=2 width='50'>".
         "<input type='submit' name='update_edit' value='Update'>".
         "</td>";
    
    echo "<td rowspan=7 width='50'>".
         "<input type='submit' name='update_delete' value='Delete'>".
         "</td>".
         "</tr>";
    echo "<tr><td>Goal</td>".
         "<td><input type='text' name='campaign_goal' value='";
        if(isset($p_campaign_goal)) { echo $p_campaign_goal; } else { echo $campaign->campaign_goal; } 
        echo "'></td>";
    echo "</tr>";
    echo "</form>";
    
    
    
    $date = new DateTime();
    $date->setTimestamp($campaign->planned_ts);
    echo "<form method='post' id='dt_form_id'>".
         "<input type='hidden' name='push_campaign_list_id' value='".$campaign->push_campaign_list_id."'>".
         "<input type='hidden' name='update_edit_ts' value='update_edit_ts'>";
    echo "<tr><td>Date Time (UTC)</td>".
         "<td colspan=2><input id='select_dt_from_now' type='text' name='planned_ts' ";
    if($campaign->planned_ts) { echo "value='".$date->format('Y-m-d H:i')."'"; }
    echo "></td>".
         "</tr>";
    echo "</form>";
    echo "<form method='post'>".
         "<input type='hidden' name='push_campaign_list_id' value='".$campaign->push_campaign_list_id."'>"; 
    echo "<tr><td>Status</td>".
         "<td><p class='attention'>".$campaign->campaign_status."</p></td>".
         "<td>";
    if($campaign->campaign_status==='off') {
        echo "<input type='submit' name='update_edit' value='On'>".
             "<input type='hidden' name='push_campaign_switch_status' value='on'>";
    } elseif($campaign->campaign_status==='sheduled') {
        echo "<input type='submit' name='update_edit' value='Off'>".
             "<input type='hidden' name='push_campaign_switch_status' value='off'>";
    }
    echo "</td".
         "</tr>";
    echo "</form>";

    echo "<form method='post' action='index.php?mode=campaigns_edit_apps'>".
         "<input type='hidden' name='push_campaign_list_id' value='".$campaign->push_campaign_list_id."'>";
    echo "<tr><td>Where Apps</td>".
         "<td><ul>";
    foreach($camp_apps as &$camp_apps_item) {
        echo "<li>".$apps[$camp_apps_item->app_list_id]->app_name;
    }
    echo "</ul></td>";
    echo "<td><input type='submit' name='edit_apps' value='Edit'></td>".
         "</tr>".
         "</form>";

    echo "<form method='post' action='index.php?mode=campaigns_edit_langs'>".
         "<input type='hidden' name='push_campaign_list_id' value='".$campaign->push_campaign_list_id."'>";
    echo "<tr><td>Languages</td>".
         "<td><ul>";
    foreach($camp_langs as &$camp_langs_item) {
        echo "<li><div style='width: 30px; display: inline-block; white-space: nowrap; text-align: right; font-weight: bold; padding-right: 4px;'>".$langs[$camp_langs_item->language_list_id]->language_code."</div>";
        echo "<divstyle='display: inline-block; white-space: nowrap; text-align: left;'>&#8211;&nbsp;".$camp_langs_item->push_message."</div>";
    }
    echo "</ul></td>";
    echo "<td><input type='submit' name='edit_langs' value='Edit'></td>".
         "</tr>".
         "</form>";

    echo "<form method='post' action='index.php?mode=campaigns_edit_conds'>".
         "<input type='hidden' name='push_campaign_list_id' value='".$campaign->push_campaign_list_id."'>";
    echo "<tr><td>Conditions</td>".
         "<td><ul class='cond'>";
    foreach($camp_conds as &$camp_conds_item) {
        if($camp_conds_item->have) { echo "<li class='have'>"; } else { echo "<li class='nhave'>"; }
        echo $apps[$camp_conds_item->app_list_id]->app_name;
    }
    echo "</ul></td>";
    echo "<td><input type='submit' name='edit_conds' value='Edit'></td>".
         "</tr>".
         "</form>";
    
    echo "</table></div>";
} elseif(isset($p_add) or
         isset($p_update_add)) {
    echo "<div class='jcommont' style='width: 600'><table>";
    echo "<thead><tr>";
    echo "<th colspan=3>Campaign add</th>";
    echo "</tr></thead>";
    echo "<form method='post'>";
    echo "<tr><td>Name</td>".
         "<td><input type='text' name='campaign_name' value='";
    if(isset($p_campaign_name)) { echo $p_campaign_name; } echo "' placeholder='campaign_name'></td>".
         "<td rowspan=3 width='50'>".
         "<input type='submit' name='update_add' value='Add'>".
         "</td>";
//    echo "<td rowspan=3 width='50'>".
//         "<input type='submit' name='update_delete' value='Delete'>".
//         "</td>".
    echo "</tr>";
    echo "<tr><td>Goal</td>".
         "<td><input type='text' name='campaign_goal' value='";
    if(isset($p_campaign_goal)) { echo $p_campaign_goal; } 
    echo "' placeholder='campaign_goal'></td>";
    echo "</tr>";
    
    $date = new DateTime();
    $date->setTimestamp(mktime(0, 0, 0, date('n'), date('j') + 1));
    echo "<tr><td>Date Time (UTC)</td>".
         "<td><input id='select_dt_from_now' type='text' name='planned_ts' value='";
        if(isset($p_planned_ts)) { echo $p_planned_ts; }
        else { echo $date->format('Y-m-d H:i'); }
    echo "'></td>";
    echo "</tr>";
    echo "</form>";
    echo "</table></div>";
}
echo "<form method='post' action='index.php?mode=campaigns'>".
     "<div style='width: 600; text-align: right; padding-top: 10;'>".
     "<input type='submit' name='edit' value='Done'>".
     "</div>".
     "</form>";
//echo "<pre>!!!\n";
//print_r(affected_devices_list($p_push_campaign_list_id));
//$r=affected_devices_list($p_push_campaign_list_id,$apps,$langs);
//echo $r['apns_count'];
//echo "</pre>";
echo "</div>";


