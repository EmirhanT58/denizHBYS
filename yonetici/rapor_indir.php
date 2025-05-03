<?php
require '../vendor/autoload.php'; 

use Dompdf\Dompdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

include '../database/db.php';

// Hasta ID'si GET ile alınıyor
$id = $_GET['id'] ?? null;

// Veritabanından hasta bilgileri ve doktor bilgilerini çekiyoruz
$sorgu = $db->prepare("
    SELECT raporlar.*, hastalar.*, doktorlar.*
    FROM raporlar
    INNER JOIN hastalar ON raporlar.hasta_id = hastalar.id
    INNER JOIN doktorlar ON raporlar.doktor_id = doktorlar.id
    WHERE raporlar.rapor_id = ?
");
$sorgu->execute([$id]);
$rapor = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$rapor) {
    die("Rapor bulunamadı.");
}
    $data = "Rapor No: " . $rapor['rapor_no'];  // QR kodunun verisi olarak rapor_no ekleniyor

// QR kodu oluşturuluyor
$builder = new Builder(
    writer: new PngWriter(),
    writerOptions: [],
    validateResult: false,
    data: $data,
    encoding: new Encoding('UTF-8'),
    errorCorrectionLevel: ErrorCorrectionLevel::High,
    size: 300,  // QR kodunun boyutu
    margin: 10   // QR kodunun etrafındaki boşluk
);

// QR kodu nesnesini oluşturuyoruz
$qrCode = $builder->build();  

// QR kodunu base64 olarak alıyoruz
$qrDataUri = $qrCode->getDataUri(); 

// HTML içeriği oluştur
$html = "
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>{$rapor['rapor_tur']}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; }
        h1 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { padding: 8px; border: 1px solid #ccc; }
        .label { font-weight: bold; background-color: #f8f8f8; }
    </style>
</head>
<body>
    <h1>{$rapor['rapor_tur']}</h1>
    <table>
        <tr><td class='label'>Hasta TC</td><td>{$rapor['tc']}</td></tr>
        <tr><td class='label'>Rapor No</td><td>{$rapor['rapor_no']}</td></tr>
        <tr><td class='label'>Hasta Ad</td><td>{$rapor['ad']}</td></tr>
        <tr><td class='label'>Hasta Soyad</td><td>{$rapor['soyad']}</td></tr>
        <tr><td class='label'>Doktor Ad</td><td>{$rapor['ad_soyad']}</td></tr>
        <tr><td class='label'>Rapor Türü</td><td>{$rapor['rapor_tur']}</td></tr>
        <tr><td class='label'>Tahlil Sonuçları</td><td>{$rapor['tahlil_sonuclari']}</td></tr>
        <tr><td class='label'>Tarih</td><td>{$rapor['tarih']}</td></tr>
        <tr><td class='label'>Durum</td><td>{$rapor['durum']}</td></tr>
    </table>
    <div style='position: absolute; bottom: 20px; left: 20px;'>
        <img src='{$qrDataUri}' width='100' height='100'>
    </div>
    <p style='text-align:center; margin-top:50px;'>Bu rapor " . date('d/m/Y') . " tarihinde oluşturulmuştur.</p>
</body>
</html>
";

// Dompdf nesnesi oluştur
$dompdf = new Dompdf();
$dompdf->loadHtml($html);

// A4 boyutunda dikey sayfa
$dompdf->setPaper('A4', 'portrait');

// PDF'i oluştur
$dompdf->render();

// PDF dosyasını indirmeye başlat
$filename = "hasta_raporu_{$rapor['ad']}_{$rapor['soyad']}_{$rapor['rapor_no']}.pdf";
$dompdf->stream($filename, ["Attachment" => false]);

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);


?>
