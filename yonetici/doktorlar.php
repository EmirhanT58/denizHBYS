<?php

if (!isset($_SESSION["yonetici_id"]) || !isset($_SESSION["k_ad"])) {
    header("Location: giris.php");
    exit;
}

require_once "../database/db.php";

$success = "";
$error = "";

// Doktor ekleme
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["doktor_ekle"])) {
    $tc = $_POST["tc"];
    $ad_soyad = $_POST["ad_soyad"];
    $uzmanlik = $_POST["uzmanlik"];
    $telefon = $_POST["telefon"];
    $email = $_POST["email"];
    $unvan = $_POST["unvan"];
    $sifre = password_hash(substr($tc, -4), PASSWORD_DEFAULT);
    
    try {
        $stmt = $db->prepare("INSERT INTO doktorlar (tc, ad_soyad, uzmanlik, unvan ,telefon, email, sifre) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tc, $ad_soyad, $uzmanlik, $unvan ,$telefon, $email, $sifre]);
        $success = "Doktor başarıyla eklendi!";
    } catch(PDOException $e) {
        $error = "Doktor eklenirken hata oluştu: " . $e->getMessage();
    }
}

// Doktor Güncelleme
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["doktor_guncelle"])) {
    $g_tc = $_POST["g_tc"];
    $g_ad_soyad = $_POST["g_ad_soyad"];
    $g_uzmanlik = $_POST["g_uzmanlik"];
    $g_telefon = $_POST["g_telefon"];
    $g_email = $_POST["g_email"];
    $g_unvan = $_POST["g_unvan"];
    $gg_sifre = $_POST["g_sifre"];
    $g_sifre = password_hash($gg_sifre, PASSWORD_DEFAULT);
    $id = $_POST["g_id"];
    
    if (!empty($g_sifre)) {
       
        $sifreHash = password_hash($g_sifre, PASSWORD_BCRYPT);
        $sql = "UPDATE doktorlar SET tc=?, ad_soyad=?, uzmanlik=?, unvan=?, telefon=?, email=?, sifre=? WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$g_tc, $g_ad_soyad, $g_uzmanlik, $g_unvan, $g_telefon, $g_email, $g_sifre, $id]);
        $success = "Doktor başarıyla güncellendi! (Şifreli)";
    } else {
      
        $sql = "UPDATE doktorlar SET tc=?, ad_soyad=?, uzmanlik=?, unvan=?, telefon=?, email=? WHERE id=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$tc, $ad_soyad, $uzmanlik, $unvan, $telefon, $email, $id]);
        $success = "Doktor başarıyla güncellendi! (Şifresiz)";
    }
}

// Doktor silme
if(isset($_GET["sil"]) && is_numeric($_GET["sil"])) {
    try {
        $stmt = $db->prepare("DELETE FROM doktorlar WHERE id = ?");
        $stmt->execute([$_GET["sil"]]);
        $success = "Doktor başarıyla silindi!";
    } catch(PDOException $e) {
        $error = "Doktor silinirken hata oluştu: " . $e->getMessage();
    }
}

// Doktor durum değiştirme
if(isset($_GET["durum"]) && is_numeric($_GET["durum"])) {
    try {
        // Mevcut durumu tersine çevir
        $stmt = $db->prepare("UPDATE doktorlar SET durum = NOT durum WHERE id = ?");
        $stmt->execute([$_GET["durum"]]);
        $success = "Doktor durumu güncellendi!";
    } catch(PDOException $e) {
        $error = "Durum güncellenirken hata oluştu: " . $e->getMessage();
    }
}



