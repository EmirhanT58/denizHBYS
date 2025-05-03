<?php
session_start();

// Kullanıcı oturumu kontrolü
if (!isset($_SESSION["yonetici_id"]) || !isset($_SESSION["k_ad"])) {
    header("Location: giris.php");
    exit;
}

require_once "../database/db.php";

// Hata ayıklama modu
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Formdan gelen verileri alalım
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST verilerini kontrol et
    echo "<pre>POST Verileri:\n";
    print_r($_POST);
    echo "</pre>";
    
    // Veritabanına gönderilecek verileri alalım
    $rapor_id = $_POST['id'] ?? null;
    $rapor_tur = $_POST['rapor_tur'] ?? null;
    $icerik = $_POST['hek_gor'] ?? null;
    $tahil = $_POST['tahil_sonuclari'] ?? null;
    $durum = $_POST['durum'] ?? null;
    $tarih = $_POST['tarih'] ?? null;
    
    // Verilerin geçerliliğini kontrol edelim
    if (empty($rapor_id) || empty($rapor_tur) || empty($icerik) || empty($durum) || empty($tarih)) {
        $_SESSION['error'] = "Lütfen tüm alanları doldurun!";
        header("Location: dashboard.php?sayfa=raporlar");
        exit;
    }

    try {
        // Veritabanı işlemi
        $sql = $db->prepare("
            UPDATE raporlar 
            SET rapor_tur = :rapor_tur, 
                hekim_gor = :icerik,  
                tahlil_sonuclari = :tah_son,
                durum = :durum, 
                tarih = :tarih
            WHERE rapor_id = :rapor_id
        ");
        
        // Parametreleri bağla
        $sql->bindParam(':rapor_tur', $rapor_tur, PDO::PARAM_STR);
        $sql->bindParam(':icerik', $icerik, PDO::PARAM_STR);
        $sql->bindParam(':tah_son', $tahil, PDO::PARAM_STR);
        $sql->bindParam(':durum', $durum, PDO::PARAM_STR);
        $sql->bindParam(':tarih', $tarih, PDO::PARAM_STR);
        $sql->bindParam(':rapor_id', $rapor_id, PDO::PARAM_INT);

        // SQL sorgusunu çalıştır
        $result = $sql->execute();
        
        // Etkilenen satır sayısını kontrol et
        $rowCount = $sql->rowCount();
        
        if ($result && $rowCount > 0) {
            $_SESSION['success'] = "Rapor başarıyla güncellendi.";
        } else {
            $_SESSION['error'] = "Rapor güncellenemedi. Etkilenen satır yok veya bir hata oluştu.";
            // Hata detayını logla
            error_log("Güncelleme hatası: " . print_r($sql->errorInfo(), true));
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Hata: " . $e->getMessage();
        error_log("PDO Hatası: " . $e->getMessage());
    }
    
    // Yönlendirme işlemi
    header("Location: dashboard.php?sayfa=raporlar");
    exit;
}

// Eğer POST metodu ile gelinmediyse buraya düşer
header("Location: dashboard.php?sayfa=raporlar");
exit;
?>