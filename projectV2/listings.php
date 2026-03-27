<?php  

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

include 'components/save_send.php';

// Fetch logged-in user info for nav
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Listings — MyEstate</title>
<meta name="description" content="Browse all verified property listings across Mumbai & Pune. Apartments, villas, plots and commercial spaces at the best prices.">
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
a{text-decoration:none;}

/* ── REVEAL ── */
.reveal{opacity:0;transform:translateY(28px);transition:opacity .7s var(--ease),transform .7s var(--ease);}
.reveal.in{opacity:1;transform:translateY(0);}

/* ── NAV ── */
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
.nav-user:hover .nav-drop-menu{display:block;}
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

/* ── PAGE HERO ── */
.page-hero{padding-top:var(--nav-h);background:linear-gradient(145deg,#fff9f9 0%,#fdf1f1 45%,#fae8e8 100%);position:relative;overflow:hidden;}
.page-hero::before{content:'';position:absolute;top:-18rem;right:-12rem;width:50rem;height:50rem;border-radius:50%;border:1px solid rgba(214,40,40,.07);pointer-events:none;}
.page-hero::after{content:'';position:absolute;top:-8rem;right:2rem;width:30rem;height:30rem;border-radius:50%;border:1px solid rgba(214,40,40,.05);pointer-events:none;}
.page-hero-inner{max-width:130rem;margin:0 auto;padding:5rem 6% 4rem;display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:2rem;}
.eyebrow{display:inline-flex;align-items:center;gap:.5rem;font-size:.9rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--r);background:var(--rp);padding:.35rem 1rem;border-radius:99px;border:1px solid rgba(214,40,40,.12);width:fit-content;margin-bottom:1.2rem;}
.eyebrow::before{content:'';width:.4rem;height:.4rem;border-radius:50%;background:var(--r);animation:blink 2s infinite;flex-shrink:0;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
.page-title{font-family:'Cormorant Garamond',serif;font-size:clamp(4rem,5vw,6.5rem);font-weight:700;color:var(--ink);letter-spacing:-.03em;line-height:.92;}
.page-title em{font-style:italic;color:var(--r);}
.page-sub{font-size:1.4rem;color:var(--ink3);line-height:1.7;max-width:48rem;margin-top:.8rem;}
.page-hero-stats{display:flex;gap:2rem;flex-wrap:wrap;}
.phs{display:flex;align-items:center;gap:.8rem;background:var(--white);border:1.5px solid var(--line);border-radius:99px;padding:.8rem 2rem;}
.phs-icon{width:3.2rem;height:3.2rem;border-radius:50%;background:var(--rp);display:grid;place-items:center;font-size:1.3rem;color:var(--r);}
.phs-n{font-family:'Cormorant Garamond',serif;font-size:2.4rem;font-weight:700;color:var(--ink);line-height:1;}
.phs-l{font-size:1.1rem;color:var(--ink3);}

/* ── LISTINGS GRID ── */
.listings-sec{max-width:130rem;margin:0 auto;padding:4rem 6% 8rem;}
.listings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(36rem,1fr));gap:2.4rem;}

