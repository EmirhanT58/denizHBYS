<?php
require_once "../../database/db.php";

// Hata ayıklama modu
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Güvenlik kontrolü
$hastane_id = 1; // Varsayılan hastane ID'si

if ($hastane_id <= 0) {
    die('<option value="">Geçersiz hastane ID</option>');
}

try {
    // 1. Doğrudan poliklinikler tablosundan h_id'ye göre filtrele
    $query = $db->prepare("
        SELECT id, ad 
        FROM poliklinikler
        WHERE h_id = ?
        ORDER BY ad ASC
    ");
    
    $query->execute([$hastane_id]);
    
    if ($query->rowCount() > 0) {
        echo '<option value="">Klinik Seçiniz</option>';
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['ad']).'</option>';
        }
    } else {
        // 2. Eğer sonuç yoksa alternatif yol (doktorlar üzerinden)
        $query = $db->prepare("
            SELECT DISTINCT p.id, p.ad
            FROM poliklinikler p
            JOIN doktorlar d ON d.pol_id = p.id
            WHERE d.h_id = ?
            ORDER BY p.ad ASC
        ");
        $query->execute([$hastane_id]);
        
        if ($query->rowCount() > 0) {
            echo '<option value="">Klinik Seçiniz</option>';
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="'.$row['id'].'">'.htmlspecialchars($row['ad']).'</option>';
            }
        } else {
            echo '<option value="">Bu hastanede klinik bulunamadı</option>';
        }
    }

} catch (PDOException $e) {
    error_log("PDO Hatası: " . $e->getMessage());
    echo '<option value="">Veritabanı hatası: '.htmlspecialchars($e->getMessage()).'</option>';
}
?>