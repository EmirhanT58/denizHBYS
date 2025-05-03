<?php
include "../database/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);
    $hasta_id = intval($_POST["hasta_id"]);
    $doktor_id = intval($_POST["doktor_id"]);
    $rapor_tur = trim($_POST["rapor_tur"]);
    $icerik = trim($_POST["icerik"]);
    $tahlil_sonuclari = !empty($_POST["tahlil_sonuclari"]) ? trim($_POST["tahlil_sonuclari"]) : null;
    $recete = !empty($_POST["recete"]) ? trim($_POST["recete"]) : null;
    $durum = trim($_POST["durum"]);

    // SQL injection'a karşı güvenli güncelleme
    $stmt = $baglanti->prepare("
        UPDATE raporlar 
        SET 
            hasta_id = ?, 
            doktor_id = ?, 
            rapor_tur = ?, 
            icerik = ?, 
            tahlil_sonuclari = ?, 
            recete = ?, 
            durum = ?
        WHERE id = ?
    ");

    $stmt->bind_param("iisssssi", 
        $hasta_id, 
        $doktor_id, 
        $rapor_tur, 
        $icerik, 
        $tahlil_sonuclari, 
        $recete, 
        $durum, 
        $id
    );

    if ($stmt->execute()) {
        header("Location: dashboard.php?sayfa=raporlar&durum=guncellendi");
        exit;
    } else {
        echo "Hata oluştu: " . $stmt->error;
    }
}
?>
