<?php
require_once "../database/db.php";
include "ipbilgisi.php";


$girisTarihi = date("d/m/Y H:i:s");




$sorgu = $db->query("SELECT COUNT(*) as aktif_sayi FROM randevular WHERE durum = 'onaylandı'");
$sonuc = $sorgu->fetch(PDO::FETCH_ASSOC);
$aktifRandevuSayisi = $sonuc['aktif_sayi'];
$aktif_randevular = $sorgu->fetchAll(PDO::FETCH_ASSOC);

$doktor_sayi = $db->query("SELECT COUNT(*) as aktif_sayi FROM doktorlar WHERE durum = 1");
$doktor = $doktor_sayi->fetch(PDO::FETCH_ASSOC);
$aktifDoktorSayisi = $doktor['aktif_sayi'];
$aktif_doktor = $doktor_sayi->fetchAll(PDO::FETCH_ASSOC);

$hasta_sayi = $db->query("SELECT COUNT(*) as aktif_sayi FROM hastalar");
$hasta = $hasta_sayi->fetch(PDO::FETCH_ASSOC);
$aktifHastaSayisi = $hasta['aktif_sayi'];
$aktif_hasta = $hasta_sayi->fetchAll(PDO::FETCH_ASSOC);

$hasta = $db->query("
SELECT r.tarih, r.aciklama, d.unvan , h.ad, h.soyad, r.durum, d.ad_soyad AS doktor_ad
FROM randevular r
INNER JOIN hastalar h ON r.hasta_id = h.id
INNER JOIN doktorlar d ON r.doktor_id = d.id
ORDER BY r.tarih DESC
LIMIT 3
");
$son_randevular = $hasta->fetchAll(PDO::FETCH_ASSOC);

$veri = $db->query("
    SELECT MONTH(tarih) AS ay, COUNT(*) AS adet
    FROM randevular
    WHERE YEAR(tarih) = YEAR(CURDATE())
    GROUP BY ay
    ORDER BY ay
");

$veriler = array_fill(1, 12, 0); // 12 ay için boş diziler

while ($row = $veri->fetch(PDO::FETCH_ASSOC)) {
    $veriler[(int)$row['ay']] = (int)$row['adet'];
}


?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Hoş Geldiniz, <?php echo htmlspecialchars($k_ad); ?></h2>
    <p class="text-gray-600"><?php echo "Son giriş: IP: " . $ip . " " ."Son İşlem Tarihi:" . $girisTarihi;

  ?></p>
</div>

<!-- İstatistik Kartları -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6 stat-card transition duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <i class="fas fa-user-injured text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Toplam Hasta</p>
                <p class="text-2xl font-bold text-gray-800"><?= $aktifHastaSayisi ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 stat-card transition duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <i class="fas fa-user-md text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Aktif Doktor</p>
                <p class="text-2xl font-bold text-gray-800"><?= $aktifDoktorSayisi ?></p>
            </div>
        </div>
        <div class="mt-4">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 stat-card transition duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                <i class="fas fa-calendar-check text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Aktif Randevu</p>
                <p class="text-2xl font-bold text-gray-800"><?= $aktifRandevuSayisi ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 stat-card transition duration-300">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                <i class="fas fa-file-invoice-dollar text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Aylık Gelir</p>
                <p class="text-2xl font-bold text-gray-800">₺245,390</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Son Randevular</h3>
        <a href="dashboard.php?sayfa=randevular" class="text-sm text-blue-600 hover:underline">Tümünü Gör</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hasta
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doktor
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($son_randevular as $randevu): ?>
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                        <?= $randevu['ad'] . ' ' . $randevu['soyad'] ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        <?= $randevu["unvan"]." ".$randevu['doktor_ad'] ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        <?= date("d/m/Y H:i", strtotime($randevu['tarih'])) ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <?php
                        $durum = strtolower($randevu['durum']);
                        $renk = 'bg-gray-100 text-gray-800';
                        if ($durum == 'onaylandı') $renk = 'bg-green-100 text-green-800';
                        elseif ($durum == 'iptal edildi') $renk = 'bg-red-100 text-red-800';
                        elseif ($durum == 'beklemede') $renk = 'bg-yellow-100 text-yellow-800';
                        ?>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $renk ?>">
                            <?= ucfirst($durum) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<!-- Sistem Durumu -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Sistem Durumu</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Veritabanı</p>
                    <p class="text-xl font-bold text-gray-800">Normal</p>
                </div>
                <div class="p-2 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: 75%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Kullanım: %75</p>
            </div>
        </div>
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Sunucu Yükü</p>
                    <p class="text-xl font-bold text-gray-800">Orta</p>
                </div>
                <div class="p-2 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-exclamation"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: 45%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">CPU: %45</p>
            </div>
        </div>
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Yedekleme</p>
                    <p class="text-xl font-bold text-gray-800">6 saat önce</p>
                </div>
                <div class="p-2 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-database"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: 100%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Sonraki: <?php echo date("H:i", strtotime("+18 hours")); ?></p>
            </div>
        </div>
    </div>
</div>
