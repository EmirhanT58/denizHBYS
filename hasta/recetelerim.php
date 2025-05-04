<?php
include 'sidebar.php';
include '../database/db.php'; // Veritabanı bağlantısı
session_start();

// Hasta oturumda değilse girişe yönlendir
if (!isset($_SESSION['hasta_id'])) {
    header("Location: hasta_login.php");
    exit();
}

// Hasta ID'sini oturumdan al
$hasta_id = $_SESSION['hasta_id'];  

// Oturumdaki hasta ID'sine göre reçeteleri çek
$query = $db->prepare("
    SELECT 
        r.recete_id,
        r.hastane_id,
        r.pol_id,
        r.doktor_id,
        r.recete_turu,
        r.ilac_id,
        r.recete_no,
        r.doz,
        r.periyod,
        r.kutu_adet,
        r.aciklama,
        r.ol_tarih,
        i.ilac_barkod,
        i.ilac_ad,
        h.h_ad AS hastane_ad,
        b.ad AS brans_ad,
        d.ad_soyad AS doktor_adsoyad
    FROM receteler r
    JOIN ilaclar i ON r.ilac_id = i.ilac_id
    JOIN hastaneler h ON r.hastane_id = h.h_id
    JOIN poliklinikler b ON r.pol_id = b.id
    LEFT JOIN doktorlar d ON r.doktor_id = d.id
    WHERE r.hasta_id = ?");
$query->execute([$hasta_id]);
$receteler = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçetelerim</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 60%;
            max-width: 800px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }
    </style>
</head>

<body class="bg-gray-50">

    <main class="container p-10">
        <div class="bg-white shadow-sm p-8 rounded-lg mt-20 ml-[320px] w-[1500px]">
            <h3 class="text-center text-2xl font-semibold text-gray-800 mb-8">Reçete Bilgilerim</h3>
            <?php if (!empty($receteler)): ?>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-100 text-gray-600">
                        <th class="px-4 py-3 text-sm font-medium">Reçete ID</th>
                        <th class="px-4 py-3 text-sm font-medium">Reçete Türü</th>
                        <th class="px-4 py-3 text-sm font-medium">Doktor Adı</th>
                        <th class="px-4 py-3 text-sm font-medium">Tarih</th>
                        <th class="px-4 py-3 text-sm font-medium">Detaylar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receteler as $recete): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($recete['recete_no']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($recete['recete_turu']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($recete['doktor_adsoyad'] ?? 'Bilinmiyor') ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($recete['ol_tarih']) ?></td>
                        <td class="px-4 py-3">
                            <button onclick="openModal(<?= htmlspecialchars(json_encode($recete), ENT_QUOTES, 'UTF-8') ?>)"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                Detayları Göster
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="text-center text-gray-500">Henüz reçeteniz bulunmamaktadır.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 class="text-xl font-semibold text-gray-800 mb-4" id="modalTitle">Reçete Detayları</h3>
            <div id="modalBody" class="text-gray-700">
                <!-- Detaylar burada dinamik olarak yüklenecek -->
            </div>
        </div>
    </div>

    <script>
    // Null kontrol fonksiyon   
    function checkNull(value) {
        // Eğer değer null, undefined veya boş string ise
        if (value === null || value === undefined || value === '') {
            return 'Bulunamadı';
        }
        return value;
    }

    // Modal'ı açma fonksiyonu
    function openModal(receteData) {
        const modal = document.getElementById('myModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');

        // Verileri modalda göster
        modalTitle.textContent = 'Reçete ID: ' + receteData.recete_id;
        modalBody.innerHTML = `
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="mb-2"><strong>Hastane Adı:</strong> ${checkNull(receteData.hastane_ad)}</p>
                    <p class="mb-2"><strong>Branş:</strong> ${checkNull(receteData.brans_ad)}</p>
                    <p class="mb-2"><strong>Reçete Türü:</strong> ${checkNull(receteData.recete_turu)}</p>
                    <p class="mb-2"><strong>Tarih:</strong> ${checkNull(receteData.ol_tarih)}</p>
                </div>
                <div>
                    <p class="mb-2"><strong>Doktor:</strong> ${checkNull(receteData.doktor_adsoyad)}</p>
                </div>
            </div>
            <h4 class="mt-4 mb-2 font-semibold">Reçetede Yazan İlaçlar</h4>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-100 text-gray-600">
                        <th class="px-4 py-2 text-sm font-medium">Barkod</th>
                        <th class="px-4 py-2 text-sm font-medium">İlaç Adı</th>
                        <th class="px-4 py-2 text-sm font-medium">Açıklama</th>
                        <th class="px-4 py-2 text-sm font-medium">Doz</th>
                        <th class="px-4 py-2 text-sm font-medium">Periyot</th>
                        <th class="px-4 py-2 text-sm font-medium">Kullanım Sayısı</th>
                        <th class="px-4 py-2 text-sm font-medium">Kutu Adedi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm text-gray-700">${checkNull(receteData.ilac_barkod)}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">${checkNull(receteData.ilac_ad)}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">${checkNull(receteData.aciklama)}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">${checkNull(receteData.doz)}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">${checkNull(receteData.periyod)}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">${checkNull(receteData.k_sayisi)}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">${checkNull(receteData.kutu_adet)}</td>
                    </tr>
                </tbody>
            </table>
        `;
        modal.style.display = "flex"; // Modal'ı aç
    }

    // Modal'ı kapama fonksiyonu
    function closeModal() {
        const modal = document.getElementById('myModal');
        modal.style.display = "none"; // Modal'ı gizle
    }

    // Modal dışına tıklandığında kapatma
    window.onclick = function(event) {
        const modal = document.getElementById('myModal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>
</body>
</html>