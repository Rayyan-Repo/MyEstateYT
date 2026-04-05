<?php

   if(session_status() === PHP_SESSION_NONE){
      session_start();
   }

   $db_name = 'mysql:host=localhost;dbname=home_db';
   $db_user_name = 'root';
   $db_user_pass = '';

   $conn = new PDO($db_name, $db_user_name, $db_user_pass);

   function create_unique_id(){
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < 20; $i++) {
          $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
      }
      return $randomString;
  }

   function validate_user_cookie($conn){
      $uid = false;

      if(isset($_SESSION['user_id']) && $_SESSION['user_id'] !== ''){
         $uid = $_SESSION['user_id'];
      } elseif(isset($_COOKIE['user_id']) && $_COOKIE['user_id'] !== ''){
         $uid = $_COOKIE['user_id'];
      }

      if(!$uid){
         return false;
      }

      $chk = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
      $chk->execute([$uid]);
      if($chk->rowCount() === 0){
         unset($_SESSION['user_id']);
         setcookie('user_id', '', time() - 3600, '/');
         return false;
      }

      $_SESSION['user_id'] = $uid;
      return $uid;
   }

   function validate_admin_cookie($conn){
      $aid = false;

      if(isset($_SESSION['admin_id']) && $_SESSION['admin_id'] !== ''){
         $aid = $_SESSION['admin_id'];
      } elseif(isset($_COOKIE['admin_id']) && $_COOKIE['admin_id'] !== ''){
         $aid = $_COOKIE['admin_id'];
      }

      if(!$aid){
         return false;
      }

      $chk = $conn->prepare("SELECT id FROM admins WHERE id = ? LIMIT 1");
      $chk->execute([$aid]);
      if($chk->rowCount() === 0){
         unset($_SESSION['admin_id']);
         setcookie('admin_id', '', time() - 3600, '/');
         return false;
      }

      $_SESSION['admin_id'] = $aid;
      return $aid;
   }

?>
