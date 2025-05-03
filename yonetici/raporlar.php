<?php

if (!isset($_SESSION["yonetici_id"]) || !isset($_SESSION["k_ad"])) {
    header("Location: giris.php");
    exit;
}

require_once "../database/db.php";


$tarihSaat = date('YmdH');
$rastgeleSayi = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
$sonSayi = mt_rand(0, 9);
$raporNo = $tarihSaat . '-' . $rastgeleSayi . '-' . $sonSayi;

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
    LIMIT 3
");
$sql->execute();
$raporlar = $sql->fetchAll(PDO::FETCH_ASSOC);


$sql = $db->prepare("SELECT id, ad, soyad FROM hastalar ORDER BY ad ASC");
$sql->execute();
$hastalar = $sql->fetchAll(PDO::FETCH_ASSOC);


$sql = $db->prepare("SELECT id, ad_soyad FROM doktorlar ORDER BY ad_soyad ASC");
$sql->execute();
$doktorlar = $sql->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar - denizHBYS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        function toggleModal(modalId) {
            document.getElementById(modalId).classList.toggle('hidden');
        }

        function raporDuzenleModal(id, baslik, icerik, hastaAdi, doktorAdi, durum, tarih,hek_gor) {
            document.getElementById('duzenleModal').classList.remove('hidden');
            document.getElementById('rapor_id').value = id;
            document.getElementById('rapor_tur').value = baslik;
            document.getElementById('rapor_tah_son').value = icerik;
            document.getElementById('rapor_hasta_adi').innerText = hastaAdi;
            document.getElementById('rapor_doktor_adi').innerText = doktorAdi;
            document.getElementById('rapor_durum').value = durum;
            document.getElementById('rapor_tarih').value = tarih;
            document.getElementById('rapor_hek_gor').value = hek_gor;
            
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">
    <main class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Rapor Yönetimi</h2>
                <div class="flex space-x-4">
                    <form method="GET" class="flex">
                        <input type="text" name="ara" value="<?= isset($_GET['ara']) ? htmlspecialchars($_GET['ara']) : '' ?>" 
                            placeholder="Hasta ara..." 
                            class="px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-md">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    <button onclick="toggleModal('ekleModal')" 
                            class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                        Yeni Rapor Ekle

                    </button>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <?= htmlspecialchars($_GET['success']) ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
            <?php endif; ?>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-blue-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">#</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Hasta Adı</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Doktor</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Rapor No</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Rapor Türü</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Tarih</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Durum</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $sira = 1; ?>
                        <?php foreach ($raporlar as $rapor): 
                            $renk = match($rapor['durum']) {
                                'Onaylandı' => 'text-green-600 bg-green-100',
                                'Bekliyor' => 'text-yellow-600 bg-yellow-100',
                                'Reddedildi' => 'text-red-600 bg-red-100',
                                default => 'text-gray-600 bg-gray-100'
                            };
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4"><?= $sira++ ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($rapor['ad'] . ' ' . $rapor['soyad']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($rapor['unvan'] . ' ' . $rapor['doktor_ad']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($rapor['rapor_no']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($rapor['rapor_tur']) ?></td>
                            <td class="px-6 py-4"><?= date("d/m/Y", strtotime($rapor['tarih'])) ?></td>
                            <td class="px-6 py-4">
                                <span class="<?= $renk ?> px-3 py-1 rounded-full font-semibold">
                                    <?= htmlspecialchars($rapor['durum']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 space-x-2">
                                <button onclick="raporDuzenleModal(
                                    '<?= $rapor['rapor_id'] ?>',
                                    '<?= htmlspecialchars($rapor['rapor_tur'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($rapor['tahlil_sonuclari'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($rapor['ad'] . ' ' . $rapor['soyad'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($rapor['unvan'] . ' ' . $rapor['doktor_ad'], ENT_QUOTES) ?>',
                                    '<?= $rapor['durum'] ?>',
                                    '<?= $rapor['tarih'] ?>',
                                    '<?= $rapor['hekim_gor'] ?>',
                                )" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="rapor_indir.php?id=<?= $rapor['rapor_id'] ?>" 
                                   class="text-green-600 hover:text-green-900" 
                                   title="Raporu İndir">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="rapor_sil.php?id=<?= $rapor['rapor_id'] ?>" 
                                   class="text-red-600 hover:text-red-900" 
                                   onclick="return confirm('Bu raporu silmek istediğinize emin misiniz?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="ekleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Yeni Rapor Ekle</h2>
            <form method="POST" action="rapor_ekle.php" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Rapor No</label>
                    <input type="text" name="rapor_no" value="<?= $raporNo ?>"readonly
                           class="mt-1 w-full bg-gray-100 border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hasta</label>
                    <select name="hasta_id" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Hasta Seçin</option>
                        <?php foreach ($hastalar as $hasta): ?>
                        <option value="<?= htmlspecialchars($hasta['id']) ?>">
                            <?= htmlspecialchars($hasta['ad'] . ' ' . $hasta['soyad']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Doktor</label>
                    <select name="doktor_id" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Doktor Seçin</option>
                        <?php foreach ($doktorlar as $doktor): ?>
                        <option value="<?= htmlspecialchars($doktor['id']) ?>">
                            <?= htmlspecialchars($doktor['ad_soyad']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Rapor Türü</label>
                    <select name="rapor_tur" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Rapor Türü Seçin</option>
                        <option value="Sağlık Raporu">Sağlık Raporu</option>
                        <option value="İlaç Raporu">İlaç Raporu</option>
                        <option value="Heyet Raporu">Heyet Raporu</option>
                        <option value="Epikriz Raporu">Epikriz Raporu</option>
                        <option value="İstirahat Raporu">İstirahat Raporu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tahlil Sonuçları</label>
                    <textarea name="tah_son" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" rows="4" required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hekim Görüşü</label>
                    <textarea name="hek_gor" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" rows="4" required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Durum</label>
                    <select name="durum" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="Bekliyor">Bekliyor</option>
                        <option value="Onaylandı">Onaylandı</option>
                        <option value="Reddedildi">Reddedildi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tarih</label>
                    <input type="date" name="tarih" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="toggleModal('ekleModal')" 
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-200">
                        İptal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="duzenleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Rapor Düzenle</h2>
            <form method="POST" action="rapor_guncelle.php" class="space-y-4">
                <input type="hidden" name="id" id="rapor_id">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Rapor No</label>
                    <input type="text" name="rapor_no" value="<?= $rapor["rapor_no"]  ?>" disabled 
                           class="mt-1 w-full bg-gray-100 border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hasta</label>
                    <p class="mt-1 bg-gray-100 p-3 rounded-md text-gray-700" id="rapor_hasta_adi"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Doktor</label>
                    <p class="mt-1 bg-gray-100 p-3 rounded-md text-gray-700" id="rapor_doktor_adi"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Rapor Türü</label>
                    <select name="rapor_tur" id="rapor_tur" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required value="<?= $rapor["rapor_tur"]?>">
                        <option value="Sağlık Raporu">Sağlık Raporu</option>
                        <option value="İlaç Raporu">İlaç Raporu</option>
                        <option value="Heyet Raporu">Heyet Raporu</option>
                        <option value="Epikriz Raporu">Epikriz Raporu</option>
                        <option value="İstirahat Raporu">İstirahat Raporu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tahlil Sonuçları</label>
                    <textarea name="tahil_sonuclari" id="rapor_tah_son" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" rows="4" required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hekim Görüşü</label>
                    <textarea name="hek_gor" id="rapor_hek_gor" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" rows="4" required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Durum</label>
                    <select name="durum" id="rapor_durum" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="Bekliyor">Bekliyor</option>
                        <option value="Onaylandı">Onaylandı</option>
                        <option value="Reddedildi">Reddedildi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tarih</label>
                    <input type="date" name="tarih" value="<?= date('Y/m/d H:i:s');?>"  id="rapor_tarih" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="toggleModal('duzenleModal')" 
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-200">
                        İptal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>