<?php

if($DEBUG) {
echo "<pre>";
print_r($_POST);
echo "</pre>";
}


// add part
if(isset($p_language_code,
         $p_language_name,
         $p_update_add)) {
    $errors = array();
    if (strlen($p_language_code)!=2) { array_push($errors, "language_code must be 2 letter code"); }
    if (!strlen($p_language_name)) { array_push($errors, "language_name empty"); }
    if (count($errors) == 0) {
        $query="insert into `language_list` set ".
               "  `language_code`='".htmlspecialchars($p_language_code,ENT_QUOTES)."' ".
               ", `language_name`='".htmlspecialchars($p_language_name,ENT_QUOTES)."' ";
        mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
        header("Location: index.php?mode=lang");
    } else {
        jalert($errors);
    }
} elseif(isset($p_language_list_id,
               $p_language_code,
               $p_language_name,
               $p_update_edit)) {
    $errors = array();
    if (strlen($p_language_code)!=2) { array_push($errors, "language_code must be 2 letter code"); }
    if (!strlen($p_language_name)) { array_push($errors, "language_name empty"); }
    if (count($errors) == 0) {
        $query="update `language_list` set ".
               "  `language_code`='".htmlspecialchars($p_language_code,ENT_QUOTES)."' ".
               ", `language_name`='".htmlspecialchars($p_language_name,ENT_QUOTES)."' ".
               "where `language_list_id`='".$p_language_list_id."'";
        mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
        header("Location: index.php?mode=lang");
    } else {
        jalert($errors);
    }
} elseif(isset($p_language_list_id, $p_update_delete)) {
    $query="delete from `language_list` where `language_list_id`='".$p_language_list_id."'";
    mysql_query($query,$link) or dielog("Cannot execure query: ".mysql_error());
    header("Location: index.php?mode=lang");
}

echo "<div class='container'>";
echo "<div class='jcommont' style='width: 600'><table>";
echo "<thead><tr>";
echo "<th width=50px>Lang<br>Code 1</th>";
echo "<th>Lang En</th>";
//echo "<th>Lang localised</th>";
echo "<th colspan=2>&nbsp;</th>";
echo "</tr></thead>";
if(isset($p_language_list_id, $p_edit)) {
    $lang=get_list_item_by_id('language_list',$p_language_list_id);
    echo "<tr><form method='post'>";
    echo "<td><input type='text' name='language_code' value='".$lang->language_code."'></td>";
    echo "<td><input type='text' name='language_name' value='".$lang->language_name."'></td>";
//    echo "<td><input type='text' name='language_name_localized' value='".$lang->language_name_localized."'></td>";
    echo "<input type='hidden' name='language_list_id' value='".$lang->language_list_id."'>";
    echo "<td width='50'><input type='submit' name='update_edit' value='Update'></td>".
         "<td width='50'><input type='submit' name='update_delete' value='Delete'></td>";
    echo "</form></tr>";
} elseif(isset($p_add) or isset($p_update_add)) {
    echo "<tr><form method='post'>";
    echo "<td><input id='language_code' type='text' name='language_code' value='' placeholder='es'></td>";
    echo "<td><input type='text' name='language_name' value='' placeholder='Spanish'></td>";
//    echo "<td><input type='text' name='language_name_localized' value='' placeholder='español'></td>";
    echo "<td colspan=2><input id='submit' type='submit' name='update_add' value='Add'></td></form></tr>";
}
echo "</table></div>";
echo "</div>";
?>

