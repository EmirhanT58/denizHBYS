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

if (!$id) {
    die("Rapor ID belirtilmedi.");
}

// Veritabanından hasta bilgileri ve doktor bilgilerini çekiyoruz
$sorgu = $db->prepare("
    SELECT 
        raporlar.*, 
        hastalar.tc AS hasta_tc,
        hastalar.ad AS hasta_ad,
        hastalar.soyad AS hasta_soyad,
        hastalar.dogum_tarihi,
        doktorlar.ad_soyad AS doktor_adsoyad,
        doktorlar.tc AS doktor_tc
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

// QR kodu verisi
$data = "Rapor No: " . $rapor['rapor_no'] . "\n";
$data .= "Hasta: " . $rapor['hasta_ad'] . " " . $rapor['hasta_soyad'] . "\n";
$data .= "TC: " . $rapor['hasta_tc'] . "\n";
$data .= "Doktor: " . $rapor['doktor_adsoyad'] . "\n";
$data .= "Tarih: " . $rapor['tarih'];

// QR kodu oluşturuluyor
$builder = new Builder(
    writer: new PngWriter(),
    writerOptions: [],
    validateResult: false,
    data: $data,
    encoding: new Encoding('UTF-8'),
    errorCorrectionLevel: ErrorCorrectionLevel::High,
    size: 300,
    margin: 10
);

$qrCode = $builder->build();  
$qrDataUri = $qrCode->getDataUri();

// Yaş hesaplama
$dogumTarihi = new DateTime($rapor['dogum_tarihi']);
$bugun = new DateTime();
$yas = $bugun->diff($dogumTarihi)->y;

// HTML içeriği oluştur
$html = "
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>{$rapor['rapor_tur']}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #333; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        .label { font-weight: bold; background-color: #f8f8f8; width: 30%; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; }
        .qr-code { position: absolute; bottom: 20px; right: 20px; }
        .signature { margin-top: 50px; text-align: right; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>{$rapor['rapor_tur']}</h1>
        <p>Rapor No: {$rapor['rapor_no']} - Tarih: {$rapor['tarih']}</p>
    </div>

    <table>
        <tr>
            <td class='label'>Hasta TC No</td>
            <td>{$rapor['hasta_tc']}</td>
        </tr>
        <tr>
            <td class='label'>Hasta Adı Soyadı</td>
            <td>{$rapor['hasta_ad']} {$rapor['hasta_soyad']} ({$yas} yaş)</td>
        </tr>
        <tr>
            <td class='label'>Doktor Adı Soyadı</td>
            <td>{$rapor['doktor_adsoyad']}</td>
        </tr>
        <tr>
            <td class='label'>Rapor Türü</td>
            <td>{$rapor['rapor_tur']}</td>
        </tr>
    </table>

    <h3>Tahlil Sonuçları:</h3>
    <p>{$rapor['tahlil_sonuclari']}</p>

    <h3>Hekim Görüşü:</h3>
    <p>{$rapor['hekim_gor']}</p>

    <div class='signature'>
        <p>Doktor İmzası</p>
        <p>                 </p>
    </div>

    <div class='qr-code'>
        <img src='{$qrDataUri}' width='100' height='100'>
        <p style='font-size:10px; text-align:center;'>Rapor Doğrulama Kodu</p>
    </div>

    <div class='footer'>
        <p>Bu rapor {$rapor['tarih']} tarihinde düzenlenmiştir.</p>
        <p>© " . date('Y') . " Hastane Adı - Tüm hakları saklıdır.</p>
    </div>
</body>
</html>
";

// Dompdf nesnesi oluştur
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// PDF dosyasını indirmeye başlat
$filename = "rapor_{$rapor['hasta_ad']}_{$rapor['hasta_soyad']}_{$rapor['rapor_no']}.pdf";
$dompdf->stream($filename, ["Attachment" => false]);

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>