<?php
session_start();
include 'components/connect.php';
include 'components/mailer.php';

// Agar session nahi hai to register pe bhejo
if(!isset($_SESSION['reg_email'])){
   header('location:register.php');
   exit();
}

$reg_email  = $_SESSION['reg_email'];
$reg_name   = $_SESSION['reg_name'];
$reg_number = $_SESSION['reg_number'];
$reg_pass   = $_SESSION['reg_pass'];

// OTP VERIFY
if(isset($_POST['verify'])){
   $entered_otp = trim($_POST['otp']);

   $chk = $conn->prepare("SELECT * FROM otp_verification WHERE email=? AND otp=? AND is_used=0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
   $chk->execute([$reg_email, $entered_otp]);

   if($chk->rowCount() > 0){
      $row_otp = $chk->fetch(PDO::FETCH_ASSOC);

      // Mark OTP used
      $upd = $conn->prepare("UPDATE otp_verification SET is_used=1 WHERE id=?");
      $upd->execute([$row_otp['id']]);

      // Insert user
      $uid = create_unique_id();
      $ins = $conn->prepare("INSERT INTO users(id, name, number, email, password) VALUES(?,?,?,?,?)");
      $ins->execute([$uid, $reg_name, $reg_number, $reg_email, $reg_pass]);

      // Send welcome email
      sendMail($reg_email, $reg_name, 'Welcome to MyEstate! 🎉', getWelcomeEmailTemplate($reg_name));

      // Clear session
      unset($_SESSION['reg_name'], $_SESSION['reg_number'], $_SESSION['reg_email'], $_SESSION['reg_pass']);

      // Login
      setcookie('user_id', $uid, time() + 60*60*24*30, '/');
      if(isset($_SESSION['redirect_after_login'])){
         $redirect = $_SESSION['redirect_after_login'];
         unset($_SESSION['redirect_after_login']);
         if($redirect === 'listings'){
            header('location:listings.php');
         } elseif($redirect === 'upcoming'){
            header('location:home.php#upSec');
         } elseif($redirect === 'about'){
            header('location:about.php');
         } elseif($redirect === 'contact'){
            header('location:contact.php');
         } elseif($redirect === 'properties'){
            header('location:listings.php');
         } else {
            header('location:home.php');
         }
      } else {
         header('location:home.php');
      }
      exit();
   } else {
      $error_msg[] = 'Invalid or expired OTP! Please try again or resend.';
   }
}

// RESEND OTP
if(isset($_POST['resend'])){
   $otp        = generateOTP();
   $expires_at_query = $conn->query("SELECT DATE_ADD(NOW(), INTERVAL 5 MINUTE) as exp")->fetch();
   $expires_at = $expires_at_query['exp']; // 5 minutes from MySQL time

   $del = $conn->prepare("DELETE FROM otp_verification WHERE email=?");
   $del->execute([$reg_email]);

   $ins = $conn->prepare("INSERT INTO otp_verification(email, otp, expires_at) VALUES(?,?,?)");
   $ins->execute([$reg_email, $otp, $expires_at]);

   $sent = sendMail($reg_email, $reg_name, 'MyEstate — New OTP', getOTPEmailTemplate($otp, $reg_name));
   if($sent){
      $success_msg[] = 'New OTP sent to your email!';
   } else {
      $error_msg[] = 'Failed to resend OTP. Please try again.';
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify OTP — MyEstate</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<style>
:root{--r:#d62828;--rd:#9e1c1c;--rp:#fdf1f1;--rp2:#fae6e6;--ink:#1a0505;--ink3:#9a6565;--white:#fff;--bg:#faf5f5;--line:rgba(214,40,40,0.12);--ease:cubic-bezier(.22,1,.36,1);}
*{margin:0;padding:0;box-sizing:border-box;}
html{font-size:62.5%;}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--ink);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;background:linear-gradient(135deg,#fff8f8,#fdf1f1,#fae6e6);position:relative;overflow:hidden;}
body::before{content:'';position:absolute;top:-10%;right:-5%;width:55rem;height:55rem;border-radius:50%;background:radial-gradient(circle,rgba(214,40,40,0.07),transparent 70%);pointer-events:none;}
body::after{content:'';position:absolute;bottom:-10%;left:-5%;width:45rem;height:45rem;border-radius:50%;background:radial-gradient(circle,rgba(214,40,40,0.05),transparent 70%);pointer-events:none;}
.card{background:var(--white);border-radius:2.8rem;padding:4.5rem;max-width:46rem;width:100%;box-shadow:0 24px 80px rgba(214,40,40,0.13);border:1.5px solid var(--line);position:relative;z-index:2;animation:pop .5s var(--ease) both;text-align:center;}
@keyframes pop{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
.icon-wrap{width:8rem;height:8rem;border-radius:50%;background:linear-gradient(135deg,var(--r),var(--rd));display:grid;place-items:center;font-size:3.2rem;margin:0 auto 2.4rem;box-shadow:0 8px 24px rgba(214,40,40,0.28);}
.icon-wrap i{color:#fff;}
h2{font-family:'Cormorant Garamond',serif;font-size:3.6rem;font-weight:700;color:var(--ink);margin-bottom:.8rem;}
h2 em{font-style:italic;color:var(--r);}
.sub{font-size:1.4rem;color:var(--ink3);line-height:1.7;margin-bottom:1rem;}
.email-badge{display:inline-flex;align-items:center;gap:.6rem;background:var(--rp);border:1.5px solid rgba(214,40,40,.15);border-radius:99px;padding:.5rem 1.4rem;font-size:1.3rem;font-weight:700;color:var(--r);margin-bottom:3rem;}
/* OTP input boxes */
.otp-inputs{display:flex;gap:1.2rem;justify-content:center;margin-bottom:1.4rem;}
.otp-box{width:5.5rem;height:6.5rem;border:2px solid var(--line);border-radius:1.4rem;font-size:2.8rem;font-weight:800;text-align:center;color:var(--ink);background:var(--rp);outline:none;font-family:'Cormorant Garamond',serif;transition:all .2s;-moz-appearance:textfield;}
.otp-box::-webkit-outer-spin-button,.otp-box::-webkit-inner-spin-button{-webkit-appearance:none;}
.otp-box:focus{border-color:var(--r);background:var(--white);box-shadow:0 0 0 4px rgba(214,40,40,.08);}
.otp-box.filled{border-color:var(--r);background:var(--white);}
/* timer */
.timer-wrap{font-size:1.35rem;color:var(--ink3);margin-bottom:2.8rem;}
.timer-wrap span{font-weight:800;color:var(--r);}
.btn-verify{width:100%;padding:1.5rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;font-size:1.5rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 8px 24px rgba(214,40,40,.28);transition:all .25s;display:flex;align-items:center;justify-content:center;gap:1rem;margin-bottom:1.6rem;}
.btn-verify:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(214,40,40,.38);}
.resend-form button{background:none;border:none;font-size:1.35rem;color:var(--ink3);cursor:pointer;font-family:'Outfit',sans-serif;transition:color .2s;}
.resend-form button:hover{color:var(--r);}
.resend-form button span{color:var(--r);font-weight:700;text-decoration:underline;}
.back-link{display:block;margin-top:1.2rem;font-size:1.3rem;color:var(--ink3);text-decoration:none;}
.back-link:hover{color:var(--r);}
</style>
</head>
<body>
<div class="card">
  <div class="icon-wrap"><i class="fas fa-envelope-open-text"></i></div>
  <h2>Verify Your <em>Email</em></h2>
  <p class="sub">We sent a 6-digit OTP to your email address</p>
  <div class="email-badge"><i class="fas fa-envelope"></i> <?= htmlspecialchars($reg_email) ?></div>

  <form action="" method="POST" id="otpForm">
    <div class="otp-inputs">
      <input type="number" class="otp-box" maxlength="1" min="0" max="9" id="o1">
      <input type="number" class="otp-box" maxlength="1" min="0" max="9" id="o2">
      <input type="number" class="otp-box" maxlength="1" min="0" max="9" id="o3">
      <input type="number" class="otp-box" maxlength="1" min="0" max="9" id="o4">
      <input type="number" class="otp-box" maxlength="1" min="0" max="9" id="o5">
      <input type="number" class="otp-box" maxlength="1" min="0" max="9" id="o6">
    </div>
    <input type="hidden" name="otp" id="otpHidden">
    <div class="timer-wrap">OTP expires in <span id="countdown">05:00</span></div>
    <button type="submit" name="verify" class="btn-verify"><i class="fas fa-check-circle"></i> Verify OTP</button>
  </form>

  <form action="" method="POST" class="resend-form">
    <button type="submit" name="resend">Didn't receive it? <span>Resend OTP</span></button>
  </form>
  <a href="register.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Register</a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/message.php'; ?>
<script>
// OTP boxes auto-focus
const boxes = document.querySelectorAll('.otp-box');
boxes.forEach((box, i) => {
  box.addEventListener('input', function(){
    this.value = this.value.slice(-1);
    if(this.value && i < 5) boxes[i+1].focus();
    this.classList.toggle('filled', this.value !== '');
  });
  box.addEventListener('keydown', function(e){
    if(e.key === 'Backspace' && !this.value && i > 0) boxes[i-1].focus();
  });
});

// Combine OTP before submit
document.getElementById('otpForm').addEventListener('submit', function(e){
  let otp = '';
  boxes.forEach(b => otp += b.value);
  document.getElementById('otpHidden').value = otp;
  if(otp.length < 6){
    e.preventDefault();
    alert('Please enter the complete 6-digit OTP.');
  }
});

// Countdown timer — 5 minutes
let secs = 300;
const cd = document.getElementById('countdown');
const timer = setInterval(() => {
  secs--;
  const m = String(Math.floor(secs/60)).padStart(2,'0');
  const s = String(secs%60).padStart(2,'0');
  cd.textContent = m + ':' + s;
  if(secs <= 0){
    clearInterval(timer);
    cd.textContent = 'Expired';
    cd.style.color = '#e74c3c';
  }
}, 1000);
</script>
</body>
</html>