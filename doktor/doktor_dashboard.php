<?php
require_once "../database/db.php";
include "../yonetici/ipbilgisi.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Oturum kontrolü
if (!isset($_SESSION['doktor_id'])) {
    header("Location: doktor_login.php");
    exit();
}
// Doktor ID'sini oturumdan al
$doctor_id = $_SESSION['doktor_id'] ?? 0;
$doctor_ad = $_SESSION['doktor_ad'] ?? '';

$girisTarihi = date("d/m/Y H:i:s");

// Doktorun son randevularını çek
$hasta = $db->prepare("
SELECT r.tarih, r.aciklama, d.unvan, h.ad, h.soyad, r.durum, d.ad_soyad AS doktor_ad
FROM randevular r
INNER JOIN hastalar h ON r.hasta_id = h.id
INNER JOIN doktorlar d ON r.doktor_id = d.id
WHERE r.doktor_id = :doktor_id
ORDER BY r.tarih DESC
LIMIT 5
"); 

$hasta->bindParam(':doktor_id', $doctor_id, PDO::PARAM_INT);
$hasta->execute();
$son_randevular = $hasta->fetchAll(PDO::FETCH_ASSOC);

// Doktorun aktif randevu sayısını çek
$randevuSayisi = $db->prepare("SELECT COUNT(*) as count FROM randevular WHERE doktor_id = :doktor_id AND durum = 'Onaylandı'");
$randevuSayisi->bindParam(':doktor_id', $doctor_id, PDO::PARAM_INT);
$randevuSayisi->execute();
$aktifRandevuSayisi = $randevuSayisi->fetch(PDO::FETCH_ASSOC)['count'];

// Doktorun toplam hasta sayısını çek (farklı hasta sayısı)
$hastaSayisi = $db->prepare("SELECT COUNT(DISTINCT hasta_id) as count FROM randevular WHERE doktor_id = :doktor_id");
$hastaSayisi->bindParam(':doktor_id', $doctor_id, PDO::PARAM_INT);
$hastaSayisi->execute();
$aktifHastaSayisi = $hastaSayisi->fetch(PDO::FETCH_ASSOC)['count'];

// Yaklaşan randevuları çek
$yaklasanRandevular = $db->prepare("
SELECT r.tarih, h.ad, h.soyad, r.durum 
FROM randevular r
INNER JOIN hastalar h ON r.hasta_id = h.id
WHERE r.doktor_id = :doktor_id
AND r.tarih >= NOW() 
AND r.durum = 'Onaylandı'
ORDER BY r.tarih ASC
LIMIT 3
");
$yaklasanRandevular->bindParam(':doktor_id', $doctor_id, PDO::PARAM_INT);
$yaklasanRandevular->execute();
$yaklasanRandevuListesi = $yaklasanRandevular->fetchAll(PDO::FETCH_ASSOC);

$bugunkuRandevuSayisi = $db->prepare("SELECT COUNT(*) as count FROM randevular WHERE doktor_id = :doktor_id AND DATE(tarih) = CURDATE() AND durum = 'Onaylandı'");
$bugunkuRandevuSayisi->bindParam(':doktor_id', $doctor_id, PDO::PARAM_INT);
$bugunkuRandevuSayisi->execute();
$bugunkuRandevuSayisiCount = $bugunkuRandevuSayisi->fetch(PDO::FETCH_ASSOC)['count'];


$haftalikRandevular = $db->prepare("
    SELECT 
        DAYOFWEEK(tarih) as gun, 
        COUNT(*) as sayi 
    FROM randevular 
    WHERE doktor_id = :doktor_id 
    AND YEARWEEK(tarih, 1) = YEARWEEK(CURDATE(), 1)
    GROUP BY DAYOFWEEK(tarih)
    ORDER BY gun
");
$haftalikRandevular->bindParam(':doktor_id', $doctor_id, PDO::PARAM_INT);
$haftalikRandevular->execute();
$haftalikData = array_fill(1, 7, 0);
foreach ($haftalikRandevular->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $haftalikData[$row['gun']] = $row['sayi'];
}

// Aylık veri (4 hafta)
$aylikRandevular = $db->prepare("
    SELECT 
        WEEK(tarih, 1) - WEEK(DATE_SUB(tarih, INTERVAL DAYOFMONTH(tarih)-1 DAY), 1) + 1 as hafta,
        COUNT(*) as sayi
    FROM randevular
    WHERE doktor_id = :doktor_id 
    AND MONTH(tarih) = MONTH(CURRENT_DATE())
    AND YEAR(tarih) = YEAR(CURRENT_DATE())
    GROUP BY hafta
    ORDER BY hafta
");
$aylikRandevular->bindParam(':doktor_id', $doctor_id, PDO::PARAM_INT);
$aylikRandevular->execute();
$aylikData = array_fill(1, 5, 0);
foreach ($aylikRandevular->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $hafta = min($row['hafta'], 5); // En fazla 5 hafta göster
    $aylikData[$hafta] = $row['sayi'];
}

// Yıllık veri
$yillikRandevular = $db->prepare("
    SELECT 
        MONTH(tarih) as ay,
        COUNT(*) as sayi
    FROM randevular
    WHERE doktor_id = :doktor_id 
    AND YEAR(tarih) = YEAR(CURRENT_DATE())
    GROUP BY ay
    ORDER BY ay
");
$yillikRandevular->bindParam(':doktor_id', $doctor_id, PDO::PARAM_INT);
$yillikRandevular->execute();
$yillikData = array_fill(1, 12, 0);
foreach ($yillikRandevular->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $yillikData[$row['ay']] = $row['sayi']; }

?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doktor Paneli - <?php echo htmlspecialchars($doctor_ad); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: {
                        50: '#f0f9ff',
                        100: '#e0f2fe',
                        200: '#bae6fd',
                        300: '#7dd3fc',
                        400: '#38bdf8',
                        500: '#0ea5e9',
                        600: '#0284c7',
                        700: '#0369a1',
                        800: '#075985',
                        900: '#0c4a6e',
                    }
                }
            }
        }
    }
    </script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    body {
        font-family: 'Inter', sans-serif;
    }

    .sidebar {
        transition: all 0.3s;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .card-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e2e8f0;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .stat-card {
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="h-8 w-8 rounded-md bg-primary-600 flex items-center justify-center mr-2">
                            <i class="fas fa-heartbeat text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-semibold text-gray-800">DenizHBYS</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-2">
                            <div class="text-right hidden md:block">
                                <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($doctor_ad); ?>
                                </p>
                                <p class="text-xs text-gray-500">Doktor</p>
                            </div>
                            <div
                                class="h-8 w-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center">
                                <i class="fas fa-user-md text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="sidebar w-64 hidden md:block border-r border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div
                        class="h-10 w-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center">
                        <i class="fas fa-user-md text-sm"></i>
                    </div>
                    <div class="">
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($doctor_ad); ?></p>
                        <p class="text-xs text-gray-500">Doktor Paneli</p>
                    </div>
                </div>
            </div>
            <nav class="p-4">
                <ul class="space-y-1">
                    <li>
                        <a href="doktor_dashboard.php"
                            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-home mr-3 text-primary-500"></i>
                            Ana Sayfa
                        </a>
                    </li>
                    <li>
                        <a href="hastalar.php"
                            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-user-injured mr-3 text-gray-500"></i>
                            Hasta Yönetimi
                        </a>
                    </li>
                    <li>
                        <a href="randevular.php"
                            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-calendar-check mr-3 text-gray-500"></i>
                            Randevu Yönetimi
                        </a>
                    </li>
                    <li>
                        <a href="raporlar.php"
                            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-file-medical mr-3 text-gray-500"></i>
                            Raporlar
                        </a>
                    </li>
                    <li>
                        <a href="recete.php"
                            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-file-medical mr-3 text-gray-500"></i>
                            Reçeteler
                        </a>
                    </li>
                    <li>
                        <a href="programim.php"
                            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-search mr-3 text-gray-500"></i>
                            Çalışma Programım
                        </a>
                    </li>
                    <li>
                        <a href="ayarlar.php"
                            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-cog mr-3 text-gray-500"></i>
                            Ayarlar
                        </a>
                    </li>
                    <li class="pt-4 border-t border-gray-200">
                        <a href="logout.php"
                            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-red-50 hover:text-red-700">
                            <i class="fas fa-sign-out-alt mr-3 text-gray-500"></i>
                            Çıkış Yap
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 min-w-0 p-4 md:p-6">
            <!-- Welcome Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 card-hover">
                <div class="p-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Hoş Geldiniz, <span
                                    class="text-primary-600"><?php echo htmlspecialchars($doctor_ad); ?></span></h2>
                            <p class="text-gray-600 text-sm mt-1">Son giriş: <span
                                    class="font-medium"><?php echo $ip . " - " . $girisTarihi; ?></span></p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <a href="randevular.php"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                                <i class="fas fa-plus mr-2"></i> Yeni Randevu
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Toplam Hasta -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 stat-card">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-50 text-blue-600 mr-4">
                            <i class="fas fa-user-injured text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Toplam Hasta</p>
                            <p class="text-xl font-bold text-gray-800 mt-1"><?= $aktifHastaSayisi ?></p>
                        </div>
                    </div>
                </div>

                <!-- Aktif Randevular -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 stat-card">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-50 text-green-600 mr-4">
                            <i class="fas fa-calendar-check text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Aktif Randevular</p>
                            <p class="text-xl font-bold text-gray-800 mt-1"><?= $aktifRandevuSayisi ?></p>
                        </div>
                    </div>
                </div>

                <!-- Bugünkü Randevular -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 stat-card">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-50 text-yellow-600 mr-4">
                            <i class="fas fa-calendar-day text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Bugünkü Randevular</p>
                            <p class="text-xl font-bold text-gray-800 mt-1"><?= $bugunkuRandevuSayisiCount ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            <!-- Randevu Grafiği -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 lg:col-span-2 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Randevu Dağılımı</h3>
                        <p class="text-xs text-gray-500 mt-1">Son 12 aylık randevu istatistikleri</p>
                    </div>
                    <div class="flex space-x-2">
                        <select id="timeRange"
                            class="text-xs border border-gray-200 rounded-md px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-gray-50">
                            <option value="week">Bu Hafta</option>
                            <option value="month">Bu Ay</option>
                            <option value="year" selected>Bu Yıl</option>
                        </select>
                       
                    </div>
                </div>

                <div class="h-64">
                    <canvas id="appointmentChart"></canvas>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="h-3 w-3 rounded-full bg-primary-500 mr-2"></div>
                        <span class="text-xs text-gray-600">Randevu Sayısı</span>
                    </div>
                    <div class="text-xs text-gray-500">
                        <span class="font-medium text-primary-600"><?= array_sum($yillikData) ?></span> toplam randevu
                    </div>
                </div>
            </div>


            <!-- Yaklaşan Randevular -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Yaklaşan Randevular</h3>
                    <a href="randevular.php"
                        class="text-sm text-primary-600 hover:text-primary-800 hover:underline">Tümünü Gör</a>
                </div>
                <div class="space-y-3 max-h-64 overflow-y-auto scrollbar pr-2">
                    <?php if (empty($yaklasanRandevuListesi)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-3xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500 text-sm">Yaklaşan randevu bulunmamaktadır</p>
                    </div>
                    <?php else: 
                            foreach ($yaklasanRandevuListesi as $randevu): 
                                $simdi = new DateTime();
                                $randevuTarihi = new DateTime($randevu['tarih']);
                                $kalanZaman = $simdi->diff($randevuTarihi);
                                
                                $kalanText = '';
                                if ($kalanZaman->d > 0) {
                                    $kalanText = $kalanZaman->d . ' gün ' . $kalanZaman->h . ' saat';
                                } elseif ($kalanZaman->h > 0) {
                                    $kalanText = $kalanZaman->h . ' saat ' . $kalanZaman->i . ' dakika';
                                } else {
                                    $kalanText = $kalanZaman->i . ' dakika';
                                }
                        ?>
                    <div
                        class="flex items-start p-3 border border-gray-100 rounded-md hover:bg-gray-50 transition-colors">
                        <div
                            class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center mt-1">
                            <i class="fas fa-calendar-alt text-sm"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($randevu['ad'] . ' ' . $randevu['soyad']) ?></p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <?= date("d/m/Y H:i", strtotime($randevu['tarih'])) ?></p>
                            <p class="text-xs text-primary-600 mt-1"><i class="far fa-clock mr-1"></i> <?= $kalanText ?>
                                sonra</p>
                        </div>
                    </div>
                    <?php endforeach; 
                        endif; ?>
                </div>
            </div>
        </div>

        <!-- Son Randevular -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden card-hover">
            <div class="px-5 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Son Randevular</h3>
                    <a href="randevular.php"
                        class="text-sm text-primary-600 hover:text-primary-800 hover:underline">Tümünü Gör</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Hasta</th>
                            <th scope="col"
                                class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tarih</th>
                            <th scope="col"
                                class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Açıklama</th>
                            <th scope="col"
                                class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Durum</th>
                            <th scope="col"
                                class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($son_randevular as $randevu): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-9 w-9 rounded-full bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600 text-sm"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($randevu['ad'] . ' ' . $randevu['soyad']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date("d/m/Y H:i", strtotime($randevu['tarih'])) ?>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500 max-w-xs truncate">
                                <?= $randevu['aciklama'] ?? "Açıklama Bulunamadı" ?>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <?php
                                    $durum = strtolower($randevu['durum']);
                                    $renk = 'bg-gray-100 text-gray-800';
                                    if ($durum == 'onaylandı') $renk = 'bg-green-100 text-green-800';
                                    elseif ($durum == 'iptal edildi') $renk = 'bg-red-100 text-red-800';
                                    elseif ($durum == 'beklemede') $renk = 'bg-yellow-100 text-yellow-800';
                                    ?>
                                <span
                                    class="px-2.5 py-1 text-xs font-medium rounded-full <?= $renk ?>"><?= ucfirst($durum) ?></span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="#"
                                        class="text-gray-500 hover:text-primary-700 p-1 rounded-md hover:bg-gray-100">
                                        <i class="fas fa-eye text-sm"></i>
                                    </a>
                                    <a href="#"
                                        class="text-gray-500 hover:text-blue-700 p-1 rounded-md hover:bg-gray-100">
                                        <i class="fas fa-edit text-sm"></i>
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
    </div>

    <!-- Mobile bottom navigation -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t border-gray-200">
        <div class="flex justify-around">
            <a href="dashboard.php" class="flex flex-col items-center justify-center p-3 text-primary-600">
                <i class="fas fa-home text-lg"></i>
                <span class="text-xs mt-1">Ana Sayfa</span>
            </a>
            <a href="dashboard.php?sayfa=randevular"
                class="flex flex-col items-center justify-center p-3 text-gray-500 hover:text-gray-700">
                <i class="fas fa-calendar-check text-lg"></i>
                <span class="text-xs mt-1">Randevular</span>
            </a>
            <a href="dashboard.php?sayfa=hastalar"
                class="flex flex-col items-center justify-center p-3 text-gray-500 hover:text-gray-700">
                <i class="fas fa-user-injured text-lg"></i>
                <span class="text-xs mt-1">Hastalar</span>
            </a>
            <a href="dashboard.php?sayfa=ayarlar"
                class="flex flex-col items-center justify-center p-3 text-gray-500 hover:text-gray-700">
                <i class="fas fa-cog text-lg"></i>
                <span class="text-xs mt-1">Ayarlar</span>
            </a>
        </div>
    </div>
</body>
<script>
const ctx = document.getElementById('appointmentChart').getContext('2d');

// PHP'den gelen verileri JavaScript'e aktar
const chartData = {
    week: {
        labels: ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'],
        data: [<?= implode(',', array_values($haftalikData)) ?>]
    },
    month: {
        labels: ['1. Hafta', '2. Hafta', '3. Hafta', '4. Hafta', '5. Hafta'],
        data: [<?= implode(',', array_values($aylikData)) ?>]
    },
    year: {
        labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim',
            'Kasım', 'Aralık'
        ],
        data: [<?= implode(',', array_values($yillikData)) ?>]
    }
};

// Grafik oluştur
let chartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartData.year.labels,
        datasets: [{
            label: 'Randevu Sayısı',
            data: chartData.year.data,
            backgroundColor: '#0ea5e9', // Tailwind primary-500 rengi
            borderRadius: 6,
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                backgroundColor: '#1e293b',
                titleFont: {
                    size: 13,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 12
                },
                padding: 12,
                cornerRadius: 6,
                displayColors: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    drawBorder: false,
                    color: '#e2e8f0'
                },
                ticks: {
                    stepSize: 5,
                    color: '#64748b'
                }
            },
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                },
                ticks: {
                    color: '#64748b'
                }
            }
        }
    }
});

// Zaman aralığı değiştiğinde grafiği güncelle
document.getElementById('timeRange').addEventListener('change', (e) => {
    const selected = e.target.value;
    chartInstance.data.labels = chartData[selected].labels;
    chartInstance.data.datasets[0].data = chartData[selected].data;
    chartInstance.update();
});
</script>

</html>