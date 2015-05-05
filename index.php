<?php

define("ROOTDIR", dirname(__FILE__));
chdir(ROOTDIR);
$ini=parse_ini_file("main.ini", true);
$DEBUG=$ini['global']['debug'];
if($DEBUG) {
    error_reporting(E_ALL ^ E_DEPRECATED);
    ini_set('display_errors', '1');
}

date_default_timezone_set('UTC');
header('Cache-Control: no-store, no-cache, must-revalidate');
$timeout=60;
ob_start();
setlocale(LC_ALL, 'ru_RU.UTF-8');
session_name('gamma');
session_start();
$auth=1;
include "functions.php";
include "push_functions.php";
$link = mysql_connect($ini['database']['hostname'], $ini['database']['username'], $ini['database']['password'], 0, 131072)
  or dielog("Cannot connect to MySQL: ".mysql_error());
mysql_select_db($ini['database']['database']) or dielog("Cannot select database '".$ini['database']['database']."'");
mysql_query("set names utf8;", $link);
mysql_query("set time_zone = '+00:00';", $link);

if(!isset($_REQUEST['mode'])){
  $mode='login';
}
if(!isset($_SESSION['login'])){
  $mode="login";
}elseif(isset($_REQUEST['mode'])){
  $mode=$_REQUEST['mode'];
}

if(isset($_SESSION['time'])){
  if($_SESSION['time']+60*$timeout < time()){
    $mode="logout";
  } else {
    $_SESSION['time']=time();
  }
}

if(isset($_SESSION['ip'],$_SESSION['agent']) &&
  ($_SESSION['ip']!=my_quote($_SERVER['REMOTE_ADDR']) ||
  $_SESSION['agent']!=my_quote($_SERVER['HTTP_USER_AGENT']))){
  $mode="logout";
  writelog("Ошибка сессии");
}

//  import_request_variables('p', 'p_'); // depricated (
foreach ($_POST as $key => $val) { $v = 'p_'.$key; $$v = $val; }
foreach ($_GET as $key => $val) { $v = 'g_'.$key; $$v = $val; }

$tITLE="Top";

echo <<< 'EO_START'
    <html>
    <head>
    <meta charset='utf-8'>
    <link href='css/main.css' rel='stylesheet'>
    <link href='css/form.css' rel='stylesheet'>
    <link href='css/table.css' rel='stylesheet'>
    <script src="js/jquery-1.11.2.min.js"></script>
    <script src="js/jquery-ui/jquery-ui.min.js"></script>
    <link rel=stylesheet type=text/css href="js/jquery-ui/jquery-ui.min.css" />
    <link rel=stylesheet type=text/css href="js/jquery-ui/jquery-ui.structure.min.css" />
    <link rel=stylesheet type=text/css href="js/jquery-ui/jquery-ui.theme.min.css" />

    <script src="js/global.js" type="text/javascript"></script>
    <script src="js/jquery-ui-timepicker-addon.min.js"></script>
    <link rel=stylesheet type=text/css href="js/jquery-ui-timepicker-addon.min.css" />

    <script src="js/global.js" type="text/javascript"></script>

    <script src="js/sweetalert/sweet-alert.min.js"></script>
    <link rel="stylesheet" type="text/css" href="js/sweetalert/sweet-alert.css">
    <script>
      $(function() { $( "#select_dt_from_now").datetimepicker({
                        firstDay: 1,
                        dateFormat: 'yy-mm-dd',
                        minDate: '-1d',
                        showMinute: 1,
                        closeText: 'Set',
                        controlType: 'slider',
                        hourGrid: 0,
                        currentText: 'Now',
                        onClose : function(){
                           $('#dt_form_id').submit();   
                        }
                    }); });
        $(function() {
          $( "#dialog-message" ).dialog({
            modal: true,
            width: 600,
            buttons: {
              Ok: function() {
                $( this ).dialog( "close" );
              }
            },
          });
        });

    </script>

    <style>
    #basicModal{
        display:none;
    }
    </style>

      <style>
         .ui-menu {
            width: 200px;
         }
      </style>
      <script>
         $(function() {
            $( "#menu-settings" ).menu({
                icons: { submenu: "ui-icon-circle-triangle-e"},    
                
            });
         });
      </script>



    </head>
    <body>
EO_START;
//new Date() //minDate

echo <<< 'EO_HEADER_TOP'
<div id="head"><div class="pad1">&nbsp;</div>
<div style="display:block; height: 50;">
<div class="logo_image"></div></div>
EO_HEADER_TOP;
if(isset($_SESSION['login'])) {
    echo <<< "EO_LOGGED"
        <div style="display:block; height: 40; border: none;">
        <div class='jmenut'>
        <table>
        <thead>
        <th width='20'></th>
        <th width='80'><a href='index.php?mode=campaigns'><input type='submit' value='Manage Campaigns'></a></td>
        <th></th>
        <th width='80'><a href='index.php?mode=apps'><input type='submit' value='Applications'></a></th>
        <th width='80'><a href='index.php?mode=lang'><input type='submit' value='Languages'></a></th>
        <th width='80'></th>
        <th width='80'><a href='index.php?mode=logout'><input type='submit' value='Logout'></a></th>
        </thead>
        </table>
        </div>
        </div>
EO_LOGGED;
}
echo <<< 'EO_HEADER_BOT'
    </div>
EO_HEADER_BOT;
echo "<div id='content'>";
echo "<div class='pad_top'></div>";
include "./modes/".$mode.".php";
echo "<div class='pad_bottom'></div>";
echo "</div>";
echo "<div id='foot'>".$tITLE."</div>";


echo <<< 'EO_FINISH'
    </body>
    </html>
EO_FINISH;

ob_end_flush();
safe_exit();
