<?php
// Only registered users can access
session_start();
if(!isset($_COOKIE['user_id'])){
  header("Location: index.php");
  exit();
}
include 'components/connect.php';
$user = $conn->prepare("SELECT name FROM users WHERE id = ?");
$user->execute([$_COOKIE['user_id']]);
$user = $user->fetch(PDO::FETCH_ASSOC);
$userName = $user ? htmlspecialchars($user['name']) : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About MyEstate — Our Story</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300;1,9..40,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<style>
:root{
  --r:#d62828;--rd:#9e1c1c;--rl:#e85555;
  --rp:#fdf1f1;--rp2:#fae6e6;--rp3:#f5d0d0;
  --ink:#1a0505;--ink2:#4a1515;--ink3:#9a6565;
  --white:#fff;--bg:#faf5f5;
  --line:rgba(214,40,40,0.13);
  --ease:cubic-bezier(.22,1,.36,1);
  --sh:0 4px 32px rgba(214,40,40,0.08);
  --sh2:0 24px 72px rgba(214,40,40,0.2);
}
*{margin:0;padding:0;box-sizing:border-box;}
html{font-size:62.5%;scroll-behavior:smooth;}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--ink);overflow-x:hidden;}
::-webkit-scrollbar{width:3px;}
::-webkit-scrollbar-thumb{background:var(--r);}
.nav{position:fixed;top:0;left:0;right:0;z-index:999;padding:1.8rem 6%;display:flex;align-items:center;justify-content:space-between;background:rgba(253,241,241,.92);backdrop-filter:blur(22px);border-bottom:1px solid var(--line);transition:all .35s var(--ease);}
.nav.scrolled{padding:1.2rem 6%;box-shadow:0 4px 32px rgba(214,40,40,.09);}
.logo{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);text-decoration:none;}
.logo span{font-style:italic;color:var(--r);}
.nav-links{display:flex;gap:3.5rem;}
.nav-links a{font-size:1.35rem;font-weight:600;color:var(--ink);text-decoration:none;transition:color .2s;position:relative;padding-bottom:.3rem;}
.nav-links a::after{content:'';position:absolute;bottom:0;left:0;width:0;height:1.5px;background:var(--r);transition:width .3s var(--ease);}
.nav-links a:hover,.nav-links a.active{color:var(--r);}
.nav-links a:hover::after,.nav-links a.active::after{width:100%;}
.nav-btns{display:flex;gap:1.2rem;}
.nb{padding:.9rem 2.2rem;border-radius:99px;font-size:1.3rem;font-weight:700;text-decoration:none;transition:all .25s;font-family:'Outfit',sans-serif;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.6rem;}
.nb.o{background:transparent;color:var(--r);border:1.5px solid rgba(214,40,40,.25);}
.nb.o:hover{background:var(--rp);}
.nb.s{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;box-shadow:0 4px 18px rgba(214,40,40,.32);}
.nb.s:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(214,40,40,.42);}
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
.reveal{opacity:0;transform:translateY(28px);transition:opacity .75s var(--ease),transform .75s var(--ease);}
.reveal.in{opacity:1;transform:translateY(0);}
.eyebrow{display:inline-flex;align-items:center;gap:.5rem;font-size:.95rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--r);background:var(--rp);padding:.35rem 1rem;border-radius:99px;border:1px solid rgba(214,40,40,.12);width:fit-content;margin-bottom:1.4rem;}
.eyebrow::before{content:'';width:.45rem;height:.45rem;border-radius:50%;background:var(--r);animation:blink 2s infinite;flex-shrink:0;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}

