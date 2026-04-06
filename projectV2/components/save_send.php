<?php
if(session_status() === PHP_SESSION_NONE){
   @session_start();
}

if(isset($_POST['save'])){
   if($user_id != ''){

      $save_id = create_unique_id();
      $property_id = $_POST['property_id'];
      $property_id = filter_var($property_id, FILTER_SANITIZE_STRING);

      $verify_saved = $conn->prepare("SELECT * FROM `saved` WHERE property_id = ? AND user_id = ?");
      $verify_saved->execute([$property_id, $user_id]);

      if($verify_saved->rowCount() > 0){
         $remove_saved = $conn->prepare("DELETE FROM `saved` WHERE property_id = ? AND user_id = ?");
         $remove_saved->execute([$property_id, $user_id]);
         $_SESSION['swal_success'][] = 'Removed from saved!';
      }else{
         $insert_saved = $conn->prepare("INSERT INTO `saved`(id, property_id, user_id) VALUES(?,?,?)");
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

      $message_id  = create_unique_id();
      $property_id = $_POST['property_id'];
      $property_id = filter_var($property_id, FILTER_SANITIZE_STRING);

      // Get sender details
      $sel_sender = $conn->prepare("SELECT name, email, number FROM `users` WHERE id = ? LIMIT 1");
      $sel_sender->execute([$user_id]);
      $sender_data = $sel_sender->fetch(PDO::FETCH_ASSOC);

      $sender_name   = $sender_data['name']   ?? '';
      $sender_email  = $sender_data['email']  ?? '';
      $sender_number = $sender_data['number'] ?? '';

      // If form fields override (from view_property enquiry form)
      if(!empty($_POST['name']))    $sender_name   = filter_var($_POST['name'],    FILTER_SANITIZE_STRING);
      if(!empty($_POST['number']))  $sender_number = filter_var($_POST['number'],  FILTER_SANITIZE_STRING);

      $message_text = !empty($_POST['message'])
         ? filter_var($_POST['message'], FILTER_SANITIZE_STRING)
         : 'I am interested in this property. Please contact me.';

      // Check if already sent inquiry for this property
      $verify_msg = $conn->prepare("SELECT * FROM `messages` WHERE property_id = ? AND user_id = ?");
      $verify_msg->execute([$property_id, $user_id]);

      if($verify_msg->rowCount() > 0){
         $_SESSION['swal_warning'][] = 'Inquiry already sent for this property!';
      }else{
         $insert_msg = $conn->prepare("INSERT INTO `messages`(id, user_id, property_id, name, email, number, message, responded) VALUES(?,?,?,?,?,?,?,0)");
         $insert_msg->execute([
            $message_id,
            $user_id,
            $property_id,
            $sender_name,
            $sender_email,
            $sender_number,
            $message_text
         ]);
         $_SESSION['swal_success'][] = 'Inquiry sent successfully!';
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
