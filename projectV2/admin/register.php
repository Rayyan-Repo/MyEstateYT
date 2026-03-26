<?php
include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
   exit();
}

function isStrongPassword($pass){
   if(strlen($pass) < 8) return false;
   if(!preg_match('/[A-Z]/', $pass)) return false;
   if(!preg_match('/[a-z]/', $pass)) return false;
   if(!preg_match('/[0-9]/', $pass)) return false;
   if(!preg_match('/[\W_]/', $pass)) return false;
   return true;
}

if(isset($_POST['submit'])){
   $id     = create_unique_id();
   $name   = trim(filter_var($_POST['name'],   FILTER_SANITIZE_STRING));
   $pass   = $_POST['pass'];
   $c_pass = $_POST['c_pass'];

   if(!isStrongPassword($pass)){
      $warning_msg[] = 'Password must be at least 8 characters with uppercase, lowercase, number & special character!';
   } elseif($pass !== $c_pass){
      $warning_msg[] = 'Passwords do not match!';
   } else {
      $chk = $conn->prepare("SELECT * FROM admins WHERE name=?");
      $chk->execute([$name]);
      if($chk->rowCount() > 0){
         $warning_msg[] = 'Username already taken!';
      } else {
         $ins = $conn->prepare("INSERT INTO admins(id, name, password) VALUES(?,?,?)");
         $ins->execute([$id, $name, sha1($pass)]);
         $success_msg[] = 'Admin registered successfully!';
      }
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register Admin</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      .register-section{min-height:calc(100vh - 6rem);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;background:#fff;padding:4rem 2rem;}
      .register-section::before{content:'';position:absolute;width:55rem;height:55rem;border-radius:50%;border:2px solid rgba(214,40,40,0.42);top:-20rem;right:-18rem;pointer-events:none;}
      .register-section::after{content:'';position:absolute;width:35rem;height:35rem;border-radius:50%;border:2px solid rgba(214,40,40,0.32);top:-8rem;right:-6rem;pointer-events:none;}
      .rs-c1{position:absolute;width:44rem;height:44rem;border-radius:50%;border:2px solid rgba(214,40,40,0.38);bottom:-18rem;left:-15rem;pointer-events:none;}
      .rs-c2{position:absolute;width:24rem;height:24rem;border-radius:50%;border:1.5px solid rgba(214,40,40,0.28);bottom:-6rem;left:-4rem;pointer-events:none;}
      .rs-dot{position:absolute;width:0.75rem;height:0.75rem;border-radius:50%;background:rgba(214,40,40,0.45);pointer-events:none;}
      .rs-d1{top:5rem;left:5rem;}.rs-d2{top:5rem;left:7rem;}.rs-d3{top:5rem;left:9rem;}
      .rs-d4{bottom:5rem;right:5rem;}.rs-d5{bottom:5rem;right:7rem;}.rs-d6{bottom:5rem;right:9rem;}
      .rs-line{position:absolute;height:1.5px;background:linear-gradient(90deg,transparent,rgba(214,40,40,0.38),transparent);pointer-events:none;}
      .rs-l1{width:9rem;top:9rem;left:3rem;transform:rotate(-45deg);}
      .rs-l2{width:6rem;top:11rem;left:5rem;transform:rotate(-45deg);}
      .rs-l3{width:9rem;bottom:9rem;right:3rem;transform:rotate(-45deg);}
      .rs-l4{width:6rem;bottom:11rem;right:5rem;transform:rotate(-45deg);}
      .register-card{width:46rem;border-radius:2rem;padding:4rem 3.8rem;background:#fff;position:relative;z-index:1;text-align:center;box-shadow:0 0 0 1.5px rgba(214,40,40,0.25),0 8px 32px rgba(214,40,40,0.14);animation:regAppear .55s cubic-bezier(.34,1.56,.64,1) both;}
      @keyframes regAppear{from{opacity:0;transform:translateY(28px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
      .register-card::before{content:'';position:absolute;top:0;left:8%;right:8%;height:3.5px;background:linear-gradient(90deg,transparent,#d62828,#b01c1c,transparent);border-radius:0 0 99px 99px;}
      .reg-icon{display:inline-flex;align-items:center;justify-content:center;width:6.5rem;height:6.5rem;background:linear-gradient(135deg,#d62828,#b01c1c);border-radius:1.4rem;font-size:2.6rem;margin-bottom:1.8rem;box-shadow:0 8px 24px rgba(214,40,40,0.22);}
      .register-card h3{font-family:'Syne',sans-serif;font-size:2.4rem;font-weight:800;color:#1a0a0a;letter-spacing:-0.03em;margin-bottom:0.5rem;}
      .register-card .subtitle{font-size:1.4rem;color:#9a7070;margin-bottom:2rem;}
      /* input wrapper for eye icon */
      .input-wrap{position:relative;margin:0.7rem 0;}
      .input-wrap .box{width:100%;border-radius:0.8rem;padding:1.4rem 4.5rem 1.4rem 1.8rem;font-size:1.55rem;color:#1a0a0a;background:#fafafa;border:1.5px solid rgba(214,40,40,0.15)!important;transition:all .25s;font-family:'Plus Jakarta Sans',sans-serif;display:block;outline:none;}
      .input-wrap .box::placeholder{color:#9a7070;}
      .input-wrap .box:focus{border-color:#d62828!important;background:#fff;box-shadow:0 0 0 3px rgba(214,40,40,0.10);}
      .eye-btn{position:absolute;right:1.4rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9a7070;font-size:1.4rem;transition:color .2s;padding:0;}
      .eye-btn:hover{color:#d62828;}
      /* password hint */
      .pass-hint{font-size:1.15rem;color:#9a7070;text-align:left;padding:0.4rem 0.4rem 0;line-height:1.55;display:flex;align-items:flex-start;gap:.4rem;margin-bottom:.5rem;}
      .pass-hint i{color:#d62828;font-size:1.05rem;margin-top:.2rem;flex-shrink:0;}
      /* strength bar */
      .strength-wrap{padding:0.3rem 0.4rem 0;margin-bottom:.5rem;}
      .strength-bar{height:4px;border-radius:99px;background:#f0e0e0;overflow:hidden;margin-bottom:0.3rem;}
      .strength-fill{height:100%;border-radius:99px;width:0%;transition:width .3s,background .3s;}
      .strength-label{font-size:1.1rem;color:#9a7070;text-align:left;}
      .register-card .btn{display:block;width:100%;margin-top:1.2rem;padding:1.5rem;font-size:1.6rem;font-weight:700;color:#fff;border-radius:0.8rem;background:linear-gradient(135deg,#d62828,#b01c1c);box-shadow:0 6px 22px rgba(214,40,40,0.22);cursor:pointer;transition:all .25s;font-family:'Plus Jakarta Sans',sans-serif;border:none;}
      .register-card .btn:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(214,40,40,0.30);}
   </style>
</head>
<body>
<?php include '../components/admin_header.php'; ?>
<div class="register-section">
   <div class="rs-c1"></div><div class="rs-c2"></div>
   <div class="rs-dot rs-d1"></div><div class="rs-dot rs-d2"></div><div class="rs-dot rs-d3"></div>
   <div class="rs-dot rs-d4"></div><div class="rs-dot rs-d5"></div><div class="rs-dot rs-d6"></div>
   <div class="rs-line rs-l1"></div><div class="rs-line rs-l2"></div>
   <div class="rs-line rs-l3"></div><div class="rs-line rs-l4"></div>

   <div class="register-card">
      <div class="reg-icon">🛡️</div>
      <h3>Register New Admin</h3>
      <p class="subtitle">Create a new admin account</p>
      <form action="" method="POST">
         <div class="input-wrap">
            <input type="text" name="name" placeholder="Enter username" maxlength="20" class="box" required oninput="this.value=this.value.replace(/\s/g,'')">
         </div>
         <div class="input-wrap">
            <input type="password" name="pass" id="ap1" placeholder="Enter password (min. 8 chars)" class="box" required oninput="checkStrength(this.value)">
            <button type="button" class="eye-btn" onclick="toggleEye('ap1','ae1')"><i class="fas fa-eye" id="ae1"></i></button>
         </div>
         <div class="strength-wrap">
            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
            <div class="strength-label" id="strengthLabel"></div>
         </div>
         <div class="pass-hint"><i class="fas fa-info-circle"></i> Min. 8 chars with uppercase, lowercase, number & special character</div>
         <div class="input-wrap">
            <input type="password" name="c_pass" id="ap2" placeholder="Confirm password" class="box" required>
            <button type="button" class="eye-btn" onclick="toggleEye('ap2','ae2')"><i class="fas fa-eye" id="ae2"></i></button>
         </div>
         <input type="submit" value="Register Now →" name="submit" class="btn">
      </form>
   </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php'; ?>
<script>
function toggleEye(inputId,iconId){
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