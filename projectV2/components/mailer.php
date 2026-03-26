<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/PHPMailer/src/Exception.php';
require_once dirname(__DIR__) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(__DIR__) . '/PHPMailer/src/SMTP.php';

function sendMail($to_email, $to_name, $subject, $html_body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rayyanebhagat@gmail.com'; // apna Gmail yahan
        $mail->Password   = 'wftk fwjz rquv eeno';    // 16 digit app password yahan
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom('rayyanebhagat@gmail.com', 'MyEstate');
        $mail->addAddress($to_email, $to_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function getOTPEmailTemplate($otp, $name) {
    return '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body{margin:0;padding:0;font-family:\'Outfit\',Arial,sans-serif;background:#faf5f5;}
  .wrap{max-width:560px;margin:40px auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 8px 40px rgba(214,40,40,0.12);}
  .header{background:linear-gradient(135deg,#d62828,#9e1c1c);padding:40px 40px 32px;text-align:center;}
  .logo{font-size:28px;font-weight:700;color:#fff;letter-spacing:-0.02em;}
  .logo em{font-style:italic;color:rgba(255,200,200,0.9);}
  .body{padding:40px;}
  .hi{font-size:22px;font-weight:700;color:#1a0505;margin-bottom:8px;}
  .msg{font-size:15px;color:#9a6565;line-height:1.7;margin-bottom:32px;}
  .otp-box{background:#fdf1f1;border:2px dashed rgba(214,40,40,0.25);border-radius:16px;padding:28px;text-align:center;margin-bottom:28px;}
  .otp-label{font-size:13px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#9a6565;margin-bottom:10px;}
  .otp-code{font-size:48px;font-weight:800;color:#d62828;letter-spacing:10px;}
  .timer{font-size:13px;color:#9a6565;margin-top:10px;display:flex;align-items:center;justify-content:center;gap:6px;}
  .warn{font-size:13px;color:#9a6565;line-height:1.7;background:#faf5f5;border-radius:10px;padding:16px;margin-bottom:24px;}
  .footer{background:#fdf1f1;padding:24px 40px;text-align:center;border-top:1px solid rgba(214,40,40,0.1);}
  .footer p{font-size:12px;color:#9a6565;margin:0;}
  .footer span{color:#d62828;font-weight:700;}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">My<span style="color:rgba(255,200,200,0.9);font-style:italic;">Estate</span></div>
  </div>
  <div class="body">
    <div class="hi">Hello, ' . htmlspecialchars($name) . '! 👋</div>
    <div class="msg">Thank you for registering with MyEstate. Please use the OTP below to verify your email address and complete your registration.</div>
    <div class="otp-box">
      <div class="otp-label">Your Verification Code</div>
      <div class="otp-code">' . $otp . '</div>
      <div class="timer">⏱ Valid for <strong>5 minutes only</strong></div>
    </div>
    <div class="warn">⚠️ Do not share this OTP with anyone. MyEstate will never ask for your OTP. If you did not request this, please ignore this email.</div>
  </div>
  <div class="footer"><p>© 2026 <span>MyEstate</span>. All rights reserved.</p></div>
</div>
</body>
</html>';
}

function getWelcomeEmailTemplate($name) {
    return '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body{margin:0;padding:0;font-family:\'Outfit\',Arial,sans-serif;background:#faf5f5;}
  .wrap{max-width:560px;margin:40px auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 8px 40px rgba(214,40,40,0.12);}
  .header{background:linear-gradient(135deg,#d62828,#9e1c1c);padding:48px 40px;text-align:center;}
  .logo{font-size:28px;font-weight:700;color:#fff;letter-spacing:-0.02em;}
  .logo em{font-style:italic;color:rgba(255,200,200,0.9);}
  .headline{font-size:32px;font-weight:800;color:#fff;margin-top:20px;line-height:1.2;}
  .body{padding:40px;}
  .hi{font-size:22px;font-weight:700;color:#1a0505;margin-bottom:12px;}
  .msg{font-size:15px;color:#9a6565;line-height:1.75;margin-bottom:28px;}
  .features{display:flex;flex-direction:column;gap:12px;margin-bottom:32px;}
  .feat{display:flex;align-items:flex-start;gap:14px;padding:16px;background:#fdf1f1;border-radius:12px;border-left:3px solid #d62828;}
  .feat-icon{font-size:22px;flex-shrink:0;}
  .feat-title{font-size:14px;font-weight:700;color:#1a0505;margin-bottom:3px;}
  .feat-desc{font-size:13px;color:#9a6565;}
  .cta{display:block;text-align:center;background:linear-gradient(135deg,#d62828,#9e1c1c);color:#fff;text-decoration:none;padding:16px 32px;border-radius:99px;font-size:16px;font-weight:800;margin:0 auto 24px;width:fit-content;box-shadow:0 8px 24px rgba(214,40,40,0.28);}
  .footer{background:#fdf1f1;padding:24px 40px;text-align:center;border-top:1px solid rgba(214,40,40,0.1);}
  .footer p{font-size:12px;color:#9a6565;margin:0;}
  .footer span{color:#d62828;font-weight:700;}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">My<span style="color:rgba(255,200,200,0.9);font-style:italic;">Estate</span></div>
    <div class="headline">Welcome to the Family! 🎉</div>
  </div>
  <div class="body">
    <div class="hi">Hello, ' . htmlspecialchars($name) . '!</div>
    <div class="msg">You have successfully verified your email and joined <strong>MyEstate</strong> — Mumbai & Pune\'s most trusted real estate platform. We\'re thrilled to have you with us!</div>
    <div class="features">
      <div class="feat"><div class="feat-icon">🏠</div><div><div class="feat-title">Browse Verified Properties</div><div class="feat-desc">Explore 100% verified listings across Mumbai & Pune.</div></div></div>
      <div class="feat"><div class="feat-icon">📅</div><div><div class="feat-title">Book Site Visits</div><div class="feat-desc">Schedule property visits at your convenience.</div></div></div>
      <div class="feat"><div class="feat-icon">💬</div><div><div class="feat-title">Direct Owner Contact</div><div class="feat-desc">Enquire directly — zero commission, zero middlemen.</div></div></div>
      <div class="feat"><div class="feat-icon">❤️</div><div><div class="feat-title">Save Your Favourites</div><div class="feat-desc">Save properties and revisit them anytime.</div></div></div>
    </div>
    <a href="http://localhost/MyEstateYT/projectV2/home.php" class="cta">Explore Properties →</a>
  </div>
  <div class="footer"><p>© 2026 <span>MyEstate</span>. Made with ♥ in Mumbai.</p></div>
</div>
</body>
</html>';
}
?>