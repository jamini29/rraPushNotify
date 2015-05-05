<?php
if(!isset($auth))
{
    header("Location: index.php");
    safe_exit();
}
session_unset();
session_destroy();
header("Location: index.php");

?>
