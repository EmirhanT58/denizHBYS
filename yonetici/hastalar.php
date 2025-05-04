<?php
// Oturum kontrolü ve yönlendirme
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["yonetici_id"]) || !isset($_SESSION["k_ad"])) {
    header("Location: giris.php");
    exit;
}

require_once "../database/db.php";

$success = "";
$error = "";

// Hasta ekleme işlemi
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["hasta_ekle"])) {
    $tc = $_POST["tc"];
    $ad = $_POST["ad"];
    $soyad = $_POST["soyad"];
    $cinsiyet = $_POST["cinsiyet"];
    $dogum_tarihi = $_POST["dogum_tarihi"];
    $telefon = $_POST["telefon"];
    $email = $_POST["email"];
    $adres = $_POST["adres"];
    $kan_grubu = $_POST["kan_grubu"];
    $sifre = password_hash($_POST["sifre"], PASSWORD_DEFAULT);

    try {
        $stmt = $db->prepare("INSERT INTO hastalar (tc, ad, soyad, cinsiyet, dogum_tarihi, telefon, email, adres, kan_grubu, sifre) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tc, $ad, $soyad, $cinsiyet, $dogum_tarihi, $telefon, $email, $adres, $kan_grubu, $sifre]);
        $success = "Hasta başarıyla eklendi!";
    } catch(PDOException $e) {
        $error = "Hasta eklenirken hata oluştu: " . $e->getMessage();
    }
}

// Hasta güncelleme işlemi
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["hasta_guncelle"])) {
    $g_id = $_POST["guncelle_id"];
    $g_tc = $_POST["g_tc"];
    $g_ad = $_POST["g_ad"];
    $g_soyad = $_POST["g_soyad"];
    $g_cinsiyet = $_POST["g_cinsiyet"];
    $g_dogum_tarihi = $_POST["g_dogum_tarihi"];
    $g_telefon = $_POST["g_telefon"];
    $g_email = $_POST["g_email"];
    $g_adres = $_POST["g_adres"];
    $g_kan_grubu = $_POST["g_kan_grubu"];
    
    // Şifre güncelleme kontrolü
    if (!empty($_POST["g_sifre"])) {
        $sifre = password_hash($_POST["g_sifre"], PASSWORD_DEFAULT);
        $sql = "UPDATE hastalar SET 
                tc = ?, ad = ?, soyad = ?, cinsiyet = ?, dogum_tarihi = ?, 
                telefon = ?, email = ?, adres = ?, kan_grubu = ?, sifre = ?
                WHERE id = ?";
        $params = [$g_tc, $g_ad, $g_soyad, $g_cinsiyet, $g_dogum_tarihi, 
                  $g_telefon, $g_email, $g_adres, $g_kan_grubu, $sifre, $g_id];
    } else {
        $sql = "UPDATE hastalar SET 
                tc = ?, ad = ?, soyad = ?, cinsiyet = ?, dogum_tarihi = ?, 
                telefon = ?, email = ?, adres = ?, kan_grubu = ?
                WHERE id = ?";
        $params = [$g_tc, $g_ad, $g_soyad, $g_cinsiyet, $g_dogum_tarihi, 
                  $g_telefon, $g_email, $g_adres, $g_kan_grubu, $g_id];
    }

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $success = "Hasta başarıyla güncellendi!";
    } catch(PDOException $e) {
        $error = "Hasta güncellenirken hata oluştu: " . $e->getMessage();
    }
}

// Hasta silme işlemi
if(isset($_GET["sil"]) && is_numeric($_GET["sil"])) {
    try {
        $stmt = $db->prepare("DELETE FROM hastalar WHERE id = ?");
        $stmt->execute([$_GET["sil"]]);
        $success = "Hasta başarıyla silindi!";
    } catch(PDOException $e) {
        $error = "Hasta silinirken hata oluştu: " . $e->getMessage();
    }
}

