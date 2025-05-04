<?php
require_once "../database/db.php";

header('Content-Type: application/json');

// Hata ayıklama modu
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Veritabanı bağlantı kontrolü
if (!$db) {
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı bağlantı hatası'
    ]);
    exit();
}

try {
    // Gelen verileri kontrol et ve temizle
    $hasta_id = filter_input(INPUT_POST, 'hasta_id', FILTER_VALIDATE_INT);
    $doktor_id = filter_input(INPUT_POST, 'doktor_id', FILTER_VALIDATE_INT);
    $tarih = isset($_POST['tarih']) ? trim($_POST['tarih']) : ''; // FILTER_SANITIZE_STRING yerine doğrudan trim
    $poliklinik_id = filter_input(INPUT_POST, 'poliklinik_id', FILTER_VALIDATE_INT);
    $hastane_id = filter_input(INPUT_POST, 'hastane_id', FILTER_VALIDATE_INT);

    // Gelen verileri logla
    error_log("Randevu Al POST Verileri: " . print_r($_POST, true));

    // Zorunlu alan kontrolü
    if (!$hasta_id || !$doktor_id || empty($tarih) || !$poliklinik_id || !$hastane_id) {
        throw new Exception("Eksik veya geçersiz bilgiler gönderildi. Lütfen tüm alanları doldurunuz.");
    }

    // Tarih format kontrolü (YYYY-MM-DD HH:MM)
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $tarih)) {
        throw new Exception("Geçersiz tarih formatı. Lütfen GG.AA.YYYY SS:DD formatında giriniz.");
    }

    // Aynı tarih ve doktor için randevu kontrolü
    $kontrolSorgu = $db->prepare("
        SELECT COUNT(*) FROM randevular 
        WHERE doktor_id = ? 
        AND tarih = ?
        AND durum != 'iptal'
    ");
    $kontrolSorgu->execute([$doktor_id, $tarih]);
    
    if ($kontrolSorgu->fetchColumn() > 0) {
        throw new Exception("Bu doktorun seçtiğiniz saatte başka bir randevusu bulunmaktadır.");
    }

    // Randevu kaydı (transaction kullanarak)
    $db->beginTransaction();

    try {
        $sorgu = $db->prepare("
            INSERT INTO randevular 
            (hasta_id, doktor_id, tarih, poliklinik_id, hastane_id, durum, olusturulma_tarihi)
            VALUES (:hasta_id, :doktor_id, :tarih, :poliklinik_id, :hastane_id, 'aktif', NOW())
        ");

        $sonuc = $sorgu->execute([
            ':hasta_id' => $hasta_id,
            ':doktor_id' => $doktor_id,
            ':tarih' => $tarih,
            ':poliklinik_id' => $poliklinik_id,
            ':hastane_id' => $hastane_id
        ]);

        if (!$sonuc) {
            throw new PDOException("Randevu kaydedilemedi");
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Randevunuz başarıyla alındı',
            'randevu_id' => $db->lastInsertId()
        ]);

    } catch (PDOException $e) {
        $db->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log("PDO Hatası: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası oluştu: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Hata: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>