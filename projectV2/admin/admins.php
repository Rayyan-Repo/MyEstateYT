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
   $verify_delete = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
   $verify_delete->execute([$delete_id]);
   if($verify_delete->rowCount() > 0){
      $delete_admin = $conn->prepare("DELETE FROM `admins` WHERE id = ?");
      $delete_admin->execute([$delete_id]);
      $success_msg[] = 'Admin removed successfully!';
   }else{
      $warning_msg[] = 'Admin already deleted!';
   }
}

// counts
$count_total = $conn->prepare("SELECT COUNT(*) FROM `admins`");
$count_total->execute();
$total_admins = $count_total->fetchColumn();

// current admin info
$select_current = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
$select_current->execute([$admin_id]);
$current_admin = $select_current->fetch(PDO::FETCH_ASSOC);

// all other admins
if(isset($_POST['search_btn'])){
   $search_box = filter_var($_POST['search_box'], FILTER_SANITIZE_STRING);
   $select_admins = $conn->prepare("SELECT * FROM `admins` WHERE name LIKE '%{$search_box}%' AND id != ?");
   $select_admins->execute([$admin_id]);
}else{
   $select_admins = $conn->prepare("SELECT * FROM `admins` WHERE id != ?");
   $select_admins->execute([$admin_id]);
}

$other_count = $select_admins->rowCount();

$avatar_colors = ['#2563eb','#1a9c4e','#ea580c','#7c3aed','#0891b2','#be185d','#ca8a04','#059669'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admins — EstateAdmin</title>
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
  --gold:#f59e0b;
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
.add-btn{display:flex;align-items:center;gap:0.8rem;background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;border:none;border-radius:99px;padding:1.2rem 2.4rem;font-size:1.4rem;font-weight:700;cursor:pointer;box-shadow:0 4px 16px rgba(214,40,40,0.22);transition:all 0.2s;text-decoration:none;}
.add-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(214,40,40,0.3);}

