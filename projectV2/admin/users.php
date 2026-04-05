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
   $verify_delete = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
   $verify_delete->execute([$delete_id]);
   if($verify_delete->rowCount() > 0){
      $select_images = $conn->prepare("SELECT * FROM `property` WHERE user_id = ?");
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
      $delete_listings = $conn->prepare("DELETE FROM `property` WHERE user_id = ?");
      $delete_listings->execute([$delete_id]);
      $delete_requests = $conn->prepare("DELETE FROM `requests` WHERE sender = ? OR receiver = ?");
      $delete_requests->execute([$delete_id, $delete_id]);
      $delete_saved = $conn->prepare("DELETE FROM `saved` WHERE user_id = ?");
      $delete_saved->execute([$delete_id]);
      $delete_user = $conn->prepare("DELETE FROM `users` WHERE id = ?");
      $delete_user->execute([$delete_id]);
      $success_msg[] = 'User deleted successfully!';
   }else{
      $warning_msg[] = 'User already deleted!';
   }
}

// counts
$count_total = $conn->prepare("SELECT COUNT(*) FROM `users`");
$count_total->execute();
$total_users = $count_total->fetchColumn();

$count_active = $conn->prepare("SELECT COUNT(DISTINCT user_id) FROM `property`");
$count_active->execute();
$active_sellers = $count_active->fetchColumn();

$count_props = $conn->prepare("SELECT COUNT(*) FROM `property`");
$count_props->execute();
$total_props = $count_props->fetchColumn();

$count_new = $conn->prepare("SELECT COUNT(*) FROM `users`");
$count_new->execute();
$new_users = $count_new->fetchColumn();

// search or fetch all
if(isset($_POST['search_btn'])){
   $search_box = filter_var($_POST['search_box'], FILTER_SANITIZE_STRING);
   $select_users = $conn->prepare("SELECT * FROM `users` WHERE name LIKE '%{$search_box}%' OR number LIKE '%{$search_box}%' OR email LIKE '%{$search_box}%' ORDER BY id DESC");
   $select_users->execute();
}else{
   $select_users = $conn->prepare("SELECT * FROM `users` ORDER BY id DESC");
   $select_users->execute();
}

