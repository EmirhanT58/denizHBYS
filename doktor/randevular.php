<?php
require_once "../database/db.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hata raporlamayı aç (geliştirme ortamında)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Oturum kontrolü
if (!isset($_SESSION['doktor_id'])) {
    header("Location: doktor_login.php");
    exit();
}

// Doktor bilgilerini çek
$stmt = $db->prepare("SELECT d.*, p.ad AS poliklinik_ad FROM doktorlar d 
                     LEFT JOIN poliklinikler p ON d.pol_id = p.id 
                     WHERE d.id = ?");
$stmt->execute([$_SESSION['doktor_id']]);
$doktorBilgisi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doktorBilgisi) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Oturum bilgilerini güncelle
$_SESSION['pol_id'] = $doktorBilgisi['pol_id'];
$_SESSION['doktor_ad'] = $doktorBilgisi['ad_soyad'];
$_SESSION['unvan'] = $doktorBilgisi['unvan'];

// Randevu türleri ve durumları
$randevuTurleri = [
    'Normal Muayene' => 'Normal Muayene',
    'Devam Eden Muayene' => 'Devam Eden Muayene',
    'Kontrol Muayenesi' => 'Kontrol Muayenesi',
];

$randevuDurumlari = [
    'onaylandi' => 'Onaylandı',
    'iptal' => 'İptal Edildi',
    'beklemede' => 'Beklemede',
    'tamamlandi' => 'Tamamlandı'
];

// Form gönderildiyse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['randevu_ekle'])) {
    $hasta_id = $_POST['hasta_id'] ?? '';
    $tarih = $_POST['tarih'] ?? '';
    $saat = $_POST['saat'] ?? '';
    $tur = $_POST['tur'] ?? '';
    $aciklama = $_POST['aciklama'] ?? '';
    $durum = 'beklemede';
    
    // Doğrulamalar
    $errors = [];
    
    if (empty($hasta_id)) {
        $errors['hasta_id'] = 'Hasta seçimi zorunludur';
    }
    
    if (empty($tarih)) {
        $errors['tarih'] = 'Tarih seçimi zorunludur';
    } elseif (strtotime($tarih) < strtotime(date('Y-m-d'))) {
        $errors['tarih'] = 'Geçmiş tarihli randevu oluşturulamaz';
    }
    
    if (empty($saat)) {
        $errors['saat'] = 'Saat seçimi zorunludur';
    }
    
    if (empty($tur) || !array_key_exists($tur, $randevuTurleri)) {
        $errors['tur'] = 'Geçersiz randevu türü';
    }
    
    // Hata yoksa veritabanına kaydet
    if (empty($errors)) {
        try {
            $datetime = date('Y-m-d H:i:s', strtotime("$tarih $saat"));
            
            $stmt = $db->prepare("
                INSERT INTO randevular 
                (doktor_id, hasta_id, pol_id, tarih, tur, aciklama, durum) 
                VALUES 
                (:doktor_id, :hasta_id, :pol_id, :tarih, :tur, :aciklama, :durum)
            ");
            
            $stmt->execute([
                ':doktor_id' => $_SESSION['doktor_id'],
                ':hasta_id' => $hasta_id,
                ':pol_id' => $_SESSION['pol_id'],
                ':tarih' => $datetime,
                ':tur' => $tur,
                ':aciklama' => $aciklama,
                ':durum' => $durum
            ]);
            
            $_SESSION['success_message'] = 'Randevu başarıyla oluşturuldu';
            header("Location: randevular.php");
            exit();
        } catch (PDOException $e) {
            $errors['database'] = 'Randevu oluşturulurken bir hata oluştu: ' . $e->getMessage();
        }
    }
}

// Hastaları veritabanından çekme
$hastalar = $db->query("SELECT id, ad, soyad, tc FROM hastalar ORDER BY ad, soyad")->fetchAll(PDO::FETCH_ASSOC);

