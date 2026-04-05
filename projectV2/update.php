<?php

include 'components/connect.php';

$user_id = validate_user_cookie($conn);
if(!$user_id){
   header('location:login.php');
   exit();
}

$select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
$select_user->execute([$user_id]);
$fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);

   if(!empty($name)){
      $update_name = $conn->prepare("UPDATE `users` SET name = ? WHERE id = ?");
      $update_name->execute([$name, $user_id]);
      $success_msg[] = 'name updated!';
   }

   if(!empty($email)){
      $verify_email = $conn->prepare("SELECT email FROM `users` WHERE email = ?");
      $verify_email->execute([$email]);
      if($verify_email->rowCount() > 0){
         $warning_msg[] = 'email already taken!';
      }else{
         $update_email = $conn->prepare("UPDATE `users` SET email = ? WHERE id = ?");
         $update_email->execute([$email, $user_id]);
         $success_msg[] = 'email updated!';
      }
   }

   if(!empty($number)){
      $verify_number = $conn->prepare("SELECT number FROM `users` WHERE number = ?");
      $verify_number->execute([$number]);
      if($verify_number->rowCount() > 0){
         $warning_msg[] = 'number already taken!';
      }else{
         $update_number = $conn->prepare("UPDATE `users` SET number = ? WHERE id = ?");
         $update_number->execute([$number, $user_id]);
         $success_msg[] = 'number updated!';
      }
   }

   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
   $prev_pass = $fetch_user['password'];
   $old_pass = sha1($_POST['old_pass']);
   $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
   $new_pass = sha1($_POST['new_pass']);
   $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   $c_pass = sha1($_POST['c_pass']);
   $c_pass = filter_var($c_pass, FILTER_SANITIZE_STRING);

   if($old_pass != $empty_pass){
      if($old_pass != $prev_pass){
         $warning_msg[] = 'old password not matched!';
      }elseif($new_pass != $c_pass){
         $warning_msg[] = 'confirm password not matched!';
      }else{
         if($new_pass != $empty_pass){
            $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
            $update_pass->execute([$c_pass, $user_id]);
            $success_msg[] = 'password updated successfully!';
         }else{
            $warning_msg[] = 'please enter new password!';
         }
      }
   }

   // Re-fetch updated user data
   $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
   $select_user->execute([$user_id]);
   $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);
}

$user_name    = $fetch_user['name'] ?? 'User';
$user_initial = strtoupper(substr($user_name, 0, 1));
$saved_count  = $conn->prepare("SELECT COUNT(*) as cnt FROM `saved` WHERE user_id = ?");
$saved_count->execute([$user_id]);
$saved_count  = $saved_count->fetch(PDO::FETCH_ASSOC)['cnt'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile — MyEstate</title>
<meta name="description" content="Update your MyEstate profile, email, phone number and password.">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<style>
:root{
  --r:#d62828;--rd:#9e1c1c;
  --rp:#fdf1f1;--rp2:#fae6e6;--rp3:#f5d0d0;
  --ink:#1a0505;--ink3:#9a6565;--ink2:#4a1515;
  --white:#ffffff;--bg:#faf5f5;
  --line:rgba(214,40,40,0.12);
  --ease:cubic-bezier(.22,1,.36,1);
  --nav-h:7rem;
}
*{margin:0;padding:0;box-sizing:border-box;}
html{font-size:62.5%;scroll-behavior:smooth;}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--ink);min-height:100vh;}
::-webkit-scrollbar{width:3px;}::-webkit-scrollbar-thumb{background:var(--r);}