$avatar_colors = ['#d62828','#2563eb','#1a9c4e','#ea580c','#7c3aed','#0891b2','#be185d','#ca8a04','#059669','#9333ea','#db2777','#0284c7','#16a34a','#d97706','#6d28d9'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users — EstateAdmin</title>
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

/* TOPBAR */
.dash-tb{position:sticky;top:0;z-index:200;background:rgba(245,237,237,0.92);backdrop-filter:blur(20px);border-bottom:1px solid var(--line);padding:1.4rem 3.2rem;display:flex;align-items:center;justify-content:space-between;}
.tb-path{display:flex;align-items:center;gap:1rem;font-size:1.3rem;}
.tb-path .seg{color:var(--ink3);}
.tb-path .sep{color:var(--ink3);opacity:0.3;}
.tb-path .cur{color:var(--ink);font-weight:700;}
.tb-r{display:flex;align-items:center;gap:1rem;}
.tb-ic{width:4rem;height:4rem;border-radius:50%;background:var(--white);border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;color:var(--ink3);font-size:1.45rem;box-shadow:var(--sh);transition:all 0.2s;text-decoration:none;}
.tb-ic:hover{border-color:var(--r);color:var(--r);transform:translateY(-2px);}

.dash-wrap{padding:3rem 3.2rem;}

/* PAGE HEADER */
.pg-hd{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:2.8rem;padding-bottom:2.4rem;border-bottom:1px solid var(--line);}
.pg-title{font-family:'Cormorant Garamond',serif;font-size:4rem;font-weight:700;color:var(--ink);letter-spacing:-0.03em;line-height:1;}
.pg-title em{font-style:italic;color:var(--r);}
.pg-sub{font-size:1.4rem;color:var(--ink3);margin-top:0.6rem;}

/* STATS */
.stats-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:1.6rem;margin-bottom:2.8rem;}
.stat-card{background:var(--white);border:1.5px solid var(--line);border-radius:1.8rem;padding:2.2rem 2.4rem;box-shadow:var(--sh);display:flex;align-items:center;gap:1.6rem;transition:all 0.3s var(--ease);cursor:pointer;position:relative;overflow:hidden;}
.stat-card:hover{transform:translateY(-6px);box-shadow:var(--sh2);}
.stat-card.active-filter{border-color:var(--r);box-shadow:0 0 0 3px rgba(214,40,40,0.1),var(--sh2);}
.stat-icon{width:5.5rem;height:5.5rem;border-radius:1.4rem;display:grid;place-items:center;font-size:2.2rem;flex-shrink:0;transition:transform 0.3s;}
.stat-card:hover .stat-icon{transform:scale(1.12) rotate(-6deg);}
.si-red{background:linear-gradient(135deg,rgba(214,40,40,0.13),rgba(214,40,40,0.04));color:var(--r);}
.si-green{background:linear-gradient(135deg,rgba(26,156,78,0.13),rgba(26,156,78,0.04));color:#1a9c4e;}
.si-blue{background:linear-gradient(135deg,rgba(37,99,235,0.13),rgba(37,99,235,0.04));color:#2563eb;}
.si-orange{background:linear-gradient(135deg,rgba(234,88,12,0.13),rgba(234,88,12,0.04));color:#ea580c;}
.stat-info .sv{font-family:'Cormorant Garamond',serif;font-size:3.4rem;font-weight:700;color:var(--ink);line-height:1;}
.stat-info .sl{font-size:1.2rem;color:var(--ink3);margin-top:0.3rem;font-weight:500;}
.stat-info .st{font-size:1.1rem;margin-top:0.4rem;font-weight:600;display:flex;align-items:center;gap:0.4rem;}
.st.up{color:#1a9c4e;}
.st.neu{color:var(--ink3);}

/* TOOLBAR */
.toolbar{display:flex;align-items:center;justify-content:space-between;gap:1.6rem;margin-bottom:2.4rem;flex-wrap:wrap;}
.srch-form{display:flex;align-items:center;gap:1rem;background:var(--white);border:1px solid var(--line);border-radius:99px;padding:1rem 2rem;box-shadow:var(--sh);flex:1;max-width:48rem;transition:all 0.2s;}
.srch-form:focus-within{border-color:rgba(214,40,40,0.35);box-shadow:0 0 0 3px rgba(214,40,40,0.07);}
.srch-form i{color:var(--ink3);font-size:1.4rem;}
.srch-form input{border:none;outline:none;font-size:1.4rem;color:var(--ink);background:transparent;flex:1;font-family:'Outfit',sans-serif;}
.srch-form input::placeholder{color:var(--ink3);}
.srch-form button{background:none;border:none;cursor:pointer;color:var(--r);font-size:1.4rem;}
.tool-btn{display:flex;align-items:center;gap:0.8rem;background:var(--white);border:1px solid var(--line);border-radius:99px;padding:1rem 1.8rem;font-size:1.35rem;font-weight:600;color:var(--ink3);cursor:pointer;box-shadow:var(--sh);transition:all 0.2s;font-family:'Outfit',sans-serif;}
.tool-btn:hover{border-color:rgba(214,40,40,0.25);color:var(--r);}

/* TABLE */
.tbl-wrap{background:var(--white);border-radius:2rem;border:1px solid var(--line);box-shadow:var(--sh);overflow:hidden;}
.tbl-head{display:grid;grid-template-columns:0.4fr 2.2fr 2.2fr 1.6fr 1fr 1.2fr 1.2fr;padding:1.6rem 2.8rem;background:linear-gradient(135deg,var(--rp),rgba(250,200,200,0.2));border-bottom:2px solid rgba(214,40,40,0.07);}
.tbl-head span{font-size:1.1rem;font-weight:800;color:var(--ink3);text-transform:uppercase;letter-spacing:0.12em;}
.tbl-row{display:grid;grid-template-columns:0.4fr 2.2fr 2.2fr 1.6fr 1fr 1.2fr 1.2fr;padding:1.8rem 2.8rem;border-bottom:1px solid var(--line);align-items:center;transition:all 0.22s var(--ease);position:relative;}
.tbl-row::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--r);transform:scaleY(0);transition:transform 0.22s;border-radius:0 3px 3px 0;}
.tbl-row:last-child{border-bottom:none;}
.tbl-row:hover{background:linear-gradient(90deg,var(--rp),rgba(253,241,241,0.3));}
.tbl-row:hover::before{transform:scaleY(1);}
.sr-no{font-size:1.3rem;font-weight:700;color:rgba(214,40,40,0.2);}
.u-avatar{display:flex;align-items:center;gap:1.4rem;}
.u-av{width:4.4rem;height:4.4rem;border-radius:50%;display:grid;place-items:center;color:#fff;font-size:1.6rem;font-weight:800;flex-shrink:0;box-shadow:0 4px 14px rgba(0,0,0,0.12);transition:transform 0.2s;}
.tbl-row:hover .u-av{transform:scale(1.1);}
.u-name{font-size:1.45rem;font-weight:700;color:var(--ink);}
.u-id{font-size:1.1rem;color:var(--ink3);font-weight:500;}
.tbl-cell{font-size:1.35rem;color:var(--ink3);}
.tbl-cell a{color:var(--ink3);text-decoration:none;transition:color 0.2s;}
.tbl-cell a:hover{color:var(--r);}
.prop-badge{display:inline-flex;align-items:center;gap:0.5rem;padding:0.5rem 1.2rem;border-radius:99px;font-size:1.2rem;font-weight:700;background:rgba(214,40,40,0.07);color:var(--r);}
.prop-badge.zero{background:rgba(150,150,150,0.07);color:#bbb;}
.date-badge{display:inline-flex;align-items:center;gap:0.5rem;background:var(--rp);color:var(--ink3);font-size:1.2rem;padding:0.5rem 1.1rem;border-radius:99px;font-weight:500;}
.act-btns{display:flex;gap:0.6rem;}
.act-btn{width:3.4rem;height:3.4rem;border-radius:0.9rem;display:grid;place-items:center;font-size:1.3rem;cursor:pointer;transition:all 0.2s var(--ease);border:1px solid transparent;}
.act-btn.view{background:var(--rp);color:var(--r);border-color:rgba(214,40,40,0.1);}
.act-btn.view:hover{background:var(--r);color:#fff;transform:translateY(-3px);box-shadow:0 6px 14px rgba(214,40,40,0.25);}
.act-btn.del{background:rgba(214,40,40,0.05);color:var(--r);}
.act-btn.del:hover{background:rgba(214,40,40,0.15);transform:translateY(-3px);}

.empty-state{text-align:center;padding:7rem 2rem;}
.empty-state i{font-size:6rem;color:rgba(214,40,40,0.12);margin-bottom:2rem;display:block;}
.empty-state h3{font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:700;color:var(--ink);margin-bottom:0.8rem;}
.empty-state p{font-size:1.4rem;color:var(--ink3);}

/* FOOTER */
.dash-foot{border-top:1px solid var(--line);padding:1.8rem 3.2rem;background:var(--white);display:flex;align-items:center;justify-content:space-between;margin-top:2.4rem;border-radius:1.6rem;box-shadow:var(--sh);}
.fl{font-size:1.3rem;color:var(--ink3);}
.fl b{color:var(--r);}

@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body class="dash-body">

<?php include '../components/admin_header.php'; ?>

<!-- TOPBAR -->
<div class="dash-tb">
  <div class="tb-path">
    <span class="seg">EstateAdmin</span>
    <span class="sep">/</span>
    <span class="cur">Users</span>
  </div>
  <div class="tb-r">
    <a href="admins.php" class="tb-ic"><i class="fas fa-user-shield"></i></a>
    <a href="messages.php" class="tb-ic"><i class="fas fa-bell"></i></a>
    <a href="../update.php" class="tb-ic"><i class="fas fa-cog"></i></a>
  </div>
</div>

<div class="dash-wrap">

  <!-- PAGE HEADER -->
  <div class="pg-hd">
    <div>
      <div class="pg-title">All <em>Users</em></div>
      <div class="pg-sub">Manage registered users and their property listings</div>
    </div>
  </div>

  <!-- STATS -->
  <div class="stats-strip">
    <div class="stat-card active-filter" id="sc-all" onclick="filterTable('all',this)">
      <div class="stat-icon si-red"><i class="fas fa-users"></i></div>
      <div class="stat-info">
        <div class="sv"><?= $total_users; ?></div>
        <div class="sl">Total Users</div>
        <div class="st up"><i class="fas fa-arrow-up"></i> All registered</div>
      </div>
    </div>
    <div class="stat-card" onclick="filterTable('active',this)">
      <div class="stat-icon si-green"><i class="fas fa-user-check"></i></div>
      <div class="stat-info">
        <div class="sv"><?= $active_sellers; ?></div>
        <div class="sl">Active Sellers</div>
        <div class="st up"><i class="fas fa-arrow-up"></i> Have listings</div>
      </div>
    </div>
    <div class="stat-card" onclick="filterTable('props',this)">
      <div class="stat-icon si-blue"><i class="fas fa-building"></i></div>
      <div class="stat-info">
        <div class="sv"><?= $total_props; ?></div>
        <div class="sl">Properties Posted</div>
        <div class="st neu"><i class="fas fa-minus"></i> Total listings</div>
      </div>
    </div>
    <div class="stat-card" onclick="filterTable('new',this)">
      <div class="stat-icon si-orange"><i class="fas fa-user-plus"></i></div>
      <div class="stat-info">
        <div class="sv"><?= $new_users; ?></div>
        <div class="sl">New This Month</div>
        <div class="st up"><i class="fas fa-arrow-up"></i> <?= date('F Y'); ?></div>
      </div>
    </div>
  </div>

  <!-- TOOLBAR -->
  <div class="toolbar">
    <form action="" method="POST" style="flex:1;max-width:48rem;">
      <div class="srch-form">
        <i class="fas fa-search"></i>
        <input type="text" name="search_box" placeholder="Search by name, email, phone..." maxlength="100">
        <button type="submit" name="search_btn"><i class="fas fa-arrow-right"></i></button>
      </div>
    </form>
    <div style="display:flex;gap:1rem;">
      <button class="tool-btn" onclick="filterTable('all',document.getElementById('sc-all'))"><i class="fas fa-sync-alt"></i> Reset</button>
    </div>
  </div>

  <!-- TABLE -->
  <div class="tbl-wrap">
    <div class="tbl-head">
      <span>#</span>
      <span>User</span>
      <span>Email</span>
      <span>Phone</span>
      <span>Properties</span>
      <span>Actions</span>
      <span>Delete</span>
    </div>

    <div id="tbl-body">
    <?php
      $row_count = 0;
      if($select_users->rowCount() > 0){
        while($fetch_users = $select_users->fetch(PDO::FETCH_ASSOC)){
          $count_property = $conn->prepare("SELECT COUNT(*) FROM `property` WHERE user_id = ?");
          $count_property->execute([$fetch_users['id']]);
          $total_properties = $count_property->fetchColumn();
          $row_count++;
          $color = $avatar_colors[($fetch_users['id'] - 1) % count($avatar_colors)];
          $initial = strtoupper(substr($fetch_users['name'], 0, 1));
          $delay = $row_count * 0.04;
          $has_props = $total_properties > 0 ? 'has-props' : 'no-props';
    ?>
      <div class="tbl-row <?= $has_props; ?>" style="animation:fadeUp 0.4s var(--ease) <?= $delay; ?>s both;">
        <div class="sr-no"><?= str_pad($row_count, 2, '0', STR_PAD_LEFT); ?></div>
        <div class="u-avatar">
          <div class="u-av" style="background:linear-gradient(135deg,<?= $color; ?>,<?= $color; ?>cc);"><?= $initial; ?></div>
          <div>
            <div class="u-name"><?= htmlspecialchars($fetch_users['name']); ?></div>
            <div class="u-id">#USR<?= str_pad($fetch_users['id'], 3, '0', STR_PAD_LEFT); ?></div>
          </div>
        </div>
        <div class="tbl-cell"><a href="mailto:<?= $fetch_users['email']; ?>"><?= htmlspecialchars($fetch_users['email']); ?></a></div>
        <div class="tbl-cell"><a href="tel:<?= $fetch_users['number']; ?>"><?= htmlspecialchars($fetch_users['number']); ?></a></div>
        <div class="tbl-cell">
          <span class="prop-badge <?= $total_properties == 0 ? 'zero' : ''; ?>">
            <i class="fas fa-home"></i> <?= $total_properties; ?>
          </span>
        </div>
        <div class="tbl-cell">
          <a href="listings.php?user=<?= $fetch_users['id']; ?>" class="act-btn view" title="View Listings"><i class="fas fa-eye"></i></a>
        </div>
        <div class="act-btns">
          <form action="" method="POST">
            <input type="hidden" name="delete_id" value="<?= $fetch_users['id']; ?>">
            <button type="submit" name="delete" class="act-btn del" title="Delete User" onclick="return confirm('Delete this user and all their listings?');"><i class="fas fa-trash-alt"></i></button>
          </form>
        </div>
      </div>
    <?php
        }
      } else { ?>
      <div class="empty-state">
        <i class="fas fa-users"></i>
        <h3>No Users Found</h3>
        <p><?= isset($_POST['search_box']) ? 'No results for your search.' : 'No user accounts added yet!'; ?></p>
      </div>
    <?php } ?>
    </div>
  </div>

  <!-- FOOTER -->
  <div class="dash-foot">
    <div class="fl">Showing <b id="showing"><?= $row_count; ?></b> of <b><?= $total_users; ?></b> users</div>
  </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>
<script>
function filterTable(type, el){
  document.querySelectorAll('.stat-card').forEach(c=>c.classList.remove('active-filter'));
  el.classList.add('active-filter');
  const rows = document.querySelectorAll('.tbl-row');
  let visible = 0;
  rows.forEach(r=>{
    let show = false;
    if(type==='all' || type==='props') show = true;
    else if(type==='active') show = r.classList.contains('has-props');
    else if(type==='new') show = true;
    r.style.display = show ? '' : 'none';
    if(show) visible++;
  });
  document.getElementById('showing').textContent = visible;
}
</script>
<?php include '../components/message.php'; ?>
</body>
</html>