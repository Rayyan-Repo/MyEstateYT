<?php
session_start();
include 'components/connect.php';
include 'components/mailer.php';

if(isset($_COOKIE['user_id'])){ header('location:home.php'); exit(); }

if(isset($_POST['send_reset'])){
   $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));

   if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $warning_msg[] = 'Please enter a valid email address!';
   } else {
      $chk = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
      $chk->execute([$email]);
      if($chk->rowCount() > 0){
         $user = $chk->fetch(PDO::FETCH_ASSOC);
         $token = bin2hex(random_bytes(32));
         $expires_at_query = $conn->query("SELECT DATE_ADD(NOW(), INTERVAL 20 MINUTE) as exp")->fetch();
         $expires_at = $expires_at_query['exp']; // 20 minutes from MySQL time

         $del = $conn->prepare("DELETE FROM password_resets WHERE email=?");
         $del->execute([$email]);

         $ins = $conn->prepare("INSERT INTO password_resets(email, token, expires_at) VALUES(?,?,?)");
         $ins->execute([$email, $token, $expires_at]);

         $reset_link = "http://localhost/MyEstateYT/projectV2/reset_password.php?token=" . $token;
         $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;background:#faf5f5;margin:0;padding:0;}.wrap{max-width:560px;margin:40px auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 8px 40px rgba(214,40,40,0.12);}.header{background:linear-gradient(135deg,#d62828,#9e1c1c);padding:40px;text-align:center;}.logo{font-size:28px;font-weight:700;color:#fff;}.body{padding:40px;}.hi{font-size:20px;font-weight:700;color:#1a0505;margin-bottom:10px;}.msg{font-size:15px;color:#9a6565;line-height:1.7;margin-bottom:28px;}.btn{display:block;text-align:center;background:linear-gradient(135deg,#d62828,#9e1c1c);color:#fff;text-decoration:none;padding:16px 32px;border-radius:99px;font-size:16px;font-weight:800;margin:0 auto 24px;width:fit-content;}.warn{font-size:13px;color:#9a6565;background:#faf5f5;border-radius:10px;padding:16px;}.footer{background:#fdf1f1;padding:20px 40px;text-align:center;border-top:1px solid rgba(214,40,40,0.1);font-size:12px;color:#9a6565;}</style></head><body><div class="wrap"><div class="header"><div class="logo">MyEstate</div></div><div class="body"><div class="hi">Hello, ' . htmlspecialchars($user['name']) . '!</div><div class="msg">We received a request to reset your MyEstate password. Click the button below to set a new password. This link is valid for <strong>20 minutes</strong>.</div><a href="' . $reset_link . '" class="btn">Reset My Password →</a><div class="warn">⚠️ If you did not request a password reset, please ignore this email. Your account is safe.</div></div><div class="footer">© 2026 MyEstate. All rights reserved.</div></div></body></html>';

         $sent = sendMail($email, $user['name'], 'MyEstate — Password Reset Request', $html);
         if($sent){
            $success_msg[] = 'Password reset link sent to your email! Valid for 20 minutes.';
         } else {
            $error_msg[] = 'Failed to send email. Please try again.';
         }
      } else {
         // Don't reveal if email exists — security best practice
         $success_msg[] = 'If this email is registered, you will receive a reset link shortly.';
      }
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password — MyEstate</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<style>
:root{--r:#d62828;--rd:#9e1c1c;--rp:#fdf1f1;--ink:#1a0505;--ink3:#9a6565;--white:#fff;--bg:#faf5f5;--line:rgba(214,40,40,0.12);--ease:cubic-bezier(.22,1,.36,1);}
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
.sub{font-size:1.4rem;color:var(--ink3);line-height:1.7;margin-bottom:3rem;}
.form-group{position:relative;margin-bottom:2rem;}
.form-group .fi{position:absolute;left:1.6rem;top:50%;transform:translateY(-50%);color:var(--ink3);font-size:1.35rem;pointer-events:none;z-index:2;}
.form-group:focus-within .fi{color:var(--r);}
.form-input{width:100%;padding:1.4rem 1.6rem 1.4rem 4.4rem;border:1.5px solid var(--line);border-radius:99px;font-size:1.4rem;font-family:'Outfit',sans-serif;color:var(--ink);background:var(--rp);outline:none;transition:all 0.25s;}
.form-input:focus{border-color:rgba(214,40,40,0.4);background:var(--white);box-shadow:0 0 0 4px rgba(214,40,40,0.07);}
.btn-submit{width:100%;padding:1.5rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;font-size:1.5rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 8px 24px rgba(214,40,40,0.28);transition:all 0.25s;display:flex;align-items:center;justify-content:center;gap:1rem;margin-bottom:2rem;}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(214,40,40,0.38);}
.back-link{display:inline-flex;align-items:center;gap:.6rem;font-size:1.3rem;color:var(--ink3);text-decoration:none;}
.back-link:hover{color:var(--r);}
</style>
</head>
<body>
<div class="card">
  <div class="icon-wrap"><i class="fas fa-key"></i></div>
  <h2>Forgot <em>Password?</em></h2>
  <p class="sub">Enter your registered email address. We'll send you a password reset link valid for 20 minutes.</p>
  <form action="" method="POST">
    <div class="form-group">
      <i class="fas fa-envelope fi"></i>
      <input type="email" name="email" required placeholder="Enter your registered email" class="form-input">
    </div>
    <button type="submit" name="send_reset" class="btn-submit"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
  </form>
  <a href="login.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/message.php'; ?>
</body>
</html>