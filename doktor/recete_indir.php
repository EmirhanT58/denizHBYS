<?php
require '../vendor/autoload.php'; 

use Dompdf\Dompdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

include '../database/db.php';

// Reçete ID'si GET ile alınıyor
$recete_id = $_GET['id'] ?? null;

// Veritabanından reçete bilgilerini çekiyoruz
$sorgu = $db->prepare("
    SELECT 
        r.*,
        h.ad AS hasta_ad,
        h.soyad AS hasta_soyad,
        h.tc AS hasta_tc,
        d.ad_soyad AS doktor_adsoyad,
        i.ilac_ad,
        i.ilac_barkod,
        hs.h_ad AS hastane_ad,
        p.ad AS poliklinik_ad
    FROM receteler r
    INNER JOIN hastalar h ON r.hasta_id = h.id
    INNER JOIN doktorlar d ON r.doktor_id = d.id
    INNER JOIN ilaclar i ON r.ilac_id = i.ilac_id
    INNER JOIN hastaneler hs ON r.hastane_id = hs.h_id
    INNER JOIN poliklinikler p ON r.pol_id = p.id
    WHERE r.recete_id = ?
");
$sorgu->execute([$recete_id]);
$recete = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$recete) {
    die("Reçete bulunamadı.");
}

// QR kodu verisi olarak reçete bilgilerini ekliyoruz
$data = "Reçete No: " . $recete['recete_id'] . "\n";
$data .= "Hasta: " . $recete['hasta_ad'] . " " . $recete['hasta_soyad'] . "\n";
$data .= "Doktor: " . $recete['doktor_adsoyad'] . "\n";
$data .= "Tarih: " . $recete['ol_tarih'];

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

// HTML içeriği oluştur
$html = "
<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>İlaç Reçetesi</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #333; margin-bottom: 5px; }
        .header p { color: #666; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f8f8f8; font-weight: bold; }
        .patient-info, .doctor-info { width: 48%; display: inline-block; vertical-align: top; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
        .qr-code { position: absolute; bottom: 20px; right: 20px; }
        .signature { margin-top: 50px; text-align: right; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>{$recete['hastane_ad']}</h1>
        <p>{$recete['poliklinik_ad']} Polikliniği</p>
        <h2>İlaç Reçetesi</h2>
    </div>

    <div class='patient-info'>
        <h3>Hasta Bilgileri</h3>
        <p><strong>Ad Soyad:</strong> {$recete['hasta_ad']} {$recete['hasta_soyad']}</p>
        <p><strong>TC Kimlik No:</strong> {$recete['hasta_tc']}</p>
    </div>

    <div class='doctor-info'>
        <h3>Doktor Bilgileri</h3>
        <p><strong>Ad Soyad:</strong> {$recete['doktor_adsoyad']}</p>
        <p><strong>Tarih:</strong> {$recete['ol_tarih']}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Barkod</th>
                <th>İlaç Adı</th>
                <th>Doz</th>
                <th>Periyot</th>
                <th>Adet</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{$recete['ilac_barkod']}</td>
                <td>{$recete['ilac_ad']}</td>
                <td>{$recete['doz']}</td>
                <td>{$recete['periyod']}</td>
                <td>{$recete['kutu_adet']}</td>
            </tr>
        </tbody>
    </table>

    <div class='aciklama'>
        <h3>Açıklama</h3>
        <p>{$recete['aciklama']}</p>
    </div>

    <div class='signature'>
        <p>Doktor İmza Kaşesi</p>
        <p>                   </p>
    </div>

    <div class='qr-code'>
        <img src='{$qrDataUri}' width='100' height='100'>
    </div>

    <div class='footer'>
        <p>Bu reçete {$recete['ol_tarih']} tarihinde düzenlenmiştir.</p>
        <p>Reçete No: {$recete['recete_no']}</p>
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
$filename = "recete_{$recete['hasta_ad']}_{$recete['hasta_soyad']}_{$recete['recete_id']}.pdf";
$dompdf->stream($filename, ["Attachment" => false]);

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>