<?php
include '../components/connect.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) { echo json_encode([]); exit(); }

$like = '%' . $q . '%';
$sel = $conn->prepare(
    "SELECT id, property_name, address, type, price
     FROM `property`
     WHERE property_name LIKE ? OR address LIKE ? OR type LIKE ?
     ORDER BY id DESC LIMIT 8"
);
$sel->execute([$like, $like, $like]);
echo json_encode($sel->fetchAll(PDO::FETCH_ASSOC));
?>