// Doktor listesini getir
$doktorlar = $db->query("SELECT * FROM doktorlar ORDER BY ad_soyad ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="p-2">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Doktor Yönetimi</h2>
        <button onclick="toggleModalEkle()"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center">
            <i class="fas fa-plus mr-2"></i> Yeni Doktor Ekle
        </button>
    </div>

    <?php if($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?php echo $success; ?>
    </div>
    <?php endif; ?>

    <?php if($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doktor
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uzmanlık
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ünvanı
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İletişim
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach($doktorlar as $doktor): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full"
                                    src="https://ui-avatars.com/api/?name=<?php echo urlencode($doktor['ad_soyad']); ?>&background=3b82f6&color=fff"
                                    alt="">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($doktor['ad_soyad']); ?></div>
                                <div class="text-sm text-gray-500">TCKN: <?php echo htmlspecialchars($doktor['tc']); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo htmlspecialchars($doktor['uzmanlik']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo htmlspecialchars($doktor['unvan']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div><?php echo htmlspecialchars($doktor['telefon']); ?></div>
                        <div class="text-blue-600"><?php echo htmlspecialchars($doktor['email']); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="dashboard.php?sayfa=doktorlar&durum=<?php echo $doktor['id']; ?>"
                            class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $doktor['durum'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo $doktor['durum'] ? 'Aktif' : 'Pasif'; ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a onclick="DoktorBilgisiGetir('<?= $doktor['id'] ?>',
    '<?= $doktor['tc'] ?>',
    '<?= htmlspecialchars($doktor['ad_soyad'], ENT_QUOTES) ?>',
    '<?= htmlspecialchars($doktor['uzmanlik'], ENT_QUOTES) ?>',
    '<?= htmlspecialchars($doktor['unvan'], ENT_QUOTES) ?>',
    '<?= $doktor['telefon'] ?>',
    '<?= $doktor['email'] ?>',
    '<?= $doktor['sifre'] ?>')" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </a>

                        <a href="dashboard.php?sayfa=doktorlar&sil=<?php echo $doktor['id']; ?>"
                            class="text-red-600 hover:text-red-900"
                            onclick="return confirm('Bu doktoru silmek istediğinize emin misiniz?')"><i
                                class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Doktor Ekleme Modal -->
<div id="doktorEkleModal" class="fixed inset-0 z-50 hidden overflow-y-auto mt-[130px]">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Yeni Doktor Ekle</h3>

                <form method="POST">
                    <div class="grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="tc" class="block text-sm font-medium text-gray-700">TC Kimlik No</label>
                            <input type="text" name="tc" id="tc" required
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>

                        <div class="sm:col-span-6">
                            <label for="ad_soyad" class="block text-sm font-medium text-gray-700">Ad Soyad</label>
                            <input type="text" name="ad_soyad" id="ad_soyad" required
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>

                        <div class="sm:col-span-6">
                            <label for="uzmanlik" class="block text-sm font-medium text-gray-700">Uzmanlık</label>
                            <input type="text" name="uzmanlik" id="uzmanlik" required
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>

                        <div class="sm:col-span-6">
                            <label for="unvan" class="block text-sm font-medium text-gray-700">Ünvanı</label>
                            <input type="text" name="unvan" id="unvan" required
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>

                        <div class="sm:col-span-6">
                            <label for="telefon" class="block text-sm font-medium text-gray-700">Telefon</label>
                            <input type="tel" name="telefon" id="telefon" required
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>

                        <div class="sm:col-span-6">
                            <label for="email" class="block text-sm font-medium text-gray-700">E-posta</label>
                            <input type="email" name="email" id="email"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>

                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="submit" name="doktor_ekle"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                            Kaydet
                        </button>
                        <button type="button" onclick="toggleModalEkle()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                            İptal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="doktorGuncelleModal" class="fixed inset-0 z-50 hidden overflow-y-auto mt-[130px]">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Doktor Bilgisi Güncelle</h3>

                <form method="POST" name="doktor_guncelleme">
                    <div class="grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-6">
                        <input type="text" hidden name="g_id" id="g_id">
                        <div class="sm:col-span-3">
                            <label for="g_tc" class="block text-sm font-medium text-gray-700">TC Kimlik No</label>
                            <input type="text" name="g_tc" id="g_tc" required
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>

                        <div class="sm:col-span-6">
                            <label for="g_ad_soyad" class="block text-sm font-medium text-gray-700">Ad Soyad</label>
                            <input type="text" name="g_ad_soyad" id="g_ad_soyad" required
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>

                        <div class="sm:col-span-6">
                            <label for="g_uzmanlik" class="block text-sm font-medium text-gray-700">Uzmanlık</label>
                            <input type="text" name="g_uzmanlik" id="g_uzmanlik" required
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>

                        <div class="sm:col-span-6">
                            <label for="g_unvan" class="block text-sm font-medium text-gray-700">Ünvanı</label>
                            <input type="text" name="g_unvan" id="g_unvan" required
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>

                        <div class="sm:col-span-6">
                            <label for="g_telefon" class="block text-sm font-medium text-gray-700">Telefon</label>
                            <input type="tel" name="g_telefon" id="g_telefon" required
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>

                        <div class="sm:col-span-6">
                            <label for="g_email" class="block text-sm font-medium text-gray-700">E-posta</label>
                            <input type="email" name="g_email" id="g_email"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>
                        <div class="sm:col-span-6">
                            <label for="email" class="block text-sm font-medium text-gray-700" require>Şifre</label>
                            <input type="password" name="g_sifre" id="g_sifre"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="submit" name="doktor_guncelle"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                            Kaydet
                        </button>
                        <button type="button" onclick="toggleModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                            İptal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleModal() {
    document.getElementById('doktorGuncelleModal').classList.toggle('hidden');
}
function toggleModalEkle() {
    document.getElementById('doktorEkleModal').classList.toggle('hidden');
}

function DoktorBilgisiGetir(id, tc, ad_soyad, uzmanlik, unvan, telefon, email, sifre) {
    document.getElementById('g_id').value = id;
    document.getElementById('g_tc').value = tc;
    document.getElementById('g_ad_soyad').value = ad_soyad;
    document.getElementById('g_uzmanlik').value = uzmanlik;
    document.getElementById('g_unvan').value = unvan;
    document.getElementById('g_telefon').value = telefon;
    document.getElementById('g_email').value = email;
    document.getElementById('g_sifre').value = sifre;

    // Modali aç
    document.getElementById('doktorGuncelleModal').classList.remove('hidden');
}
</script>