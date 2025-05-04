<?php
include '../database/db.php';  // Veritabanı bağlantısı

header('Content-Type: application/json');

// 'recete_id' parametresi var mı kontrol et
if (isset($_GET['recete_id'])) {
    $recete_id = $_GET['recete_id'];

    // Reçete detaylarını veritabanından çekelim
    $query = $pdo->prepare("
        SELECT 
            r.recete_id,
            r.hastane_id,
            r.pol_id,
            r.doktor_id,
            r.recete_turu,
            r.ilac_id,
            r.doz,
            r.periyod,
            r.k_sayisi,
            r.kutu_adet,
            r.aciklama,
            r.ol_tarih,
            i.ilac_barkod,
            i.ilac_ad,
            h.h_ad AS hastane_ad,        -- Hastane adı
            b.brans_ad AS brans_ad,      -- Branş adı
            d.ad AS doktor_ad,          -- Doktor adı
            d.soyad AS doktor_soyad     -- Doktor soyadı
        FROM receteler r
        JOIN ilaclar i ON r.ilac_id = i.ilac_id
        JOIN hastaneler h ON r.hastane_id = h.h_id
        JOIN branslar b ON r.pol_id = b.brans_id
        JOIN doktor_bilgiler d ON r.doktor_id = d.id
        WHERE r.recete_id = ?");
    $query->execute([$recete_id]);
    $recete = $query->fetch(PDO::FETCH_ASSOC);

    // Eğer reçete varsa, JSON olarak döndür
    if ($recete) {
        echo json_encode($recete);
    } else {
        echo json_encode(['error' => 'Reçete bulunamadı']);
    }
} else {
    echo json_encode(['error' => 'Geçersiz reçete ID']);
}
?>