/* HERO — warm light theme matching main website */
.ab-hero{min-height:82vh;display:grid;grid-template-columns:1fr 1fr;position:relative;overflow:hidden;background:linear-gradient(145deg,#fff9f9 0%,#fdf1f1 45%,#fae8e8 100%);padding-top:9rem;}
.ab-hero-deco{position:absolute;border-radius:50%;pointer-events:none;border:1px solid rgba(214,40,40,.07);}
.ab-hero-deco.r1{width:80rem;height:80rem;top:-30rem;right:-20rem;}
.ab-hero-deco.r2{width:55rem;height:55rem;top:-15rem;right:-5rem;}
.ab-hero-img{position:relative;overflow:hidden;}
.ab-hero-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;}
.ab-hero-img-grad{position:absolute;inset:0;background:linear-gradient(to left,transparent 30%,rgba(253,241,241,.12) 65%,rgba(253,241,241,.96) 100%);}
.ab-hero-content{position:relative;z-index:2;padding:5rem 7% 8rem;display:flex;flex-direction:column;justify-content:center;}
.ab-tag{display:inline-flex;align-items:center;gap:.6rem;font-size:.95rem;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--r);background:var(--rp);padding:.4rem 1.2rem;border-radius:99px;border:1px solid rgba(214,40,40,.15);margin-bottom:2.5rem;width:fit-content;}
.ab-tag::before{content:'';width:.4rem;height:.4rem;border-radius:50%;background:var(--r);animation:blink 2s infinite;flex-shrink:0;}
.ab-hero-h{font-family:'Cormorant Garamond',serif;font-size:clamp(5rem,7vw,9.5rem);font-weight:700;color:var(--ink);line-height:.9;letter-spacing:-.04em;margin-bottom:2.5rem;}
.ab-hero-h em{font-style:italic;color:var(--r);display:block;}
.ab-hero-sub{font-size:1.65rem;color:var(--ink3);max-width:52rem;line-height:1.75;font-family:'DM Sans',sans-serif;font-weight:300;margin-bottom:4rem;}
.ab-hero-stats{display:flex;gap:0;border:1.5px solid var(--line);border-radius:2rem;overflow:hidden;background:var(--white);width:fit-content;box-shadow:0 8px 32px rgba(214,40,40,.08);}
.abs{padding:2.4rem 3.5rem;border-right:1px solid var(--line);text-align:center;}
.abs:last-child{border-right:none;}
.abs-n{font-family:'Cormorant Garamond',serif;font-size:4.5rem;font-weight:700;color:var(--ink);line-height:1;}
.abs-l{font-size:1.1rem;color:var(--ink3);margin-top:.3rem;letter-spacing:.06em;}
.ab-scroll{display:none;}

