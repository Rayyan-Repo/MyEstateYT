<?php

include '../components/connect.php';

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING); 
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING); 

   $select_admins = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND password = ? LIMIT 1");
   $select_admins->execute([$name, $pass]);
   $row = $select_admins->fetch(PDO::FETCH_ASSOC);

   if($select_admins->rowCount() > 0){
      setcookie('admin_id', $row['id'], time() + 60*60*24*30, '/');
      header('location:dashboard.php');
   }else{
      $warning_msg[] = 'Incorrect username or password!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Login</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
   <style>
      :root {
         --red: #d62828;
         --red-dark: #b01c1c;
         --red-glow: rgba(214,40,40,0.22);
         --text-dark: #1a0a0a;
         --text-light: #9a7070;
         --radius-sm: 0.8rem;
         --ease: cubic-bezier(0.4,0,0.2,1);
      }

      * { font-family:'Plus Jakarta Sans',sans-serif; margin:0; padding:0; box-sizing:border-box; outline:none; border:none; text-decoration:none; }
      html { font-size:62.5%; }

      body {
         padding-left:0 !important;
         min-height:100vh;
         background:#fff;
         display:flex; align-items:center; justify-content:center;
         position:relative; overflow:hidden;
      }

      /* ── CIRCLES ── */
      .shape-c1{position:absolute;width:50rem;height:50rem;border-radius:50%;border:2px solid rgba(214,40,40,0.32);top:-18rem;right:-16rem;}
      .shape-c2{position:absolute;width:30rem;height:30rem;border-radius:50%;border:1.5px solid rgba(214,40,40,0.22);top:-5rem;right:-3rem;}
      .shape-c3{position:absolute;width:40rem;height:40rem;border-radius:50%;border:2px solid rgba(214,40,40,0.28);bottom:-16rem;left:-14rem;}
      .shape-c4{position:absolute;width:22rem;height:22rem;border-radius:50%;border:1.5px solid rgba(214,40,40,0.20);bottom:-4rem;left:-3rem;}
      .shape-c5{position:absolute;width:20rem;height:20rem;border-radius:50%;border:1.5px solid rgba(214,40,40,0.22);top:50%;right:5rem;transform:translateY(-50%);}
      .shape-c6{position:absolute;width:11rem;height:11rem;border-radius:50%;border:1px solid rgba(214,40,40,0.16);top:55%;right:8.5rem;transform:translateY(-50%);}
      .shape-c7{position:absolute;width:20rem;height:20rem;border-radius:50%;border:1.5px solid rgba(214,40,40,0.22);top:50%;left:5rem;transform:translateY(-50%);}
      .shape-c8{position:absolute;width:11rem;height:11rem;border-radius:50%;border:1px solid rgba(214,40,40,0.16);top:55%;left:8.5rem;transform:translateY(-50%);}
      .shape-c9{position:absolute;width:14rem;height:14rem;border-radius:50%;border:1px solid rgba(214,40,40,0.18);top:3rem;left:30%;}
      .shape-c10{position:absolute;width:8rem;height:8rem;border-radius:50%;border:1px solid rgba(214,40,40,0.13);top:5rem;left:32%;}
      .shape-c11{position:absolute;width:14rem;height:14rem;border-radius:50%;border:1px solid rgba(214,40,40,0.18);bottom:3rem;right:28%;}
      .shape-c12{position:absolute;width:8rem;height:8rem;border-radius:50%;border:1px solid rgba(214,40,40,0.13);bottom:5rem;right:30%;}

      /* ── DOTS ── */
      .dot{position:absolute;width:0.75rem;height:0.75rem;border-radius:50%;background:rgba(214,40,40,0.40);}
      .dot-1{top:8rem;left:8rem;} .dot-2{top:8rem;left:10.5rem;} .dot-3{top:8rem;left:13rem;}
      .dot-4{top:10.5rem;left:8rem;} .dot-5{top:13rem;left:8rem;}
      .dot-6{bottom:8rem;right:8rem;} .dot-7{bottom:8rem;right:10.5rem;} .dot-8{bottom:8rem;right:13rem;}
      .dot-9{bottom:10.5rem;right:8rem;} .dot-10{bottom:13rem;right:8rem;}
      .dot-11{top:8rem;right:8rem;} .dot-12{top:8rem;right:10.5rem;} .dot-13{top:8rem;right:13rem;}
      .dot-14{top:10.5rem;right:8rem;} .dot-15{top:13rem;right:8rem;}
      .dot-16{bottom:8rem;left:8rem;} .dot-17{bottom:8rem;left:10.5rem;} .dot-18{bottom:8rem;left:13rem;}
      .dot-19{bottom:10.5rem;left:8rem;} .dot-20{bottom:13rem;left:8rem;}

      /* ── LINES ── */
      .line{position:absolute;height:1.5px;background:linear-gradient(90deg,transparent,rgba(214,40,40,0.30),transparent);}
      .line-1{width:9rem;top:19rem;left:3rem;transform:rotate(-45deg);}
      .line-2{width:6rem;top:21.5rem;left:5rem;transform:rotate(-45deg);}
      .line-3{width:9rem;bottom:19rem;right:3rem;transform:rotate(-45deg);}
      .line-4{width:6rem;bottom:21.5rem;right:5rem;transform:rotate(-45deg);}
      .line-5{width:9rem;top:19rem;right:3rem;transform:rotate(45deg);}
      .line-6{width:6rem;top:21.5rem;right:5rem;transform:rotate(45deg);}
      .line-7{width:9rem;bottom:19rem;left:3rem;transform:rotate(45deg);}
      .line-8{width:6rem;bottom:21.5rem;left:5rem;transform:rotate(45deg);}

      /* ── LOGIN CARD ── */
      .form-container {
         width:46rem; border-radius:2rem; padding:4.5rem 3.8rem;
         background:#fff; position:relative; z-index:1; text-align:center;
         box-shadow:0 0 0 1.5px rgba(214,40,40,0.25), 0 8px 32px rgba(214,40,40,0.14), 0 2px 8px rgba(214,40,40,0.08);
         animation:loginAppear .55s cubic-bezier(.34,1.56,.64,1) both;
      }
      @keyframes loginAppear{from{opacity:0;transform:translateY(28px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}

      .form-container::before {
         content:''; position:absolute; top:0; left:8%; right:8%; height:3.5px;
         background:linear-gradient(90deg,transparent,var(--red),var(--red-dark),transparent);
         border-radius:0 0 99px 99px;
      }

      .login-logo {
         display:inline-flex; align-items:center; justify-content:center;
         width:7rem; height:7rem;
         background:linear-gradient(135deg,var(--red),var(--red-dark));
         border-radius:1.6rem; font-size:3rem; margin-bottom:2rem;
         box-shadow:0 8px 24px var(--red-glow);
      }

      .form-container h3 {
         font-family:'Syne',sans-serif; font-size:2.8rem; font-weight:800;
         color:var(--text-dark); letter-spacing:-.03em; margin-bottom:2.5rem;
      }

      .box {
         width:100%; border-radius:var(--radius-sm); padding:1.5rem 1.8rem;
         font-size:1.6rem; color:var(--text-dark); background:#fafafa;
         border:1.5px solid rgba(214,40,40,0.15) !important;
         margin:.8rem 0; transition:all .25s var(--ease);
         font-family:'Plus Jakarta Sans',sans-serif; display:block;
      }
      .box::placeholder{color:var(--text-light);}
      .box:focus{border-color:var(--red) !important;background:#fff;box-shadow:0 0 0 3px rgba(214,40,40,0.10);}

      .btn {
         display:block; width:100%; margin-top:1.5rem; padding:1.6rem;
         font-size:1.6rem; font-weight:700; color:#fff; border-radius:var(--radius-sm);
         background:linear-gradient(135deg,var(--red),var(--red-dark));
         box-shadow:0 6px 22px var(--red-glow); cursor:pointer;
         transition:all .25s var(--ease); letter-spacing:.04em;
         font-family:'Plus Jakarta Sans',sans-serif;
      }
      .btn:hover{transform:translateY(-2px);box-shadow:0 10px 30px var(--red-glow);}

      .footer-text{margin-top:2rem;font-size:1.25rem;color:var(--text-light);}
   </style>
</head>
<body>

   <!-- Circles -->
   <div class="shape-c1"></div><div class="shape-c2"></div>
   <div class="shape-c3"></div><div class="shape-c4"></div>
   <div class="shape-c5"></div><div class="shape-c6"></div>
   <div class="shape-c7"></div><div class="shape-c8"></div>
   <div class="shape-c9"></div><div class="shape-c10"></div>
   <div class="shape-c11"></div><div class="shape-c12"></div>

   <!-- Dots -->
   <div class="dot dot-1"></div><div class="dot dot-2"></div><div class="dot dot-3"></div>
   <div class="dot dot-4"></div><div class="dot dot-5"></div><div class="dot dot-6"></div>
   <div class="dot dot-7"></div><div class="dot dot-8"></div><div class="dot dot-9"></div>
   <div class="dot dot-10"></div><div class="dot dot-11"></div><div class="dot dot-12"></div>
   <div class="dot dot-13"></div><div class="dot dot-14"></div><div class="dot dot-15"></div>
   <div class="dot dot-16"></div><div class="dot dot-17"></div><div class="dot dot-18"></div>
   <div class="dot dot-19"></div><div class="dot dot-20"></div>

   <!-- Lines -->
   <div class="line line-1"></div><div class="line line-2"></div>
   <div class="line line-3"></div><div class="line line-4"></div>
   <div class="line line-5"></div><div class="line line-6"></div>
   <div class="line line-7"></div><div class="line line-8"></div>

   <!-- Card -->
   <div class="form-container">
      <div class="login-logo">🏠</div>
      <h3>Welcome Back!</h3>
      <form action="" method="POST">
         <input type="text" name="name" placeholder="Enter username" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="pass" placeholder="Enter password" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="submit" value="Login Now  →" name="submit" class="btn">
      </form>
      <p class="footer-text">Real Estate Admin Panel &copy; 2024</p>
   </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include '../components/message.php'; ?>

</body>
</html>