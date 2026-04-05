<?php
include '../components/connect.php';
$admin_id = validate_admin_cookie($conn);
if(!$admin_id){ header('location:login.php'); exit(); }

// Handle status update
if(isset($_POST['update_status']) && !empty($_POST['booking_id']) && !empty($_POST['new_status'])){
    $bid = trim($_POST['booking_id']);
    $ns  = trim($_POST['new_status']);
    if(in_array($ns, ['pending','confirmed','cancelled'])){
        $conn->prepare("UPDATE requests SET status=? WHERE id=?")->execute([$ns, $bid]);
    }
    header('location:requests.php');
    exit();
}

// Fetch all bookings
$filter_status = isset($_GET['status']) && in_array($_GET['status'],['pending','confirmed','cancelled']) ? $_GET['status'] : '';

$where = $filter_status ? "WHERE r.status = '$filter_status'" : '';
$sel = $conn->prepare(
    "SELECT r.*, p.property_name, p.address, p.type, p.image_01
     FROM requests r
     LEFT JOIN property p ON r.property_id = p.id
     $where
     ORDER BY r.id DESC"
);
$sel->execute();
$all_bookings = $sel->fetchAll(PDO::FETCH_ASSOC);

$total_bk   = $conn->query("SELECT COUNT(*) FROM requests")->fetchColumn();
$pending_bk = $conn->query("SELECT COUNT(*) FROM requests WHERE status='pending'")->fetchColumn();
$conf_bk    = $conn->query("SELECT COUNT(*) FROM requests WHERE status='confirmed'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bookings — EstateAdmin</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<link rel="stylesheet" href="../css/admin_style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<style>
:root{--r:#d62828;--rd:#9e1c1c;--rp:#fdf1f1;--rp2:#fae6e6;--ink:#0d0202;--ink3:#9a6565;--bg:#f5eded;--white:#fff;--line:rgba(214,40,40,0.09);--sh:0 2px 24px rgba(214,40,40,0.07);--sh2:0 16px 56px rgba(214,40,40,0.14);--ease:cubic-bezier(.22,1,.36,1);}
*{font-family:'Outfit',sans-serif;box-sizing:border-box;}html{font-size:62.5%;}body{background:var(--bg);min-height:100vh;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-thumb{background:var(--rp2);border-radius:99px;}
.dash-body{background:var(--bg);min-height:100vh;}.dash-wrap{padding:3rem 3.2rem;}
.dash-tb{position:sticky;top:0;z-index:200;background:rgba(245,237,237,.88);backdrop-filter:blur(20px);border-bottom:1px solid var(--line);padding:1.4rem 3.2rem;display:flex;align-items:center;justify-content:space-between;}
.tb-path{display:flex;align-items:center;gap:1rem;font-size:1.3rem;}.tb-path .seg{color:var(--ink3);}.tb-path .sep{color:var(--ink3);opacity:.3;}.tb-path .cur{color:var(--ink);font-weight:700;}
.tb-r{display:flex;align-items:center;gap:1rem;}
.tb-ic{width:4rem;height:4rem;border-radius:50%;background:var(--white);border:1px solid var(--line);display:grid;place-items:center;cursor:pointer;color:var(--ink3);font-size:1.45rem;box-shadow:var(--sh);transition:all .2s;text-decoration:none;}
.tb-ic:hover{border-color:var(--r);color:var(--r);transform:translateY(-2px);}
/* STATS */
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:1.6rem;margin-bottom:2.8rem;}
.s-card{background:var(--white);border:1px solid var(--line);border-radius:1.6rem;padding:2rem 2.4rem;display:flex;align-items:center;gap:1.6rem;box-shadow:var(--sh);transition:all .25s;}
.s-card:hover{box-shadow:var(--sh2);transform:translateY(-3px);}
.s-ic{width:4.8rem;height:4.8rem;border-radius:1.2rem;display:grid;place-items:center;font-size:2rem;flex-shrink:0;}
.s-n{font-family:'Cormorant Garamond',serif;font-size:3.6rem;font-weight:700;color:var(--ink);line-height:1;}
.s-l{font-size:1.2rem;color:var(--ink3);}
/* FILTER TABS */
.ft-row{display:flex;gap:.8rem;margin-bottom:2.4rem;flex-wrap:wrap;}
.ft{padding:.75rem 1.8rem;border-radius:99px;font-size:1.25rem;font-weight:700;cursor:pointer;border:1.5px solid var(--line);background:var(--white);color:var(--ink3);transition:all .22s;text-decoration:none;}
.ft:hover{border-color:var(--r);color:var(--r);}
.ft.active{background:var(--r);color:#fff;border-color:var(--r);box-shadow:0 4px 16px rgba(214,40,40,.25);}
/* TABLE */
.panel{background:var(--white);border-radius:1.8rem;border:1px solid var(--line);box-shadow:var(--sh);overflow:hidden;}
.ph{display:flex;align-items:center;justify-content:space-between;padding:2rem 2.4rem;border-bottom:1px solid var(--line);}
.pt{font-family:'Cormorant Garamond',serif;font-size:2.2rem;font-weight:700;color:var(--ink);}
.ps{font-size:1.2rem;color:var(--ink3);}
table{width:100%;border-collapse:collapse;}
th{padding:1.1rem 1.8rem;text-align:left;font-size:1.05rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--ink3);border-bottom:1px solid var(--line);}
td{padding:1.3rem 1.8rem;font-size:1.3rem;color:var(--ink);border-bottom:1px solid var(--line);}
tr:last-child td{border:none;}
tr:hover td{background:var(--rp);}
.st-badge{padding:.4rem 1.2rem;border-radius:99px;font-size:1.05rem;font-weight:700;display:inline-block;}
.st-badge.pending{background:#fff8ee;color:#b07000;}
.st-badge.confirmed{background:#edfff4;color:#1a7a4e;}
.st-badge.cancelled{background:#fff5f5;color:#c0392b;}
.act-btn{padding:.55rem 1.3rem;border-radius:.9rem;font-size:1.15rem;font-weight:700;cursor:pointer;border:none;font-family:'Outfit',sans-serif;transition:all .2s;}
.act-btn.confirm{background:#edfff4;color:#1a7a4e;}
.act-btn.confirm:hover{background:#1a7a4e;color:#fff;}
.act-btn.cancel{background:#fff5f5;color:#c0392b;}
.act-btn.cancel:hover{background:#c0392b;color:#fff;}
.empty-state{text-align:center;padding:6rem;color:var(--ink3);font-size:1.4rem;}
.empty-state i{display:block;font-size:4rem;margin-bottom:1.5rem;opacity:.25;}
@media(max-width:900px){.stat-row{grid-template-columns:1fr 1fr;}table{font-size:1.15rem;}}
</style>
</head>
<body class="dash-body">

<?php include '../components/admin_header.php'; ?>

<div class="dash-tb">
  <div class="tb-path">
    <span class="seg">EstateAdmin</span><span class="sep">/</span><span class="cur">Bookings</span>
  </div>
  <div class="tb-r">
    <a href="listings.php" class="tb-ic"><i class="fas fa-building"></i></a>
    <a href="messages.php" class="tb-ic"><i class="fas fa-bell"></i></a>
    <a href="update.php" class="tb-ic"><i class="fas fa-cog"></i></a>
  </div>
</div>

<div class="dash-wrap">

  <!-- STATS -->
  <div class="stat-row">
    <div class="s-card">
      <div class="s-ic" style="background:var(--rp);color:var(--r);"><i class="fas fa-calendar-check"></i></div>
      <div><div class="s-n"><?= $total_bk ?></div><div class="s-l">Total Bookings</div></div>
    </div>
    <div class="s-card">
      <div class="s-ic" style="background:#fff8ee;color:#b07000;"><i class="fas fa-hourglass-half"></i></div>
      <div><div class="s-n" style="color:#b07000;"><?= $pending_bk ?></div><div class="s-l">Pending</div></div>
    </div>
    <div class="s-card">
      <div class="s-ic" style="background:#edfff4;color:#1a7a4e;"><i class="fas fa-check-circle"></i></div>
      <div><div class="s-n" style="color:#1a7a4e;"><?= $conf_bk ?></div><div class="s-l">Confirmed</div></div>
    </div>
  </div>

  <!-- FILTER TABS -->
  <div class="ft-row">
    <a href="requests.php" class="ft <?= !$filter_status ? 'active' : '' ?>">All (<?= $total_bk ?>)</a>
    <a href="requests.php?status=pending" class="ft <?= $filter_status==='pending' ? 'active' : '' ?>">Pending (<?= $pending_bk ?>)</a>
    <a href="requests.php?status=confirmed" class="ft <?= $filter_status==='confirmed' ? 'active' : '' ?>">Confirmed (<?= $conf_bk ?>)</a>
    <a href="requests.php?status=cancelled" class="ft <?= $filter_status==='cancelled' ? 'active' : '' ?>">Cancelled</a>
  </div>

  <!-- TABLE -->
  <div class="panel">
    <div class="ph">
      <div><div class="pt">Site Visit <?= $filter_status ? ucfirst($filter_status) : 'All' ?> Requests</div><div class="ps">Manage all user booking requests</div></div>
    </div>
    <?php if(empty($all_bookings)): ?>
    <div class="empty-state"><i class="fas fa-calendar-times"></i>No <?= $filter_status ?: '' ?> bookings found.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table>
      <thead><tr>
        <th>Visitor</th>
        <th>Property</th>
        <th>Visit Date</th>
        <th>Slot / Purpose</th>
        <th>Booked On</th>
        <th>Status</th>
        <th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach($all_bookings as $bk):
        $st = $bk['status'] ?? 'pending';
        $vd = !empty($bk['visit_date']) ? date('d M Y', strtotime($bk['visit_date'])) : date('d M Y', strtotime($bk['date']));
        $bd = date('d M Y', strtotime($bk['date']));
      ?>
      <tr>
        <td>
          <div style="font-weight:700;"><?= htmlspecialchars($bk['user_name'] ?: 'Unknown') ?></div>
          <div style="font-size:1.15rem;color:var(--ink3);"><?= htmlspecialchars($bk['user_phone'] ?: '—') ?></div>
          <div style="font-size:1.05rem;color:var(--ink3);"><?= htmlspecialchars($bk['user_email'] ?: '') ?></div>
        </td>
        <td>
          <div style="font-weight:600;max-width:18rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($bk['property_name'] ?: 'General Visit') ?></div>
          <div style="font-size:1.15rem;color:var(--ink3);"><?= htmlspecialchars($bk['address'] ?: '—') ?></div>
        </td>
        <td style="font-weight:600;"><?= $vd ?></td>
        <td>
          <div><?= htmlspecialchars($bk['time_slot'] ?: '—') ?></div>
          <div style="font-size:1.15rem;color:var(--ink3);"><?= htmlspecialchars($bk['purpose'] ?: '') ?></div>
        </td>
        <td style="color:var(--ink3);"><?= $bd ?></td>
        <td><span class="st-badge <?= $st ?>"><?= ucfirst($st) ?></span></td>
        <td>
          <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
            <?php if($st !== 'confirmed'): ?>
            <form method="POST" style="margin:0;">
              <input type="hidden" name="booking_id" value="<?= htmlspecialchars($bk['id']) ?>">
              <input type="hidden" name="new_status" value="confirmed">
              <button type="submit" name="update_status" class="act-btn confirm" onclick="return confirm('Confirm this booking?')"><i class="fas fa-check"></i> Confirm</button>
            </form>
            <?php endif; ?>
            <?php if($st !== 'cancelled'): ?>
            <form method="POST" style="margin:0;">
              <input type="hidden" name="booking_id" value="<?= htmlspecialchars($bk['id']) ?>">
              <input type="hidden" name="new_status" value="cancelled">
              <button type="submit" name="update_status" class="act-btn cancel" onclick="return confirm('Cancel this booking?')"><i class="fas fa-times"></i> Cancel</button>
            </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>
  </div>

</div>
<?php include '../components/message.php'; ?>
</body>
</html>
