<?php
include 'components/connect.php';
$user_id = validate_user_cookie($conn);
if (!$user_id) { header('location:login.php'); exit(); }

// Handle booking cancellation
if (isset($_POST['cancel_booking']) && !empty($_POST['booking_id'])) {
    $bid = trim($_POST['booking_id']);
    $chk = $conn->prepare("SELECT id FROM requests WHERE id = ? AND sender = ?");
    $chk->execute([$bid, $user_id]);
    if ($chk->rowCount() > 0) {
        $conn->prepare("UPDATE requests SET status='cancelled' WHERE id=? AND sender=?")->execute([$bid, $user_id]);
    }
    header('location:requests.php');
    exit();
}

// Fetch user info
$sel_user = $conn->prepare("SELECT name, email FROM users WHERE id = ? LIMIT 1");
$sel_user->execute([$user_id]);
$fetch_user = $sel_user->fetch(PDO::FETCH_ASSOC);
$user_name    = $fetch_user['name'] ?? 'User';
$user_initial = strtoupper(substr($user_name, 0, 1));

$sel_saved = $conn->prepare("SELECT COUNT(*) as cnt FROM `saved` WHERE user_id = ?");
$sel_saved->execute([$user_id]);
$saved_count = $sel_saved->fetch(PDO::FETCH_ASSOC)['cnt'];

// Fetch ALL bookings made by this user
$sel_bk = $conn->prepare(
    "SELECT r.id, r.visit_date, r.time_slot, r.purpose, r.notes, r.status, r.date,
            r.property_id, r.user_name, r.user_email, r.user_phone,
            p.property_name, p.address, p.price, p.type, p.image_01
     FROM `requests` r
     LEFT JOIN `property` p ON r.property_id = p.id
     WHERE r.sender = ?
     ORDER BY r.id DESC"
);
$sel_bk->execute([$user_id]);
$bookings = $sel_bk->fetchAll(PDO::FETCH_ASSOC);

