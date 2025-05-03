<?php
require_once "../database/db.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hata raporlamayı aç (geliştirme ortamında)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Oturum kontrolü - Sadece doktorlar erişebilir
if (!isset($_SESSION['doktor_id']) || !isset($_SESSION['doktor_ad'])) {
    header("Location: doktor_login.php");
    exit();
}

$doktor_id = $_SESSION['doktor_id'];

// Rapor numarası oluşturma
$tarihSaat = date('YmdH');
$rastgeleSayi = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
$sonSayi = mt_rand(0, 9);
$raporNo = $tarihSaat . '-' . $rastgeleSayi . '-' . $sonSayi;

// Doktor bilgilerini çek
try {
    $stmt = $db->prepare("SELECT d.*, p.ad AS poliklinik_ad FROM doktorlar d 
                         LEFT JOIN poliklinikler p ON d.pol_id = p.id 
                         WHERE d.id = ?");
    $stmt->execute([$doktor_id]);
    $doktorBilgisi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doktorBilgisi) {
        session_destroy();
        header("Location: doktor_login.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Doktor bilgisi çekme hatası: " . $e->getMessage());
    $_SESSION['error_message'] = "Sistem hatası oluştu. Lütfen tekrar deneyin.";
    header("Location: dashboard.php");
    exit();
}

// Raporları veritabanından çekme - SADECE BU DOKTORA AİT OLANLARI
try {
    $stmt = $db->prepare("
        SELECT 
            r.rapor_id,
            r.rapor_no,
            r.tarih,
            r.rapor_tur,
            r.durum,
            r.doktor_id,
            h.ad AS hasta_ad,
            h.soyad AS hasta_soyad,
            h.dogum_tarihi,
            h.id AS hasta_id
        FROM raporlar r
        INNER JOIN hastalar h ON r.hasta_id = h.id
        WHERE r.doktor_id = :doktor_id
        ORDER BY r.tarih DESC
    ");
    $stmt->bindParam(':doktor_id', $doktor_id, PDO::PARAM_INT);
    $stmt->execute();
    $raporlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error_message'] = "Raporlar yüklenirken bir hata oluştu.";
    $raporlar = [];
}

// Rapor türleri ve durumları
$rapor_turleri = [
    'laboratuvar' => 'Laboratuvar',
    'radyoloji' => 'Radyoloji',
    'istirahat' => 'İstirahat',
    'epikriz' => 'Epikriz'
];

$rapor_durumlari = [
    'taslak' => 'Taslak',
    'onaylandi' => 'Onaylandı',
    'reddedildi' => 'Reddedildi',
    'tamamlandi' => 'Tamamlandı'
];

// Hastaları çek (modal formu için)
try {
    $hastalar = $db->query("SELECT id, ad, soyad FROM hastalar ORDER BY ad, soyad")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Hasta listesi çekme hatası: " . $e->getMessage());
    $hastalar = [];
    $_SESSION['error_message'] = "Hasta listesi yüklenirken bir hata oluştu.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <!-- HEAD KISMI AYNEN KALDI -->
</head>
<body class="bg-gray-50">
    <?php include "header.php"; ?>

    <div class="flex">
        <?php include "navbar.php"; ?>

        <main class="flex-1 p-4 md:p-6 ml-0 md:ml-64 overflow-x-auto">
            <!-- Başarı/Hata Mesajları -->
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= $_SESSION['success_message'] ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $_SESSION['error_message'] ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
            <?php endif; ?>

            <!-- Başlık ve Butonlar -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Rapor Yönetimi</h1>
                    <p class="text-gray-600"><?= $doktorBilgisi['unvan'] ?> <?= $doktorBilgisi['ad_soyad'] ?></p>
                    <p class="text-sm text-gray-500"><?= $doktorBilgisi['poliklinik_ad'] ?></p>
                </div>
                <button onclick="openRaporModal()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
                    <i class="fas fa-plus mr-2"></i> Yeni Rapor
                </button>
            </div>

            <!-- Rapor Listesi -->
            <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Raporlarım</h3>
                    <div class="relative">
                        <input type="text" id="raporArama" placeholder="Rapor ara..."
                            class="pl-8 pr-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-1/6 px-4 py-3 text-left">Rapor No</th>
                                <th class="w-1/4 px-4 py-3 text-left">Hasta</th>
                                <th class="w-1/6 px-4 py-3 text-left">Tarih</th>
                                <th class="w-1/6 px-4 py-3 text-left">Tür</th>
                                <th class="w-1/6 px-4 py-3 text-left">Durum</th>
                                <th class="w-1/4 px-4 py-3 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="raporListesi">
                            <?php if(empty($raporlar)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-8 px-4">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <i class="fas fa-file-alt text-4xl mb-3"></i>
                                        <p>Henüz rapor kaydı bulunmamaktadır</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach($raporlar as $rapor): 
                                    // Doktor kontrolü - ekstra güvenlik
                                    if ($rapor['doktor_id'] != $doktor_id) {
                                        continue;
                                    }
                                    
                                    $dogumTarihi = new DateTime($rapor['dogum_tarihi']);
                                    $bugun = new DateTime();
                                    $yas = $bugun->diff($dogumTarihi)->y;
                                    
                                    $turClass = [
                                        'Laboratuvar' => 'bg-purple-100 text-purple-800',
                                        'Radyoloji' => 'bg-blue-100 text-blue-800',
                                        'İstirahat' => 'bg-green-100 text-green-800',
                                        'Epikriz' => 'bg-indigo-100 text-indigo-800',
                                        'Sağlık Raporu' => 'bg-indigo-100 text-indigo-800'
                                    ][$rapor['rapor_tur']] ?? 'bg-gray-100 text-gray-800';
                                    
                                    $durumClass = [
                                        'Bekliyor' => 'bg-yellow-100 text-yellow-800',
                                        'Onaylandı' => 'bg-green-100 text-green-800',
                                        'Rededildi' => 'bg-red-100 text-red-800',
                                        'İptal Edildi' => 'bg-red-100 text-red-800'
                                    ][$rapor['durum']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">
                                        <?= htmlspecialchars($rapor['rapor_no']) ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-gray-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium">
                                                <?= htmlspecialchars($rapor['hasta_ad'] . ' ' . $rapor['hasta_soyad']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500"><?= $yas ?> yaş</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    <?= date('d.m.Y', strtotime($rapor['tarih'])) ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="status-badge  p-1 rounded-full <?= $turClass ?>">
                                        <?= $rapor_turleri[$rapor['rapor_tur']] ?? $rapor['rapor_tur'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="status-badge p-1 rounded-full <?= $durumClass ?>">
                                        <?= $rapor_durumlari[$rapor['durum']] ?? $rapor['durum'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a href="rapor_goruntule.php?id=<?= $rapor['rapor_id'] ?>"
                                            class="text-blue-600 hover:text-blue-800 p-2" title="Görüntüle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="rapor_duzenle.php?id=<?= $rapor['rapor_id'] ?>"
                                            class="text-yellow-600 hover:text-yellow-800 p-2" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../yonetici/rapor_indir.php?id=<?= $rapor['rapor_id'] ?>"
                                            class="text-green-600 hover:text-green-800 p-2" title="İndir">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button onclick="raporSil(<?= $rapor['rapor_id'] ?>)"
                                            class="text-red-600 hover:text-red-800 p-2" title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL VE DİĞER KISIMLAR AYNEN KALDI -->
</body>
</html>
    <script>
    // Modal fonksiyonları
    function openRaporModal() {
        document.getElementById('raporModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeRaporModal() {
        document.getElementById('raporModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Rapor arama fonksiyonu
    document.getElementById('raporArama').addEventListener('input', function(e) {
        const aramaMetni = e.target.value.toLowerCase();
        const satirlar = document.querySelectorAll('#raporListesi tr');

        satirlar.forEach(satir => {
            if (satir.cells.length < 2) return;
            const metin = satir.textContent.toLowerCase();
            satir.style.display = metin.includes(aramaMetni) ? '' : 'none';
        });
    });

    // Rapor silme fonksiyonu
    function raporSil(raporId) {
        if (confirm('Bu raporu silmek istediğinize emin misiniz?')) {
            window.location.href = `rapor_sil.php?id=${raporId}`;
        }
    }

    // Hata varsa modalı aç
    <?php if (isset($_GET['hata'])): ?>
    document.addEventListener('DOMContentLoaded', openRaporModal);
    <?php endif; ?>
    </script>
</body>

</html>