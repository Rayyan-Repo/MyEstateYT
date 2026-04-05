<?php

include '../components/connect.php';

$admin_id = validate_admin_cookie($conn);
if(!$admin_id){
   header('location:login.php');
   exit();
}

if(isset($_POST['delete'])){
   $delete_id = $_POST['delete_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);
   $verify_delete = $conn->prepare("SELECT * FROM `property` WHERE id = ?");
   $verify_delete->execute([$delete_id]);
   if($verify_delete->rowCount() > 0){
      $select_images = $conn->prepare("SELECT * FROM `property` WHERE id = ?");
      $select_images->execute([$delete_id]);
      while($fetch_images = $select_images->fetch(PDO::FETCH_ASSOC)){
         $image_01 = $fetch_images['image_01'];
         $image_02 = $fetch_images['image_02'];
         $image_03 = $fetch_images['image_03'];
         $image_04 = $fetch_images['image_04'];
         $image_05 = $fetch_images['image_05'];
         unlink('../uploaded_files/'.$image_01);
         if(!empty($image_02)) unlink('../uploaded_files/'.$image_02);
         if(!empty($image_03)) unlink('../uploaded_files/'.$image_03);
         if(!empty($image_04)) unlink('../uploaded_files/'.$image_04);
         if(!empty($image_05)) unlink('../uploaded_files/'.$image_05);
      }
      $delete_listings = $conn->prepare("DELETE FROM `property` WHERE id = ?");
      $delete_listings->execute([$delete_id]);
      $success_msg[] = 'Listing deleted!';
   }else{
      $warning_msg[] = 'Listing already deleted!';
   }
}

// counts for stats
$count_all = $conn->prepare("SELECT COUNT(*) FROM `property`"); $count_all->execute(); $total_all = $count_all->fetchColumn();
$count_apt = $conn->prepare("SELECT COUNT(*) FROM `property` WHERE type = 'apartment'"); $count_apt->execute(); $total_apt = $count_apt->fetchColumn();
$count_vil = $conn->prepare("SELECT COUNT(*) FROM `property` WHERE type = 'villa'"); $count_vil->execute(); $total_vil = $count_vil->fetchColumn();
$count_plt = $conn->prepare("SELECT COUNT(*) FROM `property` WHERE type = 'plot'"); $count_plt->execute(); $total_plt = $count_plt->fetchColumn();
$count_com = $conn->prepare("SELECT COUNT(*) FROM `property` WHERE type = 'commercial'"); $count_com->execute(); $total_com = $count_com->fetchColumn();

// active filter
$active_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search_box = '';

// Handle search from dashboard (GET) or search box (POST)
if(isset($_GET['q']) && !empty($_GET['q'])){
   $search_box = filter_var($_GET['q'], FILTER_SANITIZE_STRING);
   $select_listings = $conn->prepare("SELECT * FROM `property` WHERE property_name LIKE ? OR address LIKE ? OR type LIKE ? ORDER BY id DESC");
   $select_listings->execute(['%'.$search_box.'%', '%'.$search_box.'%', '%'.$search_box.'%']);
} elseif(isset($_POST['search_btn'])){
   $search_box = filter_var($_POST['search_box'], FILTER_SANITIZE_STRING);
   $select_listings = $conn->prepare("SELECT * FROM `property` WHERE property_name LIKE ? OR address LIKE ? ORDER BY id DESC");
   $select_listings->execute(['%'.$search_box.'%', '%'.$search_box.'%']);
} elseif($active_filter != 'all'){
   $select_listings = $conn->prepare("SELECT * FROM `property` WHERE type = ? ORDER BY id DESC");
   $select_listings->execute([$active_filter]);
} else {
   $select_listings = $conn->prepare("SELECT * FROM `property` ORDER BY id DESC");
   $select_listings->execute();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Listings — EstateAdmin</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<link rel="stylesheet" href="../css/admin_style.css">
<style>
:root{
  --r:#d62828;--rd:#9e1c1c;
  --rp:#fdf1f1;--rp2:#fae6e6;
  --ink:#0d0202;--ink3:#9a6565;
  --bg:#f5eded;--white:#ffffff;
  --line:rgba(214,40,40,0.09);
  --ease:cubic-bezier(.22,1,.36,1);
  --sh:0 2px 24px rgba(214,40,40,0.07);
  --sh2:0 16px 56px rgba(214,40,40,0.14);
}
*{font-family:'Outfit',sans-serif;box-sizing:border-box;}
html{font-size:62.5%;}
body{background:var(--bg);min-height:100vh;}
::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-thumb{background:var(--rp2);border-radius:99px;}

.dash-body{background:var(--bg);min-height:100vh;}
.dash-wrap{padding:3rem 3.2rem;}

/* TOPBAR */
.dash-tb{position:sticky;top:0;z-index:200;background:rgba(245,237,237,0.88);backdrop-filter:blur(20px);border-bottom:1px solid var(--line);padding:1.4rem 3.2rem;display:flex;align-items:center;justify-content:space-between;}
.tb-path{display:flex;align-items:center;gap:1rem;font-size:1.3rem;}
.tb-path .seg{color:var(--ink3);}
.tb-path .sep{color:var(--ink3);opacity:0.3;}
.tb-path .cur{color:var(--ink);font-weight:700;}
.tb-r{display:flex;align-items:center;gap:1rem;}
.tb-ic{width:4rem;height:4rem;border-radius:50%;background:var(--white);border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;color:var(--ink3);font-size:1.45rem;box-shadow:var(--sh);transition:all 0.2s;text-decoration:none;}
.tb-ic:hover{border-color:var(--r);color:var(--r);transform:translateY(-2px);}

/* PAGE HEADER */
.pg-hd{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:2.8rem;padding-bottom:2.4rem;border-bottom:1px solid var(--line);}
.pg-title{font-family:'Cormorant Garamond',serif;font-size:4rem;font-weight:700;color:var(--ink);letter-spacing:-0.03em;line-height:1;}
.pg-title em{font-style:italic;color:var(--r);}
.pg-sub{font-size:1.4rem;color:var(--ink3);margin-top:0.6rem;}
.pg-stats{display:flex;gap:1.2rem;}
.pg-stat{background:var(--white);border:1px solid var(--line);border-radius:1.2rem;padding:1.2rem 2rem;text-align:center;box-shadow:var(--sh);}
.pg-stat .sv{font-family:'Cormorant Garamond',serif;font-size:2.6rem;font-weight:700;color:var(--ink);line-height:1;}
.pg-stat .sl{font-size:1.1rem;color:var(--ink3);text-transform:uppercase;letter-spacing:0.08em;margin-top:0.2rem;}

/* TOOLBAR */
.toolbar{display:flex;align-items:center;justify-content:space-between;gap:1.6rem;margin-bottom:2rem;flex-wrap:wrap;}
.srch{display:flex;align-items:center;gap:1rem;background:var(--white);border:1px solid var(--line);border-radius:99px;padding:1rem 2rem;box-shadow:var(--sh);flex:1;max-width:44rem;transition:all 0.2s;}
.srch:focus-within{border-color:rgba(214,40,40,0.3);}
.srch i{color:var(--ink3);font-size:1.4rem;}
.srch input{border:none;outline:none;font-size:1.4rem;color:var(--ink);background:transparent;flex:1;font-family:'Outfit',sans-serif;}
.srch input::placeholder{color:var(--ink3);}
.srch button{background:none;border:none;cursor:pointer;color:var(--r);font-size:1.4rem;}
.toolbar-r{display:flex;gap:1rem;align-items:center;}
.add-btn{display:flex;align-items:center;gap:0.8rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;padding:1rem 2rem;font-size:1.35rem;font-weight:600;cursor:pointer;box-shadow:0 4px 16px rgba(214,40,40,0.22);transition:all 0.2s;text-decoration:none;}
.add-btn:hover{transform:translateY(-2px);}

/* FILTER CHIPS */
.chips{display:flex;gap:0.8rem;margin-bottom:2.4rem;flex-wrap:wrap;}
.chip{display:inline-flex;align-items:center;gap:0.6rem;padding:0.7rem 1.6rem;border-radius:99px;font-size:1.25rem;font-weight:600;cursor:pointer;transition:all 0.2s;border:1.5px solid transparent;text-decoration:none;}
.chip.on{background:var(--r);color:#fff;box-shadow:0 2px 10px rgba(214,40,40,0.22);}
.chip:not(.on){background:var(--white);color:var(--ink3);border-color:var(--line);box-shadow:var(--sh);}
.chip:not(.on):hover{border-color:rgba(214,40,40,0.25);color:var(--r);}

/* GRID */
.listings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(30rem,1fr));gap:2rem;}

/* CARD */
.l-card{background:var(--white);border-radius:1.8rem;overflow:hidden;border:1px solid var(--line);box-shadow:var(--sh);transition:all 0.28s var(--ease);animation:fadeUp 0.5s var(--ease) both;position:relative;}
.l-card:hover{transform:translateY(-6px);box-shadow:var(--sh2);}
.l-img{position:relative;height:19rem;overflow:hidden;}
.l-img img{width:100%;height:100%;object-fit:cover;transition:transform 0.5s var(--ease);}
.l-card:hover .l-img img{transform:scale(1.05);}
.l-img-overlay{position:absolute;inset:0;background:linear-gradient(to bottom,transparent 50%,rgba(13,2,2,0.45));pointer-events:none;}
.l-img-count{position:absolute;top:1.2rem;left:1.2rem;display:flex;align-items:center;gap:0.5rem;background:rgba(0,0,0,0.55);backdrop-filter:blur(8px);color:#fff;font-size:1.2rem;font-weight:600;padding:0.4rem 1rem;border-radius:99px;}
.l-type{position:absolute;top:1.2rem;right:1.2rem;background:var(--r);color:#fff;font-size:1.1rem;font-weight:700;padding:0.4rem 1rem;border-radius:99px;letter-spacing:0.04em;text-transform:capitalize;}
.l-status{position:absolute;bottom:1.2rem;left:1.2rem;display:flex;align-items:center;gap:0.5rem;background:rgba(26,156,78,0.88);color:#fff;font-size:1.1rem;font-weight:700;padding:0.4rem 1rem;border-radius:99px;}
.l-status::before{content:'';width:0.5rem;height:0.5rem;border-radius:50%;background:#fff;display:block;}
.l-body{padding:2rem 2rem 1.4rem;}
.l-price{font-family:'Cormorant Garamond',serif;font-size:2.4rem;font-weight:700;color:var(--r);line-height:1;margin-bottom:0.6rem;}
.l-name{font-size:1.5rem;font-weight:700;color:var(--ink);margin-bottom:0.4rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.l-loc{font-size:1.3rem;color:var(--ink3);display:flex;align-items:center;gap:0.5rem;}
.l-loc i{color:var(--r);font-size:1.1rem;}
.l-meta{display:flex;gap:0;border-top:1px solid var(--line);margin-top:1.4rem;}
.l-meta-item{flex:1;padding:1rem 0;text-align:center;border-right:1px solid var(--line);font-size:1.15rem;color:var(--ink3);}
.l-meta-item:last-child{border-right:none;}
.l-meta-item i{display:block;font-size:1.3rem;color:var(--r);margin-bottom:0.3rem;}
.l-actions{display:flex;gap:0.8rem;padding:1.4rem 2rem 2rem;}
.l-btn{flex:1;display:flex;align-items:center;justify-content:center;gap:0.6rem;padding:1rem;border-radius:1rem;font-size:1.3rem;font-weight:600;cursor:pointer;transition:all 0.2s;border:none;text-decoration:none;}
.l-btn.view{background:var(--rp);color:var(--r);border:1px solid rgba(214,40,40,0.12);}
.l-btn.view:hover{background:var(--r);color:#fff;}
.l-btn.del{background:rgba(214,40,40,0.06);color:var(--r);border:1px solid rgba(214,40,40,0.1);}
.l-btn.del:hover{background:rgba(214,40,40,0.18);}

.empty-state{grid-column:1/-1;text-align:center;padding:6rem 2rem;background:var(--white);border-radius:1.8rem;border:2px dashed rgba(214,40,40,0.15);}
.empty-state i{font-size:5rem;color:rgba(214,40,40,0.2);margin-bottom:1.6rem;display:block;}
.empty-state h3{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);margin-bottom:0.6rem;}
.empty-state p{font-size:1.4rem;color:var(--ink3);}

@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}

/* FOOTER */
.dash-foot{border-top:1px solid var(--line);padding:1.8rem 3.2rem;background:var(--white);display:flex;align-items:center;justify-content:space-between;margin-top:3rem;}
.fl{font-size:1.25rem;color:var(--ink3);}
.fl b{color:var(--r);}
</style>
</head>
<body class="dash-body">

<?php include '../components/admin_header.php'; ?>

<!-- TOPBAR -->
<div class="dash-tb">
  <div class="tb-path">
    <span class="seg">EstateAdmin</span>
    <span class="sep">/</span>
    <span class="cur">Listings</span>
  </div>
  <div class="tb-r">
    <a href="admins.php" class="tb-ic"><i class="fas fa-user-shield"></i></a>
    <a href="messages.php" class="tb-ic"><i class="fas fa-bell"></i></a>
    <a href="update.php" class="tb-ic"><i class="fas fa-cog"></i></a>
  </div>
</div>

<div class="dash-wrap">

  <!-- PAGE HEADER -->
  <div class="pg-hd">
    <div>
      <div class="pg-title">All <em>Listings</em></div>
      <div class="pg-sub">Manage and monitor all property listings on the platform</div>
    </div>
    <div class="pg-stats">
      <div class="pg-stat"><div class="sv"><?= $total_all; ?></div><div class="sl">Total</div></div>
      <div class="pg-stat"><div class="sv"><?= $total_apt; ?></div><div class="sl">Apartment</div></div>
      <div class="pg-stat"><div class="sv"><?= $total_vil; ?></div><div class="sl">Villa</div></div>
      <div class="pg-stat"><div class="sv"><?= $total_plt; ?></div><div class="sl">Plot</div></div>
    </div>
  </div>

  <!-- TOOLBAR -->
  <div class="toolbar">
    <form action="" method="POST" style="flex:1;max-width:44rem;">
      <div class="srch">
        <i class="fas fa-search"></i>
        <input type="text" name="search_box" placeholder="Search by name, location..." maxlength="100">
        <button type="submit" name="search_btn"><i class="fas fa-arrow-right"></i></button>
      </div>
    </form>
    <div class="toolbar-r">
      <a href="../post_property.php" class="add-btn"><i class="fas fa-plus"></i> Add Listing</a>
    </div>
  </div>

  <!-- FILTER CHIPS -->
  <div class="chips">
    <a href="listings.php?filter=all" class="chip <?= $active_filter=='all'?'on':''; ?>">All (<?= $total_all; ?>)</a>
    <a href="listings.php?filter=apartment" class="chip <?= $active_filter=='apartment'?'on':''; ?>">Apartment (<?= $total_apt; ?>)</a>
    <a href="listings.php?filter=villa" class="chip <?= $active_filter=='villa'?'on':''; ?>">Villa (<?= $total_vil; ?>)</a>
    <a href="listings.php?filter=plot" class="chip <?= $active_filter=='plot'?'on':''; ?>">Plot (<?= $total_plt; ?>)</a>
    <a href="listings.php?filter=commercial" class="chip <?= $active_filter=='commercial'?'on':''; ?>">Commercial (<?= $total_com; ?>)</a>
  </div>

  <!-- LISTINGS GRID -->
  <div class="listings-grid">
  <?php
    $count = 0;
    if($select_listings->rowCount() > 0){
      while($fetch_listing = $select_listings->fetch(PDO::FETCH_ASSOC)){
        $listing_id = $fetch_listing['id'];
        $total_images = 1;
        if(!empty($fetch_listing['image_02'])) $total_images++;
        if(!empty($fetch_listing['image_03'])) $total_images++;
        if(!empty($fetch_listing['image_04'])) $total_images++;
        if(!empty($fetch_listing['image_05'])) $total_images++;
        $delay = $count * 0.05;
        $count++;
  ?>
    <div class="l-card" style="animation-delay:<?= $delay; ?>s">
      <div class="l-img">
        <img src="../uploaded_files/<?= $fetch_listing['image_01']; ?>" alt="<?= htmlspecialchars($fetch_listing['property_name']); ?>">
        <div class="l-img-overlay"></div>
        <div class="l-img-count"><i class="far fa-image"></i> <?= $total_images; ?></div>
        <div class="l-type"><?= htmlspecialchars($fetch_listing['type'] ?? 'Property'); ?></div>
        <div class="l-status">Active</div>
      </div>
      <div class="l-body">
        <div class="l-price">₹<?= htmlspecialchars($fetch_listing['price']); ?></div>
        <div class="l-name"><?= htmlspecialchars($fetch_listing['property_name']); ?></div>
        <div class="l-loc"><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($fetch_listing['address']); ?></div>
      </div>
      <div class="l-meta">
        <div class="l-meta-item"><i class="fas fa-bed"></i><?= $fetch_listing['bhk'] ?? '—'; ?> BHK</div>
        <div class="l-meta-item"><i class="fas fa-bath"></i><?= $fetch_listing['bathroom'] ?? '—'; ?> Bath</div>
        <div class="l-meta-item"><i class="fas fa-vector-square"></i><?= $fetch_listing['carpet'] ?? '—'; ?> sqft</div>
      </div>
      <div class="l-actions">
        <a href="view_property.php?get_id=<?= $listing_id; ?>" class="l-btn view"><i class="fas fa-eye"></i> View</a>
        <form action="" method="POST" style="flex:1;">
          <input type="hidden" name="delete_id" value="<?= $listing_id; ?>">
          <button type="submit" name="delete" class="l-btn del" style="width:100%;" onclick="return confirm('Delete this listing?');"><i class="fas fa-trash-alt"></i> Delete</button>
        </form>
      </div>
    </div>
  <?php } } else { ?>
    <div class="empty-state">
      <i class="fas fa-building"></i>
      <h3>No Listings Found</h3>
      <p><?= isset($_POST['search_box']) ? 'No results for your search.' : 'No properties posted yet!'; ?></p>
    </div>
  <?php } ?>
  </div>

</div>

<!-- FOOTER -->
<div class="dash-foot">
  <div class="fl">Showing <b><?= $count; ?></b> of <b><?= $total_all; ?></b> listings</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php'; ?>

</body>
</html>