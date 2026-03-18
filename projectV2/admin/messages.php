<?php

include '../components/connect.php';

if(isset($_COOKIE['admin_id'])){
   $admin_id = $_COOKIE['admin_id'];
}else{
   $admin_id = '';
   header('location:login.php');
}

if(isset($_POST['delete'])){
   $delete_id = $_POST['delete_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);
   $verify_delete = $conn->prepare("SELECT * FROM `messages` WHERE id = ?");
   $verify_delete->execute([$delete_id]);
   if($verify_delete->rowCount() > 0){
      $delete_msg = $conn->prepare("DELETE FROM `messages` WHERE id = ?");
      $delete_msg->execute([$delete_id]);
      $success_msg[] = 'Message deleted!';
   }else{
      $warning_msg[] = 'Message already deleted!';
   }
}

// counts
$count_total  = $conn->prepare("SELECT COUNT(*) FROM `messages`"); $count_total->execute();
$total_msgs   = $count_total->fetchColumn();

// fetch messages with property join
if(isset($_POST['search_btn'])){
   $search_box = filter_var($_POST['search_box'], FILTER_SANITIZE_STRING);
   $select_messages = $conn->prepare("
      SELECT m.*, p.property_name, p.type, p.price, p.image_01
      FROM `messages` m
      LEFT JOIN `property` p ON m.property_id = p.id
      WHERE m.name LIKE '%{$search_box}%'
      OR m.email LIKE '%{$search_box}%'
      OR m.number LIKE '%{$search_box}%'
   ");
   $select_messages->execute();
}else{
   $select_messages = $conn->prepare("
      SELECT m.*, p.property_name, p.type, p.price, p.image_01
      FROM `messages` m
      LEFT JOIN `property` p ON m.property_id = p.id
      ORDER BY m.id DESC
   ");
   $select_messages->execute();
}

$avatar_colors = ['#d62828','#2563eb','#7c3aed','#1a9c4e','#ea580c','#0891b2','#be185d','#ca8a04','#059669','#dc2626'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages — EstateAdmin</title>
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
*{font-family:'Outfit',sans-serif;box-sizing:border-box;margin:0;padding:0;}
html{font-size:62.5%;}
body{background:var(--bg);min-height:100vh;}
::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-thumb{background:var(--rp2);border-radius:99px;}

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
.stats-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:1.6rem;margin-bottom:2.8rem;}
.stat-card{background:var(--white);border:1.5px solid var(--line);border-radius:1.8rem;padding:2rem 2.2rem;box-shadow:var(--sh);display:flex;align-items:center;gap:1.4rem;transition:all 0.3s var(--ease);}
.stat-card:hover{transform:translateY(-5px);box-shadow:var(--sh2);}
.stat-icon{width:5rem;height:5rem;border-radius:1.2rem;display:grid;place-items:center;font-size:2rem;flex-shrink:0;transition:transform 0.3s;}
.stat-card:hover .stat-icon{transform:scale(1.1) rotate(-5deg);}
.si-red{background:linear-gradient(135deg,rgba(214,40,40,0.13),rgba(214,40,40,0.04));color:var(--r);}
.si-blue{background:linear-gradient(135deg,rgba(37,99,235,0.13),rgba(37,99,235,0.04));color:#2563eb;}
.si-green{background:linear-gradient(135deg,rgba(26,156,78,0.13),rgba(26,156,78,0.04));color:#1a9c4e;}
.stat-info .sv{font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:700;color:var(--ink);line-height:1;}
.stat-info .sl{font-size:1.2rem;color:var(--ink3);margin-top:0.2rem;}

/* TOOLBAR */
.toolbar{display:flex;align-items:center;justify-content:space-between;gap:1.6rem;margin-bottom:2.4rem;flex-wrap:wrap;}
.srch-form{display:flex;align-items:center;gap:1rem;background:var(--white);border:1px solid var(--line);border-radius:99px;padding:1rem 2rem;box-shadow:var(--sh);flex:1;max-width:44rem;transition:all 0.2s;}
.srch-form:focus-within{border-color:rgba(214,40,40,0.35);}
.srch-form i{color:var(--ink3);font-size:1.4rem;}
.srch-form input{border:none;outline:none;font-size:1.4rem;color:var(--ink);background:transparent;flex:1;font-family:'Outfit',sans-serif;}
.srch-form input::placeholder{color:var(--ink3);}
.srch-form button{background:none;border:none;cursor:pointer;color:var(--r);font-size:1.4rem;}

/* MESSAGES LIST */
.msg-list{display:flex;flex-direction:column;gap:1.6rem;}

/* MESSAGE CARD */
.msg-card{background:var(--white);border-radius:2rem;border:1.5px solid var(--line);box-shadow:var(--sh);overflow:hidden;transition:all 0.28s var(--ease);animation:fadeUp 0.4s var(--ease) both;}
.msg-card:hover{transform:translateY(-4px);box-shadow:var(--sh2);}

.msg-header{display:flex;align-items:center;justify-content:space-between;padding:1.8rem 2.4rem;cursor:pointer;gap:1.6rem;transition:background 0.2s;}
.msg-header:hover{background:var(--rp);}
.msg-hdr-left{display:flex;align-items:center;gap:1.6rem;flex:1;min-width:0;}
.msg-av{width:4.6rem;height:4.6rem;border-radius:50%;display:grid;place-items:center;font-size:1.7rem;font-weight:800;color:#fff;flex-shrink:0;box-shadow:0 4px 12px rgba(0,0,0,0.1);}
.msg-sender{flex:1;min-width:0;}
.msg-name{font-size:1.45rem;font-weight:700;color:var(--ink);}
.msg-contact{font-size:1.2rem;color:var(--ink3);margin-top:0.2rem;display:flex;align-items:center;gap:1.2rem;flex-wrap:wrap;}
.msg-contact a{color:var(--ink3);text-decoration:none;transition:color 0.2s;display:flex;align-items:center;gap:0.4rem;}
.msg-contact a:hover{color:var(--r);}
.msg-preview{font-size:1.3rem;color:var(--ink3);flex:2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding:0 2rem;}
.msg-hdr-right{display:flex;align-items:center;gap:1rem;flex-shrink:0;}
.msg-toggle{width:3.2rem;height:3.2rem;border-radius:0.8rem;background:var(--rp);border:1px solid rgba(214,40,40,0.1);display:grid;place-items:center;cursor:pointer;color:var(--r);font-size:1.2rem;transition:all 0.2s;}
.msg-toggle:hover{background:var(--r);color:#fff;}
.msg-toggle i{transition:transform 0.3s var(--ease);}

/* EXPANDED BODY */
.msg-body{display:none;border-top:1px solid var(--line);}
.msg-body.open{display:block;animation:fadeUp 0.3s var(--ease);}
.msg-body-inner{padding:2rem 2.4rem;display:grid;grid-template-columns:1fr auto;gap:2rem;align-items:start;}

.msg-text-wrap .msg-label{font-size:1.1rem;color:var(--ink3);text-transform:uppercase;letter-spacing:0.1em;font-weight:700;margin-bottom:0.8rem;}
.msg-text{font-size:1.4rem;color:var(--ink);line-height:1.8;background:var(--rp);padding:1.6rem 2rem;border-radius:1.2rem;border:1px solid var(--line);margin-bottom:1.6rem;}
.msg-actions{display:flex;gap:1rem;flex-wrap:wrap;}
.msg-btn{display:flex;align-items:center;gap:0.7rem;padding:1rem 1.8rem;border-radius:1rem;font-size:1.3rem;font-weight:600;cursor:pointer;transition:all 0.2s;border:none;text-decoration:none;font-family:'Outfit',sans-serif;}
.msg-btn.reply{background:linear-gradient(135deg,var(--r),var(--rd));color:#fff;box-shadow:0 4px 12px rgba(214,40,40,0.2);}
.msg-btn.reply:hover{transform:translateY(-2px);}
.msg-btn.email{background:rgba(37,99,235,0.08);color:#2563eb;border:1px solid rgba(37,99,235,0.12);}
.msg-btn.email:hover{background:#2563eb;color:#fff;}
.msg-btn.del{background:rgba(214,40,40,0.06);color:var(--r);border:1px solid rgba(214,40,40,0.1);}
.msg-btn.del:hover{background:rgba(214,40,40,0.15);}

/* PROPERTY TAG */
.prop-tag{background:var(--rp);border:1.5px solid rgba(214,40,40,0.12);border-radius:1.6rem;padding:1.4rem 1.8rem;min-width:22rem;max-width:26rem;flex-shrink:0;}
.prop-tag-label{font-size:1.1rem;color:var(--ink3);text-transform:uppercase;letter-spacing:0.1em;font-weight:700;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;}
.prop-tag-label i{color:var(--r);}
.prop-tag-inner{display:flex;align-items:center;gap:1.2rem;}
.prop-img{width:5rem;height:5rem;border-radius:1rem;object-fit:cover;flex-shrink:0;border:2px solid rgba(214,40,40,0.1);}
.prop-img-placeholder{width:5rem;height:5rem;border-radius:1rem;background:var(--rp2);display:grid;place-items:center;color:var(--r);font-size:1.8rem;flex-shrink:0;}
.prop-info .prop-name{font-size:1.3rem;font-weight:700;color:var(--ink);line-height:1.3;}
.prop-info .prop-type{font-size:1.1rem;color:var(--ink3);margin-top:0.3rem;text-transform:capitalize;}
.prop-info .prop-price{font-size:1.2rem;font-weight:700;color:var(--r);margin-top:0.3rem;}
.prop-view{display:inline-flex;align-items:center;gap:0.5rem;font-size:1.15rem;font-weight:600;color:var(--r);text-decoration:none;margin-top:0.8rem;transition:gap 0.2s;}
.prop-view:hover{gap:0.8rem;}

/* EMPTY */
.empty-state{text-align:center;padding:7rem 2rem;background:var(--white);border-radius:2rem;border:2px dashed rgba(214,40,40,0.12);}
.empty-state i{font-size:6rem;color:rgba(214,40,40,0.12);margin-bottom:2rem;display:block;}
.empty-state h3{font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--ink);}
.empty-state p{font-size:1.4rem;color:var(--ink3);margin-top:0.5rem;}

/* FOOTER */
.dash-foot{border-top:1px solid var(--line);padding:1.8rem 3.2rem;background:var(--white);display:flex;align-items:center;justify-content:space-between;margin-top:2.4rem;border-radius:1.6rem;box-shadow:var(--sh);}
.fl{font-size:1.3rem;color:var(--ink3);}
.fl b{color:var(--r);}

@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<!-- TOPBAR -->
<div class="dash-tb">
  <div class="tb-path">
    <span class="seg">EstateAdmin</span><span class="sep">/</span><span class="cur">Messages</span>
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
      <div class="pg-title">All <em>Messages</em></div>
      <div class="pg-sub">View and manage enquiries from potential buyers</div>
    </div>
  </div>

  <!-- STATS -->
  <div class="stats-strip">
    <div class="stat-card">
      <div class="stat-icon si-red"><i class="fas fa-envelope"></i></div>
      <div class="stat-info"><div class="sv"><?= $total_msgs; ?></div><div class="sl">Total Messages</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-blue"><i class="fas fa-building"></i></div>
      <?php
        $count_prop = $conn->prepare("SELECT COUNT(DISTINCT property_id) FROM `messages` WHERE property_id IS NOT NULL");
        $count_prop->execute();
        $prop_count = $count_prop->fetchColumn();
      ?>
      <div class="stat-info"><div class="sv"><?= $prop_count; ?></div><div class="sl">Properties Enquired</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-green"><i class="fas fa-users"></i></div>
      <?php
        $count_unique = $conn->prepare("SELECT COUNT(DISTINCT email) FROM `messages`");
        $count_unique->execute();
        $unique_count = $count_unique->fetchColumn();
      ?>
      <div class="stat-info"><div class="sv"><?= $unique_count; ?></div><div class="sl">Unique Senders</div></div>
    </div>
  </div>

  <!-- TOOLBAR -->
  <div class="toolbar">
    <form action="" method="POST" style="flex:1;max-width:44rem;">
      <div class="srch-form">
        <i class="fas fa-search"></i>
        <input type="text" name="search_box" placeholder="Search by name, email, phone..." maxlength="100">
        <button type="submit" name="search_btn"><i class="fas fa-arrow-right"></i></button>
      </div>
    </form>
  </div>

  <!-- MESSAGES LIST -->
  <div class="msg-list">
  <?php
    if($select_messages->rowCount() > 0){
      $idx = 0;
      while($m = $select_messages->fetch(PDO::FETCH_ASSOC)){
        $color = $avatar_colors[$idx % count($avatar_colors)];
        $initial = strtoupper(substr($m['name'], 0, 1));
        $delay = ($idx) * 0.05;
        $idx++;
        $preview = htmlspecialchars(substr($m['message'], 0, 80)) . '...';
        $has_prop = !empty($m['property_name']);
        // format price
        $price_str = '';
        if(!empty($m['price'])){
          $p = (int)$m['price'];
          if($p >= 10000000) $price_str = '₹' . round($p/10000000, 1) . ' Cr';
          elseif($p >= 100000) $price_str = '₹' . round($p/100000, 1) . ' L';
          else $price_str = '₹' . number_format($p);
        }
  ?>
    <div class="msg-card" style="animation-delay:<?= $delay; ?>s">
      <div class="msg-header" onclick="toggleMsg(this)">
        <div class="msg-hdr-left">
          <div class="msg-av" style="background:linear-gradient(135deg,<?= $color; ?>,<?= $color; ?>cc);"><?= $initial; ?></div>
          <div class="msg-sender">
            <div class="msg-name"><?= htmlspecialchars($m['name']); ?></div>
            <div class="msg-contact">
              <a href="mailto:<?= $m['email']; ?>"><i class="fas fa-envelope"></i> <?= htmlspecialchars($m['email']); ?></a>
              <a href="tel:<?= $m['number']; ?>"><i class="fas fa-phone"></i> <?= htmlspecialchars($m['number']); ?></a>
            </div>
          </div>
        </div>
        <div class="msg-preview"><?= $preview; ?></div>
        <div class="msg-hdr-right">
          <?php if($has_prop){ ?>
          <span style="display:flex;align-items:center;gap:0.5rem;font-size:1.2rem;background:var(--rp);color:var(--r);padding:0.5rem 1.2rem;border-radius:99px;border:1px solid rgba(214,40,40,0.12);font-weight:600;white-space:nowrap;">
            <i class="fas fa-building"></i> <?= htmlspecialchars(substr($m['property_name'],0,20)); ?>
          </span>
          <?php } ?>
          <button class="msg-toggle"><i class="fas fa-chevron-down"></i></button>
        </div>
      </div>

      <div class="msg-body">
        <div class="msg-body-inner">
          <div class="msg-text-wrap">
            <div class="msg-label"><i class="fas fa-comment-dots" style="color:var(--r);"></i> Message</div>
            <div class="msg-text"><?= htmlspecialchars($m['message']); ?></div>
            <div class="msg-actions">
              <a href="mailto:<?= $m['email']; ?>" class="msg-btn reply"><i class="fas fa-reply"></i> Reply</a>
              <a href="mailto:<?= $m['email']; ?>" class="msg-btn email"><i class="fas fa-envelope"></i> Email</a>
              <form action="" method="POST" style="display:inline;">
                <input type="hidden" name="delete_id" value="<?= $m['id']; ?>">
                <button type="submit" name="delete" class="msg-btn del" onclick="return confirm('Delete this message?');"><i class="fas fa-trash-alt"></i> Delete</button>
              </form>
            </div>
          </div>

          <?php if($has_prop){ ?>
          <div class="prop-tag">
            <div class="prop-tag-label"><i class="fas fa-map-marker-alt"></i> Enquired Property</div>
            <div class="prop-tag-inner">
              <?php if(!empty($m['image_01'])){ ?>
              <img src="../uploaded_files/<?= $m['image_01']; ?>" class="prop-img" alt="property">
              <?php } else { ?>
              <div class="prop-img-placeholder"><i class="fas fa-building"></i></div>
              <?php } ?>
              <div class="prop-info">
                <div class="prop-name"><?= htmlspecialchars($m['property_name']); ?></div>
                <div class="prop-type"><i class="fas fa-tag"></i> <?= ucfirst($m['type']); ?></div>
                <?php if($price_str){ ?><div class="prop-price"><?= $price_str; ?></div><?php } ?>
                <a href="view_property.php?id=<?= $m['property_id']; ?>" class="prop-view">View Listing <i class="fas fa-arrow-right"></i></a>
              </div>
            </div>
          </div>
          <?php } ?>

        </div>
      </div>
    </div>
  <?php
      }
    } else { ?>
    <div class="empty-state">
      <i class="fas fa-comment-slash"></i>
      <h3>No Messages Yet</h3>
      <p>When users send enquiries, they will appear here.</p>
    </div>
  <?php } ?>
  </div>

  <!-- FOOTER -->
  <div class="dash-foot">
    <div class="fl">Total <b><?= $total_msgs; ?></b> message(s) in inbox</div>
  </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script src="../js/admin_script.js"></script>
<script>
function toggleMsg(header){
  const body = header.nextElementSibling;
  const icon = header.querySelector('.msg-toggle i');
  body.classList.toggle('open');
  icon.style.transform = body.classList.contains('open') ? 'rotate(180deg)' : '';
}
</script>
<?php include '../components/message.php'; ?>

</body>
</html>