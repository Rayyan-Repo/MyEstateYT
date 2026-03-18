<?php

include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
}

if(isset($_POST['submit'])){

   $id = create_unique_id();
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING); 
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING); 
   $c_pass = sha1($_POST['c_pass']);
   $c_pass = filter_var($c_pass, FILTER_SANITIZE_STRING);   

   $select_admins = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
   $select_admins->execute([$name]);

   if($select_admins->rowCount() > 0){
      $warning_msg[] = 'Username already taken!';
   }else{
      if($pass != $c_pass){
         $warning_msg[] = 'Password not matched!';
      }else{
         $insert_admin = $conn->prepare("INSERT INTO `admins`(id, name, password) VALUES(?,?,?)");
         $insert_admin->execute([$id, $name, $c_pass]);
         $success_msg[] = 'Registered successfully!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      .register-section{
         min-height: calc(100vh - 6rem);
         display: flex;
         align-items: center;
         justify-content: center;
         position: relative;
         overflow: hidden;
         background: #fff;
         padding: 4rem 2rem;
      }

      /* circles */
      .register-section::before{
         content:'';
         position:absolute;
         width:55rem; height:55rem; border-radius:50%;
         border:2px solid rgba(214,40,40,0.42);
         top:-20rem; right:-18rem; pointer-events:none;
      }
      .register-section::after{
         content:'';
         position:absolute;
         width:35rem; height:35rem; border-radius:50%;
         border:2px solid rgba(214,40,40,0.32);
         top:-8rem; right:-6rem; pointer-events:none;
      }
      .rs-c1{position:absolute;width:44rem;height:44rem;border-radius:50%;border:2px solid rgba(214,40,40,0.38);bottom:-18rem;left:-15rem;pointer-events:none;}
      .rs-c2{position:absolute;width:24rem;height:24rem;border-radius:50%;border:1.5px solid rgba(214,40,40,0.28);bottom:-6rem;left:-4rem;pointer-events:none;}
      .rs-c3{position:absolute;width:22rem;height:22rem;border-radius:50%;border:1.5px solid rgba(214,40,40,0.32);top:50%;left:4rem;transform:translateY(-50%);pointer-events:none;}
      .rs-c4{position:absolute;width:22rem;height:22rem;border-radius:50%;border:1.5px solid rgba(214,40,40,0.32);top:50%;right:4rem;transform:translateY(-50%);pointer-events:none;}

      /* dots */
      .rs-dot{position:absolute;width:0.75rem;height:0.75rem;border-radius:50%;background:rgba(214,40,40,0.45);pointer-events:none;}
      .rs-d1{top:5rem;left:5rem;} .rs-d2{top:5rem;left:7rem;} .rs-d3{top:5rem;left:9rem;}
      .rs-d4{bottom:5rem;right:5rem;} .rs-d5{bottom:5rem;right:7rem;} .rs-d6{bottom:5rem;right:9rem;}

      /* lines */
      .rs-line{position:absolute;height:1.5px;background:linear-gradient(90deg,transparent,rgba(214,40,40,0.38),transparent);pointer-events:none;}
      .rs-l1{width:9rem;top:9rem;left:3rem;transform:rotate(-45deg);}
      .rs-l2{width:6rem;top:11rem;left:5rem;transform:rotate(-45deg);}
      .rs-l3{width:9rem;bottom:9rem;right:3rem;transform:rotate(-45deg);}
      .rs-l4{width:6rem;bottom:11rem;right:5rem;transform:rotate(-45deg);}

      /* card */
      .register-card{
         width: 46rem;
         border-radius: 2rem;
         padding: 4rem 3.8rem;
         background: #fff;
         position: relative;
         z-index: 1;
         text-align: center;
         box-shadow:
            0 0 0 1.5px rgba(214,40,40,0.25),
            0 8px 32px rgba(214,40,40,0.14),
            0 2px 8px rgba(214,40,40,0.08);
         animation: regAppear .55s cubic-bezier(.34,1.56,.64,1) both;
      }

      @keyframes regAppear{
         from{opacity:0;transform:translateY(28px) scale(.97)}
         to{opacity:1;transform:translateY(0) scale(1)}
      }

      .register-card::before{
         content:'';
         position:absolute;
         top:0; left:8%; right:8%; height:3.5px;
         background:linear-gradient(90deg,transparent,#d62828,#b01c1c,transparent);
         border-radius:0 0 99px 99px;
      }

      .reg-icon{
         display:inline-flex; align-items:center; justify-content:center;
         width:6.5rem; height:6.5rem;
         background:linear-gradient(135deg,#d62828,#b01c1c);
         border-radius:1.4rem; font-size:2.6rem; margin-bottom:1.8rem;
         box-shadow:0 8px 24px rgba(214,40,40,0.22);
      }

      .register-card h3{
         font-family:'Syne',sans-serif;
         font-size:2.4rem; font-weight:800;
         color:#1a0a0a; letter-spacing:-0.03em;
         margin-bottom:0.5rem;
      }

      .register-card .subtitle{
         font-size:1.4rem; color:#9a7070; margin-bottom:2rem;
      }

      .register-card .box{
         width:100%; border-radius:0.8rem;
         padding:1.4rem 1.8rem; font-size:1.55rem;
         color:#1a0a0a; background:#fafafa;
         border:1.5px solid rgba(214,40,40,0.15) !important;
         margin:0.7rem 0; transition:all .25s ease;
         font-family:'Plus Jakarta Sans',sans-serif; display:block;
      }
      .register-card .box::placeholder{ color:#9a7070; }
      .register-card .box:focus{
         border-color:#d62828 !important;
         background:#fff;
         box-shadow:0 0 0 3px rgba(214,40,40,0.10);
         outline:none;
      }

      .register-card .btn{
         display:block; width:100%; margin-top:1.2rem; padding:1.5rem;
         font-size:1.6rem; font-weight:700; color:#fff; border-radius:0.8rem;
         background:linear-gradient(135deg,#d62828,#b01c1c);
         box-shadow:0 6px 22px rgba(214,40,40,0.22); cursor:pointer;
         transition:all .25s ease; letter-spacing:0.04em;
         font-family:'Plus Jakarta Sans',sans-serif; border:none;
      }
      .register-card .btn:hover{
         transform:translateY(-2px);
         box-shadow:0 10px 30px rgba(214,40,40,0.30);
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<!-- register section starts  -->
<div class="register-section">

   <!-- shapes -->
   <div class="rs-c1"></div>
   <div class="rs-c2"></div>
   <div class="rs-c3"></div>
   <div class="rs-c4"></div>
   <div class="rs-dot rs-d1"></div>
   <div class="rs-dot rs-d2"></div>
   <div class="rs-dot rs-d3"></div>
   <div class="rs-dot rs-d4"></div>
   <div class="rs-dot rs-d5"></div>
   <div class="rs-dot rs-d6"></div>
   <div class="rs-line rs-l1"></div>
   <div class="rs-line rs-l2"></div>
   <div class="rs-line rs-l3"></div>
   <div class="rs-line rs-l4"></div>

   <div class="register-card">
      <div class="reg-icon">🛡️</div>
      <h3>Register New Admin</h3>
      <p class="subtitle">Create a new admin account</p>
      <form action="" method="POST">
         <input type="text" name="name" placeholder="Enter username" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="pass" placeholder="Enter password" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="c_pass" placeholder="Confirm password" maxlength="20" class="box" required oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="submit" value="Register Now  →" name="submit" class="btn">
      </form>
   </div>

</div>
<!-- register section ends -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php'; ?>

</body>
</html>
