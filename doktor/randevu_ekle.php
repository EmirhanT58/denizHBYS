<?php
require_once "../database/db.php";
session_start();

header('Content-Type: application/json');

// Oturum kontrolleri
if (!isset($_SESSION['doktor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim. Lütfen giriş yapın.']);
    exit();
}

if (!isset($_SESSION['pol_id'])) {
    echo json_encode(['success' => false, 'message' => 'Poliklinik bilgisi bulunamadı.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// Zorunlu alanlar
$requiredFields = ['hasta_id', 'tarih', 'saat', 'tur'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => 'Lütfen tüm zorunlu alanları doldurun.']);
        exit();
    }
}

try {
    $datetime = date('Y-m-d H:i:s', strtotime($data['tarih'] . ' ' . $data['saat']));
    $aciklama = isset($data['aciklama']) ? substr($data['aciklama'], 0, 256) : null;

    $stmt = $db->prepare("
        INSERT INTO randevular 
        (doktor_id, hasta_id, pol_id, tarih, tur, aciklama, durum, created_at) 
        VALUES 
        (:doktor_id, :hasta_id, :pol_id, :tarih, :tur, :aciklama, :durum, NOW())
    ");
    
    $stmt->execute([
        ':doktor_id' => $_SESSION['doktor_id'],
        ':hasta_id' => $data['hasta_id'],
        ':pol_id' => $_SESSION['pol_id'], // Oturumdan alıyoruz
        ':tarih' => $datetime,
        ':tur' => $data['tur'],
        ':aciklama' => $aciklama,
        ':durum' => 'Bekliyor'
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Randevu oluşturulamadı: ' . $e->getMessage()
    ]);
}