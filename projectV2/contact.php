<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

// Fetch user info for nav
$nav_user_name = 'Guest';
$nav_user_initial = 'G';
$nav_saved_count = 0;
if($user_id != ''){
   $sel_nav_user = $conn->prepare("SELECT name FROM `users` WHERE id = ? LIMIT 1");
   $sel_nav_user->execute([$user_id]);
   $nav_u = $sel_nav_user->fetch(PDO::FETCH_ASSOC);
   if($nav_u){
      $nav_user_name = $nav_u['name'];
      $nav_user_initial = strtoupper(substr($nav_u['name'], 0, 1));
   }
   $sel_nav_saved = $conn->prepare("SELECT COUNT(*) as cnt FROM `saved` WHERE user_id = ?");
   $sel_nav_saved->execute([$user_id]);
   $nav_saved_count = $sel_nav_saved->fetch(PDO::FETCH_ASSOC)['cnt'];
}

// Handle contact form
if(isset($_POST['send'])){
   $msg_id = create_unique_id();
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

   $verify_contact = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $verify_contact->execute([$name, $email, $number, $message]);

   if($verify_contact->rowCount() > 0){
      $warning_msg[] = 'Message already sent!';
   }else{
      $send_message = $conn->prepare("INSERT INTO `messages`(id, name, email, number, message) VALUES(?,?,?,?,?)");
      $send_message->execute([$msg_id, $name, $email, $number, $message]);
      $success_msg[] = 'Message sent successfully!';
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us — MyEstate</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<style>
:root{
  --r:#d62828;--rd:#9e1c1c;--rl:#e85555;
  --rp:#fdf1f1;--rp2:#fae6e6;--rp3:#f5d0d0;
  --ink:#1a0505;--ink2:#4a1515;--ink3:#9a6565;
  --white:#fff;--bg:#faf5f5;
  --line:rgba(214,40,40,.13);
  --ease:cubic-bezier(.22,1,.36,1);
  --sh:0 4px 32px rgba(214,40,40,.08);
  --sh2:0 24px 72px rgba(214,40,40,.2);
  --nav-h:7rem;
}
*{margin:0;padding:0;box-sizing:border-box;}
html{font-size:62.5%;scroll-behavior:smooth;}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--ink);overflow-x:hidden;}
::-webkit-scrollbar{width:3px;}
::-webkit-scrollbar-thumb{background:var(--r);}
a{text-decoration:none;}

