<?php
session_start();
require_once "../../database/db.php";

// Hasta ID'sini oturumdan kontrol et
if (!isset($_SESSION['hasta_id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum bulunamadı']);
    exit();
}

$randevu_id = $_POST['randevu_id'] ?? null;

if (!$randevu_id) {
    echo json_encode(['success' => false, 'message' => 'Randevu ID belirtilmedi']);
    exit();
}

try {
    // Randevunun bu hastaya ait olduğunu kontrol et
    $check = $db->prepare("SELECT id FROM randevular WHERE id = ? AND hasta_id = ?");
    $check->execute([$randevu_id, $_SESSION['hasta_id']]);
    
    if ($check->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Randevu bulunamadı veya yetkiniz yok']);
        exit();
    }

    // Randevuyu iptal et
    $update = $db->prepare("UPDATE randevular SET durum = 'İptal Edildi' WHERE id = ?");
    $update->execute([$randevu_id]);
    
    echo json_encode(['success' => true, 'message' => 'Randevu iptal edildi']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>