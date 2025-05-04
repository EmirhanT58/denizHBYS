<?php

require_once "../database/db.php";

// Oturum kontrolü
if (!isset($_SESSION["yonetici_id"]) || !isset($_SESSION["k_ad"])) {
    header("Location: giris.php");
    exit;
}

// Doktor programlarını veritabanından çekme
$sql = $db->prepare("
    SELECT dp.*, d.ad_soyad AS doktor_adi, p.ad AS poliklinik_adi 
    FROM doktor_programlari dp
    JOIN doktorlar d ON dp.doktor_id = d.id
    JOIN poliklinikler p ON dp.poliklinik_id = p.id
    ORDER BY d.ad_soyad, dp.program_turu
");
$sql->execute();
$programlar = $sql->fetchAll(PDO::FETCH_ASSOC);

// Doktor ve poliklinik listelerini çekme
$doktorlar = $db->query("SELECT id, pol_id, ad_soyad FROM doktorlar ORDER BY ad_soyad")->fetchAll(PDO::FETCH_ASSOC);
$poliklinikler = $db->query("SELECT id, ad FROM poliklinikler ORDER BY ad")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doktor Çalışma Programları</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    .hover-effect:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .status-active {
        background-color: #d1fae5;
        color: #065f46;
    }

    .status-inactive {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .ameliyatli {
        background-color: #ffedd5;
    }
    </style>
</head>

<body class="bg-gray-50">
    <div class="container mx-auto p-4">
        <div class="bg-white rounded-lg shadow-lg p-6 hover-effect">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Doktor Çalışma Programları</h1>
                <button onclick="openDoktorProgramModal('ekleModal')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
                    <i class="fas fa-plus mr-2"></i> Yeni Program Ekle
                </button>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doktor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Poliklinik</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program Türü</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mesai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saatler</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nerde</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($programlar as $program): ?>
                    <tr class="">
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($program['doktor_adi']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($program['poliklinik_adi']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= $program['program_turu'] == 'haftaici' ? 'Hafta İçi' : 'Hafta Sonu' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= htmlspecialchars($program['mesai_turu']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= substr($program['baslangic_saati'], 0, 5) ?> -
                            <?= substr($program['bitis_saati'], 0, 5) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= $program['nerde']  ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= $program['aktif'] ? 
                                '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Aktif</span>' : 
                                '<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Pasif</span>' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button onclick="deleteProgram(<?= $program['id'] ?>)"
                                class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ekleme Modalı -->
    <div id="ekleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 ">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4">Yeni Çalışma Programı Ekle</h2>
            <form action="program_ekle.php" method="POST">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Doktor</label>
                        <select name="doktor_id" id="doktorSelect" class="w-full px-3 py-2 border rounded" required>
                            <option value="">Doktor Seçin</option>
                            <?php foreach ($doktorlar as $doktor): ?>
                            <option value="<?= $doktor['id'] ?>" data-poliklinik="<?= $doktor['pol_id'] ?>">
                                <?= htmlspecialchars($doktor['ad_soyad']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="poliklinikContainer">
                        <label class="block text-gray-700 mb-2">Poliklinik</label>
                        <select name="poliklinik_id" id="poliklinikSelect" class="w-full px-3 py-2 border rounded"
                            required>
                            <option value="">Önce doktor seçin</option>
                            <?php foreach ($poliklinikler as $pol): ?>
                            <option value="<?= $pol['id'] ?>"><?= htmlspecialchars($pol['ad']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NEREDE GÖREV YAPACAK</label>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="flex items-center">
                                <input type="radio" id="poliklinik" name="operasyon_turu" value="Poliklinik"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                                <label for="poliklinik" class="ml-2 block text-sm text-gray-700">Poliklinik</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="ameliyat" name="operasyon_turu" value="Ameliyat"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="ameliyat" class="ml-2 block text-sm text-gray-700">Ameliyat</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="acil" name="operasyon_turu" value="Acil"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="acil" class="ml-2 block text-sm text-gray-700">Acil</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="egitim" name="operasyon_turu" value="Eğitim"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="egitim" class="ml-2 block text-sm text-gray-700">Eğitim</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="kongre" name="operasyon_turu" value="Kongre"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="kongre" class="ml-2 block text-sm text-gray-700">Kongre</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="ybu" name="operasyon_turu" value="Yoğun Bakım Ünitesi (YBÜ)"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="ybu" class="ml-2 block text-sm text-gray-700">Yoğun Bakım Ünitesi
                                    (YBÜ)</label>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">MESAİ TÜRÜ</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="flex items-center">
                                <input type="radio" id="poliklinik" name="mesai_turu" value="Normal Mesai"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                                <label for="poliklinik" class="ml-2 block text-sm text-gray-700">Normal Mesai</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="ameliyat" name="mesai_turu" value="Nöbet"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="ameliyat" class="ml-2 block text-sm text-gray-700">Nöbet</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="acil" name="mesai_turu" value="İcap Nöbeti"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="acil" class="ml-2 block text-sm text-gray-700">İcap Nöbeti</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="egitim" name="mesai_turu" value="Ek Mesai"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="egitim" class="ml-2 block text-sm text-gray-700">Ek Mesai</label>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="aktif" name="aktif"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                        <label for="aktif" class="ml-2 block text-sm text-gray-700">Aktif Program</label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Başlangıç Saati</label>
                        <input type="time" name="baslangic_saati" class="w-full px-3 py-2 border rounded" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Bitiş Saati</label>
                        <input type="time" name="bitis_saati" class="w-full px-3 py-2 border rounded" required>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="closeDoktorProgramModal('ekleModal')" class="mr-2 px-4 py-2 bg-gray-300 rounded">
                        İptal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Modal functions
    function openDoktorProgramModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDoktorProgramModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Delete program function
    function deleteProgram(programId) {
        if (confirm('Bu programı silmek istediğinize emin misiniz?')) {
            window.location.href = 'program_sil.php?id=' + programId;
        }
    }

    // Doktor seçildiğinde poliklinik bilgisini otomatik doldur
    document.getElementById("doktorSelect").addEventListener("change", function () {
        const selectedOption = this.options[this.selectedIndex];
        const polId = selectedOption.getAttribute("data-poliklinik");
        const polSelect = document.getElementById("poliklinikSelect");
        for (let i = 0; i < polSelect.options.length; i++) {
            if (polSelect.options[i].value === polId) {
                polSelect.selectedIndex = i;
                break;
            }
        }
    });
    </script>
</body>
</html>