/* NAV */
.nav{position:fixed;top:0;left:0;right:0;z-index:999;height:var(--nav-h);padding:0 6%;display:flex;align-items:center;justify-content:space-between;background:rgba(253,241,241,.94);backdrop-filter:blur(24px);border-bottom:1px solid var(--line);transition:box-shadow .3s;}
.nav.scrolled{box-shadow:0 4px 40px rgba(214,40,40,.1);}
.logo{font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:700;color:var(--ink);}
.logo span{font-style:italic;color:var(--r);}
.nav-center{display:flex;gap:3rem;}
.nav-center a{font-size:1.3rem;font-weight:600;color:var(--ink3);transition:color .2s;position:relative;padding-bottom:.3rem;}
.nav-center a::after{content:'';position:absolute;bottom:0;left:0;width:0;height:1.5px;background:var(--r);transition:width .3s var(--ease);}
.nav-center a:hover,.nav-center a.active{color:var(--r);}
.nav-center a:hover::after,.nav-center a.active::after{width:100%;}
.nav-right{display:flex;align-items:center;gap:1.4rem;}
.nav-icon{width:4.2rem;height:4.2rem;border-radius:50%;border:1.5px solid var(--line);background:var(--white);display:grid;place-items:center;font-size:1.5rem;color:var(--ink3);transition:all .22s;position:relative;}
.nav-icon:hover{border-color:var(--r);color:var(--r);background:var(--rp);}
.nav-badge{position:absolute;top:-.3rem;right:-.3rem;width:1.6rem;height:1.6rem;border-radius:50%;background:var(--r);color:#fff;font-size:.75rem;font-weight:800;display:grid;place-items:center;border:2px solid var(--bg);}
.nav-user{display:flex;align-items:center;gap:1rem;padding:.7rem 1.6rem .7rem .7rem;border:1.5px solid var(--line);border-radius:99px;background:var(--white);cursor:pointer;transition:all .22s;position:relative;}
.nav-user:hover{border-color:var(--r);background:var(--rp);}
.nav-av{width:3.4rem;height:3.4rem;border-radius:50%;background:linear-gradient(135deg,var(--r),var(--rd));display:grid;place-items:center;font-size:1.4rem;font-weight:800;color:#fff;flex-shrink:0;}
.nav-drop-menu{display:none;position:absolute;top:calc(100% + 1rem);right:0;background:var(--white);border-radius:1.6rem;border:1.5px solid var(--line);box-shadow:0 20px 60px rgba(214,40,40,.15);padding:.8rem;min-width:20rem;z-index:100;}
.nav-drop-menu.open{display:block;}
.nd-item{display:flex;align-items:center;gap:1rem;padding:1.1rem 1.4rem;border-radius:1rem;font-size:1.3rem;color:var(--ink2);transition:all .18s;}
.nd-item i{width:2rem;text-align:center;color:var(--ink3);font-size:1.2rem;}
.nd-item:hover{background:var(--rp);color:var(--r);}
.nd-item:hover i{color:var(--r);}
.nd-sep{height:1px;background:var(--line);margin:.5rem 0;}
.nd-danger{color:#c0392b!important;}
.nd-danger i{color:#c0392b!important;}
.nd-danger:hover{background:#fff5f5!important;}
.nav-guest{display:flex;gap:1rem;align-items:center;}
.nav-btn{padding:.9rem 2rem;border-radius:99px;font-size:1.3rem;font-weight:700;font-family:'Outfit',sans-serif;transition:all .25s;}
.nav-btn.ghost{background:rgba(214,40,40,.08);color:var(--r);border:1.5px solid rgba(214,40,40,.2);}
.nav-btn.ghost:hover{background:rgba(214,40,40,.15);}
.nav-btn.solid{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;box-shadow:0 4px 14px rgba(214,40,40,.3);border:none;}
.nav-btn.solid:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(214,40,40,.38);}

/* PAGE */
.page-hero{padding-top:var(--nav-h);background:linear-gradient(145deg,#fff9f9 0%,#fdf1f1 45%,#fae8e8 100%);position:relative;overflow:hidden;}
.page-hero-inner{max-width:130rem;margin:0 auto;padding:5rem 6% 4rem;}
.eyebrow{display:inline-flex;align-items:center;gap:.5rem;font-size:.9rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--r);background:var(--rp);padding:.35rem 1rem;border-radius:99px;border:1px solid rgba(214,40,40,.12);width:fit-content;margin-bottom:1.2rem;}
.eyebrow::before{content:'';width:.4rem;height:.4rem;border-radius:50%;background:var(--r);animation:blink 2s infinite;flex-shrink:0;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
.page-title{font-family:'Cormorant Garamond',serif;font-size:clamp(4rem,5vw,6.5rem);font-weight:700;color:var(--ink);letter-spacing:-.03em;line-height:.92;}
.page-title em{font-style:italic;color:var(--r);}

/* CONTACT SECTION */
.contact-sec{max-width:130rem;margin:0 auto;padding:5rem 6% 8rem;display:grid;grid-template-columns:1fr 1fr;gap:5rem;align-items:start;}

/* FORM SIDE */
.contact-form{background:var(--white);border-radius:2.4rem;padding:4rem;border:1.5px solid var(--line);box-shadow:0 16px 56px rgba(214,40,40,.1);}
.cf-title{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);margin-bottom:.5rem;}
.cf-sub{font-size:1.3rem;color:var(--ink3);margin-bottom:2.5rem;}
.cf-field{display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.4rem;}
.cf-field label{font-size:1.15rem;font-weight:600;color:var(--ink2);}
.cf-field input,.cf-field textarea{padding:1.2rem 1.6rem;border:1.5px solid var(--line);border-radius:1.4rem;font-size:1.3rem;font-family:'Outfit',sans-serif;color:var(--ink);background:var(--rp);outline:none;transition:all .2s;}
.cf-field input:focus,.cf-field textarea:focus{border-color:rgba(214,40,40,.4);background:var(--white);}
.cf-field textarea{resize:vertical;min-height:12rem;}
.cf-btn{width:100%;padding:1.4rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;font-size:1.45rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 8px 24px rgba(214,40,40,.28);transition:all .25s;display:flex;align-items:center;justify-content:center;gap:.8rem;margin-top:.5rem;}
.cf-btn:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(214,40,40,.38);}

/* INFO SIDE */
.contact-info{display:flex;flex-direction:column;gap:2rem;}
.ci-card{background:var(--white);border-radius:2rem;padding:2.5rem;border:1.5px solid var(--line);display:flex;align-items:flex-start;gap:1.8rem;transition:all .3s var(--ease);}
.ci-card:hover{transform:translateY(-4px);box-shadow:var(--sh2);}
.ci-icon{width:5rem;height:5rem;border-radius:1.3rem;background:var(--rp);display:grid;place-items:center;font-size:2rem;color:var(--r);flex-shrink:0;}
.ci-label{font-size:1rem;font-weight:600;color:var(--ink3);letter-spacing:.1em;text-transform:uppercase;margin-bottom:.3rem;}
.ci-value{font-size:1.5rem;font-weight:700;color:var(--ink);line-height:1.4;}
.ci-sub{font-size:1.15rem;color:var(--ink3);margin-top:.2rem;}
.ci-map{border-radius:2rem;overflow:hidden;height:28rem;border:1.5px solid var(--line);}
.ci-map iframe{width:100%;height:100%;border:none;}

/* FOOTER */
.footer{background:linear-gradient(135deg,#fff0f0,#fde0e0);border-top:1px solid var(--line);padding:6rem 6% 3.5rem;}
.foot-grid{display:grid;grid-template-columns:2.2fr 1fr 1fr 1.3fr;gap:5rem;padding-bottom:4rem;border-bottom:1px solid var(--line);max-width:130rem;margin:0 auto;}
.foot-logo{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);display:block;margin-bottom:1.2rem;}
.foot-logo span{font-style:italic;color:var(--r);}
.foot-brand p{font-size:1.3rem;color:var(--ink3);line-height:1.7;margin-bottom:2rem;max-width:26rem;}
.foot-socials{display:flex;gap:.9rem;}
.fsc{width:3.8rem;height:3.8rem;border-radius:50%;border:1.5px solid var(--line);background:var(--white);display:grid;place-items:center;color:var(--ink3);font-size:1.4rem;transition:all .2s;}
.fsc:hover{border-color:var(--r);color:var(--r);background:var(--rp);transform:translateY(-3px);}
.foot-col h4{font-size:1.05rem;font-weight:700;color:var(--ink);letter-spacing:.14em;text-transform:uppercase;margin-bottom:1.6rem;}
.foot-col a{display:flex;align-items:center;gap:.6rem;font-size:1.25rem;color:var(--ink3);margin-bottom:.95rem;transition:all .2s;}
.foot-col a i{font-size:.9rem;color:rgba(214,40,40,.28);}
.foot-col a:hover{color:var(--r);padding-left:.4rem;}
.foot-col a:hover i{color:var(--r);}
.fci{display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.3rem;}
.fci-ic{width:3.4rem;height:3.4rem;border-radius:.8rem;background:var(--white);border:1px solid var(--line);display:grid;place-items:center;color:var(--r);font-size:1.3rem;flex-shrink:0;}
.fci-t{font-size:1.25rem;color:var(--ink3);line-height:1.5;}
.fci-t strong{display:block;font-size:1.05rem;font-weight:700;color:var(--ink);margin-bottom:.15rem;}
.foot-bot{display:flex;align-items:center;justify-content:space-between;padding-top:2.5rem;max-width:130rem;margin:0 auto;flex-wrap:wrap;gap:1.2rem;}
.foot-copy{font-size:1.2rem;color:var(--ink3);}
.foot-copy span{color:var(--r);font-weight:700;}
.foot-bot-links{display:flex;gap:2rem;}
.foot-bot-links a{font-size:1.2rem;color:var(--ink3);transition:color .2s;}
.foot-bot-links a:hover{color:var(--r);}

@media(max-width:1100px){.foot-grid{grid-template-columns:1fr 1fr;gap:3rem;}.contact-sec{grid-template-columns:1fr;}}
@media(max-width:768px){
  .nav-center{display:none;}
  .page-hero-inner{padding:3.5rem 5% 3rem;}
  .contact-sec{padding:3rem 5% 6rem;}
  .contact-form{padding:2.5rem;}
  .foot-grid{grid-template-columns:1fr;}
  .foot-bot{flex-direction:column;}
}
</style>
</head>
<body>

<nav class="nav" id="mainNav">
  <a href="home.php" class="logo">My<span>Estate</span></a>
  <div class="nav-center">
    <a href="home.php">Home</a>
    <a href="listings.php">Properties</a>
    <a href="home.php#upSec">Upcoming</a>
    <a href="about.php">About</a>
    <a href="contact.php" class="active">Contact</a>
  </div>
  <div class="nav-right">
    <?php if($user_id != ''){ ?>
    <a href="saved.php" class="nav-icon"><i class="fas fa-heart"></i><?php if($nav_saved_count > 0){ ?><span class="nav-badge"><?= $nav_saved_count; ?></span><?php } ?></a>
    <div class="nav-user" id="navUser">
      <div class="nav-av"><?= $nav_user_initial; ?></div>
      <span style="font-size:1.3rem;font-weight:700;color:var(--ink);"><?= htmlspecialchars($nav_user_name); ?></span>
      <i class="fas fa-chevron-down" style="font-size:1rem;color:var(--ink3);margin-left:.4rem;"></i>
      <div class="nav-drop-menu" id="navDropMenuContact">
        <a href="saved.php" class="nd-item"><i class="fas fa-heart"></i>Saved Properties</a>
        <a href="requests.php" class="nd-item"><i class="fas fa-file-alt"></i>My Requests</a>
        <div class="nd-sep"></div>
        <a href="home.php#agentSec" class="nd-item" style="color:var(--r);font-weight:700;"><i class="fas fa-user-tie" style="color:var(--r);"></i>Become an Agent</a>
        <div class="nd-sep"></div>
        <a href="update.php" class="nd-item"><i class="fas fa-user-edit"></i>Edit Profile</a>
        <div class="nd-sep"></div>
        <a href="javascript:void(0)" onclick="confirmLogout()" class="nd-item nd-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
      </div>
    </div>
    <?php }else{ ?>
    <div class="nav-guest">
      <a href="login.php" class="nav-btn ghost">Login</a>
      <a href="register.php" class="nav-btn solid">Get Started</a>
    </div>
    <?php } ?>
  </div>
</nav>

<section class="page-hero">
  <div class="page-hero-inner">
    <div class="eyebrow">Get in Touch</div>
    <h1 class="page-title">Send Us a <em>Message</em></h1>
  </div>
</section>

<div class="contact-sec">
  <div class="contact-form">
    <h2 class="cf-title">Send a <span style="color:var(--r);font-style:italic;">Message</span></h2>
    <p class="cf-sub">Fill in the form below and our team will respond within 24 hours.</p>
    <form action="" method="post">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.4rem;">
        <div class="cf-field"><label>Your Name *</label><input type="text" name="name" required maxlength="50" placeholder="Enter your full name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ($user_id != '' ? htmlspecialchars($nav_user_name) : ''); ?>"></div>
        <div class="cf-field"><label>Email Address *</label><input type="email" name="email" required maxlength="50" placeholder="you@email.com"></div>
      </div>
      <div class="cf-field"><label>Phone Number *</label><input type="number" name="number" required maxlength="10" placeholder="10-digit mobile number"></div>
      <div class="cf-field"><label>Your Message *</label><textarea name="message" required maxlength="1000" placeholder="Tell us what you're looking for..." style="resize:none;min-height:14rem;"></textarea></div>
      <button type="submit" name="send" class="cf-btn"><i class="fas fa-paper-plane"></i> Send Message</button>
    </form>
  </div>

  <div class="contact-info">
    <div class="ci-card">
      <div class="ci-icon"><i class="fas fa-map-marker-alt"></i></div>
      <div>
        <div class="ci-label">Office Address</div>
        <div class="ci-value">Nalasopara West, Mumbai</div>
        <div class="ci-sub">Maharashtra — 401203, India</div>
      </div>
    </div>
    <div class="ci-card">
      <div class="ci-icon"><i class="fas fa-envelope"></i></div>
      <div>
        <div class="ci-label">Email</div>
        <div class="ci-value">rayyanbhagate@gmail.com</div>
        <div class="ci-sub">We reply within 24 hours</div>
      </div>
    </div>
    <div class="ci-card">
      <div class="ci-icon"><i class="fas fa-clock"></i></div>
      <div>
        <div class="ci-label">Working Hours</div>
        <div class="ci-value">Mon – Sat: 9 AM – 7 PM</div>
        <div class="ci-sub">Sunday: 10 AM – 4 PM</div>
      </div>
    </div>
    <div class="ci-map">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3767.5!2d72.7785!3d19.4551!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7a9b1b1b1b1b1%3A0x0!2sNalasopara+West%2C+Maharashtra+401203!5e0!3m2!1sen!2sin!4v1" allowfullscreen="" loading="lazy"></iframe>
    </div>
  </div>
</div>

<footer class="footer" id="footer">
  <div class="foot-grid">
    <div class="foot-brand"><a href="home.php" class="foot-logo">My<span>Estate</span></a><p>Trusted real estate across Mumbai & Pune.</p><div class="foot-socials"><a href="#" class="fsc"><i class="fab fa-instagram"></i></a><a href="#" class="fsc"><i class="fab fa-facebook-f"></i></a><a href="#" class="fsc"><i class="fab fa-twitter"></i></a><a href="#" class="fsc"><i class="fab fa-youtube"></i></a></div></div>
    <div class="foot-col"><h4>Properties</h4><a href="listings.php"><i class="fas fa-chevron-right"></i>All Listings</a><a href="search.php"><i class="fas fa-chevron-right"></i>Search</a></div>
    <div class="foot-col"><h4>Quick Links</h4><a href="home.php"><i class="fas fa-chevron-right"></i>Dashboard</a><a href="about.php"><i class="fas fa-chevron-right"></i>About Us</a></div>
    <div class="foot-col"><h4>Contact Us</h4><div class="fci"><div class="fci-ic"><i class="fas fa-map-marker-alt"></i></div><div class="fci-t"><strong>Office</strong>Nalasopara West, Maharashtra — 401203</div></div><div class="fci"><div class="fci-ic"><i class="fas fa-envelope"></i></div><div class="fci-t"><strong>Email</strong>rayyanbhagate@gmail.com</div></div></div>
  </div>
  <div class="foot-bot"><p class="foot-copy">© <?= date('Y'); ?> <span>MyEstate</span>. Made with ♥ in Mumbai.</p><div class="foot-bot-links"><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Cookies</a></div></div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/message.php'; ?>

<script>
window.addEventListener('scroll', () => {
  document.getElementById('mainNav').classList.toggle('scrolled', scrollY > 40);
});
// Click-based nav dropdown
const navUser = document.getElementById('navUser');
if(navUser){
  const menu = document.getElementById('navDropMenuContact');
  navUser.addEventListener('click', function(e){ e.stopPropagation(); menu.classList.toggle('open'); });
  menu.addEventListener('click', function(e){ e.stopPropagation(); });
  document.addEventListener('click', function(e){ if(!navUser.contains(e.target)) menu.classList.remove('open'); });
  window.addEventListener('scroll', function(){ menu.classList.remove('open'); }, {passive:true});
}
// Logout confirmation
function confirmLogout(){
  swal({ title: 'Logout?', text: 'Are you sure you want to logout?', icon: 'warning',
    buttons: ['Cancel', 'Logout'], dangerMode: true
  }).then(ok => { if(ok) window.location = 'components/user_logout.php'; });
}
</script>
</body>
</html>
