<?php
include 'components/connect.php';
$user_id = validate_user_cookie($conn);
if(!$user_id){ header('Location: login.php'); exit(); }
$sel_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
$sel_user->execute([$user_id]);
$fetch_user = $sel_user->fetch(PDO::FETCH_ASSOC);
$user_name = $fetch_user ? $fetch_user['name'] : 'User';
$user_initial = strtoupper(substr($user_name, 0, 1));
$sel_saved = $conn->prepare("SELECT COUNT(*) as cnt FROM `saved` WHERE user_id = ?");
$sel_saved->execute([$user_id]);
$saved_count = $sel_saved->fetch(PDO::FETCH_ASSOC)['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upcoming Projects — MyEstate</title>
<meta name="description" content="Explore upcoming premium real estate projects in Mumbai and Pune. Under construction luxury apartments, villas, and gated communities.">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600;1,700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
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
  --sh2:0 28px 80px rgba(214,40,40,.2);
  --nav-h:7rem;
}
*{margin:0;padding:0;box-sizing:border-box;}
html{font-size:62.5%;scroll-behavior:smooth;}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--ink);overflow-x:hidden;}
::-webkit-scrollbar{width:3px;}
::-webkit-scrollbar-thumb{background:var(--r);}
a{text-decoration:none;}
.reveal{opacity:0;transform:translateY(32px);transition:opacity .8s var(--ease),transform .8s var(--ease);}
.reveal.in{opacity:1;transform:translateY(0);}
.eyebrow{display:inline-flex;align-items:center;gap:.5rem;font-size:.9rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--r);background:var(--rp);padding:.35rem 1rem;border-radius:99px;border:1px solid rgba(214,40,40,.12);width:fit-content;margin-bottom:1.2rem;}
.eyebrow::before{content:'';width:.4rem;height:.4rem;border-radius:50%;background:var(--r);animation:blink 2s infinite;flex-shrink:0;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
/* NAV */
.nav{position:fixed;top:0;left:0;right:0;z-index:999;height:var(--nav-h);padding:0 6%;display:flex;align-items:center;justify-content:space-between;background:rgba(253,241,241,.94);backdrop-filter:blur(24px);border-bottom:1px solid var(--line);transition:box-shadow .3s;}
.nav.scrolled{box-shadow:0 4px 40px rgba(214,40,40,.1);}
.logo{font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:700;color:var(--ink);}
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
/* HERO */
.up-hero{padding-top:var(--nav-h);background:linear-gradient(145deg,#fff9f9 0%,#fdf1f1 45%,#fae8e8 100%);position:relative;overflow:hidden;padding-bottom:6rem;}
.up-hero-deco{position:absolute;border-radius:50%;pointer-events:none;border:1px solid rgba(214,40,40,.07);}
.up-hero-deco.r1{width:80rem;height:80rem;top:-30rem;right:-20rem;}
.up-hero-deco.r2{width:55rem;height:55rem;top:-15rem;right:-5rem;}
.up-hero-inner{max-width:130rem;margin:0 auto;padding:5rem 6% 2rem;position:relative;z-index:1;}
.up-hero-tag{display:inline-flex;align-items:center;gap:.7rem;font-size:.95rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--r);background:var(--rp);padding:.5rem 1.4rem;border-radius:99px;border:1px solid rgba(214,40,40,.15);margin-bottom:2rem;}
.up-hero-tag::before{content:'';width:.45rem;height:.45rem;border-radius:50%;background:var(--r);animation:blink 2s infinite;flex-shrink:0;}
.up-hero-h{font-family:'Cormorant Garamond',serif;font-size:clamp(4.5rem,6vw,8rem);font-weight:700;color:var(--ink);letter-spacing:-.04em;line-height:.9;margin-bottom:1.8rem;}
.up-hero-h em{font-style:italic;color:var(--r);}
.up-hero-sub{font-size:1.6rem;color:var(--ink3);max-width:60rem;line-height:1.75;font-family:'DM Sans',sans-serif;font-weight:300;margin-bottom:3rem;}
.up-stats{display:flex;gap:3rem;flex-wrap:wrap;}
.up-stat{display:flex;align-items:center;gap:.8rem;font-size:1.3rem;font-weight:600;color:var(--ink2);}
.up-stat i{color:var(--r);font-size:1.1rem;}
/* PROJECT CARDS */
.up-sec{max-width:130rem;margin:0 auto;padding:6rem 6% 8rem;}
.up-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:3rem;}
/* Image Carousel */
.proj-card{background:var(--white);border-radius:2.8rem;overflow:hidden;border:1.5px solid var(--line);transition:all .4s var(--ease);position:relative;}
.proj-card:hover{transform:translateY(-8px);box-shadow:var(--sh2);}
.proj-img-wrap{position:relative;height:30rem;overflow:hidden;}
.proj-img-slide{position:absolute;inset:0;opacity:0;transition:opacity .8s var(--ease);}
.proj-img-slide.active{opacity:1;}
.proj-img-slide img{width:100%;height:100%;object-fit:cover;}
.proj-img-ov{position:absolute;inset:0;background:linear-gradient(to top,rgba(15,2,2,.7) 0%,transparent 55%);z-index:1;}
.proj-img-dots{position:absolute;bottom:1.4rem;left:50%;transform:translateX(-50%);display:flex;gap:.5rem;z-index:3;}
.pid{width:.7rem;height:.7rem;border-radius:50%;background:rgba(255,255,255,.4);cursor:pointer;transition:all .3s;border:none;padding:0;}
.pid.on{background:#fff;transform:scale(1.3);}
.proj-badge{position:absolute;top:1.8rem;left:1.8rem;z-index:3;display:flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.95);backdrop-filter:blur(12px);padding:.45rem 1.1rem;border-radius:99px;font-size:1rem;font-weight:700;color:var(--r);}
.proj-badge i{font-size:.85rem;}
.proj-completion{position:absolute;top:1.8rem;right:1.8rem;z-index:3;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;padding:.45rem 1.1rem;border-radius:99px;font-size:1rem;font-weight:700;}
.proj-body{padding:2.8rem;}
.proj-type{font-size:.9rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--r);margin-bottom:.6rem;}
.proj-name{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);margin-bottom:.5rem;line-height:1.05;}
.proj-loc{font-size:1.2rem;color:var(--ink3);display:flex;align-items:center;gap:.4rem;margin-bottom:1.8rem;}
.proj-loc i{color:var(--r);font-size:1rem;}
.proj-feats{display:flex;gap:.7rem;flex-wrap:wrap;margin-bottom:2rem;}
.pf{display:flex;align-items:center;gap:.4rem;background:var(--rp);border:1px solid rgba(214,40,40,.1);color:var(--ink2);padding:.4rem .95rem;border-radius:99px;font-size:1.05rem;font-weight:600;}
.pf i{font-size:.85rem;color:var(--r);}
.proj-price{font-family:'Cormorant Garamond',serif;font-size:2.6rem;font-weight:700;color:var(--ink);margin-bottom:.3rem;}
.proj-price span{font-size:1.2rem;color:var(--ink3);font-family:'Outfit',sans-serif;font-weight:400;}
.proj-desc{font-size:1.25rem;color:var(--ink3);line-height:1.7;font-family:'DM Sans',sans-serif;font-weight:300;margin-bottom:2rem;}
.proj-acts{display:flex;gap:1rem;flex-wrap:wrap;padding-top:1.8rem;border-top:1px solid var(--line);}
.proj-btn{display:inline-flex;align-items:center;gap:.6rem;padding:1rem 2.2rem;border-radius:99px;font-size:1.2rem;font-weight:700;cursor:pointer;font-family:'Outfit',sans-serif;transition:all .22s;border:none;text-decoration:none;}
.proj-btn.prim{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;box-shadow:0 4px 16px rgba(214,40,40,.3);}
.proj-btn.prim:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(214,40,40,.45);}
.proj-btn.sec{background:var(--rp);color:var(--r);border:1.5px solid rgba(214,40,40,.18);}
.proj-btn.sec:hover{background:var(--rp2);}
.proj-progress{margin-bottom:2rem;}
.prog-label{display:flex;justify-content:space-between;font-size:1.1rem;font-weight:600;color:var(--ink2);margin-bottom:.6rem;}
.prog-bar{height:.7rem;background:var(--rp2);border-radius:99px;overflow:hidden;}
.prog-fill{height:100%;background:linear-gradient(90deg,var(--r),var(--rl));border-radius:99px;transition:width 1.2s var(--ease);}
/* NEWSLETTER / REGISTER BANNER */
.up-banner{background:linear-gradient(135deg,var(--r),var(--rd));border-radius:2.8rem;padding:5rem 6%;display:grid;grid-template-columns:1fr auto;gap:4rem;align-items:center;margin-top:6rem;position:relative;overflow:hidden;}
.up-banner::before{content:'';position:absolute;width:40rem;height:40rem;border-radius:50%;border:1px solid rgba(255,255,255,.08);top:-15rem;right:-10rem;}
.up-banner::after{content:'';position:absolute;width:25rem;height:25rem;border-radius:50%;border:1px solid rgba(255,255,255,.05);bottom:-10rem;left:20%;}
.ub-l{position:relative;z-index:1;}
.ub-eyebrow{display:inline-flex;align-items:center;gap:.5rem;font-size:.9rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.7);background:rgba(255,255,255,.1);padding:.35rem 1rem;border-radius:99px;margin-bottom:1.4rem;}
.up-banner h2{font-family:'Cormorant Garamond',serif;font-size:clamp(3rem,4vw,5rem);font-weight:700;color:#fff;line-height:.95;letter-spacing:-.03em;margin-bottom:1rem;}
.up-banner h2 em{font-style:italic;color:rgba(255,220,220,.9);}
.up-banner p{font-size:1.4rem;color:rgba(255,255,255,.7);font-family:'DM Sans',sans-serif;font-weight:300;line-height:1.65;max-width:50rem;}
.ub-r{position:relative;z-index:1;display:flex;flex-direction:column;gap:1.2rem;min-width:32rem;}
.ub-input{padding:1.4rem 2rem;border-radius:1.4rem;border:none;font-size:1.3rem;font-family:'Outfit',sans-serif;color:var(--ink);background:#fff;outline:none;width:100%;}
.ub-btn{padding:1.4rem 2rem;border-radius:99px;border:none;font-size:1.3rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;background:var(--ink);color:#fff;transition:all .25s;display:flex;align-items:center;justify-content:center;gap:.7rem;}
.ub-btn:hover{background:#2a0808;transform:translateY(-2px);}
/* FOOTER */
.footer{background:linear-gradient(135deg,#fff0f0,#fde0e0);border-top:1px solid var(--line);padding:6rem 6% 3.5rem;}
.foot-grid{display:grid;grid-template-columns:2.2fr 1fr 1fr 1.3fr;gap:5rem;padding-bottom:4rem;border-bottom:1px solid var(--line);}
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
.foot-bot{display:flex;align-items:center;justify-content:space-between;padding-top:2.5rem;flex-wrap:wrap;gap:1.2rem;}
.foot-copy{font-size:1.2rem;color:var(--ink3);}
.foot-copy span{color:var(--r);font-weight:700;}
.foot-bot-links{display:flex;gap:2rem;}
.foot-bot-links a{font-size:1.2rem;color:var(--ink3);text-decoration:none;transition:color .2s;}
.foot-bot-links a:hover{color:var(--r);}
@media(max-width:1100px){.up-grid{grid-template-columns:1fr;}.up-banner{grid-template-columns:1fr;}.foot-grid{grid-template-columns:1fr 1fr;gap:3rem;}}
@media(max-width:768px){.nav-center{display:none;}.up-grid{grid-template-columns:1fr;}.ub-r{min-width:auto;}.foot-grid{grid-template-columns:1fr;}.foot-bot{flex-direction:column;}}
</style>
</head>
<body>
<nav class="nav" id="mainNav">
  <a href="home.php" class="logo">My<span>Estate</span></a>
  <div class="nav-center">
    <a href="home.php">Home</a>
    <a href="listings.php">Properties</a>
    <a href="upcoming.php" class="active">Upcoming</a>
    <a href="about.php">About</a>
    <a href="contact.php">Contact</a>
  </div>
  <div class="nav-right">
    <a href="saved.php" class="nav-icon"><i class="fas fa-heart"></i><?php if($saved_count > 0): ?><span class="nav-badge"><?= $saved_count; ?></span><?php endif; ?></a>
    <div class="nav-user" id="navUser">
      <div class="nav-av"><?= $user_initial; ?></div>
      <span style="font-size:1.3rem;font-weight:700;color:var(--ink);"><?= htmlspecialchars($user_name); ?></span>
      <i class="fas fa-chevron-down" style="font-size:1rem;color:var(--ink3);margin-left:.4rem;"></i>
      <div class="nav-drop-menu" id="navDrop">
        <a href="saved.php" class="nd-item"><i class="fas fa-heart"></i>Saved Properties</a>
        <a href="requests.php" class="nd-item"><i class="fas fa-file-alt"></i>My Requests</a>
        <div class="nd-sep"></div>
        <a href="home.php#agentSec" class="nd-item" style="color:var(--r);font-weight:700;"><i class="fas fa-user-tie" style="color:var(--r);"></i>Become an Agent</a>
        <div class="nd-sep"></div>
        <a href="update.php" class="nd-item"><i class="fas fa-user-edit"></i>Edit Profile</a>
        <div class="nd-sep"></div>
        <a href="components/user_logout.php" class="nd-item nd-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
      </div>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="up-hero">
  <div class="up-hero-deco r1"></div>
  <div class="up-hero-deco r2"></div>
  <div class="up-hero-inner reveal">
    <div class="up-hero-tag">Coming Soon</div>
    <h1 class="up-hero-h">Upcoming <em>Projects</em></h1>
    <p class="up-hero-sub">Premium under-construction properties in Mumbai & Pune — register your interest early and be first when bookings open.</p>
    <div class="up-stats">
      <div class="up-stat"><i class="fas fa-hard-hat"></i> 6 Active Projects</div>
      <div class="up-stat"><i class="fas fa-map-marker-alt"></i> Mumbai & Pune</div>
      <div class="up-stat"><i class="fas fa-key"></i> Delivery Q4 2026 – Q1 2028</div>
      <div class="up-stat"><i class="fas fa-shield-alt"></i> 100% Verified Developers</div>
    </div>
  </div>
</section>

<!-- PROJECTS GRID -->
<div class="up-sec">
<div class="up-grid">

<?php
$projects = [
  [
    'name'=>'Skyline Residences','type'=>'Luxury Apartments','loc'=>'Borivali West, Mumbai, Maharashtra',
    'price'=>'₹85L – ₹1.8 Cr','completion'=>'Q4 2026','progress'=>72,
    'desc'=>'Soaring 35 floors above Borivali, Skyline Residences redefines western Mumbai luxury living. Each residence features floor-to-ceiling windows, imported marble, and a private terrace.',
    'feats'=>[['fa-bed','2–4 BHK'],['fa-ruler-combined','850–2200 sqft'],['fa-swimming-pool','Rooftop Pool'],['fa-car','2 Parking']],
    'imgs'=>[
      'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=900&q=85&auto=format',
    ]
  ],
  [
    'name'=>'The Horizon Towers','type'=>'Premium Apartments','loc'=>'Andheri East, Mumbai, Maharashtra',
    'price'=>'₹1.2 Cr – ₹3.5 Cr','completion'=>'Q2 2027','progress'=>55,
    'desc'=>'Twin iconic towers rising 42 floors in Andheri East. Steps from the metro, minutes from BKC. Horizon Towers offers world-class amenities and connectivity that professionals demand.',
    'feats'=>[['fa-bed','3–5 BHK'],['fa-ruler-combined','1100–3200 sqft'],['fa-dumbbell','Premium Gym'],['fa-concierge-bell','Concierge']],
    'imgs'=>[
      'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1613977257365-aaae5a9817ff?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=900&q=85&auto=format',
    ]
  ],
  [
    'name'=>'Greenfield Villas','type'=>'Luxury Villas','loc'=>'Panvel, Navi Mumbai, Maharashtra',
    'price'=>'₹2.8 Cr – ₹5.5 Cr','completion'=>'Q1 2027','progress'=>63,
    'desc'=>'Spread across 18 acres of lush greenery, Greenfield Villas is a private gated community of 120 ultra-luxury row villas. Each villa includes a private garden, basement parking, and home automation.',
    'feats'=>[['fa-home','4–6 BHK Villas'],['fa-ruler-combined','3200–6800 sqft'],['fa-seedling','Private Garden'],['fa-shield-alt','24/7 Security']],
    'imgs'=>[
      'https://images.unsplash.com/photo-1613977257592-4871e5fcd7c4?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=900&q=85&auto=format',
    ]
  ],
  [
    'name'=>'Prestige One','type'=>'Mixed-Use Tower','loc'=>'Wakad, Pune, Maharashtra',
    'price'=>'₹55L – ₹1.4 Cr','completion'=>'Q3 2026','progress'=>80,
    'desc'=>'Prestige One is Wakad\'s most anticipated mixed-use development — retail on the ground floors, Grade-A office spaces, and premium residences above. Surrounded by Pune\'s booming IT corridor.',
    'feats'=>[['fa-bed','2–3 BHK'],['fa-ruler-combined','720–1600 sqft'],['fa-store','Retail Podium'],['fa-wifi','Smart Building']],
    'imgs'=>[
      'https://images.unsplash.com/photo-1497366216548-37526070297c?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=900&q=85&auto=format',
    ]
  ],
  [
    'name'=>'Eden Estates','type'=>'Gated Community','loc'=>'Thane West, Maharashtra',
    'price'=>'₹1.1 Cr – ₹2.6 Cr','completion'=>'Q1 2028','progress'=>38,
    'desc'=>'Eden Estates is a thoughtfully planned gated township spreading over 28 acres in Thane West. Featuring 8 residential towers, a clubhouse, school, and a 3-acre central park — it\'s a city within a city.',
    'feats'=>[['fa-bed','2–4 BHK'],['fa-ruler-combined','950–2100 sqft'],['fa-tree','3-Acre Park'],['fa-school','In-Campus School']],
    'imgs'=>[
      'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1613977257365-aaae5a9817ff?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=900&q=85&auto=format',
    ]
  ],
  [
    'name'=>'Marina Heights','type'=>'Ultra-Luxury Residences','loc'=>'Bandra West, Mumbai, Maharashtra',
    'price'=>'₹6.5 Cr – ₹18 Cr','completion'=>'Q4 2027','progress'=>45,
    'desc'=>'On the most prestigious stretch of Bandra West, Marina Heights is 24 exclusive residences across 12 floors. Sea views, private sky decks, a dedicated butler service — this is Mumbai at its most extraordinary.',
    'feats'=>[['fa-bed','4–6 BHK Sky Villas'],['fa-ruler-combined','4200–9500 sqft'],['fa-water','Sea View'],['fa-concierge-bell','Butler Service']],
    'imgs'=>[
      'https://images.unsplash.com/photo-1613977257592-4871e5fcd7c4?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1570168007204-dfb528c6958f?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=900&q=85&auto=format',
      'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=900&q=85&auto=format',
    ]
  ],
];

foreach($projects as $pi => $p):
?>
<div class="proj-card reveal" style="transition-delay:<?= ($pi%2)*0.1 ?>s;">
  <div class="proj-img-wrap" id="pw<?= $pi ?>">
    <?php foreach($p['imgs'] as $ii => $img): ?>
    <div class="proj-img-slide <?= $ii===0?'active':'' ?>" data-proj="<?= $pi ?>" data-slide="<?= $ii ?>">
      <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
    </div>
    <?php endforeach; ?>
    <div class="proj-img-ov"></div>
    <div class="proj-badge"><i class="fas fa-hard-hat"></i> Under Construction</div>
    <div class="proj-completion"><?= $p['completion'] ?></div>
    <div class="proj-img-dots" id="pd<?= $pi ?>">
      <?php foreach($p['imgs'] as $ii => $img): ?>
      <button class="pid <?= $ii===0?'on':'' ?>" onclick="goSlide(<?= $pi ?>,<?= $ii ?>)" aria-label="Image <?= $ii+1 ?>"></button>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="proj-body">
    <div class="proj-type"><?= htmlspecialchars($p['type']) ?></div>
    <div class="proj-name"><?= htmlspecialchars($p['name']) ?></div>
    <div class="proj-loc"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($p['loc']) ?></div>
    <div class="proj-progress">
      <div class="prog-label"><span>Construction Progress</span><span><?= $p['progress'] ?>%</span></div>
      <div class="prog-bar"><div class="prog-fill" style="width:<?= $p['progress'] ?>%"></div></div>
    </div>
    <div class="proj-feats">
      <?php foreach($p['feats'] as $f): ?>
      <div class="pf"><i class="fas <?= $f[0] ?>"></i><?= htmlspecialchars($f[1]) ?></div>
      <?php endforeach; ?>
    </div>
    <div class="proj-price"><?= htmlspecialchars($p['price']) ?> <span>Onward</span></div>
    <p class="proj-desc"><?= htmlspecialchars($p['desc']) ?></p>
    <div class="proj-acts">
      <a href="contact.php" class="proj-btn prim"><i class="fas fa-paper-plane"></i> Register Interest</a>
      <a href="listings.php" class="proj-btn sec"><i class="fas fa-building"></i> View Ready Homes</a>
    </div>
  </div>
</div>
<?php endforeach; ?>

</div><!-- end up-grid -->

<!-- REGISTER BANNER -->
<div class="up-banner reveal" style="transition-delay:.1s;">
  <div class="ub-l">
    <div class="ub-eyebrow"><i class="fas fa-bell"></i> Stay Updated</div>
    <h2>Be First to <em>Know</em> When<br>Bookings Open</h2>
    <p>Register your interest now and our team will reach out the moment pre-launch bookings open for your preferred project. Early registrants receive priority allocation.</p>
  </div>
  <div class="ub-r">
    <input type="email" class="ub-input" placeholder="Your email address" id="regEmail">
    <input type="text" class="ub-input" placeholder="Your full name" id="regName">
    <button class="ub-btn" onclick="registerInterest()"><i class="fas fa-paper-plane"></i> Register Interest Now</button>
  </div>
</div>

</div><!-- end up-sec -->

<!-- FOOTER -->
<footer class="footer">
  <div class="foot-grid">
    <div class="foot-brand"><a href="home.php" class="foot-logo">My<span>Estate</span></a><p>Trusted real estate across Mumbai & Pune. Verified listings, zero commission, expert guidance.</p><div class="foot-socials"><a href="#" class="fsc"><i class="fab fa-instagram"></i></a><a href="#" class="fsc"><i class="fab fa-facebook-f"></i></a><a href="#" class="fsc"><i class="fab fa-twitter"></i></a><a href="#" class="fsc"><i class="fab fa-youtube"></i></a></div></div>
    <div class="foot-col"><h4>Properties</h4><a href="listings.php?type=apartment"><i class="fas fa-chevron-right"></i>Apartments</a><a href="listings.php?type=villa"><i class="fas fa-chevron-right"></i>Villas</a><a href="listings.php?type=plot"><i class="fas fa-chevron-right"></i>Plots</a><a href="listings.php?type=commercial"><i class="fas fa-chevron-right"></i>Commercial</a></div>
    <div class="foot-col"><h4>Quick Links</h4><a href="home.php"><i class="fas fa-chevron-right"></i>Dashboard</a><a href="listings.php"><i class="fas fa-chevron-right"></i>All Listings</a><a href="upcoming.php"><i class="fas fa-chevron-right"></i>Upcoming</a><a href="about.php"><i class="fas fa-chevron-right"></i>About Us</a></div>
    <div class="foot-col"><h4>Contact Us</h4><div class="fci"><div class="fci-ic"><i class="fas fa-map-marker-alt"></i></div><div class="fci-t"><strong>Office</strong>Nalasopara West, Maharashtra — 401203</div></div><div class="fci"><div class="fci-ic"><i class="fas fa-envelope"></i></div><div class="fci-t"><strong>Email</strong>rayyanbhagate@gmail.com</div></div></div>
  </div>
  <div class="foot-bot"><p class="foot-copy">© 2026 <span>MyEstate</span>. Made with ♥ in Mumbai.</p><div class="foot-bot-links"><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Cookies</a></div></div>
</footer>

<script>
// Intersection Observer for reveal
const obs=new IntersectionObserver(e=>e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('in');obs.unobserve(x.target);}}),{threshold:.05});
document.querySelectorAll('.reveal').forEach(r=>obs.observe(r));
window.addEventListener('scroll',()=>document.getElementById('mainNav').classList.toggle('scrolled',scrollY>40));

// Nav dropdown - click based
const navUser=document.getElementById('navUser');
if(navUser){
  const menu=navUser.querySelector('.nav-drop-menu');
  navUser.addEventListener('click',function(e){e.stopPropagation();menu.classList.toggle('open');});
  menu.addEventListener('click',function(e){e.stopPropagation();});
  document.addEventListener('click',function(e){if(!navUser.contains(e.target))menu.classList.remove('open');});
  window.addEventListener('scroll',function(){menu.classList.remove('open');},{passive:true});
}

// Image carousel - track current slide per project
const curSlide = {};
function goSlide(proj, idx) {
  const slides = document.querySelectorAll(`.proj-img-slide[data-proj="${proj}"]`);
  const dots = document.getElementById('pd'+proj).querySelectorAll('.pid');
  slides.forEach(s=>s.classList.remove('active'));
  dots.forEach(d=>d.classList.remove('on'));
  slides[idx].classList.add('active');
  dots[idx].classList.add('on');
  curSlide[proj]=idx;
}

// Auto-advance carousels
<?php foreach($projects as $pi => $p): ?>
curSlide[<?= $pi ?>]=0;
<?php endforeach; ?>

setInterval(()=>{
  <?php foreach($projects as $pi => $p): ?>
  (function(p,total){
    const next=(curSlide[p]+1)%total;
    goSlide(p,next);
  })(<?= $pi ?>,<?= count($p['imgs']) ?>);
  <?php endforeach; ?>
},4000);

function registerInterest(){
  const email=document.getElementById('regEmail').value.trim();
  const name=document.getElementById('regName').value.trim();
  if(!email||!name){alert('Please enter your name and email.');return;}
  alert('Thank you, '+name+'! We\'ll notify you at '+email+' when bookings open.');
  document.getElementById('regEmail').value='';
  document.getElementById('regName').value='';
}
</script>
</body>
</html>
