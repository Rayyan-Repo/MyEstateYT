<?php

include 'connect.php';

if(session_status() === PHP_SESSION_NONE){
   session_start();
}
session_unset();
session_destroy();
setcookie('admin_id', '', time() - 3600, '/');
setcookie('PHPSESSID', '', time() - 3600, '/');

header('location:../admin/login.php');
exit();

?>