<?php
# Oturum Kontrolü
require '../database/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['doktor_id'])) {
    header("Location: doktor_login.php");
    exit();
}
$doctor_id = $_SESSION['doktor_id'] ?? 0;
$doctor_ad = $_SESSION['doktor_ad'] ?? '';

$sql = "SELECT 
    p.ad,
    dp.program_turu, 
    dp.mesai_turu, 
    dp.nerde, 
    dp.baslangic_saati, 
    dp.bitis_saati, 
    dp.aktif, 
    dp.created_at, 
    dp.updated_at 
FROM 
    doktor_programlari dp
INNER JOIN 
    poliklinikler p ON dp.poliklinik_id = p.id
WHERE 
    dp.doktor_id = ?;";

$stmt = $db->prepare($sql);
$stmt->bindParam(1, $doctor_id, PDO::PARAM_INT);
$stmt->execute();
$program = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doktor Programı</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="flex">
        <?php include 'navbar.php'; ?>
        <div class="container mx-auto mt-8 px-4">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Programım</h1>
    <div class="overflow-x-auto rounded-lg shadow-md">
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-gradient-to-r from-blue-50 to-gray-50">
                <tr>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Poliklinik</th>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Program Türü</th>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mesai Türü</th>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nerede</th>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Başlangıç Saati</th>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Bitiş Saati</th>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aktif</th>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Oluşturulma Tarihi</th>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Güncellenme Tarihi</th>
                </tr>
            </thead>
                <tbody>
                    <?php foreach ($program as $row): ?>
                        <tr>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['ad']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['program_turu']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['mesai_turu']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['nerde']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['baslangic_saati']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['bitis_saati']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['aktif'] ? 'Evet' : 'Hayır') ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['created_at']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($row['updated_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
