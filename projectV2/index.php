<?php
// ── COOKIE CHECK: if already logged in, redirect ──
if(isset($_COOKIE['user_id'])){
  header("Location: home.php");
  exit();
}
if(isset($_COOKIE['admin_id'])){
  header("Location: admin/dashboard.php");
  exit();
}

// ── DB CONNECTION ──
include 'components/connect.php';

// ── STATS ──
$prop_count = $conn->query("SELECT COUNT(*) FROM property")->fetchColumn();
$user_count = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

// ── FEATURED PROPERTIES (from DB) ──
$featured = $conn->query("SELECT id,property_name,type,address,price,bedroom,bathroom,image_01 FROM property ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// ── PRICE FORMATTER ──
function formatPrice($p){
  if($p >= 10000000) return '₹' . round($p/10000000,1) . ' Cr';
  if($p >= 100000)   return '₹' . round($p/100000,1)   . ' L';
  return '₹' . number_format($p);
}

// ── SAVE REDIRECT INTENT ──
if(isset($_GET['redirect'])){
  $_SESSION['redirect_after_login'] = $_GET['redirect'];
}
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MyEstate — Find Your Dream Home</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
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

/* ── SHARED ── */
.eyebrow{display:inline-flex;align-items:center;gap:.5rem;font-size:.95rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--r);background:var(--rp);padding:.35rem 1rem;border-radius:99px;border:1px solid rgba(214,40,40,.12);width:fit-content;}
.eyebrow::before{content:'';width:.45rem;height:.45rem;border-radius:50%;background:var(--r);animation:blink 2s infinite;flex-shrink:0;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sec-title{font-family:'Cormorant Garamond',serif;font-size:clamp(3.4rem,5vw,5.8rem);font-weight:700;color:var(--ink);letter-spacing:-.03em;line-height:1.0;margin-bottom:1.4rem;}
.sec-title em{font-style:italic;color:var(--r);}
.sec-sub{font-size:1.45rem;color:var(--ink3);line-height:1.75;max-width:50rem;}
.sec-hd{display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:2rem;}
.btn-outline{display:inline-flex;align-items:center;gap:.8rem;font-size:1.35rem;font-weight:700;color:var(--r);text-decoration:none;border:1.5px solid rgba(214,40,40,.22);padding:1rem 2.2rem;border-radius:99px;background:var(--white);transition:all .25s;white-space:nowrap;cursor:pointer;}
.btn-outline:hover{background:var(--rp);gap:1.4rem;}
.reveal{opacity:0;transform:translateY(28px);transition:opacity .75s var(--ease),transform .75s var(--ease);}
.reveal.in{opacity:1;transform:translateY(0);}

/* ── NAV ── */
.nav{position:fixed;top:0;left:0;right:0;z-index:999;padding:1.8rem 6%;display:flex;align-items:center;justify-content:space-between;background:rgba(253,241,241,.92);backdrop-filter:blur(22px);border-bottom:1px solid var(--line);transition:all .35s var(--ease);}
.nav.scrolled{padding:1.2rem 6%;box-shadow:0 4px 32px rgba(214,40,40,.09);}
.logo{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);text-decoration:none;}
.logo span{font-style:italic;color:var(--r);}
.nav-links{display:flex;gap:3.5rem;}
.nav-links a{font-size:1.35rem;font-weight:600;color:var(--ink);text-decoration:none;transition:color .2s;position:relative;padding-bottom:.3rem;cursor:pointer;}
.nav-links a::after{content:'';position:absolute;bottom:0;left:0;width:0;height:1.5px;background:var(--r);transition:width .3s var(--ease);}
.nav-links a:hover{color:var(--r);}
.nav-links a:hover::after{width:100%;}
.nav-btns{display:flex;gap:1.2rem;}
.nb{padding:.9rem 2.2rem;border-radius:99px;font-size:1.3rem;font-weight:700;text-decoration:none;transition:all .25s;font-family:'Outfit',sans-serif;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.6rem;}
.nb.g{background:transparent;color:var(--r);border:1.5px solid rgba(214,40,40,.25);}
.nb.g:hover{background:var(--rp);}
.nb.s{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;box-shadow:0 4px 18px rgba(214,40,40,.32);}
.nb.s:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(214,40,40,.42);}
.nav-ham{display:none;flex-direction:column;gap:.5rem;cursor:pointer;padding:.5rem;}
.nav-ham span{width:2.2rem;height:2px;background:var(--ink);border-radius:2px;transition:all .3s;}
.mob-nav{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(253,241,241,.98);backdrop-filter:blur(24px);z-index:998;flex-direction:column;padding:8rem 6% 4rem;}
.mob-nav.open{display:flex;}
.mob-nav a{font-size:1.8rem;font-weight:600;color:var(--ink);text-decoration:none;padding:1.4rem 0;border-bottom:1px solid var(--line);transition:color .2s;cursor:pointer;}
.mob-nav a:hover{color:var(--r);}
.mob-btns{display:flex;gap:1rem;padding-top:2.5rem;}
.mob-close{position:absolute;top:2rem;right:6%;font-size:2.4rem;color:var(--ink3);cursor:pointer;background:none;border:none;}

/* ── REGISTER POPUP ── */
.popup-overlay{display:none;position:fixed;inset:0;z-index:1100;background:rgba(10,2,2,.55);backdrop-filter:blur(6px);align-items:center;justify-content:center;}
.popup-overlay.open{display:flex;}
.popup-box{background:var(--white);border-radius:2.8rem;padding:5rem 4.5rem;max-width:50rem;width:90%;box-shadow:0 32px 80px rgba(214,40,40,.22);position:relative;text-align:center;}
.popup-close{position:absolute;top:2rem;right:2.4rem;font-size:2rem;color:var(--ink3);cursor:pointer;background:none;border:none;transition:color .2s;}
.popup-close:hover{color:var(--r);}
.popup-icon{width:7rem;height:7rem;border-radius:50%;background:var(--rp);border:2px solid rgba(214,40,40,.15);display:grid;place-items:center;font-size:3rem;color:var(--r);margin:0 auto 2.5rem;}
.popup-box h3{font-family:'Cormorant Garamond',serif;font-size:3.6rem;font-weight:700;color:var(--ink);margin-bottom:1rem;}
.popup-box p{font-size:1.35rem;color:var(--ink3);line-height:1.7;margin-bottom:3rem;}
.popup-btns{display:flex;gap:1.2rem;justify-content:center;flex-wrap:wrap;align-items:center;}
.popup-btns a{padding:1.3rem 3rem;border-radius:99px;font-size:1.4rem;font-weight:800;text-decoration:none;transition:all .25s;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:.7rem;}
.pb-reg{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;box-shadow:0 8px 24px rgba(214,40,40,.35);}
.pb-reg:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(214,40,40,.5);}
.pb-log{border:1.5px solid rgba(214,40,40,.25);color:var(--r);background:var(--rp);}
.pb-log:hover{background:var(--rp2);}
.popup-divider{font-size:1.2rem;color:var(--ink3);}

/* ── ENQUIRY POPUP ── */
.enq-overlay{display:none;position:fixed;inset:0;z-index:1100;background:rgba(10,2,2,.55);backdrop-filter:blur(6px);align-items:center;justify-content:center;}
.enq-overlay.open{display:flex;}
.enq-box{background:var(--white);border-radius:2.8rem;padding:4.5rem;max-width:54rem;width:92%;box-shadow:0 32px 80px rgba(214,40,40,.22);position:relative;}
.enq-box h3{font-family:'Cormorant Garamond',serif;font-size:3.2rem;font-weight:700;color:var(--ink);margin-bottom:.6rem;}
.enq-box p{font-size:1.3rem;color:var(--ink3);margin-bottom:2.8rem;}
.enq-close{position:absolute;top:2rem;right:2.4rem;font-size:2rem;color:var(--ink3);cursor:pointer;background:none;border:none;}
.enq-close:hover{color:var(--r);}
.enq-row{display:grid;grid-template-columns:1fr 1fr;gap:1.4rem;margin-bottom:1.4rem;}
.enq-field{display:flex;flex-direction:column;gap:.5rem;}
.enq-field label{font-size:1.15rem;font-weight:600;color:var(--ink2);}
.enq-field input,.enq-field select,.enq-field textarea{padding:1.1rem 1.6rem;border:1.5px solid var(--line);border-radius:1.2rem;font-size:1.3rem;font-family:'Outfit',sans-serif;color:var(--ink);background:var(--rp);outline:none;transition:border .2s;}
.enq-field input:focus,.enq-field select:focus,.enq-field textarea:focus{border-color:rgba(214,40,40,.4);}
.enq-field.full{grid-column:1/-1;}
.enq-field textarea{resize:vertical;min-height:9rem;}
.enq-submit{width:100%;padding:1.4rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;font-size:1.45rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 8px 24px rgba(214,40,40,.35);transition:all .25s;margin-top:.5rem;}
.enq-submit:hover{transform:translateY(-2px);box-shadow:0 14px 36px rgba(214,40,40,.5);}