$total    = count($bookings);
$pending  = count(array_filter($bookings, fn($b) => ($b['status'] ?? 'pending') === 'pending'));
$confirmed= count(array_filter($bookings, fn($b) => ($b['status'] ?? '') === 'confirmed'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Requests — MyEstate</title>
<meta name="description" content="Track your property visit bookings on MyEstate.">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
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
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--ink);}
::-webkit-scrollbar{width:3px;}::-webkit-scrollbar-thumb{background:var(--r);}
/* NAV */
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
.nd-danger{color:#c0392b!important;}.nd-danger i{color:#c0392b!important;}.nd-danger:hover{background:#fff5f5!important;}
/* PAGE */
.page-wrap{padding-top:calc(var(--nav-h) + 5rem);padding-bottom:8rem;min-height:100vh;}
.container{max-width:118rem;margin:0 auto;padding:0 6%;}
.eyebrow{display:inline-flex;align-items:center;gap:.5rem;font-size:.9rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--r);background:var(--rp);padding:.35rem 1rem;border-radius:99px;border:1px solid rgba(214,40,40,.12);width:fit-content;margin-bottom:1.2rem;}
.eyebrow::before{content:'';width:.4rem;height:.4rem;border-radius:50%;background:var(--r);animation:blink 2s infinite;flex-shrink:0;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
.pg-header{margin-bottom:4rem;}
.pg-title{font-family:'Cormorant Garamond',serif;font-size:clamp(4rem,5.5vw,6.5rem);font-weight:700;color:var(--ink);line-height:.9;letter-spacing:-.03em;margin-bottom:1rem;}
.pg-title em{font-style:italic;color:var(--r);}
.pg-sub{font-size:1.4rem;color:var(--ink3);line-height:1.75;}
/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:1.6rem;margin-bottom:4rem;}
.sc{background:var(--white);border:1.5px solid var(--line);border-radius:2rem;padding:2.4rem;transition:all .3s var(--ease);display:flex;align-items:center;gap:1.8rem;}
.sc:hover{border-color:rgba(214,40,40,.25);box-shadow:var(--sh);transform:translateY(-3px);}
.sc-icon{width:5rem;height:5rem;border-radius:1.3rem;background:var(--rp);display:grid;place-items:center;font-size:2.1rem;color:var(--r);flex-shrink:0;}
.sc-n{font-family:'Cormorant Garamond',serif;font-size:4rem;font-weight:700;color:var(--ink);line-height:1;}
.sc-l{font-size:1.2rem;color:var(--ink3);}
/* FILTER TABS */
.filter-tabs{display:flex;gap:.8rem;margin-bottom:3rem;flex-wrap:wrap;}
.ftab{padding:.8rem 2rem;border-radius:99px;font-size:1.25rem;font-weight:700;cursor:pointer;transition:all .22s;border:1.5px solid var(--line);background:var(--white);color:var(--ink3);}
.ftab.active{background:var(--r);color:#fff;border-color:var(--r);box-shadow:0 4px 16px rgba(214,40,40,.25);}
.ftab:not(.active):hover{border-color:rgba(214,40,40,.3);color:var(--r);}
/* CARDS */
.bookings-grid{display:grid;gap:2rem;}
.bk-card{background:var(--white);border:1.5px solid var(--line);border-radius:2.4rem;overflow:hidden;display:grid;grid-template-columns:20rem 1fr;transition:all .35s var(--ease);animation:fadeUp .5s var(--ease) both;}
.bk-card:hover{box-shadow:var(--sh2);transform:translateY(-5px);}
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
.bk-img{position:relative;overflow:hidden;min-height:18rem;}
.bk-img img{width:100%;height:100%;object-fit:cover;transition:transform .7s var(--ease);}
.bk-card:hover .bk-img img{transform:scale(1.06);}
.bk-img-ov{position:absolute;inset:0;background:linear-gradient(to top,rgba(15,2,2,.5) 0%,transparent 60%);}
.bk-type{position:absolute;top:1.2rem;left:1.2rem;background:var(--r);color:#fff;font-size:1.05rem;font-weight:700;padding:.4rem 1rem;border-radius:99px;text-transform:capitalize;letter-spacing:.04em;}
.bk-no-img{width:100%;height:100%;min-height:18rem;background:linear-gradient(135deg,var(--rp2),var(--rp3));display:grid;place-items:center;}
.bk-no-img i{font-size:4rem;color:rgba(214,40,40,.3);}
.bk-body{padding:2.4rem 2.8rem;display:flex;flex-direction:column;justify-content:space-between;}
.bk-top-row{display:flex;align-items:flex-start;justify-content:space-between;gap:1.6rem;margin-bottom:1.6rem;}
.bk-name{font-family:'Cormorant Garamond',serif;font-size:2.4rem;font-weight:700;color:var(--ink);line-height:1.05;margin-bottom:.4rem;}
.bk-addr{font-size:1.25rem;color:var(--ink3);display:flex;align-items:center;gap:.4rem;}
.bk-addr i{color:var(--r);font-size:1rem;}
.bk-price{font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:700;color:var(--r);}
.bk-status{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1.3rem;border-radius:99px;font-size:1.1rem;font-weight:700;flex-shrink:0;}
.bk-status.pending{background:#fff8ee;color:#b07000;border:1.5px solid rgba(176,112,0,.18);}
.bk-status.confirmed{background:#edfff4;color:#1a7a4e;border:1.5px solid rgba(26,122,78,.18);}
.bk-status.cancelled{background:#fff5f5;color:#c0392b;border:1.5px solid rgba(192,57,43,.15);}
.bk-status::before{content:'';width:.5rem;height:.5rem;border-radius:50%;background:currentColor;flex-shrink:0;}
.bk-details{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem;}
.bk-det{background:var(--rp);border:1px solid rgba(214,40,40,.1);border-radius:1.2rem;padding:1.2rem 1.4rem;}
.bk-det-lbl{font-size:.95rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--ink3);margin-bottom:.3rem;}
.bk-det-val{font-size:1.3rem;font-weight:700;color:var(--ink);}
.bk-actions{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}
.bk-btn{display:inline-flex;align-items:center;gap:.6rem;padding:.9rem 2rem;border-radius:99px;font-size:1.2rem;font-weight:700;cursor:pointer;transition:all .22s;text-decoration:none;border:none;font-family:'Outfit',sans-serif;}
.bk-btn.view{background:var(--r);color:#fff;box-shadow:0 4px 16px rgba(214,40,40,.28);}
.bk-btn.view:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(214,40,40,.42);}
.bk-btn.cancel{background:var(--white);color:var(--ink3);border:1.5px solid var(--line);}
.bk-btn.cancel:hover{border-color:var(--r);color:var(--r);}
.bk-btn.disabled{opacity:.5;cursor:not-allowed;pointer-events:none;}
/* MODAL */
.modal-ov{display:none;position:fixed;inset:0;z-index:1200;background:rgba(10,2,2,.72);backdrop-filter:blur(10px);align-items:center;justify-content:center;}
.modal-ov.open{display:flex;}
.modal-box{background:var(--white);border-radius:2.8rem;width:90%;max-width:56rem;max-height:88vh;overflow-y:auto;padding:4rem;position:relative;box-shadow:0 40px 100px rgba(214,40,40,.25);animation:popIn .4s var(--ease);}
@keyframes popIn{from{opacity:0;transform:scale(.95) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal-close{position:absolute;top:2rem;right:2rem;width:4rem;height:4rem;border-radius:50%;border:1.5px solid var(--line);background:var(--bg);display:grid;place-items:center;font-size:1.5rem;color:var(--ink3);cursor:pointer;transition:all .2s;}
.modal-close:hover{border-color:var(--r);color:var(--r);}
.modal-title{font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:700;color:var(--ink);margin-bottom:.5rem;}
.modal-title em{font-style:italic;color:var(--r);}
.modal-sub{font-size:1.3rem;color:var(--ink3);margin-bottom:2.8rem;line-height:1.6;}
.modal-row{display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;}
.modal-field{background:var(--rp);border:1px solid rgba(214,40,40,.1);border-radius:1.4rem;padding:1.6rem 2rem;}
.modal-field.full{grid-column:1/-1;}
.mf-lbl{font-size:.95rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--ink3);margin-bottom:.4rem;}
.mf-val{font-size:1.4rem;font-weight:700;color:var(--ink);}
/* EMPTY STATE */
.empty-state{text-align:center;padding:8rem 2rem;background:var(--white);border-radius:3rem;border:2px dashed rgba(214,40,40,.15);}
.empty-state i{font-size:6rem;color:rgba(214,40,40,.18);margin-bottom:2rem;display:block;}
.empty-state h2{font-family:'Cormorant Garamond',serif;font-size:4rem;font-weight:700;color:var(--ink);margin-bottom:1rem;}
.empty-state p{font-size:1.4rem;color:var(--ink3);line-height:1.75;margin-bottom:3rem;max-width:40rem;margin-left:auto;margin-right:auto;}
.empty-cta{display:inline-flex;align-items:center;gap:.8rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;padding:1.4rem 3.2rem;border-radius:99px;font-size:1.4rem;font-weight:800;text-decoration:none;box-shadow:0 8px 28px rgba(214,40,40,.32);transition:all .25s;}
.empty-cta:hover{transform:translateY(-3px);box-shadow:0 14px 36px rgba(214,40,40,.45);}
@media(max-width:900px){.bk-card{grid-template-columns:1fr;}.bk-img{height:20rem;}.stats-row{grid-template-columns:1fr 1fr;}}
@media(max-width:600px){.bk-details{grid-template-columns:1fr 1fr;}.stats-row{grid-template-columns:1fr;}.bk-top-row{flex-direction:column;}.modal-row{grid-template-columns:1fr;}}
</style>
</head>
<body>

<!-- Detail Modal -->
<div class="modal-ov" id="detailModal">
  <div class="modal-box">
    <button class="modal-close" onclick="document.getElementById('detailModal').classList.remove('open')"><i class="fas fa-times"></i></button>
    <div id="modalInner"></div>
  </div>
</div>

<!-- NAV -->
<nav class="nav" id="mainNav">
  <a href="home.php" class="logo">My<span>Estate</span></a>
  <div class="nav-center">
    <a href="home.php">Home</a>
    <a href="listings.php">Properties</a>
    <a href="about.php">About</a>
    <a href="contact.php">Contact</a>
  </div>
  <div class="nav-right">
    <a href="saved.php" class="nav-icon"><i class="fas fa-heart"></i><?php if($saved_count > 0): ?><span class="nav-badge"><?= $saved_count ?></span><?php endif; ?></a>
    <div class="nav-user" id="navUser">
      <div class="nav-av"><?= $user_initial ?></div>
      <span style="font-size:1.3rem;font-weight:700;color:var(--ink);"><?= htmlspecialchars($user_name) ?></span>
      <i class="fas fa-chevron-down" style="font-size:1rem;color:var(--ink3);margin-left:.4rem;"></i>
      <div class="nav-drop-menu" id="navDropMenu">
        <a href="saved.php" class="nd-item"><i class="fas fa-heart"></i>Saved Properties</a>
        <a href="requests.php" class="nd-item" style="color:var(--r);"><i class="fas fa-file-alt"></i>My Requests</a>
        <div class="nd-sep"></div>
        <a href="home.php#agentSec" class="nd-item" style="color:var(--r);font-weight:700;"><i class="fas fa-user-tie"></i>Become an Agent</a>
        <div class="nd-sep"></div>
        <a href="update.php" class="nd-item"><i class="fas fa-user-edit"></i>Edit Profile</a>
        <div class="nd-sep"></div>
        <a href="javascript:void(0)" onclick="confirmLogout()" class="nd-item nd-danger"><i class="fas fa-sign-out-alt"></i>Logout</a>
      </div>
    </div>
  </div>
</nav>

<div class="page-wrap">
  <div class="container">

    <!-- PAGE HEADER -->
    <div class="pg-header">
      <div class="eyebrow">My Bookings</div>
      <h1 class="pg-title">Visit <em>Requests</em></h1>
      <p class="pg-sub">All your site visit bookings and their current status.</p>
    </div>

    <!-- STATS -->
    <div class="stats-row">
      <div class="sc">
        <div class="sc-icon"><i class="fas fa-calendar-check"></i></div>
        <div><div class="sc-n"><?= $total ?></div><div class="sc-l">Total Bookings</div></div>
      </div>
      <div class="sc">
        <div class="sc-icon" style="background:#fff8ee;"><i class="fas fa-hourglass-half" style="color:#b07000;"></i></div>
        <div><div class="sc-n" style="color:#b07000;"><?= $pending ?></div><div class="sc-l">Pending</div></div>
      </div>
      <div class="sc">
        <div class="sc-icon" style="background:#edfff4;"><i class="fas fa-check-circle" style="color:#1a7a4e;"></i></div>
        <div><div class="sc-n" style="color:#1a7a4e;"><?= $confirmed ?></div><div class="sc-l">Confirmed</div></div>
      </div>
    </div>

    <!-- FILTER TABS -->
    <div class="filter-tabs">
      <button class="ftab active" onclick="filterCards('all',this)">All (<?= $total ?>)</button>
      <button class="ftab" onclick="filterCards('pending',this)">Pending (<?= $pending ?>)</button>
      <button class="ftab" onclick="filterCards('confirmed',this)">Confirmed (<?= $confirmed ?>)</button>
      <button class="ftab" onclick="filterCards('cancelled',this)">Cancelled (<?= count($bookings) - $pending - $confirmed ?>)</button>
    </div>

    <!-- BOOKINGS -->
    <div class="bookings-grid" id="bookingsGrid">
    <?php if (empty($bookings)): ?>
      <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h2>No Bookings Yet</h2>
        <p>You haven't booked any site visits. Browse properties and book a visit to get started.</p>
        <a href="listings.php" class="empty-cta"><i class="fas fa-search"></i> Browse Properties</a>
      </div>
    <?php else: ?>
      <?php foreach ($bookings as $i => $bk): 
        $status = !empty($bk['status']) ? $bk['status'] : 'pending';
        $prop_name = !empty($bk['property_name']) ? $bk['property_name'] : 'General Visit';
        $prop_addr = !empty($bk['address'])        ? $bk['address']        : '—';
        $prop_price= !empty($bk['price'])          ? '₹'.$bk['price']      : '';
        $visit_dt  = !empty($bk['visit_date'])     ? date('d M Y', strtotime($bk['visit_date'])) : date('d M Y', strtotime($bk['date']));
        $booked_on = date('d M Y', strtotime($bk['date']));
        $delay = $i * 0.07;
      ?>
      <div class="bk-card" data-status="<?= $status ?>" style="animation-delay:<?= $delay ?>s">
        <div class="bk-img">
          <?php if (!empty($bk['image_01'])): ?>
            <img src="uploaded_files/<?= htmlspecialchars($bk['image_01']) ?>" alt="<?= htmlspecialchars($prop_name) ?>">
            <div class="bk-img-ov"></div>
          <?php else: ?>
            <div class="bk-no-img"><i class="fas fa-building"></i></div>
          <?php endif; ?>
          <?php if (!empty($bk['type'])): ?>
            <div class="bk-type"><?= htmlspecialchars($bk['type']) ?></div>
          <?php endif; ?>
        </div>
        <div class="bk-body">
          <div>
            <div class="bk-top-row">
              <div>
                <div class="bk-name"><?= htmlspecialchars($prop_name) ?></div>
                <div class="bk-addr"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($prop_addr) ?></div>
              </div>
              <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.8rem;">
                <span class="bk-status <?= $status ?>"><?= ucfirst($status) ?></span>
                <?php if ($prop_price): ?><div class="bk-price"><?= $prop_price ?></div><?php endif; ?>
              </div>
            </div>
            <div class="bk-details">
              <div class="bk-det">
                <div class="bk-det-lbl">Visit Date</div>
                <div class="bk-det-val"><?= $visit_dt ?></div>
              </div>
              <div class="bk-det">
                <div class="bk-det-lbl">Time Slot</div>
                <div class="bk-det-val"><?= htmlspecialchars($bk['time_slot'] ?: '—') ?></div>
              </div>
              <div class="bk-det">
                <div class="bk-det-lbl">Purpose</div>
                <div class="bk-det-val"><?= htmlspecialchars($bk['purpose'] ?: '—') ?></div>
              </div>
            </div>
          </div>
          <div class="bk-actions">
            <?php if (!empty($bk['property_id'])): ?>
              <a href="view_property.php?get_id=<?= htmlspecialchars($bk['property_id']) ?>" class="bk-btn view"><i class="fas fa-eye"></i> View Property</a>
            <?php endif; ?>
            <button class="bk-btn view" style="background:var(--rp);color:var(--r);box-shadow:none;" onclick='showDetails(<?= json_encode([
              "prop_name"  => $prop_name,
              "prop_addr"  => $prop_addr,
              "prop_price" => $prop_price,
              "visit_dt"   => $visit_dt,
              "time_slot"  => $bk["time_slot"] ?: "—",
              "purpose"    => $bk["purpose"]   ?: "—",
              "notes"      => $bk["notes"]     ?: "None",
              "status"     => $status,
              "booked_on"  => $booked_on,
              "phone"      => $bk["user_phone"]?: "—",
            ]) ?>)'><i class="fas fa-info-circle"></i> Details</button>
            <?php if ($status !== 'cancelled'): ?>
              <form action="" method="POST" style="margin:0;">
                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($bk['id']) ?>">
                <button type="submit" name="cancel_booking" class="bk-btn cancel" onclick="return confirm('Cancel this booking?')"><i class="fas fa-times"></i> Cancel</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
    </div>

  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script>
// NAV scroll
window.addEventListener('scroll',()=>document.getElementById('mainNav').classList.toggle('scrolled',scrollY>40));

// Profile dropdown
const navUserEl = document.getElementById('navUser');
const navMenu   = document.getElementById('navDropMenu');
if (navUserEl) {
  navUserEl.addEventListener('click', (e) => { e.stopPropagation(); navMenu.classList.toggle('open'); });
  navMenu.addEventListener('click', (e) => { e.stopPropagation(); });
  document.addEventListener('click', () => navMenu.classList.remove('open'));
  window.addEventListener('scroll', () => navMenu.classList.remove('open'), {passive:true});
}

// Logout confirmation
function confirmLogout() {
  swal({ title: "Logout?", text: "Are you sure you want to logout?", icon: "warning",
    buttons: ["Cancel", "Logout"], dangerMode: true
  }).then(ok => { if (ok) window.location = 'components/user_logout.php'; });
}

// Filter cards by status
function filterCards(status, btn) {
  document.querySelectorAll('.ftab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.bk-card').forEach(card => {
    card.style.display = (status === 'all' || card.dataset.status === status) ? '' : 'none';
  });
}

// Show detail modal
function showDetails(d) {
  document.getElementById('modalInner').innerHTML = `
    <div class="eyebrow">Booking Details</div>
    <div class="modal-title">${d.prop_name.replace(/</g,'&lt;')}</div>
    <div class="modal-sub"><i class="fas fa-map-marker-alt" style="color:var(--r);margin-right:.4rem;"></i>${d.prop_addr.replace(/</g,'&lt;')}</div>
    <div class="modal-row">
      <div class="modal-field"><div class="mf-lbl">Visit Date</div><div class="mf-val">${d.visit_dt}</div></div>
      <div class="modal-field"><div class="mf-lbl">Time Slot</div><div class="mf-val">${d.time_slot}</div></div>
      <div class="modal-field"><div class="mf-lbl">Purpose</div><div class="mf-val">${d.purpose}</div></div>
      <div class="modal-field"><div class="mf-lbl">Status</div><div class="mf-val" style="text-transform:capitalize;">${d.status}</div></div>
      <div class="modal-field"><div class="mf-lbl">Booked On</div><div class="mf-val">${d.booked_on}</div></div>
      <div class="modal-field"><div class="mf-lbl">Contact</div><div class="mf-val">${d.phone}</div></div>
      ${d.prop_price ? `<div class="modal-field"><div class="mf-lbl">Property Price</div><div class="mf-val">${d.prop_price}</div></div>` : ''}
      ${d.notes && d.notes !== 'None' ? `<div class="modal-field full"><div class="mf-lbl">Notes</div><div class="mf-val">${d.notes.replace(/</g,'&lt;')}</div></div>` : ''}
    </div>
  `;
  document.getElementById('detailModal').classList.add('open');
}
document.getElementById('detailModal').addEventListener('click', function(e) {
  if (e.target === this) this.classList.remove('open');
});
</script>
</body>
</html>