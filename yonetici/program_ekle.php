<?php
require_once "../database/db.php";
session_start();

// Oturum kontrolü
if (!isset($_SESSION["yonetici_id"])) {
    header("Location: giris.php");
    exit;
}

// Form verilerini al
$doktor_id = $_POST['doktor_id'] ?? null;
$poliklinik_id = $_POST['poliklinik_id'] ?? null;
$program_turu = $_POST['program_turu'] ?? 'haftaici';
$mesai_turu = $_POST['mesai_turu'] ?? 'Normal Mesai';
$baslangic_saati = $_POST['baslangic_saati'] ?? null;
$bitis_saati = $_POST['bitis_saati'] ?? null;
$operasyon_turu = $_POST['operasyon_turu'] ?? 'Poliklinik';
$aktif = isset($_POST['aktif']) ? 1 : 0;

// Ameliyatlı mı kontrolü

// Hatalı giriş kontrolü
if (!$doktor_id || !$poliklinik_id || !$baslangic_saati || !$bitis_saati) {
    die("Eksik bilgi. Lütfen tüm alanları doldurun.");
}

// Veritabanına ekleme işlemi
$query = $db->prepare("INSERT INTO doktor_programlari 
    (doktor_id, poliklinik_id, program_turu, mesai_turu, nerde ,baslangic_saati, bitis_saati, aktif) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$insert = $query->execute([
  $doktor_id,
  $poliklinik_id,
   $program_turu,
   $mesai_turu,
   $operasyon_turu,
   $baslangic_saati,
   $bitis_saati,
    $aktif
]);

if ($insert) {
    header("Location: dashboard.php?sayfa=doktorcalismaprogrami&baslangic=$baslangic_saati&bitis=$bitis_saati&doktor_id=$doktor_id&poliklinik_id=$poliklinik_id");
    exit;
} else {
    echo "Kayıt sırasında bir hata oluştu.";
}
