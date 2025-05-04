<?php
session_start();
include "../database/db.php"; 

if (!isset($_SESSION['hasta_id'])) {
    header("Location: login.php");
    exit();
}

date_default_timezone_set("Europe/Istanbul");

$hasta_id = $_SESSION['hasta_id'];

// Hasta randevularını çek
try {
    $query = $db->prepare("
       SELECT 
            r.id,
            r.tarih,
            r.durum,
            k.ad AS klinik_ad,
            d.ad_soyad AS doktor_ad,
            d.unvan AS doktor_unvan,
            h.h_ad AS hastane_ad,
            r.aciklama AS notlar,
            r.created_at AS olusturulma_tarihi
        FROM randevular r
        JOIN doktorlar d ON r.doktor_id = d.id 
        JOIN poliklinikler k ON d.pol_id = k.id 
        JOIN hastaneler h ON r.hastane_id = h.h_id
        WHERE r.hasta_id = :hasta_id
        ORDER BY r.tarih DESC
    ");
    $query->bindParam(':hasta_id', $hasta_id, PDO::PARAM_INT);
    $query->execute();
    $randevular = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Randevu verileri çekilemedi: " . $e->getMessage());
}

// Hasta bilgilerini çek
try {
    $query = $db->prepare("SELECT * FROM hastalar WHERE id = :hasta_id");
    $query->bindParam(':hasta_id', $hasta_id, PDO::PARAM_INT);
    $query->execute();
    $hasta = $query->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Hasta bilgileri çekilemedi: " . $e->getMessage());
}

// Randevu onaylama işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['onayla_randevu'])) {
    $randevu_id = $_POST['randevu_id'];
    
    try {
        $update = $db->prepare("UPDATE randevular SET durum = 'Onaylandı' WHERE id = :id AND hasta_id = :hasta_id");
        $update->bindParam(':id', $randevu_id, PDO::PARAM_INT);
        $update->bindParam(':hasta_id', $hasta_id, PDO::PARAM_INT);
        $update->execute();
        
        $_SESSION['success_message'] = "Randevu başarıyla onaylandı!";
        header("Location: randevularim.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Randevu onaylanırken hata oluştu: " . $e->getMessage();
        header("Location: randevularim.php");
        exit();
    }
}

// Randevu iptal işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['iptal_randevu'])) {
    $randevu_id = $_POST['randevu_id'];
    
    try {
        $update = $db->prepare("UPDATE randevular SET durum = 'İptal Edildi' WHERE id = :id AND hasta_id = :hasta_id");
        $update->bindParam(':id', $randevu_id, PDO::PARAM_INT);
        $update->bindParam(':hasta_id', $hasta_id, PDO::PARAM_INT);
        $update->execute();
        
        $_SESSION['success_message'] = "Randevu başarıyla iptal edildi!";
        header("Location: randevularim.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Randevu iptal edilirken hata oluştu: " . $e->getMessage();
        header("Location: randevularim.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevularım</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#6366f1',
                    secondary: '#8b5cf6'
                }
            }
        }
    }
    </script>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar -->
    <?php include "sidebar.php"; ?>

    <!-- Ana İçerik -->
    <main class="ml-64 p-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Randevularım</h1>
                    <p class="text-gray-600">Geçmiş ve gelecek randevu bilgileriniz</p>
                </div>
                <a href="../RandevuMHRS/dashboard.php" target="_blank"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-calendar-plus mr-2"></i> MHRS'den Randevu Al
                </a>
            </div>

            <!-- Hata veya başarı mesajları -->
            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['error_message'] ?></span>
                <button onclick="this.parentElement.remove()" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['error_message']); endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['success_message'] ?></span>
                <button onclick="this.parentElement.remove()" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); endif; ?>

            <!-- Randevular Tablosu -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="overflow-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-sm font-semibold uppercase">
                                <th class="px-6 py-4 text-left">Tarih</th>
                                <th class="px-6 py-4 text-left">Durum</th>
                                <th class="px-6 py-4 text-left">Klinik</th>
                                <th class="px-6 py-4 text-left">Doktor</th>
                                <th class="px-6 py-4 text-left">Hastane</th>
                                <th class="px-6 py-4 text-left">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($randevular)): ?>
                            <?php foreach ($randevular as $randevu): 
                                    $randevuTarihi = new DateTime($randevu['tarih']);
                                    $bugun = new DateTime();
                                    $gecmisMi = $randevuTarihi < $bugun;
                                    
                                    $durumClass = [
                                        'Onaylandı' => 'bg-green-100 text-green-800',
                                        'Beklemede' => 'bg-yellow-100 text-yellow-800',
                                        'İptal Edildi' => 'bg-red-100 text-red-800'
                                    ][$randevu['durum']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?= date("d.m.Y H:i", strtotime($randevu['tarih'])) ?>
                                    <?php if($gecmisMi): ?>
                                    <span class="text-xs text-gray-500">(Geçmiş)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $durumClass ?>">
                                        <?= $randevu['durum'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?= htmlspecialchars($randevu['klinik_ad']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?= htmlspecialchars($randevu['doktor_unvan'] . ' ' . $randevu['doktor_ad']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?= htmlspecialchars($randevu['hastane_ad']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?php if(!$gecmisMi && $randevu['durum'] == 'Onaylandı'): ?>
                                    <button onclick="randevuIptal(<?= $randevu['id'] ?>)" 
                                            class="text-red-600 hover:text-red-800 mr-3">
                                        <i class="fas fa-calendar-times"></i> İptal Et
                                    </button>
                                    <?php elseif(!$gecmisMi && $randevu['durum'] == 'Beklemede'): ?>
                                    <button onclick="onaylaRandevu(<?= $randevu['id'] ?>)" 
                                            class="text-green-600 hover:text-green-800 mr-3">
                                        <i class="fas fa-check-circle"></i> Onayla
                                    </button>
                                    <?php endif; ?>
                                    <button onclick="showRandevuDetay(<?= htmlspecialchars(json_encode($randevu)) ?>)" 
                                            class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-info-circle mr-1"></i> Detay
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                                    <i class="fas fa-calendar-times text-2xl mb-2"></i>
                                    <p>Kayıtlı randevu bulunamadı.</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Randevu İptal Formu (hidden) -->
    <form id="iptalForm" method="POST" action="randevularim.php" class="hidden">
        <input type="hidden" name="randevu_id" id="iptalRandevuId">
        <input type="hidden" name="iptal_randevu" value="1">
    </form>

    <!-- Randevu Onay Formu (hidden) -->
    <form id="onayForm" method="POST" action="randevularim.php" class="hidden">
        <input type="hidden" name="randevu_id" id="onayRandevuId">
        <input type="hidden" name="onayla_randevu" value="1">
    </form>

    <script>
    // Randevu detaylarını gösterme fonksiyonu
    function showRandevuDetay(randevu) {
        const randevuTarihi = new Date(randevu.tarih);
        const olusturulmaTarihi = new Date(randevu.olusturulma_tarihi);
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit' 
        };
        
        let html = `
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-700">Randevu Tarihi</h4>
                        <p>${randevuTarihi.toLocaleDateString('tr-TR', options)}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700">Durum</h4>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${randevu.durum === 'Onaylandı' ? 'bg-green-100 text-green-800' : 
                            randevu.durum === 'Beklemede' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800' }">
                            ${randevu.durum}
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-700">Klinik</h4>
                        <p>${randevu.klinik_ad}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700">Doktor</h4>
                        <p>${randevu.doktor_unvan} ${randevu.doktor_ad}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-700">Hastane</h4>
                        <p>${randevu.hastane_ad}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700">Oluşturulma Tarihi</h4>
                        <p>${olusturulmaTarihi.toLocaleDateString('tr-TR', options)}</p>
                    </div>
                </div>
                
                ${randevu.notlar ? `
                <div>
                    <h4 class="font-semibold text-gray-700">Notlar</h4>
                    <p class="bg-gray-50 p-3 rounded">${randevu.notlar}</p>
                </div>
                ` : ''}
            </div>
        `;
        
        Swal.fire({
            title: 'Randevu Detayları',
            html: html,
            width: '800px',
            showCloseButton: true,
            showConfirmButton: false
        });
    }

    // Randevu iptal fonksiyonu
    async function randevuIptal(randevuId) {
        const { isConfirmed } = await Swal.fire({
            title: 'Emin misiniz?',
            text: 'Bu randevuyu iptal etmek istediğinize emin misiniz?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, İptal Et',
            cancelButtonText: 'Vazgeç',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        });

        if (isConfirmed) {
            document.getElementById('iptalRandevuId').value = randevuId;
            document.getElementById('iptalForm').submit();
        }
    }

    // Randevu onaylama fonksiyonu
    async function onaylaRandevu(randevuId) {
        const { isConfirmed } = await Swal.fire({
            title: 'Randevuyu Onayla',
            text: 'Bu randevuyu onaylamak istediğinize emin misiniz?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Evet, Onayla',
            cancelButtonText: 'Vazgeç',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        });

        if (isConfirmed) {
            document.getElementById('onayRandevuId').value = randevuId;
            document.getElementById('onayForm').submit();
        }
    }
    </script>
</body>
</html>