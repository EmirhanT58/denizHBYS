<?php
session_start();

// Veritabanı bağlantısı
require_once '../database/db.php';

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tc = trim($_POST['tc']);
    $ad = trim($_POST['ad']);
    $soyad = trim($_POST['soyad']);
    $dogum_tarihi = $_POST['dogum_tarihi'];
    $telefon = trim($_POST['telefon']);
    $eposta = trim($_POST['eposta']);
    $sifre = $_POST['sifre'];
    $sifre_tekrar = $_POST['sifre_tekrar'];

    // Validasyon
    if (empty($tc) || empty($ad) || empty($soyad) || empty($sifre)) {
        $_SESSION['error'] = 'Zorunlu alanları doldurunuz!';
    } elseif ($sifre !== $sifre_tekrar) {
        $_SESSION['error'] = 'Şifreler uyuşmuyor!';
    } else {
        try {
            // TC kontrolü
            $check = $db->prepare("SELECT id FROM hastalar WHERE tc = ?");
            $check->execute([$tc]);
            
            if ($check->rowCount() > 0) {
                $_SESSION['error'] = 'Bu TC Kimlik Numarası ile zaten kayıtlı!';
            } else {
                // Şifreyi hashle
                $hashed_password = password_hash($sifre, PASSWORD_DEFAULT);
                
                // Kayıt işlemi
                $insert = $db->prepare("INSERT INTO hastalar 
                                        (tc, ad, soyad, dogum_tarihi, telefon, email, sifre) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insert->execute([$tc, $ad, $soyad, $dogum_tarihi, $telefon, $eposta, $hashed_password]);
                
                // Veri ekleme başarılı ise
                if ($insert->rowCount() > 0) {
                    $_SESSION['success'] = 'Kayıt başarılı! Giriş yapabilirsiniz.';
                    header('Location: hasta_login.php');
                    exit;
                } else {
                    $_SESSION['error'] = 'Kayıt sırasında bir sorun oluştu. Lütfen tekrar deneyin.';
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Kayıt sırasında hata: ' . $e->getMessage();
        }
    }
}

// Hata mesajını gösterme
if (isset($_SESSION['error'])) {
    echo $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
