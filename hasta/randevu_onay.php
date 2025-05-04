<?php
session_start();
include "../../database/db.php";

if (empty($_SESSION['hasta_id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum açılmamış']);
    exit();
}

$hasta_id = $_SESSION['hasta_id'];
$randevu_id = $_POST['randevu_id'] ?? 0;

try {
    // Önce randevunun durumunu kontrol et
    $check = $db->prepare("SELECT durum FROM randevular WHERE id = ? AND hasta_id = ?");
    $check->execute([$randevu_id, $hasta_id]);
    $randevu = $check->fetch(PDO::FETCH_ASSOC);

    if (!$randevu) {
        echo json_encode(['success' => false, 'message' => 'Randevu bulunamadı']);
        exit();
    }

    if ($randevu['durum'] != 'Beklimede') {
        echo json_encode(['success' => false, 'message' => 'Sadece bekleyen randevular onaylanabilir']);
        exit();
    }

    // Randevuyu onayla
    $update = $db->prepare("UPDATE randevular SET durum = 'Onaylandı', onay_tarihi = NOW() WHERE id = ?");
    $update->execute([$randevu_id]);

    echo json_encode(['success' => true, 'message' => 'Randevu başarıyla onaylandı']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
}
?>