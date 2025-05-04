<?php
session_start();
require_once '../database/db.php';

// Doktor giriş kontrolü
if (!isset($_SESSION['doktor_id'])) {
    header("Location: doktor_giris.php");
    exit;
}

$doktor_id = $_SESSION['doktor_id'];

// Rastgele reçete numarası üretme fonksiyonu
function generateReceteNo() {
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $result = '';
    for ($i = 0; $i < 10; $i++) {
        $result .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $result;
}

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['recete_ekle'])) {
    try {
        $recete_no = generateReceteNo();
        $hasta_id = $_POST['hasta_id'] ?: NULL;
        $hastane_id = $_POST['hastane_id'];
        $pol_id = $_POST['pol_id'];
        $recete_turu = $_POST['recete_turu'];
        $ilac_id = $_POST['ilac_id'];
        $doz = $_POST['doz'];
        $periyod = $_POST['periyod'] ?: NULL;
        $kutu_adet = $_POST['kutu_adet'] ?: 1;
        $aciklama = $_POST['aciklama'] ?: NULL;

        $insert = $db->prepare("INSERT INTO receteler 
                              (recete_no, hastane_id, pol_id, doktor_id, hasta_id, recete_turu, ilac_id, doz, periyod, kutu_adet, aciklama)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([$recete_no, $hastane_id, $pol_id, $doktor_id, $hasta_id, $recete_turu, $ilac_id, $doz, $periyod, $kutu_adet, $aciklama]);

        $_SESSION['success'] = "Reçete başarıyla oluşturuldu! Reçete No: $recete_no";
        header("Location: recete.php");
        exit;
    } catch (PDOException $e) {
        $error = "Reçete oluşturma hatası: " . $e->getMessage();
    }
}

// Reçeteleri çek
$sql = "SELECT 
            r.recete_id,
            r.recete_no,
            r.recete_turu,
            r.doz,
            r.periyod,
            r.kutu_adet,
            r.ol_tarih,
            r.recete_durum,
            h.h_ad AS hastane_adi,
            p.ad AS poliklinik_adi,
            i.ilac_ad,
            ha.ad AS hasta_adi,
            ha.soyad AS hasta_soyad
        FROM receteler r
        LEFT JOIN hastaneler h ON r.hastane_id = h.h_id
        LEFT JOIN poliklinikler p ON r.pol_id = p.id
        LEFT JOIN ilaclar i ON r.ilac_id = i.ilac_id
        LEFT JOIN hastalar ha ON r.hasta_id = ha.id
        WHERE r.doktor_id = ?
        ORDER BY r.ol_tarih DESC";

$stmt = $db->prepare($sql);
$stmt->execute([$doktor_id]);
$receteler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gerekli verileri çek
$hastaneler = $db->query("SELECT h_id, h_ad FROM hastaneler")->fetchAll();
$poliklinikler = $db->query("SELECT id, ad FROM poliklinikler")->fetchAll();
$ilaclar = $db->query("SELECT ilac_id, ilac_ad FROM ilaclar")->fetchAll();
$hastalar = $db->query("SELECT * FROM hastalar")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Reçete Yönetimi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    .modal {
        transition: opacity 0.25s ease;
    }

    .recete-tur-kirmizi {
        border-left: 4px solid #ef4444;
    }

    .recete-tur-yesil {
        border-left: 4px solid #10b981;
    }

    .recete-tur-mor {
        border-left: 4px solid #8b5cf6;
    }

    .recete-tur-turuncu {
        border-left: 4px solid #f97316;
    }
    </style>
</head>

<body class="bg-gray-50">

    <?php 
     include 'header.php';
    include 'navbar.php';
     ?>

    <div class="ml-64 p-8  fixed top-20 left-0 right-0   z-10">
        <div class="max-w-6xl mx-auto">
            <!-- Başlık ve Buton -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Reçete Yönetimi</h1>
                <button onclick="openModal()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Yeni Reçete
                </button>
            </div>

            <!-- Mesajlar -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Reçete Listesi -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <?php if (!empty($receteler)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Reçete No</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hasta</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    İlaç</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Doz</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tarih</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Durum</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($receteler as $recete): ?>
                            <tr class="hover:bg-gray-50 <?= match($recete['recete_turu']) {
                                'kırmızı' => 'recete-tur-kirmizi',
                                'yeşil' => 'recete-tur-yesil',
                                'mor' => 'recete-tur-mor',
                                'turuncu' => 'recete-tur-turuncu',
                                default => ''
                            } ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-blue-600">
                                        <?= htmlspecialchars($recete['recete_no']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($recete['hasta_adi']): ?>
                                    <div class="text-sm"><?= htmlspecialchars($recete['hasta_adi']) ?>
                                        <?= htmlspecialchars($recete['hasta_soyad']) ?></div>
                                    <?php else: ?>
                                    <div class="text-sm text-gray-500">Hasta belirtilmemiş</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium"><?= htmlspecialchars($recete['ilac_ad']) ?></div>
                                    <?= htmlspecialchars($recete['kutu_adet']) ?> adet</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"><?= htmlspecialchars($recete['doz']) ?></div>
                                    <?php if ($recete['periyod']): ?>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($recete['periyod']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm"><?= date("d.m.Y H:i", strtotime($recete['ol_tarih'])) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        <?= match($recete['recete_durum']) {
                                            'aktif' => 'bg-green-100 text-green-800',
                                            'iptal' => 'bg-red-100 text-red-800',
                                            'kullanıldı' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        } ?>">
                                        <?= htmlspecialchars(ucfirst($recete['recete_durum'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-3">
                                        <a href="recete_indir.php?id=<?= $recete['recete_id'] ?>"
                                            class="text-green-600 hover:text-green-900" title="PDF Oluştur">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <?php if ($recete['recete_durum'] == 'aktif'): ?>
                                        <a href="recete_iptal.php?id=<?= $recete['recete_id'] ?>"
                                            class="text-red-600 hover:text-red-900" title="İptal Et"
                                            onclick="return confirm('Bu reçeteyi iptal etmek istediğinize emin misiniz?')">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="p-8 text-center">
                    <i class="fas fa-prescription text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">Henüz kayıtlı reçeteniz bulunmamaktadır.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Reçete Ekleme Modal -->
    <div id="receteModal" class="fixed inset-0 z-50 flex items-center justify-center hidden modal">
        <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeModal()"></div>

        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl z-10 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Yeni Reçete Oluştur</h3>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="recete_ekle" value="1">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Reçete No (Otomatik) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reçete No</label>
                            <input type="text" value="<?= generateReceteNo() ?>" readonly
                                class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg">
                        </div>

                        <!-- Reçete Türü -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reçete Türü *</label>
                            <select name="recete_turu" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="normal">Normal</option>
                                <option value="kırmızı">Kırmızı</option>
                                <option value="yeşil">Yeşil</option>
                                <option value="mor">Mor</option>
                                <option value="turuncu">Turuncu</option>
                            </select>
                        </div>

                        <!-- Hastane Seçimi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hastane *</label>
                            <select name="hastane_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Seçiniz</option>
                                <?php foreach ($hastaneler as $hastane): ?>
                                <option value="<?= $hastane['h_id'] ?>"><?= htmlspecialchars($hastane['h_ad']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Poliklinik Seçimi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Poliklinik *</label>
                            <select name="pol_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Seçiniz</option>
                                <?php foreach ($poliklinikler as $pol): ?>
                                <option value="<?= $pol['id'] ?>"><?= htmlspecialchars($pol['ad']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Hasta ID -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                            <select name="hasta_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Hasta Seçiniz</option>
                                <?php foreach ($hastalar as $hasta): ?>
                                <option value="<?= $hasta['id'] ?>">
                                    <?= htmlspecialchars($hasta['ad'] . ' ' . $hasta['soyad'] . ' (ID: ' . $hasta['id'] . ')') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- İlaç Seçimi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">İlaç *</label>
                            <select name="ilac_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Seçiniz</option>
                                <?php foreach ($ilaclar as $ilac): ?>
                                <option value="<?= $ilac['ilac_id'] ?>"><?= htmlspecialchars($ilac['ilac_ad']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Doz -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Doz *</label>
                            <input type="text" name="doz" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Örn: 1x1">
                        </div>

                        <!-- Periyod -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Periyod (Opsiyonel)</label>
                            <input type="text" name="periyod"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Örn: Sabah-Akşam">
                        </div>

                        <!-- Kutu Adet -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kutu Adet</label>
                            <input type="number" name="kutu_adet" min="1" value="1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Açıklama -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama (Opsiyonel)</label>
                        <textarea name="aciklama" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Reçete ile ilgili ek açıklamalar"></textarea>
                    </div>

                    <!-- Butonlar -->
                    <div class="flex justify-end space-x-4 pt-6">
                        <button type="button" onclick="closeModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                            İptal
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                            Reçeteyi Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Modal fonksiyonları
    function openModal() {
        document.getElementById('receteModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
        document.getElementById('receteModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // ESC tuşu ile modalı kapatma
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    </script>
</body>

</html>