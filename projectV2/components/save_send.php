<?php
if(session_status() === PHP_SESSION_NONE){
   @session_start();
}

if(isset($_POST['save'])){
   if($user_id != ''){

      $save_id = create_unique_id();
      $property_id = $_POST['property_id'];
      $property_id = filter_var($property_id, FILTER_SANITIZE_STRING);

      $verify_saved = $conn->prepare("SELECT * FROM `saved` WHERE property_id = ? and user_id = ?");
      $verify_saved->execute([$property_id, $user_id]);

      if($verify_saved->rowCount() > 0){
         $remove_saved = $conn->prepare("DELETE FROM `saved` WHERE property_id = ? AND user_id = ?");
         $remove_saved->execute([$property_id, $user_id]);
         $_SESSION['swal_success'][] = 'Removed from saved!';
      }else{
         $insert_saved = $conn->prepare("INSERT INTO`saved`(id, property_id, user_id) VALUES(?,?,?)");
         $insert_saved->execute([$save_id, $property_id, $user_id]);
         $_SESSION['swal_success'][] = 'Property saved successfully!';
      }

   }else{
      $_SESSION['swal_warning'][] = 'Please login first!';
   }
   $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
   if(!empty($_SERVER['QUERY_STRING'])){
      $redirect_url .= '?' . $_SERVER['QUERY_STRING'];
   }
   header("Location: " . $redirect_url);
   exit();
}

if(isset($_POST['send'])){
   if($user_id != ''){

      $request_id = create_unique_id();
      $property_id = $_POST['property_id'];
      $property_id = filter_var($property_id, FILTER_SANITIZE_STRING);

      $select_receiver = $conn->prepare("SELECT user_id FROM `property` WHERE id = ? LIMIT 1");
      $select_receiver->execute([$property_id]);
      $fetch_receiver = $select_receiver->fetch(PDO::FETCH_ASSOC);
      $receiver = $fetch_receiver['user_id'];

      $verify_request = $conn->prepare("SELECT * FROM `requests` WHERE property_id = ? AND sender = ? AND receiver = ?");
      $verify_request->execute([$property_id, $user_id, $receiver]);

      if(($verify_request->rowCount() > 0)){
         $_SESSION['swal_warning'][] = 'Request sent already!';
      }else{
         $send_request = $conn->prepare("INSERT INTO `requests`(id, property_id, sender, receiver) VALUES(?,?,?,?)");
         $send_request->execute([$request_id, $property_id, $user_id, $receiver]);
         $_SESSION['swal_success'][] = 'Enquiry sent successfully!';
      }

   }else{
      $_SESSION['swal_warning'][] = 'Please login first!';
   }
   $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
   if(!empty($_SERVER['QUERY_STRING'])){
      $redirect_url .= '?' . $_SERVER['QUERY_STRING'];
   }
   header("Location: " . $redirect_url);
   exit();
}

?>