/* ── PROPERTY CARD ── */
.pc{border-radius:2.4rem;overflow:hidden;border:1.5px solid var(--line);background:var(--white);transition:all .4s var(--ease);display:flex;flex-direction:column;}
.pc:hover{transform:translateY(-7px);box-shadow:var(--sh2);}
.pc-img{position:relative;height:24rem;overflow:hidden;}
.pc-img img{width:100%;height:100%;object-fit:cover;transition:transform .8s var(--ease);}
.pc:hover .pc-img img{transform:scale(1.06);}
.pc-ov{position:absolute;inset:0;background:linear-gradient(to top,rgba(15,2,2,.6) 0%,transparent 55%);pointer-events:none;}
.pc-badge{position:absolute;top:1.3rem;left:1.3rem;background:rgba(255,255,255,.93);backdrop-filter:blur(12px);padding:.4rem 1rem;border-radius:99px;font-size:1rem;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:.4rem;}
.pc-badge i{color:var(--r);}
.pc-save{position:absolute;top:1.3rem;right:1.3rem;width:3.6rem;height:3.6rem;border-radius:50%;background:rgba(255,255,255,.93);backdrop-filter:blur(12px);border:none;cursor:pointer;display:grid;place-items:center;font-size:1.4rem;color:var(--ink3);transition:all .22s;z-index:2;}
.pc-save:hover,.pc-save.saved{color:var(--r);}
.pc-price-tag{position:absolute;bottom:1.3rem;right:1.3rem;background:rgba(15,2,2,.78);backdrop-filter:blur(12px);padding:.55rem 1.3rem;border-radius:99px;}
.pc-price-tag span{font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:700;color:#fff;}
.pc-price-tag i{color:rgba(255,255,255,.6);margin-right:.3rem;font-size:1.2rem;}
.pc-body{padding:2.2rem 2.4rem;display:flex;flex-direction:column;flex:1;}
.pc-type{font-size:.95rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--r);margin-bottom:.4rem;}
.pc-name{font-family:'Cormorant Garamond',serif;font-size:2.2rem;font-weight:700;color:var(--ink);margin-bottom:.4rem;line-height:1.15;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.pc-addr{font-size:1.2rem;color:var(--ink3);display:flex;align-items:center;gap:.4rem;margin-bottom:1.4rem;}
.pc-addr i{color:var(--r);font-size:.95rem;}
.pc-pills{display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1.6rem;}
.pc-pill{display:flex;align-items:center;gap:.4rem;background:var(--rp);border:1px solid rgba(214,40,40,.1);color:var(--ink2);padding:.35rem .9rem;border-radius:99px;font-size:1rem;font-weight:600;}
.pc-pill i{font-size:.85rem;color:var(--r);}
.pc-admin{display:flex;align-items:center;gap:1rem;padding-top:1.2rem;border-top:1px solid var(--line);margin-bottom:1.4rem;}
.pc-admin-av{width:3.4rem;height:3.4rem;border-radius:50%;background:linear-gradient(135deg,var(--rp2),var(--rp3));display:grid;place-items:center;font-size:1.3rem;font-weight:800;color:var(--r);flex-shrink:0;}
.pc-admin-name{font-size:1.2rem;font-weight:600;color:var(--ink);}
.pc-admin-date{font-size:1.05rem;color:var(--ink3);}
.pc-acts{display:flex;gap:.8rem;flex-wrap:wrap;margin-top:auto;padding-top:1.2rem;}
.pca{padding:.7rem 1.4rem;border-radius:99px;font-size:1.1rem;font-weight:700;cursor:pointer;font-family:'Outfit',sans-serif;transition:all .22s;border:none;display:flex;align-items:center;gap:.5rem;}
.pca.v{background:var(--r);color:#fff;box-shadow:0 4px 14px rgba(214,40,40,.3);}
.pca.v:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(214,40,40,.45);}
.pca.e{background:var(--rp);color:var(--r);border:1.5px solid rgba(214,40,40,.18);}
.pca.e:hover{background:var(--rp2);}

/* ── EMPTY STATE ── */
.empty-state{text-align:center;padding:8rem 2rem;grid-column:1/-1;}
.empty-icon{width:10rem;height:10rem;border-radius:50%;background:var(--rp);display:grid;place-items:center;font-size:4rem;color:var(--r);margin:0 auto 2.5rem;border:2px dashed rgba(214,40,40,.2);}
.empty-state h3{font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:700;color:var(--ink);margin-bottom:.8rem;}
.empty-state p{font-size:1.4rem;color:var(--ink3);margin-bottom:2.5rem;}
.empty-btn{display:inline-flex;align-items:center;gap:.8rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;padding:1.2rem 3rem;font-size:1.3rem;font-weight:800;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 8px 24px rgba(214,40,40,.3);transition:all .25s;}
.empty-btn:hover{transform:translateY(-3px);box-shadow:0 14px 36px rgba(214,40,40,.45);}

/* ── FOOTER ── */
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

/* ── RESPONSIVE ── */
@media(max-width:1100px){
  .foot-grid{grid-template-columns:1fr 1fr;gap:3rem;}
  .listings-grid{grid-template-columns:repeat(auto-fill,minmax(32rem,1fr));}
}
@media(max-width:768px){
  .nav-center{display:none;}
  .page-hero-inner{padding:3.5rem 5% 3rem;}
  .page-hero-stats{display:none;}
  .listings-grid{grid-template-columns:1fr;}
  .foot-grid{grid-template-columns:1fr;}
  .foot-bot{flex-direction:column;}
}
@media(max-width:480px){
  .pc-img{height:20rem;}
  .listings-sec{padding:3rem 4% 6rem;}
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
    <a href="search.php">Search</a>
    <a href="about.php">About</a>
    <a href="contact.php">Contact</a>
  </div>
  <div class="nav-right">
    <?php if($user_id != ''){ ?>
    <a href="saved.php" class="nav-icon"><i class="fas fa-heart"></i><?php if($nav_saved_count > 0){ ?><span class="nav-badge"><?= $nav_saved_count; ?></span><?php } ?></a>
    <div class="nav-user">
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
        <a href="components/user_logout.php" class="nd-item nd-danger" onclick="return confirm('Logout from this website?');"><i class="fas fa-sign-out-alt"></i>Logout</a>
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

<!-- PAGE HERO -->
<?php
   $count_all = $conn->prepare("SELECT COUNT(*) as total FROM `property`");
   $count_all->execute();
   $total_listings = $count_all->fetch(PDO::FETCH_ASSOC)['total'];

   $count_sale = $conn->prepare("SELECT COUNT(*) as total FROM `property` WHERE offer = 'sale' OR offer = 'resale'");
   $count_sale->execute();
   $total_sale = $count_sale->fetch(PDO::FETCH_ASSOC)['total'];

   $count_rent = $conn->prepare("SELECT COUNT(*) as total FROM `property` WHERE offer = 'rent'");
   $count_rent->execute();
   $total_rent = $count_rent->fetch(PDO::FETCH_ASSOC)['total'];
?>
<section class="page-hero">
  <div class="page-hero-inner">
    <div>
      <div class="eyebrow">Browse Properties</div>
      <h1 class="page-title">All <em>Listings</em></h1>
      <p class="page-sub">Explore <?= $total_listings; ?> verified properties across Mumbai & Pune. Every listing is real, every price is transparent.</p>
    </div>
    <div class="page-hero-stats">
      <div class="phs"><div class="phs-icon"><i class="fas fa-building"></i></div><div><div class="phs-n"><?= $total_listings; ?></div><div class="phs-l">Total Listings</div></div></div>
      <div class="phs"><div class="phs-icon"><i class="fas fa-tag"></i></div><div><div class="phs-n"><?= $total_sale; ?></div><div class="phs-l">For Sale</div></div></div>
      <div class="phs"><div class="phs-icon"><i class="fas fa-key"></i></div><div><div class="phs-n"><?= $total_rent; ?></div><div class="phs-l">For Rent</div></div></div>
    </div>
  </div>
</section>

<!-- LISTINGS -->
<section class="listings-sec">
  <div class="listings-grid">
    <?php
       // Handle search from home page
       $search_where = [];
       $search_params = [];

       if(isset($_GET['location']) && !empty($_GET['location'])){
          $search_where[] = "address LIKE ?";
          $search_params[] = '%' . $_GET['location'] . '%';
       }
       if(isset($_GET['type']) && !empty($_GET['type'])){
          $search_where[] = "type LIKE ?";
          $search_params[] = '%' . $_GET['type'] . '%';
       }
       if(isset($_GET['budget']) && !empty($_GET['budget'])){
          $budget_parts = explode('-', $_GET['budget']);
          if(count($budget_parts) == 2){
             $search_where[] = "price BETWEEN ? AND ?";
             $search_params[] = $budget_parts[0];
             $search_params[] = $budget_parts[1];
          }
       }

       if(!empty($search_where)){
          $sql = "SELECT * FROM `property` WHERE " . implode(' AND ', $search_where) . " ORDER BY date DESC";
          $select_properties = $conn->prepare($sql);
          $select_properties->execute($search_params);
       }else{
          $select_properties = $conn->prepare("SELECT * FROM `property` ORDER BY date DESC");
          $select_properties->execute();
       }
       if($select_properties->rowCount() > 0){
          $card_delay = 0;
          while($fetch_property = $select_properties->fetch(PDO::FETCH_ASSOC)){

          $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
          $select_user->execute([$fetch_property['user_id']]);
          $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

          // Count images
          $total_images = 1;
          if(!empty($fetch_property['image_02'])) $total_images++;
          if(!empty($fetch_property['image_03'])) $total_images++;
          if(!empty($fetch_property['image_04'])) $total_images++;
          if(!empty($fetch_property['image_05'])) $total_images++;

          $select_saved = $conn->prepare("SELECT * FROM `saved` WHERE property_id = ? AND user_id = ?");
          $select_saved->execute([$fetch_property['id'], $user_id]);
          $is_saved = $select_saved->rowCount() > 0;

          // Format price
          $price_raw = $fetch_property['price'];
          if($price_raw >= 10000000){
             $price_fmt = '₹' . round($price_raw / 10000000, 2) . ' Cr';
          } elseif($price_raw >= 100000){
             $price_fmt = '₹' . round($price_raw / 100000, 2) . ' L';
          } elseif($price_raw >= 1000){
             $price_fmt = '₹' . round($price_raw / 1000, 1) . 'K';
          } else{
             $price_fmt = '₹' . number_format($price_raw);
          }

          $delay_style = 'transition-delay:' . ($card_delay * 0.06) . 's';
          $card_delay++;
          if($card_delay > 8) $card_delay = 0; // reset delay after 8 cards
    ?>
    <div class="pc reveal" style="<?= $delay_style; ?>">
      <form action="" method="POST" style="display:contents;">
        <input type="hidden" name="property_id" value="<?= $fetch_property['id']; ?>">
        <div class="pc-img">
          <img src="uploaded_files/<?= $fetch_property['image_01']; ?>" alt="<?= htmlspecialchars($fetch_property['property_name']); ?>">
          <div class="pc-ov"></div>
          <div class="pc-badge"><i class="far fa-image"></i> <?= $total_images; ?> Photos</div>
          <button type="submit" name="save" class="pc-save <?= $is_saved ? 'saved' : ''; ?>">
            <i class="<?= $is_saved ? 'fas' : 'far'; ?> fa-heart"></i>
          </button>
          <div class="pc-price-tag"><span><?= $price_fmt; ?></span></div>
        </div>
        <div class="pc-body">
          <div class="pc-type"><?= $fetch_property['type']; ?> • <?= $fetch_property['offer']; ?></div>
          <div class="pc-name"><?= htmlspecialchars($fetch_property['property_name']); ?></div>
          <div class="pc-addr"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($fetch_property['address']); ?></div>
          <div class="pc-pills">
            <div class="pc-pill"><i class="fas fa-bed"></i> <?= $fetch_property['bhk']; ?> BHK</div>
            <div class="pc-pill"><i class="fas fa-bath"></i> <?= $fetch_property['bathroom']; ?> Bath</div>
            <div class="pc-pill"><i class="fas fa-ruler-combined"></i> <?= $fetch_property['carpet']; ?> sqft</div>
            <div class="pc-pill"><i class="fas fa-couch"></i> <?= $fetch_property['furnished']; ?></div>
            <div class="pc-pill"><i class="fas fa-trowel"></i> <?= $fetch_property['status']; ?></div>
          </div>
          <div class="pc-admin">
            <div class="pc-admin-av"><?= strtoupper(substr($fetch_user['name'], 0, 1)); ?></div>
            <div>
              <div class="pc-admin-name"><?= htmlspecialchars($fetch_user['name']); ?></div>
              <div class="pc-admin-date"><?= $fetch_property['date']; ?></div>
            </div>
          </div>
          <div class="pc-acts">
            <a href="view_property.php?get_id=<?= $fetch_property['id']; ?>" class="pca v"><i class="fas fa-eye"></i> View Details</a>
            <button type="submit" name="send" class="pca e"><i class="fas fa-phone-alt"></i> Send Enquiry</button>
          </div>
        </div>
      </form>
    </div>
    <?php
          }
       }else{
    ?>
    <div class="empty-state">
      <div class="empty-icon"><i class="fas fa-building"></i></div>
      <h3>No Properties Listed Yet</h3>
      <p>Be the first to list a property on MyEstate. Start reaching thousands of verified buyers today.</p>
      <a href="post_property.php" class="empty-btn"><i class="fas fa-plus"></i> Post a Property</a>
    </div>
    <?php
       }
    ?>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer" id="footer">
  <div class="foot-grid">
    <div class="foot-brand"><a href="home.php" class="foot-logo">My<span>Estate</span></a><p>Trusted real estate across Mumbai & Pune. Verified listings, zero commission, expert guidance.</p><div class="foot-socials"><a href="#" class="fsc"><i class="fab fa-instagram"></i></a><a href="#" class="fsc"><i class="fab fa-facebook-f"></i></a><a href="#" class="fsc"><i class="fab fa-twitter"></i></a><a href="#" class="fsc"><i class="fab fa-youtube"></i></a></div></div>
    <div class="foot-col"><h4>Properties</h4><a href="listings.php"><i class="fas fa-chevron-right"></i>All Listings</a><a href="search.php"><i class="fas fa-chevron-right"></i>Filter Search</a><a href="saved.php"><i class="fas fa-chevron-right"></i>Saved</a></div>
    <div class="foot-col"><h4>Quick Links</h4><a href="home.php"><i class="fas fa-chevron-right"></i>Dashboard</a><a href="about.php"><i class="fas fa-chevron-right"></i>About Us</a><a href="contact.php"><i class="fas fa-chevron-right"></i>Contact</a></div>
    <div class="foot-col"><h4>Contact Us</h4><div class="fci"><div class="fci-ic"><i class="fas fa-map-marker-alt"></i></div><div class="fci-t"><strong>Office</strong>Bandra West, Mumbai — 400050</div></div><div class="fci"><div class="fci-ic"><i class="fas fa-phone-alt"></i></div><div class="fci-t"><strong>Phone</strong>+91 98765 43210</div></div><div class="fci"><div class="fci-ic"><i class="fas fa-envelope"></i></div><div class="fci-t"><strong>Email</strong>hello@myestate.in</div></div></div>
  </div>
  <div class="foot-bot"><p class="foot-copy">© <?= date('Y'); ?> <span>MyEstate</span>. Made with ♥ in Mumbai.</p><div class="foot-bot-links"><a href="#">Privacy</a><a href="#">Terms</a><a href="#">Cookies</a></div></div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php include 'components/message.php'; ?>

<script>
// Scroll reveal
const obs = new IntersectionObserver(entries => {
  entries.forEach(x => {
    if(x.isIntersecting){ x.target.classList.add('in'); obs.unobserve(x.target); }
  });
}, {threshold: 0.05});
document.querySelectorAll('.reveal').forEach(r => obs.observe(r));

// Nav scroll shadow
window.addEventListener('scroll', () => {
  document.getElementById('mainNav').classList.toggle('scrolled', scrollY > 40);
});
</script>

</body>
</html>