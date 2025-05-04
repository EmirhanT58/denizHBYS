<?php
session_start();


date_default_timezone_set("Europe/istanbul");
include "../database/db.php";
if (!isset($_SESSION['hasta_id'])) {
    header("Location: hasta_login.php?error=Yetkisiz giriş");
    exit();
} 
$hasta_id = $_SESSION['hasta_id'];

try {
    $stmt = $db->prepare("
        SELECT 
            z.h_takip_kodu AS htk,
            h.h_ad AS hastane_ad, 
            z.tarih, 
            p.ad AS brans_ad, 
            d.ad_soyad AS doktor_ad, 
            d.unvan AS doktor_unvan
        FROM h_ziyaretler z
        JOIN hastaneler h ON z.hastane_id = h.h_id
        JOIN poliklinikler p ON z.poliklinik_id = p.id
        JOIN doktorlar d ON z.doktor_id = d.id
        WHERE z.hasta_id = :hasta_id
        ORDER BY z.tarih DESC
    ");
    $stmt->bindParam(':hasta_id', $hasta_id, PDO::PARAM_INT);  // Giriş yapan hasta_id'yi bağla
    $stmt->execute();
    $ziyaretler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ziyaret verileri çekilemedi: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasta Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Sidebar -->
    <?php include "sidebar.php"; ?>

    <!-- Ana İçerik -->
    <main class="ml-72 p-8 w-full max-w-7xl">
        <div class="bg-white p-8 rounded-lg shadow-sm">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Hoş Geldiniz,
                        <span class="text-blue-600">
                            <?php echo $_SESSION["hasta_adi"]; ?>
                        </span>
                    </h1>
                    <p class="text-gray-500 text-sm mt-1">
                        Son aktivite: <?php echo date('d.m.Y H:i:s'); ?>
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="h-10 w-px bg-gray-200"></div>
                    <div class="flex items-center gap-3">
                        <img src="../img/sb.png" alt="Profil" class="w-10 h-10 rounded-full border-2 border-blue-100">
                        <span class="text-gray-700 font-medium">T.C. Sağlık Bakanlığı</span>
                    </div>
                </div>
            </div>
        </div>

        <h1 class="mt-8 text-xl font-semibold text-gray-900">Son Ziyaretlerim</h1>
        <div class="mt-4 bg-white rounded-lg shadow-sm">
            <div class="overflow-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-3 text-left text-sm font-medium text-gray-500">Hastane Takip Kodu</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-500">Hastane Adı</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-500">Poliklinik</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-500">Doktor Adı</th>
                            <th class="p-3 text-left text-sm font-medium text-gray-500">Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($ziyaretler)) {
                            foreach ($ziyaretler as $ziyaret) {
                                echo "<tr class='border-b border-gray-100 hover:bg-gray-50'>";
                                echo "<td class='p-3 text-sm text-gray-700'>" . htmlspecialchars($ziyaret['htk']) . "</td>";
                                echo "<td class='p-3 text-sm text-gray-700'>" . htmlspecialchars($ziyaret['hastane_ad']) ."</td>";
                                echo "<td class='p-3 text-sm text-gray-700'>" . htmlspecialchars($ziyaret['brans_ad']) . "</td>";
                                echo "<td class='p-3 text-sm text-gray-700'>" . htmlspecialchars($ziyaret['doktor_unvan']). " ". htmlspecialchars($ziyaret['doktor_ad']) . " " . htmlspecialchars($ziyaret['doktor_soyad']) . "</td>";
                                echo "<td class='p-3 text-sm text-gray-700'>" . date("d.m.Y", strtotime($ziyaret['tarih'])) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='p-6 text-center text-gray-400'>
                                    <i class='fas fa-calendar-times text-2xl mb-2'></i>
                                    <p class='text-sm'>Kayıtlı Ziyaret bulunamadı</p>
                                  </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>