/* STATS */
.stats-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:1.6rem;margin-bottom:2.8rem;}
.stat-card{background:var(--white);border:1.5px solid var(--line);border-radius:1.8rem;padding:2.2rem 2.4rem;box-shadow:var(--sh);display:flex;align-items:center;gap:1.6rem;transition:all 0.3s var(--ease);}
.stat-card:hover{transform:translateY(-5px);box-shadow:var(--sh2);}
.stat-icon{width:5.5rem;height:5.5rem;border-radius:1.4rem;display:grid;place-items:center;font-size:2.2rem;flex-shrink:0;transition:transform 0.3s;}
.stat-card:hover .stat-icon{transform:scale(1.1) rotate(-5deg);}
.si-red{background:linear-gradient(135deg,rgba(214,40,40,0.13),rgba(214,40,40,0.04));color:var(--r);}
.si-gold{background:linear-gradient(135deg,rgba(245,158,11,0.15),rgba(245,158,11,0.05));color:var(--gold);}
.si-green{background:linear-gradient(135deg,rgba(26,156,78,0.13),rgba(26,156,78,0.04));color:#1a9c4e;}
.stat-info .sv{font-family:'Cormorant Garamond',serif;font-size:3.4rem;font-weight:700;color:var(--ink);line-height:1;}
.stat-info .sl{font-size:1.2rem;color:var(--ink3);margin-top:0.3rem;}
.stat-info .st{font-size:1.1rem;margin-top:0.4rem;font-weight:600;color:#1a9c4e;display:flex;align-items:center;gap:0.4rem;}

/* CURRENT ADMIN HERO */
.cur-admin-card{background:linear-gradient(135deg,var(--r) 0%,var(--rd) 100%);border-radius:2rem;padding:2.8rem 3rem;display:flex;align-items:center;justify-content:space-between;margin-bottom:2.4rem;box-shadow:0 8px 32px rgba(214,40,40,0.3);position:relative;overflow:hidden;}
.cur-admin-card::before{content:'';position:absolute;top:-40%;right:-10%;width:28rem;height:28rem;border-radius:50%;background:rgba(255,255,255,0.06);pointer-events:none;}
.cur-admin-card::after{content:'';position:absolute;bottom:-50%;left:20%;width:20rem;height:20rem;border-radius:50%;background:rgba(255,255,255,0.04);pointer-events:none;}
.cac-left{display:flex;align-items:center;gap:2rem;z-index:1;}
.cac-av{width:6.5rem;height:6.5rem;border-radius:50%;background:rgba(255,255,255,0.2);border:3px solid rgba(255,255,255,0.4);display:grid;place-items:center;font-size:2.6rem;font-weight:800;color:#fff;backdrop-filter:blur(10px);}
.cac-name{font-family:'Cormorant Garamond',serif;font-size:2.6rem;font-weight:700;color:#fff;line-height:1;}
.cac-id{font-size:1.3rem;color:rgba(255,255,255,0.65);margin-top:0.3rem;}
.cac-badge{display:inline-flex;align-items:center;gap:0.5rem;background:rgba(255,255,255,0.2);color:#fff;font-size:1.2rem;font-weight:700;padding:0.5rem 1.2rem;border-radius:99px;margin-top:0.8rem;backdrop-filter:blur(10px);}
.cac-badge i{color:var(--gold);}
.cac-right{display:flex;gap:1.2rem;z-index:1;}
.cac-btn{display:flex;align-items:center;gap:0.8rem;padding:1.1rem 2rem;border-radius:99px;font-size:1.35rem;font-weight:700;cursor:pointer;transition:all 0.2s;text-decoration:none;border:none;}
.cac-btn.white{background:rgba(255,255,255,0.95);color:var(--r);}
.cac-btn.white:hover{background:#fff;transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,0.15);}
.cac-btn.outline{background:rgba(255,255,255,0.15);color:#fff;border:1.5px solid rgba(255,255,255,0.4);}
.cac-btn.outline:hover{background:rgba(255,255,255,0.25);transform:translateY(-2px);}

/* TOOLBAR */
.toolbar{display:flex;align-items:center;gap:1.6rem;margin-bottom:2.4rem;}
.srch-form{display:flex;align-items:center;gap:1rem;background:var(--white);border:1px solid var(--line);border-radius:99px;padding:1rem 2rem;box-shadow:var(--sh);flex:1;max-width:44rem;transition:all 0.2s;}
.srch-form:focus-within{border-color:rgba(214,40,40,0.35);}
.srch-form i{color:var(--ink3);font-size:1.4rem;}
.srch-form input{border:none;outline:none;font-size:1.4rem;color:var(--ink);background:transparent;flex:1;font-family:'Outfit',sans-serif;}
.srch-form input::placeholder{color:var(--ink3);}
.srch-form button{background:none;border:none;cursor:pointer;color:var(--r);font-size:1.4rem;}

/* ADMINS GRID */
.admins-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(32rem,1fr));gap:2rem;}

/* ADMIN CARD */
.admin-card{background:var(--white);border-radius:2rem;border:1.5px solid var(--line);box-shadow:var(--sh);padding:2.4rem;transition:all 0.28s var(--ease);position:relative;overflow:hidden;animation:fadeUp 0.5s var(--ease) both;}
.admin-card:hover{transform:translateY(-6px);box-shadow:var(--sh2);}
.admin-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--r),var(--rd));opacity:0;transition:opacity 0.25s;}
.admin-card:hover::before{opacity:1;}
.ac-top{display:flex;align-items:center;gap:1.6rem;margin-bottom:2rem;}
.ac-av{width:5.6rem;height:5.6rem;border-radius:50%;display:grid;place-items:center;font-size:2rem;font-weight:800;color:#fff;flex-shrink:0;box-shadow:0 4px 16px rgba(0,0,0,0.12);transition:transform 0.2s;}
.admin-card:hover .ac-av{transform:scale(1.08);}
.ac-name{font-size:1.6rem;font-weight:700;color:var(--ink);}
.ac-id{font-size:1.2rem;color:var(--ink3);margin-top:0.2rem;}
.ac-badges{display:flex;gap:0.6rem;margin-top:0.6rem;}
.badge{display:inline-flex;align-items:center;gap:0.4rem;padding:0.35rem 1rem;border-radius:99px;font-size:1.1rem;font-weight:700;}
.badge.admin-b{background:rgba(214,40,40,0.07);color:var(--r);border:1px solid rgba(214,40,40,0.12);}
.badge.active-b{background:rgba(26,156,78,0.08);color:#1a9c4e;border:1px solid rgba(26,156,78,0.15);}
.badge.active-b::before{content:'';width:0.5rem;height:0.5rem;border-radius:50%;background:#1a9c4e;display:block;}
.ac-divider{height:1px;background:var(--line);margin:1.6rem 0;}
.ac-info{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:2rem;}
.ai-label{font-size:1.1rem;color:var(--ink3);text-transform:uppercase;letter-spacing:0.08em;font-weight:700;margin-bottom:0.3rem;}
.ai-val{font-size:1.35rem;font-weight:600;color:var(--ink);}
.ac-actions{display:flex;gap:1rem;}
.ac-btn{flex:1;display:flex;align-items:center;justify-content:center;gap:0.7rem;padding:1.1rem;border-radius:1.1rem;font-size:1.3rem;font-weight:700;cursor:pointer;transition:all 0.2s;border:none;text-decoration:none;}
.ac-btn.update{background:var(--rp);color:var(--r);border:1px solid rgba(214,40,40,0.12);}
.ac-btn.update:hover{background:var(--r);color:#fff;}
.ac-btn.del{background:rgba(214,40,40,0.06);color:var(--r);border:1px solid rgba(214,40,40,0.1);}
.ac-btn.del:hover{background:rgba(214,40,40,0.16);}

.empty-state{text-align:center;padding:6rem 2rem;background:var(--white);border-radius:2rem;border:2px dashed rgba(214,40,40,0.12);}
.empty-state i{font-size:5rem;color:rgba(214,40,40,0.15);margin-bottom:1.6rem;display:block;}
.empty-state h3{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);}
.empty-state p{font-size:1.4rem;color:var(--ink3);margin-top:0.5rem;}

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
    <span class="seg">EstateAdmin</span><span class="sep">/</span><span class="cur">Admins</span>
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
      <div class="pg-title">Manage <em>Admins</em></div>
      <div class="pg-sub">Control admin access and platform permissions</div>
    </div>
    <a href="register.php" class="add-btn"><i class="fas fa-plus"></i> Add New Admin</a>
  </div>

  <!-- STATS -->
  <div class="stats-strip">
    <div class="stat-card">
      <div class="stat-icon si-red"><i class="fas fa-user-shield"></i></div>
      <div class="stat-info">
        <div class="sv"><?= $total_admins; ?></div>
        <div class="sl">Total Admins</div>
        <div class="st"><i class="fas fa-arrow-up"></i> All active</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-gold"><i class="fas fa-crown"></i></div>
      <div class="stat-info">
        <div class="sv">1</div>
        <div class="sl">Super Admin</div>
        <div class="st"><i class="fas fa-shield-alt"></i> Full access</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-green"><i class="fas fa-user-check"></i></div>
      <div class="stat-info">
        <div class="sv"><?= $total_admins - 1; ?></div>
        <div class="sl">Regular Admins</div>
        <div class="st"><i class="fas fa-check"></i> Active now</div>
      </div>
    </div>
  </div>

  <!-- CURRENT ADMIN HERO CARD -->
  <?php if($current_admin){ 
    $initial = strtoupper(substr($current_admin['name'], 0, 1));
  ?>
  <div class="cur-admin-card">
    <div class="cac-left">
      <div class="cac-av"><?= $initial; ?></div>
      <div>
        <div class="cac-name"><?= htmlspecialchars($current_admin['name']); ?></div>
        <div class="cac-id">#ADM<?= str_pad($current_admin['id'], 3, '0', STR_PAD_LEFT); ?> &nbsp;·&nbsp; Logged in now</div>
        <div class="cac-badge"><i class="fas fa-crown"></i> Super Admin &nbsp;·&nbsp; <i class="fas fa-circle" style="font-size:0.8rem;color:#4ade80;"></i> Active</div>
      </div>
    </div>
    <div class="cac-right">
      <a href="update.php" class="cac-btn white"><i class="fas fa-edit"></i> Update Account</a>
      <a href="register.php" class="cac-btn outline"><i class="fas fa-plus"></i> Register New</a>
    </div>
  </div>
  <?php } ?>

  <!-- TOOLBAR -->
  <div class="toolbar">
    <form action="" method="POST" style="flex:1;max-width:44rem;">
      <div class="srch-form">
        <i class="fas fa-search"></i>
        <input type="text" name="search_box" placeholder="Search admins by name..." maxlength="100">
        <button type="submit" name="search_btn"><i class="fas fa-arrow-right"></i></button>
      </div>
    </form>
  </div>

  <!-- ADMINS GRID -->
  <div class="admins-grid">
  <?php
    $idx = 0;
    if($select_admins->rowCount() > 0){
      while($fetch_admins = $select_admins->fetch(PDO::FETCH_ASSOC)){
        $color = $avatar_colors[$idx % count($avatar_colors)];
        $initial = strtoupper(substr($fetch_admins['name'], 0, 1));
        $delay = ($idx + 1) * 0.06;
        $idx++;
  ?>
    <div class="admin-card" style="animation-delay:<?= $delay; ?>s">
      <div class="ac-top">
        <div class="ac-av" style="background:linear-gradient(135deg,<?= $color; ?>,<?= $color; ?>cc);"><?= $initial; ?></div>
        <div>
          <div class="ac-name"><?= htmlspecialchars($fetch_admins['name']); ?></div>
          <div class="ac-id">#ADM<?= str_pad($fetch_admins['id'], 3, '0', STR_PAD_LEFT); ?></div>
          <div class="ac-badges">
            <span class="badge admin-b"><i class="fas fa-user-shield"></i> Admin</span>
            <span class="badge active-b"><i></i> Active</span>
          </div>
        </div>
      </div>
      <div class="ac-divider"></div>
      <div class="ac-info">
        <div><div class="ai-label">Access Level</div><div class="ai-val">Standard</div></div>
        <div><div class="ai-label">Manage</div><div class="ai-val">Listings, Users</div></div>
      </div>
      <div class="ac-actions">
        <a href="update.php" class="ac-btn update"><i class="fas fa-edit"></i> Update</a>
        <form action="" method="POST" style="flex:1;">
          <input type="hidden" name="delete_id" value="<?= $fetch_admins['id']; ?>">
          <button type="submit" name="delete" class="ac-btn del" style="width:100%;" onclick="return confirm('Remove this admin?');"><i class="fas fa-trash-alt"></i> Remove</button>
        </form>
      </div>
    </div>
  <?php 
      }
    } else { ?>
    <div class="empty-state" style="grid-column:1/-1;">
      <i class="fas fa-user-shield"></i>
      <h3>No Other Admins</h3>
      <p>You are the only admin. Add more admins using the button above.</p>
    </div>
  <?php } ?>
  </div>

  <!-- FOOTER -->
  <div class="dash-foot">
    <div class="fl">Total <b><?= $total_admins; ?></b> admin(s) on platform</div>
  </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>
<?php include '../components/message.php'; ?>

</body>
</html>