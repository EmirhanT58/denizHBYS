<?php
$title = "Randevu Yönetimi";
include_once "../database/db.php";

$result = $db->prepare("SELECT r.id, h.ad, h.soyad, r.durum ,d.unvan ,r.tur ,d.ad_soyad AS doktor_ad, p.ad AS poliklinik_ad, r.tarih, r.aciklama
    FROM randevular r
    JOIN hastalar h ON r.hasta_id = h.id
    JOIN doktorlar d ON r.doktor_id = d.id
    JOIN poliklinikler p ON r.pol_id = p.id");
$result->execute();
$randevular = $result->fetchAll(PDO::FETCH_ASSOC);

$doktor_sorgu = $db->prepare("SELECT id, unvan, ad_soyad, pol_id FROM doktorlar");
$doktor_sorgu->execute();
$doktorlar = $doktor_sorgu->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Ana İçerik -->
<div class="flex flex-col flex-1 overflow-hidden">
    <header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo $title; ?></h1>
    </header>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hasta
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doktor
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Poliklinik</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Randevu
                        Türü</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlem
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach($randevular as $randevu): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full"
                                    src="https://ui-avatars.com/api/?name=<?php echo urlencode($randevu['ad']." ".$randevu['soyad']); ?>&background=3b82f6&color=fff"
                                    alt="">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($randevu['ad']. " " .$randevu['soyad'])  ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo htmlspecialchars($randevu['unvan']." ".$randevu['doktor_ad']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo htmlspecialchars($randevu['poliklinik_ad']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div><?php echo date("d/m/Y H:i", strtotime($randevu['tarih'])); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div><?php echo $randevu["tur"]; ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="#" class="px-2 py-1 text-xs font-semibold rounded-full 
    <?php 
    if ($randevu['durum'] == 'Onaylandı') {
        echo 'bg-green-100 text-green-800';
    } elseif ($randevu['durum'] == 'Beklemede') {
        echo 'bg-yellow-100 text-yellow-800';
    } elseif ($randevu['durum'] == 'İptal Edildi') {
        echo 'bg-red-100 text-red-800';
    } else {
        // Default style for unknown statuses
        echo 'bg-gray-100 text-gray-800';
    }
    ?>">
                            <?php echo $randevu['durum'] ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">

                        <a href="dashboard.php?sayfa=doktorlar&sil=<?php echo $doktor['id']; ?>"
                            class="text-red-600 hover:text-red-900"
                            onclick="return confirm('Bu Randevuyu silmek istediğinize emin misiniz?')"><i
                                class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div id="randevuModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>

            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6 z-10">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Randevu Bilgisi Güncelle</h3>

                <form method="POST" action="randevu_guncelle.php">
                    <input type="hidden" name="randevu_id" id="randevu_id">

                    <div class="mb-4">
                        <label for="ad_soyad" class="block text-sm font-medium text-gray-700">Ad Soyad</label>
                        <input type="text" name="ad_soyad" id="ad_soyad" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 focus:ring focus:ring-blue-200">
                    </div>

                    <div class="mb-4">
                        <label for="r_tarih" class="block text-sm font-medium text-gray-700">Randevu Tarihi</label>
                        <input type="datetime-local" name="r_tarih" id="r_tarih" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 focus:ring focus:ring-blue-200">
                    </div>

                    <div class="mb-4">
                        <label for="dr_ad" class="block text-sm font-medium text-gray-700">Doktor Seç</label>
                        <select name="dr_ad" id="dr_ad" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 focus:ring focus:ring-blue-200">
                            <option value="">Doktor Seçiniz</option>
                            <?php foreach ($doktorlar as $doktor): ?>
                            <option
                                value="<?= htmlspecialchars($doktor['unvan'] . ' ' . $doktor['ad_soyad'] . ' - ' . $doktor['uzmanlik']) ?>">
                                <?= htmlspecialchars($doktor['unvan'] . ' ' . $doktor['ad_soyad']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="poliklinik_id" class="block text-sm font-medium text-gray-700">Poliklinik</label>
                        <select name="poliklinik_id" id="poliklinik_id" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 focus:ring focus:ring-blue-200">
                            <?php
        $poliklinikler = $db->query("SELECT id, ad FROM poliklinikler")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($poliklinikler as $poli) {
            echo "<option value='{$poli['id']}'>{$poli['ad']}</option>";
        }
        ?>
                        </select>
                    </div>


                    <div class="mb-4">
                        <label for="durum" class="block text-sm font-medium text-gray-700">Durum</label>
                        <select name="durum" id="durum" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm p-2 focus:ring focus:ring-blue-200">
                            <option value="Beklemede">Beklemede</option>
                            <option value="Onaylandı">Onaylandı</option>
                            <option value="İptal Edildi">İptal Edildi</option>
                            <option value="Tamamlandı">Tamamlandı</option>
                        </select>
                    </div>


                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="toggleModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">İptal</button>
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleModal() {
    document.getElementById('randevuModal').classList.toggle('hidden');
}

function openGuncelleModal(id, adSoyad, tarih, doktorAd, poliklinikAd, durum) {
    document.getElementById('randevu_id').value = id;
    document.getElementById('ad_soyad').value = adSoyad;
    document.getElementById('r_tarih').value = tarih;
    document.getElementById('dr_ad').value = doktorAd;
    document.getElementById('poliklinik_id').value = poliklinik_id;
    document.getElementById('durum').value = durum;

    toggleModal();
}
</script>