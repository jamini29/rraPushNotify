<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of functions
 *
 * @author andrew_minich
 */

function dielog ($message)
{
    global $link;
    syslog_error("$message");
    safe_exit();
}

function syslog_error ($message)
{
//    define_syslog_variables();
    openlog($_SERVER['SCRIPT_NAME'], LOG_PID | LOG_PERROR, LOG_INFO);
    syslog(LOG_WARNING,$message);
    closelog();
}

function safe_exit ()
{
    global $link, $result;
    if(isset($result)) {
        mysql_free_result($result);
    }
    if(isset($link)){
        mysql_close($link);
    }
    exit;
}

function session_redirect ($url = "")
{
    function _safe_set (&$var_true, $var_false = "")
    {
        if(!isset($var_true)){$var_true=$var_false;}
    }
    $parse_url=parse_url ($url);
    _safe_set($parse_url["scheme"],"http");
    _safe_set($parse_url["host"],$_SERVER['HTTP_HOST']);
    _safe_set($parse_url["path"],"");
    _safe_set($parse_url["query"],"");
    _safe_set($parse_url["fragment"],"");
    if(substr($parse_url["path"],0,1)!="/")
    {
        $parse_url["path"]=dirname($_SERVER['PHP_SELF'])."/".$parse_url["path"];
    }
    if($parse_url["query"]!="")
    {
        $parse_url["query"]=$parse_url["query"]."&";
    }
    $parse_url["query"]="?".$parse_url["query"].session_name()."=".strip_tags(session_id());
    if($parse_url["fragment"]!="")
    {
        $parse_url["fragment"]="#".$parse_url["fragment"];
    }
    $url=$parse_url["scheme"]."://".$parse_url["host"].$parse_url["path"].$parse_url["query"].$parse_url["fragment"];
    session_write_close();
    header("Location: ".$url);
    safe_exit();
}

function jalert($message_arr)
{
    echo "<div id='dialog-message' title='Alert' width='400' style='color:#cd0a0a'>";
    foreach($message_arr as &$message_arr_item) {
        echo "<p>".$message_arr_item."</p>";
    }
    echo "</div>";
    //    $message=join("\n",$message_arr);
//    send_alert($message_arr[0]);
}

function camp_langs_sort($camp_langs) {
    $old_key=0;
    foreach($camp_langs as $key => $item) {
        if($item->language_list_id == 1) {
            $old_key = $key;
        }
    }
    if($old_key) {
        array_push($camp_langs,$camp_langs[$old_key]);
        unset($camp_langs[$old_key]);
    }
    return $camp_langs;
}

function set_stack($fi,$se) {
    if(isset($fi)) { return $fi; }
    elseif (isset($se)) { return $se; }
    else { return ''; }
}

if(!isset($auth)){ session_redirect("index.php"); }

?>