// Randevuları listeleme
try {
    $stmt = $db->prepare("
        SELECT 
            r.id, r.tarih, r.tur, r.durum, r.aciklama,
            h.id AS hasta_id, h.ad AS hasta_ad, h.soyad AS hasta_soyad, h.tc
        FROM randevular r
        INNER JOIN hastalar h ON r.hasta_id = h.id
        WHERE r.doktor_id = :doktor_id
        ORDER BY r.tarih DESC
    ");
    $stmt->bindParam(':doktor_id', $_SESSION['doktor_id'], PDO::PARAM_INT);
    $stmt->execute();
    $randevular = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $randevular = [];
}

$bugun = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu Yönetimi - <?= $_SESSION['unvan'] ?> <?= $_SESSION['doktor_ad'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            width: 100%;
            max-width: 500px;
        }
        .has-error {
            border-color: #f56565;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.25rem;
        }
        table {
            width: 100%;
            table-layout: fixed;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include "header.php"; ?>
    
    <div class="flex">
        <?php include "navbar.php"; ?>

        <main class="flex-1 p-4 md:p-6  md:ml-64 overflow-x-auto max-w-6xl mx-auto">
            
            <!-- Başarı/Hata Mesajları -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= $_SESSION['success_message'] ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($errors['database'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $errors['database'] ?>
                </div>
            <?php endif; ?>

            <!-- Randevu Listesi Başlık ve Buton -->
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Randevu Listesi</h1>
                    <p class="text-gray-600"><?= $_SESSION['unvan'] ?> <?= $_SESSION['doktor_ad'] ?></p>
                </div>
                <button id="openModalBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
                    <i class="fas fa-plus mr-2"></i> Yeni Randevu
                </button>
            </div>

            <!-- Randevu Tablosu -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="w-1/4">Hasta Bilgisi</th>
                                <th class="w-1/6">Tarih/Saat</th>
                                <th class="w-1/6">Tür</th>
                                <th class="w-1/6">Durum</th>
                                <th class="w-1/4 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($randevular)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-8">
                                        <div class="flex flex-col items-center justify-center text-gray-500">
                                            <i class="fas fa-calendar-times text-4xl mb-3"></i>
                                            <p>Henüz randevu kaydı bulunmamaktadır</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($randevular as $randevu): 
                                    $tarih = date('d.m.Y', strtotime($randevu['tarih']));
                                    $saat = date('H:i', strtotime($randevu['tarih']));
                                    
                                    $statusClass = [
                                        'Onaylandı' => 'bg-green-100 text-green-800',
                                        'İptal Edildi' => 'bg-red-100 text-red-800',
                                        'Beklemede' => 'bg-yellow-100 text-yellow-800',
                                        'Tamamlandı' => 'bg-blue-100 text-blue-800'
                                    ][$randevu['durum']] ?? 'bg-gray-100 text-gray-800';
                                    
                                    $typeClass = [
                                        'Kontrol Muayenesi' => 'bg-blue-100 text-blue-800',
                                        'Normal Muayene' => 'bg-purple-100 text-purple-800',
                                        'Devam Eden Muayene' => 'bg-indigo-100 text-indigo-800',
                                    ][$randevu['tur']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                                <i class="fas fa-user text-gray-600"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium">
                                                    <?= htmlspecialchars($randevu['hasta_ad'] . ' ' . $randevu['hasta_soyad']) ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?= htmlspecialchars($randevu['tc']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?= $tarih ?></div>
                                        <div class="text-sm text-gray-500"><?= $saat ?></div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $typeClass ?>">
                                            <?= $randevuTurleri[$randevu['tur']] ?? $randevu['tur'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= $randevuDurumlari[$randevu['durum']] ?? $randevu['durum'] ?>
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end space-x-2">
                                            <a href="randevu_sil.php?id=<?= $randevu['id'] ?>" class="text-red-600 hover:text-red-800 p-2" title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </a>
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

    <!-- Randevu Ekleme Modalı -->
    <div id="randevuModal" class="modal <?= !empty($errors) ? 'active' : '' ?>">
        <div class="modal-content bg-white rounded-lg shadow-lg">
            <div class="border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Yeni Randevu Ekle</h3>
                <button id="closeModalBtn" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="" class="p-6">
                <input type="hidden" name="randevu_ekle" value="1">
                
                <div class="space-y-4">
                    <!-- Hasta Seçimi -->
                    <div>
                        <label class="block mb-1">Hasta Seçimi</label>
                        <select name="hasta_id" required class="w-full border rounded px-3 py-2 <?= isset($errors['hasta_id']) ? 'border-red-500' : '' ?>">
                            <option value="">Seçiniz</option>
                            <?php foreach ($hastalar as $hasta): ?>
                                <option value="<?= $hasta['id'] ?>" <?= (isset($_POST['hasta_id']) && $_POST['hasta_id'] == $hasta['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($hasta['ad'] . ' ' . $hasta['soyad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['hasta_id'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['hasta_id'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Tarih ve Saat -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1">Tarih</label>
                            <input type="date" name="tarih" min="<?= date('Y-m-d') ?>" value="<?= $_POST['tarih'] ?? date('Y-m-d') ?>" required
                                class="w-full border rounded px-3 py-2 <?= isset($errors['tarih']) ? 'border-red-500' : '' ?>">
                            <?php if (isset($errors['tarih'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?= $errors['tarih'] ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block mb-1">Saat</label>
                            <input type="time" name="saat" value="<?= $_POST['saat'] ?? '09:00' ?>" required
                                class="w-full border rounded px-3 py-2 <?= isset($errors['saat']) ? 'border-red-500' : '' ?>">
                            <?php if (isset($errors['saat'])): ?>
                                <p class="text-red-500 text-sm mt-1"><?= $errors['saat'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Tür ve Açıklama -->
                    <div>
                        <label class="block mb-1">Randevu Türü</label>
                        <select name="tur" required class="w-full border rounded px-3 py-2 <?= isset($errors['tur']) ? 'border-red-500' : '' ?>">
                            <?php foreach ($randevuTurleri as $key => $value): ?>
                                <option value="<?= $key ?>" <?= (isset($_POST['tur']) && $_POST['tur'] == $key) ? 'selected' : '' ?>>
                                    <?= $value ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['tur'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?= $errors['tur'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label class="block mb-1">Açıklama</label>
                        <textarea name="aciklama" rows="3" class="w-full border rounded px-3 py-2"><?= $_POST['aciklama'] ?? '' ?></textarea>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" id="cancelBtn" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">
                        İptal
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Modal yönetimi
    const modal = document.getElementById('randevuModal');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    
    function openModal() {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    
    // Event listeners
    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    // Modal dışına tıklayarak kapat
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Hata varsa modalı aç
    <?php if (!empty($errors)): ?>
        document.addEventListener('DOMContentLoaded', openModal);
    <?php endif; ?>
    </script>
</body>
</html>