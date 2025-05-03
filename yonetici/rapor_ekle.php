<?php
include "../database/db.php";

// Form gönderildiyse
if ($_SERVER["REQUEST_METHOD"] ?? '' === "POST") {
    $rapor_no = $_POST['rapor_no'] ?? null;
    $hasta_id = $_POST['hasta_id'] ?? null;
    $doktor_id = $_POST['doktor_id'] ?? null;
    $rapor_tur = $_POST['rapor_tur'] ?? null;
    $durum = $_POST['durum'] ?? 'Bekliyor';
    $hekim_gor = $_POST['hek_gor'] ?? null;
    $tarih = $_POST['tarih'] ?? null;

    if ($rapor_no && $hasta_id && $doktor_id && $rapor_tur && $tarih) {
        $stmt = $db->prepare("INSERT INTO raporlar (rapor_no, hasta_id, doktor_id, rapor_tur, tahlil_sonuclari, hekim_gor, durum, tarih)
                              VALUES (:rapor_no, :hasta_id, :doktor_id, :rapor_tur, :hekim_gor, :tah_son, :durum, :tarih)");
        $stmt->bindParam(':rapor_no', $rapor_no);
        $stmt->bindParam(':hasta_id', $hasta_id, PDO::PARAM_INT);
        $stmt->bindParam(':doktor_id', $doktor_id, PDO::PARAM_INT);
        $stmt->bindParam(':rapor_tur', $rapor_tur);
        $stmt->bindParam(':tah_son', $icerik);
        $stmt->bindParam(':hekim_gor', $hekim_gor);
        $stmt->bindParam(':durum', $durum);
        $stmt->bindParam(':tarih', $tarih);

        if ($stmt->execute()) {
            // Başarılı kayıt sonrası yönlendirme               
            header("Location: dashboard.php?sayfa=raporlar");
            exit;
        } else {
            echo "Kayıt eklenemedi.";
        }
    } else {
        echo "Tüm alanları doldurmanız gerekiyor.";
    }
} else {
    echo "Geçersiz istek.";
}

?>