// Hasta arama veya listeleme
$search = "";
$hastalar = [];
if(isset($_GET["ara"])) {
    $search = $_GET["ara"];
    $stmt = $db->prepare("SELECT * FROM hastalar 
                          WHERE ad LIKE ? OR soyad LIKE ? OR tc LIKE ? OR telefon LIKE ?
                          ORDER BY ad, soyad ASC");
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
    $hastalar = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $hastalar = $db->query("SELECT * FROM hastalar ORDER BY ad, soyad ASC")->fetchAll(PDO::FETCH_ASSOC);
}
$kan_grubu = isset($hasta['kan_grubu']) && $hasta['kan_grubu'] !== null ? htmlspecialchars($hasta['kan_grubu'], ENT_QUOTES) : 'Belirtilmemiş';
$adres = isset($hasta['adres']) && $hasta['dares'] !== null ? htmlspecialchars($hasta['adres'], ENT_QUOTES) : 'Belirtilmemiş';

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasta Yönetimi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="p-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Hasta Yönetimi</h2>
            <div class="flex space-x-3">
                <form method="GET" class="flex">
                    <input type="hidden" name="sayfa" value="hastalar">
                    <input type="text" name="ara" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Hasta ara..."
                        class="px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-md">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <button onclick="document.getElementById('hastaModal').classList.remove('hidden')"
                    class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg">
                    <i class="fas fa-plus mr-1"></i> Yeni Hasta Ekle
                </button>
            </div>
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

        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hasta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bilgiler</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İletişim</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($hastalar as $hasta): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full"
                                        src="https://ui-avatars.com/api/?name=<?php echo urlencode($hasta['ad'].'+'.$hasta['soyad']); ?>&background=3b82f6&color=fff"
                                        alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($hasta['ad'].' '.$hasta['soyad']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">TCKN: <?php echo htmlspecialchars($hasta['tc']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php 
                            if($hasta['cinsiyet'] == null) {
                                echo "Belirtilmemiş";}
                            echo htmlspecialchars($hasta['cinsiyet']); ?></div>
                            <div class="text-sm text-gray-500">
                                <?php echo date('d/m/Y', strtotime($hasta['dogum_tarihi'])); ?>
                            </div>
                            <div class="text-sm text-gray-500"><?php 
                            if($hasta['kan_grubu'] == null) {
                                echo "Belirtilmemiş";}
                             echo $hasta['kan_grubu' ];  ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div><?php echo htmlspecialchars($hasta['telefon']); ?></div>
                            <div class="text-blue-600"><?php echo htmlspecialchars($hasta['email']); ?></div>
                            <div class="text-xs text-gray-400 truncate max-w-xs">
                                <?php if($hasta['adres'] == null) {
                                echo "Belirtilmemiş";}
                                
                                echo $hasta['adres']; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a onclick="hastaDuzenleModal('<?php echo $hasta['id']; ?>',
                              '<?php echo htmlspecialchars($hasta['tc'], ENT_QUOTES); ?>',
                              '<?php echo htmlspecialchars($hasta['ad'], ENT_QUOTES); ?>',
                              '<?php echo htmlspecialchars($hasta['soyad'], ENT_QUOTES); ?>',
                              '<?php echo htmlspecialchars($hasta['cinsiyet'], ENT_QUOTES); ?>',
                              '<?php echo $hasta['dogum_tarihi']; ?>',
                              '<?php echo htmlspecialchars($hasta['telefon'], ENT_QUOTES); ?>',
                              '<?php echo htmlspecialchars($hasta['email'], ENT_QUOTES); ?>',
                              '<?php echo htmlspecialchars($adres, ENT_QUOTES); ?>',
                              '<?php echo htmlspecialchars( $kan_grubu, ENT_QUOTES); ?>')"
                                class="text-blue-600 hover:text-blue-900 mr-3 cursor-pointer">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?sayfa=hastalar&sil=<?php echo $hasta['id']; ?>"
                                class="text-red-600 hover:text-red-900 mr-3"
                                onclick="return confirm('Bu hastayı silmek istediğinize emin misiniz?')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <a href="hasta_bilgisi_indir.php?hasta_id=<?php echo $hasta['id']; ?>"
                                class="text-green-600 hover:text-green-900">
                                <i class="fas fa-file-medical"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hasta Ekle Modal -->
    <div id="hastaModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Yeni Hasta Ekle</h3>
                    <form method="POST">
                        <div class="grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-2">
                                <label for="tc" class="block text-sm font-medium text-gray-700">TC Kimlik No</label>
                                <input type="text" name="tc" id="tc" required maxlength="11"
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="ad" class="block text-sm font-medium text-gray-700">Ad</label>
                                <input type="text" name="ad" id="ad" required
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="soyad" class="block text-sm font-medium text-gray-700">Soyad</label>
                                <input type="text" name="soyad" id="soyad" required
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-3">
                                <label for="cinsiyet" class="block text-sm font-medium text-gray-700">Cinsiyet</label>
                                <select name="cinsiyet" id="cinsiyet" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Seçiniz</option>
                                    <option value="Erkek">Erkek</option>
                                    <option value="Kadın">Kadın</option>
                                    <option value="Diğer">Diğer</option>
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="dogum_tarihi" class="block text-sm font-medium text-gray-700">Doğum Tarihi</label>
                                <input type="date" name="dogum_tarihi" id="dogum_tarihi" required
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-3">
                                <label for="telefon" class="block text-sm font-medium text-gray-700">Telefon</label>
                                <input type="tel" name="telefon" id="telefon" required
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-3">
                                <label for="email" class="block text-sm font-medium text-gray-700">E-Posta</label>
                                <input type="email" name="email" id="email"
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-6">
                                <label for="adres" class="block text-sm font-medium text-gray-700">Adres</label>
                                <textarea name="adres" id="adres" rows="2"
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2"></textarea>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="kan_grubu" class="block text-sm font-medium text-gray-700">Kan Grubu</label>
                                <input type="text" name="kan_grubu" id="kan_grubu"
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-3">
                                <label for="sifre" class="block text-sm font-medium text-gray-700">Şifre</label>
                                <input type="password" name="sifre" id="sifre" required
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="document.getElementById('hastaModal').classList.add('hidden')"
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                                İptal
                            </button>
                            <button type="submit" name="hasta_ekle"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                Ekle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hasta Güncelleme Modal -->
    <div id="hastaGuncelleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Hasta Bilgisi Güncelle</h3>
                    <form method="POST">
                        <input type="hidden" name="guncelle_id" id="guncelle_id">
                        <div class="grid grid-cols-1 gap-y-4 gap-x-6 sm:grid-cols-6">
                            <div class="sm:col-span-2">
                                <label for="guncelle_tc" class="block text-sm font-medium text-gray-700">TC Kimlik No</label>
                                <input type="text" name="g_tc" id="guncelle_tc" required maxlength="11"
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="guncelle_ad" class="block text-sm font-medium text-gray-700">Ad</label>
                                <input type="text" name="g_ad" id="guncelle_ad" required
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="guncelle_soyad" class="block text-sm font-medium text-gray-700">Soyad</label>
                                <input type="text" name="g_soyad" id="guncelle_soyad" required
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-3">
                                <label for="guncelle_cinsiyet" class="block text-sm font-medium text-gray-700">Cinsiyet</label>
                                <select name="g_cinsiyet" id="guncelle_cinsiyet" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Seçiniz</option>
                                    <option value="Erkek">Erkek</option>
                                    <option value="Kadın">Kadın</option>
                                    <option value="Diğer">Diğer</option>
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="guncelle_dogum_tarihi" class="block text-sm font-medium text-gray-700">Doğum Tarihi</label>
                                <input type="date" name="g_dogum_tarihi" id="guncelle_dogum_tarihi" required
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-3">
                                <label for="guncelle_telefon" class="block text-sm font-medium text-gray-700">Telefon</label>
                                <input type="tel" name="g_telefon" id="guncelle_telefon" required
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-3">
                                <label for="guncelle_email" class="block text-sm font-medium text-gray-700">E-Posta</label>
                                <input type="email" name="g_email" id="guncelle_email"
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-6">
                                <label for="guncelle_adres" class="block text-sm font-medium text-gray-700">Adres</label>
                                <textarea name="g_adres" id="guncelle_adres" rows="2"
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2"></textarea>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="guncelle_kan_grubu" class="block text-sm font-medium text-gray-700">Kan Grubu</label>
                                <input type="text" name="g_kan_grubu" id="guncelle_kan_grubu"
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                            <div class="sm:col-span-3">
                                <label for="guncelle_sifre" class="block text-sm font-medium text-gray-700">Yeni Şifre (Değiştirmek istemiyorsanız boş bırakın)</label>
                                <input type="password" name="g_sifre" id="guncelle_sifre"
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2">
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="document.getElementById('hastaGuncelleModal').classList.add('hidden')"
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                                İptal
                            </button>
                            <button type="submit" name="hasta_guncelle"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                Güncelle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function hastaDuzenleModal(id, tc, ad, soyad, cinsiyet, dogum_tarihi, telefon, email, adres, kan_grubu) {
        // Modal'ı aç
        document.getElementById('hastaGuncelleModal').classList.remove('hidden');

        // Form alanlarına verileri yerleştir
        document.getElementById('guncelle_id').value = id;
        document.getElementById('guncelle_tc').value = tc;
        document.getElementById('guncelle_ad').value = ad;
        document.getElementById('guncelle_soyad').value = soyad;
        document.getElementById('guncelle_cinsiyet').value = cinsiyet;
        document.getElementById('guncelle_dogum_tarihi').value = dogum_tarihi;
        document.getElementById('guncelle_telefon').value = telefon;
        document.getElementById('guncelle_email').value = email;
        document.getElementById('guncelle_adres').value = adres;
        document.getElementById('guncelle_kan_grubu').value = kan_grubu;
        document.getElementById('guncelle_sifre').value = '';
    }
    </script>
</body>
</html>