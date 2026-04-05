<?php

include '../components/connect.php';

$admin_id = validate_admin_cookie($conn);
if(!$admin_id){
   header('location:login.php');
   exit();
}

$select_admin = $conn->prepare("SELECT * FROM `admins` WHERE id = ? LIMIT 1");
$select_admin->execute([$admin_id]);
$fetch_admin = $select_admin->fetch(PDO::FETCH_ASSOC);

$count_listings = $conn->prepare("SELECT COUNT(*) FROM `property`");
$count_listings->execute();
$total_listings = $count_listings->fetchColumn();

$count_users = $conn->prepare("SELECT COUNT(*) FROM `users`");
$count_users->execute();
$total_users = $count_users->fetchColumn();

$count_admins = $conn->prepare("SELECT COUNT(*) FROM `admins`");
$count_admins->execute();
$total_admins = $count_admins->fetchColumn();

$count_messages = $conn->prepare("SELECT COUNT(*) FROM `messages`");
$count_messages->execute();
$total_messages = $count_messages->fetchColumn();

// Bookings / requests
$count_requests = $conn->prepare("SELECT COUNT(*) FROM `requests`");
$count_requests->execute();
$total_requests = $count_requests->fetchColumn();

$count_pending = $conn->prepare("SELECT COUNT(*) FROM `requests` WHERE status='pending'");
$count_pending->execute();
$total_pending = $count_pending->fetchColumn();

$recent_requests = $conn->prepare(
    "SELECT r.id, r.visit_date, r.time_slot, r.purpose, r.status, r.date,
            r.user_name, r.user_phone,
            p.property_name, p.address
     FROM requests r
     LEFT JOIN property p ON r.property_id = p.id
     ORDER BY r.id DESC LIMIT 6"
);
$recent_requests->execute();

$recent_listings = $conn->prepare("SELECT * FROM `property` ORDER BY id DESC LIMIT 4");
$recent_listings->execute();

$recent_users = $conn->prepare("SELECT * FROM `users` ORDER BY id DESC LIMIT 4");
$recent_users->execute();

$recent_messages = $conn->prepare("SELECT * FROM `messages` ORDER BY id DESC LIMIT 4");
$recent_messages->execute();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<link rel="stylesheet" href="../css/admin_style.css">
<style>
:root{
  --r:#d62828;--rd:#9e1c1c;
  --rp:#fdf1f1;--rp2:#fae6e6;
  --ink:#0d0202;--ink2:#3a0f0f;--ink3:#9a6565;
  --bg:#f5eded;--white:#ffffff;
  --line:rgba(214,40,40,0.09);
  --ease:cubic-bezier(.22,1,.36,1);
  --sh:0 2px 24px rgba(214,40,40,0.07);
  --sh2:0 16px 56px rgba(214,40,40,0.14);
}
*{font-family:'Outfit',sans-serif;box-sizing:border-box;}
html{font-size:62.5%;}
::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-thumb{background:var(--rp2);border-radius:99px;}

/* ── PAGE LAYOUT ── */
.dash-body{background:var(--bg);min-height:100vh;}
.dash-wrap{padding:3rem 3.2rem;flex:1;}

/* ── TOPBAR ── */
.dash-tb{
  position:sticky;top:0;z-index:200;
  background:rgba(245,237,237,0.88);
  backdrop-filter:blur(20px);
  border-bottom:1px solid var(--line);
  padding:1.4rem 3.2rem;
  display:flex;align-items:center;justify-content:space-between;
}
.tb-path{display:flex;align-items:center;gap:1rem;font-size:1.3rem;}
.tb-path .seg{color:var(--ink3);}
.tb-path .sep{color:var(--ink3);opacity:0.3;}
.tb-path .cur{color:var(--ink);font-weight:700;}
.tb-r{display:flex;align-items:center;gap:1rem;}
.tb-dpill{
  display:flex;align-items:center;gap:0.8rem;
  background:var(--white);border:1px solid var(--line);
  border-radius:99px;padding:0.8rem 1.6rem;
  font-size:1.3rem;font-weight:600;color:var(--ink);
  box-shadow:var(--sh);
}
.tb-dpill i{color:var(--r);}
.tb-srch{
  display:flex;align-items:center;gap:1rem;
  background:var(--white);border:1px solid var(--line);
  border-radius:99px;padding:0.85rem 1.8rem;
  box-shadow:var(--sh);transition:all 0.2s;
}
.tb-srch:focus-within{border-color:rgba(214,40,40,0.3);}
.tb-srch input{border:none;outline:none;font-size:1.35rem;color:var(--ink);background:transparent;width:18rem;}
.tb-srch i{color:var(--ink3);font-size:1.3rem;}
.tb-ic{
  width:4rem;height:4rem;border-radius:50%;
  background:var(--white);border:1px solid var(--line);
  display:grid;place-items:center;
  cursor:pointer;color:var(--ink3);font-size:1.45rem;
  box-shadow:var(--sh);transition:all 0.2s;position:relative;
  text-decoration:none;
}
.tb-ic:hover{border-color:var(--r);color:var(--r);transform:translateY(-2px);}
.pip{position:absolute;top:0.5rem;right:0.5rem;width:0.8rem;height:0.8rem;border-radius:50%;background:var(--r);border:2px solid var(--bg);}

