<?php
require_once "../database/db.php";
session_start();

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Oturum kontrolü
if (!isset($_SESSION['doktor_id'])) {
    $_SESSION['error_message'] = 'Oturum açılmamış';
    header("Location: login.php");
    exit();
}
// Form verilerini al
$doktor_id = $_POST['doktor_id'] ?? $_SESSION['doktor_id'];
$hasta_id = $_POST['hasta_id'] ?? null;
$rapor_no = $_POST['rapor_no'] ?? null;
$rapor_tur = $_POST['rapor_tur'] ?? null;
$tarih = $_POST['tarih'] ?? date('Y-m-d');
$icerik = $_POST['icerik'] ?? null;
$durum = 'Beklemede'; // Varsayılan durum

// Doğrulama
$errors = [];
if (empty($hasta_id)) $errors[] = 'Hasta seçimi gereklidir';
if (empty($rapor_no)) $errors[] = 'Rapor no gereklidir';
if (empty($rapor_tur)) $errors[] = 'Rapor türü gereklidir';
if (empty($icerik)) $errors[] = 'Rapor içeriği gereklidir';

if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header("Location: raporlar.php?hata=1");
    exit();
}

try {
    // Raporu veritabanına ekle
    $stmt = $db->prepare("
        INSERT INTO raporlar 
        (doktor_id, hasta_id, rapor_no, rapor_tur, tarih, icerik, durum) 
        VALUES 
        (:doktor_id, :hasta_id, :rapor_no, :tur, :tarih, :icerik, :durum)
    ");
    
    $stmt->execute([
        ':doktor_id' => $doktor_id,
        ':hasta_id' => $hasta_id,
        ':rapor_no' => $rapor_no,
        ':tur' => $rapor_tur,
        ':tarih' => $tarih,
        ':icerik' => $icerik,
        ':durum' => $durum
    ]);
    
    $_SESSION['success_message'] = 'Rapor başarıyla oluşturuldu';
    header("Location: raporlar.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Rapor oluşturulurken bir hata oluştu: ' . $e->getMessage();
    header("Location: raporlar.php?hata=1");
    exit();
}