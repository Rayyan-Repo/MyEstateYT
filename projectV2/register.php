<?php
session_start();
include 'components/connect.php';
include 'components/mailer.php';

if(isset($_COOKIE['user_id'])){
   header('location:home.php');
   exit();
}

// PASSWORD STRENGTH CHECK
function isStrongPassword($pass) {
   if(strlen($pass) < 8) return false;
   if(!preg_match('/[A-Z]/', $pass)) return false;
   if(!preg_match('/[a-z]/', $pass)) return false;
   if(!preg_match('/[0-9]/', $pass)) return false;
   if(!preg_match('/[\W_]/', $pass)) return false;
   return true;
}

if(isset($_POST['submit'])){
   $name   = trim(filter_var($_POST['name'],   FILTER_SANITIZE_STRING));
   $number = trim(filter_var($_POST['number'], FILTER_SANITIZE_STRING));
   $email  = trim(filter_var($_POST['email'],  FILTER_SANITIZE_EMAIL));
   $pass   = $_POST['pass'];
   $c_pass = $_POST['c_pass'];

   // Validate real email format
   if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $warning_msg[] = 'Please enter a valid email address!';
   } elseif(!isStrongPassword($pass)){
      $warning_msg[] = 'Password must be at least 8 characters with uppercase, lowercase, number & special character!';
   } elseif($pass !== $c_pass){
      $warning_msg[] = 'Passwords do not match!';
   } else {
      // Check if email exists
      $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
      $chk->execute([$email]);
      if($chk->rowCount() > 0){
         $warning_msg[] = 'Email already registered!';
      } else {
         // Generate OTP
         $otp        = generateOTP();
         $expires_at_query = $conn->query("SELECT DATE_ADD(NOW(), INTERVAL 5 MINUTE) as exp")->fetch();
         $expires_at = $expires_at_query['exp']; // 5 minutes from MySQL time

         // Delete old OTPs for this email
         $del = $conn->prepare("DELETE FROM otp_verification WHERE email = ?");
         $del->execute([$email]);

         // Insert new OTP
         $ins = $conn->prepare("INSERT INTO otp_verification(email, otp, expires_at) VALUES(?,?,?)");
         $ins->execute([$email, $otp, $expires_at]);

         // Send OTP email
         $sent = sendMail($email, $name, 'MyEstate - Email Verification OTP', getOTPEmailTemplate($otp, $name));

         if($sent){
            // Store registration data in session temporarily
            $_SESSION['reg_name']   = $name;
            $_SESSION['reg_number'] = $number;
            $_SESSION['reg_email']  = $email;
            $_SESSION['reg_pass']   = sha1($pass);
            header('location:verify_otp.php');
            exit();
         } else {
            $error_msg[] = 'Failed to send OTP. Please try again!';
         }
      }
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — MyEstate</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<style>
:root{
  --r:#d62828;--rd:#9e1c1c;
  --rp:#fdf1f1;--rp2:#fae6e6;--rp3:#f5d0d0;
  --ink:#1a0505;--ink3:#9a6565;
  --white:#ffffff;--bg:#faf5f5;
  --line:rgba(214,40,40,0.12);
  --ease:cubic-bezier(.22,1,.36,1);
}
*{margin:0;padding:0;box-sizing:border-box;}
html{font-size:62.5%;scroll-behavior:smooth;}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--ink);min-height:100vh;}
.nav{position:fixed;top:0;left:0;right:0;z-index:1000;padding:1.6rem 6%;display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#fff0f0,#fde0e0);border-bottom:1px solid var(--line);box-shadow:0 2px 20px rgba(214,40,40,0.08);}
.nav-logo{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);text-decoration:none;}
.nav-logo span{font-style:italic;color:var(--r);}
.nav-links{display:flex;align-items:center;gap:3rem;}
.nav-links a{font-size:1.4rem;font-weight:600;color:var(--ink);text-decoration:none;transition:color 0.2s;padding:0.5rem 0;position:relative;}
.nav-links a::after{content:'';position:absolute;bottom:0;left:0;width:0;height:2px;background:var(--r);transition:width 0.25s;border-radius:99px;}
.nav-links a:hover{color:var(--r);}
.nav-links a:hover::after{width:100%;}
.nav-btns{display:flex;gap:1rem;align-items:center;}
.nav-btn{padding:0.9rem 2rem;border-radius:99px;font-size:1.35rem;font-weight:700;text-decoration:none;transition:all 0.25s;font-family:'Outfit',sans-serif;}
.nav-btn.ghost{background:rgba(214,40,40,0.08);color:var(--r);border:1.5px solid rgba(214,40,40,0.2);}
.nav-btn.ghost:hover{background:rgba(214,40,40,0.15);}
.nav-btn.solid{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;box-shadow:0 4px 14px rgba(214,40,40,0.3);}
.nav-btn.solid:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(214,40,40,0.38);}
.nav-ham{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:4px;}
.nav-ham span{display:block;width:24px;height:2px;background:var(--r);border-radius:99px;}
.nav-mobile{display:none;position:fixed;inset:0;background:linear-gradient(135deg,#fff0f0,#fde0e0);z-index:999;flex-direction:column;align-items:center;justify-content:center;gap:2.5rem;}
.nav-mobile.open{display:flex;}
.nav-mobile a{font-size:2.4rem;font-weight:700;color:var(--ink);text-decoration:none;font-family:'Cormorant Garamond',serif;}
.nav-mobile a:hover{color:var(--r);}
.nav-mobile-close{position:absolute;top:2rem;right:2.5rem;font-size:2.4rem;cursor:pointer;color:var(--r);}
.nav-mobile-btns{display:flex;gap:1rem;margin-top:1rem;}
.form-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:12rem 2rem 8rem;background:linear-gradient(135deg,#fff8f8 0%,#fdf1f1 40%,#fae6e6 100%);position:relative;overflow:hidden;}
.form-page::before{content:'';position:absolute;top:-10%;right:-5%;width:55rem;height:55rem;border-radius:50%;background:radial-gradient(circle,rgba(214,40,40,0.07) 0%,transparent 70%);pointer-events:none;}
.form-page::after{content:'';position:absolute;bottom:-10%;left:-5%;width:45rem;height:45rem;border-radius:50%;background:radial-gradient(circle,rgba(214,40,40,0.05) 0%,transparent 70%);pointer-events:none;}
.form-card{background:var(--white);border-radius:2.8rem;padding:4.5rem;max-width:50rem;width:100%;box-shadow:0 24px 80px rgba(214,40,40,0.13);border:1.5px solid var(--line);position:relative;z-index:2;animation:popUp 0.5s var(--ease) both;}
@keyframes popUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
.form-icon-wrap{width:7rem;height:7rem;border-radius:50%;background:linear-gradient(135deg,var(--r),var(--rd));display:grid;place-items:center;font-size:2.8rem;margin:0 auto 2.4rem;box-shadow:0 8px 24px rgba(214,40,40,0.28);}
.form-icon-wrap i{color:#fff;}
.form-title{font-family:'Cormorant Garamond',serif;font-size:3.6rem;font-weight:700;color:var(--ink);text-align:center;margin-bottom:0.6rem;line-height:1.1;}
.form-title em{font-style:italic;color:var(--r);}
.form-sub{font-size:1.4rem;color:var(--ink3);text-align:center;margin-bottom:3.2rem;line-height:1.6;}
.form-group{position:relative;margin-bottom:1.4rem;}
.form-group .fi{position:absolute;left:1.6rem;top:50%;transform:translateY(-50%);color:var(--ink3);font-size:1.35rem;pointer-events:none;transition:color 0.2s;z-index:2;}
.form-group:focus-within .fi{color:var(--r);}
/* eye icon */
.eye-btn{position:absolute;right:1.8rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--ink3);font-size:1.4rem;transition:color 0.2s;z-index:2;padding:0;}
.eye-btn:hover{color:var(--r);}
.form-input{width:100%;padding:1.4rem 4.4rem 1.4rem 4.4rem;border:1.5px solid var(--line);border-radius:99px;font-size:1.4rem;font-family:'Outfit',sans-serif;color:var(--ink);background:var(--rp);outline:none;transition:all 0.25s;-moz-appearance:textfield;}
.form-input::-webkit-outer-spin-button,.form-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0;}
.form-input:focus{border-color:rgba(214,40,40,0.4);background:var(--white);box-shadow:0 0 0 4px rgba(214,40,40,0.07);}
/* password hint */
.pass-hint{font-size:1.15rem;color:var(--ink3);padding:0.6rem 1.6rem 0;line-height:1.55;display:flex;align-items:flex-start;gap:0.5rem;}
.pass-hint i{color:var(--r);font-size:1.1rem;margin-top:0.2rem;flex-shrink:0;}
/* strength bar */
.strength-wrap{padding:0.5rem 1.6rem 0;}
.strength-bar{height:4px;border-radius:99px;background:#f0e0e0;overflow:hidden;margin-bottom:0.4rem;}
.strength-fill{height:100%;border-radius:99px;width:0%;transition:width 0.3s,background 0.3s;}
.strength-label{font-size:1.1rem;color:var(--ink3);}
.form-link-row{font-size:1.3rem;color:var(--ink3);text-align:center;margin:1.2rem 0 2.4rem;}
.form-link-row a{color:var(--r);font-weight:700;text-decoration:none;}
.form-link-row a:hover{text-decoration:underline;}
.btn-submit{width:100%;padding:1.5rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;font-size:1.5rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 8px 24px rgba(214,40,40,0.28);transition:all 0.25s;display:flex;align-items:center;justify-content:center;gap:1rem;}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(214,40,40,0.38);}
.footer{background:linear-gradient(135deg,#fff0f0,#fde0e0);border-top:1px solid var(--line);padding:5rem 6% 3rem;}
.foot-top{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:4rem;padding-bottom:3.5rem;border-bottom:1px solid var(--line);}
.foot-logo{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);margin-bottom:1rem;display:block;}
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
  .nav-links,.nav-btns{display:none;}
  .nav-ham{display:flex;}
  .foot-top{grid-template-columns:1fr;}
  .form-card{padding:3rem 2.4rem;}
}
</style>
</head>
<body>
<nav class="nav" id="nav">
  <a href="index.php" class="nav-logo">My<span>Estate</span></a>
  <div class="nav-links">
    <a href="index.php#featured">Properties</a>
    <a href="index.php#upcoming">Upcoming</a>
    <a href="index.php#why">About</a>
    <a href="index.php#testimonials">Reviews</a>
  </div>
  <div class="nav-btns">
    <a href="login.php" class="nav-btn ghost">Login</a>
    <a href="register.php" class="nav-btn solid">Get Started</a>
  </div>
  <div class="nav-ham" id="ham" onclick="document.getElementById('mobileNav').classList.toggle('open')">
    <span></span><span></span><span></span>
  </div>
</nav>
<div class="nav-mobile" id="mobileNav">
  <span class="nav-mobile-close" onclick="document.getElementById('mobileNav').classList.remove('open')"><i class="fas fa-times"></i></span>
  <a href="index.php#featured" onclick="document.getElementById('mobileNav').classList.remove('open')">Properties</a>
  <a href="index.php#upcoming" onclick="document.getElementById('mobileNav').classList.remove('open')">Upcoming</a>
  <a href="index.php#why" onclick="document.getElementById('mobileNav').classList.remove('open')">About</a>
  <a href="index.php#testimonials" onclick="document.getElementById('mobileNav').classList.remove('open')">Reviews</a>
  <div class="nav-mobile-btns">
    <a href="login.php" class="nav-btn ghost">Login</a>
    <a href="register.php" class="nav-btn solid">Get Started</a>
  </div>
</div>

<div class="form-page">
  <div class="form-card">
    <div class="form-icon-wrap"><i class="fas fa-home"></i></div>
    <div class="form-title">Create an <em>Account!</em></div>
    <p class="form-sub">Join MyEstate and find your dream property</p>
    <form action="" method="post" id="regForm">
      <div class="form-group">
        <i class="fas fa-user fi"></i>
        <input type="text" name="name" required maxlength="50" placeholder="Enter your full name" class="form-input">
      </div>
      <div class="form-group">
        <i class="fas fa-envelope fi"></i>
        <input type="email" name="email" required maxlength="100" placeholder="Enter your email address" class="form-input">
      </div>
      <div class="form-group">
        <i class="fas fa-phone fi"></i>
        <input type="tel" name="number" required maxlength="10" placeholder="Enter your 10-digit number" class="form-input" pattern="[0-9]{10}">
      </div>
      <!-- Password -->
      <div class="form-group">
        <i class="fas fa-lock fi"></i>
        <input type="password" name="pass" id="passInput" required placeholder="Create a strong password" class="form-input" oninput="checkStrength(this.value)">
        <button type="button" class="eye-btn" onclick="toggleEye('passInput','eyePass')"><i class="fas fa-eye" id="eyePass"></i></button>
      </div>
      <div class="strength-wrap">
        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
        <div class="strength-label" id="strengthLabel"></div>
      </div>
      <div class="pass-hint"><i class="fas fa-info-circle"></i> Min. 8 characters with uppercase, lowercase, number & special character (e.g. @, #, !)</div>
      <!-- Confirm Password -->
      <div class="form-group" style="margin-top:1.4rem;">
        <i class="fas fa-lock fi"></i>
        <input type="password" name="c_pass" id="cPassInput" required placeholder="Confirm your password" class="form-input">
        <button type="button" class="eye-btn" onclick="toggleEye('cPassInput','eyeCPass')"><i class="fas fa-eye" id="eyeCPass"></i></button>
      </div>
      <p class="form-link-row">already have an account? <a href="login.php">login now</a></p>
      <button type="submit" name="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Send OTP & Register</button>
    </form>
  </div>
</div>

<footer class="footer">
  <div class="foot-top">
    <div class="foot-brand">
      <span class="foot-logo">My<span>Estate</span></span>
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
      <a href="index.php#featured"><i class="fas fa-chevron-right"></i>Apartments</a>
      <a href="index.php#featured"><i class="fas fa-chevron-right"></i>Villas</a>
      <a href="index.php#featured"><i class="fas fa-chevron-right"></i>Plots</a>
      <a href="index.php#upcoming"><i class="fas fa-chevron-right"></i>Upcoming</a>
    </div>
    <div class="foot-col">
      <h4>Quick Links</h4>
      <a href="index.php#why"><i class="fas fa-chevron-right"></i>About Us</a>
      <a href="index.php#testimonials"><i class="fas fa-chevron-right"></i>Reviews</a>
      <a href="login.php"><i class="fas fa-chevron-right"></i>Login</a>
      <a href="register.php"><i class="fas fa-chevron-right"></i>Register</a>
    </div>
    <div class="foot-col">
      <h4>Contact Us</h4>
      <div class="foot-contact-item"><div class="foot-contact-ic"><i class="fas fa-map-marker-alt"></i></div><div class="foot-contact-txt"><strong>Office</strong>Bandra West, Mumbai — 400050</div></div>
      <div class="foot-contact-item"><div class="foot-contact-ic"><i class="fas fa-phone-alt"></i></div><div class="foot-contact-txt"><strong>Phone</strong>+91 98765 43210</div></div>
      <div class="foot-contact-item"><div class="foot-contact-ic"><i class="fas fa-envelope"></i></div><div class="foot-contact-txt"><strong>Email</strong>hello@myestate.in</div></div>
    </div>
  </div>
  <div class="foot-bottom">
    <div class="foot-copy">© 2026 <span>MyEstate</span>. All rights reserved.</div>
    <div class="foot-links"><a href="#">Privacy Policy</a><a href="#">Terms of Use</a></div>
  </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="js/script.js"></script>
<?php include 'components/message.php'; ?>
<script>
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
function checkStrength(val){
  const fill  = document.getElementById('strengthFill');
  const label = document.getElementById('strengthLabel');
  let score = 0;
  if(val.length >= 8) score++;
  if(/[A-Z]/.test(val)) score++;
  if(/[a-z]/.test(val)) score++;
  if(/[0-9]/.test(val)) score++;
  if(/[\W_]/.test(val)) score++;
  const map = [
    {w:'0%',  bg:'transparent', txt:''},
    {w:'20%', bg:'#e74c3c',     txt:'Very Weak'},
    {w:'40%', bg:'#e67e22',     txt:'Weak'},
    {w:'60%', bg:'#f1c40f',     txt:'Fair'},
    {w:'80%', bg:'#2ecc71',     txt:'Strong'},
    {w:'100%',bg:'#27ae60',     txt:'Very Strong'},
  ];
  fill.style.width      = map[score].w;
  fill.style.background = map[score].bg;
  label.textContent     = map[score].txt;
  label.style.color     = map[score].bg;
}
</script>
</body>
</html>