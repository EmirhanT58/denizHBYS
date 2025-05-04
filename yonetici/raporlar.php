<?php
// Çıktı tamponlamayı başlatalım
ob_start();

// Oturumu kontrol edelim
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Yönetici giriş kontrolü
if (!isset($_SESSION["yonetici_id"]) || !isset($_SESSION["k_ad"])) {
    header("Location: giris.php");
    exit;
}

require_once "../database/db.php";

// Rapor durum güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rapor_id'])) {
    $rapor_id = $_POST['rapor_id'];
    $durum = $_POST['durum'];
    
    try {
        $update = $db->prepare("UPDATE raporlar SET durum = ? WHERE rapor_id = ?");
        $update->execute([$durum, $rapor_id]);
        
        if ($update->rowCount() > 0) {
            $_SESSION['success'] = "Rapor durumu güncellendi!";
        } else {
            $_SESSION['error'] = "Güncelleme başarısız oldu! Rapor bulunamadı.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Veritabanı hatası: " . $e->getMessage();
    }
    
    header("Location: dashboard.php?sayfa=raporlar?durum=$durum");
    exit;
}

// Raporları çekme işlemi
try {
    $sql = $db->prepare("
        SELECT 
            r.rapor_id,
            r.hasta_id,
            r.doktor_id,
            r.rapor_no,
            r.tarih,
            r.tahlil_sonuclari,
            r.hekim_gor,
            r.rapor_tur,
            r.durum,
            d.ad_soyad,
            d.unvan,
            h.ad,
            h.soyad,
            d.ad_soyad AS doktor_ad
        FROM raporlar r
        INNER JOIN hastalar h ON r.hasta_id = h.id
        INNER JOIN doktorlar d ON r.doktor_id = d.id
        ORDER BY r.tarih DESC
    ");
    $sql->execute();
    $raporlar = $sql->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// HTML çıktısı başlamadan önce tüm PHP işlemleri tamamlanmış olmalı
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor Yönetimi - denizHBYS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-approved { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }
        .whitespace-pre-line { white-space: pre-line; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Başlık ve Arama -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Rapor Yönetim Paneli</h1>

        </div>

        <!-- Mesajlar -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            <div class="flex justify-between items-center">
                <p><?= htmlspecialchars($_SESSION['success']) ?></p>
                <button onclick="this.parentElement.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['success']); endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <div class="flex justify-between items-center">
                <p><?= htmlspecialchars($_SESSION['error']) ?></p>
                <button onclick="this.parentElement.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['error']); endif; ?>

        <!-- Rapor Tablosu -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rapor No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hasta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doktor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tür</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($raporlar as $rapor): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($rapor['rapor_no']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= htmlspecialchars($rapor['ad'] . ' ' . $rapor['soyad']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= htmlspecialchars($rapor['unvan'] . ' ' . $rapor['doktor_ad']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= htmlspecialchars($rapor['rapor_tur']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= date("d/m/Y H:i", strtotime($rapor['tarih'])) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    <?= $rapor['durum'] == 'Onaylandı' ? 'status-approved' : 
                                       ($rapor['durum'] == 'Rededildi' ? 'status-rejected' : 'status-pending') ?>">
                                    <?= htmlspecialchars($rapor['durum']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <!-- Detay Görüntüle -->
                                    <button onclick="showReportDetails(
                                        '<?= htmlspecialchars($rapor['rapor_no'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($rapor['ad'] . ' ' . $rapor['soyad'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($rapor['unvan'] . ' ' . $rapor['doktor_ad'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($rapor['rapor_tur'], ENT_QUOTES) ?>',
                                        `<?=  $rapor['tahlil_sonuclari'] ?? 'YOK' ?>`,
                                        `<?=  htmlspecialchars($rapor['hekim_gor'], ENT_QUOTES) ?>`,
                                        '<?= htmlspecialchars($rapor['durum'], ENT_QUOTES) ?>',
                                        '<?= date("d/m/Y H:i", strtotime($rapor['tarih'])) ?>'
                                    )" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <!-- Durum Güncelleme Formu -->
                                    <form method="POST" action="" class="inline">
                                        <input type="hidden" name="rapor_id" value="<?= $rapor['rapor_id'] ?>">
                                        <input type="hidden" name="durum" value="Onaylandı">
                                        <button type="submit" class="text-green-600 hover:text-green-900" 
                                                onclick="return confirm('Bu raporu onaylamak istediğinize emin misiniz?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="" class="inline">
                                        <input type="hidden" name="rapor_id" value="<?= $rapor['rapor_id'] ?>">
                                        <input type="hidden" name="durum" value="Rededildi">
                                        <button type="submit" class="text-red-600 hover:text-red-900" 
                                                onclick="return confirm('Bu raporu reddetmek istediğinize emin misiniz?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                    
                                    <!-- PDF İndir -->
                                    <a href="rapor_indir.php?id=<?= $rapor['rapor_id'] ?>" 
                                       class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Rapor Detay Modal -->
    <div id="reportModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-bold text-gray-800" id="modalReportNo"></h3>
                    <button onclick="document.getElementById('reportModal').classList.add('hidden')" 
                            class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-500">Hasta Adı</p>
                        <p class="font-medium" id="modalHastaAdi"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Doktor</p>
                        <p class="font-medium" id="modalDoktorAdi"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Rapor Türü</p>
                        <p class="font-medium" id="modalRaporTuru"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Durum</p>
                        <p class="font-medium" id="modalDurum"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tarih</p>
                        <p class="font-medium" id="modalTarih"></p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-500">Tahlil Sonuçları</p>
                    <div class="mt-1 p-3 bg-gray-50 rounded-lg whitespace-pre-line" id="modalTahlilSonuclari"></div>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-500">Hekim Görüşü</p>
                    <div class="mt-1 p-3 bg-gray-50 rounded-lg whitespace-pre-line" id="modalHekimGorusu"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Rapor detaylarını göster
        function showReportDetails(raporNo, hastaAdi, doktorAdi, raporTuru, tahlilSonuclari, hekimGorusu, durum, tarih) {
            document.getElementById('modalReportNo').textContent = 'Rapor No: ' + raporNo;
            document.getElementById('modalHastaAdi').textContent = hastaAdi;
            document.getElementById('modalDoktorAdi').textContent = doktorAdi;
            document.getElementById('modalRaporTuru').textContent = raporTuru;
            document.getElementById('modalTahlilSonuclari').textContent = tahlilSonuclari;
            document.getElementById('modalHekimGorusu').textContent = hekimGorusu;
            document.getElementById('modalDurum').textContent = durum;
            document.getElementById('modalTarih').textContent = tarih;
            
            // Durum rengini ayarla
            const durumElement = document.getElementById('modalDurum');
            durumElement.className = 'font-medium';
            
            if (durum === 'Onaylandı') {
                durumElement.classList.add('text-green-600');
            } else if (durum === 'Reddedildi') {
                durumElement.classList.add('text-red-600');
            } else {
                durumElement.classList.add('text-yellow-600');
            }
            
            document.getElementById('reportModal').classList.remove('hidden');
        }
    </script>
</body>
</html>
<?php
// Çıktı tamponunu temizle
ob_end_flush();
?>