<?php
session_start();

// Oturum kontrolü
if (!isset($_SESSION['hasta_id'])) {
    header("Location: hasta_login.php");
    exit;
}

$hasta_id = $_SESSION['hasta_id'];

include '../database/db.php';

try {
    $sql = "SELECT 
                r.rapor_id,
                r.rapor_no,
                r.rapor_tur,
                r.tahlil_sonuclari,
                r.hekim_gor,
                r.tarih,
                r.durum,
                h.ad AS hasta_adi,
                h.soyad AS hasta_soyad,
                d.ad_soyad AS doktor_adi,
                d.unvan AS doktor_unvan,
                p.ad AS poliklinik_adi
            FROM raporlar r
            INNER JOIN hastalar h ON r.hasta_id = h.id
            INNER JOIN doktorlar d ON r.doktor_id = d.id
            LEFT JOIN poliklinikler p ON d.pol_id = p.id
            WHERE r.hasta_id = :hasta_id
            ORDER BY r.tarih DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':hasta_id', $hasta_id, PDO::PARAM_INT);
    $stmt->execute();
    $raporlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Raporlarım</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
 
<body class="bg-gray-100 text-gray-800">
   <?php include "sidebar.php"; ?>

    <div class="bg-white rounded-xl shadow-md overflow-hidden w-[1300px] fixed top-[50px] left-[1100px] transform -translate-x-1/2">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-xl font-semibold text-gray-800">Rapor Geçmişim</h2>
            <p class="text-sm text-gray-500 mt-1">Tüm raporlarınız burada listelenmektedir</p>
        </div>

        <div class="overflow-x-auto p-12">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-center">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rapor
                            No</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tür
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Doktor</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Poliklinik</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($raporlar as $rapor): ?>
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-blue-600"><?= htmlspecialchars($rapor['rapor_no']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?= htmlspecialchars($rapor['rapor_tur']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-md text-blue-600"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($rapor['doktor_unvan']) ?>
                                        <?= htmlspecialchars($rapor['doktor_adi']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?= htmlspecialchars($rapor['poliklinik_adi']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?= date("d.m.Y H:i", strtotime($rapor['tarih'])) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?= $rapor['durum'] == 'Onaylandı' ? 'bg-green-100 text-green-800' : 
                               ($rapor['durum'] == 'Rededildi' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                <?= htmlspecialchars($rapor['durum']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap  text-sm font-medium ">
                            <div class="flex space-x-3 text-center">
                                <a href="rapor_indir.php?id=<?= $rapor['rapor_id'] ?>"
                                    class="text-purple-600 hover:text-purple-900" title="PDF İndir">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($raporlar)): ?>
        <div class="p-8 text-center">
            <i class="fas fa-file-medical text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">Henüz kayıtlı raporunuz bulunmamaktadır.</p>
        </div>
        <?php endif; ?>

        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Toplam <span class="font-medium"><?= count($raporlar) ?></span> rapor listeleniyor
            </div>
            <div class="flex space-x-2">
                <button
                    class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button
                    class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</body>

</html>