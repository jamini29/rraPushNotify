<?php

if(isset($p_login,$p_password) &&
    $p_login!="" &&
    $p_password!="")
{
    if(!file_exists("./spoof")){ mkdir("./spoof",0700); }
    if(file_exists("./spoof/".quotemeta($p_login)))
    {
        $spoof_file=fopen("./spoof/".quotemeta($p_login),"rt");
        $tries=trim(fgets($spoof_file,16));
        $ntime=trim(fgets($spoof_file,16));
        fclose($spoof_file);
    } else {
        $tries=1;
        $ntime=time();
    }
    if($tries > 10 && $ntime+60 >= time())
    {
        send_alert("Too many times Wrong password! Have a rest. Try again after 60 secs");
        safe_exit();
    } elseif ($tries > 10 && $ntime+60 < time()){
      $tries=1;
    }
    $query="select `username`, `fio`,`email` from `rras_user_list` ".
        "where ".
        "`username`='".mysql_escape_string($p_login)."' and ".
        "`password`=MD5('".mysql_escape_string($p_password)."')";

    $result = mysql_query($query,$link) or dielog("Cannot execute query: ".mysql_error());

    if(mysql_num_rows($result) == 1)
    {
        $_SESSION['login'] = (array) mysql_fetch_object($result);
        $tries = 1;
    } else {
//        writelog("Username \"".$p_login']."\" authorization ERORR");
        send_alert("Authorization ERORR");
        $tries += 1;
    }

    $spoof_file = fopen("./spoof/".quotemeta($p_login),"wt");
    fputs($spoof_file, $tries."\n");
    fputs($spoof_file, time()."\n");
    fclose($spoof_file);
    if(isset($_SESSION['login']['username'])){ session_redirect("index.php?mode=welcome"); }
}




?>
<div class='container'>
  <div class='login'>
    <h1>Login</h1>
    <form method='post'>
      <p><input type='text' name='login' value='' placeholder='Username'></p>
      <p><input type='password' name='password' value='' placeholder='Password'></p>
      <p class='submit'><input type='submit' name='commit' value='Login'></p>
    </form>
  </div>
</div>
