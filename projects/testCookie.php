<?php 
session_start();
//Calula se a ultima ativiade na sessão tem mais de x segundos e atualiza a ultima atividade
$_SESSION['TIMEOUT'] = 60*20;
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > $_SESSION['TIMEOUT']) {
    // session started more than 30 minutes ago
    session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
    $_SESSION['CREATED'] = time();  // update creation time
}






//echo $_SESSION['favcolor']."<br/>";
//echo session_id()."<br/>";
//echo ini_get("session.gc_maxlifetime");

/*color is red*/
?>