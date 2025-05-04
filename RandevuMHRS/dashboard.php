<?php
session_start();
require_once "../database/db.php";

if (!isset($_SESSION['hasta_id'])) {
    header("Location: hasta/hasta_login.php");
    exit();
}

$hasta_id = $_SESSION['hasta_id'];

// Hasta bilgilerini çek
$hasta = $db->prepare("SELECT * FROM hastalar WHERE id = ?");
$hasta->execute([$hasta_id]);
$hasta = $hasta->fetch(PDO::FETCH_ASSOC);

// Randevu oluşturma işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['randevu_al'])) {
        $doktor_id = $_POST['doktor'];
        $tarih = $_POST['tarih'] . ' ' . $_POST['saat'] . ':00';
        $poliklinik_id = $_POST['klinik'];
        $hastane_id = $_POST['hastane'];
        
        // Gerekli alanların boş olup olmadığını kontrol et
        if (empty($doktor_id)) {
            echo json_encode([
                'success' => false,
                'message' => 'Lütfen bir doktor seçiniz.'
            ]);
            exit();
        }
        
        try {
            // Aynı hastanın aynı gün ve saatte başka randevusu var mı kontrolü
            $existingAppointment = $db->prepare("
                SELECT id FROM randevular 
                WHERE hasta_id = ? AND tarih = ?
            ");
            $existingAppointment->execute([$hasta_id, $tarih]);
            
            if ($existingAppointment->rowCount() > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Aynı gün ve saatte zaten bir randevunuz bulunmaktadır. Lütfen farklı bir saat seçiniz.'
                ]);
                exit();
            }
            
            // Doktorun o saatte müsait olup olmadığını kontrol et
            $checkDoctorAvailability = $db->prepare("
                SELECT id FROM randevular 
                WHERE doktor_id = ? AND tarih = ?
            ");
            $checkDoctorAvailability->execute([$doktor_id, $tarih]);
            
            if ($checkDoctorAvailability->rowCount() > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Seçtiğiniz saatte doktorun randevusu doludur, lütfen başka bir saat seçiniz.'
                ]);
                exit();
            }
            
            // Randevu oluştur
            $insert = $db->prepare("INSERT INTO randevular (hasta_id, doktor_id, tarih, pol_id, hastane_id, durum) 
                                   VALUES (?, ?, ?, ?, ?, 'Beklemede')");
            $insertResult = $insert->execute([$hasta_id, $doktor_id, $tarih, $poliklinik_id, $hastane_id]);
            
            if ($insertResult) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Randevunuz başarıyla oluşturuldu!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Randevu oluşturulamadı.'
                ]);
            }
            exit();
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Randevu oluşturulurken hata oluştu: ' . $e->getMessage()
            ]);
            exit();
        }
    }
}

