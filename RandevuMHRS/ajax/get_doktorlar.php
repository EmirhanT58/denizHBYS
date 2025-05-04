<?php
require_once "../../database/db.php";

// Hata ayıklama modunu aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Gelen parametreleri al ve kontrol et
$klinik_id = isset($_GET['klinik_id']) ? (int)$_GET['klinik_id'] : 0;
$hastane_id = isset($_GET['hastane_id']) ? (int)$_GET['hastane_id'] : 0;

// Parametre kontrolü
if ($klinik_id <= 0 || $hastane_id <= 0) {
    die('<option value="">Geçersiz parametreler</option>');
}

try {
    // 1. Klinik ve hastane uyumunu kontrol et
    $kontrol = $db->prepare("
        SELECT 1 FROM poliklinikler 
        WHERE id = ? AND h_id = ?
    ");
    $kontrol->execute([$klinik_id, $hastane_id]);
    
    if ($kontrol->rowCount() === 0) {
        die('<option value="">Geçersiz klinik-hastane eşleşmesi</option>');
    }

    // 2. Doktorları getir
    $query = $db->prepare("
        SELECT d.id, d.ad_soyad AS doktor_adi
        FROM doktorlar d
        WHERE d.pol_id = ? AND d.h_id = ?
        ORDER BY d.ad_soyad ASC
    ");
    
    $query->execute([$klinik_id, $hastane_id]);
    
    if ($query->rowCount() > 0) {
        echo '<option value="">Doktor Seçiniz</option>';
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['doktor_adi']).'</option>';
        }
    } else {
        echo '<option value="">Bu klinikte doktor bulunamadı</option>';
    }

} catch (PDOException $e) {
    error_log("PDO Hatası: " . $e->getMessage());
    echo '<option value="">Veritabanı hatası: '.htmlspecialchars($e->getMessage()).'</option>';
}
?>