/* SECTIONS */
.ab-sec{padding:9rem 7%;}
.ab-sec.alt{background:var(--white);}
.ab-sec.rp{background:linear-gradient(150deg,#fff9f9,#fdf1f1 50%,#fae8e8);}
.ab-h2{font-family:'Cormorant Garamond',serif;font-size:clamp(3.8rem,6vw,7rem);font-weight:700;color:var(--ink);letter-spacing:-.035em;line-height:.94;margin-bottom:2rem;}
.ab-h2 em{font-style:italic;color:var(--r);}
.ab-body{font-size:1.55rem;color:var(--ink3);line-height:1.82;font-family:'DM Sans',sans-serif;font-weight:300;max-width:72rem;}
.ab-body strong{color:var(--ink);font-weight:500;}
.ab-body+.ab-body{margin-top:1.8rem;}

/* STORY */
.story-grid{display:grid;grid-template-columns:1fr 1fr;gap:8rem;align-items:center;}
.story-img-stack{position:relative;height:62rem;}
.si-main{position:absolute;inset:0;border-radius:3rem;overflow:hidden;box-shadow:0 32px 80px rgba(214,40,40,.18);}
.si-main img{width:100%;height:100%;object-fit:cover;}
.si-float{position:absolute;bottom:-3.5rem;right:-3.5rem;width:22rem;height:22rem;border-radius:2rem;overflow:hidden;border:4px solid var(--white);box-shadow:0 16px 48px rgba(214,40,40,.22);}
.si-float img{width:100%;height:100%;object-fit:cover;}
.si-badge{position:absolute;top:3.5rem;left:-3rem;background:var(--white);border-radius:1.8rem;padding:1.8rem 2.4rem;box-shadow:0 12px 40px rgba(214,40,40,.18);border:1px solid var(--line);white-space:nowrap;}
.si-badge-n{font-family:'Cormorant Garamond',serif;font-size:4rem;font-weight:700;color:var(--ink);line-height:1;}
.si-badge-l{font-size:1.2rem;color:var(--ink3);}
.story-quote{font-family:'Cormorant Garamond',serif;font-size:2.4rem;font-style:italic;color:var(--ink2);line-height:1.55;border-left:3px solid var(--r);padding-left:2.8rem;margin:3.5rem 0;}

/* TIMELINE */
.timeline{display:flex;flex-direction:column;gap:0;position:relative;padding-left:4rem;}
.timeline::before{content:'';position:absolute;left:1.2rem;top:0;bottom:0;width:2px;background:linear-gradient(to bottom,var(--r),rgba(214,40,40,.08));}
.tl-item{padding:0 0 5rem 4rem;position:relative;}
.tl-item:last-child{padding-bottom:0;}
.tl-dot{position:absolute;left:-2.8rem;top:.4rem;width:1.6rem;height:1.6rem;border-radius:50%;background:var(--r);box-shadow:0 0 0 4px rgba(214,40,40,.15);}
.tl-year{font-size:1rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--r);margin-bottom:.6rem;}
.tl-title{font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:700;color:var(--ink);margin-bottom:.8rem;line-height:1.1;}
.tl-desc{font-size:1.4rem;color:var(--ink3);line-height:1.75;font-family:'DM Sans',sans-serif;font-weight:300;}

/* ACHIEVEMENTS BENTO */
.ach-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:2rem;}
.ach-big{grid-column:1/5;grid-row:1/3;border-radius:2.8rem;overflow:hidden;position:relative;min-height:48rem;}
.ach-big img{width:100%;height:100%;object-fit:cover;transition:transform 7s ease;}
.ach-big:hover img{transform:scale(1.05);}
.ach-big-ov{position:absolute;inset:0;background:linear-gradient(to top,rgba(20,3,3,.95) 0%,rgba(20,3,3,.2) 60%,transparent 100%);}
.ach-big-cnt{position:absolute;bottom:0;left:0;right:0;padding:3.5rem;}
.ach-big-n{font-family:'Cormorant Garamond',serif;font-size:8rem;font-weight:700;color:rgba(255,255,255,.06);line-height:1;}
.ach-big-s{font-family:'Cormorant Garamond',serif;font-size:4rem;font-weight:700;color:#fff;line-height:1.1;margin-top:-.6rem;}
.ach-big-l{font-size:1.25rem;color:rgba(255,255,255,.5);margin-top:.7rem;}
.ach-stat{border-radius:2rem;background:var(--white);border:1.5px solid var(--line);padding:3rem;display:flex;flex-direction:column;justify-content:space-between;transition:all .35s var(--ease);}
.ach-stat:hover{transform:translateY(-5px);box-shadow:var(--sh2);}
.ach-stat-icon{width:5rem;height:5rem;border-radius:1.4rem;background:var(--rp);display:grid;place-items:center;font-size:2rem;color:var(--r);margin-bottom:1.8rem;transition:all .3s;}
.ach-stat:hover .ach-stat-icon{background:var(--r);color:#fff;}
.ach-stat-n{font-family:'Cormorant Garamond',serif;font-size:5.5rem;font-weight:700;color:var(--ink);line-height:1;margin-bottom:.4rem;}
.ach-stat-l{font-size:1.25rem;color:var(--ink3);}
.as1{grid-column:5/7;}.as2{grid-column:7/9;}.as3{grid-column:9/11;}.as4{grid-column:11/13;}
.as5{grid-column:5/8;grid-row:2;}.as6{grid-column:8/11;grid-row:2;}.as7{grid-column:11/13;grid-row:2;}

/* FULL WIDTH IMAGE — warm overlay */
.ab-fullimg{position:relative;height:55rem;overflow:hidden;}
.ab-fullimg img{width:100%;height:100%;object-fit:cover;filter:brightness(.75);}
.ab-fullimg-ov{position:absolute;inset:0;background:linear-gradient(90deg,rgba(214,40,40,.78) 0%,rgba(150,20,20,.55) 50%,rgba(253,241,241,.15) 100%);}
.ab-fullimg-cnt{position:absolute;inset:0;display:flex;align-items:center;padding:0 8%;}
.ab-fullimg-inner{max-width:72rem;}
.ab-fullimg-inner .eyebrow{background:rgba(255,255,255,.18);color:#fff;border-color:rgba(255,255,255,.3);}
.ab-fullimg-inner .eyebrow::before{background:#fff;}
.ab-fullimg-inner h2{font-family:'Cormorant Garamond',serif;font-size:clamp(4rem,6.5vw,8rem);font-weight:700;color:#fff;letter-spacing:-.035em;line-height:.9;margin-bottom:2.5rem;}
.ab-fullimg-inner h2 em{font-style:italic;color:rgba(255,220,220,.95);}
.ab-fullimg-inner p{font-size:1.6rem;color:rgba(255,255,255,.8);line-height:1.78;font-family:'DM Sans',sans-serif;font-weight:300;max-width:56rem;margin-bottom:3.5rem;}
.ab-cta{display:inline-flex;align-items:center;gap:.9rem;background:#fff;color:var(--r);text-decoration:none;padding:1.5rem 3.4rem;border-radius:99px;font-size:1.45rem;font-weight:800;font-family:'Outfit',sans-serif;box-shadow:0 8px 28px rgba(0,0,0,.2);transition:all .3s;}
.ab-cta:hover{transform:translateY(-3px);background:var(--rp);box-shadow:0 16px 44px rgba(0,0,0,.28);}

/* VALUES */
.values-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:2.4rem;margin-top:5rem;}
.val-card{background:var(--white);border-radius:2.4rem;overflow:hidden;border:1.5px solid var(--line);transition:all .38s var(--ease);}
.val-card:hover{transform:translateY(-7px);box-shadow:var(--sh2);}
.val-img{height:22rem;overflow:hidden;position:relative;}
.val-img img{width:100%;height:100%;object-fit:cover;transition:transform .7s var(--ease);}
.val-card:hover .val-img img{transform:scale(1.07);}
.val-img-ov{position:absolute;inset:0;background:linear-gradient(to bottom,transparent 50%,rgba(253,241,241,.98) 100%);}
.val-body{padding:2.8rem;}
.val-icon{width:4.8rem;height:4.8rem;border-radius:1.2rem;background:var(--rp);display:grid;place-items:center;font-size:2rem;color:var(--r);margin-bottom:1.6rem;transition:all .3s;}
.val-card:hover .val-icon{background:var(--r);color:#fff;}
.val-title{font-family:'Cormorant Garamond',serif;font-size:2.4rem;font-weight:700;color:var(--ink);margin-bottom:.8rem;}
.val-desc{font-size:1.3rem;color:var(--ink3);line-height:1.72;font-family:'DM Sans',sans-serif;font-weight:300;}

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

@media(max-width:1100px){
  .story-grid{grid-template-columns:1fr;gap:5rem;}
  .story-img-stack{height:45rem;}
  .si-float{width:16rem;height:16rem;right:-1.5rem;bottom:-1.5rem;}
  .ach-grid{grid-template-columns:1fr 1fr;}
  .ach-big{grid-column:1/3;grid-row:auto;min-height:32rem;}
  .as1,.as2,.as3,.as4,.as5,.as6,.as7{grid-column:auto;grid-row:auto;}
  .foot-grid{grid-template-columns:1fr 1fr;gap:3rem;}
}
@media(max-width:768px){
  .nav-links,.nav-btns,.nav-right{display:none;}
  .ab-hero-h{font-size:5.5rem;}
  .ab-sec{padding:6rem 5%;}
  .values-grid{grid-template-columns:1fr;}
  .ach-grid{grid-template-columns:1fr;}
  .ach-big{grid-column:1;}
  .ab-fullimg{height:50rem;}
  .foot-grid{grid-template-columns:1fr;}
  .foot-bot{flex-direction:column;align-items:flex-start;}
}
</style>
</head>
<body>

<nav class="nav" id="mainNav">
  <a href="home.php" class="logo">My<span>Estate</span></a>
  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="listings.php">Properties</a>
    <a href="upcoming.php">Upcoming</a>
    <a href="about.php" class="active">About</a>
    <a href="contact.php">Contact</a>
  </div>
  <div class="nav-right">
    <a href="saved.php" class="nav-icon"><i class="fas fa-heart"></i></a>
    <div class="nav-user" id="navUser">
      <div class="nav-av"><?= strtoupper(substr($userName, 0, 1)); ?></div>
      <span style="font-size:1.3rem;font-weight:700;color:var(--ink);"><?= $userName ?></span>
      <i class="fas fa-chevron-down" style="font-size:1rem;color:var(--ink3);margin-left:.4rem;"></i>
      <div class="nav-drop-menu">
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

<section class="ab-hero">
  <div class="ab-hero-deco r1"></div>
  <div class="ab-hero-deco r2"></div>
  <div class="ab-hero-content reveal">
    <div class="ab-tag">Our Story</div>
    <h1 class="ab-hero-h">Built on <em>Trust.</em><br><span style="font-size:.62em;color:var(--ink3);font-weight:400;font-family:'Outfit',sans-serif;font-style:normal;">Driven by</span><em>Dreams.</em></h1>
    <p class="ab-hero-sub">MyEstate was born from a simple belief — that every family deserves a home they love, without the hassle, the fraud, or the hidden costs.</p>
    <div class="ab-hero-stats">
      <div class="abs"><div class="abs-n">8+</div><div class="abs-l">Properties Listed</div></div>
      <div class="abs"><div class="abs-n">20+</div><div class="abs-l">Happy Families</div></div>
      <div class="abs"><div class="abs-n">2</div><div class="abs-l">Cities Covered</div></div>
      <div class="abs"><div class="abs-n">4.9★</div><div class="abs-l">Average Rating</div></div>
    </div>
  </div>
  <div class="ab-hero-img">
    <img src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=1400&q=90&auto=format" alt="MyEstate premium real estate">
    <div class="ab-hero-img-grad"></div>
  </div>
</section>

<section class="ab-sec alt">
  <div class="story-grid">
    <div class="story-img-stack reveal">
      <div class="si-main"><img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1200&q=88&auto=format" alt="Our team"></div>
      <div class="si-float"><img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=700&q=88&auto=format" alt="Property"></div>
      <div class="si-badge"><div class="si-badge-n">2022</div><div class="si-badge-l">Founded in Mumbai</div></div>
    </div>
    <div class="story-text reveal" style="transition-delay:.15s">
      <div class="eyebrow">Who We Are</div>
      <h2 class="ab-h2">A Platform <em>Built</em> for Real People</h2>
      <p class="ab-body">MyEstate started in 2022 with one goal: make real estate in Mumbai and Pune <strong>honest, transparent, and accessible</strong> for everyone. We were tired of seeing families get misled by brokers, overcharged on commissions, or stuck with properties they never truly saw.</p>
      <p class="ab-body">Today, we're a growing platform connecting verified property owners directly with serious buyers and renters. Every listing on MyEstate is physically verified by our team before it goes live. No surprises. No fake photos. Just real homes for real people.</p>
      <blockquote class="story-quote">"We don't just list properties — we walk you through the journey of finding a home that fits your life."</blockquote>
      <p class="ab-body">From a modest start with 3 listings in Bandra, we've grown to cover prime locations across Mumbai and Pune, with thousands of enquiries processed and hundreds of families successfully settled.</p>
    </div>
  </div>
</section>

<section class="ab-sec rp">
  <div class="reveal" style="margin-bottom:5rem;">
    <div class="eyebrow">Our Journey</div>
    <h2 class="ab-h2">How We <em>Grew</em></h2>
    <p class="ab-body">Every milestone shaped who we are today.</p>
  </div>
  <div class="timeline">
    <div class="tl-item reveal"><div class="tl-dot"></div><div class="tl-year">2022 — Year One</div><div class="tl-title">The First Listing</div><p class="tl-desc">MyEstate launched in Bandra West, Mumbai, with just 3 verified properties and a team of 2. Our founder, frustrated with broker fraud, built the first version of the platform in under 6 weeks. Within a month, our first family moved into their new home through MyEstate.</p></div>
    <div class="tl-item reveal" style="transition-delay:.08s"><div class="tl-dot"></div><div class="tl-year">2022 — Growth</div><div class="tl-title">Expanding to Pune</div><p class="tl-desc">After overwhelming demand from Pune-based buyers, we expanded coverage to Hinjewadi, Baner, and Koregaon Park. We introduced our rigorous 3-step property verification process — site visit, document check, and photo audit — to ensure zero fraudulent listings.</p></div>
    <div class="tl-item reveal" style="transition-delay:.16s"><div class="tl-dot"></div><div class="tl-year">2023 — Trust Milestone</div><div class="tl-title">10 Families. Zero Complaints.</div><p class="tl-desc">By mid-2023, we had successfully helped 10 families find verified homes — with zero fraud complaints and zero hidden charges. We received our first 5-star batch of reviews, and word spread. Our user base doubled in 3 months purely through referrals.</p></div>
    <div class="tl-item reveal" style="transition-delay:.24s"><div class="tl-dot"></div><div class="tl-year">2024 — Platform 2.0</div><div class="tl-title">Rebuilding From Scratch</div><p class="tl-desc">We completely redesigned the platform with a new focus on mobile experience, faster search filters, and a clean dashboard for both buyers and property owners. Post-sale support — including interior design consultation and tenant management — was introduced as a free service for all registered users.</p></div>
    <div class="tl-item reveal" style="transition-delay:.32s"><div class="tl-dot"></div><div class="tl-year">2025–2026 — Today</div><div class="tl-title">Growing Every Day</div><p class="tl-desc">With 8+ active listings, 20+ registered buyers, and upcoming projects launching across Bandra, Worli, and Kothrud, MyEstate is on a mission to become the most trusted real estate platform in Maharashtra. The journey has just begun.</p></div>
  </div>
  <!-- Journey Images -->
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:2rem;margin-top:5rem;">
    <div style="border-radius:2rem;overflow:hidden;height:28rem;" class="reveal"><img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&q=85&auto=format" alt="Modern apartment interior" style="width:100%;height:100%;object-fit:cover;transition:transform .6s var(--ease);" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'"></div>
    <div style="border-radius:2rem;overflow:hidden;height:28rem;" class="reveal" style="transition-delay:.1s"><img src="https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&q=85&auto=format" alt="Luxury living room" style="width:100%;height:100%;object-fit:cover;transition:transform .6s var(--ease);" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'"></div>
    <div style="border-radius:2rem;overflow:hidden;height:28rem;" class="reveal" style="transition-delay:.2s"><img src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=800&q=85&auto=format" alt="Beautiful home exterior" style="width:100%;height:100%;object-fit:cover;transition:transform .6s var(--ease);" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'"></div>
  </div>
</section>

<section class="ab-sec alt">
  <div class="reveal" style="margin-bottom:5rem;">
    <div class="eyebrow">What We've Built</div>
    <h2 class="ab-h2">Our <em>Achievements</em></h2>
    <p class="ab-body">Numbers that reflect real impact — not just metrics.</p>
  </div>
  <div class="ach-grid">
    <div class="ach-big reveal"><img src="https://images.unsplash.com/photo-1613977257365-aaae5a9817ff?w=1200&q=88&auto=format" alt="Achievement"><div class="ach-big-ov"></div><div class="ach-big-cnt"><div class="ach-big-n">100%</div><div class="ach-big-s">Verified<br>Always.</div><div class="ach-big-l">Every listing physically checked by our team</div></div></div>
    <div class="ach-stat as1 reveal" style="transition-delay:.05s"><div class="ach-stat-icon"><i class="fas fa-home"></i></div><div><div class="ach-stat-n">8+</div><div class="ach-stat-l">Active Listings</div></div></div>
    <div class="ach-stat as2 reveal" style="transition-delay:.09s"><div class="ach-stat-icon"><i class="fas fa-users"></i></div><div><div class="ach-stat-n">20+</div><div class="ach-stat-l">Registered Buyers</div></div></div>
    <div class="ach-stat as3 reveal" style="transition-delay:.13s"><div class="ach-stat-icon"><i class="fas fa-city"></i></div><div><div class="ach-stat-n">2</div><div class="ach-stat-l">Cities Covered</div></div></div>
    <div class="ach-stat as4 reveal" style="transition-delay:.17s"><div class="ach-stat-icon"><i class="fas fa-star"></i></div><div><div class="ach-stat-n">4.9★</div><div class="ach-stat-l">Avg. Rating</div></div></div>
    <div class="ach-stat as5 reveal" style="transition-delay:.21s"><div class="ach-stat-icon"><i class="fas fa-rupee-sign"></i></div><div><div class="ach-stat-n">₹0</div><div class="ach-stat-l">Hidden Fees — Ever</div></div></div>
    <div class="ach-stat as6 reveal" style="transition-delay:.25s"><div class="ach-stat-icon"><i class="fas fa-handshake"></i></div><div><div class="ach-stat-n">100%</div><div class="ach-stat-l">Direct Owner Contact</div></div></div>
    <div class="ach-stat as7 reveal" style="transition-delay:.29s"><div class="ach-stat-icon"><i class="fas fa-shield-alt"></i></div><div><div class="ach-stat-n">0</div><div class="ach-stat-l">Fraud Cases</div></div></div>
  </div>
</section>

<!-- GALLERY -->
<section class="ab-sec" style="padding:0;">
  <div style="display:grid;grid-template-columns:2fr 1fr 1fr;grid-template-rows:22rem 22rem;gap:1.5rem;padding:0 7%;">
    <div style="grid-row:1/3;border-radius:2.4rem;overflow:hidden;position:relative;" class="reveal"><img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=88&auto=format" alt="Luxury villa" style="width:100%;height:100%;object-fit:cover;"><div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.4),transparent);"></div><div style="position:absolute;bottom:2rem;left:2.5rem;color:#fff;font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;">Premium Villas</div></div>
    <div style="border-radius:2.4rem;overflow:hidden;position:relative;" class="reveal" style="transition-delay:.08s"><img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=700&q=85&auto=format" alt="Modern apartment" style="width:100%;height:100%;object-fit:cover;"><div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.35),transparent);"></div><div style="position:absolute;bottom:1.5rem;left:2rem;color:#fff;font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:700;">City Apartments</div></div>
    <div style="border-radius:2.4rem;overflow:hidden;position:relative;" class="reveal" style="transition-delay:.14s"><img src="https://images.unsplash.com/photo-1486325212027-8081e485255e?w=700&q=85&auto=format" alt="Mumbai skyline" style="width:100%;height:100%;object-fit:cover;"><div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.35),transparent);"></div><div style="position:absolute;bottom:1.5rem;left:2rem;color:#fff;font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:700;">Mumbai Skyline</div></div>
    <div style="border-radius:2.4rem;overflow:hidden;position:relative;" class="reveal" style="transition-delay:.2s"><img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=700&q=85&auto=format" alt="Luxury home" style="width:100%;height:100%;object-fit:cover;"><div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.35),transparent);"></div><div style="position:absolute;bottom:1.5rem;left:2rem;color:#fff;font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:700;">Luxury Homes</div></div>
  </div>
</section>

<div class="ab-fullimg">
  <img src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=2000&q=88&auto=format" alt="Trust">
  <div class="ab-fullimg-ov"></div>
  <div class="ab-fullimg-cnt">
    <div class="ab-fullimg-inner reveal">
      <div class="eyebrow">Our Promise</div>
      <h2>We're Not<br>Just a <em>Platform.</em><br>We're Your <em>Partner.</em></h2>
      <p>From the moment you register to the day you receive your keys — and beyond — we stand beside you. Our team is available 7 days a week, and our post-sale support never expires.</p>
      <a href="listings.php" class="ab-cta"><i class="fas fa-search"></i> Explore All Properties</a>
    </div>
  </div>
</div>

<section class="ab-sec rp">
  <div class="reveal">
    <div class="eyebrow">What Drives Us</div>
    <h2 class="ab-h2">Our Core <em>Values</em></h2>
    <p class="ab-body">Three pillars that guide every decision we make at MyEstate.</p>
  </div>
  <div class="values-grid">
    <div class="val-card reveal" style="transition-delay:.06s"><div class="val-img"><img src="https://images.unsplash.com/photo-1563986768494-4dee2763ff3f?w=800&q=80&auto=format" alt="Transparency"><div class="val-img-ov"></div></div><div class="val-body"><div class="val-icon"><i class="fas fa-eye"></i></div><div class="val-title">Radical Transparency</div><p class="val-desc">Every price you see is the actual price. Every photo is taken by our team. Every detail has been verified on-site. No hidden costs, no shock revelations on moving day.</p></div></div>
    <div class="val-card reveal" style="transition-delay:.12s"><div class="val-img"><img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?w=800&q=80&auto=format" alt="People First"><div class="val-img-ov"></div></div><div class="val-body"><div class="val-icon"><i class="fas fa-heart"></i></div><div class="val-title">People Over Profit</div><p class="val-desc">We charge zero commission to buyers. We believe homeownership should not be a transaction — it's a life milestone. Our success is measured by satisfied families, not closed deals.</p></div></div>
    <div class="val-card reveal" style="transition-delay:.18s"><div class="val-img"><img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&q=80&auto=format" alt="Excellence"><div class="val-img-ov"></div></div><div class="val-body"><div class="val-icon"><i class="fas fa-award"></i></div><div class="val-title">Relentless Excellence</div><p class="val-desc">From the UI you use to the paperwork we process — we obsess over quality at every step. We'd rather list 8 perfect properties than 80 mediocre ones.</p></div></div>
  </div>
</section>

<footer class="footer">
  <div class="foot-grid">
    <div class="foot-brand"><span class="foot-logo">My<span>Estate</span></span><p>Your trusted partner for premium real estate across Mumbai and Pune. Verified listings, expert guidance, seamless transactions.</p><div class="foot-socials"><a href="#" class="fsc"><i class="fab fa-instagram"></i></a><a href="#" class="fsc"><i class="fab fa-facebook-f"></i></a><a href="#" class="fsc"><i class="fab fa-twitter"></i></a><a href="#" class="fsc"><i class="fab fa-youtube"></i></a></div></div>
    <div class="foot-col"><h4>Properties</h4><a href="listings.php"><i class="fas fa-chevron-right"></i>Apartments</a><a href="listings.php"><i class="fas fa-chevron-right"></i>Villas</a><a href="listings.php"><i class="fas fa-chevron-right"></i>Plots</a><a href="listings.php"><i class="fas fa-chevron-right"></i>Commercial</a></div>
    <div class="foot-col"><h4>Quick Links</h4><a href="home.php"><i class="fas fa-chevron-right"></i>Dashboard</a><a href="listings.php"><i class="fas fa-chevron-right"></i>All Listings</a><a href="post_property.php"><i class="fas fa-chevron-right"></i>Post a Property</a><a href="about.php"><i class="fas fa-chevron-right"></i>About Us</a></div>
    <div class="foot-col"><h4>Contact Us</h4><div class="fci"><div class="fci-ic"><i class="fas fa-map-marker-alt"></i></div><div class="fci-t"><strong>Office</strong>Nalasopara West, Maharashtra — 401203</div></div><div class="fci"><div class="fci-ic"><i class="fas fa-envelope"></i></div><div class="fci-t"><strong>Email</strong>rayyanbhagate@gmail.com</div></div></div>
  </div>
  <div class="foot-bot"><p class="foot-copy">© 2026 <span>MyEstate</span>. All rights reserved.</p><div class="foot-bot-links"><a href="#">Privacy Policy</a><a href="#">Terms of Use</a><a href="#">Cookie Policy</a></div></div>
</footer>

<script>
const obs=new IntersectionObserver(e=>e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('in');obs.unobserve(x.target);}}),{threshold:.06});
document.querySelectorAll('.reveal').forEach(r=>obs.observe(r));
window.addEventListener('scroll',()=>document.getElementById('mainNav').classList.toggle('scrolled',scrollY>40));
// Profile dropdown — click to open, click outside to close, stays open on hover inside
const navUser=document.getElementById('navUser');
if(navUser){
  const menu=navUser.querySelector('.nav-drop-menu');
  navUser.addEventListener('click',function(e){
    e.stopPropagation();
    menu.classList.toggle('open');
  });
  // Keep open while hovering inside the menu
  menu.addEventListener('click',function(e){e.stopPropagation();});
  document.addEventListener('click',function(e){
    if(!navUser.contains(e.target))menu.classList.remove('open');
  });
  // Remove on scroll
  window.addEventListener('scroll',function(){menu.classList.remove('open');},{passive:true});
}
</script>
</body>
</html>