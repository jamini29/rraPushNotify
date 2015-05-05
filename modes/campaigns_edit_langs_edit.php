<?php
//if($DEBUG or 1) {
//    echo "<pre>";
//    print_r($_POST);
//    echo "</pre>";
//}
$apps=get_list('app_list');
$langs=get_list('language_list');
// add part
if(isset($p_push_campaign_list_id,
         $p_new_lang_list_id,
         $p_push_message_new,
         $p_update_edit_add_langs) and
         $p_new_lang_list_id!=''
         ) {
    $camp_langs=get_list_where('push_campaign_list_languages','push_campaign_list_id',$p_push_campaign_list_id);
    $camp_langs_key_exists=array();
    foreach($camp_langs as &$camp_langs_item) { array_push($camp_langs_key_exists, $camp_langs_item->language_list_id);  }
    $errors = array();
    if ($p_new_lang_list_id==0) { array_push($errors, "Select language"); }
    if (!strlen($p_push_message_new)) { array_push($errors, "'Push message' is empty"); }
    if (count($errors) == 0) {
        if(!in_array($p_new_lang_list_id,$camp_langs_key_exists)) {
            $query="insert into `push_campaign_list_languages` set ".
                   "`push_campaign_list_id`='".$p_push_campaign_list_id."', ".
                   "`language_list_id`='".$p_new_lang_list_id."', ".
                   "`push_message`='".htmlspecialchars($p_push_message_new,ENT_QUOTES)."'";
            mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
            unset($p_push_message_new);
        }
        update_camp_affected($p_push_campaign_list_id);
        $_SESSION['redirect']['push_campaign_list_id']=$p_push_campaign_list_id;
        header("Location: index.php?mode=campaigns_edit_langs");
    } else { jalert($errors); }
} elseif(isset($p_push_campaign_list_languages_id,
               $p_push_message,
               $p_update_edit_update_langs)) {
    $errors = array();
    if (!strlen($p_push_message)) { array_push($errors, "Push message is empty"); }
    if (count($errors) == 0) {
        $query="update `push_campaign_list_languages` ".
               "set `push_message`='".htmlspecialchars($p_push_message,ENT_QUOTES)."' ".
               "where `push_campaign_list_languages_id`='".$p_push_campaign_list_languages_id."'";
        mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
        $_SESSION['redirect']['push_campaign_list_id']=$p_push_campaign_list_id;
        header("Location: index.php?mode=campaigns_edit_langs");
    } else { jalert($errors); }
} elseif(isset($p_push_campaign_list_languages_id,
               $p_update_edit_delete_langs)) {
    $query="delete from `push_campaign_list_languages` ".
           "where `push_campaign_list_languages_id`='".$p_push_campaign_list_languages_id."'";
    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    update_camp_affected($p_push_campaign_list_id);
    $_SESSION['redirect']['push_campaign_list_id']=$p_push_campaign_list_id;
    header("Location: index.php?mode=campaigns_edit_langs");
}
echo "<div class='container'>";
if(
    isset($p_update_update_langs, $p_push_campaign_list_id, $p_push_campaign_list_languages_id)
    or
    isset($p_update_edit_update_langs, $p_push_campaign_list_id, $p_push_campaign_list_languages_id)
    or
    isset($p_update_add_langs, $p_push_campaign_list_id)
    or
    isset($p_update_edit_add_langs, $p_push_campaign_list_id)
) {
    echo "<div class='jcommont' style='width: 800'><table>";
    echo "<thead><tr>";
    echo "<th>Language</th>";
    echo "<th>Push message</th>";
    echo "<th colspan=2>&nbsp;</th>";
    echo "</tr></thead>";
    $camp_langs=get_list_where('push_campaign_list_languages','push_campaign_list_id',$p_push_campaign_list_id);

    // apps in campaign already
    $camp_langs_key_exists=array();
    foreach($camp_langs as &$camp_langs_item) { array_push($camp_langs_key_exists, $camp_langs_item->language_list_id);  }
    if(
        isset($p_update_update_langs)
        or
        isset($p_update_edit_update_langs)
    ) {
        echo "<tr>";
        echo "<form method='post'>";
        echo "<td>".$langs[$camp_langs[$p_push_campaign_list_languages_id]->language_list_id]->language_name."</td>";
        echo "<td><input width='36em' type='text' name='push_message' value='";
        if(isset($p_push_message)) { echo $p_push_message; }
        else { echo $camp_langs[$p_push_campaign_list_languages_id]->push_message; }
        echo "'></td>";
        echo "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>";
        echo "<input type='hidden' name='push_campaign_list_languages_id' value='".
             $p_push_campaign_list_languages_id."'>";
        echo "<td width='50'><input type='submit' name='update_edit_update_langs' value='Update'></td>";
        echo "<td width='50'><input type='submit' name='update_edit_delete_langs' value='Delete'></td>";
        echo "</form>";
        echo "</tr>";
    } elseif (
            isset($p_update_add_langs)
            or
            isset($p_update_edit_add_langs)
    ) {
        echo "<tr>";
        echo "<form method='post'>";
        echo "<td><div class='styled'><select name='new_lang_list_id'>";
        echo "<option value='0'>- Select Language -</option>";
        foreach($langs as &$langs_item) {
            if(!in_array($langs_item->language_list_id,$camp_langs_key_exists)) {
                echo "<option value='".$langs_item->language_list_id."'>".$langs_item->language_name."</option>"; 
            }
        }
        echo "</select></div></td>";
        echo "<td><input type='text' name='push_message_new' value='";
        if(isset($p_push_message_new)) { echo $p_push_message_new; }
        echo "'></td>";
        echo "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>";
        echo "<td><input type='submit' name='update_edit_add_langs' value='Add'></td></form></tr>";
    }
    echo "</table></div>";

    echo "<form method='post' action='index.php?mode=campaigns_edit_langs'>".
         "<input type='hidden' name='push_campaign_list_id' value='".$p_push_campaign_list_id."'>".
         "<div style='width: 600; text-align: right; padding-top: 10;'>".
         "<input type='submit' name='edit_langs' value='Cancel'>".
         "</div>".
         "</form>";
}
echo "</div>";
