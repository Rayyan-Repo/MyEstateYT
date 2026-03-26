<?php
session_start();
include 'components/connect.php';

$token = $_GET['token'] ?? '';
$valid = false;
$user_email = '';

if($token){
   $chk = $conn->prepare("SELECT * FROM password_resets WHERE token=? AND is_used=0 AND expires_at > NOW() LIMIT 1");
   $chk->execute([$token]);
   if($chk->rowCount() > 0){
      $valid = true;
      $reset_row = $chk->fetch(PDO::FETCH_ASSOC);
      $user_email = $reset_row['email'];
   }
}

function isStrongPassword($pass){
   if(strlen($pass) < 8) return false;
   if(!preg_match('/[A-Z]/', $pass)) return false;
   if(!preg_match('/[a-z]/', $pass)) return false;
   if(!preg_match('/[0-9]/', $pass)) return false;
   if(!preg_match('/[\W_]/', $pass)) return false;
   return true;
}

if(isset($_POST['reset']) && $valid){
   $new_pass = $_POST['new_pass'];
   $c_pass   = $_POST['c_pass'];

   if(!isStrongPassword($new_pass)){
      $warning_msg[] = 'Password must be at least 8 characters with uppercase, lowercase, number & special character!';
   } elseif($new_pass !== $c_pass){
      $warning_msg[] = 'Passwords do not match!';
   } else {
      $hashed = sha1($new_pass);
      $upd = $conn->prepare("UPDATE users SET password=? WHERE email=?");
      $upd->execute([$hashed, $user_email]);

      $markUsed = $conn->prepare("UPDATE password_resets SET is_used=1 WHERE token=?");
      $markUsed->execute([$token]);

      $success_msg[] = 'Password reset successfully! You can now login.';
      header('refresh:2;url=login.php');
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password — MyEstate</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<style>
:root{--r:#d62828;--rd:#9e1c1c;--rp:#fdf1f1;--ink:#1a0505;--ink3:#9a6565;--white:#fff;--bg:#faf5f5;--line:rgba(214,40,40,0.12);--ease:cubic-bezier(.22,1,.36,1);}
*{margin:0;padding:0;box-sizing:border-box;}
html{font-size:62.5%;}
body{font-family:'Outfit',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;background:linear-gradient(135deg,#fff8f8,#fdf1f1,#fae6e6);position:relative;overflow:hidden;}
body::before{content:'';position:absolute;top:-10%;right:-5%;width:55rem;height:55rem;border-radius:50%;background:radial-gradient(circle,rgba(214,40,40,0.07),transparent 70%);pointer-events:none;}
.card{background:var(--white);border-radius:2.8rem;padding:4.5rem;max-width:46rem;width:100%;box-shadow:0 24px 80px rgba(214,40,40,0.13);border:1.5px solid var(--line);position:relative;z-index:2;animation:pop .5s var(--ease) both;text-align:center;}
@keyframes pop{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
.icon-wrap{width:8rem;height:8rem;border-radius:50%;background:linear-gradient(135deg,var(--r),var(--rd));display:grid;place-items:center;font-size:3.2rem;margin:0 auto 2.4rem;box-shadow:0 8px 24px rgba(214,40,40,0.28);}
.icon-wrap i{color:#fff;}
h2{font-family:'Cormorant Garamond',serif;font-size:3.6rem;font-weight:700;color:var(--ink);margin-bottom:.8rem;}
h2 em{font-style:italic;color:var(--r);}
.sub{font-size:1.4rem;color:var(--ink3);line-height:1.7;margin-bottom:3rem;}
.expired-box{background:var(--rp);border:1.5px solid rgba(214,40,40,0.2);border-radius:1.6rem;padding:2.4rem;margin-bottom:2rem;}
.expired-box i{font-size:3rem;color:var(--r);margin-bottom:1rem;display:block;}
.expired-box p{font-size:1.4rem;color:var(--ink3);line-height:1.65;}
.form-group{position:relative;margin-bottom:1.4rem;text-align:left;}
.form-group .fi{position:absolute;left:1.6rem;top:50%;transform:translateY(-50%);color:var(--ink3);font-size:1.35rem;pointer-events:none;z-index:2;}
.form-group:focus-within .fi{color:var(--r);}
.eye-btn{position:absolute;right:1.8rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--ink3);font-size:1.4rem;z-index:2;padding:0;}
.eye-btn:hover{color:var(--r);}
.form-input{width:100%;padding:1.4rem 4.4rem 1.4rem 4.4rem;border:1.5px solid var(--line);border-radius:99px;font-size:1.4rem;font-family:'Outfit',sans-serif;color:var(--ink);background:var(--rp);outline:none;transition:all 0.25s;}
.form-input:focus{border-color:rgba(214,40,40,0.4);background:var(--white);box-shadow:0 0 0 4px rgba(214,40,40,0.07);}
.pass-hint{font-size:1.15rem;color:var(--ink3);padding:0.5rem 1.6rem 0;line-height:1.55;text-align:left;display:flex;align-items:flex-start;gap:.5rem;margin-bottom:1rem;}
.pass-hint i{color:var(--r);font-size:1.1rem;margin-top:.2rem;flex-shrink:0;}
.strength-wrap{padding:0.4rem 1.6rem 0;margin-bottom:1.4rem;}
.strength-bar{height:4px;border-radius:99px;background:#f0e0e0;overflow:hidden;margin-bottom:0.4rem;}
.strength-fill{height:100%;border-radius:99px;width:0%;transition:width .3s,background .3s;}
.strength-label{font-size:1.1rem;color:var(--ink3);text-align:left;}
.btn-submit{width:100%;padding:1.5rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;font-size:1.5rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 8px 24px rgba(214,40,40,0.28);transition:all 0.25s;display:flex;align-items:center;justify-content:center;gap:1rem;margin-bottom:2rem;}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(214,40,40,0.38);}
.back-link{display:inline-flex;align-items:center;gap:.6rem;font-size:1.3rem;color:var(--ink3);text-decoration:none;}
.back-link:hover{color:var(--r);}
</style>
</head>
<body>
<div class="card">
  <div class="icon-wrap"><i class="fas fa-lock"></i></div>
  <h2>Reset <em>Password</em></h2>

  <?php if(!$valid): ?>
    <p class="sub">This link is invalid or has expired.</p>
    <div class="expired-box">
      <i class="fas fa-exclamation-triangle"></i>
      <p>Password reset links are valid for 20 minutes only. Please request a new one.</p>
    </div>
    <a href="forgot_password.php" class="btn-submit" style="text-decoration:none;"><i class="fas fa-redo"></i> Request New Link</a>
  <?php else: ?>
    <p class="sub">Enter your new password below.</p>
    <form action="" method="POST">
      <div class="form-group">
        <i class="fas fa-lock fi"></i>
        <input type="password" name="new_pass" id="newPass" required placeholder="Enter new password" class="form-input" oninput="checkStrength(this.value)">
        <button type="button" class="eye-btn" onclick="toggleEye('newPass','eyeNew')"><i class="fas fa-eye" id="eyeNew"></i></button>
      </div>
      <div class="strength-wrap">
        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
        <div class="strength-label" id="strengthLabel"></div>
      </div>
      <div class="pass-hint"><i class="fas fa-info-circle"></i> Min. 8 characters with uppercase, lowercase, number & special character</div>
      <div class="form-group">
        <i class="fas fa-lock fi"></i>
        <input type="password" name="c_pass" id="cPass" required placeholder="Confirm new password" class="form-input">
        <button type="button" class="eye-btn" onclick="toggleEye('cPass','eyeC')"><i class="fas fa-eye" id="eyeC"></i></button>
      </div>
      <button type="submit" name="reset" class="btn-submit"><i class="fas fa-check-circle"></i> Reset Password</button>
    </form>
  <?php endif; ?>
  <a href="login.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/message.php'; ?>
<script>
function toggleEye(inputId, iconId){
  const inp=document.getElementById(inputId),ico=document.getElementById(iconId);
  if(inp.type==='password'){inp.type='text';ico.classList.replace('fa-eye','fa-eye-slash');}
  else{inp.type='password';ico.classList.replace('fa-eye-slash','fa-eye');}
}
function checkStrength(val){
  const fill=document.getElementById('strengthFill'),label=document.getElementById('strengthLabel');
  let s=0;
  if(val.length>=8)s++;if(/[A-Z]/.test(val))s++;if(/[a-z]/.test(val))s++;if(/[0-9]/.test(val))s++;if(/[\W_]/.test(val))s++;
  const m=[{w:'0%',bg:'transparent',t:''},{w:'20%',bg:'#e74c3c',t:'Very Weak'},{w:'40%',bg:'#e67e22',t:'Weak'},{w:'60%',bg:'#f1c40f',t:'Fair'},{w:'80%',bg:'#2ecc71',t:'Strong'},{w:'100%',bg:'#27ae60',t:'Very Strong'}];
  fill.style.width=m[s].w;fill.style.background=m[s].bg;label.textContent=m[s].t;label.style.color=m[s].bg;
}
</script>
</body>
</html>