<?php
session_start();

// Veritabanı bağlantısı
require_once '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $tc = trim($_POST['username']);
    $sifre = $_POST['password'];
    
    try {
        // Hasta sorgulama
        $query = $db->prepare("SELECT * FROM hastalar WHERE tc = ?");
        $query->execute([$tc]);
        $hasta = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($hasta && password_verify($sifre, $hasta['sifre'])) {
            // Giriş başarılı
            $_SESSION['hasta_id'] = $hasta['id'];
            $_SESSION['hasta_tc'] = $hasta['tc_kimlik'];
            $_SESSION['hasta_adi'] = $hasta['ad'] . ' ' . $hasta['soyad'];
            
            header('Location: hasta_panel.php');
            exit;
        } else {
            $_SESSION['error'] = 'TC Kimlik No veya şifre hatalı!';
            header('Location: hasta_login.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Giriş sırasında hata oluştu: ' . $e->getMessage();
        header('Location: hasta_login.php');
        exit;
    }
}
?>