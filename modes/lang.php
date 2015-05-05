<?php

$langs=get_list('language_list');

echo "<div class='container'>";
echo "<div class='jcommont' style='width: 600'><table>";
echo "<thead><tr>";
echo "<th width=50px>Lang<br>Code</th>";
echo "<th>Language</th>";
//echo "<th>Lang localised</th>";
echo "<th width=50px>&nbsp;</th>";
echo "</tr></thead>";
echo "<tbody>";

foreach ($langs as &$lang)
{
    if($lang->language_list_id==1) { continue; }
    echo "<tr>";
    echo "<td>".$lang->language_code."</td>";
    echo "<td>".$lang->language_name."</td>";
//    echo "<td>".$lang->language_name_localized."</td>";
    echo "<form action='index.php?mode=lang_edit' method='post'><input type='hidden' name='language_list_id' value='".$lang->language_list_id."'><td><input type='submit' name='edit' value='Edit'></td></form>";
    echo "</tr>";
}
echo "<tr style='background-color: E1EEF4;'><td colspan='2'>&nbsp;</td><form action='index.php?mode=lang_edit' method='post'><td><input type='submit' name='add' value='Add'></td></form></tr>";
echo "</tbody>";
echo "</table></div>";
echo "</div>";
?>

