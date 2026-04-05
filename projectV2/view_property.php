<?php  
include 'components/connect.php';

$user_id = validate_user_cookie($conn);
if(!$user_id){
   setcookie('user_id', '', time() - 3600, '/');
   $user_id = '';
}

if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   $get_id = '';
   header('location:home.php');
   exit();
}

include 'components/save_send.php';

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

// Fetch property
$select_properties = $conn->prepare("SELECT * FROM `property` WHERE id = ? ORDER BY date DESC LIMIT 1");
$select_properties->execute([$get_id]);
if($select_properties->rowCount() == 0){
   header('location:home.php');
   exit();
}
$fetch_property = $select_properties->fetch(PDO::FETCH_ASSOC);
$property_id = $fetch_property['id'];

$select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_user->execute([$fetch_property['user_id']]);
$fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

$select_saved = $conn->prepare("SELECT * FROM `saved` WHERE property_id = ? and user_id = ?");
$select_saved->execute([$fetch_property['id'], $user_id]);
$is_saved = $select_saved->rowCount() > 0;

// Build images array
$images = [];
if(!empty($fetch_property['image_01'])) $images[] = $fetch_property['image_01'];
if(!empty($fetch_property['image_02'])) $images[] = $fetch_property['image_02'];
if(!empty($fetch_property['image_03'])) $images[] = $fetch_property['image_03'];
if(!empty($fetch_property['image_04'])) $images[] = $fetch_property['image_04'];
if(!empty($fetch_property['image_05'])) $images[] = $fetch_property['image_05'];

// Format price
$price_raw = $fetch_property['price'];
if($price_raw >= 10000000) $price_fmt = '₹' . round($price_raw / 10000000, 2) . ' Cr';
elseif($price_raw >= 100000) $price_fmt = '₹' . round($price_raw / 100000, 2) . ' L';
elseif($price_raw >= 1000) $price_fmt = '₹' . round($price_raw / 1000, 1) . 'K';
else $price_fmt = '₹' . number_format($price_raw);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($fetch_property['property_name']); ?> — MyEstate</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
<style>
:root{
  --r:#d62828;--rd:#9e1c1c;--rl:#e85555;
  --rp:#fdf1f1;--rp2:#fae6e6;--rp3:#f5d0d0;
  --ink:#1a0505;--ink2:#4a1515;--ink3:#9a6565;
  --white:#fff;--bg:#faf5f5;
  --line:rgba(214,40,40,.13);
  --ease:cubic-bezier(.22,1,.36,1);
  --sh:0 4px 32px rgba(214,40,40,.08);
  --sh2:0 28px 80px rgba(214,40,40,.2);
  --nav-h:7rem;
}
*{margin:0;padding:0;box-sizing:border-box;}
html{font-size:62.5%;scroll-behavior:smooth;}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--ink);overflow-x:hidden;}
::-webkit-scrollbar{width:3px;}
::-webkit-scrollbar-thumb{background:var(--r);}
a{text-decoration:none;}

