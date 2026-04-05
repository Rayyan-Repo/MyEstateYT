<?php
include 'connect.php';
header('Content-Type: application/json');

$user_id = validate_user_cookie($conn);
if (!$user_id) {
    echo json_encode(['success' => false, 'msg' => 'Session expired. Please login again.']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request.']);
    exit();
}

$property_id = trim($_POST['property_id'] ?? '');
$visit_date  = trim($_POST['visit_date']  ?? '');
$time_slot   = trim($_POST['time_slot']   ?? '');
$purpose     = trim($_POST['purpose']     ?? '');
$notes       = trim($_POST['notes']       ?? '');
$vp_name     = trim($_POST['vp_name']     ?? '');
$vp_phone    = trim($_POST['vp_phone']    ?? '');
$vp_email    = trim($_POST['vp_email']    ?? '');

if (empty($visit_date) || empty($time_slot) || empty($purpose)) {
    echo json_encode(['success' => false, 'msg' => 'Please fill all required fields.']);
    exit();
}

// Get user data from DB
$sel_user = $conn->prepare("SELECT name, email, number FROM users WHERE id = ? LIMIT 1");
$sel_user->execute([$user_id]);
$user_data = $sel_user->fetch(PDO::FETCH_ASSOC);
$user_name  = !empty($vp_name)  ? $vp_name  : ($user_data['name']   ?? '');
$user_email = !empty($vp_email) ? $vp_email : ($user_data['email']  ?? '');
$user_phone = !empty($vp_phone) ? $vp_phone : ($user_data['number'] ?? '');

// Get property data
$property_name  = 'General Visit';
$property_owner = $user_id;
if (!empty($property_id)) {
    $sel_prop = $conn->prepare("SELECT property_name, user_id FROM property WHERE id = ? LIMIT 1");
    $sel_prop->execute([$property_id]);
    $prop_data = $sel_prop->fetch(PDO::FETCH_ASSOC);
    if ($prop_data) {
        $property_name  = $prop_data['property_name'];
        $property_owner = !empty($prop_data['user_id']) ? $prop_data['user_id'] : $user_id;
    }
}

// Save booking to DB
$booking_id = create_unique_id();
$ins = $conn->prepare(
    "INSERT INTO `requests`
     (id, property_id, sender, receiver, date, visit_date, time_slot, purpose, notes, status, user_name, user_email, user_phone)
     VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, 'pending', ?, ?, ?)"
);
$ok = $ins->execute([
    $booking_id, $property_id, $user_id, $property_owner,
    $visit_date, $time_slot, $purpose, $notes,
    $user_name, $user_email, $user_phone
]);

if (!$ok) {
    echo json_encode(['success' => false, 'msg' => 'Failed to save booking. Try again.']);
    exit();
}

// Send confirmation email (non-blocking — booking saved even if email fails)
$email_sent = false;
if (!empty($user_email)) {
    try {
        include_once 'mailer.php';
        $html = getBookingEmailTemplate($user_name, $property_name, $visit_date, $time_slot, $purpose);
        $email_sent = sendMail($user_email, $user_name, 'Visit Booking Confirmed — MyEstate', $html);
    } catch (Exception $e) {
        $email_sent = false;
    }
}

echo json_encode([
    'success'    => true,
    'msg'        => 'Booking confirmed!',
    'booking_id' => $booking_id,
    'email_sent' => $email_sent
]);
exit();
?>