/* NAV */
.nav{position:fixed;top:0;left:0;right:0;z-index:1000;height:var(--nav-h);padding:0 6%;display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#fff0f0,#fde0e0);border-bottom:1px solid var(--line);box-shadow:0 2px 20px rgba(214,40,40,0.08);transition:box-shadow .3s;}
.nav.scrolled{box-shadow:0 4px 40px rgba(214,40,40,.12);}
.nav-logo{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);text-decoration:none;}
.nav-logo span{font-style:italic;color:var(--r);}
.nav-links{display:flex;align-items:center;gap:3rem;}
.nav-links a{font-size:1.4rem;font-weight:600;color:var(--ink);text-decoration:none;transition:color 0.2s;padding:0.5rem 0;position:relative;}
.nav-links a::after{content:'';position:absolute;bottom:0;left:0;width:0;height:2px;background:var(--r);transition:width 0.25s;border-radius:99px;}
.nav-links a:hover{color:var(--r);}
.nav-links a:hover::after{width:100%;}
.nav-right{display:flex;align-items:center;gap:1.4rem;}
.nav-icon{width:4.2rem;height:4.2rem;border-radius:50%;border:1.5px solid var(--line);background:var(--white);display:grid;place-items:center;font-size:1.5rem;color:var(--ink3);text-decoration:none;transition:all .22s;position:relative;}
.nav-icon:hover{border-color:var(--r);color:var(--r);background:var(--rp);}
.nav-badge{position:absolute;top:-.3rem;right:-.3rem;width:1.6rem;height:1.6rem;border-radius:50%;background:var(--r);color:#fff;font-size:.75rem;font-weight:800;display:grid;place-items:center;border:2px solid var(--bg);}
.nav-user{display:flex;align-items:center;gap:1rem;padding:.7rem 1.6rem .7rem .7rem;border:1.5px solid var(--line);border-radius:99px;background:var(--white);cursor:pointer;transition:all .22s;position:relative;}
.nav-user:hover{border-color:var(--r);background:var(--rp);}
.nav-av{width:3.4rem;height:3.4rem;border-radius:50%;background:linear-gradient(135deg,var(--r),var(--rd));display:grid;place-items:center;font-size:1.4rem;font-weight:800;color:#fff;flex-shrink:0;}
.nav-drop-menu{display:none;position:absolute;top:calc(100% + 1rem);right:0;background:var(--white);border-radius:1.6rem;border:1.5px solid var(--line);box-shadow:0 20px 60px rgba(214,40,40,.15);padding:.8rem;min-width:20rem;z-index:100;}
.nav-drop-menu.open{display:block;}
.nd-item{display:flex;align-items:center;gap:1rem;padding:1.1rem 1.4rem;border-radius:1rem;font-size:1.3rem;color:var(--ink2);text-decoration:none;transition:all .18s;}
.nd-item i{width:2rem;text-align:center;color:var(--ink3);font-size:1.2rem;}
.nd-item:hover{background:var(--rp);color:var(--r);}
.nd-item:hover i{color:var(--r);}
.nd-sep{height:1px;background:var(--line);margin:.5rem 0;}
.nd-danger{color:#c0392b!important;}.nd-danger i{color:#c0392b!important;}.nd-danger:hover{background:#fff5f5!important;}
.nav-ham{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:4px;}
.nav-ham span{display:block;width:24px;height:2px;background:var(--r);border-radius:99px;}
.nav-mobile{display:none;position:fixed;inset:0;background:linear-gradient(135deg,#fff0f0,#fde0e0);z-index:999;flex-direction:column;align-items:center;justify-content:center;gap:2.5rem;}
.nav-mobile.open{display:flex;}
.nav-mobile a{font-size:2.4rem;font-weight:700;color:var(--ink);text-decoration:none;font-family:'Cormorant Garamond',serif;}
.nav-mobile a:hover{color:var(--r);}
.nav-mobile-close{position:absolute;top:2rem;right:2.5rem;font-size:2.4rem;cursor:pointer;color:var(--r);}

/* PAGE */
.form-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:calc(var(--nav-h) + 4rem) 2rem 8rem;background:linear-gradient(135deg,#fff8f8 0%,#fdf1f1 40%,#fae6e6 100%);position:relative;overflow:hidden;}
.form-page::before{content:'';position:absolute;top:-10%;right:-5%;width:55rem;height:55rem;border-radius:50%;background:radial-gradient(circle,rgba(214,40,40,0.07) 0%,transparent 70%);pointer-events:none;}
.form-page::after{content:'';position:absolute;bottom:-10%;left:-5%;width:45rem;height:45rem;border-radius:50%;background:radial-gradient(circle,rgba(214,40,40,0.05) 0%,transparent 70%);pointer-events:none;}
.form-card{background:var(--white);border-radius:2.8rem;padding:4.5rem;max-width:52rem;width:100%;box-shadow:0 24px 80px rgba(214,40,40,0.13);border:1.5px solid var(--line);position:relative;z-index:2;animation:popUp 0.5s var(--ease) both;}
@keyframes popUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
.form-icon-wrap{width:7rem;height:7rem;border-radius:50%;background:linear-gradient(135deg,var(--r),var(--rd));display:grid;place-items:center;font-size:2.8rem;margin:0 auto 2.4rem;box-shadow:0 8px 24px rgba(214,40,40,0.28);}
.form-icon-wrap i{color:#fff;}
.form-title{font-family:'Cormorant Garamond',serif;font-size:3.6rem;font-weight:700;color:var(--ink);text-align:center;margin-bottom:0.6rem;line-height:1.1;}
.form-title em{font-style:italic;color:var(--r);}
.form-sub{font-size:1.4rem;color:var(--ink3);text-align:center;margin-bottom:1.6rem;line-height:1.6;}
.user-info-strip{display:flex;align-items:center;gap:1.4rem;background:var(--rp);border:1px solid var(--line);border-radius:1.4rem;padding:1.4rem 1.8rem;margin-bottom:2.8rem;}
.ui-avatar{width:4.4rem;height:4.4rem;border-radius:50%;background:linear-gradient(135deg,var(--r),var(--rd));display:grid;place-items:center;font-size:1.8rem;font-weight:800;color:#fff;flex-shrink:0;}
.ui-name{font-size:1.4rem;font-weight:700;color:var(--ink);}
.ui-email{font-size:1.2rem;color:var(--ink3);}
.section-label{font-size:1rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--ink3);margin-bottom:1.2rem;margin-top:2rem;display:flex;align-items:center;gap:.6rem;}
.section-label::before{content:'';flex:1;height:1px;background:var(--line);}
.section-label::after{content:'';flex:1;height:1px;background:var(--line);}
.form-group{position:relative;margin-bottom:1.4rem;}
.form-group .fi{position:absolute;left:1.6rem;top:50%;transform:translateY(-50%);color:var(--ink3);font-size:1.35rem;pointer-events:none;transition:color 0.2s;z-index:2;}
.form-group:focus-within .fi{color:var(--r);}
.eye-btn{position:absolute;right:1.8rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--ink3);font-size:1.4rem;transition:color 0.2s;z-index:2;padding:0;}
.eye-btn:hover{color:var(--r);}
.form-input{width:100%;padding:1.4rem 4.4rem 1.4rem 4.4rem;border:1.5px solid var(--line);border-radius:99px;font-size:1.4rem;font-family:'Outfit',sans-serif;color:var(--ink);background:var(--rp);outline:none;transition:all 0.25s;-moz-appearance:textfield;}
.form-input::-webkit-outer-spin-button,.form-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0;}
.form-input:focus{border-color:rgba(214,40,40,0.4);background:var(--white);box-shadow:0 0 0 4px rgba(214,40,40,0.07);}
.form-input.no-right-pad{padding-right:1.6rem;}
.form-input::placeholder{color:var(--ink3);}
.btn-submit{width:100%;padding:1.5rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;font-size:1.5rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 8px 24px rgba(214,40,40,0.28);transition:all 0.25s;display:flex;align-items:center;justify-content:center;gap:1rem;margin-top:1.6rem;}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(214,40,40,0.38);}
.form-back-row{font-size:1.3rem;color:var(--ink3);text-align:center;margin-top:1.8rem;}
.form-back-row a{color:var(--r);font-weight:700;text-decoration:none;}
.form-back-row a:hover{text-decoration:underline;}

/* FOOTER */
.footer{background:linear-gradient(135deg,#fff0f0,#fde0e0);border-top:1px solid var(--line);padding:5rem 6% 3rem;}
.foot-top{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:4rem;padding-bottom:3.5rem;border-bottom:1px solid var(--line);}
.foot-logo{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);margin-bottom:1rem;display:block;text-decoration:none;}
.foot-logo span{font-style:italic;color:var(--r);}
.foot-brand p{font-size:1.3rem;color:var(--ink3);line-height:1.7;margin-bottom:2rem;max-width:26rem;}
.foot-socials{display:flex;gap:1rem;}
.foot-soc{width:3.6rem;height:3.6rem;border-radius:50%;border:1.5px solid var(--line);background:var(--white);display:grid;place-items:center;color:var(--ink3);font-size:1.3rem;text-decoration:none;transition:all 0.2s;}
.foot-soc:hover{border-color:var(--r);color:var(--r);background:var(--rp);transform:translateY(-3px);}
.foot-col h4{font-size:1.1rem;font-weight:700;color:var(--ink);letter-spacing:0.12em;text-transform:uppercase;margin-bottom:1.6rem;}
.foot-col a{display:flex;align-items:center;gap:0.6rem;font-size:1.25rem;color:var(--ink3);text-decoration:none;margin-bottom:0.9rem;transition:all 0.2s;}
.foot-col a i{font-size:1rem;color:rgba(214,40,40,0.3);}
.foot-col a:hover{color:var(--r);padding-left:0.4rem;}
.foot-col a:hover i{color:var(--r);}
.foot-contact-item{display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.2rem;}
.foot-contact-ic{width:3.2rem;height:3.2rem;border-radius:0.8rem;background:var(--white);border:1px solid var(--line);display:grid;place-items:center;color:var(--r);font-size:1.2rem;flex-shrink:0;}
.foot-contact-txt{font-size:1.25rem;color:var(--ink3);line-height:1.5;}
.foot-contact-txt strong{display:block;font-size:1.1rem;font-weight:700;color:var(--ink);margin-bottom:0.2rem;}
.foot-bottom{display:flex;align-items:center;justify-content:space-between;padding-top:2.4rem;flex-wrap:wrap;gap:1rem;}
.foot-copy{font-size:1.2rem;color:var(--ink3);}
.foot-copy span{color:var(--r);font-weight:700;}
.foot-links{display:flex;gap:2rem;}
.foot-links a{font-size:1.2rem;color:var(--ink3);text-decoration:none;transition:color 0.2s;}
.foot-links a:hover{color:var(--r);}
@media(max-width:1100px){.foot-top{grid-template-columns:1fr 1fr;gap:3rem;}}
@media(max-width:768px){
  .nav-links,.nav-right{display:none;}
  .nav-ham{display:flex;}
  .foot-top{grid-template-columns:1fr;}
  .form-card{padding:3rem 2.4rem;}
}
</style>
</head>
<body>

<!-- MOBILE NAV -->
<div class="nav-mobile" id="mobileNav">
  <span class="nav-mobile-close" onclick="document.getElementById('mobileNav').classList.remove('open')"><i class="fas fa-times"></i></span>
  <a href="home.php" onclick="document.getElementById('mobileNav').classList.remove('open')">Home</a>
  <a href="listings.php" onclick="document.getElementById('mobileNav').classList.remove('open')">Properties</a>
  <a href="about.php" onclick="document.getElementById('mobileNav').classList.remove('open')">About</a>
  <a href="contact.php" onclick="document.getElementById('mobileNav').classList.remove('open')">Contact</a>
</div>

<!-- NAV -->
<nav class="nav" id="mainNav">
  <a href="home.php" class="nav-logo">My<span>Estate</span></a>
  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="listings.php">Properties</a>
    <a href="upcoming.php">Upcoming</a>
    <a href="about.php">About</a>
    <a href="contact.php">Contact</a>
  </div>
  <div class="nav-right">
    <a href="saved.php" class="nav-icon"><i class="fas fa-heart"></i><?php if($saved_count > 0): ?><span class="nav-badge"><?= $saved_count ?></span><?php endif; ?></a>
    <div class="nav-user" id="navUser">
      <div class="nav-av"><?= $user_initial ?></div>
      <span style="font-size:1.3rem;font-weight:700;color:var(--ink);"><?= htmlspecialchars($user_name) ?></span>
      <i class="fas fa-chevron-down" style="font-size:1rem;color:var(--ink3);margin-left:.4rem;"></i>
      <div class="nav-drop-menu" id="navDropUpdate">
        <a href="saved.php" class="nd-item"><i class="fas fa-heart"></i>Saved Properties</a>
        <a href="requests.php" class="nd-item"><i class="fas fa-file-alt"></i>My Requests</a>
        <div class="nd-sep"></div>
        <a href="home.php#agentSec" class="nd-item" style="color:var(--r);font-weight:700;"><i class="fas fa-user-tie" style="color:var(--r);"></i>Become an Agent</a>
        <div class="nd-sep"></div>
        <a href="update.php" class="nd-item" style="color:var(--r);"><i class="fas fa-user-edit"></i>Edit Profile</a>
        <div class="nd-sep"></div>
        <a href="javascript:void(0)" onclick="confirmLogout()" class="nd-item nd-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
      </div>
    </div>
  </div>
  <div class="nav-ham" id="ham" onclick="document.getElementById('mobileNav').classList.toggle('open')">
    <span></span><span></span><span></span>
  </div>
</nav>

<!-- FORM PAGE -->
<div class="form-page">
  <div class="form-card">
    <div class="form-icon-wrap"><i class="fas fa-user-edit"></i></div>
    <div class="form-title">Edit <em>Profile</em></div>
    <p class="form-sub">Update your account information below</p>

    <!-- Current user info strip -->
    <div class="user-info-strip">
      <div class="ui-avatar"><?= $user_initial ?></div>
      <div>
        <div class="ui-name"><?= htmlspecialchars($fetch_user['name'] ?? '') ?></div>
        <div class="ui-email"><?= htmlspecialchars($fetch_user['email'] ?? '') ?></div>
      </div>
    </div>

    <form action="" method="post" id="updateForm">

      <div class="section-label">Profile Info</div>

      <div class="form-group">
        <i class="fas fa-user fi"></i>
        <input type="text" name="name" maxlength="50" placeholder="<?= htmlspecialchars($fetch_user['name'] ?? 'Enter your name') ?>" class="form-input">
      </div>
      <div class="form-group">
        <i class="fas fa-envelope fi"></i>
        <input type="email" name="email" maxlength="50" placeholder="<?= htmlspecialchars($fetch_user['email'] ?? 'Enter your email') ?>" class="form-input">
      </div>
      <div class="form-group">
        <i class="fas fa-phone fi"></i>
        <input type="number" name="number" min="0" max="9999999999" maxlength="10" placeholder="<?= htmlspecialchars($fetch_user['number'] ?? 'Enter your number') ?>" class="form-input no-right-pad">
      </div>

      <div class="section-label">Change Password</div>

      <div class="form-group">
        <i class="fas fa-lock fi"></i>
        <input type="password" name="old_pass" id="oldPassInput" maxlength="20" placeholder="Enter your current password" class="form-input">
        <button type="button" class="eye-btn" onclick="toggleEye('oldPassInput','eyeOld')"><i class="fas fa-eye" id="eyeOld"></i></button>
      </div>
      <div class="form-group">
        <i class="fas fa-key fi"></i>
        <input type="password" name="new_pass" id="newPassInput" maxlength="20" placeholder="Enter your new password" class="form-input">
        <button type="button" class="eye-btn" onclick="toggleEye('newPassInput','eyeNew')"><i class="fas fa-eye" id="eyeNew"></i></button>
      </div>
      <div class="form-group">
        <i class="fas fa-check-circle fi"></i>
        <input type="password" name="c_pass" id="cPassInput" maxlength="20" placeholder="Confirm your new password" class="form-input">
        <button type="button" class="eye-btn" onclick="toggleEye('cPassInput','eyeConf')"><i class="fas fa-eye" id="eyeConf"></i></button>
      </div>

      <button type="submit" name="submit" class="btn-submit"><i class="fas fa-save"></i> Save Changes</button>
    </form>

    <p class="form-back-row"><a href="home.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></p>
  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <div class="foot-top">
    <div class="foot-brand">
      <a href="home.php" class="foot-logo">My<span>Estate</span></a>
      <p>Your trusted partner for premium real estate across Mumbai and Pune.</p>
      <div class="foot-socials">
        <a href="https://instagram.com" target="_blank" class="foot-soc"><i class="fab fa-instagram"></i></a>
        <a href="https://facebook.com" target="_blank" class="foot-soc"><i class="fab fa-facebook-f"></i></a>
        <a href="https://twitter.com" target="_blank" class="foot-soc"><i class="fab fa-twitter"></i></a>
        <a href="https://youtube.com" target="_blank" class="foot-soc"><i class="fab fa-youtube"></i></a>
      </div>
    </div>
    <div class="foot-col">
      <h4>Properties</h4>
      <a href="listings.php"><i class="fas fa-chevron-right"></i>All Listings</a>
      <a href="listings.php?type=apartment"><i class="fas fa-chevron-right"></i>Apartments</a>
      <a href="listings.php?type=villa"><i class="fas fa-chevron-right"></i>Villas</a>
      <a href="upcoming.php"><i class="fas fa-chevron-right"></i>Upcoming</a>
    </div>
    <div class="foot-col">
      <h4>Quick Links</h4>
      <a href="home.php"><i class="fas fa-chevron-right"></i>Dashboard</a>
      <a href="saved.php"><i class="fas fa-chevron-right"></i>Saved</a>
      <a href="requests.php"><i class="fas fa-chevron-right"></i>My Requests</a>
      <a href="about.php"><i class="fas fa-chevron-right"></i>About Us</a>
    </div>
    <div class="foot-col">
      <h4>Contact Us</h4>
      <div class="foot-contact-item"><div class="foot-contact-ic"><i class="fas fa-map-marker-alt"></i></div><div class="foot-contact-txt"><strong>Office</strong>Nalasopara West, Maharashtra — 401203</div></div>
      <div class="foot-contact-item"><div class="foot-contact-ic"><i class="fas fa-phone-alt"></i></div><div class="foot-contact-txt"><strong>Phone</strong>+91 98765 43210</div></div>
      <div class="foot-contact-item"><div class="foot-contact-ic"><i class="fas fa-envelope"></i></div><div class="foot-contact-txt"><strong>Email</strong>rayyanbhagate@gmail.com</div></div>
    </div>
  </div>
  <div class="foot-bottom">
    <div class="foot-copy">© <?= date('Y') ?> <span>MyEstate</span>. All rights reserved.</div>
    <div class="foot-links"><a href="#">Privacy Policy</a><a href="#">Terms of Use</a></div>
  </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/message.php'; ?>
<script>
// Nav scroll
window.addEventListener('scroll',()=>document.getElementById('mainNav').classList.toggle('scrolled',scrollY>40));

// Nav dropdown
const navUserEl = document.getElementById('navUser');
const navDropEl = document.getElementById('navDropUpdate');
if(navUserEl && navDropEl){
  navUserEl.addEventListener('click', e => { e.stopPropagation(); navDropEl.classList.toggle('open'); });
  navDropEl.addEventListener('click', e => { e.stopPropagation(); });
  document.addEventListener('click', () => navDropEl.classList.remove('open'));
  window.addEventListener('scroll', () => navDropEl.classList.remove('open'), {passive:true});
}

// Toggle password visibility
function toggleEye(inputId, iconId){
  const inp = document.getElementById(inputId);
  const ico = document.getElementById(iconId);
  if(inp.type === 'password'){
    inp.type = 'text';
    ico.classList.replace('fa-eye','fa-eye-slash');
  } else {
    inp.type = 'password';
    ico.classList.replace('fa-eye-slash','fa-eye');
  }
}

// Logout confirmation
function confirmLogout(){
  swal({title:'Logout?',text:'Are you sure you want to logout?',icon:'warning',
    buttons:['Cancel','Logout'],dangerMode:true
  }).then(ok=>{ if(ok) window.location='components/user_logout.php'; });
}
</script>
</body>
</html>