.nav{position:fixed;top:0;left:0;right:0;z-index:999;height:var(--nav-h);padding:0 6%;display:flex;align-items:center;justify-content:space-between;background:rgba(253,241,241,.94);backdrop-filter:blur(24px);border-bottom:1px solid var(--line);transition:box-shadow .3s;}
.nav.scrolled{box-shadow:0 4px 40px rgba(214,40,40,.1);}
.logo{font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:700;color:var(--ink);text-decoration:none;}
.logo span{font-style:italic;color:var(--r);}
.nav-center{display:flex;gap:3rem;}
.nav-center a{font-size:1.3rem;font-weight:600;color:var(--ink3);text-decoration:none;transition:color .2s;position:relative;padding-bottom:.3rem;}
.nav-center a::after{content:'';position:absolute;bottom:0;left:0;width:0;height:1.5px;background:var(--r);transition:width .3s var(--ease);}
.nav-center a:hover,.nav-center a.active{color:var(--r);}
.nav-center a:hover::after,.nav-center a.active::after{width:100%;}
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
.nd-danger{color:#c0392b!important;}
.nd-danger i{color:#c0392b!important;}
.nd-danger:hover{background:#fff5f5!important;}
.nav-guest{display:flex;gap:1rem;align-items:center;}
.nav-btn{padding:.9rem 2rem;border-radius:99px;font-size:1.3rem;font-weight:700;font-family:'Outfit',sans-serif;transition:all .25s;}
.nav-btn.ghost{background:rgba(214,40,40,.08);color:var(--r);border:1.5px solid rgba(214,40,40,.2);}
.nav-btn.ghost:hover{background:rgba(214,40,40,.15);}
.nav-btn.solid{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;box-shadow:0 4px 14px rgba(214,40,40,.3);border:none;}
.nav-btn.solid:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(214,40,40,.38);}

.vp-section{padding-top:calc(var(--nav-h) + 3rem);max-width:120rem;margin:0 auto;padding-left:6%;padding-right:6%;padding-bottom:6rem;}

/* Gallery */
.vp-gallery{border-radius:2.4rem;overflow:hidden;margin-bottom:3.5rem;position:relative;}
.vp-gallery .swiper{width:100%;height:50rem;}
.vp-gallery .swiper-slide img{width:100%;height:100%;object-fit:cover;}
.vp-gallery .swiper-pagination-bullet-active{background:var(--r);}
.vp-img-count{position:absolute;top:2rem;right:2rem;background:rgba(0,0,0,.6);backdrop-filter:blur(12px);color:#fff;padding:.6rem 1.4rem;border-radius:99px;font-size:1.2rem;font-weight:700;z-index:10;display:flex;align-items:center;gap:.5rem;}

/* Layout — single full-width column under image */
.vp-layout{display:block;max-width:120rem;}
.vp-price-inline{background:var(--white);border-radius:2rem;border:1.5px solid var(--line);padding:2.4rem 2.8rem;margin-bottom:2.5rem;display:flex;align-items:center;justify-content:space-between;box-shadow:var(--sh);flex-wrap:wrap;gap:1.5rem;}
.vp-price-inline-left{}
.vp-price-inline-right{display:flex;gap:1rem;align-items:center;}

/* Main content */
.vp-main{}
.vp-header{margin-bottom:3rem;}
.vp-breadcrumb{display:flex;align-items:center;gap:.6rem;font-size:1.1rem;color:var(--ink3);margin-bottom:1.5rem;}
.vp-breadcrumb a{color:var(--r);text-decoration:none;font-weight:600;}
.vp-breadcrumb a:hover{text-decoration:underline;}
.vp-type-badge{display:inline-flex;align-items:center;gap:.5rem;background:var(--rp);border:1px solid rgba(214,40,40,.15);color:var(--r);padding:.4rem 1.2rem;border-radius:99px;font-size:1rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;margin-bottom:1rem;}
.vp-title{font-family:'Cormorant Garamond',serif;font-size:clamp(3.2rem,4vw,4.8rem);font-weight:700;color:var(--ink);line-height:1.05;margin-bottom:.8rem;}
.vp-addr{font-size:1.4rem;color:var(--ink3);display:flex;align-items:center;gap:.6rem;}
.vp-addr i{color:var(--r);}

/* Info pills */
.vp-pills{display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:3rem;}
.vp-pill{display:flex;align-items:center;gap:.6rem;background:var(--white);border:1.5px solid var(--line);padding:.8rem 1.6rem;border-radius:1.4rem;font-size:1.2rem;font-weight:600;color:var(--ink2);}
.vp-pill i{color:var(--r);font-size:1.1rem;}
.vp-pill b{color:var(--ink);}

/* Details grid */
.vp-card{background:var(--white);border-radius:2rem;border:1.5px solid var(--line);padding:2.8rem;margin-bottom:2.5rem;}
.vp-card-title{font-family:'Cormorant Garamond',serif;font-size:2.4rem;font-weight:700;color:var(--ink);margin-bottom:2rem;display:flex;align-items:center;gap:.8rem;}
.vp-card-title i{color:var(--r);font-size:1.8rem;}
.vp-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;}
.vp-detail{display:flex;align-items:center;gap:1rem;padding:1.2rem;background:var(--bg);border-radius:1.2rem;border:1px solid rgba(214,40,40,.06);}
.vp-detail-icon{width:3.6rem;height:3.6rem;border-radius:1rem;background:var(--rp);display:grid;place-items:center;font-size:1.2rem;color:var(--r);flex-shrink:0;}
.vp-detail-label{font-size:1.05rem;color:var(--ink3);}
.vp-detail-val{font-size:1.3rem;font-weight:700;color:var(--ink);}

/* Amenities */
.vp-amen-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.2rem;}
.vp-amen{display:flex;align-items:center;gap:.8rem;padding:1.2rem;background:var(--bg);border-radius:1.2rem;border:1px solid rgba(214,40,40,.06);}
.vp-amen-icon{width:3rem;height:3rem;border-radius:.8rem;display:grid;place-items:center;font-size:1rem;flex-shrink:0;}
.vp-amen-icon.yes{background:#e8f5e9;color:#2e7d32;}
.vp-amen-icon.no{background:var(--rp);color:var(--r);}
.vp-amen-name{font-size:1.15rem;font-weight:600;color:var(--ink2);}

/* Description */
.vp-desc{font-size:1.45rem;color:var(--ink3);line-height:1.8;}

/* Sidebar */
.vp-sidebar{position:sticky;top:calc(var(--nav-h) + 2rem);}
.vp-price-card{background:var(--white);border-radius:2.4rem;border:1.5px solid var(--line);padding:3rem;margin-bottom:2rem;box-shadow:var(--sh);}
.vp-price-label{font-size:1rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--ink3);margin-bottom:.5rem;}
.vp-price{font-family:'Cormorant Garamond',serif;font-size:4.5rem;font-weight:700;color:var(--ink);line-height:1;margin-bottom:.3rem;}
.vp-price-sub{font-size:1.2rem;color:var(--ink3);margin-bottom:2rem;}

.vp-owner{display:flex;align-items:center;gap:1.2rem;padding:1.5rem;background:var(--bg);border-radius:1.4rem;margin-bottom:2rem;}
.vp-owner-av{width:4.5rem;height:4.5rem;border-radius:50%;background:linear-gradient(135deg,var(--r),var(--rd));display:grid;place-items:center;font-size:1.8rem;font-weight:800;color:#fff;flex-shrink:0;}
.vp-owner-name{font-size:1.4rem;font-weight:700;color:var(--ink);}
.vp-owner-role{font-size:1.1rem;color:var(--ink3);}

.vp-actions{display:flex;flex-direction:column;gap:1rem;}
.vp-btn{width:100%;padding:1.4rem;border-radius:99px;font-size:1.4rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;transition:all .25s;display:flex;align-items:center;justify-content:center;gap:.8rem;border:none;text-decoration:none;}
.vp-btn.primary{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;box-shadow:0 8px 24px rgba(214,40,40,.35);}
.vp-btn.primary:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(214,40,40,.5);}
.vp-btn.secondary{background:var(--rp);color:var(--r);border:1.5px solid rgba(214,40,40,.18);}
.vp-btn.secondary:hover{background:var(--rp2);}
.vp-btn.outline{background:var(--white);color:var(--ink2);border:1.5px solid var(--line);}
.vp-btn.outline:hover{border-color:var(--r);color:var(--r);}
.vp-btn.saved{background:var(--r);color:#fff;}

/* Enquiry section */
.vp-enq{background:var(--white);border-radius:2.4rem;border:1.5px solid var(--line);padding:3rem;margin-top:3rem;}
.vp-enq h3{font-family:'Cormorant Garamond',serif;font-size:2.6rem;font-weight:700;color:var(--ink);margin-bottom:.5rem;}
.vp-enq p{font-size:1.3rem;color:var(--ink3);margin-bottom:2rem;}
.vp-enq-row{display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;margin-bottom:1.2rem;}
.vp-enq-field{display:flex;flex-direction:column;gap:.4rem;}
.vp-enq-field label{font-size:1.1rem;font-weight:600;color:var(--ink2);}
.vp-enq-field input,.vp-enq-field textarea{padding:1rem 1.4rem;border:1.5px solid var(--line);border-radius:1.2rem;font-size:1.3rem;font-family:'Outfit',sans-serif;color:var(--ink);background:var(--rp);outline:none;transition:border .2s;}
.vp-enq-field input:focus,.vp-enq-field textarea:focus{border-color:rgba(214,40,40,.4);background:var(--white);}
.vp-enq-field.full{grid-column:1/-1;}
.vp-enq-field textarea{resize:none;min-height:10rem;}

/* Visit booking popup */
.vp-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(8px);z-index:1000;place-items:center;}
.vp-overlay.open{display:grid;}
.vp-popup{background:var(--white);border-radius:2.4rem;padding:3.5rem;max-width:48rem;width:90%;position:relative;box-shadow:var(--sh2);animation:popIn .35s var(--ease);}
@keyframes popIn{from{opacity:0;transform:scale(.92) translateY(1.5rem);}to{opacity:1;transform:scale(1) translateY(0);}}
.vp-popup h3{font-family:'Cormorant Garamond',serif;font-size:2.6rem;font-weight:700;color:var(--ink);margin-bottom:.4rem;}
.vp-popup>p{font-size:1.3rem;color:var(--ink3);margin-bottom:2rem;}
.vp-popup-close{position:absolute;top:1.5rem;right:1.5rem;width:3.6rem;height:3.6rem;border-radius:50%;border:1.5px solid var(--line);background:var(--bg);display:grid;place-items:center;font-size:1.4rem;color:var(--ink3);cursor:pointer;transition:all .2s;}
.vp-popup-close:hover{border-color:var(--r);color:var(--r);background:var(--rp);}
.vp-visit-field{display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.4rem;}
.vp-visit-field label{font-size:1.1rem;font-weight:600;color:var(--ink2);}
.vp-visit-field input{padding:1rem 1.4rem;border:1.5px solid var(--line);border-radius:1.2rem;font-size:1.3rem;font-family:'Outfit',sans-serif;color:var(--ink);background:var(--rp);outline:none;transition:border .2s;}
.vp-visit-field input:focus{border-color:rgba(214,40,40,.4);background:var(--white);}

/* Footer */
.footer{background:linear-gradient(135deg,#fff0f0,#fde0e0);border-top:1px solid var(--line);padding:6rem 6% 3.5rem;}
.foot-grid{display:grid;grid-template-columns:2.2fr 1fr 1fr 1.3fr;gap:5rem;padding-bottom:4rem;border-bottom:1px solid var(--line);max-width:130rem;margin:0 auto;}
.foot-logo{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);display:block;margin-bottom:1.2rem;}
.foot-logo span{font-style:italic;color:var(--r);}
.foot-brand p{font-size:1.3rem;color:var(--ink3);line-height:1.7;margin-bottom:2rem;max-width:26rem;}
.foot-socials{display:flex;gap:.9rem;}
.fsc{width:3.8rem;height:3.8rem;border-radius:50%;border:1.5px solid var(--line);background:var(--white);display:grid;place-items:center;color:var(--ink3);font-size:1.4rem;text-decoration:none;transition:all .2s;}
.fsc:hover{border-color:var(--r);color:var(--r);background:var(--rp);transform:translateY(-3px);}
.foot-col h4{font-size:1.05rem;font-weight:700;color:var(--ink);letter-spacing:.14em;text-transform:uppercase;margin-bottom:1.6rem;}
.foot-col a{display:flex;align-items:center;gap:.6rem;font-size:1.25rem;color:var(--ink3);text-decoration:none;margin-bottom:.95rem;transition:all .2s;}
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

@media(max-width:1100px){
  .vp-layout{grid-template-columns:1fr;}
  .vp-sidebar{position:static;}
  .vp-gallery .swiper{height:35rem;}
  .foot-grid{grid-template-columns:1fr 1fr;gap:3rem;}
}
@media(max-width:768px){
  .nav-center{display:none;}
  .vp-section{padding-left:5%;padding-right:5%;}
  .vp-gallery .swiper{height:28rem;}
  .vp-detail-grid{grid-template-columns:1fr;}
  .vp-amen-grid{grid-template-columns:1fr 1fr;}
  .vp-enq-row{grid-template-columns:1fr;}
  .foot-grid{grid-template-columns:1fr;}
  .foot-bot{flex-direction:column;}
}
</style>
</head>
<body>

<!-- NAV -->
<nav class="nav" id="mainNav">
  <a href="home.php" class="logo">My<span>Estate</span></a>
  <div class="nav-center">
    <a href="home.php">Home</a>
    <a href="listings.php" class="active">Properties</a>
    <a href="home.php#upSec">Upcoming</a>
    <a href="about.php">About</a>
    <a href="contact.php">Contact</a>
  </div>
  <div class="nav-right">
    <?php if($user_id != ''){ ?>
    <a href="saved.php" class="nav-icon"><i class="fas fa-heart"></i><?php if($nav_saved_count > 0): ?><span class="nav-badge"><?= $nav_saved_count; ?></span><?php endif; ?></a>
    <div class="nav-user" id="navUser">
      <div class="nav-av"><?= $nav_user_initial; ?></div>
      <span style="font-size:1.3rem;font-weight:700;color:var(--ink);"><?= htmlspecialchars($nav_user_name); ?></span>
      <i class="fas fa-chevron-down" style="font-size:1rem;color:var(--ink3);margin-left:.4rem;"></i>
      <div class="nav-drop-menu">
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

<section class="vp-section">
  <!-- Gallery -->
  <div class="vp-gallery">
    <div class="swiper vpSwiper">
      <div class="swiper-wrapper">
        <?php foreach($images as $img):
          $img_src = (strpos($img,'http')===0) ? $img : 'uploaded_files/'.$img; ?>
        <div class="swiper-slide"><img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($fetch_property['property_name']); ?>"></div>
        <?php endforeach; ?>
      </div>
      <div class="swiper-pagination"></div>
    </div>
    <div class="vp-img-count"><i class="far fa-image"></i> <?= count($images); ?> Photos</div>
  </div>

  <div class="vp-layout">
    <!-- Price inline bar -->
    <div class="vp-price-inline">
      <div class="vp-price-inline-left">
        <div class="vp-price-label">Listed Price</div>
        <div class="vp-price"><?= $price_fmt; ?></div>
        <?php if($fetch_property['offer'] == 'rent'): ?><div class="vp-price-sub">per month</div><?php else: ?><div class="vp-price-sub">Negotiable</div><?php endif; ?>
      </div>
      <div class="vp-price-inline-right">
        <!-- owner hidden: removed name display per user request -->
        <form action="" method="POST" style="display:contents;">
          <input type="hidden" name="property_id" value="<?= $property_id; ?>">
          <button type="submit" name="save" class="vp-btn <?= $is_saved ? 'saved' : 'secondary'; ?>" style="width:auto;padding:1.2rem 2rem;"><i class="fas fa-heart"></i> <?= $is_saved ? 'Saved' : 'Save'; ?></button>
        </form>
        <button type="button" class="vp-btn primary" id="bookVisitBtn" style="width:auto;padding:1.2rem 2rem;"><i class="fas fa-calendar-check"></i> Book Visit</button>
        <a href="#enquiry" class="vp-btn secondary" style="width:auto;padding:1.2rem 2rem;"><i class="fas fa-paper-plane"></i> Enquire</a>
      </div>
    </div>
    <!-- Main Content full width -->
    <div class="vp-main">
      <div class="vp-header">
        <div class="vp-breadcrumb"><a href="home.php">Home</a> <i class="fas fa-chevron-right" style="font-size:.8rem;"></i> <a href="listings.php">Properties</a> <i class="fas fa-chevron-right" style="font-size:.8rem;"></i> <span><?= htmlspecialchars($fetch_property['property_name']); ?></span></div>
        <div class="vp-type-badge"><i class="fas fa-tag"></i> <?= ucfirst($fetch_property['type']); ?> · <?= ucfirst($fetch_property['offer']); ?></div>
        <h1 class="vp-title"><?= htmlspecialchars($fetch_property['property_name']); ?></h1>
        <div class="vp-addr"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($fetch_property['address']); ?></div>
      </div>

      <div class="vp-pills">
        <?php if(!empty($fetch_property['bhk'])): ?><div class="vp-pill"><i class="fas fa-bed"></i><b><?= $fetch_property['bhk']; ?> BHK</b></div><?php endif; ?>
        <?php if(!empty($fetch_property['bathroom'])): ?><div class="vp-pill"><i class="fas fa-bath"></i><b><?= $fetch_property['bathroom']; ?></b> Bathrooms</div><?php endif; ?>
        <?php if(!empty($fetch_property['carpet'])): ?><div class="vp-pill"><i class="fas fa-ruler-combined"></i><b><?= $fetch_property['carpet']; ?></b> sqft</div><?php endif; ?>
        <?php if(!empty($fetch_property['furnished'])): ?><div class="vp-pill"><i class="fas fa-couch"></i><?= $fetch_property['furnished']; ?></div><?php endif; ?>
        <?php if(!empty($fetch_property['status'])): ?><div class="vp-pill"><i class="fas fa-trowel"></i><?= $fetch_property['status']; ?></div><?php endif; ?>
        <?php if(!empty($fetch_property['floor'])): ?><div class="vp-pill"><i class="fas fa-layer-group"></i>Floor <?= $fetch_property['floor']; ?></div><?php endif; ?>
      </div>

      <!-- Property Details -->
      <div class="vp-card">
        <div class="vp-card-title"><i class="fas fa-info-circle"></i> Property Details</div>
        <div class="vp-detail-grid">
          <?php if(!empty($fetch_property['bedroom'])): ?>
          <div class="vp-detail"><div class="vp-detail-icon"><i class="fas fa-bed"></i></div><div><div class="vp-detail-label">Bedrooms</div><div class="vp-detail-val"><?= $fetch_property['bedroom']; ?></div></div></div>
          <?php endif; ?>
          <?php if(!empty($fetch_property['bathroom'])): ?>
          <div class="vp-detail"><div class="vp-detail-icon"><i class="fas fa-bath"></i></div><div><div class="vp-detail-label">Bathrooms</div><div class="vp-detail-val"><?= $fetch_property['bathroom']; ?></div></div></div>
          <?php endif; ?>
          <?php if(!empty($fetch_property['balcony'])): ?>
          <div class="vp-detail"><div class="vp-detail-icon"><i class="fas fa-border-all"></i></div><div><div class="vp-detail-label">Balconies</div><div class="vp-detail-val"><?= $fetch_property['balcony']; ?></div></div></div>
          <?php endif; ?>
          <?php if(!empty($fetch_property['carpet'])): ?>
          <div class="vp-detail"><div class="vp-detail-icon"><i class="fas fa-ruler-combined"></i></div><div><div class="vp-detail-label">Carpet Area</div><div class="vp-detail-val"><?= $fetch_property['carpet']; ?> sqft</div></div></div>
          <?php endif; ?>
          <?php if(!empty($fetch_property['age'])): ?>
          <div class="vp-detail"><div class="vp-detail-icon"><i class="fas fa-clock"></i></div><div><div class="vp-detail-label">Property Age</div><div class="vp-detail-val"><?= $fetch_property['age']; ?> years</div></div></div>
          <?php endif; ?>
          <?php if(!empty($fetch_property['total_floors'])): ?>
          <div class="vp-detail"><div class="vp-detail-icon"><i class="fas fa-building"></i></div><div><div class="vp-detail-label">Total Floors</div><div class="vp-detail-val"><?= $fetch_property['total_floors']; ?></div></div></div>
          <?php endif; ?>
          <?php if(!empty($fetch_property['room_floor'])): ?>
          <div class="vp-detail"><div class="vp-detail-icon"><i class="fas fa-layer-group"></i></div><div><div class="vp-detail-label">Floor</div><div class="vp-detail-val"><?= $fetch_property['room_floor']; ?></div></div></div>
          <?php endif; ?>
          <?php if(!empty($fetch_property['loan'])): ?>
          <div class="vp-detail"><div class="vp-detail-icon"><i class="fas fa-landmark"></i></div><div><div class="vp-detail-label">Loan</div><div class="vp-detail-val"><?= $fetch_property['loan']; ?></div></div></div>
          <?php endif; ?>
          <?php if(!empty($fetch_property['deposite'])): ?>
          <div class="vp-detail"><div class="vp-detail-icon"><i class="fas fa-wallet"></i></div><div><div class="vp-detail-label">Deposit</div><div class="vp-detail-val">₹<?= number_format($fetch_property['deposite']); ?></div></div></div>
          <?php endif; ?>
          <!-- Listed On: hidden per user request -->
        </div>
      </div>

      <!-- Amenities -->
      <div class="vp-card">
        <div class="vp-card-title"><i class="fas fa-star"></i> Amenities</div>
        <div class="vp-amen-grid">
          <?php
          $amenities = [
            ['lift','Lifts'],['security_guard','Security'],['play_ground','Playground'],['garden','Garden'],
            ['water_supply','Water Supply'],['power_backup','Power Backup'],['parking_area','Parking'],
            ['gym','Gym'],['shopping_mall','Shopping Mall'],['hospital','Hospital'],['school','Schools'],['market_area','Market']
          ];
          foreach($amenities as $a):
            $key = $a[0];
            $label = $a[1];
            $has = (isset($fetch_property[$key]) && $fetch_property[$key] == 'yes');
          ?>
          <div class="vp-amen">
            <div class="vp-amen-icon <?= $has ? 'yes' : 'no'; ?>"><i class="fas fa-<?= $has ? 'check' : 'times'; ?>"></i></div>
            <div class="vp-amen-name"><?= $label; ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Description -->
      <?php if(!empty($fetch_property['description'])): ?>
      <div class="vp-card">
        <div class="vp-card-title"><i class="fas fa-align-left"></i> Description</div>
        <p class="vp-desc"><?= nl2br(htmlspecialchars($fetch_property['description'])); ?></p>
      </div>
      <?php endif; ?>

      <!-- Enquiry Form -->
      <div class="vp-enq" id="enquiry">
        <h3>Send an <span style="color:var(--r);font-style:italic;">Enquiry</span></h3>
        <p>Interested in this property? Fill in your details and the owner will contact you.</p>
        <form action="" method="POST">
          <input type="hidden" name="property_id" value="<?= $property_id; ?>">
          <div class="vp-enq-row">
            <div class="vp-enq-field"><label>Your Name</label><input type="text" name="name" placeholder="Full name" value="<?= $user_id ? htmlspecialchars($nav_user_name) : ''; ?>"></div>
            <div class="vp-enq-field"><label>Phone</label><input type="tel" name="number" placeholder="+91 98765 43210"></div>
          </div>
          <div class="vp-enq-field full" style="margin-bottom:1.5rem;"><label>Message</label><textarea name="message" placeholder="I'm interested in this property..."></textarea></div>
          <button type="submit" name="send" class="vp-btn primary" style="width:100%;"><i class="fas fa-paper-plane"></i> Send Enquiry</button>
        </form>
      </div>
    </div><!-- end vp-main -->
  </div>
</section>

<!-- Book Visit Popup -->
<div class="vp-overlay" id="visitOverlay">
  <div class="vp-popup">
    <div class="vp-popup-close" id="visitClose"><i class="fas fa-times"></i></div>
    <h3>Book a <span style="color:var(--r);font-style:italic;">Site Visit</span></h3>
    <p>Schedule a visit to <?= htmlspecialchars($fetch_property['property_name']); ?></p>
    <form action="" method="POST">
      <input type="hidden" name="property_id" value="<?= $property_id; ?>">
      <div class="vp-visit-field"><label>Preferred Date</label><input type="date" name="visit_date" required></div>
      <div class="vp-visit-field"><label>Preferred Time</label><input type="time" name="visit_time" required></div>
      <div class="vp-visit-field"><label>Contact Number</label><input type="tel" name="visit_phone" placeholder="+91 98765 43210" required></div>
      <button type="submit" name="send" class="vp-btn primary" style="margin-top:.5rem;"><i class="fas fa-calendar-check"></i> Confirm Booking</button>
    </form>
  </div>
</div>

<!-- FOOTER -->
<footer class="footer" id="footer">
  <div class="foot-grid">
    <div class="foot-brand">
      <a href="home.php" class="foot-logo">My<span>Estate</span></a>
      <p>Trusted real estate across Mumbai & Pune.</p>
      <div class="foot-socials">
        <a href="#" class="fsc"><i class="fab fa-instagram"></i></a>
        <a href="#" class="fsc"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="fsc"><i class="fab fa-twitter"></i></a>
        <a href="#" class="fsc"><i class="fab fa-youtube"></i></a>
      </div>
    </div>
    <div class="foot-col">
      <h4>Properties</h4>
      <a href="listings.php"><i class="fas fa-chevron-right"></i>All Listings</a>
      <a href="saved.php"><i class="fas fa-chevron-right"></i>Saved</a>
    </div>
    <div class="foot-col">
      <h4>Quick Links</h4>
      <a href="home.php"><i class="fas fa-chevron-right"></i>Dashboard</a>
      <a href="about.php"><i class="fas fa-chevron-right"></i>About Us</a>
      <a href="contact.php"><i class="fas fa-chevron-right"></i>Contact</a>
    </div>
    <div class="foot-col">
      <h4>Contact Us</h4>
      <div class="fci"><div class="fci-ic"><i class="fas fa-map-marker-alt"></i></div><div class="fci-t"><strong>Office</strong>Nalasopara West, Maharashtra — 401203</div></div>
      <div class="fci"><div class="fci-ic"><i class="fas fa-envelope"></i></div><div class="fci-t"><strong>Email</strong>rayyanbhagate@gmail.com</div></div>
    </div>
  </div>
  <div class="foot-bot">
    <p class="foot-copy">&copy; <?= date('Y'); ?> <span>MyEstate</span>. Made with &#10084; in Mumbai.</p>
    <div class="foot-bot-links"><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Cookies</a></div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/message.php'; ?>
<script>
// Swiper gallery
var swiper = new Swiper(".vpSwiper", {
  loop: true,
  pagination: { el: ".swiper-pagination", clickable: true },
  autoplay: { delay: 4000, disableOnInteraction: false },
});

// Nav scroll
window.addEventListener('scroll', function(){
  document.getElementById('mainNav').classList.toggle('scrolled', window.scrollY > 40);
});

// Profile dropdown — click to open, click outside to close
var navUser = document.getElementById('navUser');
if(navUser){
  var navMenu = navUser.querySelector('.nav-drop-menu');
  navUser.addEventListener('click', function(e){ e.stopPropagation(); navMenu.classList.toggle('open'); });
  navMenu.addEventListener('click', function(e){ e.stopPropagation(); });
  document.addEventListener('click', function(e){ if(!navUser.contains(e.target)) navMenu.classList.remove('open'); });
  window.addEventListener('scroll', function(){ navMenu.classList.remove('open'); }, {passive:true});
}

// Logout confirmation
function confirmLogout(){
  swal({title:'Logout?',text:'Are you sure you want to logout?',icon:'warning',
    buttons:['Cancel','Logout'],dangerMode:true
  }).then(ok=>{ if(ok) window.location='components/user_logout.php'; });
}

// Book Visit popup
var bookBtn = document.getElementById('bookVisitBtn');
var overlay = document.getElementById('visitOverlay');
var closeBtn = document.getElementById('visitClose');
if(bookBtn && overlay){
  bookBtn.addEventListener('click', function(){ overlay.classList.add('open'); });
}
if(closeBtn && overlay){
  closeBtn.addEventListener('click', function(){ overlay.classList.remove('open'); });
  overlay.addEventListener('click', function(e){
    if(e.target === overlay) overlay.classList.remove('open');
  });
}
</script>
</body>
</html>