/* ── HERO ── */
.hero{height:100vh;display:grid;grid-template-columns:1fr 1fr;overflow:hidden;}
.hero-l{background:linear-gradient(150deg,#fff9f9 0%,#fdf1f1 50%,#fae8e8 100%);padding:0 6% 0 7%;display:flex;flex-direction:column;justify-content:center;position:relative;}
.hero-l::after{content:'';position:absolute;right:0;top:0;bottom:0;width:5rem;background:linear-gradient(to right,transparent,#faf5f5);z-index:2;pointer-events:none;}
.hero-tag{display:inline-flex;align-items:center;gap:.7rem;font-size:.95rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--r);margin-bottom:2.4rem;background:var(--rp);padding:.35rem 1rem;border-radius:99px;border:1px solid rgba(214,40,40,.14);width:fit-content;}
.hero-tag::before{content:'';width:.45rem;height:.45rem;border-radius:50%;background:var(--r);animation:blink 2s infinite;flex-shrink:0;}
.hero-h{font-family:'Cormorant Garamond',serif;font-size:clamp(4.8rem,6.5vw,9rem);font-weight:700;line-height:.9;color:var(--ink);letter-spacing:-.04em;margin-bottom:2.4rem;}
.hero-h em{font-style:italic;color:var(--r);display:block;margin-left:3rem;}
.hero-sub{font-size:1.55rem;color:var(--ink3);max-width:42rem;line-height:1.72;margin-bottom:3.8rem;}
.hero-ctas{display:flex;align-items:center;gap:1.6rem;margin-bottom:4.5rem;flex-wrap:wrap;}
.cta-a{display:flex;align-items:center;gap:.9rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;padding:1.4rem 3rem;font-size:1.45rem;font-weight:800;cursor:pointer;text-decoration:none;box-shadow:0 8px 28px rgba(214,40,40,.3);transition:all .3s;font-family:'Outfit',sans-serif;}
.cta-a:hover{transform:translateY(-3px);box-shadow:0 16px 42px rgba(214,40,40,.42);}
.cta-b{display:flex;align-items:center;gap:.9rem;color:var(--ink3);font-size:1.45rem;font-weight:600;text-decoration:none;border:1.5px solid var(--line);padding:1.3rem 2.6rem;border-radius:99px;background:rgba(255,255,255,.8);transition:all .25s;}
.cta-b:hover{border-color:rgba(214,40,40,.3);color:var(--r);}
.hero-stats{display:flex;border:1.5px solid var(--line);border-radius:1.8rem;background:var(--white);overflow:hidden;box-shadow:var(--sh);width:fit-content;}
.hst{padding:1.5rem 2.8rem;border-right:1px solid var(--line);text-align:center;}
.hst:last-child{border-right:none;}
.hst-n{font-family:'Cormorant Garamond',serif;font-size:3.4rem;font-weight:700;color:var(--ink);line-height:1;}
.hst-l{font-size:1rem;color:var(--ink3);margin-top:.3rem;text-transform:uppercase;letter-spacing:.1em;}
.hero-r{position:relative;overflow:hidden;}
.hrs{position:absolute;inset:0;}
.hrs-slide{position:absolute;inset:0;opacity:0;transition:opacity 1.2s var(--ease);}
.hrs-slide.on{opacity:1;}
.hrs-slide img{width:100%;height:100%;object-fit:cover;}
.hero-r-grad{position:absolute;inset:0;background:linear-gradient(to right,rgba(250,240,240,.2) 0%,transparent 25%),linear-gradient(to top,rgba(20,2,2,.52) 0%,transparent 55%);z-index:2;pointer-events:none;}
.hfc{position:absolute;z-index:4;background:rgba(255,255,255,.93);backdrop-filter:blur(20px);border-radius:1.4rem;padding:1.2rem 1.6rem;border:1px solid rgba(255,255,255,.55);box-shadow:0 8px 30px rgba(0,0,0,.12);}
.hfc-1{top:7rem;left:2.5rem;animation:fl1 5s ease-in-out infinite;}
.hfc-2{bottom:11rem;right:2.5rem;animation:fl2 5s ease-in-out 2s infinite;}
.hfc-3{bottom:3rem;left:2.5rem;animation:fl1 6s ease-in-out 1s infinite;}
@keyframes fl1{0%,100%{transform:translateY(0)}50%{transform:translateY(-9px)}}
@keyframes fl2{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}
.hfc-label{font-size:.95rem;color:var(--ink3);margin-bottom:.25rem;}
.hfc-val{font-size:1.35rem;font-weight:800;color:var(--ink);display:flex;align-items:center;gap:.45rem;}
.hfc-val i{color:var(--r);}
.hfc-sub{font-size:.95rem;font-weight:700;color:#1a9c4e;margin-top:.2rem;}
.hfc-sub.red{color:var(--r);}
.hrs-dots{position:absolute;bottom:2rem;right:2.5rem;display:flex;gap:.55rem;z-index:5;}
.hrsd{width:.6rem;height:.6rem;border-radius:50%;background:rgba(255,255,255,.35);cursor:pointer;transition:all .3s;}
.hrsd.on{background:#fff;width:1.8rem;border-radius:99px;}

/* ── FEATURED ── */
.feat{background:var(--white);display:flex;flex-direction:column;overflow:hidden;}
.feat-hd{padding:3.5rem 6% 3rem;flex-shrink:0;}
.feat-hd .sec-hd{margin-bottom:0;}
.feat-stage{position:relative;flex:1;overflow:hidden;min-height:0;}
.fs{position:absolute;inset:0;opacity:0;z-index:0;transition:opacity 1.1s var(--ease);}
.fs.on{opacity:1;z-index:1;}
.fs img{width:100%;height:100%;object-fit:cover;transition:transform 7s ease;}
.fs.on img{transform:scale(1.04);}
.fs-ov{position:absolute;inset:0;background:linear-gradient(110deg,rgba(4,0,0,.88) 0%,rgba(4,0,0,.5) 38%,rgba(4,0,0,.1) 65%,transparent 100%);z-index:2;}
.fs-ctr{position:absolute;top:3rem;right:6%;z-index:6;display:flex;align-items:center;gap:1.6rem;}
.fs-cn{font-family:'Cormorant Garamond',serif;font-size:2.4rem;font-weight:700;color:rgba(255,255,255,.9);}
.fs-ct{font-size:1.8rem;color:rgba(255,255,255,.3);}
.fs-nav{display:flex;gap:.8rem;}
.fs-nb{width:4.2rem;height:4.2rem;border-radius:50%;border:1.5px solid rgba(255,255,255,.22);background:rgba(255,255,255,.07);backdrop-filter:blur(10px);color:#fff;font-size:1.3rem;cursor:pointer;display:grid;place-items:center;transition:all .2s;}
.fs-nb:hover{background:rgba(255,255,255,.2);}
.fs-cnt{position:absolute;bottom:0;left:0;right:0;padding:5rem 6% 4.5rem;z-index:6;display:flex;align-items:flex-end;justify-content:space-between;gap:4rem;}
.fs-type{font-size:1.15rem;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.18em;margin-bottom:.5rem;}
.fs-name{font-family:'Cormorant Garamond',serif;font-size:clamp(4rem,5.5vw,7.5rem);font-weight:700;color:#fff;line-height:.92;margin-bottom:1.6rem;letter-spacing:-.025em;}
.fs-addr{display:flex;align-items:center;gap:.6rem;font-size:1.4rem;color:rgba(255,255,255,.6);margin-bottom:2rem;}
.fs-addr i{color:rgba(214,40,40,.9);}
.fs-pills{display:flex;gap:1rem;flex-wrap:wrap;}
.fs-pill{display:flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.1);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.85);padding:.6rem 1.4rem;border-radius:99px;font-size:1.2rem;font-weight:600;}
.fs-pill i{color:rgba(214,40,40,.85);}
.fs-right{display:flex;flex-direction:column;align-items:flex-end;gap:2rem;flex-shrink:0;}
.fs-pbox{background:rgba(255,255,255,.09);backdrop-filter:blur(22px);border:1px solid rgba(255,255,255,.16);border-radius:2rem;padding:2.2rem 3rem;text-align:right;}
.fs-pl{font-size:1.1rem;color:rgba(255,255,255,.45);margin-bottom:.4rem;}
.fs-price{font-family:'Cormorant Garamond',serif;font-size:4.5rem;font-weight:700;color:#fff;line-height:1;}
.fs-enq{display:flex;align-items:center;gap:.8rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;padding:1.5rem 3rem;font-size:1.45rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 8px 28px rgba(214,40,40,.45);transition:all .25s;text-decoration:none;}
.fs-enq:hover{transform:translateY(-3px);box-shadow:0 16px 44px rgba(214,40,40,.55);}
.fs-dots{position:absolute;left:6%;bottom:2.2rem;display:flex;gap:.8rem;z-index:6;}
.fsd{height:3px;width:2.8rem;border-radius:99px;background:rgba(255,255,255,.18);cursor:pointer;transition:all .35s;overflow:hidden;position:relative;}
.fsd.on{width:5.5rem;background:rgba(255,255,255,.35);}
.fsd-bar{position:absolute;left:0;top:0;height:100%;background:var(--r);width:0%;}
.fsd.on .fsd-bar{width:100%;transition:width 3.5s linear;}

/* ── UPCOMING ── */
.upcoming{background:var(--bg);display:flex;flex-direction:column;overflow:hidden;}
.up-hd{padding:3.5rem 6% 3rem;flex-shrink:0;}
.up-hd .sec-hd{margin-bottom:0;}
.up-acc{flex:1;display:flex;overflow:hidden;min-height:0;}
.up-p{flex:1;position:relative;overflow:hidden;cursor:pointer;transition:flex .75s var(--ease);border-right:1px solid rgba(0,0,0,.08);}
.up-p:last-child{border-right:none;}
.up-p:hover{flex:3.8;}
.up-p img{width:100%;height:100%;object-fit:cover;transition:transform .8s var(--ease);}
.up-p:hover img{transform:scale(1.05);}
.up-ov{position:absolute;inset:0;background:linear-gradient(to top,rgba(5,0,0,.95) 0%,rgba(5,0,0,.4) 50%,rgba(5,0,0,.12) 100%);transition:.5s;}
.up-p:hover .up-ov{background:linear-gradient(to top,rgba(5,0,0,.97) 0%,rgba(5,0,0,.22) 55%,transparent 100%);}
.up-vl{position:absolute;bottom:4rem;left:50%;transform:translateX(-50%) rotate(-90deg);white-space:nowrap;font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:700;color:rgba(255,255,255,.2);letter-spacing:.08em;transition:opacity .3s;pointer-events:none;}
.up-p:hover .up-vl{opacity:0;}
.up-cnt{position:absolute;bottom:0;left:0;right:0;padding:4rem 3.5rem;z-index:2;transform:translateY(2.5rem);opacity:0;transition:all .5s var(--ease) .1s;}
.up-p:hover .up-cnt{transform:translateY(0);opacity:1;}
.up-n{font-family:'Cormorant Garamond',serif;font-size:12rem;font-weight:700;color:rgba(255,255,255,.03);line-height:1;margin-bottom:-2.5rem;}
.up-badge{display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.1);backdrop-filter:blur(12px);color:#fff;padding:.5rem 1.2rem;border-radius:99px;font-size:1.05rem;font-weight:700;border:1px solid rgba(255,255,255,.18);margin-bottom:1.4rem;}
.up-dot{width:.55rem;height:.55rem;border-radius:50%;background:#ff5e5e;flex-shrink:0;box-shadow:0 0 6px rgba(255,94,94,.8);animation:lp 2s infinite;}
@keyframes lp{0%,100%{opacity:1}50%{opacity:.4}}
.up-name{font-family:'Cormorant Garamond',serif;font-size:clamp(3rem,4vw,5rem);font-weight:700;color:#fff;line-height:1;margin-bottom:.8rem;}
.up-addr{font-size:1.3rem;color:rgba(255,255,255,.55);display:flex;align-items:center;gap:.5rem;margin-bottom:2rem;}
.up-addr i{color:rgba(214,40,40,.85);}
.up-meta{display:flex;gap:1.8rem;flex-wrap:wrap;padding-top:1.8rem;border-top:1px solid rgba(255,255,255,.1);margin-bottom:1.4rem;}
.up-m{font-size:1.25rem;color:rgba(255,255,255,.6);display:flex;align-items:center;gap:.5rem;}
.up-m i{color:rgba(214,40,40,.8);}
.up-m b{color:#fff;font-weight:600;}
.up-launch{font-size:1.25rem;color:rgba(255,150,150,.85);font-weight:600;display:flex;align-items:center;gap:.5rem;}

/* ── WHY ── */
.why{background:var(--white);padding:7rem 6%;}
.why-hd{margin-bottom:4.5rem;}
.why-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:1.6rem;}
.why-big{grid-column:1/5;grid-row:1/3;border-radius:2.4rem;overflow:hidden;position:relative;min-height:50rem;}
.why-big img{width:100%;height:100%;object-fit:cover;transition:transform 7s ease;}
.why-big:hover img{transform:scale(1.04);}
.why-big-ov{position:absolute;inset:0;background:linear-gradient(to top,rgba(20,3,3,.9) 0%,rgba(20,3,3,.2) 60%,transparent 100%);}
.why-big-cnt{position:absolute;bottom:0;left:0;right:0;padding:3rem;}
.why-big-n{font-family:'Cormorant Garamond',serif;font-size:7rem;font-weight:700;color:rgba(255,255,255,.05);line-height:1;}
.why-big-s{font-family:'Cormorant Garamond',serif;font-size:5rem;font-weight:700;color:#fff;line-height:1;margin-top:-.8rem;}
.why-big-l{font-size:1.3rem;color:rgba(255,255,255,.55);margin-top:.5rem;}
.why-card{border-radius:2rem;border:1.5px solid var(--line);overflow:hidden;background:var(--bg);transition:all .38s var(--ease);display:flex;flex-direction:column;}
.why-card:hover{transform:translateY(-6px);box-shadow:var(--sh2);background:var(--white);}
.why-card:hover .wic{background:var(--r);color:#fff;transform:scale(1.1) rotate(-5deg);}
.w-img{height:13rem;overflow:hidden;position:relative;flex-shrink:0;}
.w-img img{width:100%;height:100%;object-fit:cover;transition:transform .7s var(--ease);}
.why-card:hover .w-img img{transform:scale(1.09);}
.w-img-ov{position:absolute;inset:0;background:linear-gradient(to bottom,transparent 40%,rgba(250,241,241,.97) 100%);}
.wic{position:absolute;bottom:-2rem;left:2rem;width:4.6rem;height:4.6rem;border-radius:1.2rem;background:var(--white);box-shadow:0 4px 16px rgba(214,40,40,.14);display:grid;place-items:center;font-size:1.8rem;color:var(--r);border:1.5px solid var(--line);transition:all .3s;}
.w-body{padding:3rem 2.2rem 2.2rem;flex:1;display:flex;flex-direction:column;}
.w-title{font-family:'Cormorant Garamond',serif;font-size:2.1rem;font-weight:700;color:var(--ink);margin-bottom:.6rem;}
.w-desc{font-size:1.25rem;color:var(--ink3);line-height:1.7;flex:1;}
.w-badge{display:inline-flex;align-items:center;gap:.5rem;margin-top:1.2rem;font-size:1.15rem;font-weight:700;color:var(--r);background:var(--rp);padding:.4rem 1.1rem;border-radius:99px;}
.wc1{grid-column:5/9;}.wc2{grid-column:9/13;}.wc3{grid-column:5/9;}.wc4{grid-column:9/13;}
.wc5,.wc6{position:relative;flex-direction:row;min-height:18rem;}
.wc5{grid-column:1/7;grid-row:3;}.wc6{grid-column:7/13;grid-row:3;}
.wc5 .w-img,.wc6 .w-img{height:100%;width:38%;position:absolute;left:0;top:0;bottom:0;}
.wc5 .w-img-ov,.wc6 .w-img-ov{background:linear-gradient(to right,transparent 50%,rgba(250,241,241,.98) 100%);}
.wc5 .wic,.wc6 .wic{top:2.2rem;bottom:auto;}
.wc5 .w-body,.wc6 .w-body{margin-left:38%;padding:2.5rem 2.2rem;}

/* ── TESTIMONIALS ── */
.testi{background:linear-gradient(150deg,#fff9f9,#fdf1f1 50%,#fae8e8);padding:7rem 6%;overflow:hidden;}
.testi-hd{margin-bottom:4rem;}
.testi-stage{max-width:68rem;margin:0 auto;}
.testi-slot{position:relative;min-height:28rem;}
.tc{position:absolute;top:0;left:0;right:0;background:var(--white);border-radius:2.4rem;padding:4rem;border:1.5px solid var(--line);box-shadow:0 8px 48px rgba(214,40,40,.08);opacity:0;transform:translateY(14px);transition:opacity .5s var(--ease),transform .5s var(--ease);pointer-events:none;}
.tc.active{position:relative;opacity:1;transform:translateY(0);pointer-events:auto;}
.tc-stars{display:flex;gap:.4rem;margin-bottom:2rem;}
.tc-star{font-size:1.6rem;color:#f59e0b;}
.tc-star.grey{color:#ddc070;}
.tc-text{font-size:1.7rem;line-height:1.85;color:var(--ink);font-style:italic;font-family:'Cormorant Garamond',serif;margin-bottom:3rem;}
.tc-auth{display:flex;align-items:center;gap:1.6rem;padding-top:2.4rem;border-top:1.5px solid var(--line);}
.tc-av{width:5.6rem;height:5.6rem;border-radius:50%;display:grid;place-items:center;font-size:2rem;font-weight:800;color:#fff;flex-shrink:0;}
.tc-name{font-size:1.5rem;font-weight:700;color:var(--ink);}
.tc-role{font-size:1.2rem;color:var(--ink3);margin-top:.2rem;}
.tc-prop{font-size:1.1rem;color:var(--r);margin-top:.4rem;display:flex;align-items:center;gap:.4rem;}
.testi-bar{height:2px;background:rgba(214,40,40,.1);border-radius:99px;margin-top:2.8rem;overflow:hidden;}
.testi-bar-fill{height:100%;background:var(--r);width:0%;border-radius:99px;}
.testi-nav{display:flex;align-items:center;justify-content:center;gap:2rem;margin-top:2.4rem;}
.testi-arr{width:4.4rem;height:4.4rem;border-radius:50%;border:1.5px solid rgba(214,40,40,.22);background:var(--white);color:var(--r);font-size:1.4rem;cursor:pointer;display:grid;place-items:center;transition:all .25s;box-shadow:var(--sh);}
.testi-arr:hover{background:var(--r);color:#fff;box-shadow:0 6px 20px rgba(214,40,40,.3);}
.testi-dots-row{display:flex;gap:.7rem;align-items:center;}
.td{width:.7rem;height:.7rem;border-radius:50%;background:rgba(214,40,40,.18);cursor:pointer;transition:all .3s;}
.td.on{background:var(--r);width:2.2rem;border-radius:99px;}

/* ── FOOTER ── */
.footer{background:linear-gradient(135deg,#fff0f0,#fde0e0);border-top:1px solid var(--line);padding:6rem 6% 3.5rem;}
.foot-grid{display:grid;grid-template-columns:2.2fr 1fr 1fr 1.3fr;gap:5rem;padding-bottom:4rem;border-bottom:1px solid var(--line);}
.foot-logo{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);display:block;margin-bottom:1.2rem;}
.foot-logo span{font-style:italic;color:var(--r);}
.foot-brand p{font-size:1.3rem;color:var(--ink3);line-height:1.7;margin-bottom:2rem;max-width:26rem;}
.foot-socials{display:flex;gap:.9rem;}
.fsc{width:3.8rem;height:3.8rem;border-radius:50%;border:1.5px solid var(--line);background:var(--white);display:grid;place-items:center;color:var(--ink3);font-size:1.4rem;text-decoration:none;transition:all .2s;}
.fsc:hover{border-color:var(--r);color:var(--r);background:var(--rp);transform:translateY(-3px);}
.foot-col h4{font-size:1.05rem;font-weight:700;color:var(--ink);letter-spacing:.14em;text-transform:uppercase;margin-bottom:1.6rem;}
.foot-col a{display:flex;align-items:center;gap:.6rem;font-size:1.25rem;color:var(--ink3);text-decoration:none;margin-bottom:.95rem;transition:all .2s;cursor:pointer;}
.foot-col a i{font-size:.9rem;color:rgba(214,40,40,.28);transition:color .2s;}
.foot-col a:hover,.foot-col a:hover i{color:var(--r);}
.foot-col a:hover{padding-left:.4rem;}
.fci{display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.3rem;}
.fci-ic{width:3.4rem;height:3.4rem;border-radius:.8rem;background:var(--white);border:1px solid var(--line);display:grid;place-items:center;color:var(--r);font-size:1.3rem;flex-shrink:0;}
.fci-t{font-size:1.25rem;color:var(--ink3);line-height:1.5;}
.fci-t strong{display:block;font-size:1.05rem;font-weight:700;color:var(--ink);margin-bottom:.15rem;}
.foot-enq-btn{display:flex;align-items:center;gap:.7rem;margin-top:1.8rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;padding:1.1rem 2.4rem;font-size:1.25rem;font-weight:700;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 4px 16px rgba(214,40,40,.28);transition:all .25s;width:100%;justify-content:center;}
.foot-enq-btn:hover{transform:translateY(-2px);box-shadow:0 8px 26px rgba(214,40,40,.42);}
.foot-bot{display:flex;align-items:center;justify-content:space-between;padding-top:2.5rem;flex-wrap:wrap;gap:1.2rem;}
.foot-copy{font-size:1.2rem;color:var(--ink3);}
.foot-copy span{color:var(--r);font-weight:700;}
.foot-bot-links{display:flex;gap:2rem;}
.foot-bot-links a{font-size:1.2rem;color:var(--ink3);text-decoration:none;transition:color .2s;}
.foot-bot-links a:hover{color:var(--r);}

/* ── RESPONSIVE ── */
@media(max-width:1100px){
  .hero{grid-template-columns:1fr;height:auto;min-height:100svh;}
  .hero-r{height:55vw;min-height:32rem;}
  .hfc-2{display:none;}
  .why-grid{grid-template-columns:1fr 1fr;}
  .why-big{grid-column:1/3;min-height:24rem;}
  .wc1,.wc2,.wc3,.wc4{grid-column:auto;}
  .wc5,.wc6{grid-column:1/3;grid-row:auto;flex-direction:column;}
  .wc5 .w-img,.wc6 .w-img{width:100%;height:13rem;position:relative;}
  .wc5 .w-body,.wc6 .w-body{margin-left:0;}
  .foot-grid{grid-template-columns:1fr 1fr;gap:3rem;}
}
@media(max-width:900px){
  .feat,.upcoming{height:auto!important;}
  .feat-stage{height:60vw;min-height:28rem;flex:none;}
  .up-acc{flex-direction:column;height:auto;min-height:50rem;}
  .up-p{min-height:20rem;}
  .up-p:hover{flex:none;}
  .up-cnt{opacity:1;transform:none;}
  .up-vl{display:none;}
}
@media(max-width:768px){
  .nav-links,.nav-btns{display:none;}
  .nav-ham{display:flex;}
  .hero-l{padding:9rem 5% 5%;}
  .hero-r{height:58vw;min-height:26rem;}
  .feat-hd,.up-hd{padding:2.5rem 5% 2rem;}
  .feat-stage{height:62vw;min-height:26rem;}
  .fs-cnt{flex-direction:column;align-items:flex-start;padding:2rem 5% 4rem;}
  .fs-right{align-items:flex-start;}
  .why-grid{grid-template-columns:1fr;}
  .why-big{grid-column:1;min-height:22rem;}
  .wc1,.wc2,.wc3,.wc4,.wc5,.wc6{grid-column:1;grid-row:auto;flex-direction:column;}
  .wc5 .w-img,.wc6 .w-img{width:100%;height:12rem;position:relative;}
  .wc5 .w-body,.wc6 .w-body{margin-left:0;}
  .testi-stage{max-width:100%;}
  .tc{padding:2.8rem;}
  .foot-grid{grid-template-columns:1fr;}
  .foot-bot{flex-direction:column;align-items:flex-start;}
}
@media(max-width:480px){
  .hero-h{font-size:4.2rem;}
  .hero-ctas{flex-direction:column;align-items:flex-start;}
  .fs-name{font-size:3.5rem;}
  .up-name{font-size:3rem;}
  .sec-title{font-size:clamp(2.8rem,8vw,4.5rem);}
  .popup-box{padding:4rem 2.8rem;}
  .enq-row{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<!-- REGISTER POPUP -->
<div class="popup-overlay" id="regPopup">
  <div class="popup-box">
    <button class="popup-close" onclick="closePopup('regPopup')"><i class="fas fa-times"></i></button>
    <div class="popup-icon"><i class="fas fa-home"></i></div>
    <h3>Join MyEstate</h3>
    <p>Create a free account to explore all properties, enquire directly with owners and get personalised listings.</p>
    <div class="popup-btns">
      <a href="register.php" class="pb-reg" id="popupRegBtn"><i class="fas fa-user-plus"></i> Register Free</a>
      <span class="popup-divider">or</span>
      <a href="login.php" class="pb-log" id="popupLogBtn"><i class="fas fa-sign-in-alt"></i> Login</a>
    </div>
  </div>
</div>

<!-- ENQUIRY POPUP -->
<div class="enq-overlay" id="enqPopup">
  <div class="enq-box">
    <button class="enq-close" onclick="closePopup('enqPopup')"><i class="fas fa-times"></i></button>
    <h3>Send Enquiry</h3>
    <p>Fill in your details and we'll get back to you within 24 hours.</p>
    <div class="enq-row">
      <div class="enq-field"><label>Full Name *</label><input type="text" placeholder="Your full name" required></div>
      <div class="enq-field"><label>Phone Number *</label><input type="tel" placeholder="+91 98765 43210" required></div>
      <div class="enq-field"><label>Email Address *</label><input type="email" placeholder="you@email.com" required></div>
      <div class="enq-field"><label>Property Type</label>
        <select><option value="">Select type</option><option>Apartment</option><option>Villa</option><option>Plot</option><option>Commercial</option></select>
      </div>
      <div class="enq-field full"><label>Message</label><textarea placeholder="Tell us what you're looking for..."></textarea></div>
    </div>
    <button class="enq-submit" onclick="alert('Enquiry submitted! We will contact you within 24 hours.');closePopup('enqPopup')"><i class="fas fa-paper-plane"></i> Submit Enquiry</button>
  </div>
</div>

<!-- NAV -->
<nav class="nav" id="mainNav">
  <a href="index.php" class="logo">My<span>Estate</span></a>
  <div class="nav-links">
    <a onclick="navClick('properties')">Properties</a>
    <a onclick="navClick('upcoming')">Upcoming</a>
    <a onclick="navClick('about')">About</a>
    <a onclick="navClick('contact')">Contact</a>
  </div>
  <div class="nav-btns">
    <a href="login.php" class="nb g"><i class="fas fa-sign-in-alt"></i> Login</a>
    <a href="register.php" class="nb s"><i class="fas fa-user-plus"></i> Get Started</a>
  </div>
  <div class="nav-ham" id="navHam" onclick="toggleMob()">
    <span id="hs1"></span><span id="hs2"></span><span id="hs3"></span>
  </div>
</nav>

<!-- MOBILE NAV -->
<div class="mob-nav" id="mobNav">
  <button class="mob-close" onclick="closeMob()"><i class="fas fa-times"></i></button>
  <a onclick="navClick('properties');closeMob()">Properties</a>
  <a onclick="navClick('upcoming');closeMob()">Upcoming</a>
  <a onclick="navClick('about');closeMob()">About</a>
  <a onclick="navClick('contact');closeMob()">Contact</a>
  <div class="mob-btns">
    <a href="login.php" class="nb g"><i class="fas fa-sign-in-alt"></i> Login</a>
    <a href="register.php" class="nb s"><i class="fas fa-user-plus"></i> Register</a>
  </div>
</div>

<!-- HERO -->
<section class="hero" id="heroSec">
  <div class="hero-l">
    <div class="hero-tag">Premium Real Estate</div>
    <h1 class="hero-h">Find Your<em>Dream Home.</em></h1>
    <p class="hero-sub">Discover curated luxury properties across Mumbai and Pune. From sleek city apartments to sprawling villas — your perfect home awaits.</p>
    <div class="hero-ctas">
      <a onclick="navClick('properties')" class="cta-a"><i class="fas fa-search"></i> Explore Properties</a>
      <a href="register.php" class="cta-b"><i class="fas fa-user-plus"></i> Join Free</a>
    </div>
    <div class="hero-stats">
      <div class="hst"><div class="hst-n"><?= $prop_count ?>+</div><div class="hst-l">Properties</div></div>
      <div class="hst"><div class="hst-n"><?= $user_count ?>+</div><div class="hst-l">Happy Buyers</div></div>
      <div class="hst"><div class="hst-n">8+</div><div class="hst-l">Cities</div></div>
    </div>
  </div>
  <div class="hero-r">
    <div class="hrs">
      <div class="hrs-slide on"><img src="https://images.unsplash.com/photo-1613977257365-aaae5a9817ff?w=1200&q=90&auto=format" alt="Villa"></div>
      <div class="hrs-slide"><img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1200&q=90&auto=format" alt="House"></div>
      <div class="hrs-slide"><img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=90&auto=format" alt="Modern"></div>
      <div class="hrs-slide"><img src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=1200&q=90&auto=format" alt="Luxury"></div>
    </div>
    <div class="hero-r-grad"></div>
    <div class="hfc hfc-1"><div class="hfc-label">Properties Listed</div><div class="hfc-val"><i class="fas fa-building"></i> <?= $prop_count ?>+ Homes</div><div class="hfc-sub"><i class="fas fa-arrow-up"></i> Growing daily</div></div>
    <div class="hfc hfc-2"><div class="hfc-label">Happy Buyers</div><div class="hfc-val"><i class="fas fa-users"></i> <?= $user_count ?>+ Users</div><div class="hfc-sub red">Mumbai &amp; Pune</div></div>
    <div class="hfc hfc-3"><div class="hfc-label">Average Rating</div><div class="hfc-val"><i class="fas fa-star"></i> 4.9 / 5.0</div><div class="hfc-sub"><i class="fas fa-check-circle"></i> Verified</div></div>
    <div class="hrs-dots" id="hrsDots">
      <div class="hrsd on"></div><div class="hrsd"></div><div class="hrsd"></div><div class="hrsd"></div>
    </div>
  </div>
</section>

<!-- FEATURED PROPERTIES -->
<section class="feat" id="featSec">
  <div class="feat-hd reveal">
    <div class="sec-hd">
      <div>
        <div class="eyebrow">Top Picks</div>
        <h2 class="sec-title">Featured <em>Properties</em></h2>
        <p class="sec-sub">Hand-picked listings across prime locations in Mumbai and Pune.</p>
      </div>
      <button onclick="openPopup('listings')" class="btn-outline">View All Listings <i class="fas fa-arrow-right"></i></button>
    </div>
  </div>
  <div class="feat-stage" id="featStage">
    <?php
    $slides = [
      ['img'=>'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1920&q=90&auto=format','type'=>'Commercial','name'=>'Commercial Shop','addr'=>'FC Road, Pune','pills'=>[['fas fa-bath','1 Bath'],['fas fa-ruler-combined','450 sqft']],'price'=>'₹55 L','id'=>1],
      ['img'=>'https://images.unsplash.com/photo-1613977257592-4871e5fcd7c4?w=1920&q=90&auto=format','type'=>'Villa','name'=>'Spacious 5BHK Villa','addr'=>'Juhu, Mumbai','pills'=>[['fas fa-bed','5 BHK'],['fas fa-bath','5 Bath'],['fas fa-ruler-combined','5500 sqft']],'price'=>'₹5.5 Cr','id'=>2],
      ['img'=>'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=1920&q=90&auto=format','type'=>'Apartment','name'=>'Modern 1BHK Studio','addr'=>'Baner, Pune','pills'=>[['fas fa-bed','1 BHK'],['fas fa-bath','1 Bath'],['fas fa-ruler-combined','550 sqft']],'price'=>'₹28 L','id'=>3],
      ['img'=>'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=1920&q=90&auto=format','type'=>'Plot','name'=>'Residential Plot','addr'=>'Wakad, Pune','pills'=>[['fas fa-ruler-combined','1800 sqft'],['fas fa-bolt','New Listing']],'price'=>'₹32 L','id'=>4],
      ['img'=>'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1920&q=90&auto=format','type'=>'Apartment','name'=>'3BHK Premium Flat','addr'=>'Andheri West, Mumbai','pills'=>[['fas fa-bed','3 BHK'],['fas fa-bath','2 Bath'],['fas fa-ruler-combined','1200 sqft']],'price'=>'₹1.8 Cr','id'=>5],
    ];
    // Override with DB data if available
    foreach($featured as $i => $p){
      if(isset($slides[$i])){
        $slides[$i]['name']  = htmlspecialchars($p['property_name']);
        $slides[$i]['type']  = ucfirst($p['type']);
        $slides[$i]['addr']  = htmlspecialchars($p['address']);
        $slides[$i]['price'] = formatPrice($p['price']);
        $slides[$i]['id']    = $p['id'];
        if($p['image_01']) $slides[$i]['img'] = '../uploaded_files/'.$p['image_01'];
        $slides[$i]['pills'] = [];
        if($p['bedroom'])  $slides[$i]['pills'][] = ['fas fa-bed',  $p['bedroom'].' BHK'];
        if($p['bathroom']) $slides[$i]['pills'][] = ['fas fa-bath', $p['bathroom'].' Bath'];
      }
    }
    $total = count($slides);
    foreach($slides as $i => $s):
      $active = $i===0 ? ' on' : '';
      $num = str_pad($i+1,2,'0',STR_PAD_LEFT);
    ?>
    <div class="fs<?= $active ?>">
      <img src="<?= $s['img'] ?>" alt="<?= $s['name'] ?>" onerror="this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1920&q=80&auto=format'">
      <div class="fs-ov"></div>
      <div class="fs-ctr">
        <span class="fs-cn"><?= $num ?></span>
        <span class="fs-ct">/ <?= str_pad($total,2,'0',STR_PAD_LEFT) ?></span>
        <div class="fs-nav">
          <button class="fs-nb" onclick="fNav(-1)"><i class="fas fa-chevron-left"></i></button>
          <button class="fs-nb" onclick="fNav(1)"><i class="fas fa-chevron-right"></i></button>
        </div>
      </div>
      <div class="fs-cnt">
        <div>
          <div class="fs-type"><?= $s['type'] ?></div>
          <div class="fs-name"><?= $s['name'] ?></div>
          <div class="fs-addr"><i class="fas fa-map-marker-alt"></i><?= $s['addr'] ?></div>
          <div class="fs-pills">
            <?php foreach($s['pills'] as $pill): ?>
            <div class="fs-pill"><i class="<?= $pill[0] ?>"></i> <?= $pill[1] ?></div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="fs-right">
          <div class="fs-pbox"><div class="fs-pl">Listed Price</div><div class="fs-price"><?= $s['price'] ?></div></div>
          <button class="fs-enq" onclick="openPopup('property_<?= $s['id'] ?>')"><i class="fas fa-phone-alt"></i> Enquire Now</button>
        </div>
      </div>
      <?php if($i===0): ?>
      <div class="fs-dots" id="fsDots">
        <?php for($d=0;$d<$total;$d++): ?>
        <div class="fsd<?= $d===0?' on':'' ?>"><div class="fsd-bar"></div></div>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- UPCOMING PROJECTS -->
<section class="upcoming" id="upSec">
  <div class="up-hd reveal">
    <div class="sec-hd">
      <div>
        <div class="eyebrow">Coming Soon</div>
        <h2 class="sec-title">Upcoming <em>Projects</em></h2>
        <p class="sec-sub">Exclusive launches — be the first to know and register your interest.</p>
      </div>
      <button onclick="openPopup('upcoming')" class="btn-outline">Register Interest <i class="fas fa-arrow-right"></i></button>
    </div>
  </div>
  <div class="up-acc">
    <div class="up-p">
      <img src="https://images.unsplash.com/photo-1486325212027-8081e485255e?w=1400&q=88&auto=format" alt="Skyline">
      <div class="up-ov"></div><div class="up-vl">Skyline Residences</div>
      <div class="up-cnt"><div class="up-n">01</div><div class="up-badge"><div class="up-dot"></div>Launching Soon</div><div class="up-name">Skyline Residences</div><div class="up-addr"><i class="fas fa-map-marker-alt"></i>Bandra West, Mumbai</div><div class="up-meta"><div class="up-m"><i class="fas fa-building"></i><b>24</b>&nbsp;Units</div><div class="up-m"><i class="fas fa-layer-group"></i><b>12</b>&nbsp;Floors</div><div class="up-m"><i class="fas fa-home"></i><b>2–3</b>&nbsp;BHK</div></div><div class="up-launch"><i class="fas fa-calendar-alt"></i>Expected: June 2026</div></div>
    </div>
    <div class="up-p">
      <img src="https://images.unsplash.com/photo-1613977257592-4871e5fcd7c4?w=1400&q=88&auto=format" alt="Green Valley">
      <div class="up-ov"></div><div class="up-vl">Green Valley Villas</div>
      <div class="up-cnt"><div class="up-n">02</div><div class="up-badge"><div class="up-dot"></div>Pre-Launch</div><div class="up-name">Green Valley Villas</div><div class="up-addr"><i class="fas fa-map-marker-alt"></i>Hinjewadi, Pune</div><div class="up-meta"><div class="up-m"><i class="fas fa-home"></i><b>18</b>&nbsp;Villas</div><div class="up-m"><i class="fas fa-expand"></i><b>4000+</b>&nbsp;sqft</div><div class="up-m"><i class="fas fa-tree"></i><b>40%</b>&nbsp;Green</div></div><div class="up-launch"><i class="fas fa-calendar-alt"></i>Expected: Aug 2026</div></div>
    </div>
    <div class="up-p">
      <img src="https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=1400&q=88&auto=format" alt="Business Hub">
      <div class="up-ov"></div><div class="up-vl">The Business Hub</div>
      <div class="up-cnt"><div class="up-n">03</div><div class="up-badge"><div class="up-dot"></div>Coming Soon</div><div class="up-name">The Business Hub</div><div class="up-addr"><i class="fas fa-map-marker-alt"></i>Powai, Mumbai</div><div class="up-meta"><div class="up-m"><i class="fas fa-briefcase"></i><b>50+</b>&nbsp;Offices</div><div class="up-m"><i class="fas fa-layer-group"></i><b>8</b>&nbsp;Floors</div><div class="up-m"><i class="fas fa-car"></i><b>200</b>&nbsp;Parking</div></div><div class="up-launch"><i class="fas fa-calendar-alt"></i>Expected: Sep 2026</div></div>
    </div>
    <div class="up-p">
      <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1400&q=88&auto=format" alt="Marina Heights">
      <div class="up-ov"></div><div class="up-vl">Marina Heights</div>
      <div class="up-cnt"><div class="up-n">04</div><div class="up-badge"><div class="up-dot"></div>Pre-Booking</div><div class="up-name">Marina Heights</div><div class="up-addr"><i class="fas fa-map-marker-alt"></i>Worli, Mumbai</div><div class="up-meta"><div class="up-m"><i class="fas fa-building"></i><b>36</b>&nbsp;Units</div><div class="up-m"><i class="fas fa-layer-group"></i><b>18</b>&nbsp;Floors</div><div class="up-m"><i class="fas fa-home"></i><b>3–4</b>&nbsp;BHK</div></div><div class="up-launch"><i class="fas fa-calendar-alt"></i>Expected: Dec 2026</div></div>
    </div>
    <div class="up-p">
      <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=1400&q=88&auto=format" alt="Sunrise Enclave">
      <div class="up-ov"></div><div class="up-vl">Sunrise Enclave</div>
      <div class="up-cnt"><div class="up-n">05</div><div class="up-badge"><div class="up-dot"></div>Under Planning</div><div class="up-name">Sunrise Enclave</div><div class="up-addr"><i class="fas fa-map-marker-alt"></i>Kothrud, Pune</div><div class="up-meta"><div class="up-m"><i class="fas fa-home"></i><b>12</b>&nbsp;Bungalows</div><div class="up-m"><i class="fas fa-expand"></i><b>3500+</b>&nbsp;sqft</div><div class="up-m"><i class="fas fa-tree"></i><b>60%</b>&nbsp;Green</div></div><div class="up-launch"><i class="fas fa-calendar-alt"></i>Expected: Mar 2027</div></div>
    </div>
  </div>
</section>

<!-- WHY CHOOSE US -->
<section class="why" id="whySec">
  <div class="why-hd sec-hd reveal">
    <div>
      <div class="eyebrow" style="margin-bottom:1.2rem;">Why Us</div>
      <h2 class="sec-title">Why Choose <em>MyEstate?</em></h2>
      <p class="sec-sub">We make buying, selling and renting property simple, transparent and stress-free.</p>
    </div>
    <a href="register.php" class="btn-outline">Get Started Free <i class="fas fa-arrow-right"></i></a>
  </div>
  <div class="why-grid">
    <div class="why-big reveal">
      <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=900&q=88&auto=format" alt="Trust">
      <div class="why-big-ov"></div>
      <div class="why-big-cnt"><div class="why-big-n">6+</div><div class="why-big-s">Reasons</div><div class="why-big-l">Why thousands trust MyEstate</div></div>
    </div>
    <div class="why-card wc1 reveal" style="transition-delay:.06s"><div class="w-img"><img src="https://images.unsplash.com/photo-1563986768494-4dee2763ff3f?w=700&q=80&auto=format" alt="Verified"><div class="w-img-ov"></div><div class="wic"><i class="fas fa-shield-alt"></i></div></div><div class="w-body"><div class="w-title">Verified Listings</div><p class="w-desc">Every property is personally verified. No fake listings, no surprises.</p><div class="w-badge"><i class="fas fa-check-circle"></i>100% Verified</div></div></div>
    <div class="why-card wc2 reveal" style="transition-delay:.1s"><div class="w-img"><img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=700&q=80&auto=format" alt="Price"><div class="w-img-ov"></div><div class="wic"><i class="fas fa-hand-holding-usd"></i></div></div><div class="w-body"><div class="w-title">Best Price Guarantee</div><p class="w-desc">Negotiate directly with owners. Zero hidden commissions, ever.</p><div class="w-badge"><i class="fas fa-rupee-sign"></i>No Hidden Charges</div></div></div>
    <div class="why-card wc3 reveal" style="transition-delay:.14s"><div class="w-img"><img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?w=700&q=80&auto=format" alt="Support"><div class="w-img-ov"></div><div class="wic"><i class="fas fa-headset"></i></div></div><div class="w-body"><div class="w-title">Dedicated Support</div><p class="w-desc">Expert advisors available 7 days a week from enquiry to registry.</p><div class="w-badge"><i class="fas fa-clock"></i>7 Days Support</div></div></div>
    <div class="why-card wc4 reveal" style="transition-delay:.18s"><div class="w-img"><img src="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=700&q=80&auto=format" alt="Docs"><div class="w-img-ov"></div><div class="wic"><i class="fas fa-file-contract"></i></div></div><div class="w-body"><div class="w-title">Hassle-free Paperwork</div><p class="w-desc">Legal docs, loan assistance and registration handled by our experts.</p><div class="w-badge"><i class="fas fa-bolt"></i>Fast Documentation</div></div></div>
    <div class="why-card wc5 reveal" style="transition-delay:.22s"><div class="w-img"><img src="https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?w=1000&q=80&auto=format" alt="Locations"><div class="w-img-ov"></div><div class="wic"><i class="fas fa-map-marked-alt"></i></div></div><div class="w-body"><div class="w-title">Prime Locations</div><p class="w-desc">Only in Mumbai and Pune's most sought-after neighbourhoods with great connectivity.</p><div class="w-badge"><i class="fas fa-map-marker-alt"></i>Mumbai &amp; Pune</div></div></div>
    <div class="why-card wc6 reveal" style="transition-delay:.26s"><div class="w-img"><img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1000&q=80&auto=format" alt="Post Sale"><div class="w-img-ov"></div><div class="wic"><i class="fas fa-home"></i></div></div><div class="w-body"><div class="w-title">Post-Sale Assistance</div><p class="w-desc">Interior consultation, tenant management and resale support for every client.</p><div class="w-badge"><i class="fas fa-heart"></i>Lifetime Support</div></div></div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="testi" id="testiSec">
  <div class="testi-hd sec-hd reveal">
    <div>
      <div class="eyebrow" style="margin-bottom:1.2rem;">Reviews</div>
      <h2 class="sec-title">What Our <em>Clients Say</em></h2>
      <p class="sec-sub">Real stories from real homeowners who found their perfect home with us.</p>
    </div>
    <a href="register.php" class="btn-outline">Join Our Community <i class="fas fa-arrow-right"></i></a>
  </div>
  <div class="testi-stage">
    <div class="testi-slot" id="testiSlot">
      <div class="tc active">
        <div class="tc-stars"><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i></div>
        <p class="tc-text">"Found my dream 3BHK in Andheri within two weeks. Incredibly responsive team, handled all paperwork. Could not have asked for a smoother experience."</p>
        <div class="tc-auth"><div class="tc-av" style="background:linear-gradient(135deg,#d62828,#9e1c1c);">R</div><div><div class="tc-name">Rahul Sharma</div><div class="tc-role">Software Engineer, Mumbai</div><div class="tc-prop"><i class="fas fa-building"></i>Bought 3BHK, Andheri West</div></div></div>
      </div>
      <div class="tc">
        <div class="tc-stars"><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i></div>
        <p class="tc-text">"The villa in Koregaon Park exceeded all expectations. Transparent pricing, genuine photos, site visit arranged within 24 hours. Highly recommend!"</p>
        <div class="tc-auth"><div class="tc-av" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">P</div><div><div class="tc-name">Priya Patel</div><div class="tc-role">Business Owner, Pune</div><div class="tc-prop"><i class="fas fa-home"></i>Bought 4BHK Villa, Pune</div></div></div>
      </div>
      <div class="tc">
        <div class="tc-stars"><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star grey fas fa-star"></i></div>
        <p class="tc-text">"As a first-time buyer I was nervous. The MyEstate team guided me through every step — from loan application to final registry. Outstanding service."</p>
        <div class="tc-auth"><div class="tc-av" style="background:linear-gradient(135deg,#1a9c4e,#15803d);">A</div><div><div class="tc-name">Amit Desai</div><div class="tc-role">Doctor, Thane</div><div class="tc-prop"><i class="fas fa-building"></i>Bought 2BHK, Thane West</div></div></div>
      </div>
      <div class="tc">
        <div class="tc-stars"><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i></div>
        <p class="tc-text">"Sold my old flat and bought a new one through MyEstate in under 3 months. Seamless process — they even helped with interior design recommendations!"</p>
        <div class="tc-auth"><div class="tc-av" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);">S</div><div><div class="tc-name">Sneha Kulkarni</div><div class="tc-role">CA, Pune</div><div class="tc-prop"><i class="fas fa-home"></i>Bought 3BHK, Kothrud</div></div></div>
      </div>
      <div class="tc">
        <div class="tc-stars"><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i><i class="tc-star fas fa-star"></i></div>
        <p class="tc-text">"Rented a beautiful 2BHK in Hinjewadi within days. Listing photos were accurate and the landlord was genuine. Will definitely use MyEstate again!"</p>
        <div class="tc-auth"><div class="tc-av" style="background:linear-gradient(135deg,#ea580c,#c2410c);">V</div><div><div class="tc-name">Vikram Joshi</div><div class="tc-role">IT Professional, Pune</div><div class="tc-prop"><i class="fas fa-key"></i>Rented 2BHK, Hinjewadi</div></div></div>
      </div>
    </div>
    <div class="testi-bar"><div class="testi-bar-fill" id="testiBarFill"></div></div>
    <div class="testi-nav">
      <button class="testi-arr" id="testiPrev"><i class="fas fa-chevron-left"></i></button>
      <div class="testi-dots-row" id="testiDots">
        <div class="td on"></div><div class="td"></div><div class="td"></div><div class="td"></div><div class="td"></div>
      </div>
      <button class="testi-arr" id="testiNext"><i class="fas fa-chevron-right"></i></button>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer" id="footerSec">
  <div class="foot-grid">
    <div class="foot-brand">
      <span class="foot-logo">My<span>Estate</span></span>
      <p>Your trusted partner for premium real estate across Mumbai and Pune. Verified listings, expert guidance, seamless transactions.</p>
      <div class="foot-socials">
        <a href="#" class="fsc"><i class="fab fa-instagram"></i></a>
        <a href="#" class="fsc"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="fsc"><i class="fab fa-twitter"></i></a>
        <a href="#" class="fsc"><i class="fab fa-youtube"></i></a>
      </div>
    </div>
    <div class="foot-col">
      <h4>Properties</h4>
      <a onclick="footerClick('listings')"><i class="fas fa-chevron-right"></i>Apartments</a>
      <a onclick="footerClick('listings')"><i class="fas fa-chevron-right"></i>Villas</a>
      <a onclick="footerClick('listings')"><i class="fas fa-chevron-right"></i>Plots</a>
      <a onclick="footerClick('listings')"><i class="fas fa-chevron-right"></i>Commercial</a>
      <a onclick="navClick('upcoming')"><i class="fas fa-chevron-right"></i>Upcoming Projects</a>
    </div>
    <div class="foot-col">
      <h4>Quick Links</h4>
      <a href="login.php"><i class="fas fa-chevron-right"></i>Login</a>
      <a href="register.php"><i class="fas fa-chevron-right"></i>Register</a>
      <a onclick="footerClick('post')"><i class="fas fa-chevron-right"></i>Post a Property</a>
      <a onclick="navClick('about')"><i class="fas fa-chevron-right"></i>About Us</a>
    </div>
    <div class="foot-col">
      <h4>Contact Us</h4>
      <div class="fci"><div class="fci-ic"><i class="fas fa-map-marker-alt"></i></div><div class="fci-t"><strong>Office</strong>Bandra West, Mumbai — 400050</div></div>
      <div class="fci"><div class="fci-ic"><i class="fas fa-phone-alt"></i></div><div class="fci-t"><strong>Phone</strong>+91 98765 43210</div></div>
      <div class="fci"><div class="fci-ic"><i class="fas fa-envelope"></i></div><div class="fci-t"><strong>Email</strong>hello@myestate.in</div></div>
      <button class="foot-enq-btn" onclick="openEnqPopup()"><i class="fas fa-paper-plane"></i> Send Enquiry</button>
    </div>
  </div>
  <div class="foot-bot">
    <p class="foot-copy">© 2026 <span>MyEstate</span>. All rights reserved.</p>
    <div class="foot-bot-links">
      <a href="#">Privacy Policy</a>
      <a href="#">Terms of Use</a>
      <a href="#">Cookie Policy</a>
    </div>
  </div>
</footer>

<script>
// SCROLL REVEAL
const obs=new IntersectionObserver(e=>e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('in');obs.unobserve(x.target);}}),{threshold:.07});
document.querySelectorAll('.reveal').forEach(r=>obs.observe(r));

// NAV SCROLL
window.addEventListener('scroll',()=>document.getElementById('mainNav').classList.toggle('scrolled',scrollY>40));

// FIT SECTIONS TO VIEWPORT
function fitSections(){
  if(window.innerWidth>900){
    const navH=document.getElementById('mainNav').getBoundingClientRect().height;
    const avail=window.innerHeight-navH;
    document.getElementById('featSec').style.height=avail+'px';
    document.getElementById('upSec').style.height=avail+'px';
  }else{
    document.getElementById('featSec').style.height='';
    document.getElementById('upSec').style.height='';
  }
}
fitSections();
window.addEventListener('resize',fitSections);

// MOBILE NAV
function toggleMob(){
  const m=document.getElementById('mobNav');
  const open=m.classList.toggle('open');
  document.getElementById('hs1').style.transform=open?'translateY(7px) rotate(45deg)':'';
  document.getElementById('hs2').style.opacity=open?'0':'1';
  document.getElementById('hs3').style.transform=open?'translateY(-7px) rotate(-45deg)':'';
}
function closeMob(){
  document.getElementById('mobNav').classList.remove('open');
  ['hs1','hs2','hs3'].forEach(id=>{document.getElementById(id).style.transform='';document.getElementById(id).style.opacity='';});
}

// NAV CLICK — pre-login scrolls to section
function navClick(t){
  closeMob();
  const m={properties:'featSec',upcoming:'upSec',about:'whySec',contact:'footerSec'};
  const el=document.getElementById(m[t]);
  if(el)el.scrollIntoView({behavior:'smooth'});
}

// POPUP
function openPopup(intent){
  document.getElementById('popupRegBtn').href='register.php?redirect='+intent;
  document.getElementById('popupLogBtn').href='login.php?redirect='+intent;
  document.getElementById('regPopup').classList.add('open');
  document.body.style.overflow='hidden';
}
function closePopup(id){document.getElementById(id).classList.remove('open');document.body.style.overflow='';}
document.getElementById('regPopup').addEventListener('click',function(e){if(e.target===this)closePopup('regPopup');});
document.getElementById('enqPopup').addEventListener('click',function(e){if(e.target===this)closePopup('enqPopup');});

function footerClick(page){openPopup(page);}
function openEnqPopup(){openPopup('enquiry');}

// HERO SLIDER
let hc=0;const hSl=document.querySelectorAll('.hrs-slide'),hDt=document.querySelectorAll('.hrsd');
function hrNext(){hSl[hc].classList.remove('on');hDt[hc].classList.remove('on');hc=(hc+1)%hSl.length;hSl[hc].classList.add('on');hDt[hc].classList.add('on');}
setInterval(hrNext,3500);
hDt.forEach((d,i)=>d.addEventListener('click',()=>{hSl[hc].classList.remove('on');hDt[hc].classList.remove('on');hc=i;hSl[hc].classList.add('on');hDt[hc].classList.add('on');}));

// FEATURED SLIDER
const FDUR=3500;let fc=0,ftmr;
const fSl=document.querySelectorAll('.fs'),fDt=document.querySelectorAll('.fsd'),fBr=document.querySelectorAll('.fsd-bar');
function goFeat(n){
  fSl[fc].classList.remove('on');fDt[fc].classList.remove('on');
  fBr[fc].style.transition='none';fBr[fc].style.width='0%';
  fc=(n+fSl.length)%fSl.length;
  fSl[fc].classList.add('on');fDt[fc].classList.add('on');
  setTimeout(()=>{fBr[fc].style.transition='width '+FDUR+'ms linear';fBr[fc].style.width='100%';},30);
  clearInterval(ftmr);ftmr=setInterval(()=>goFeat(fc+1),FDUR);
}
fBr[0].style.transition='width '+FDUR+'ms linear';fBr[0].style.width='100%';
ftmr=setInterval(()=>goFeat(fc+1),FDUR);
function fNav(d){goFeat(fc+d);}
fDt.forEach((d,i)=>d.addEventListener('click',()=>goFeat(i)));

// TESTIMONIALS
(function(){
  const DUR=4500;
  const cards=document.querySelectorAll('.tc'),dots=document.querySelectorAll('.td'),fill=document.getElementById('testiBarFill');
  const prev=document.getElementById('testiPrev'),next=document.getElementById('testiNext');
  const total=cards.length;let cur=0,timer;
  cards.forEach((c,i)=>{if(i!==0){c.style.position='absolute';c.style.top='0';c.style.left='0';c.style.right='0';}});
  function startFill(){fill.style.transition='none';fill.style.width='0%';requestAnimationFrame(()=>requestAnimationFrame(()=>{fill.style.transition='width '+DUR+'ms linear';fill.style.width='100%';}));}
  function show(n){
    cards[cur].classList.remove('active');cards[cur].style.position='absolute';dots[cur].classList.remove('on');
    cur=(n+total)%total;
    cards[cur].style.position='relative';cards[cur].classList.add('active');dots[cur].classList.add('on');
    startFill();
  }
  function startTimer(){clearInterval(timer);timer=setInterval(()=>show(cur+1),DUR);}
  startFill();startTimer();
  prev.addEventListener('click',()=>{show(cur-1);startTimer();});
  next.addEventListener('click',()=>{show(cur+1);startTimer();});
  dots.forEach((d,i)=>d.addEventListener('click',()=>{show(i);startTimer();}));
})();
</script>
</body>
</html>