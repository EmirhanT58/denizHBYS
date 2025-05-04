<?php
session_start();
require_once("db.php");

if (!isset($_SESSION['hasta_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    echo json_encode(["hata" => "Yetkisiz erişim."]);
    exit();
}

$hasta_id = $_SESSION['hasta_id'];
$randevu_id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM randevular WHERE id = ? AND hasta_id = ?");
$stmt->execute([$randevu_id, $hasta_id]);
$randevu = $stmt->fetch(PDO::FETCH_ASSOC);

if ($randevu) {
    header('Content-Type: application/json');
    echo json_encode($randevu);
} else {
    http_response_code(404);
    echo json_encode(["hata" => "Randevu bulunamadı."]);
}