/* ── GREETING ── */
.pg-head{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:2.8rem;}
.pg-greeting{font-family:'Cormorant Garamond',serif;font-size:4rem;font-weight:700;color:var(--ink);line-height:1;letter-spacing:-0.03em;}
.pg-greeting em{font-style:italic;color:var(--r);}
.pg-sub{font-size:1.4rem;color:var(--ink3);margin-top:0.6rem;}
.pg-tabs{display:flex;gap:0.6rem;background:var(--white);padding:0.5rem;border-radius:1rem;border:1px solid var(--line);box-shadow:var(--sh);}
.pg-tab{padding:0.8rem 1.8rem;border-radius:0.7rem;font-size:1.3rem;font-weight:600;color:var(--ink3);cursor:pointer;transition:all 0.2s;}
.pg-tab.on{background:var(--r);color:#fff;box-shadow:0 4px 14px rgba(214,40,40,0.25);}
.pg-tab:not(.on):hover{background:var(--rp);color:var(--r);}

/* ── HERO ── */
.hero{display:grid;grid-template-columns:1fr 1fr 1fr;gap:2rem;margin-bottom:2.6rem;}
.h-main{
  grid-column:span 2;
  background:linear-gradient(145deg,#fff0f0 0%,#fde0e0 45%,#fac8c8 100%);
  border-radius:2rem;padding:3.6rem;
  position:relative;overflow:hidden;
  box-shadow:0 24px 72px rgba(214,40,40,0.14);
  border:1px solid rgba(214,40,40,0.15);
  display:flex;flex-direction:column;justify-content:space-between;
  min-height:24rem;
  animation:fadeUp 0.6s var(--ease) both;
}
.h-main::before{content:'';position:absolute;width:50rem;height:50rem;border-radius:50%;border:1.5px solid rgba(214,40,40,0.18);top:-22rem;right:-16rem;}
.h-main::after{content:'';position:absolute;width:28rem;height:28rem;border-radius:50%;border:1px solid rgba(214,40,40,0.12);bottom:-12rem;right:4rem;}
.hr3{position:absolute;width:18rem;height:18rem;border-radius:50%;border:1px solid rgba(255,255,255,0.03);top:50%;left:30%;transform:translateY(-50%);}
.glow{position:absolute;width:20rem;height:20rem;border-radius:50%;background:radial-gradient(circle,rgba(214,40,40,0.15),transparent 70%);top:50%;left:50%;transform:translate(-50%,-50%);animation:glowPulse 3s ease-in-out infinite;pointer-events:none;}
@keyframes glowPulse{0%,100%{opacity:0.4;transform:translate(-50%,-50%) scale(1)}50%{opacity:0.7;transform:translate(-50%,-50%) scale(1.3)}}
.h-tag{display:inline-flex;align-items:center;gap:0.8rem;background:rgba(214,40,40,0.12);border:1px solid rgba(214,40,40,0.25);color:var(--r);font-size:1.15rem;font-weight:600;padding:0.45rem 1.3rem;border-radius:99px;width:fit-content;margin-bottom:1.6rem;position:relative;z-index:1;letter-spacing:0.06em;}
.p-dot{width:0.55rem;height:0.55rem;border-radius:50%;background:var(--r);animation:blink 1.6s infinite;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:0.3}}
.h-title{font-family:'Cormorant Garamond',serif;font-size:4.2rem;font-weight:700;color:var(--ink);line-height:1.05;letter-spacing:-0.02em;position:relative;z-index:1;}
.h-title em{font-style:italic;color:var(--r);}
.h-sub{font-size:1.35rem;color:var(--ink3);margin-top:0.8rem;position:relative;z-index:1;}
.h-stats{display:flex;margin-top:0;position:relative;z-index:1;background:rgba(214,40,40,0.08);border-radius:1.2rem;overflow:hidden;border:1px solid rgba(214,40,40,0.12);}
.h-st{flex:1;padding:1.6rem 2rem;text-align:center;border-right:1px solid rgba(214,40,40,0.1);transition:background 0.2s;cursor:default;}
.h-st:last-child{border-right:none;}
.h-st:hover{background:rgba(214,40,40,0.12);}
.h-st .v{font-family:'Cormorant Garamond',serif;font-size:2.6rem;font-weight:700;color:var(--ink);line-height:1;}
.h-st .l{font-size:1.05rem;color:var(--ink3);text-transform:uppercase;letter-spacing:0.1em;margin-top:0.3rem;}

.h-side{display:flex;flex-direction:column;gap:1.6rem;}
.h-sc{background:var(--white);border-radius:1.6rem;padding:2.4rem;border:1px solid var(--line);box-shadow:var(--sh);flex:1;display:flex;align-items:center;gap:1.8rem;cursor:pointer;transition:all 0.25s var(--ease);position:relative;overflow:hidden;animation:fadeUp 0.5s var(--ease) both;}
.h-sc::after{content:'';position:absolute;right:0;top:0;bottom:0;width:3px;border-radius:0 1.6rem 1.6rem 0;opacity:0;transition:opacity 0.2s;}
.h-sc.c1::after{background:linear-gradient(180deg,var(--r),var(--rd));}
.h-sc.c2::after{background:linear-gradient(180deg,#1a9c4e,#0d6e33);}
.h-sc:hover{transform:translateX(-4px);box-shadow:var(--sh2);}
.h-sc:hover::after{opacity:1;}
.h-sic{width:5.2rem;height:5.2rem;border-radius:1.4rem;display:grid;place-items:center;font-size:2.2rem;flex-shrink:0;}
.c1 .h-sic{background:#fff0f0;color:var(--r);}
.c2 .h-sic{background:#edfff4;color:#1a9c4e;}
.h-sc-v{font-family:'Cormorant Garamond',serif;font-size:3.4rem;font-weight:700;color:var(--ink);line-height:1;}
.h-sc-l{font-size:1.2rem;color:var(--ink3);margin-top:0.2rem;font-weight:500;}
.h-sc-d{margin-left:auto;font-size:1.15rem;font-weight:700;padding:0.4rem 1rem;border-radius:99px;flex-shrink:0;}
.c1 .h-sc-d{background:rgba(214,40,40,0.08);color:var(--r);}
.c2 .h-sc-d{background:#edfff4;color:#1a9c4e;}

/* ── KPI ── */
.kpi-strip{display:grid;grid-template-columns:repeat(5,1fr);gap:1.6rem;margin-bottom:2.6rem;}
.kpi{background:var(--white);border-radius:1.6rem;padding:2rem 2rem 1.6rem;border:1px solid var(--line);box-shadow:var(--sh);cursor:pointer;transition:all 0.25s var(--ease);animation:fadeUp 0.5s var(--ease) both;position:relative;overflow:hidden;}
.kpi:nth-child(1){animation-delay:0.05s;}.kpi:nth-child(2){animation-delay:0.1s;}.kpi:nth-child(3){animation-delay:0.15s;}.kpi:nth-child(4){animation-delay:0.2s;}.kpi:nth-child(5){animation-delay:0.25s;}
.kpi:hover{transform:translateY(-5px);box-shadow:var(--sh2);}
.kpi-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.6rem;}
.kpi-ic{width:4.2rem;height:4.2rem;border-radius:1.1rem;display:grid;place-items:center;font-size:1.7rem;}
.kpi-badge{font-size:1.05rem;font-weight:700;padding:0.3rem 0.85rem;border-radius:99px;}
.kv{font-family:'Cormorant Garamond',serif;font-size:3.4rem;font-weight:700;color:var(--ink);line-height:1;}
.kl{font-size:1.15rem;color:var(--ink3);margin-top:0.4rem;font-weight:500;letter-spacing:0.03em;}
.kspark{height:3.6rem;margin-top:1.4rem;position:relative;}

@keyframes fadeUp{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:translateY(0)}}

/* ── CHARTS ── */
.ch-row{display:grid;grid-template-columns:2fr 1fr 1fr;gap:2rem;margin-bottom:2.4rem;}
.panel{background:var(--white);border-radius:1.8rem;padding:2.6rem;border:1px solid var(--line);box-shadow:var(--sh);animation:fadeUp 0.5s var(--ease) 0.3s both;}
.ph{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:2.4rem;}
.pt{font-family:'Cormorant Garamond',serif;font-size:2.2rem;font-weight:700;color:var(--ink);letter-spacing:-0.02em;}
.ps{font-size:1.25rem;color:var(--ink3);margin-top:0.3rem;}
.ptag{display:inline-flex;align-items:center;gap:0.6rem;background:var(--rp);color:var(--r);border:1px solid rgba(214,40,40,0.1);font-size:1.15rem;font-weight:600;padding:0.45rem 1.1rem;border-radius:99px;cursor:pointer;transition:all 0.2s;white-space:nowrap;}
.ptag:hover{background:var(--r);color:#fff;}
.chbox{height:22rem;position:relative;}
.chbox-sm{height:17rem;position:relative;}
.donut-mid{position:absolute;top:50%;left:50%;transform:translate(-50%,-58%);text-align:center;pointer-events:none;}
.donut-mid .dv{font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:700;color:var(--ink);}
.donut-mid .dl{font-size:1.05rem;color:var(--ink3);text-transform:uppercase;letter-spacing:0.1em;}
.leg{display:flex;flex-direction:column;gap:1rem;margin-top:1.8rem;}
.lg-r{display:flex;align-items:center;gap:1rem;}
.lg-sq{width:0.9rem;height:0.9rem;border-radius:0.3rem;flex-shrink:0;}
.lg-lbl{font-size:1.25rem;color:var(--ink3);width:8rem;}
.lg-bg{flex:1;height:0.45rem;background:var(--rp2);border-radius:99px;overflow:hidden;}
.lg-fill{height:100%;border-radius:99px;transition:width 1.4s var(--ease);}
.lg-pct{font-size:1.25rem;font-weight:700;color:var(--ink);width:3rem;text-align:right;}

/* ── BOTTOM ── */
.bot-row{display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:2rem;margin-bottom:2.4rem;}
.tpanel,.upanel,.mpanel{background:var(--white);border-radius:1.8rem;padding:2.6rem;border:1px solid var(--line);box-shadow:var(--sh);animation:fadeUp 0.5s var(--ease) 0.35s both;}
.tbl{width:100%;border-collapse:collapse;}
.tbl th{font-size:1rem;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--ink3);padding-bottom:1.4rem;text-align:left;border-bottom:1px solid rgba(214,40,40,0.07);}
.tbl td{font-size:1.35rem;color:var(--ink);padding:1.4rem 0;border-bottom:1px solid rgba(214,40,40,0.04);vertical-align:middle;}
.tbl tr:last-child td{border:none;}
.tbl tbody tr{transition:all 0.15s;cursor:pointer;}
.tbl tbody tr:hover td{background:var(--rp);}
.tbl tbody tr:hover td:first-child{border-radius:0.8rem 0 0 0.8rem;padding-left:0.8rem;}
.tbl tbody tr:hover td:last-child{border-radius:0 0.8rem 0.8rem 0;padding-right:0.8rem;}
.t-ic{width:4rem;height:4rem;border-radius:1rem;background:var(--rp);display:grid;place-items:center;font-size:1.8rem;}
.t-n{font-weight:600;font-size:1.35rem;}
.t-l{font-size:1.15rem;color:var(--ink3);margin-top:0.2rem;}
.t-p{font-family:'Cormorant Garamond',serif;font-size:1.8rem;font-weight:700;color:var(--r);}
.bdg{display:inline-block;font-size:1.05rem;font-weight:700;padding:0.3rem 1rem;border-radius:99px;}
.ba{background:#edfff4;color:#1a9c4e;}
.bp{background:#fff8ee;color:#e07b00;}
.u-row{display:flex;align-items:center;gap:1.4rem;padding:1.2rem 0.6rem;border-radius:1rem;border-bottom:1px solid rgba(214,40,40,0.04);transition:all 0.15s;cursor:pointer;}
.u-row:last-child{border:none;}
.u-row:hover{background:var(--rp);padding-left:1.2rem;}
.u-av{width:3.8rem;height:3.8rem;border-radius:50%;display:grid;place-items:center;font-family:'Cormorant Garamond',serif;font-size:1.7rem;font-weight:700;color:#fff;flex-shrink:0;}
.u-n{font-size:1.35rem;font-weight:600;color:var(--ink);}
.u-e{font-size:1.15rem;color:var(--ink3);}
.u-t{font-size:1.1rem;color:var(--ink3);margin-left:auto;white-space:nowrap;}
.u-s{font-size:1.05rem;font-weight:700;padding:0.25rem 0.8rem;border-radius:99px;margin-left:0.8rem;}
.m-row{padding:1.4rem 0.6rem;border-bottom:1px solid rgba(214,40,40,0.05);cursor:pointer;border-radius:1rem;transition:all 0.15s;}
.m-row:last-child{border:none;}
.m-row:hover{background:var(--rp);padding-left:1.2rem;}
.m-top{display:flex;align-items:center;gap:1rem;margin-bottom:0.5rem;}
.m-av{width:3rem;height:3rem;border-radius:50%;display:grid;place-items:center;font-size:1.3rem;font-weight:700;color:#fff;flex-shrink:0;font-family:'Cormorant Garamond',serif;}
.m-from{font-size:1.35rem;font-weight:700;color:var(--ink);}
.m-time{margin-left:auto;font-size:1.1rem;color:var(--ink3);}
.m-txt{font-size:1.25rem;color:var(--ink3);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.unread-dot{width:0.6rem;height:0.6rem;border-radius:50%;background:var(--r);flex-shrink:0;}

/* ── TIMELINE ── */
.atpanel{background:var(--white);border-radius:1.8rem;padding:2.6rem;border:1px solid var(--line);box-shadow:var(--sh);margin-bottom:2.4rem;animation:fadeUp 0.5s var(--ease) 0.5s both;}
.timeline{position:relative;padding-left:3.2rem;}
.timeline::before{content:'';position:absolute;left:1.2rem;top:0;bottom:0;width:1px;background:linear-gradient(180deg,var(--r),transparent);}
.tl-item{position:relative;margin-bottom:2.2rem;}
.tl-item:last-child{margin-bottom:0;}
.tl-dot{position:absolute;left:-2rem;top:0.4rem;width:1.4rem;height:1.4rem;border-radius:50%;border:2px solid var(--white);box-shadow:0 0 0 2px rgba(214,40,40,0.2);}
.tl-content{display:flex;align-items:flex-start;justify-content:space-between;gap:1.6rem;}
.tl-main{flex:1;}
.tl-title{font-size:1.35rem;font-weight:600;color:var(--ink);}
.tl-desc{font-size:1.2rem;color:var(--ink3);margin-top:0.2rem;}
.tl-time{font-size:1.1rem;color:var(--ink3);white-space:nowrap;flex-shrink:0;}
.tl-tag{display:inline-block;font-size:1.05rem;font-weight:700;padding:0.2rem 0.8rem;border-radius:99px;margin-top:0.4rem;}

/* ── FOOTER ── */
.dash-foot{border-top:1px solid var(--line);padding:1.8rem 3.2rem;background:var(--white);display:flex;align-items:center;justify-content:space-between;}
.fl{font-size:1.25rem;color:var(--ink3);}
.fl b{color:var(--r);}
.fr{display:flex;gap:1rem;}
.fbtn{display:flex;align-items:center;gap:0.8rem;padding:0.9rem 1.8rem;border-radius:99px;font-size:1.3rem;font-weight:600;cursor:pointer;transition:all 0.2s;border:none;}
.fbtn.p{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;box-shadow:0 4px 16px rgba(214,40,40,0.22);}
.fbtn.p:hover{transform:translateY(-2px);}
.fbtn.s{border:1.5px solid rgba(214,40,40,0.15);color:var(--r);background:transparent;}
.fbtn.s:hover{background:var(--rp);}
</style>
</head>
<body class="dash-body">

<?php include '../components/admin_header.php'; ?>

<!-- TOPBAR -->
<div class="dash-tb">
  <div class="tb-path">
    <span class="seg">EstateAdmin</span>
    <span class="sep">/</span>
    <span class="cur">Dashboard</span>
  </div>
  <div class="tb-r">
    <div class="tb-dpill"><i class="fas fa-calendar-alt"></i> <?= date('D, j M Y'); ?></div>
    <form action="listings.php" method="GET" style="display:flex;align-items:center;">
    <div class="tb-srch">
      <i class="fas fa-search"></i>
      <input name="q" placeholder="Search properties, users..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
    </div>
    </form>
    <a href="admins.php" class="tb-ic"><i class="fas fa-user-shield"></i></a>
    <a href="messages.php" class="tb-ic"><i class="fas fa-bell"></i><?php if($total_messages > 0): ?><div class="pip"></div><?php endif; ?></a>
    <a href="update.php" class="tb-ic"><i class="fas fa-cog"></i></a>
  </div>
</div>

<div class="dash-wrap">

  <!-- GREETING -->
  <div class="pg-head">
    <div>
      <div class="pg-greeting">Good morning, <em><?= isset($fetch_admin['name']) ? $fetch_admin['name'] : 'Admin'; ?>.</em></div>
      <div class="pg-sub">Here's everything happening across your platform today.</div>
    </div>
    <div class="pg-tabs">
      <div class="pg-tab on">Overview</div>
      <div class="pg-tab">Analytics</div>
      <div class="pg-tab">Reports</div>
    </div>
  </div>

  <!-- HERO -->
  <div class="hero">
    <div class="h-main">
      <div class="hr3"></div>
      <div class="glow"></div>
      <div>
        <div class="h-tag"><div class="p-dot"></div> Live · Real-time Data</div>
        <div class="h-title">Your Real Estate<br>Empire, <em><?= isset($fetch_admin['name']) ? $fetch_admin['name'] : 'Admin'; ?>.</em></div>
        <div class="h-sub">Full platform visibility — properties, users, messages, revenue.</div>
      </div>
      <div class="h-stats">
        <div class="h-st"><div class="v"><?= $total_listings; ?></div><div class="l">Properties</div></div>
        <div class="h-st"><div class="v"><?= $total_users; ?></div><div class="l">Users</div></div>
        <div class="h-st"><div class="v"><?= $total_admins; ?></div><div class="l">Admins</div></div>
        <div class="h-st"><div class="v"><?= $total_messages; ?></div><div class="l">Messages</div></div>
      </div>
    </div>
    <div class="h-side">
      <div class="h-sc c1">
        <div class="h-sic"><i class="fas fa-building"></i></div>
        <div><div class="h-sc-v"><?= $total_listings; ?></div><div class="h-sc-l">Total Listings</div></div>
        <i class="fas fa-arrow-right" style="color:var(--ink3);font-size:1.2rem;margin-left:auto;"></i>
      </div>
      <div class="h-sc c2">
        <div class="h-sic"><i class="fas fa-users"></i></div>
        <div><div class="h-sc-v"><?= $total_users; ?></div><div class="h-sc-l">Total Users</div></div>
        <i class="fas fa-arrow-right" style="color:var(--ink3);font-size:1.2rem;margin-left:auto;"></i>
      </div>
    </div>
  </div>

  <!-- KPI STRIP -->
  <div class="kpi-strip">
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:#fff0f0;color:var(--r);"><i class="fas fa-home"></i></div>
        <div class="kpi-badge" style="background:rgba(214,40,40,0.08);color:var(--r);">Total</div>
      </div>
      <div class="kv"><?= $total_listings; ?></div><div class="kl">Properties</div>
      <div class="kspark"><canvas id="sp1"></canvas></div>
    </div>
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:#fff8ee;color:#e07b00;"><i class="fas fa-users"></i></div>
        <div class="kpi-badge" style="background:#fff8ee;color:#e07b00;">Total</div>
      </div>
      <div class="kv"><?= $total_users; ?></div><div class="kl">Total Users</div>
      <div class="kspark"><canvas id="sp2"></canvas></div>
    </div>
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:#edfff4;color:#1a9c4e;"><i class="fas fa-user-shield"></i></div>
        <div class="kpi-badge" style="background:#edfff4;color:#1a9c4e;">Total</div>
      </div>
      <div class="kv"><?= $total_admins; ?></div><div class="kl">Total Admins</div>
      <div class="kspark"><canvas id="sp3"></canvas></div>
    </div>
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:#eef2ff;color:#3a5bd9;"><i class="fas fa-envelope"></i></div>
        <div class="kpi-badge" style="background:#eef2ff;color:#3a5bd9;"><?= $total_messages > 0 ? 'New' : 'None'; ?></div>
      </div>
      <div class="kv"><?= $total_messages; ?></div><div class="kl">Messages</div>
      <div class="kspark"><canvas id="sp4"></canvas></div>
    </div>
    <div class="kpi">
      <div class="kpi-top">
        <div class="kpi-ic" style="background:#fdf4ff;color:#9333ea;"><i class="fas fa-calendar-check"></i></div>
        <div class="kpi-badge" style="background:#fff8ee;color:#e07b00;"><?= $total_pending > 0 ? $total_pending.' Pending' : 'None'; ?></div>
      </div>
      <div class="kv"><?= $total_requests; ?></div><div class="kl">Site Visits Booked</div>
      <div class="kspark"><canvas id="sp5"></canvas></div>
    </div>
  </div>

  <!-- RECENT BOOKINGS TABLE -->
  <div class="panel" style="margin-bottom:2.4rem;">
    <div class="ph">
      <div><div class="pt">Recent Visit Bookings</div><div class="ps">Latest site visit requests from users</div></div>
      <a href="requests.php" style="font-size:1.2rem;color:var(--r);font-weight:700;text-decoration:none;"><i class="fas fa-arrow-right"></i> View All</a>
    </div>
    <?php
    $bk_rows = $recent_requests->fetchAll(PDO::FETCH_ASSOC);
    if(empty($bk_rows)):
    ?>
    <div style="text-align:center;padding:3rem;color:var(--ink3);font-size:1.3rem;"><i class="fas fa-calendar-times" style="display:block;font-size:3rem;margin-bottom:1rem;opacity:.3;"></i> No bookings yet.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:1.25rem;">
      <thead><tr style="border-bottom:1px solid var(--line);">
        <th style="padding:1rem 1.4rem;text-align:left;font-size:1rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--ink3);">Visitor</th>
        <th style="padding:1rem 1.4rem;text-align:left;font-size:1rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--ink3);">Property</th>
        <th style="padding:1rem 1.4rem;text-align:left;font-size:1rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--ink3);">Visit Date</th>
        <th style="padding:1rem 1.4rem;text-align:left;font-size:1rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--ink3);">Slot</th>
        <th style="padding:1rem 1.4rem;text-align:left;font-size:1rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--ink3);">Status</th>
      </tr></thead>
      <tbody>
      <?php foreach($bk_rows as $bk):
        $st = $bk['status'] ?? 'pending';
        $st_colors = ['pending'=>'#b07000::#fff8ee','confirmed'=>'#1a7a4e::#edfff4','cancelled'=>'#c0392b::#fff5f5'];
        $stc = isset($st_colors[$st]) ? explode('::',$st_colors[$st]) : ['#9a6565','#fdf1f1'];
      ?>
      <tr style="border-bottom:1px solid var(--line);transition:background .15s;" onmouseover="this.style.background='var(--rp)'" onmouseout="this.style.background=''">
        <td style="padding:1.2rem 1.4rem;"><div style="font-weight:700;color:var(--ink);"><?= htmlspecialchars($bk['user_name'] ?: 'Unknown') ?></div><div style="font-size:1.1rem;color:var(--ink3);"><?= htmlspecialchars($bk['user_phone'] ?: '—') ?></div></td>
        <td style="padding:1.2rem 1.4rem;"><div style="font-weight:600;color:var(--ink);max-width:20rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($bk['property_name'] ?: 'General Visit') ?></div><div style="font-size:1.1rem;color:var(--ink3);"><?= htmlspecialchars($bk['address'] ?: '—') ?></div></td>
        <td style="padding:1.2rem 1.4rem;font-weight:600;color:var(--ink);"><?= !empty($bk['visit_date']) ? date('d M Y', strtotime($bk['visit_date'])) : date('d M Y', strtotime($bk['date'])) ?></td>
        <td style="padding:1.2rem 1.4rem;color:var(--ink3);"><?= htmlspecialchars($bk['time_slot'] ?: '—') ?></td>
        <td style="padding:1.2rem 1.4rem;"><span style="padding:.4rem 1.1rem;border-radius:99px;font-size:1.05rem;font-weight:700;background:<?= $stc[1] ?>;color:<?= $stc[0] ?>"><?= ucfirst($st) ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- CHARTS -->
  <div class="ch-row">
    <div class="panel">
      <div class="ph">
        <div><div class="pt">Platform Growth</div><div class="ps">Monthly overview</div></div>
        <div class="ptag"><i class="fas fa-calendar"></i> 2025</div>
      </div>
      <div class="chbox"><canvas id="lineChart"></canvas></div>
    </div>
    <div class="panel">
      <div class="ph"><div><div class="pt">By City</div><div class="ps">Listings per location</div></div></div>
      <div class="chbox"><canvas id="barChart"></canvas></div>
    </div>
    <div class="panel">
      <div class="ph"><div><div class="pt">Mix</div><div class="ps">Property type split</div></div></div>
      <div class="chbox-sm" style="position:relative;">
        <canvas id="donutChart"></canvas>
        <div class="donut-mid"><div class="dv"><?= $total_listings; ?></div><div class="dl">Total</div></div>
      </div>
      <div class="leg">
        <?php
          $donut_total = max(1, $total_listings);
          $types = ['Apartment'=>'#d62828','Villa'=>'#f7a400','Plot'=>'#1a9c4e','Commercial'=>'#3a5bd9'];
          $type_keys = ['apartment','villa','plot','commercial'];
          $type_counts_arr = [];
          foreach($type_keys as $tk){
            $qc=$conn->prepare("SELECT COUNT(*) FROM property WHERE type=?");
            $qc->execute([$tk]);
            $type_counts_arr[$tk]=$qc->fetchColumn();
          }
          $labels=['Apartment','Villa','Plot','Commercial'];
          $tkeys=['apartment','villa','plot','commercial'];
          $colors_d=['#d62828','#f7a400','#1a9c4e','#3a5bd9'];
          foreach($labels as $li=>$lb):
            $cnt=$type_counts_arr[$tkeys[$li]];
            $pct=$donut_total>0?round($cnt/$donut_total*100):0;
        ?>
        <div class="lg-r"><div class="lg-sq" style="background:<?= $colors_d[$li]; ?>"></div><div class="lg-lbl"><?= $lb ?></div><div class="lg-bg"><div class="lg-fill" style="width:<?= $pct ?>%;background:<?= $colors_d[$li]; ?>"></div></div><div class="lg-pct"><?= $pct ?>%</div></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- BOTTOM ROW -->
  <div class="bot-row">
    <div class="tpanel">
      <div class="ph">
        <div><div class="pt">Recent Listings</div><div class="ps">Latest on platform</div></div>
        <a href="listings.php" class="ptag">View All <i class="fas fa-arrow-right"></i></a>
      </div>
      <table class="tbl">
        <thead><tr><th>Property</th><th>Price</th><th>Status</th></tr></thead>
        <tbody>
          <?php $recent_listings->execute(); while($p = $recent_listings->fetch(PDO::FETCH_ASSOC)): ?>
          <tr>
            <td><div style="display:flex;align-items:center;gap:1.2rem;">
              <div class="t-ic">🏠</div>
              <div><div class="t-n"><?= htmlspecialchars($p['property_name'] ?? 'Property'); ?></div>
              <div class="t-l"><i class="fas fa-map-marker-alt" style="color:var(--r);font-size:1rem;"></i> <?= htmlspecialchars($p['address'] ?? '—'); ?></div></div>
            </div></td>
            <td><div class="t-p">₹<?= htmlspecialchars($p['price'] ?? '—'); ?></div></td>
            <td><span class="bdg ba">Active</span></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <div class="upanel">
      <div class="ph">
        <div><div class="pt">Recent Users</div><div class="ps">Latest signups</div></div>
        <a href="users.php" class="ptag">View All</a>
      </div>
      <?php
      $colors = ['linear-gradient(135deg,#d62828,#a01818)','linear-gradient(135deg,#3a5bd9,#1a3a9c)','linear-gradient(135deg,#1a9c4e,#0d6e33)','linear-gradient(135deg,#f7a400,#c07800)'];
      $i = 0;
      $recent_users->execute();
      while($u = $recent_users->fetch(PDO::FETCH_ASSOC)):
      ?>
      <div class="u-row">
        <div class="u-av" style="background:<?= $colors[$i % 4]; ?>"><?= strtoupper(substr($u['name'], 0, 1)); ?></div>
        <div><div class="u-n"><?= htmlspecialchars($u['name']); ?></div><div class="u-e"><?= htmlspecialchars($u['email']); ?></div></div>
        <div class="u-t"><?= date('d M', strtotime($u['date'] ?? date('Y-m-d'))); ?></div>
        <span class="u-s ba">Active</span>
      </div>
      <?php $i++; endwhile; ?>
    </div>

    <div class="mpanel">
      <div class="ph">
        <div><div class="pt">Messages</div><div class="ps"><?= $total_messages; ?> unread</div></div>
        <a href="messages.php" class="ptag">View All</a>
      </div>
      <?php
      $msg_colors = ['linear-gradient(135deg,var(--r),var(--rd))','linear-gradient(135deg,#3a5bd9,#1a3a9c)','linear-gradient(135deg,#1a9c4e,#0d6e33)'];
      $j = 0;
      $recent_messages->execute();
      while($m = $recent_messages->fetch(PDO::FETCH_ASSOC)):
      ?>
      <div class="m-row">
        <div class="m-top">
          <div class="m-av" style="background:<?= $msg_colors[$j % 3]; ?>"><?= strtoupper(substr($m['name'] ?? 'U', 0, 1)); ?></div>
          <div class="m-from"><?= htmlspecialchars($m['name'] ?? 'User'); ?></div>
          <?php if(!($m['seen'] ?? 1)): ?><div class="unread-dot"></div><?php endif; ?>
          <div class="m-time"><?= date('d M', strtotime($m['date'] ?? 'now')); ?></div>
        </div>
        <div class="m-txt"><?= htmlspecialchars(substr($m['message'] ?? $m['msg'] ?? '—', 0, 80)); ?>...</div>
      </div>
      <?php $j++; endwhile; ?>
    </div>
  </div>

  <!-- TIMELINE -->
  <div class="atpanel">
    <div class="ph" style="margin-bottom:2.4rem;">
      <div><div class="pt">Activity Timeline</div><div class="ps">Recent actions across the platform</div></div>
      <a href="listings.php" class="ptag">View Listings</a>
    </div>
    <div class="timeline">
      <div class="tl-item">
        <div class="tl-dot" style="background:var(--r);"></div>
        <div class="tl-content">
          <div class="tl-main">
            <div class="tl-title">Platform has <?= $total_listings; ?> total properties listed</div>
            <div class="tl-desc">Across all categories · Mumbai, Pune, Thane & more</div>
            <span class="tl-tag" style="background:rgba(214,40,40,0.08);color:var(--r);">Listings</span>
          </div>
          <div class="tl-time">Live</div>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-dot" style="background:#1a9c4e;"></div>
        <div class="tl-content">
          <div class="tl-main">
            <div class="tl-title"><?= $total_users; ?> users registered on the platform</div>
            <div class="tl-desc">Total registered user base</div>
            <span class="tl-tag" style="background:#edfff4;color:#1a9c4e;">Users</span>
          </div>
          <div class="tl-time">Live</div>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-dot" style="background:#3a5bd9;"></div>
        <div class="tl-content">
          <div class="tl-main">
            <div class="tl-title"><?= $total_messages; ?> unread messages awaiting response</div>
            <div class="tl-desc">Check messages section to respond</div>
            <span class="tl-tag" style="background:#eef2ff;color:#3a5bd9;">Messages</span>
          </div>
          <div class="tl-time">Live</div>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-dot" style="background:#9333ea;"></div>
        <div class="tl-content">
          <div class="tl-main">
            <div class="tl-title"><?= $total_admins; ?> admin account(s) managing the platform</div>
            <div class="tl-desc">Super Administrator access</div>
            <span class="tl-tag" style="background:#fdf4ff;color:#9333ea;">System</span>
          </div>
          <div class="tl-time">Live</div>
        </div>
      </div>
    </div>
  </div>

</div><!-- end dash-wrap -->

<!-- FOOTER -->
<div class="dash-foot">
  <div class="fl">EstateAdmin <b>v2.0</b> · Real Estate Management · © <?= date('Y'); ?></div>
  <div class="fr">
    <a href="listings.php" class="fbtn s"><i class="fas fa-list"></i> Listings</a>
    <a href="users.php" class="fbtn s"><i class="fas fa-users"></i> Users</a>
    <a href="messages.php" class="fbtn p"><i class="fas fa-envelope"></i> Messages</a>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php'; ?>

<script>
/* TAB SWITCHER */
document.querySelectorAll('.pg-tab').forEach(t=>{
  t.addEventListener('click',()=>{
    document.querySelectorAll('.pg-tab').forEach(x=>x.classList.remove('on'));
    t.classList.add('on');
  });
});

/* SPARKLINES */
function spark(id,data,color){
  const c=document.getElementById(id);if(!c)return;
  new Chart(c,{type:'line',data:{labels:data.map((_,i)=>i),datasets:[{data,borderColor:color,borderWidth:2,pointRadius:0,tension:0.4,fill:true,backgroundColor:color.replace('rgb(','rgba(').replace(')',',0.1)')}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{enabled:false}},scales:{x:{display:false},y:{display:false}},animation:{duration:1400}}});
}
spark('sp1',[12,8,14,10,18,16,20,<?= $total_listings; ?>],'rgb(214,40,40)');
spark('sp2',[80,95,105,98,118,124,130,<?= $total_users; ?>],'rgb(247,164,0)');
spark('sp3',[1,1,1,1,1,1,1,<?= $total_admins; ?>],'rgb(26,156,78)');
spark('sp4',[2,5,3,8,4,7,3,<?= $total_messages; ?>],'rgb(58,91,217)');
spark('sp5',[50,80,70,120,100,140,130,<?= $total_listings + $total_users; ?>],'rgb(147,51,234)');

/* LINE CHART */
new Chart(document.getElementById('lineChart'),{type:'line',data:{labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],datasets:[{label:'Users',data:[8,14,10,22,18,30,25,38,32,45,40,<?= $total_users; ?>],borderColor:'#d62828',backgroundColor:(ctx)=>{const g=ctx.chart.ctx.createLinearGradient(0,0,0,280);g.addColorStop(0,'rgba(214,40,40,0.15)');g.addColorStop(1,'rgba(214,40,40,0)');return g;},borderWidth:2.5,pointBackgroundColor:'#fff',pointBorderColor:'#d62828',pointBorderWidth:2,pointRadius:4,pointHoverRadius:7,tension:0.45,fill:true},{label:'Listings',data:[2,4,3,6,5,8,7,10,9,12,10,<?= $total_listings; ?>],borderColor:'rgba(214,40,40,0.25)',borderDash:[5,4],borderWidth:1.5,pointRadius:0,tension:0.45,fill:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'top',labels:{color:'#9a6565',font:{size:11},boxWidth:14,padding:16}},tooltip:{backgroundColor:'#0d0202',bodyColor:'#fff',padding:14,cornerRadius:12}},scales:{x:{grid:{color:'rgba(214,40,40,0.04)',drawBorder:false},ticks:{color:'#9a6565',font:{size:11}}},y:{grid:{color:'rgba(214,40,40,0.04)',drawBorder:false},ticks:{color:'#9a6565',font:{size:11}},beginAtZero:true}}}});

/* BAR CHART */
new Chart(document.getElementById('barChart'),{type:'bar',data:{labels:['Mumbai','Pune','Thane','Nashik','Nagpur'],datasets:[{data:[10,6,4,3,1],backgroundColor:['rgba(214,40,40,0.9)','rgba(214,40,40,0.7)','rgba(214,40,40,0.5)','rgba(214,40,40,0.35)','rgba(214,40,40,0.2)'],borderRadius:8,borderSkipped:false,hoverBackgroundColor:'#d62828'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{backgroundColor:'#0d0202',bodyColor:'#fff',padding:12,cornerRadius:10}},scales:{x:{grid:{display:false},ticks:{color:'#9a6565',font:{size:11}}},y:{grid:{color:'rgba(214,40,40,0.04)',drawBorder:false},ticks:{color:'#9a6565',font:{size:11}},beginAtZero:true}}}});

/* DONUT */
new Chart(document.getElementById('donutChart'),{type:'doughnut',data:{labels:['Apartment','Villa','Plot','Commercial'],datasets:[{data:[45,25,20,10],backgroundColor:['#d62828','#f7a400','#1a9c4e','#3a5bd9'],borderWidth:0,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{backgroundColor:'#0d0202',bodyColor:'#fff',padding:12,cornerRadius:10}},cutout:'72%'}});

/* ANIMATE BARS */
window.addEventListener('load',()=>{
  document.querySelectorAll('.lg-fill').forEach(b=>{const w=b.style.width;b.style.width='0';requestAnimationFrame(()=>requestAnimationFrame(()=>{b.style.width=w;}));});
});
</script>
</body>
</html>
