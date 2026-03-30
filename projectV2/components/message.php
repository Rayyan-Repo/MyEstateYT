<style>
.me-swal-popup{border-radius:2rem!important;border:1.5px solid rgba(214,40,40,.13)!important;box-shadow:0 24px 80px rgba(214,40,40,.2)!important;font-family:'Outfit',sans-serif!important;padding:2rem 3rem!important;max-width:40rem!important;}
.me-swal-title{font-family:'Cormorant Garamond',serif!important;font-size:2.2rem!important;font-weight:700!important;color:#1a0505!important;margin:0!important;}
.me-swal-icon{border-color:rgba(214,40,40,.15)!important;}
.swal2-timer-progress-bar{background:linear-gradient(135deg,#d62828,#9e1c1c)!important;height:3px!important;border-radius:99px!important;}
.me-swal-popup .swal2-success-ring{border-color:#d62828!important;}
.me-swal-popup .swal2-success-line-tip,.me-swal-popup .swal2-success-line-long{background-color:#d62828!important;}
</style>
<?php
// Session-based messages (from save_send.php POST/Redirect/GET)
if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['swal_success'])){
   foreach($_SESSION['swal_success'] as $msg){
      echo '<script>
      if(typeof swal !== "undefined"){
         swal({
            title: "'.$msg.'",
            icon: "success",
            buttons: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {popup: "me-swal-popup", title: "me-swal-title", icon: "me-swal-icon"},
            showConfirmButton: false
         });
      }
      </script>';
   }
   unset($_SESSION['swal_success']);
}

if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['swal_warning'])){
   foreach($_SESSION['swal_warning'] as $msg){
      echo '<script>
      if(typeof swal !== "undefined"){
         swal({
            title: "'.$msg.'",
            icon: "warning",
            buttons: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {popup: "me-swal-popup", title: "me-swal-title"},
            showConfirmButton: false
         });
      }
      </script>';
   }
   unset($_SESSION['swal_warning']);
}

if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['swal_info'])){
   foreach($_SESSION['swal_info'] as $msg){
      echo '<script>
      if(typeof swal !== "undefined"){
         swal({
            title: "'.$msg.'",
            icon: "info",
            buttons: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {popup: "me-swal-popup", title: "me-swal-title"},
            showConfirmButton: false
         });
      }
      </script>';
   }
   unset($_SESSION['swal_info']);
}

if(session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['swal_error'])){
   foreach($_SESSION['swal_error'] as $msg){
      echo '<script>
      if(typeof swal !== "undefined"){
         swal({
            title: "'.$msg.'",
            icon: "error",
            buttons: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {popup: "me-swal-popup", title: "me-swal-title"},
            showConfirmButton: false
         });
      }
      </script>';
   }
   unset($_SESSION['swal_error']);
}

// Legacy support: direct variable-based messages
if(isset($success_msg)){
   foreach($success_msg as $msg){
      echo '<script>
      if(typeof swal !== "undefined"){
         swal({
            title: "'.$msg.'",
            icon: "success",
            buttons: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {popup: "me-swal-popup", title: "me-swal-title", icon: "me-swal-icon"},
            showConfirmButton: false
         });
      }
      </script>';
   }
}

if(isset($warning_msg)){
   foreach($warning_msg as $msg){
      echo '<script>
      if(typeof swal !== "undefined"){
         swal({
            title: "'.$msg.'",
            icon: "warning",
            buttons: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {popup: "me-swal-popup", title: "me-swal-title"},
            showConfirmButton: false
         });
      }
      </script>';
   }
}

if(isset($info_msg)){
   foreach($info_msg as $msg){
      echo '<script>
      if(typeof swal !== "undefined"){
         swal({
            title: "'.$msg.'",
            icon: "info",
            buttons: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {popup: "me-swal-popup", title: "me-swal-title"},
            showConfirmButton: false
         });
      }
      </script>';
   }
}

if(isset($error_msg)){
   foreach($error_msg as $msg){
      echo '<script>
      if(typeof swal !== "undefined"){
         swal({
            title: "'.$msg.'",
            icon: "error",
            buttons: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {popup: "me-swal-popup", title: "me-swal-title"},
            showConfirmButton: false
         });
      }
      </script>';
   }
}
?>