// Randevuları çek
$randevular = $db->prepare("
    SELECT r.id, r.tarih, TIME(r.tarih) AS saat, 
           d.ad_soyad AS doktor_ad,
           p.ad AS poliklinik_ad, h.h_ad AS hastane_ad,
           r.durum
    FROM randevular r
    JOIN doktorlar d ON r.doktor_id = d.id
    JOIN poliklinikler p ON d.pol_id = p.id
    JOIN hastaneler h ON d.h_id = h.h_id
    WHERE r.hasta_id = ?
    ORDER BY r.tarih DESC
");
$randevular->execute([$hasta_id]);
$randevu_listesi = $randevular->fetchAll(PDO::FETCH_ASSOC);

// Çalışma saatleri
$calisma_saatleri = [
    'baslangic' => '08:00',
    'bitis' => '17:00',
    'mola_baslangic' => '12:00',
    'mola_bitis' => '13:00'
];

// Saatleri oluştur
$musaitSaatler = [];
try {
    $baslangic = new DateTime($calisma_saatleri['baslangic']);
    $bitis = new DateTime($calisma_saatleri['bitis']);
    $interval = new DateInterval('PT15M');
    $period = new DatePeriod($baslangic, $interval, $bitis);

    foreach ($period as $dt) {
        $saat = $dt->format('H:i');
        
        // Mola saatlerini atla
        if ($calisma_saatleri['mola_baslangic'] && $calisma_saatleri['mola_bitis']) {
            $saatObj = new DateTime($saat);
            $molaBaslangic = new DateTime($calisma_saatleri['mola_baslangic']);
            $molaBitis = new DateTime($calisma_saatleri['mola_bitis']);
            
            if ($saatObj >= $molaBaslangic && $saatObj < $molaBitis) {
                continue;
            }
        }
        
        $musaitSaatler[] = $saat;
    }
} catch (Exception $e) {
    $musaitSaatler = [];
}

// İlleri çek
$iller = $db->query("SELECT DISTINCT il FROM hastaneler ORDER BY il ASC")->fetchAll(PDO::FETCH_ASSOC);

// Seçilen değerlere göre verileri çek
$ilceler = [];
$hastaneler = [];
$klinikler = [];
$doktorlar = [];

if (isset($_GET['il'])) {
    $il = $_GET['il'];
    $ilceler = $db->prepare("SELECT DISTINCT ilce FROM hastaneler WHERE il = ? ORDER BY ilce ASC");
    $ilceler->execute([$il]);
    $ilceler = $ilceler->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET['ilce'])) {
    $ilce = $_GET['ilce'];
    $hastaneler = $db->prepare("SELECT h_id, h_ad FROM hastaneler WHERE ilce = ? ORDER BY h_ad ASC");
    $hastaneler->execute([$ilce]);
    $hastaneler = $hastaneler->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET['hastane_id'])) {
    $hastane_id = $_GET['hastane_id'];
    $klinikler = $db->prepare("SELECT id, ad FROM poliklinikler WHERE hastane_id = ? ORDER BY ad ASC");
    $klinikler->execute([$hastane_id]);
    $klinikler = $klinikler->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET['klinik_id']) && isset($_GET['hastane_id'])) {
    $klinik_id = $_GET['klinik_id'];
    $hastane_id = $_GET['hastane_id'];
    $doktorlar = $db->prepare("SELECT id, ad_soyad FROM doktorlar WHERE pol_id = ? AND h_id = ? ORDER BY ad_soyad ASC");
    $doktorlar->execute([$klinik_id, $hastane_id]);
    $doktorlar = $doktorlar->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MHRS - Hasta Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
    .calendar-day {
        cursor: pointer;
        transition: all 0.2s;
    }

    .calendar-day:hover {
        background-color: #EFF6FF;
    }

    .calendar-day.selected {
        background-color: #3B82F6;
        color: white;
    }

    .calendar-day.disabled {
        color: #D1D5DB;
        cursor: not-allowed;
    }

    .time-slot {
        cursor: pointer;
        transition: all 0.2s;
    }

    .time-slot:hover {
        background-color: #EFF6FF;
    }

    .time-slot.selected {
        background-color: #3B82F6;
        color: white;
    }

    .time-slot.booked {
        background-color: #FECACA;
        color: #B91C1C;
        cursor: not-allowed;
    }
    </style>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-blue-600 text-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-hospital-alt text-2xl"></i>
                    <h1 class="text-2xl font-bold">MHRS</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="font-medium"><?= htmlspecialchars($hasta['ad'] . ' ' . $hasta['soyad']) ?></span>
                    <a href="../hasta/hasta_login.php" class="hover:text-blue-200">
                        <i class="fas fa-sign-out-alt"></i> Çıkış
                    </a>
                </div>
            </div>
        </header>

        <!-- Hata ve Başarı Mesajları -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="max-w-7xl mx-auto px-4 py-2">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?= $_SESSION['error'] ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3"
                    onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </span>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="max-w-7xl mx-auto px-4 py-2">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?= $_SESSION['success'] ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3"
                    onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </span>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <!-- Randevu Alma Kartı -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8 border border-blue-100">
                <div class="px-6 py-4 bg-blue-50 border-b border-blue-200">
                    <h2 class="text-lg font-semibold text-blue-800">
                        <i class="fas fa-calendar-plus mr-2"></i> Randevu Alma
                    </h2>
                </div>
                <div class="p-6">
                    <!-- Adım 1: Konum ve Kurum Seçimi -->
                    <div id="step1" class="space-y-6">
                        <h3 class="text-md font-medium text-gray-700 border-b pb-2">1. Konum ve Kurum Seçimi</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- İl Seçimi -->
                            <div>
                                <label for="il" class="block text-sm font-medium text-gray-700 mb-1">İl</label>
                                <select id="il" name="il"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
                                    <option value="">İl Seçiniz</option>
                                    <?php foreach ($iller as $il): ?>
                                    <option value="<?= htmlspecialchars($il['il']) ?>"
                                        <?= isset($_GET['il']) && $_GET['il'] == $il['il'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($il['il']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- İlçe Seçimi -->
                            <div>
                                <label for="ilce" class="block text-sm font-medium text-gray-700 mb-1">İlçe</label>
                                <select id="ilce" name="ilce"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
                                    <?= empty($ilceler) ? 'disabled' : '' ?>>
                                    <option value="">İlçe Seçiniz</option>
                                    <?php foreach ($ilceler as $ilce): ?>
                                    <option value="<?= htmlspecialchars($ilce['ilce']) ?>"
                                        <?= isset($_GET['ilce']) && $_GET['ilce'] == $ilce['ilce'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ilce['ilce']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Hastane Seçimi -->
                            <div>
                                <label for="hastane"
                                    class="block text-sm font-medium text-gray-700 mb-1">Hastane</label>
                                <select id="hastane" name="hastane"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
                                    <?= empty($hastaneler) ? 'disabled' : '' ?>>
                                    <option value="">Hastane Seçiniz</option>
                                    <?php foreach ($hastaneler as $hastane): ?>
                                    <option value="<?= $hastane['h_id'] ?>"
                                        <?= isset($_GET['hastane_id']) && $_GET['hastane_id'] == $hastane['h_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($hastane['h_ad']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Klinik Seçimi -->
                            <div>
                                <label for="klinik" class="block text-sm font-medium text-gray-700 mb-1">Klinik</label>
                                <select id="klinik" name="klinik"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
                                    <?= empty($klinikler) ? 'disabled' : '' ?>>
                                    <option value="">Klinik Seçiniz</option>
                                    <?php foreach ($klinikler as $klinik): ?>
                                    <option value="<?= $klinik['id'] ?>"
                                        <?= isset($_GET['klinik_id']) && $_GET['klinik_id'] == $klinik['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($klinik['ad']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button id="nextStep1" type="button"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                                <?= empty($klinikler) ? 'disabled' : '' ?>>
                                Sonraki <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Adım 2: Doktor ve Tarih Seçimi -->
                    <div id="step2" class="space-y-6 hidden">
                        <h3 class="text-md font-medium text-gray-700 border-b pb-2">2. Doktor ve Tarih Seçimi</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Doktor Seçimi -->
                            <div>
                                <label for="doktor" class="block text-sm font-medium text-gray-700 mb-1">Doktor</label>
                                <select id="doktor" name="doktor"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
                                    <?= empty($doktorlar) ? 'disabled' : '' ?>>
                                    <option value="">Doktor Seçiniz</option>
                                    <?php foreach ($doktorlar as $doktor): ?>
                                    <option value="<?= $doktor['id'] ?>"
                                        <?= isset($_GET['doktor_id']) && $_GET['doktor_id'] == $doktor['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($doktor['ad_soyad']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Takvim -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Randevu Tarihi</label>
                                <div class="bg-white rounded-md border border-gray-200 p-4">
                                    <div class="flex justify-between items-center mb-4">
                                        <button id="prevMonth" class="p-1 rounded-full hover:bg-gray-100">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <h4 id="currentMonth" class="font-medium"></h4>
                                        <button id="nextMonth" class="p-1 rounded-full hover:bg-gray-100">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-7 gap-1 text-center text-sm">
                                        <div class="font-medium text-gray-500 py-1">Pzt</div>
                                        <div class="font-medium text-gray-500 py-1">Sal</div>
                                        <div class="font-medium text-gray-500 py-1">Çar</div>
                                        <div class="font-medium text-gray-500 py-1">Per</div>
                                        <div class="font-medium text-gray-500 py-1">Cum</div>
                                        <div class="font-medium text-gray-500 py-1">Cmt</div>
                                        <div class="font-medium text-gray-500 py-1">Paz</div>

                                        <div id="calendarDays" class="col-span-7 grid grid-cols-7 gap-1"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <button id="prevStep2" type="button"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                <i class="fas fa-arrow-left mr-2"></i> Önceki
                            </button>
                            <button id="nextStep2" type="button"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                                disabled>
                                Sonraki <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Adım 3: Saat Seçimi ve Onay -->
                    <div id="step3" class="space-y-6 hidden">
                        <h3 class="text-md font-medium text-gray-700 border-b pb-2">3. Saat Seçimi ve Onay</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Seçilen Bilgiler -->
                            <div>
                                <div class="bg-blue-50 rounded-md p-4 border border-blue-100">
                                    <h4 class="font-medium text-blue-800 mb-2">Randevu Bilgileri</h4>
                                    <div class="space-y-2">
                                        <p><span class="font-medium">Hastane:</span> <span id="selectedHospital"></span>
                                        </p>
                                        <p><span class="font-medium">Klinik:</span> <span id="selectedClinic"></span>
                                        </p>
                                        <p><span class="font-medium">Doktor:</span> <span id="selectedDoctor"></span>
                                        </p>
                                        <p><span class="font-medium">Tarih:</span> <span id="selectedDate"></span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Saat Seçimi -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Randevu Saati</label>
                                <div class="grid grid-cols-3 gap-2" id="timeSlots">
                                    <?php foreach ($musaitSaatler as $saat): ?>
                                    <div class="time-slot border border-gray-200 rounded-md p-2 text-center"
                                        data-time="<?= $saat ?>">
                                        <?= $saat ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <button id="prevStep3" type="button"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                <i class="fas fa-arrow-left mr-2"></i> Önceki
                            </button>
                            <button id="confirmAppointment" type="button"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50"
                                disabled>
                                Randevu Al <i class="fas fa-check ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Randevu Listesi -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-history mr-2"></i> Randevu Geçmişim
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tarih/Saat</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hastane</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Klinik</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Doktor</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Durum</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($randevu_listesi as $randevu): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= date('d.m.Y H:i', strtotime($randevu['tarih'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($randevu['hastane_ad']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= htmlspecialchars($randevu['poliklinik_ad']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($randevu['doktor_ad']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $randevu['durum'] == 'Onaylandı' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $randevu['durum'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($randevu['durum'] == 'Onaylandı'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="randevu_id" value="<?= $randevu['id'] ?>">
                                        <button type="submit" name="randevu_iptal"
                                            class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('Randevuyu iptal etmek istediğinize emin misiniz?')">
                                            <i class="fas fa-times-circle mr-1"></i> İptal Et
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
                // Takvim için gerekli değişkenler
                const monthNames = ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül",
                    "Ekim", "Kasım", "Aralık"
                ];
                const dayNames = ["Pazar", "Pazartesi", "Salı", "Çarşamba", "Perşembe", "Cuma", "Cumartesi"];
                let currentDate = new Date();
                let currentMonth = currentDate.getMonth();
                let currentYear = currentDate.getFullYear();
                let selectedDate = null;

                // Adım geçişleri
                $('#nextStep1').click(function() {
                    $('#step1').addClass('hidden');
                    $('#step2').removeClass('hidden');
                    generateCalendar(currentMonth, currentYear);
                });

                $('#prevStep2').click(function() {
                    $('#step2').addClass('hidden');
                    $('#step1').removeClass('hidden');
                });

                $('#nextStep2').click(function() {
                    if (!selectedDate) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata!',
                            text: 'Lütfen bir tarih seçiniz!'
                        });
                        return;
                    }
                    $('#step2').addClass('hidden');
                    $('#step3').removeClass('hidden');
                    updateSummary();
                });

                $('#prevStep3').click(function() {
                    $('#step3').addClass('hidden');
                    $('#step2').removeClass('hidden');
                });

                // Özet bilgileri güncelle
                function updateSummary() {
                    $('#selectedHospital').text($('#hastane option:selected').text());
                    $('#selectedClinic').text($('#klinik option:selected').text());
                    $('#selectedDoctor').text($('#doktor option:selected').text());

                    if (selectedDate) {
                        $('#selectedDate').text(
                            `${selectedDate.day} ${monthNames[selectedDate.month]} ${selectedDate.year}`
                        );
                    }

                    // Saat seçeneklerini yükle
                    loadTimeSlots();
                }

                // Saat seçeneklerini yükle
                function loadTimeSlots() {
                    $('#timeSlots').html('');
                    <?php foreach ($musaitSaatler as $saat): ?>
                    $('#timeSlots').append(`
                    <div class="time-slot border border-gray-200 rounded-md p-2 text-center" data-time="<?= $saat ?>">
                        <?= $saat ?>
                    </div>
                `);
                    <?php endforeach; ?>

                    $('.time-slot').click(function() {
                        $('.time-slot').removeClass('selected');
                        $(this).addClass('selected');
                        $('#confirmAppointment').prop('disabled', false);
                    });
                }

                // Takvim oluşturma fonksiyonu
                function generateCalendar(month, year) {
                    const firstDay = new Date(year, month, 1);
                    const lastDay = new Date(year, month + 1, 0);
                    const daysInMonth = lastDay.getDate();

                    $('#currentMonth').text(`${monthNames[month]} ${year}`);

                    let html = '';

                    // Boş hücreler (ayın ilk gününden önce)
                    for (let i = 0; i < firstDay.getDay(); i++) {
                        html += '<div class="py-2"></div>';
                    }

                    // Günler
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    for (let i = 1; i <= daysInMonth; i++) {
                        const currentDate = new Date(year, month, i);
                        const isDisabled = !isAvailableDay(currentDate) || currentDate < today;

                        html += `
                    <div class="calendar-day py-2 rounded-md ${isDisabled ? 'disabled' : ''}" 
                         data-day="${i}" data-month="${month}" data-year="${year}"
                         ${isDisabled ? '' : 'onclick="selectDay(this)"'}>
                        ${i}
                    </div>
                `;
                    }

                    $('#calendarDays').html(html);
                }

                // Doktorun müsait olduğu günleri kontrol et
                function isAvailableDay(date) {
                    const day = date.getDay();
                    // Örnek: Pazartesi(1) ile Perşembe(4) arası çalışıyor
                    return day >= 1 && day <= 4;
                }

                // Gün seçimi (global fonksiyon)
                window.selectDay = function(element) {
                    $('.calendar-day').removeClass('selected');
                    $(element).addClass('selected');

                    selectedDate = {
                        day: $(element).data('day'),
                        month: $(element).data('month'),
                        year: $(element).data('year')
                    };

                    $('#nextStep2').prop('disabled', false);
                };

                // Ay değiştirme
                $('#prevMonth').click(function() {
                    currentMonth--;
                    if (currentMonth < 0) {
                        currentMonth = 11;
                        currentYear--;
                    }
                    generateCalendar(currentMonth, currentYear);
                    $('.calendar-day.selected').removeClass('selected');
                    selectedDate = null;
                    $('#nextStep2').prop('disabled', true);
                });

                $('#nextMonth').click(function() {
                    currentMonth++;
                    if (currentMonth > 11) {
                        currentMonth = 0;
                        currentYear++;
                    }
                    generateCalendar(currentMonth, currentYear);
                    $('.calendar-day.selected').removeClass('selected');
                    selectedDate = null;
                    $('#nextStep2').prop('disabled', true);
                });

                // İl-İlçe-Hastane-Klinik-Doktor zinciri
                $('#il').change(function() {
                    const il = $(this).val();
                    if (il) {
                        $('#ilce').prop('disabled', false).html('<option value="">Yükleniyor...</option>');
                        $.get('ajax/get_ilceler.php', {
                            il: il
                        }, function(data) {
                            $('#ilce').html(data);
                            $('#hastane, #klinik, #doktor').val('').prop('disabled', true);
                            $('#nextStep1').prop('disabled', true);
                        }).fail(function() {
                            $('#ilce').html('<option value="">Yükleme hatası</option>');
                        });
                    } else {
                        $('#ilce, #hastane, #klinik, #doktor').val('').prop('disabled', true);
                        $('#nextStep1').prop('disabled', true);
                    }
                });

                $('#ilce').change(function() {
                    const ilce = $(this).val();
                    if (ilce) {
                        $('#hastane').prop('disabled', false).html('<option value="">Yükleniyor...</option>');
                        $.get('ajax/get_hastaneler.php', {
                            ilce: ilce
                        }, function(data) {
                            $('#hastane').html(data);
                            $('#klinik, #doktor').val('').prop('disabled', true);
                            $('#nextStep1').prop('disabled', true);
                        }).fail(function() {
                            $('#hastane').html('<option value="">Yükleme hatası</option>');
                        });
                    } else {
                        $('#hastane, #klinik, #doktor').val('').prop('disabled', true);
                        $('#nextStep1').prop('disabled', true);
                    }
                });

                $('#hastane').change(function() {
                    const hastane_id = $(this).val();
                    if (hastane_id) {
                        $('#klinik').prop('disabled', false).html('<option value="">Yükleniyor...</option>');
                        $.get('ajax/get_klinikler.php', {
                            hastane_id: hastane_id
                        }, function(data) {
                            $('#klinik').html(data);
                            $('#doktor').val('').prop('disabled', true);
                            $('#nextStep1').prop('disabled', true);
                        }).fail(function() {
                            $('#klinik').html('<option value="">Yükleme hatası</option>');
                        });
                    } else {
                        $('#klinik, #doktor').val('').prop('disabled', true);
                        $('#nextStep1').prop('disabled', true);
                    }
                });

                $('#klinik').change(function() {
                    const klinik_id = $(this).val();
                    const hastane_id = $('#hastane').val();
                    if (klinik_id && hastane_id) {
                        $('#doktor').prop('disabled', false).html('<option value="">Yükleniyor...</option>');
                        $.get('ajax/get_doktorlar.php', {
                            klinik_id: klinik_id,
                            hastane_id: hastane_id
                        }, function(data) {
                            $('#doktor').html(data);
                            $('#nextStep1').prop('disabled', false);
                        }).fail(function() {
                            $('#doktor').html('<option value="">Yükleme hatası</option>');
                        });
                    } else {
                        $('#doktor').val('').prop('disabled', true);
                        $('#nextStep1').prop('disabled', true);
                    }
                });

                // Randevu onayı
$('#confirmAppointment').click(function() {
    const selectedTime = $('.time-slot.selected').data('time');
    const selectedDoctor = $('#doktor').val();
    
    if (!selectedDate || !selectedTime) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Lütfen tarih ve saat seçiniz!'
        });
        return;
    }
    
    if (!selectedDoctor) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Lütfen bir doktor seçiniz!'
        });
        return;
    }
    
    const day = selectedDate.day.toString().padStart(2, '0');
    const month = (selectedDate.month + 1).toString().padStart(2, '0');
    const year = selectedDate.year;
    
    const formattedDate = `${year}-${month}-${day}`;
    
    // Form verilerini topla ve gönder
    const formData = {
        hasta_id: <?= $hasta_id ?>,
        doktor: selectedDoctor,
        tarih: formattedDate,
        saat: selectedTime,
        klinik: $('#klinik').val(),
        hastane: $('#hastane').val(),
        randevu_al: true
    };
    
    // AJAX ile form gönderimi
    $.ajax({
        url: 'dashboard.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: response.message,
                    showConfirmButton: true
                }).then((result) => {
                    window.location.href = 'dashboard.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: response.message,
                    showConfirmButton: true
                }).then((result) => {
                    if (response.message.includes('Aynı gün ve saatte zaten bir randevunuz') || 
                        response.message.includes('doktorun randevusu doludur')) {
                        window.location.href = 'dashboard.php';
                    }
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Sunucu hatası: ' + xhr.statusText
            });
        }

})
                    // İlk takvimi oluştur
                    generateCalendar(currentMonth, currentYear);
                });
            });
    </script>
</body>

</html>