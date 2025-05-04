<?php
session_start();
include "../database/db.php"; // Veritabanı bağlantısı

// Hasta oturumunda mı kontrol et
if (!isset($_SESSION['hasta_id'])) {
    header("Location: hasta_login.php");
    exit();
}

$hasta_id = $_SESSION['hasta_id'];

// Tahlil sonuçlarını çek
try {
    $query = $db->prepare("
        SELECT t.*, l.islem_adi, l.birim, l.referans_degeri,  d.ad AS doktor_ad, d.soyad AS doktor_soyad
        FROM tahliller t
        JOIN lab_islemler l ON t.lab_islem_id = l.id
        JOIN doktor_bilgiler d ON t.dr_id = d.id
        WHERE t.hasta_id = :hasta_id
        ORDER BY t.tarih DESC
    ");
    $query->bindParam(':hasta_id', $hasta_id, PDO::PARAM_INT);
    $query->execute();
    $tahliller = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Tahlil sonuçları çekilemedi: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tahlil Sonuçlarım</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Sidebar -->
    <?php include "sidebar.php"; ?>

    <!-- Ana İçerik -->
    <main class="ml-64 p-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Tahlil Sonuçlarım</h1>

            <!-- Tahlil Sonuçları Tablosu -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="overflow-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-sm font-semibold uppercase">
                                <th class="px-6 py-4 text-left">İşlem Adı</th>
                                <th class="px-6 py-4 text-left">Sonuç</th>
                                <th class="px-6 py-4 text-left">Birim</th>
                                <th class="px-6 py-4 text-left">Referans Değeri</th>
                                <th class="px-6 py-4 text-left">Rapor</th>
                                <th class="px-6 py-4 text-left">Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tahliller)): ?>
                                <?php foreach ($tahliller as $tahlil): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($tahlil['islem_adi']) ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($tahlil['sonuc']) ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($tahlil['birim']) ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($tahlil['referans_degeri']) ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($tahlil['durum']) ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($tahlil['tarih']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                                        <i class="fas fa-calendar-times text-2xl mb-2"></i>
                                        <p>Kayıtlı tahlil sonucu bulunamadı.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>