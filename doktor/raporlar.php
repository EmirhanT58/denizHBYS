<?php
require_once "../database/db.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hata raporlamayı aç (geliştirme ortamında)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Oturum kontrolü - Sadece doktorlar erişebilir
if (!isset($_SESSION['doktor_id'])) {
    header("Location: doktor_login.php");
    exit();
}

$doktor_id = $_SESSION['doktor_id'];
$doktor_unvan = $_SESSION['unvan'];
$doktor_ad_soyad = $_SESSION['doktor_ad'];
// Rapor no oluşturma
$tarihSaat = date('YmdH');
$rastgeleSayi = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
$sonSayi = mt_rand(0, 9);
$raporNo = $tarihSaat . '-' . $rastgeleSayi . '-' . $sonSayi;


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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
    .modal-overlay {
        z-index: 40;
    }

    .modal-content {
        z-index: 50;
    }
    </style>
</head>

<body class="bg-gray-50">
    <?php include "header.php"; ?>

    <div class="flex">
        <?php include "navbar.php"; ?>

        <main class="flex-1 p-4 md:p-6 ml-0 md:ml-64 overflow-x-auto">
            <!-- Başarı/Hata Mesajları -->
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
            <?php endif; ?>

            <div>
                <div class="flex justify-between items-center mb-6 px-4" style="max-width: 1200px; margin: 0 auto;">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Rapor Yönetimi</h1>
                    </div>
                    <button id="yeniRaporBtn"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
                        <i class="fas fa-plus mr-2"></i> Yeni Rapor
                    </button>
                </div>

           
                <!-- Rapor Listesi -->
                <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden "
                    style="width: 90%; max-width: 1200px; margin-top: 1.5rem; margin-bottom: 2rem;">
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
                                    <th class="px-4 py-3 text-left">Rapor No</th>
                                    <th class="px-4 py-3 text-left">Hasta</th>
                                    <th class="px-4 py-3 text-left">Tarih</th>
                                    <th class="px-4 py-3 text-left">Tür</th>
                                    <th class="px-4 py-3 text-left">Durum</th>
                                    <th class="px-4 py-3 text-right">İşlemler</th>
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
                                    if ($rapor['doktor_id'] != $doktor_id) continue;
                                    
                                    $dogumTarihi = new DateTime($rapor['dogum_tarihi']);
                                    $bugun = new DateTime();
                                    $yas = $bugun->diff($dogumTarihi)->y;
                                    
                                    $turClass = [
                                        'laboratuvar' => 'bg-aqua-100 text-aqua-800',
                                        'radyoloji' => 'bg-blue-100 text-blue-800',
                                        'İstirahat Raporu' => 'bg-blue-100 text-green-800',
                                        'laboratuvar' => 'bg-purple-100 text-purple-800',
                                        'radyoloji' => 'bg-blue-100 text-blue-800',
                                        'epikriz' => 'bg-indigo-100 text-indigo-800',
                                        'Sağlık Raporu' => 'bg-indigo-100 text-indigo-800'
                                    ][$rapor['rapor_tur']] ?? 'bg-gray-100 text-gray-800';
                                    
                                    $durumClass = [
                                        'Bekliyor' => 'bg-yellow-100 text-yellow-800',
                                        'Onaylandı' => 'bg-green-100 text-green-800',
                                        'Red Edildi' => 'bg-red-100 text-red-800',
                                        'Süresi Doldu' => 'bg-blue-100 text-blue-800'
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
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $turClass ?>">
                                            <?= $rapor_turleri[$rapor['rapor_tur']] ?? $rapor['rapor_tur'] ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $durumClass ?>">
                                            <?= $rapor_durumlari[$rapor['durum']] ?? $rapor['durum'] ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end space-x-2">
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

    <!-- Modal Overlay -->
    <div id="raporModalOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden modal-overlay"></div>

    <!-- Rapor Form Modal -->
    <div id="raporFormModal" class="fixed inset-0 flex items-center justify-center p-4 hidden modal-content">
        <div class="bg-white rounded-lg shadow-md p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Yeni Rapor Oluştur</h2>

            <form action="rapor_kaydet.php" method="POST" class="space-y-6">
                <input type="hidden" name="doktor_id" value="<?= htmlspecialchars($doktor_id) ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="rapor_no" class="block text-sm font-medium text-gray-700 mb-1">Rapor No</label>
                        <input type="text" id="rapor_no" name="rapor_no" value="<?= htmlspecialchars($raporNo) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 bg-gray-100"
                            readonly>
                    </div>

                    <div>
                        <label for="tarih" class="block text-sm font-medium text-gray-700 mb-1">Tarih</label>
                        <input type="date" id="tarih" name="tarih"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="hasta_id" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                        <select id="hasta_id" name="hasta_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Hasta Seçin</option>
                            <?php foreach($hastalar as $hasta): ?>
                            <option value="<?= htmlspecialchars($hasta['id']) ?>">
                                <?= htmlspecialchars($hasta['ad'] . ' ' . $hasta['soyad']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="doktor_id" class="block text-sm font-medium text-gray-700 mb-1">Doktor</label>
                        <select id="doktor_id" name="doktor_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            disabled>
                            <option value="<?= $_SESSION["doktor_id"] ?>" selected>
                                <?php echo $doktor_ad_soyad; ?>
                            </option>
                        </select>
                        <input type="hidden" name="doktor_id" value="<?= htmlspecialchars($doktor_id) ?>">
                    </div>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="rapor_tur" class="block text-sm font-medium text-gray-700 mb-1">Rapor Türü</label>
                        <select id="rapor_tur" name="rapor_tur" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Rapor Türü Seçin</option>
                            <option value="Laboratuvar Raporu">Laboratuvar</option>
                            <option value="Radyoloji Raporu">Radyoloji</option>
                            <option value="İstirahat Raporu">İstirahat</option>
                            <option value="Epikriz Raporu">Epikriz</option>
                            <option value="Engelli Raporu">Engelli</option>
                        </select>
                    </div>

                    <div>
                        <label for="durum" class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
                        <select id="durum" name="durum" required class="w-full px-4 py-2 border rounded-md" readonly>
                            <option value="Bekliyor" selected>Bekliyor</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="tahlil_sonuclari" class="block text-sm font-medium text-gray-700 mb-1">Tahlil
                        Sonuçları</label>
                    <textarea id="tahlil_sonuclari" name="tahlil_sonuclari" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div>
                    <label for="hekim_gorusu" class="block text-sm font-medium text-gray-700 mb-1">Hekim Görüşü</label>
                    <textarea id="hekim_gorusu" name="hekim_gorusu" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        İptal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Raporu Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Modal fonksiyonları
    function openModal() {
        document.getElementById('raporModalOverlay').classList.remove('hidden');
        document.getElementById('raporFormModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('raporModalOverlay').classList.add('hidden');
        document.getElementById('raporFormModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Yeni Rapor butonuna click eventi
    document.getElementById('yeniRaporBtn').addEventListener('click', openModal);

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
    document.addEventListener('DOMContentLoaded', openModal);
    <?php endif; ?>
    </script>
</body>

</html>