<?php

$tITLE="Manage campaigns";
$campaigns=get_list('push_campaign_list');
$apps=get_list('app_list');
$campaigns_apps=get_list('push_campaign_list_app');
$campaigns_conditions=get_list('push_campaign_list_conditions');
$campaigns_languages=get_list('push_campaign_list_languages');
$camp_grouped=array();
foreach($campaigns as &$campaign) { $camp_grouped[$campaign->push_campaign_list_id]=array(); }
foreach($campaigns_apps as &$ca_item) {
    if(!array_key_exists($ca_item->push_campaign_list_id,$camp_grouped)) {
        $camp_grouped[$ca_item->push_campaign_list_id]=array();
    }
    array_push($camp_grouped[$ca_item->push_campaign_list_id],$ca_item->app_list_id);
}

echo "<div class='container'>";
echo "<div class='jcommont'><table>";
echo "<thead><tr>";
echo "<th>Name</th>";
echo "<th>Where</th>";
echo "<th>Goal</th>";
echo "<th width='80'>Status</th>";
echo "<th width='80'>Planned<br>Reach</th>";
echo "<th width='80'>Actual<br>Reach</th>";
echo "<th colspan=2></th>";
echo "</tr></thead>";
echo "<tbody>";
foreach ($campaigns as &$campaign)
{
    if($campaign->deleted_ts) { continue; }
    echo "<tr";
    if($campaign->campaign_status==='completed') { echo "  bgcolor='#f5fbef'"; }
    if($campaign->campaign_status==='sheduled') { echo "  bgcolor='#fbeff2'"; }
    echo ">";
    echo "<td>".$campaign->campaign_name."</td>";
    echo "<td><ul>";
    foreach($camp_grouped[$campaign->push_campaign_list_id] as &$app_item) {
        echo "<li>".$apps[$app_item]->app_name;
    }
    echo "</ul></td>";
    echo "<td>".$campaign->campaign_goal."</td>";
    echo "<td><p class='attention'>".$campaign->campaign_status."</p></td>";
    echo "<td>".$campaign->planned_reach."</td>";
    echo "<td>".$campaign->actual_reach."</td>";
    echo "<form action='index.php?mode=campaigns_edit' method='post'>".
         "<input type='hidden' name='push_campaign_list_id' value='".$campaign->push_campaign_list_id."'>".
         "<td width='50'>";
    if($campaign->campaign_status!='completed') { echo "<input type='submit' name='edit' value='Edit'>"; }
    else { echo "<input type='submit' name='edit' value='View'>"; }
    echo "</td>".
         "</form>";
    echo "<form action='index.php?mode=campaigns_edit' method='post'>".
         "<input type='hidden' name='push_campaign_list_id_orig' value='".$campaign->push_campaign_list_id."'>".
         "<td width='50'><input type='submit' name='copy' value='Copy'></td></form>";
    echo "</tr>";
}
echo "<tr style='background-color: E1EEF4;'><td colspan='6'>&nbsp;</td><form action='index.php?mode=campaigns_edit' method='post'><td colspan=2><input type='submit' name='add' value='Add'></td></form></tr>";
echo "</tbody>";
echo "</table></div>";
echo "</div>";


