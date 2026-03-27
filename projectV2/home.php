<?php
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   header('location:login.php');
   exit();
}

// Fetch user data
$sel_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
$sel_user->execute([$user_id]);
$fetch_user = $sel_user->fetch(PDO::FETCH_ASSOC);
$user_name = $fetch_user ? $fetch_user['name'] : 'User';
$user_initial = strtoupper(substr($user_name, 0, 1));

// Count saved properties
$sel_saved = $conn->prepare("SELECT COUNT(*) as cnt FROM `saved` WHERE user_id = ?");
$sel_saved->execute([$user_id]);
$saved_count = $sel_saved->fetch(PDO::FETCH_ASSOC)['cnt'];

// Count total listings
$sel_total = $conn->prepare("SELECT COUNT(*) as cnt FROM `property`");
$sel_total->execute();
$total_listings = $sel_total->fetch(PDO::FETCH_ASSOC)['cnt'];

// Count total users
$sel_users = $conn->prepare("SELECT COUNT(*) as cnt FROM `users`");
$sel_users->execute();
$total_users = $sel_users->fetch(PDO::FETCH_ASSOC)['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MyEstate — Dashboard</title>
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
  --sh2:0 28px 80px rgba(214,40,40,.2);
  --nav-h:7rem;
}
*{margin:0;padding:0;box-sizing:border-box;}
html{font-size:62.5%;scroll-behavior:smooth;}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--ink);overflow-x:hidden;}
::-webkit-scrollbar{width:3px;}
::-webkit-scrollbar-thumb{background:var(--r);}
.reveal{opacity:0;transform:translateY(32px);transition:opacity .8s var(--ease),transform .8s var(--ease);}
.reveal.in{opacity:1;transform:translateY(0);}
.eyebrow{display:inline-flex;align-items:center;gap:.5rem;font-size:.9rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--r);background:var(--rp);padding:.35rem 1rem;border-radius:99px;border:1px solid rgba(214,40,40,.12);width:fit-content;margin-bottom:1.2rem;}
.eyebrow::before{content:'';width:.4rem;height:.4rem;border-radius:50%;background:var(--r);animation:blink 2s infinite;flex-shrink:0;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
.sec-title{font-family:'Cormorant Garamond',serif;font-size:clamp(3.6rem,4.8vw,5.8rem);font-weight:700;color:var(--ink);letter-spacing:-.03em;line-height:.96;margin-bottom:1rem;}
.sec-title em{font-style:italic;color:var(--r);}
.sec-sub{font-size:1.4rem;color:var(--ink3);line-height:1.75;max-width:52rem;}
.sec-hd{display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:2rem;margin-bottom:4.5rem;}
.btn-ol{display:inline-flex;align-items:center;gap:.7rem;font-size:1.3rem;font-weight:700;color:var(--r);border:1.5px solid rgba(214,40,40,.22);padding:.9rem 2rem;border-radius:99px;background:var(--white);transition:all .25s;white-space:nowrap;cursor:pointer;text-decoration:none;}
.btn-ol:hover{background:var(--rp);gap:1.3rem;}
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
.nav-user:hover .nav-drop-menu{display:block;}
.nd-item{display:flex;align-items:center;gap:1rem;padding:1.1rem 1.4rem;border-radius:1rem;font-size:1.3rem;color:var(--ink2);text-decoration:none;transition:all .18s;}
.nd-item i{width:2rem;text-align:center;color:var(--ink3);font-size:1.2rem;}
.nd-item:hover{background:var(--rp);color:var(--r);}
.nd-item:hover i{color:var(--r);}
.nd-sep{height:1px;background:var(--line);margin:.5rem 0;}
.nd-danger{color:#c0392b!important;}
.nd-danger i{color:#c0392b!important;}
.nd-danger:hover{background:#fff5f5!important;}
.hero{min-height:100vh;padding-top:var(--nav-h);display:grid;grid-template-columns:1fr 1fr;overflow:hidden;position:relative;background:linear-gradient(145deg,#fff9f9 0%,#fdf1f1 45%,#fae8e8 100%);}
.hero-deco-ring{position:absolute;border-radius:50%;pointer-events:none;border:1px solid rgba(214,40,40,.07);}
.hero-deco-ring.r1{width:80rem;height:80rem;top:-30rem;right:-20rem;}
.hero-deco-ring.r2{width:55rem;height:55rem;top:-15rem;right:-5rem;}
.hero-deco-ring.r3{width:30rem;height:30rem;top:0;right:10rem;}
.hero-l{padding:6rem 5% 6rem 7%;display:flex;flex-direction:column;justify-content:center;position:relative;z-index:2;}
.hero-tag{display:inline-flex;align-items:center;gap:.7rem;font-size:.9rem;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--r);background:var(--rp);padding:.35rem 1.2rem;border-radius:99px;border:1px solid rgba(214,40,40,.15);margin-bottom:2.8rem;width:fit-content;}
.hero-tag::before{content:'';width:.4rem;height:.4rem;border-radius:50%;background:var(--r);animation:blink 2s infinite;flex-shrink:0;}
.hero-h{font-family:'Cormorant Garamond',serif;font-size:clamp(5rem,6vw,9rem);font-weight:700;line-height:.88;letter-spacing:-.04em;color:var(--ink);margin-bottom:2.2rem;}
.hero-h em{font-style:italic;color:var(--r);display:block;}
.hero-h span{display:block;}
.hero-sub{font-size:1.5rem;color:var(--ink3);max-width:42rem;line-height:1.75;margin-bottom:4rem;}
.srch-wrap{background:var(--white);border-radius:2rem;border:1.5px solid var(--line);padding:2rem 2.4rem;box-shadow:0 16px 56px rgba(214,40,40,.1);margin-bottom:3rem;}
.srch-label{font-size:1rem;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--ink3);margin-bottom:1.4rem;}
.srch-row{display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:1.2rem;align-items:end;}
.sf{display:flex;flex-direction:column;gap:.5rem;}
.sf label{font-size:1.1rem;font-weight:600;color:var(--ink2);}
.sf input,.sf select{padding:1rem 1.4rem;border:1.5px solid var(--line);border-radius:1.2rem;font-size:1.3rem;font-family:'Outfit',sans-serif;color:var(--ink);background:var(--rp);outline:none;transition:border .2s;}
.sf input:focus,.sf select:focus{border-color:rgba(214,40,40,.4);background:var(--white);}
.srch-btn{height:4.4rem;display:flex;align-items:center;gap:.7rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:1.2rem;padding:0 2.2rem;font-size:1.3rem;font-weight:700;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 6px 20px rgba(214,40,40,.35);transition:all .25s;white-space:nowrap;}
.srch-btn:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(214,40,40,.48);}
.hero-pills{display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:3.5rem;}
.hp{display:flex;align-items:center;gap:.7rem;padding:.95rem 2rem;border-radius:99px;font-size:1.2rem;font-weight:700;cursor:pointer;transition:all .25s;font-family:'Outfit',sans-serif;border:none;text-decoration:none;}
.hp.prim{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;box-shadow:0 6px 22px rgba(214,40,40,.32);}
.hp.prim:hover{transform:translateY(-3px);box-shadow:0 14px 36px rgba(214,40,40,.48);}
.hp.sec{background:var(--white);color:var(--ink2);border:1.5px solid var(--line);}
.hp.sec:hover{border-color:var(--r);color:var(--r);background:var(--rp);transform:translateY(-2px);}
.trust-row{display:flex;gap:2.4rem;flex-wrap:wrap;}
.tr-item{display:flex;align-items:center;gap:.6rem;font-size:1.1rem;font-weight:600;color:var(--ink3);}
.tr-item i{color:var(--r);font-size:.95rem;}
.hero-r{position:relative;overflow:hidden;}
.hero-r img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;}
.hero-r-grad{position:absolute;inset:0;background:linear-gradient(to left,transparent 30%,rgba(253,241,241,.12) 65%,rgba(253,241,241,.96) 100%);}
.hf{position:absolute;z-index:4;background:rgba(255,255,255,.93);backdrop-filter:blur(22px);border-radius:1.8rem;padding:1.5rem 2rem;border:1px solid rgba(255,255,255,.6);box-shadow:0 8px 36px rgba(0,0,0,.1);}
.hf1{top:8rem;right:3.5rem;animation:flt1 5s ease-in-out infinite;}
.hf2{top:50%;right:3.5rem;transform:translateY(-50%);animation:flt2 6s ease-in-out 1.5s infinite;}
.hf3{bottom:8rem;right:3.5rem;animation:flt1 4.5s ease-in-out 3s infinite;}
@keyframes flt1{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
@keyframes flt2{0%,100%{transform:translateY(-50%)}50%{transform:translateY(calc(-50% - 8px))}}
.hf-ic{width:3.8rem;height:3.8rem;border-radius:1rem;background:var(--rp);display:grid;place-items:center;font-size:1.6rem;color:var(--r);margin-bottom:.8rem;}
.hf-n{font-family:'Cormorant Garamond',serif;font-size:3.2rem;font-weight:700;color:var(--ink);line-height:1;}
.hf-l{font-size:1.1rem;color:var(--ink3);}
.stats-strip{background:linear-gradient(135deg,var(--r),var(--rd));padding:3.5rem 6%;display:grid;grid-template-columns:repeat(4,1fr);position:relative;overflow:hidden;}
.ss-texture{position:absolute;inset:0;opacity:.04;background:repeating-linear-gradient(45deg,#fff 0,#fff 1px,transparent 0,transparent 50%) 0/18px 18px;}
.si{text-align:center;padding:0 3rem;border-right:1px solid rgba(255,255,255,.15);position:relative;z-index:1;}
.si:last-child{border-right:none;}
.si-n{font-family:'Cormorant Garamond',serif;font-size:5.5rem;font-weight:700;color:#fff;line-height:1;margin-bottom:.3rem;}
.si-l{font-size:1.15rem;color:rgba(255,255,255,.6);letter-spacing:.07em;}
.si-i{font-size:1.9rem;color:rgba(255,255,255,.28);margin-bottom:.8rem;}
.lst-sec{padding:8rem 6%;background:var(--white);}
.lst-grid{display:grid;grid-template-columns:1fr 1fr;gap:2rem;}
.lc{border-radius:2.4rem;overflow:hidden;border:1.5px solid var(--line);background:var(--bg);transition:all .4s var(--ease);position:relative;}
.lc:hover{transform:translateY(-7px);box-shadow:var(--sh2);}
.lc.big{grid-column:1;grid-row:1/3;display:flex;flex-direction:column;}
.lc.big .lc-img{height:38rem;flex-shrink:0;}
.lc.side{display:grid;grid-template-columns:15rem 1fr;}
.lc.side .lc-img{min-height:17rem;}
.lc-img{position:relative;overflow:hidden;}
.lc-img img{width:100%;height:100%;object-fit:cover;transition:transform .8s var(--ease);}
.lc:hover .lc-img img{transform:scale(1.06);}
.lc-ov{position:absolute;inset:0;background:linear-gradient(to top,rgba(15,2,2,.68) 0%,transparent 55%);}
.lc-badge{position:absolute;top:1.4rem;left:1.4rem;background:rgba(255,255,255,.93);backdrop-filter:blur(12px);padding:.4rem 1rem;border-radius:99px;font-size:1rem;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:.4rem;}
.lc-badge i{color:var(--r);}
.lc-save{position:absolute;top:1.4rem;right:1.4rem;width:3.6rem;height:3.6rem;border-radius:50%;background:rgba(255,255,255,.93);backdrop-filter:blur(12px);border:none;cursor:pointer;display:grid;place-items:center;font-size:1.4rem;color:var(--ink3);transition:all .22s;}
.lc-save:hover,.lc-save.saved{color:var(--r);}
.lc-price-tag{position:absolute;bottom:1.4rem;right:1.4rem;background:rgba(15,2,2,.78);backdrop-filter:blur(12px);padding:.55rem 1.3rem;border-radius:99px;}
.lc-price-tag span{font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:700;color:#fff;}
.lc-body{padding:2.4rem;display:flex;flex-direction:column;flex:1;}
.lc-type{font-size:.95rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--r);margin-bottom:.5rem;}
.lc-name{font-family:'Cormorant Garamond',serif;font-size:2.2rem;font-weight:700;color:var(--ink);margin-bottom:.4rem;line-height:1.1;}
.lc-addr{font-size:1.2rem;color:var(--ink3);display:flex;align-items:center;gap:.4rem;margin-bottom:1.5rem;}
.lc-addr i{color:var(--r);font-size:.95rem;}
.lc-pills{display:flex;gap:.7rem;flex-wrap:wrap;margin-bottom:1.8rem;}
.lc-pill{display:flex;align-items:center;gap:.4rem;background:var(--rp);border:1px solid rgba(214,40,40,.1);color:var(--ink2);padding:.4rem .95rem;border-radius:99px;font-size:1.05rem;font-weight:600;}
.lc-pill i{font-size:.85rem;color:var(--r);}
.lc-acts{display:flex;gap:.8rem;flex-wrap:wrap;margin-top:auto;padding-top:1.6rem;border-top:1px solid var(--line);}
.lca{padding:.75rem 1.4rem;border-radius:99px;font-size:1.1rem;font-weight:700;cursor:pointer;font-family:'Outfit',sans-serif;transition:all .22s;text-decoration:none;border:none;display:flex;align-items:center;gap:.5rem;}
.lca.v{background:var(--r);color:#fff;box-shadow:0 4px 14px rgba(214,40,40,.3);}
.lca.v:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(214,40,40,.45);}
.lca.b{background:var(--rp);color:var(--r);border:1.5px solid rgba(214,40,40,.18);}
.lca.b:hover{background:var(--rp2);}
.lca.e{background:var(--white);color:var(--ink2);border:1.5px solid var(--line);}
.lca.e:hover{border-color:var(--r);color:var(--r);}
.expl-sec{padding:8rem 6%;background:var(--bg);overflow:hidden;}
.cat-track{display:flex;gap:1.8rem;align-items:stretch;}
.ct{position:relative;border-radius:2.4rem;overflow:hidden;cursor:pointer;flex-shrink:0;transition:box-shadow .4s var(--ease),transform .08s linear;will-change:transform;}
.ct:hover{box-shadow:0 32px 80px rgba(214,40,40,.25);}
.ct.tall{width:22rem;height:50rem;}
.ct.mid{width:20rem;height:38rem;margin-top:6rem;}
.ct.short{width:18rem;height:30rem;margin-top:12rem;}
.ct.wide{width:28rem;height:44rem;margin-top:3rem;}
.ct.sqr{width:19rem;height:36rem;margin-top:8rem;}
.ct-img-wrap{position:absolute;inset:0;overflow:hidden;border-radius:inherit;}
.ct-img-wrap img{width:100%;height:100%;object-fit:cover;transition:transform .9s var(--ease);}
.ct:hover .ct-img-wrap img{transform:scale(1.1);}
.ct-ov{position:absolute;inset:0;transition:opacity .5s;}
.ct-ov-a{position:absolute;inset:0;background:linear-gradient(160deg,rgba(15,2,2,.05) 0%,rgba(15,2,2,.85) 100%);}
.ct-ov-b{position:absolute;inset:0;background:linear-gradient(160deg,rgba(120,5,5,.3) 0%,rgba(15,2,2,.97) 100%);opacity:0;transition:opacity .5s var(--ease);}
.ct:hover .ct-ov-b{opacity:1;}
.ct::after{content:'';position:absolute;inset:0;border-radius:inherit;box-shadow:inset 0 0 0 2px rgba(214,40,40,0);transition:box-shadow .4s;pointer-events:none;}
.ct:hover::after{box-shadow:inset 0 0 0 2px rgba(214,40,40,.55);}
.ct-body{position:absolute;bottom:0;left:0;right:0;padding:2.8rem 2.4rem;z-index:2;}
.ct-num{font-family:'Cormorant Garamond',serif;font-size:9rem;font-weight:700;color:rgba(255,255,255,.04);line-height:1;position:absolute;top:1.5rem;right:2rem;transition:color .4s;}
.ct:hover .ct-num{color:rgba(214,40,40,.08);}
.ct-icon{width:5rem;height:5rem;border-radius:1.4rem;background:rgba(255,255,255,.1);backdrop-filter:blur(14px);display:grid;place-items:center;font-size:2rem;color:#fff;margin-bottom:1.4rem;border:1px solid rgba(255,255,255,.16);transition:all .4s var(--ease);transform:translateY(0) rotate(0deg);}
.ct:hover .ct-icon{background:var(--r);border-color:var(--r);box-shadow:0 8px 30px rgba(214,40,40,.6);transform:translateY(-4px) rotate(-8deg);}
.ct-name{font-family:'Cormorant Garamond',serif;font-size:2.6rem;font-weight:700;color:#fff;line-height:1.05;margin-bottom:.5rem;transition:letter-spacing .4s var(--ease);}
.ct:hover .ct-name{letter-spacing:.025em;}
.ct-count{font-size:1.1rem;color:rgba(255,255,255,.5);display:flex;align-items:center;gap:.5rem;transition:all .4s;}
.ct:hover .ct-count{color:rgba(255,180,180,.85);}
.ct-pill{display:inline-flex;align-items:center;gap:.5rem;background:rgba(214,40,40,.88);backdrop-filter:blur(12px);color:#fff;padding:.45rem 1.2rem;border-radius:99px;font-size:1.05rem;font-weight:700;margin-top:1.2rem;transform:translateY(2rem);opacity:0;transition:all .45s var(--ease);}
.ct:hover .ct-pill{transform:translateY(0);opacity:1;}
.feed-sec{padding:8rem 6%;background:var(--white);overflow:hidden;}
.feed-layout{display:grid;grid-template-columns:1fr 1.4fr;gap:5rem;align-items:start;}
.feed-left{}
.live-badge{display:inline-flex;align-items:center;gap:.7rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;padding:.5rem 1.3rem;border-radius:99px;font-size:1rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;margin-bottom:2rem;box-shadow:0 4px 18px rgba(214,40,40,.32);}
.live-dot{width:.7rem;height:.7rem;border-radius:50%;background:#fff;animation:livepulse 1.4s ease-in-out infinite;}
@keyframes livepulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.6);opacity:.5}}
.feed-counter-row{display:grid;grid-template-columns:1fr 1fr;gap:1.4rem;margin-top:3.5rem;}
.fcount{background:var(--bg);border:1.5px solid var(--line);border-radius:2rem;padding:2.4rem;transition:all .35s var(--ease);}
.fcount:hover{border-color:rgba(214,40,40,.28);box-shadow:var(--sh);transform:translateY(-4px);}
.fcount-icon{width:4.8rem;height:4.8rem;border-radius:1.2rem;background:var(--rp);display:grid;place-items:center;font-size:2rem;color:var(--r);margin-bottom:1.6rem;transition:all .3s;}
.fcount:hover .fcount-icon{background:var(--r);color:#fff;}
.fcount-n{font-family:'Cormorant Garamond',serif;font-size:5rem;font-weight:700;color:var(--ink);line-height:1;margin-bottom:.3rem;}
.fcount-l{font-size:1.2rem;color:var(--ink3);}
.fcount-delta{display:inline-flex;align-items:center;gap:.4rem;margin-top:.8rem;font-size:1.05rem;font-weight:700;padding:.3rem .8rem;border-radius:99px;}
.fcount-delta.up{color:#2e7d32;background:#e8f5e9;}
.fcount-delta.hot{color:var(--r);background:var(--rp);}
.feed-stream{display:flex;flex-direction:column;gap:0;}
.feed-item{display:flex;align-items:flex-start;gap:1.6rem;padding:2rem 2.4rem;border-bottom:1px solid var(--line);transition:background .2s;position:relative;}
.feed-item:last-child{border-bottom:none;}
.feed-item:hover{background:var(--rp);}
.feed-item::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:transparent;border-radius:0 2px 2px 0;transition:background .2s;}
.feed-item:hover::before{background:var(--r);}
.fi-icon{width:4.4rem;height:4.4rem;border-radius:1.2rem;display:grid;place-items:center;font-size:1.7rem;flex-shrink:0;}
.fi-icon.view{background:#fff3e0;color:#e65100;}
.fi-icon.enq{background:#e8f5e9;color:#2e7d32;}
.fi-icon.save{background:var(--rp);color:var(--r);}
.fi-icon.new{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;box-shadow:0 4px 14px rgba(214,40,40,.28);}
.fi-icon.visit{background:#e3f2fd;color:#1565c0;}
.fi-body{flex:1;}
.fi-text{font-size:1.3rem;font-weight:600;color:var(--ink);line-height:1.5;margin-bottom:.3rem;}
.fi-text strong{color:var(--r);}
.fi-meta{display:flex;align-items:center;gap:1rem;}
.fi-time{font-size:1.05rem;color:var(--ink3);display:flex;align-items:center;gap:.4rem;}
.fi-time i{font-size:.85rem;}
.fi-prop-tag{font-size:1.05rem;font-weight:600;color:var(--ink2);background:var(--rp2);padding:.2rem .8rem;border-radius:99px;}
.fi-pulse{width:.7rem;height:.7rem;border-radius:50%;background:var(--r);animation:livepulse 2s ease-in-out infinite;flex-shrink:0;margin-top:.6rem;}
.fi-pulse.green{background:#2e7d32;}
.fi-pulse.blue{background:#1565c0;}
.fi-pulse.orange{background:#e65100;}
.feed-stream-hd{display:flex;align-items:center;justify-content:space-between;padding:1.6rem 2.4rem;background:var(--bg);border-bottom:1px solid var(--line);border-radius:2rem 2rem 0 0;}
.feed-stream-hd span{font-size:1.15rem;font-weight:700;color:var(--ink2);}
.feed-stream-hd .live-now{display:flex;align-items:center;gap:.5rem;font-size:1.05rem;font-weight:700;color:var(--r);}
.feed-stream-box{border:1.5px solid var(--line);border-radius:2rem;overflow:hidden;}
.nbhd-sec{padding:8rem 6%;background:var(--bg);}
.nbhd-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-radius:3rem;overflow:hidden;box-shadow:0 32px 96px rgba(214,40,40,.16);}
.nb{position:relative;height:62rem;overflow:hidden;cursor:pointer;}
.nb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:transform 1s var(--ease);}
.nb:hover img{transform:scale(1.08);}
.nb-ov{position:absolute;inset:0;background:linear-gradient(to top,rgba(10,2,2,.96) 0%,rgba(10,2,2,.5) 40%,rgba(10,2,2,.15) 100%);transition:background .6s var(--ease);}
.nb:hover .nb-ov{background:linear-gradient(to top,rgba(10,2,2,.98) 0%,rgba(10,2,2,.62) 55%,rgba(10,2,2,.12) 100%);}
.nb:not(:last-child)::after{content:'';position:absolute;right:0;top:0;bottom:0;width:1px;background:rgba(255,255,255,.08);z-index:10;}
.nb-static{position:absolute;bottom:0;left:0;right:0;padding:3.5rem 3rem 3rem;z-index:5;transform:translateY(0);transition:transform .6s var(--ease);}
.nb:hover .nb-static{transform:translateY(-26rem);}
.nb-city-name{font-family:'Cormorant Garamond',serif;font-size:clamp(3.5rem,3.5vw,5rem);font-weight:700;color:#fff;line-height:.95;letter-spacing:-.03em;margin-bottom:.5rem;}
.nb-state{font-size:1.2rem;color:rgba(255,255,255,.45);letter-spacing:.12em;text-transform:uppercase;}
.nb-listing-count{display:inline-flex;align-items:center;gap:.5rem;margin-top:1.4rem;background:rgba(214,40,40,.22);backdrop-filter:blur(12px);border:1px solid rgba(214,40,40,.3);color:rgba(255,200,200,.9);padding:.4rem 1.1rem;border-radius:99px;font-size:1.05rem;font-weight:700;}
.nb-detail{position:absolute;bottom:-26rem;left:0;right:0;padding:2.5rem 3rem 3rem;z-index:5;transition:bottom .6s var(--ease);}
.nb:hover .nb-detail{bottom:0;}
.nb-price-strip{display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;padding-bottom:1.6rem;border-bottom:1px solid rgba(255,255,255,.12);}
.nb-avg-price{font-family:'Cormorant Garamond',serif;font-size:3.2rem;font-weight:700;color:#fff;}
.nb-avg-label{font-size:1.05rem;color:rgba(255,255,255,.45);margin-top:.2rem;}
.nb-rating{display:flex;flex-direction:column;align-items:flex-end;}
.nb-stars{color:#f59e0b;font-size:1.2rem;letter-spacing:.1rem;}
.nb-rating-label{font-size:1.05rem;color:rgba(255,255,255,.45);margin-top:.3rem;}
.nb-amen{display:grid;grid-template-columns:1fr 1fr;gap:.8rem;margin-bottom:2rem;}
.nb-am{display:flex;align-items:center;gap:.8rem;padding:1rem 1.2rem;background:rgba(255,255,255,.07);backdrop-filter:blur(12px);border-radius:1.1rem;border:1px solid rgba(255,255,255,.09);}
.nb-am-ic{width:2.8rem;height:2.8rem;border-radius:.7rem;background:rgba(214,40,40,.2);display:grid;place-items:center;font-size:1.1rem;color:rgba(255,180,180,.9);flex-shrink:0;}
.nb-am-label{font-size:1rem;font-weight:700;color:rgba(255,255,255,.9);line-height:1.2;}
.nb-am-val{font-size:.9rem;color:rgba(255,255,255,.45);}
.nb-cta{display:flex;align-items:center;gap:.8rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;padding:1.1rem 2.4rem;font-size:1.3rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 6px 22px rgba(214,40,40,.45);transition:all .22s;width:100%;justify-content:center;text-decoration:none;}
.nb-cta:hover{transform:translateY(-2px);box-shadow:0 12px 36px rgba(214,40,40,.6);}
.emi-sec{padding:7rem 6%;background:linear-gradient(150deg,var(--rp) 0%,var(--rp2) 50%,var(--rp3) 100%);}
.emi-inner{display:grid;grid-template-columns:1fr 1fr;gap:6rem;align-items:center;}
.emi-left{}
.emi-right{background:var(--white);border-radius:2.8rem;padding:4rem;box-shadow:var(--sh2);}
.emi-output{margin-bottom:3.5rem;}
.emi-output-label{font-size:1.1rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--ink3);margin-bottom:.6rem;}
.emi-output-n{font-family:'Cormorant Garamond',serif;font-size:7rem;font-weight:700;color:var(--ink);line-height:1;letter-spacing:-.03em;}
.emi-output-n span{font-size:2.8rem;color:var(--r);}
.emi-output-sub{font-size:1.3rem;color:var(--ink3);margin-top:.4rem;}
.emi-breakdown{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.2rem;margin-bottom:3rem;}
.emib{background:var(--rp);border-radius:1.4rem;padding:1.5rem;border:1.5px solid var(--line);text-align:center;}
.emib-n{font-family:'Cormorant Garamond',serif;font-size:2.4rem;font-weight:700;color:var(--ink);}
.emib-l{font-size:1rem;color:var(--ink3);margin-top:.2rem;}
.emi-sliders{display:flex;flex-direction:column;gap:2rem;}
.emi-field label{font-size:1.1rem;font-weight:600;color:var(--ink2);display:flex;justify-content:space-between;margin-bottom:.8rem;}
.emi-field label span{font-weight:800;color:var(--r);}
.emi-range{-webkit-appearance:none;width:100%;height:5px;border-radius:99px;background:var(--rp3);outline:none;cursor:pointer;}
.emi-range::-webkit-slider-thumb{-webkit-appearance:none;width:2rem;height:2rem;border-radius:50%;background:var(--r);box-shadow:0 2px 10px rgba(214,40,40,.35);cursor:pointer;border:2px solid #fff;}
.agent-sec{background:linear-gradient(155deg,#fff9f9 0%,#fdf1f1 40%,#fae8e8 100%);overflow:hidden;position:relative;}
.agent-sec-bg{position:absolute;inset:0;z-index:0;pointer-events:none;overflow:hidden;}
.agent-sec-bg svg{width:100%;height:100%;opacity:.18;}
.agent-banner{position:relative;z-index:1;display:grid;grid-template-columns:1fr 1fr;min-height:52rem;overflow:hidden;}
.agent-banner-img{position:relative;overflow:hidden;}
.agent-banner-img img{width:100%;height:100%;object-fit:cover;filter:brightness(.88);}
.agent-banner-img-ov{position:absolute;inset:0;background:linear-gradient(to right,transparent 40%,rgba(253,241,241,.9) 100%);}
.agent-banner-content{padding:6rem 6% 6rem 5%;display:flex;flex-direction:column;justify-content:center;}
.agent-banner-eyebrow{display:inline-flex;align-items:center;gap:.6rem;background:var(--rp);border:1px solid rgba(214,40,40,.18);color:var(--r);padding:.4rem 1.3rem;border-radius:99px;font-size:.9rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;margin-bottom:2.5rem;width:fit-content;}
.agent-banner-h{font-family:'Cormorant Garamond',serif;font-size:clamp(4.5rem,6vw,8.5rem);font-weight:700;color:var(--ink);letter-spacing:-.04em;line-height:.88;margin-bottom:2rem;}
.agent-banner-h em{font-style:italic;color:var(--r);}
.agent-banner-sub{font-size:1.55rem;color:var(--ink3);max-width:46rem;line-height:1.72;}
.agent-steps-wrap{padding:6rem 6%;position:relative;z-index:1;}
.agent-steps-title{text-align:center;margin-bottom:4rem;}
.agent-steps-title h3{font-family:'Cormorant Garamond',serif;font-size:clamp(3rem,4vw,5rem);font-weight:700;color:var(--ink);letter-spacing:-.03em;}
.agent-steps-title h3 em{font-style:italic;color:var(--r);}
.agent-steps-title p{font-size:1.4rem;color:var(--ink3);margin-top:.8rem;}
.agent-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:0;position:relative;}
.agent-steps::before{content:'';position:absolute;top:3.5rem;left:calc(12.5% + 2rem);right:calc(12.5% + 2rem);height:1px;background:linear-gradient(to right,rgba(214,40,40,.35),rgba(214,40,40,.12),rgba(214,40,40,.35));z-index:0;}
.as-step{display:flex;flex-direction:column;align-items:center;text-align:center;padding:0 2rem;position:relative;z-index:1;}
.as-num{width:7rem;height:7rem;border-radius:50%;background:var(--white);border:1.5px solid rgba(214,40,40,.22);display:grid;place-items:center;font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:700;color:rgba(214,40,40,.6);margin-bottom:2rem;position:relative;transition:all .3s;box-shadow:0 4px 16px rgba(214,40,40,.08);}
.as-num::after{content:'';position:absolute;inset:-4px;border-radius:50%;border:1px solid rgba(214,40,40,.08);}
.as-step.active .as-num{background:var(--r);border-color:var(--r);color:#fff;box-shadow:0 8px 32px rgba(214,40,40,.35);}
.as-title{font-size:1.5rem;font-weight:700;color:var(--ink);margin-bottom:.6rem;}
.as-desc{font-size:1.2rem;color:var(--ink3);line-height:1.6;}
.as-step.locked .as-title{color:var(--ink3);}
.as-step.locked .as-num{opacity:.5;}
.agent-benefits-wrap{padding:0 6% 5rem;position:relative;z-index:1;}
.agent-benefits-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.6rem;margin-bottom:4rem;}
.ag-benefit{background:var(--white);border:1.5px solid var(--line);border-radius:2rem;padding:3rem;transition:all .35s var(--ease);}
.ag-benefit:hover{border-color:rgba(214,40,40,.3);box-shadow:var(--sh2);transform:translateY(-5px);}
.ag-b-icon{width:5rem;height:5rem;border-radius:1.3rem;background:var(--rp);display:grid;place-items:center;font-size:2rem;color:var(--r);margin-bottom:2rem;transition:all .3s;}
.ag-benefit:hover .ag-b-icon{background:var(--r);color:#fff;box-shadow:0 6px 22px rgba(214,40,40,.32);}
.ag-b-title{font-size:1.55rem;font-weight:700;color:var(--ink);margin-bottom:.6rem;}
.ag-b-desc{font-size:1.2rem;color:var(--ink3);line-height:1.7;}
.agent-warning{display:flex;align-items:flex-start;gap:1.8rem;background:var(--rp2);border:1.5px solid rgba(214,40,40,.18);border-radius:2rem;padding:2.5rem 3rem;margin-bottom:4rem;}
.aw-icon{width:5rem;height:5rem;border-radius:1.2rem;background:var(--rp3);display:grid;place-items:center;font-size:2rem;color:var(--r);flex-shrink:0;}
.aw-title{font-size:1.5rem;font-weight:700;color:var(--ink);margin-bottom:.5rem;}
.aw-text{font-size:1.25rem;color:var(--ink3);line-height:1.65;}
.agent-cta-row{display:flex;align-items:center;justify-content:center;gap:2rem;flex-wrap:wrap;}
.agent-apply-btn{display:inline-flex;align-items:center;gap:1rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;padding:1.8rem 4.5rem;font-size:1.55rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 12px 40px rgba(214,40,40,.35);transition:all .3s;letter-spacing:.02em;}
.agent-apply-btn:hover{transform:translateY(-4px);box-shadow:0 22px 60px rgba(214,40,40,.55);}
.agent-note{font-size:1.2rem;color:var(--ink3);display:flex;align-items:center;gap:.5rem;}
.agent-note i{color:var(--r);}
.popup-ov{display:none;position:fixed;inset:0;z-index:1200;background:rgba(10,2,2,.7);backdrop-filter:blur(10px);align-items:center;justify-content:center;}
.popup-ov.open{display:flex;}
.ap-box{background:var(--white);border-radius:3rem;width:90%;max-width:62rem;max-height:90vh;overflow-y:auto;position:relative;box-shadow:0 48px 120px rgba(214,40,40,.3);}
.ap-hd{padding:3.5rem 4rem 0;display:flex;align-items:flex-start;justify-content:space-between;gap:2rem;}
.ap-title{font-family:'Cormorant Garamond',serif;font-size:3.8rem;font-weight:700;color:var(--ink);line-height:1;}
.ap-title em{font-style:italic;color:var(--r);}
.ap-sub{font-size:1.3rem;color:var(--ink3);margin-top:.6rem;max-width:38rem;line-height:1.65;}
.ap-close{width:4rem;height:4rem;border-radius:50%;border:1.5px solid var(--line);background:var(--bg);display:grid;place-items:center;font-size:1.5rem;color:var(--ink3);cursor:pointer;flex-shrink:0;transition:all .2s;}
.ap-close:hover{border-color:var(--r);color:var(--r);}
.ap-body{padding:3rem 4rem 4rem;}
.ap-progress{display:flex;gap:0;margin-bottom:3rem;background:var(--bg);border-radius:1.4rem;padding:.5rem;border:1.5px solid var(--line);}
.ap-prog-step{flex:1;padding:.9rem;border-radius:1rem;text-align:center;font-size:1.15rem;font-weight:700;color:var(--ink3);transition:all .3s;}
.ap-prog-step.active{background:var(--white);color:var(--r);box-shadow:0 2px 12px rgba(214,40,40,.12);}
.ap-prog-step.done{color:var(--r);}
.ap-panel{display:none;}
.ap-panel.active{display:block;}
.ap-row{display:grid;grid-template-columns:1fr 1fr;gap:1.4rem;margin-bottom:1.4rem;}
.ap-field{display:flex;flex-direction:column;gap:.5rem;}
.ap-field.full{grid-column:1/-1;}
.ap-field label{font-size:1.15rem;font-weight:700;color:var(--ink2);}
.ap-field label span{color:var(--r);}
.ap-field input,.ap-field select,.ap-field textarea{padding:1.2rem 1.6rem;border:1.5px solid var(--line);border-radius:1.4rem;font-size:1.3rem;font-family:'Outfit',sans-serif;color:var(--ink);background:var(--rp);outline:none;transition:all .2s;width:100%;}
.ap-field input:focus,.ap-field select:focus,.ap-field textarea:focus{border-color:rgba(214,40,40,.4);background:var(--white);}
.ap-field textarea{resize:vertical;min-height:9rem;}
.ap-upload{border:2px dashed rgba(214,40,40,.25);border-radius:1.4rem;padding:2.5rem;text-align:center;background:var(--rp);cursor:pointer;transition:all .22s;}
.ap-upload:hover{border-color:var(--r);background:var(--rp2);}
.ap-upload i{font-size:3rem;color:var(--r);margin-bottom:1rem;display:block;}
.ap-upload-text{font-size:1.3rem;font-weight:600;color:var(--ink2);margin-bottom:.3rem;}
.ap-upload-sub{font-size:1.1rem;color:var(--ink3);}
.ap-terms{display:flex;align-items:flex-start;gap:1.2rem;padding:1.8rem;background:var(--rp);border-radius:1.4rem;border:1px solid rgba(214,40,40,.12);margin-bottom:2rem;font-size:1.2rem;color:var(--ink3);line-height:1.65;}
.ap-terms input{width:1.8rem;height:1.8rem;accent-color:var(--r);flex-shrink:0;margin-top:.15rem;cursor:pointer;}
.ap-nav{display:flex;gap:1rem;justify-content:flex-end;margin-top:2rem;}
.ap-back{padding:1.2rem 2.8rem;border-radius:99px;font-size:1.3rem;font-weight:700;cursor:pointer;font-family:'Outfit',sans-serif;border:1.5px solid var(--line);background:var(--white);color:var(--ink2);transition:all .22s;}
.ap-back:hover{border-color:var(--r);color:var(--r);}
.ap-next{padding:1.2rem 3rem;border-radius:99px;font-size:1.3rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;box-shadow:0 6px 20px rgba(214,40,40,.32);transition:all .22s;}
.ap-next:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(214,40,40,.48);}
.ap-success{text-align:center;padding:2rem 0;}
.ap-success-icon{width:10rem;height:10rem;border-radius:50%;background:linear-gradient(135deg,var(--r),var(--rd));display:grid;place-items:center;font-size:4rem;color:#fff;margin:0 auto 2.5rem;box-shadow:0 16px 48px rgba(214,40,40,.38);}
.ap-success h3{font-family:'Cormorant Garamond',serif;font-size:4.2rem;font-weight:700;color:var(--ink);margin-bottom:1rem;}
.ap-success p{font-size:1.4rem;color:var(--ink3);line-height:1.72;max-width:42rem;margin:0 auto;}
.vp-box{background:var(--white);border-radius:3rem;width:90%;max-width:66rem;max-height:92vh;overflow-y:auto;position:relative;box-shadow:0 48px 120px rgba(214,40,40,.28);}
.vp-hd{padding:3.5rem 4rem 0;display:flex;align-items:flex-start;justify-content:space-between;}
.vp-hd-left .eyebrow{margin-bottom:.6rem;}
.vp-title{font-family:'Cormorant Garamond',serif;font-size:3.8rem;font-weight:700;color:var(--ink);line-height:1;}
.vp-title em{font-style:italic;color:var(--r);}
.vp-sub{font-size:1.3rem;color:var(--ink3);margin-top:.5rem;}
.vp-close{width:4rem;height:4rem;border-radius:50%;border:1.5px solid var(--line);background:var(--bg);display:grid;place-items:center;font-size:1.5rem;color:var(--ink3);cursor:pointer;flex-shrink:0;transition:all .2s;}
.vp-close:hover{border-color:var(--r);color:var(--r);}
.vp-steps-bar{display:flex;align-items:center;gap:0;padding:2.5rem 4rem 0;}
.vps{display:flex;align-items:center;gap:1rem;flex:1;}
.vps:last-child{flex:none;}
.vps-num{width:3.4rem;height:3.4rem;border-radius:50%;border:2px solid var(--line);display:grid;place-items:center;font-size:1.2rem;font-weight:800;color:var(--ink3);transition:all .3s;background:var(--bg);flex-shrink:0;}
.vps.act .vps-num,.vps.done .vps-num{border-color:var(--r);background:var(--r);color:#fff;}
.vps-label{font-size:1.1rem;font-weight:600;color:var(--ink3);}
.vps.act .vps-label,.vps.done .vps-label{color:var(--r);}
.vps-line{flex:1;height:2px;background:var(--line);margin:0 .8rem;transition:background .3s;}
.vps-line.done{background:var(--r);}
.vp-body{padding:3rem 4rem 4rem;}
.vp-panel{display:none;}
.vp-panel.act{display:block;}
.vp-row{display:grid;grid-template-columns:1fr 1fr;gap:1.4rem;margin-bottom:1.4rem;}
.vp-f{display:flex;flex-direction:column;gap:.5rem;}
.vp-f.full{grid-column:1/-1;}
.vp-f label{font-size:1.15rem;font-weight:700;color:var(--ink2);}
.vp-f label span{color:var(--r);}
.vp-f input,.vp-f select,.vp-f textarea{padding:1.2rem 1.6rem;border:1.5px solid var(--line);border-radius:1.4rem;font-size:1.3rem;font-family:'Outfit',sans-serif;color:var(--ink);background:var(--rp);outline:none;transition:all .2s;width:100%;}
.vp-f input:focus,.vp-f select:focus{border-color:rgba(214,40,40,.4);background:var(--white);}
.vp-f textarea{resize:vertical;min-height:8rem;}
.time-slots{display:grid;grid-template-columns:repeat(4,1fr);gap:.8rem;margin-top:.6rem;}
.ts{padding:.9rem;border:1.5px solid var(--line);border-radius:1rem;text-align:center;font-size:1.2rem;font-weight:600;color:var(--ink2);cursor:pointer;transition:all .22s;background:var(--rp);}
.ts:hover,.ts.sel{border-color:var(--r);background:var(--r);color:#fff;}
.ts.na{opacity:.35;cursor:not-allowed;}
.vp-nav{display:flex;gap:1rem;justify-content:flex-end;margin-top:2.5rem;}
.vp-back{padding:1.2rem 2.8rem;border-radius:99px;font-size:1.3rem;font-weight:700;cursor:pointer;font-family:'Outfit',sans-serif;border:1.5px solid var(--line);background:var(--white);color:var(--ink2);transition:all .22s;}
.vp-back:hover{border-color:var(--r);color:var(--r);}
.vp-fwd{padding:1.2rem 3rem;border-radius:99px;font-size:1.3rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;box-shadow:0 6px 20px rgba(214,40,40,.32);transition:all .22s;}
.vp-fwd:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(214,40,40,.48);}
.vp-success{text-align:center;padding:2rem 0;}
.vp-sicon{width:9rem;height:9rem;border-radius:50%;background:linear-gradient(135deg,var(--r),var(--rd));display:grid;place-items:center;font-size:3.5rem;color:#fff;margin:0 auto 2.5rem;box-shadow:0 12px 40px rgba(214,40,40,.38);}
.vp-success h3{font-family:'Cormorant Garamond',serif;font-size:4rem;font-weight:700;color:var(--ink);margin-bottom:1rem;}
.vp-success p{font-size:1.4rem;color:var(--ink3);line-height:1.7;max-width:40rem;margin:0 auto;}
.footer{background:linear-gradient(135deg,#fff0f0,#fde0e0);border-top:1px solid var(--line);padding:6rem 6% 3.5rem;}
.foot-grid{display:grid;grid-template-columns:2.2fr 1fr 1fr 1.3fr;gap:5rem;padding-bottom:4rem;border-bottom:1px solid var(--line);}
.foot-logo{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);display:block;margin-bottom:1.2rem;text-decoration:none;}
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
  .hero{grid-template-columns:1fr;min-height:auto;}
  .hero-r{height:50vw;min-height:35rem;}
  .hf1,.hf3{display:none;}
  .lst-grid{grid-template-columns:1fr;}
  .lc.big{grid-row:auto;}
  .lc.big .lc-img{height:28rem;}
  .cat-track{flex-wrap:wrap;}
  .ct.tall,.ct.mid,.ct.short,.ct.wide,.ct.sqr{width:calc(50% - .9rem);height:28rem;margin-top:0;}
  .feed-layout{grid-template-columns:1fr;}
  .nbhd-grid{grid-template-columns:1fr 1fr;}
  .nb:nth-child(3){display:none;}
  .agent-banner{grid-template-columns:1fr;}
  .agent-banner-img{height:35rem;}
  .agent-steps{grid-template-columns:1fr 1fr;gap:3rem;}
  .agent-steps::before{display:none;}
  .agent-benefits-grid{grid-template-columns:1fr 1fr;}
  .emi-inner{grid-template-columns:1fr;}
  .foot-grid{grid-template-columns:1fr 1fr;gap:3rem;}
}
@media(max-width:768px){
  .nav-center{display:none;}
  .hero-l{padding:4rem 5%;}
  .srch-row{grid-template-columns:1fr 1fr;}
  .srch-btn{grid-column:1/-1;width:100%;justify-content:center;}
  .cat-track{flex-direction:column;}
  .ct.tall,.ct.mid,.ct.short,.ct.wide,.ct.sqr{width:100%;height:24rem;margin-top:0;}
  .lc.side{grid-template-columns:1fr;}
  .lc.side .lc-img{height:20rem;}
  .nbhd-grid{grid-template-columns:1fr;}
  .nb:nth-child(3){display:block;}
  .nb{height:55rem!important;}
  .agent-benefits-grid{grid-template-columns:1fr;}
  .vp-row,.ap-row{grid-template-columns:1fr;}
  .vp-hd,.vp-body,.ap-hd,.ap-body{padding-left:2.5rem;padding-right:2.5rem;}
  .time-slots{grid-template-columns:repeat(3,1fr);}
  .foot-grid{grid-template-columns:1fr;}
  .foot-bot{flex-direction:column;}
}
@media(max-width:480px){
  .stats-strip{grid-template-columns:1fr 1fr;}
  .si:nth-child(2){border-right:none;}
  .si:nth-child(1),.si:nth-child(2){padding-bottom:2rem;border-bottom:1px solid rgba(255,255,255,.15);}
  .hero-pills{flex-direction:column;}
  .hp{justify-content:center;}
  .agent-steps{grid-template-columns:1fr;}
}
</style>
</head>
<body>
<!-- BOOK VISIT POPUP -->
<div class="popup-ov" id="visitPopup">
  <div class="vp-box">
    <div class="vp-hd">
      <div class="vp-hd-left">
        <div class="eyebrow">Schedule Now</div>
        <div class="vp-title">Book a <em>Site Visit</em></div>
        <div class="vp-sub">Professional scheduling in under 2 minutes</div>
      </div>
      <button class="vp-close" onclick="closePopup('visitPopup')"><i class="fas fa-times"></i></button>
    </div>
    <div class="vp-steps-bar">
      <div class="vps act" id="vps1"><div class="vps-num">1</div><div class="vps-label">Property & Date</div></div>
      <div class="vps-line" id="vline1"></div>
      <div class="vps" id="vps2"><div class="vps-num">2</div><div class="vps-label">Your Details</div></div>
      <div class="vps-line" id="vline2"></div>
      <div class="vps" id="vps3"><div class="vps-num">3</div><div class="vps-label">Confirm</div></div>
    </div>
    <div class="vp-body">
      <div class="vp-panel act" id="vpanel1">
        <div class="vp-row">
          <div class="vp-f full"><label>Select Property <span>*</span></label><select id="vp-prop"><option value="">Choose the property you want to visit</option><option>Commercial Shop — FC Road, Pune (₹55L)</option><option>Spacious 5BHK Villa — Juhu, Mumbai (₹5.5 Cr)</option><option>Modern 1BHK Studio — Baner, Pune (₹28L)</option><option>3BHK Premium Flat — Andheri West, Mumbai (₹1.8 Cr)</option></select></div>
          <div class="vp-f"><label>Preferred Date <span>*</span></label><input type="date" id="vp-date"></div>
          <div class="vp-f"><label>Visit Purpose <span>*</span></label><select id="vp-purpose"><option value="">Select purpose</option><option>Buying</option><option>Renting</option><option>Investment</option><option>Exploring options</option></select></div>
          <div class="vp-f full"><label>Select Time Slot <span>*</span></label>
            <div class="time-slots">
              <div class="ts" onclick="pickSlot(this)">9:00 AM</div><div class="ts" onclick="pickSlot(this)">10:30 AM</div><div class="ts" onclick="pickSlot(this)">12:00 PM</div><div class="ts na">1:30 PM</div><div class="ts" onclick="pickSlot(this)">3:00 PM</div><div class="ts" onclick="pickSlot(this)">4:30 PM</div><div class="ts na">6:00 PM</div><div class="ts" onclick="pickSlot(this)">7:00 PM</div>
            </div>
          </div>
          <div class="vp-f full"><label>Special Requirements</label><textarea placeholder="Anything specific you'd like to check during the visit..."></textarea></div>
        </div>
        <div class="vp-nav"><button class="vp-fwd" onclick="vpGo(2)">Continue <i class="fas fa-arrow-right"></i></button></div>
      </div>
      <div class="vp-panel" id="vpanel2">
        <div class="vp-row">
          <div class="vp-f"><label>Full Name <span>*</span></label><input type="text" placeholder="Your full name"></div>
          <div class="vp-f"><label>Phone Number <span>*</span></label><input type="tel" placeholder="+91 98765 43210"></div>
          <div class="vp-f"><label>Email Address <span>*</span></label><input type="email" placeholder="you@email.com"></div>
          <div class="vp-f"><label>Budget Range</label><select><option value="">Select budget</option><option>Under ₹30 Lakh</option><option>₹30L – ₹1 Crore</option><option>₹1Cr – ₹3 Crore</option><option>Above ₹3 Crore</option></select></div>
        </div>
        <div class="vp-nav"><button class="vp-back" onclick="vpGo(1)"><i class="fas fa-arrow-left"></i> Back</button><button class="vp-fwd" onclick="vpGo(3)">Confirm Booking <i class="fas fa-check"></i></button></div>
      </div>
      <div class="vp-panel" id="vpanel3">
        <div class="vp-success"><div class="vp-sicon"><i class="fas fa-calendar-check"></i></div><h3>Visit Confirmed!</h3><p>Your site visit has been scheduled. Our agent will call you within <strong>2 hours</strong> to confirm. Check your email for details.</p><br><button class="vp-fwd" onclick="closePopup('visitPopup')" style="margin:0 auto;display:flex;">Done <i class="fas fa-check"></i></button></div>
      </div>
    </div>
  </div>
</div>
<!-- AGENT POPUP -->
<div class="popup-ov" id="agentPopup">
  <div class="ap-box">
    <div class="ap-hd">
      <div><div class="eyebrow">Application</div><div class="ap-title">Agent <em>Application</em></div><div class="ap-sub">3 steps. Admin review. No shortcuts — this is how quality is maintained.</div></div>
      <button class="ap-close" onclick="closePopup('agentPopup')"><i class="fas fa-times"></i></button>
    </div>
    <div class="ap-body">
      <div class="ap-progress"><div class="ap-prog-step active" id="aps1">1. Your Profile</div><div class="ap-prog-step" id="aps2">2. Experience</div><div class="ap-prog-step" id="aps3">3. Documents</div></div>
      <div class="ap-panel active" id="apanel1">
        <div class="ap-row">
          <div class="ap-field"><label>Full Name <span>*</span></label><input type="text" placeholder="Your full legal name"></div>
          <div class="ap-field"><label>Phone Number <span>*</span></label><input type="tel" placeholder="+91 98765 43210"></div>
          <div class="ap-field"><label>Email Address <span>*</span></label><input type="email" placeholder="professional@email.com"></div>
          <div class="ap-field"><label>City <span>*</span></label><select><option value="">Select your city</option><option>Mumbai</option><option>Pune</option><option>Both</option></select></div>
          <div class="ap-field full"><label>Brief Introduction <span>*</span></label><textarea placeholder="Who are you, what makes you a good real estate agent?"></textarea></div>
        </div>
        <div class="ap-nav"><button class="ap-next" onclick="apGo(2)">Next Step <i class="fas fa-arrow-right"></i></button></div>
      </div>
      <div class="ap-panel" id="apanel2">
        <div class="ap-row">
          <div class="ap-field"><label>Years of Experience <span>*</span></label><select><option value="">Select</option><option>Fresher</option><option>1–2 years</option><option>3–5 years</option><option>5–10 years</option><option>10+ years</option></select></div>
          <div class="ap-field"><label>Specialisation <span>*</span></label><select><option value="">Select</option><option>Residential</option><option>Commercial</option><option>Plots & Land</option><option>All types</option></select></div>
          <div class="ap-field"><label>Previous Agency</label><input type="text" placeholder="If applicable"></div>
          <div class="ap-field"><label>LinkedIn Profile</label><input type="url" placeholder="https://linkedin.com/in/yourprofile"></div>
          <div class="ap-field full"><label>Why MyEstate? <span>*</span></label><textarea placeholder="What value can you bring to MyEstate buyers and sellers?"></textarea></div>
        </div>
        <div class="ap-nav"><button class="ap-back" onclick="apGo(1)"><i class="fas fa-arrow-left"></i> Back</button><button class="ap-next" onclick="apGo(3)">Next Step <i class="fas fa-arrow-right"></i></button></div>
      </div>
      <div class="ap-panel" id="apanel3">
        <div class="ap-row">
          <div class="ap-field full"><label>Government ID <span>*</span></label><div class="ap-upload"><i class="fas fa-cloud-upload-alt"></i><div class="ap-upload-text">Click to upload or drag & drop</div><div class="ap-upload-sub">PDF, JPG or PNG — max 5MB</div></div></div>
          <div class="ap-field full"><label>RERA Certificate (optional)</label><div class="ap-upload"><i class="fas fa-file-certificate"></i><div class="ap-upload-text">Upload your RERA certificate</div><div class="ap-upload-sub">PDF — max 5MB</div></div></div>
        </div>
        <div class="ap-terms"><input type="checkbox" id="apTerms"><label for="apTerms">I confirm all information is accurate. I understand that listing properties requires admin approval after agent verification. I accept MyEstate's agent code of conduct.</label></div>
        <div class="ap-nav"><button class="ap-back" onclick="apGo(2)"><i class="fas fa-arrow-left"></i> Back</button><button class="ap-next" onclick="submitAgent()"><i class="fas fa-paper-plane"></i> Submit Application</button></div>
      </div>
      <div class="ap-panel" id="apanel4">
        <div class="ap-success"><div class="ap-success-icon"><i class="fas fa-user-check"></i></div><h3>Application Submitted</h3><p>Our team will personally review your profile within <strong>3 working days</strong>. You'll receive an email with next steps.</p><br><button class="ap-next" onclick="closePopup('agentPopup')" style="margin:0 auto;display:flex;">Done <i class="fas fa-check"></i></button></div>
      </div>
    </div>
  </div>
</div>
<!-- NAV -->
<nav class="nav" id="mainNav">
  <a href="home.php" class="logo">My<span>Estate</span></a>
  <div class="nav-center"><a href="home.php" class="active">Home</a><a href="listings.php">Properties</a><a href="about.php">About</a><a href="#footer">Contact</a></div>
  <div class="nav-right">
    <a href="saved.php" class="nav-icon"><i class="fas fa-heart"></i><?php if($saved_count > 0): ?><span class="nav-badge"><?= $saved_count; ?></span><?php endif; ?></a>
    <div class="nav-user">
      <div class="nav-av"><?= $user_initial; ?></div>
      <span style="font-size:1.3rem;font-weight:700;color:var(--ink);"><?= htmlspecialchars($user_name); ?></span>
      <i class="fas fa-chevron-down" style="font-size:1rem;color:var(--ink3);margin-left:.4rem;"></i>
      <div class="nav-drop-menu">
        <a href="saved.php" class="nd-item"><i class="fas fa-heart"></i>Saved Properties</a>
        <a href="requests.php" class="nd-item"><i class="fas fa-file-alt"></i>My Requests</a>
        <div class="nd-sep"></div>
        <a href="#agentSec" class="nd-item" style="color:var(--r);font-weight:700;"><i class="fas fa-user-tie" style="color:var(--r);"></i>Become an Agent</a>
        <div class="nd-sep"></div>
        <a href="update.php" class="nd-item"><i class="fas fa-user-edit"></i>Edit Profile</a>
        <div class="nd-sep"></div>
        <a href="components/user_logout.php" class="nd-item nd-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
      </div>
    </div>
  </div>
</nav>
<!-- HERO -->
<section class="hero">
  <div class="hero-deco-ring r1"></div><div class="hero-deco-ring r2"></div><div class="hero-deco-ring r3"></div>
  <div class="hero-l">
    <div class="hero-tag">Welcome back, <?= htmlspecialchars($user_name); ?></div>
    <h1 class="hero-h"><span>Good evening,</span><em><?= htmlspecialchars($user_name); ?>.</em><span style="font-size:.62em;color:var(--ink3);font-weight:400;font-family:'Outfit',sans-serif;font-style:normal;letter-spacing:-.01em;">Find your</span><span>perfect home.</span></h1>
    <p class="hero-sub">You have <strong style="color:var(--r);"><?= $saved_count; ?> saved properties</strong> and <?= $total_listings; ?> verified listings waiting for you across Mumbai & Pune.</p>
    <div class="srch-wrap">
      <div class="srch-label">Search Properties</div>
      <form action="listings.php" method="GET">
      <div class="srch-row">
        <div class="sf"><label>Location</label><input type="text" name="location" placeholder="City or area..."></div>
        <div class="sf"><label>Type</label><select name="type"><option value="">Any type</option><option value="flat">Apartment</option><option value="house">Villa</option><option value="shop">Commercial</option></select></div>
        <div class="sf"><label>Budget</label><select name="budget"><option value="">Any budget</option><option value="0-3000000">Under ₹30L</option><option value="3000000-10000000">₹30L – ₹1Cr</option><option value="10000000-999999999">Above ₹1Cr</option></select></div>
        <button type="submit" class="srch-btn"><i class="fas fa-search"></i> Search</button>
      </div>
      </form>
    </div>
    <div class="hero-pills">
      <button class="hp prim" onclick="openPopup('visitPopup')"><i class="fas fa-calendar-check"></i> Book a Site Visit</button>
      <a href="listings.php" class="hp sec"><i class="fas fa-building"></i> All Properties</a>
      <a href="saved.php" class="hp sec"><i class="fas fa-heart"></i> Saved (<?= $saved_count; ?>)</a>
      <a href="#nbhd" class="hp sec"><i class="fas fa-map-marked-alt"></i> Explore Areas</a>
    </div>
    <div class="trust-row">
      <div class="tr-item"><i class="fas fa-shield-alt"></i> 100% Verified</div>
      <div class="tr-item"><i class="fas fa-rupee-sign"></i> Zero Commission</div>
      <div class="tr-item"><i class="fas fa-headset"></i> 7-Day Support</div>
    </div>
  </div>
  <div class="hero-r">
    <img src="https://images.unsplash.com/photo-1613977257365-aaae5a9817ff?w=1400&q=90&auto=format" alt="Property">
    <div class="hero-r-grad"></div>
    <div class="hf hf1"><div class="hf-ic"><i class="fas fa-home"></i></div><div class="hf-n"><?= $total_listings; ?>+</div><div class="hf-l">Properties Listed</div></div>
    <div class="hf hf2"><div class="hf-ic"><i class="fas fa-users"></i></div><div class="hf-n"><?= $total_users; ?>+</div><div class="hf-l">Happy Buyers</div></div>
    <div class="hf hf3"><div class="hf-ic"><i class="fas fa-star"></i></div><div class="hf-n">4.9★</div><div class="hf-l">Avg Rating</div></div>
  </div>
</section>
<!-- STATS -->
<div class="stats-strip">
  <div class="ss-texture"></div>
  <div class="si reveal"><div class="si-i"><i class="fas fa-building"></i></div><div class="si-n"><?= $total_listings; ?>+</div><div class="si-l">Active Listings</div></div>
  <div class="si reveal" style="transition-delay:.07s"><div class="si-i"><i class="fas fa-users"></i></div><div class="si-n"><?= $total_users; ?>+</div><div class="si-l">Registered Buyers</div></div>
  <div class="si reveal" style="transition-delay:.14s"><div class="si-i"><i class="fas fa-city"></i></div><div class="si-n">2</div><div class="si-l">Cities Covered</div></div>
  <div class="si reveal" style="transition-delay:.21s"><div class="si-i"><i class="fas fa-star"></i></div><div class="si-n">4.9★</div><div class="si-l">Average Rating</div></div>
</div>
<!-- LISTINGS -->
<section class="lst-sec">
  <div class="sec-hd reveal"><div><div class="eyebrow">New Arrivals</div><h2 class="sec-title">Latest <em>Listings</em></h2><p class="sec-sub">Fresh verified properties this week across Mumbai & Pune.</p></div><a href="listings.php" class="btn-ol">View All <i class="fas fa-arrow-right"></i></a></div>
  <div class="lst-grid">
    <div class="lc big reveal">
      <div class="lc-img" style="height:38rem;"><img src="https://images.unsplash.com/photo-1613977257592-4871e5fcd7c4?w=1200&q=88&auto=format" alt="Villa"><div class="lc-ov"></div><div class="lc-badge"><i class="fas fa-fire"></i> Featured</div><button class="lc-save"><i class="fas fa-heart"></i></button><div class="lc-price-tag"><span>₹5.5 Cr</span></div></div>
      <div class="lc-body"><div class="lc-type">Villa • For Sale</div><div class="lc-name">Spacious 5BHK Villa</div><div class="lc-addr"><i class="fas fa-map-marker-alt"></i> Juhu, Mumbai</div><div class="lc-pills"><div class="lc-pill"><i class="fas fa-bed"></i> 5 BHK</div><div class="lc-pill"><i class="fas fa-bath"></i> 5 Bath</div><div class="lc-pill"><i class="fas fa-ruler-combined"></i> 5500 sqft</div><div class="lc-pill"><i class="fas fa-couch"></i> Furnished</div></div><div class="lc-acts"><a href="view_property.php?id=2" class="lca v"><i class="fas fa-eye"></i> View</a><button class="lca b" onclick="openPopup('visitPopup')"><i class="fas fa-calendar-check"></i> Book Visit</button><a href="view_property.php?id=2#enquiry" class="lca e"><i class="fas fa-phone-alt"></i> Enquire</a></div></div>
    </div>
    <div class="lc side reveal" style="transition-delay:.08s">
      <div class="lc-img"><img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&q=85&auto=format" alt="Shop"><div class="lc-ov"></div><button class="lc-save"><i class="fas fa-heart"></i></button><div class="lc-price-tag"><span>₹55 L</span></div></div>
      <div class="lc-body"><div class="lc-type">Commercial • Sale</div><div class="lc-name">Commercial Shop</div><div class="lc-addr"><i class="fas fa-map-marker-alt"></i> FC Road, Pune</div><div class="lc-pills"><div class="lc-pill"><i class="fas fa-ruler-combined"></i> 450 sqft</div><div class="lc-pill"><i class="fas fa-bolt"></i> New</div></div><div class="lc-acts"><a href="view_property.php?id=1" class="lca v"><i class="fas fa-eye"></i> View</a><button class="lca b" onclick="openPopup('visitPopup')"><i class="fas fa-calendar-check"></i> Visit</button></div></div>
    </div>
    <div class="lc side reveal" style="transition-delay:.14s">
      <div class="lc-img"><img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600&q=85&auto=format" alt="Studio"><div class="lc-ov"></div><button class="lc-save saved"><i class="fas fa-heart"></i></button><div class="lc-price-tag"><span>₹28 L</span></div></div>
      <div class="lc-body"><div class="lc-type">Apartment • Sale</div><div class="lc-name">Modern 1BHK Studio</div><div class="lc-addr"><i class="fas fa-map-marker-alt"></i> Baner, Pune</div><div class="lc-pills"><div class="lc-pill"><i class="fas fa-bed"></i> 1 BHK</div><div class="lc-pill"><i class="fas fa-ruler-combined"></i> 550 sqft</div></div><div class="lc-acts"><a href="view_property.php?id=3" class="lca v"><i class="fas fa-eye"></i> View</a><button class="lca b" onclick="openPopup('visitPopup')"><i class="fas fa-calendar-check"></i> Visit</button></div></div>
    </div>
  </div>
</section>
<!-- EXPLORE BY TYPE -->
<section class="expl-sec">
  <div class="sec-hd reveal"><div><div class="eyebrow">Browse</div><h2 class="sec-title">Explore by <em>Type</em></h2><p class="sec-sub">Every category verified, every listing real.</p></div><a href="listings.php" class="btn-ol">All Listings <i class="fas fa-arrow-right"></i></a></div>
  <div class="cat-track" id="catTrack">
    <div class="ct tall reveal" onclick="location='listings.php?type=apartment'"><div class="ct-img-wrap"><img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=700&q=88&auto=format" alt=""></div><div class="ct-ov"><div class="ct-ov-a"></div><div class="ct-ov-b"></div></div><div class="ct-num">01</div><div class="ct-body"><div class="ct-icon"><i class="fas fa-building"></i></div><div class="ct-name">Apartments</div><div class="ct-count"><i class="fas fa-home"></i> 4 listings</div><div class="ct-pill"><i class="fas fa-arrow-right"></i> Browse</div></div></div>
    <div class="ct mid reveal" style="transition-delay:.07s" onclick="location='listings.php?type=villa'"><div class="ct-img-wrap"><img src="https://images.unsplash.com/photo-1613977257592-4871e5fcd7c4?w=700&q=88&auto=format" alt=""></div><div class="ct-ov"><div class="ct-ov-a"></div><div class="ct-ov-b"></div></div><div class="ct-num">02</div><div class="ct-body"><div class="ct-icon"><i class="fas fa-home"></i></div><div class="ct-name">Villas</div><div class="ct-count"><i class="fas fa-home"></i> 2 listings</div><div class="ct-pill"><i class="fas fa-arrow-right"></i> Browse</div></div></div>
    <div class="ct short reveal" style="transition-delay:.14s" onclick="location='listings.php?type=plot'"><div class="ct-img-wrap"><img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=700&q=88&auto=format" alt=""></div><div class="ct-ov"><div class="ct-ov-a"></div><div class="ct-ov-b"></div></div><div class="ct-num">03</div><div class="ct-body"><div class="ct-icon"><i class="fas fa-vector-square"></i></div><div class="ct-name">Plots</div><div class="ct-count"><i class="fas fa-home"></i> 1 listing</div><div class="ct-pill"><i class="fas fa-arrow-right"></i> Browse</div></div></div>
    <div class="ct wide reveal" style="transition-delay:.21s" onclick="location='listings.php?type=commercial'"><div class="ct-img-wrap"><img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=900&q=88&auto=format" alt=""></div><div class="ct-ov"><div class="ct-ov-a"></div><div class="ct-ov-b"></div></div><div class="ct-num">04</div><div class="ct-body"><div class="ct-icon"><i class="fas fa-store"></i></div><div class="ct-name">Commercial</div><div class="ct-count"><i class="fas fa-home"></i> 1 listing</div><div class="ct-pill"><i class="fas fa-arrow-right"></i> Browse</div></div></div>
    <div class="ct sqr reveal" style="transition-delay:.28s" onclick="openPopup('visitPopup')"><div class="ct-img-wrap"><img src="https://images.unsplash.com/photo-1486325212027-8081e485255e?w=700&q=88&auto=format" alt=""></div><div class="ct-ov"><div class="ct-ov-a"></div><div class="ct-ov-b"></div></div><div class="ct-num">05</div><div class="ct-body"><div class="ct-icon"><i class="fas fa-calendar-check"></i></div><div class="ct-name">Book Visit</div><div class="ct-count"><i class="fas fa-clock"></i> Available today</div><div class="ct-pill"><i class="fas fa-calendar-plus"></i> Schedule</div></div></div>
  </div>
</section>
<!-- LIVE ACTIVITY FEED -->
<section class="feed-sec">
  <div class="feed-layout">
    <div class="feed-left reveal">
      <div class="live-badge"><div class="live-dot"></div> Live Activity</div>
      <div class="eyebrow">What's Happening</div>
      <h2 class="sec-title">Real-Time <em>Activity</em></h2>
      <p class="sec-sub" style="margin-bottom:0;">See what buyers are looking at right now — live enquiries, new saves, fresh listings and scheduled visits happening across the platform.</p>
      <div class="feed-counter-row">
        <div class="fcount reveal" style="transition-delay:.06s"><div class="fcount-icon"><i class="fas fa-eye"></i></div><div class="fcount-n">142</div><div class="fcount-l">Property views today</div><div class="fcount-delta up"><i class="fas fa-arrow-up"></i> +23 this hour</div></div>
        <div class="fcount reveal" style="transition-delay:.1s"><div class="fcount-icon"><i class="fas fa-phone-alt"></i></div><div class="fcount-n">18</div><div class="fcount-l">Enquiries sent today</div><div class="fcount-delta hot"><i class="fas fa-fire"></i> High demand</div></div>
        <div class="fcount reveal" style="transition-delay:.14s"><div class="fcount-icon"><i class="fas fa-calendar-check"></i></div><div class="fcount-n">7</div><div class="fcount-l">Visits booked today</div><div class="fcount-delta up"><i class="fas fa-arrow-up"></i> +3 since morning</div></div>
        <div class="fcount reveal" style="transition-delay:.18s"><div class="fcount-icon"><i class="fas fa-heart"></i></div><div class="fcount-n">34</div><div class="fcount-l">Properties saved today</div><div class="fcount-delta hot"><i class="fas fa-fire"></i> Trending</div></div>
      </div>
    </div>
    <div class="reveal" style="transition-delay:.08s">
      <div class="feed-stream-box">
        <div class="feed-stream-hd"><span>Platform Activity</span><div class="live-now"><div class="live-dot" style="width:.6rem;height:.6rem;background:var(--r);border-radius:50%;animation:livepulse 1.4s infinite;flex-shrink:0;"></div> Live</div></div>
        <div class="feed-stream" id="feedStream">
          <div class="feed-item"><div class="fi-icon new"><i class="fas fa-plus"></i></div><div class="fi-body"><div class="fi-text"><strong>New listing added</strong> — 3BHK Apartment in Baner, Pune</div><div class="fi-meta"><div class="fi-time"><i class="fas fa-clock"></i> 4 min ago</div><div class="fi-prop-tag">Apartment</div></div></div><div class="fi-pulse"></div></div>
          <div class="feed-item"><div class="fi-icon view"><i class="fas fa-eye"></i></div><div class="fi-body"><div class="fi-text"><strong>3 people</strong> viewed the 5BHK Villa in Juhu in the last hour</div><div class="fi-meta"><div class="fi-time"><i class="fas fa-clock"></i> 8 min ago</div><div class="fi-prop-tag">Villa</div></div></div><div class="fi-pulse orange"></div></div>
          <div class="feed-item"><div class="fi-icon visit"><i class="fas fa-calendar-check"></i></div><div class="fi-body"><div class="fi-text">Site visit booked for <strong>Commercial Shop</strong> on FC Road, Pune</div><div class="fi-meta"><div class="fi-time"><i class="fas fa-clock"></i> 15 min ago</div><div class="fi-prop-tag">Commercial</div></div></div><div class="fi-pulse blue"></div></div>
          <div class="feed-item"><div class="fi-icon enq"><i class="fas fa-phone-alt"></i></div><div class="fi-body"><div class="fi-text">New enquiry sent for <strong>1BHK Studio</strong> in Baner — seller notified</div><div class="fi-meta"><div class="fi-time"><i class="fas fa-clock"></i> 22 min ago</div><div class="fi-prop-tag">Apartment</div></div></div><div class="fi-pulse green"></div></div>
          <div class="feed-item"><div class="fi-icon save"><i class="fas fa-heart"></i></div><div class="fi-body"><div class="fi-text"><strong>5 users</strong> saved the 3BHK Premium Flat in Andheri West today</div><div class="fi-meta"><div class="fi-time"><i class="fas fa-clock"></i> 31 min ago</div><div class="fi-prop-tag">Apartment</div></div></div><div class="fi-pulse"></div></div>
          <div class="feed-item"><div class="fi-icon view"><i class="fas fa-eye"></i></div><div class="fi-body"><div class="fi-text">Residential Plot in Wakad, Pune viewed <strong>12 times</strong> today</div><div class="fi-meta"><div class="fi-time"><i class="fas fa-clock"></i> 45 min ago</div><div class="fi-prop-tag">Plot</div></div></div><div class="fi-pulse orange"></div></div>
          <div class="feed-item"><div class="fi-icon new"><i class="fas fa-user-plus"></i></div><div class="fi-body"><div class="fi-text"><strong>New buyer registered</strong> — looking for 2BHK in Pune under ₹60L</div><div class="fi-meta"><div class="fi-time"><i class="fas fa-clock"></i> 1 hr ago</div><div class="fi-prop-tag">New User</div></div></div><div class="fi-pulse"></div></div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- NEIGHBOURHOOD EXPLORER -->
<section class="nbhd-sec" id="nbhd">
  <div class="sec-hd reveal"><div><div class="eyebrow">Explore Areas</div><h2 class="sec-title">Neighbourhood <em>Explorer</em></h2><p class="sec-sub">Hover over an area to reveal schools, hospitals, malls, transit and live price data.</p></div><a href="listings.php" class="btn-ol">Browse by Area <i class="fas fa-arrow-right"></i></a></div>
  <div class="nbhd-grid">
    <div class="nb">
      <img src="https://images.unsplash.com/photo-1570168007204-dfb528c6958f?w=900&q=88&auto=format" alt="Bandra">
      <div class="nb-ov"></div>
      <div class="nb-static"><div class="nb-city-name">Bandra West</div><div class="nb-state">Mumbai, Maharashtra</div><div class="nb-listing-count"><i class="fas fa-building"></i> 3 listings available</div></div>
      <div class="nb-detail">
        <div class="nb-price-strip"><div><div class="nb-avg-price">₹28,000<span style="font-size:1.8rem;font-weight:400;color:rgba(255,255,255,.45)">/sqft</span></div><div class="nb-avg-label">Avg. market price</div></div><div class="nb-rating"><div class="nb-stars">★★★★★</div><div class="nb-rating-label">4.8 Liveability</div></div></div>
        <div class="nb-amen"><div class="nb-am"><div class="nb-am-ic"><i class="fas fa-graduation-cap"></i></div><div><div class="nb-am-label">12 Schools</div><div class="nb-am-val">St. Andrew's, Rizvi</div></div></div><div class="nb-am"><div class="nb-am-ic"><i class="fas fa-hospital"></i></div><div><div class="nb-am-label">8 Hospitals</div><div class="nb-am-val">Holy Family, Lilavati</div></div></div><div class="nb-am"><div class="nb-am-ic"><i class="fas fa-shopping-bag"></i></div><div><div class="nb-am-label">Malls & Markets</div><div class="nb-am-val">Linking Rd, Palladium</div></div></div><div class="nb-am"><div class="nb-am-ic"><i class="fas fa-train"></i></div><div><div class="nb-am-label">Transit</div><div class="nb-am-val">Bandra Station 1.2km</div></div></div></div>
        <a href="listings.php" class="nb-cta"><i class="fas fa-search"></i> View Listings in Bandra West</a>
      </div>
    </div>
    <div class="nb">
      <img src="https://images.unsplash.com/photo-1567157577867-05ccb1388e66?w=900&q=88&auto=format" alt="Andheri">
      <div class="nb-ov"></div>
      <div class="nb-static"><div class="nb-city-name">Andheri West</div><div class="nb-state">Mumbai, Maharashtra</div><div class="nb-listing-count"><i class="fas fa-building"></i> 2 listings available</div></div>
      <div class="nb-detail">
        <div class="nb-price-strip"><div><div class="nb-avg-price">₹22,000<span style="font-size:1.8rem;font-weight:400;color:rgba(255,255,255,.45)">/sqft</span></div><div class="nb-avg-label">Avg. market price</div></div><div class="nb-rating"><div class="nb-stars">★★★★★</div><div class="nb-rating-label">4.7 Liveability</div></div></div>
        <div class="nb-amen"><div class="nb-am"><div class="nb-am-ic"><i class="fas fa-graduation-cap"></i></div><div><div class="nb-am-label">15 Schools</div><div class="nb-am-val">Ryan Int'l, Orchid</div></div></div><div class="nb-am"><div class="nb-am-ic"><i class="fas fa-hospital"></i></div><div><div class="nb-am-label">Top Hospitals</div><div class="nb-am-val">Kokilaben, Nanavati</div></div></div><div class="nb-am"><div class="nb-am-ic"><i class="fas fa-shopping-bag"></i></div><div><div class="nb-am-label">Malls</div><div class="nb-am-val">InOrbit, Citi Mall</div></div></div><div class="nb-am"><div class="nb-am-ic"><i class="fas fa-subway"></i></div><div><div class="nb-am-label">Metro</div><div class="nb-am-val">Andheri Metro 0.5km</div></div></div></div>
        <a href="listings.php" class="nb-cta"><i class="fas fa-search"></i> View Listings in Andheri West</a>
      </div>
    </div>
    <div class="nb">
      <img src="https://images.unsplash.com/photo-1486325212027-8081e485255e?w=900&q=88&auto=format" alt="Hinjewadi">
      <div class="nb-ov"></div>
      <div class="nb-static"><div class="nb-city-name">Hinjewadi</div><div class="nb-state">Pune, Maharashtra</div><div class="nb-listing-count"><i class="fas fa-building"></i> 2 listings available</div></div>
      <div class="nb-detail">
        <div class="nb-price-strip"><div><div class="nb-avg-price">₹8,500<span style="font-size:1.8rem;font-weight:400;color:rgba(255,255,255,.45)">/sqft</span></div><div class="nb-avg-label">Avg. market price</div></div><div class="nb-rating"><div class="nb-stars">★★★★☆</div><div class="nb-rating-label">4.5 Liveability</div></div></div>
        <div class="nb-amen"><div class="nb-am"><div class="nb-am-ic"><i class="fas fa-graduation-cap"></i></div><div><div class="nb-am-label">9 Schools</div><div class="nb-am-val">Blue Ridge, VIBGYOR</div></div></div><div class="nb-am"><div class="nb-am-ic"><i class="fas fa-hospital"></i></div><div><div class="nb-am-label">6 Hospitals</div><div class="nb-am-val">Symbiosis, Sahyadri</div></div></div><div class="nb-am"><div class="nb-am-ic"><i class="fas fa-briefcase"></i></div><div><div class="nb-am-label">IT Companies</div><div class="nb-am-val">Infosys, TCS, Wipro</div></div></div><div class="nb-am"><div class="nb-am-ic"><i class="fas fa-shopping-bag"></i></div><div><div class="nb-am-label">Shopping</div><div class="nb-am-val">Westend Mall, D-Mart</div></div></div></div>
        <a href="listings.php" class="nb-cta"><i class="fas fa-search"></i> View Listings in Hinjewadi</a>
      </div>
    </div>
  </div>
</section>
<!-- EMI CALCULATOR -->
<section class="emi-sec">
  <div class="emi-inner">
    <div class="emi-left reveal"><div class="eyebrow">Financial Tools</div><h2 class="sec-title">EMI <em>Calculator</em></h2><p class="sec-sub">Estimate your monthly home loan payment instantly. Adjust loan amount, interest rate and tenure to plan your purchase.</p><br><div class="trust-row" style="margin-top:1rem;"><div class="tr-item"><i class="fas fa-landmark"></i> All major banks supported</div><div class="tr-item"><i class="fas fa-percentage"></i> Rates from 8.5% p.a.</div></div></div>
    <div class="emi-right reveal" style="transition-delay:.1s">
      <div class="emi-output"><div class="emi-output-label">Monthly EMI</div><div class="emi-output-n" id="emiN"><span>₹</span><span id="emiVal">42,500</span></div><div class="emi-output-sub" id="emiSub">on ₹50L loan at 8.5% for 20 years</div></div>
      <div class="emi-breakdown"><div class="emib"><div class="emib-n" id="ebLoan">₹50L</div><div class="emib-l">Loan Amount</div></div><div class="emib"><div class="emib-n" id="ebRate">8.5%</div><div class="emib-l">Interest Rate</div></div><div class="emib"><div class="emib-n" id="ebTenure">20 Yrs</div><div class="emib-l">Tenure</div></div></div>
      <div class="emi-sliders">
        <div class="emi-field"><label>Loan Amount <span id="eLoanL">₹50 Lakh</span></label><input type="range" class="emi-range" id="eLoan" min="10" max="500" value="50" oninput="calcEMI()"></div>
        <div class="emi-field"><label>Interest Rate <span id="eRateL">8.5%</span></label><input type="range" class="emi-range" id="eRate" min="6" max="15" step="0.1" value="8.5" oninput="calcEMI()"></div>
        <div class="emi-field"><label>Tenure <span id="eTenureL">20 Years</span></label><input type="range" class="emi-range" id="eTenure" min="5" max="30" value="20" oninput="calcEMI()"></div>
      </div>
    </div>
  </div>
</section>
<!-- BECOME AN AGENT -->
<section class="agent-sec" id="agentSec">
  <div class="agent-sec-bg">
    <svg viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
      <polygon points="720,40 820,200 720,360 620,200" fill="none" stroke="#d62828" stroke-width="1"/>
      <polygon points="720,80 800,200 720,320 640,200" fill="none" stroke="#d62828" stroke-width=".5"/>
      <path d="M0,0 L120,0 L120,20 L20,20 L20,120 L0,120 Z" fill="none" stroke="#d62828" stroke-width="1"/>
      <path d="M1440,0 L1320,0 L1320,20 L1420,20 L1420,120 L1440,120 Z" fill="none" stroke="#d62828" stroke-width="1"/>
      <path d="M0,900 L120,900 L120,880 L20,880 L20,780 L0,780 Z" fill="none" stroke="#d62828" stroke-width="1"/>
      <path d="M1440,900 L1320,900 L1320,880 L1420,880 L1420,780 L1440,780 Z" fill="none" stroke="#d62828" stroke-width="1"/>
      <g stroke="#d62828" stroke-width=".6"><line x1="100" y1="280" x2="140" y2="280"/><line x1="120" y1="260" x2="120" y2="300"/><line x1="300" y1="180" x2="340" y2="180"/><line x1="320" y1="160" x2="320" y2="200"/><line x1="1100" y1="280" x2="1140" y2="280"/><line x1="1120" y1="260" x2="1120" y2="300"/><line x1="1300" y1="480" x2="1340" y2="480"/><line x1="1320" y1="460" x2="1320" y2="500"/></g>
      <polygon points="80,450 140,415 200,450 200,520 140,555 80,520" fill="none" stroke="#d62828" stroke-width=".8"/>
      <polygon points="1360,450 1420,415 1440,450 1440,520 1420,555 1360,520" fill="none" stroke="#d62828" stroke-width=".8"/>
      <circle cx="400" cy="120" r="60" fill="none" stroke="#d62828" stroke-width=".7"/>
      <circle cx="400" cy="120" r="40" fill="none" stroke="#d62828" stroke-width=".4"/>
      <circle cx="1040" cy="780" r="60" fill="none" stroke="#d62828" stroke-width=".7"/>
      <circle cx="1040" cy="780" r="40" fill="none" stroke="#d62828" stroke-width=".4"/>
      <path d="M 520 900 Q 720 750 920 900" fill="none" stroke="#d62828" stroke-width=".8"/>
    </svg>
  </div>
  <div class="agent-banner">
    <div class="agent-banner-img"><img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1400&q=88&auto=format" alt="Agent"><div class="agent-banner-img-ov"></div></div>
    <div class="agent-banner-content reveal">
      <div class="agent-banner-eyebrow"><i class="fas fa-circle" style="font-size:.55rem;"></i> Exclusive Partnership</div>
      <h2 class="agent-banner-h">Want to <em>List</em><br>Properties<br>on MyEstate?</h2>
      <p class="agent-banner-sub">Not everyone can post. Only verified, approved agents can list properties. Here's how to earn that access.</p>
    </div>
  </div>
  <div class="agent-steps-wrap">
    <div class="agent-steps-title reveal"><h3>The <em>4-Step</em> Agent Journey</h3><p>No shortcuts. Every application is personally reviewed by our team.</p></div>
    <div class="agent-steps">
      <div class="as-step active reveal"><div class="as-num">01</div><div class="as-title">Apply Online</div><div class="as-desc">Fill your profile, experience & upload documents. Takes 5 minutes.</div></div>
      <div class="as-step reveal" style="transition-delay:.08s"><div class="as-num">02</div><div class="as-title">Admin Review</div><div class="as-desc">Our team personally reviews every application within 3 working days.</div></div>
      <div class="as-step locked reveal" style="transition-delay:.16s"><div class="as-num">03</div><div class="as-title">Background Check</div><div class="as-desc">Identity & documents verified. RERA compliance checked if applicable.</div></div>
      <div class="as-step locked reveal" style="transition-delay:.24s"><div class="as-num">04</div><div class="as-title">Badge Unlocked</div><div class="as-desc">Get your verified badge and start listing properties with full access.</div></div>
    </div>
  </div>
  <div class="agent-benefits-wrap">
    <div class="agent-benefits-grid">
      <div class="ag-benefit reveal"><div class="ag-b-icon"><i class="fas fa-shield-check"></i></div><div class="ag-b-title">Verified Agent Badge</div><div class="ag-b-desc">A trust signal that buyers respect. Your profile is highlighted across all search results.</div></div>
      <div class="ag-benefit reveal" style="transition-delay:.07s"><div class="ag-b-icon"><i class="fas fa-building"></i></div><div class="ag-b-title">Unlimited Listings</div><div class="ag-b-desc">Post and manage multiple properties with full photo support and pricing control.</div></div>
      <div class="ag-benefit reveal" style="transition-delay:.14s"><div class="ag-b-icon"><i class="fas fa-phone-volume"></i></div><div class="ag-b-title">Direct Buyer Leads</div><div class="ag-b-desc">Qualified enquiries and visit requests delivered straight to your profile. No middlemen.</div></div>
      <div class="ag-benefit reveal" style="transition-delay:.07s"><div class="ag-b-icon"><i class="fas fa-chart-bar"></i></div><div class="ag-b-title">Analytics Dashboard</div><div class="ag-b-desc">See how many people viewed, saved and enquired about each listing in real time.</div></div>
      <div class="ag-benefit reveal" style="transition-delay:.14s"><div class="ag-b-icon"><i class="fas fa-star"></i></div><div class="ag-b-title">Priority Placement</div><div class="ag-b-desc">Agent listings rank higher in search and appear in featured sections on the homepage.</div></div>
      <div class="ag-benefit reveal" style="transition-delay:.21s"><div class="ag-b-icon"><i class="fas fa-headset"></i></div><div class="ag-b-title">Dedicated Support</div><div class="ag-b-desc">A dedicated account manager assists you with every listing, documentation and buyer query.</div></div>
    </div>
    <div class="agent-warning reveal">
      <div class="aw-icon"><i class="fas fa-lock"></i></div>
      <div><div class="aw-title">Listing is Locked Until You're Approved</div><div class="aw-text">Regular registered users cannot post properties directly. All listings must come from verified agents. Apply below — approval is fair, transparent and based purely on merit.</div></div>
    </div>
    <div class="agent-cta-row reveal">
      <button class="agent-apply-btn" onclick="openPopup('agentPopup')"><i class="fas fa-paper-plane"></i> Apply to Become an Agent</button>
      <div class="agent-note"><i class="fas fa-clock"></i> Reviewed in 3 working days &nbsp;·&nbsp; <i class="fas fa-rupee-sign"></i> Free to apply</div>
    </div>
  </div>
</section>
<!-- FOOTER -->
<footer class="footer" id="footer">
  <div class="foot-grid">
    <div class="foot-brand"><a href="home.php" class="foot-logo">My<span>Estate</span></a><p>Trusted real estate across Mumbai & Pune. Verified listings, zero commission, expert guidance.</p><div class="foot-socials"><a href="#" class="fsc"><i class="fab fa-instagram"></i></a><a href="#" class="fsc"><i class="fab fa-facebook-f"></i></a><a href="#" class="fsc"><i class="fab fa-twitter"></i></a><a href="#" class="fsc"><i class="fab fa-youtube"></i></a></div></div>
    <div class="foot-col"><h4>Properties</h4><a href="listings.php?type=apartment"><i class="fas fa-chevron-right"></i>Apartments</a><a href="listings.php?type=villa"><i class="fas fa-chevron-right"></i>Villas</a><a href="listings.php?type=plot"><i class="fas fa-chevron-right"></i>Plots</a><a href="listings.php?type=commercial"><i class="fas fa-chevron-right"></i>Commercial</a></div>
    <div class="foot-col"><h4>Quick Links</h4><a href="home.php"><i class="fas fa-chevron-right"></i>Dashboard</a><a href="listings.php"><i class="fas fa-chevron-right"></i>All Listings</a><a href="saved.php"><i class="fas fa-chevron-right"></i>Saved</a><a href="about.php"><i class="fas fa-chevron-right"></i>About Us</a></div>
    <div class="foot-col"><h4>Contact Us</h4><div class="fci"><div class="fci-ic"><i class="fas fa-map-marker-alt"></i></div><div class="fci-t"><strong>Office</strong>Bandra West, Mumbai — 400050</div></div><div class="fci"><div class="fci-ic"><i class="fas fa-phone-alt"></i></div><div class="fci-t"><strong>Phone</strong>+91 98765 43210</div></div><div class="fci"><div class="fci-ic"><i class="fas fa-envelope"></i></div><div class="fci-t"><strong>Email</strong>hello@myestate.in</div></div></div>
  </div>
  <div class="foot-bot"><p class="foot-copy">© 2026 <span>MyEstate</span>. Made with ♥ in Mumbai.</p><div class="foot-bot-links"><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Cookies</a></div></div>
</footer>
<script>
const obs=new IntersectionObserver(e=>e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('in');obs.unobserve(x.target);}}),{threshold:.05});
document.querySelectorAll('.reveal').forEach(r=>obs.observe(r));
window.addEventListener('scroll',()=>document.getElementById('mainNav').classList.toggle('scrolled',scrollY>40));
document.querySelectorAll('.lc-save').forEach(b=>b.addEventListener('click',e=>{e.preventDefault();b.classList.toggle('saved');}));
function openPopup(id){document.getElementById(id).classList.add('open');document.body.style.overflow='hidden';}
function closePopup(id){document.getElementById(id).classList.remove('open');document.body.style.overflow='';}
document.querySelectorAll('.popup-ov').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)closePopup(o.id);}));
(function(){const d=new Date();document.getElementById('vp-date').min=d.toISOString().split('T')[0];})();
let selSlot='';
function pickSlot(el){document.querySelectorAll('.ts').forEach(t=>t.classList.remove('sel'));el.classList.add('sel');selSlot=el.textContent;}
let vStep=1;
function vpGo(n){
  if(n===2){if(!document.getElementById('vp-prop').value||!document.getElementById('vp-date').value||!document.getElementById('vp-purpose').value||!selSlot){alert('Please fill all required fields and select a time slot.');return;}}
  document.querySelectorAll('.vp-panel').forEach(p=>p.classList.remove('act'));
  document.querySelectorAll('.vps').forEach(s=>s.classList.remove('act','done'));
  document.querySelectorAll('.vps-line').forEach(l=>l.classList.remove('done'));
  for(let i=1;i<n;i++){document.getElementById('vps'+i).classList.add('done');const ln=document.getElementById('vline'+i);if(ln)ln.classList.add('done');}
  document.getElementById('vps'+n).classList.add('act');
  document.getElementById('vpanel'+n).classList.add('act');
  vStep=n;
}
let apStep=1;
function apGo(n){
  document.querySelectorAll('.ap-panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.ap-prog-step').forEach((s,i)=>{s.classList.remove('active','done');if(i+1<n)s.classList.add('done');});
  document.getElementById('aps'+n).classList.add('active');
  document.getElementById('apanel'+n).classList.add('active');
  apStep=n;
}
function submitAgent(){
  if(!document.getElementById('apTerms').checked){alert('Please accept the terms and conditions.');return;}
  document.querySelectorAll('.ap-panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.ap-prog-step').forEach(s=>{s.classList.remove('active');s.classList.add('done');});
  document.getElementById('apanel4').classList.add('active');
}
function calcEMI(){
  const P=parseFloat(document.getElementById('eLoan').value)*100000;
  const r=parseFloat(document.getElementById('eRate').value)/12/100;
  const n=parseFloat(document.getElementById('eTenure').value)*12;
  const emi=Math.round(P*r*Math.pow(1+r,n)/(Math.pow(1+r,n)-1));
  document.getElementById('emiVal').textContent=emi.toLocaleString('en-IN');
  const loan=document.getElementById('eLoan').value;
  const rate=parseFloat(document.getElementById('eRate').value).toFixed(1);
  const ten=document.getElementById('eTenure').value;
  document.getElementById('eLoanL').textContent='₹'+loan+' Lakh';
  document.getElementById('eRateL').textContent=rate+'%';
  document.getElementById('eTenureL').textContent=ten+' Years';
  document.getElementById('ebLoan').textContent='₹'+loan+'L';
  document.getElementById('ebRate').textContent=rate+'%';
  document.getElementById('ebTenure').textContent=ten+' Yrs';
  document.getElementById('emiSub').textContent='on ₹'+loan+'L loan at '+rate+'% for '+ten+' years';
}
document.querySelectorAll('.ct').forEach(card=>{
  card.addEventListener('mousemove',e=>{
    const r=card.getBoundingClientRect();
    const x=e.clientX-r.left,y=e.clientY-r.top;
    const cx=r.width/2,cy=r.height/2;
    const rx=(cy-y)/cy*12,ry=(x-cx)/cx*12;
    card.style.transform=`perspective(800px) rotateX(${rx}deg) rotateY(${ry}deg) scale(1.03)`;
  });
  card.addEventListener('mouseleave',()=>{card.style.transform='perspective(800px) rotateX(0) rotateY(0) scale(1)';card.style.transition='transform .5s var(--ease),box-shadow .4s var(--ease)';});
  card.addEventListener('mouseenter',()=>{card.style.transition='transform .08s linear,box-shadow .4s';});
});
const feedMessages=[
  {icon:'plus',cls:'new',text:'<strong>New listing added</strong> — 2BHK Apartment in Wakad, Pune',tag:'Apartment',time:'just now'},
  {icon:'eye',cls:'view',text:'A buyer is viewing <strong>Villa in Juhu</strong> right now',tag:'Villa',time:'1 min ago'},
  {icon:'calendar-check',cls:'visit',text:'New site visit booked for <strong>Studio in Baner</strong>',tag:'Apartment',time:'2 min ago'},
  {icon:'heart',cls:'save',text:'<strong>2 users</strong> just saved the Premium Flat in Andheri',tag:'Apartment',time:'3 min ago'},
  {icon:'phone-alt',cls:'enq',text:'Enquiry received for <strong>Commercial Shop</strong> on FC Road',tag:'Commercial',time:'5 min ago'},
];
let feedIdx=0;
setInterval(()=>{
  const stream=document.getElementById('feedStream');
  const msg=feedMessages[feedIdx%feedMessages.length];
  const pulseColors={new:'',view:'orange',enq:'green',save:'',visit:'blue'};
  const item=document.createElement('div');
  item.className='feed-item';item.style.opacity='0';item.style.transform='translateY(-10px)';item.style.transition='all .5s';
  item.innerHTML=`<div class="fi-icon ${msg.cls}"><i class="fas fa-${msg.icon}"></i></div><div class="fi-body"><div class="fi-text">${msg.text}</div><div class="fi-meta"><div class="fi-time"><i class="fas fa-clock"></i> ${msg.time}</div><div class="fi-prop-tag">${msg.tag}</div></div></div><div class="fi-pulse ${pulseColors[msg.cls]||''}"></div>`;
  stream.insertBefore(item,stream.firstChild);
  setTimeout(()=>{item.style.opacity='1';item.style.transform='translateY(0)';},50);
  if(stream.children.length>8)stream.lastChild.remove();
  feedIdx++;
},5000);
</script>
</body>
</html>