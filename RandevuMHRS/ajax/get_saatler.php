<?php
require_once "../../database/db.php";

// Hata ayıklama modu
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Gelen parametreleri al
$doktor_id = (int)($_GET['doktor_id'] ?? 0);
$tarih = $_GET['tarih'] ?? '';
$klinik_id = (int)($_GET['klinik_id'] ?? 0);

// Parametre kontrolü
if ($doktor_id <= 0 || $klinik_id <= 0 || empty($tarih)) {
    die('<option value="">Geçersiz parametreler</option>');
}

// Tarih format kontrolü
try {
    $tarihObj = new DateTime($tarih);
    $tarihFormatted = $tarihObj->format('Y-m-d');
} catch (Exception $e) {
    die('<option value="">Geçersiz tarih formatı</option>');
}

try {
    // 1. Doktorun çalışma saatlerini al (varsayılan 09:00-17:00)
    $calismaSaatleri = [
        'baslangic' => '09:00',
        'bitis' => '17:00',
        'mola_baslangic' => '12:30',
        'mola_bitis' => '13:30'
    ];

    // 2. Dolu randevu saatlerini al
    $randevuSorgu = $db->prepare("
        SELECT TIME(tarih) AS saat
        FROM randevular
        WHERE doktor_id = ?
        AND DATE(tarih) = ?
        AND durum != 'iptal'
    ");
    $randevuSorgu->execute([$doktor_id, $tarihFormatted]);
    $doluSaatler = $randevuSorgu->fetchAll(PDO::FETCH_COLUMN, 0);

    // 3. 15 dakikalık aralıklarla tüm saatleri oluştur
    $baslangic = new DateTime($calismaSaatleri['baslangic']);
    $bitis = new DateTime($calismaSaatleri['bitis']);
    $interval = new DateInterval('PT15M'); // 15 dakikalık aralık
    $period = new DatePeriod($baslangic, $interval, $bitis);

    // 4. Müsait saatleri filtrele
    $musaitSaatler = [];
    foreach ($period as $dt) {
        $saat = $dt->format('H:i');
        
        // Mola saatlerini atla
        if ($calismaSaatleri['mola_baslangic'] && $calismaSaatleri['mola_bitis']) {
            $saatObj = new DateTime($saat);
            $molaBaslangic = new DateTime($calismaSaatleri['mola_baslangic']);
            $molaBitis = new DateTime($calismaSaatleri['mola_bitis']);
            
            if ($saatObj >= $molaBaslangic && $saatObj < $molaBitis) {
                continue;
            }
        }
        
        // Dolu saatleri atla
        if (!in_array($saat, $doluSaatler)) {
            $musaitSaatler[] = $saat;
        }
    }

    // 5. Sonuçları döndür
    if (empty($musaitSaatler)) {
        echo '<option value="">Müsait saat bulunamadı</option>';
    } else {
        echo '<option value="">Saat Seçiniz</option>';
        foreach ($musaitSaatler as $saat) {
            echo '<option value="'.htmlspecialchars($saat).'">'.htmlspecialchars($saat).'</option>';
        }
    }

} catch (PDOException $e) {
    echo '<option value="">Veritabanı hatası</option>';
} catch (Exception $e) {
    echo '<option value="">Sistem hatası</option>';
}
?>