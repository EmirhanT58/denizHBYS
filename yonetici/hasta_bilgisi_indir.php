<?php
require '../vendor/autoload.php'; // dompdf autoload dosyası

use Dompdf\Dompdf;

session_start();
include '../database/db.php'; // veritabanı bağlantı dosyan

// Hasta ID'si GET ile alınıyor
$id = $_GET['hasta_id'] ?? null;

if (!$id) {
    die("Hasta ID belirtilmedi.");
}

// Veritabanından hasta bilgilerini çekiyoruz
$sorgu = $db->prepare("SELECT * FROM hastalar WHERE id = ?");
$sorgu->execute([$id]);
$hasta = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$hasta) {
    die("Hasta bulunamadı.");
}

// HTML içeriği hazırlıyoruz
$html = "
<style>
    body { font-family: DejaVu Sans, sans-serif; }
    h1 { text-align: center; color: #333; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    td { padding: 8px; border: 1px solid #ccc; }
    .label { font-weight: bold; background-color: #f8f8f8; }
</style>

<h1>Hasta Bilgisi Raporu</h1>
<table>
    <tr><td class='label'>ID</td><td>{$hasta['id']}</td></tr>
    <tr><td class='label'>TC Kimlik No</td><td>{$hasta['tc']}</td></tr>
    <tr><td class='label'>Ad</td><td>{$hasta['ad']}</td></tr>
    <tr><td class='label'>Soyad</td><td>{$hasta['soyad']}</td></tr>
    <tr><td class='label'>Cinsiyet</td><td>{$hasta['cinsiyet']}</td></tr>
    <tr><td class='label'>Doğum Tarihi</td><td>{$hasta['dogum_tarihi']}</td></tr>
    <tr><td class='label'>Telefon</td><td>{$hasta['telefon']}</td></tr>
    <tr><td class='label'>E-Mail</td><td>{$hasta['email']}</td></tr>
    <tr><td class='label'>Adres</td><td>{$hasta['adres']}</td></tr>
    <tr><td class='label'>Kan Grubu</td><td>{$hasta['kan_grubu']}</td></tr>
    <tr><td class='label'>Kayıt Tarihi</td><td>{$hasta['created_at']}</td></tr>
</table>

<p style='text-align:center; margin-top:50px;'>Bu rapor " . date('d/m/Y') . " tarihinde oluşturulmuştur.</p>
";

// dompdf nesnesi oluştur
$dompdf = new Dompdf();
$dompdf->loadHtml($html);

// A4 dikey yap
$dompdf->setPaper('A4', 'portrait');

// PDF oluştur
$dompdf->render();

// İndirme başlat
$filename = "hasta_raporu_{$hasta['ad']}_{$hasta['soyad']}.pdf";
$dompdf->stream($filename, ["Attachment" => true]);
?>
