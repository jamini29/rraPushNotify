<?php
//if($DEBUG or 1) {
//    echo "<pre>";
//    print_r($_POST);
//    print_r($_SESSION);
//    echo "</pre>";
//}

if(isset($_SESSION['redirect'], $_SESSION['redirect']['push_campaign_list_id']))
{
    $p_edit_langs='redirect';
    $p_push_campaign_list_id=$_SESSION['redirect']['push_campaign_list_id'];
    unset($_SESSION['redirect']);
}

$langs=get_list('language_list');
echo "<div class='container'>";
update_camp_affected($p_push_campaign_list_id);
if(isset($p_push_campaign_list_id, $p_edit_langs))
{
    $campaign=get_list_item_by_id('push_campaign_list',$p_push_campaign_list_id);
    echo "<div class='jcommont' style='width: 600'>";
    echo "<h1>Planned Reach: ".$campaign->planned_reach."</h1>";
    echo "<table>";
    echo "<thead><tr>";
    echo "<th>Language</th>";
    echo "<th>Push message</th>";
    echo "<th>&nbsp;</th>";
    echo "</tr></thead>";
    $camp_langs=camp_langs_sort(get_list_where('push_campaign_list_languages','push_campaign_list_id',$p_push_campaign_list_id));
    
    foreach($camp_langs as &$camp_langs_item) {
        echo "<tr>";
        echo "<form method='post' action='index.php?mode=campaigns_edit_langs_edit'>";
        echo "<td>".$langs[$camp_langs_item->language_list_id]->language_name."</td>";
        echo "<td>".$camp_langs_item->push_message."</td>";
        echo "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>";
        echo "<input type='hidden' name='push_campaign_list_languages_id' value='".
             $camp_langs_item->push_campaign_list_languages_id."'>";
        echo "<td width='50'><input type='submit' name='update_update_langs' value='Edit'></td>";
//        if($langs[$camp_langs_item->language_list_id]->language_list_id != 1) {
//            echo "<td width='50'><input type='submit' name='update_delete_langs' value='Delete'></td>";
//        } else { echo "<td width='50'>&nbsp;</td>"; }
        echo "</form>";
        echo "</tr>";
    }
    echo "<tr><form method='post' action='index.php?mode=campaigns_edit_langs_edit'>";
    echo "<td colspan=2>&nbsp;</td>";
    echo "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>";
    echo "<td><input type='submit' name='update_add_langs' value='Add'></td></form></tr>";
    echo "</table></div>";
    echo "<form method='post' action='index.php?mode=campaigns_edit'>".
         "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>".
         "<div style='width: 600; text-align: right; padding-top: 10;'>".
         "<input type='submit' name='edit' value='Done'>".
         "</div>".
         "</form>";
}
echo "</div>";


?>
