
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasta Kayıt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #bae6fd 100%);
        }
        .register-container {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg register-container">
            <h2 class="text-3xl font-extrabold text-center text-gray-800 mb-8">Hasta Kayıt Formu</h2>
            
            <!-- Hata Mesajı -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <p><?= htmlspecialchars($_SESSION['error']) ?></p>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Başarı Mesajı -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <p><?= htmlspecialchars($_SESSION['success']) ?></p>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <!-- Kayıt Formu -->
            <form action="kayit.php" method="POST" class="space-y-4">
                <!-- TC Kimlik -->
                <div>
                    <label for="tc" class="block text-sm font-semibold text-gray-700 mb-1">TC Kimlik No *</label>
                    <input type="text" id="tc" name="tc" maxlength="11" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150" 
                           placeholder="11 haneli TC kimlik numaranız" required>
                </div>
                
                <!-- Ad ve Soyad -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ad" class="block text-sm font-semibold text-gray-700 mb-1">Ad *</label>
                        <input type="text" id="ad" name="ad" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150" 
                               placeholder="Adınız" required>
                    </div>
                    <div>
                        <label for="soyad" class="block text-sm font-semibold text-gray-700 mb-1">Soyad *</label>
                        <input type="text" id="soyad" name="soyad" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150" 
                               placeholder="Soyadınız" required>
                    </div>
                </div>
                
                <!-- Doğum Tarihi -->
                <div>
                    <label for="dogum_tarihi" class="block text-sm font-semibold text-gray-700 mb-1">Doğum Tarihi</label>
                    <input type="date" id="dogum_tarihi" name="dogum_tarihi" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150">
                </div>
                
                <!-- Telefon ve Eposta -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="telefon" class="block text-sm font-semibold text-gray-700 mb-1">Telefon</label>
                        <input type="tel" id="telefon" name="telefon" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150" 
                               placeholder="5__ ___ __ __">
                    </div>
                    <div>
                        <label for="eposta" class="block text-sm font-semibold text-gray-700 mb-1">E-posta</label>
                        <input type="email" id="eposta" name="eposta" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150" 
                               placeholder="email@example.com">
                    </div>
                </div>
                
                <!-- Şifre -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="sifre" class="block text-sm font-semibold text-gray-700 mb-1">Şifre *</label>
                        <input type="password" id="sifre" name="sifre" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150" 
                               placeholder="En az 6 karakter" required>
                    </div>
                    <div>
                        <label for="sifre_tekrar" class="block text-sm font-semibold text-gray-700 mb-1">Şifre Tekrar *</label>
                        <input type="password" id="sifre_tekrar" name="sifre_tekrar" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150" 
                               placeholder="Şifrenizi tekrar girin" required>
                    </div>
                </div>
                
                <!-- Kayıt Butonu -->
                <button type="submit" 
                        class="w-full py-3 px-6 text-lg font-semibold text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-md mt-6">
                    Kayıt Ol
                </button>
                
                <!-- Giriş Sayfası Linki -->
                <div class="text-center mt-4">
                    <p class="text-gray-600">Zaten hesabınız var mı? 
                        <a href="hasta_login.php" class="text-blue-600 font-medium hover:text-blue-800">Giriş Yap</a>
                    </p>
                </div>
            </form>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p class="text-sm mb-4">&copy; 2024 Tüm Hakları Saklıdır. Sağlık Yönetim Sistemi</p>
            <div class="flex justify-center space-x-6">
                <a href="#" class="text-blue-300 hover:text-blue-100 transition duration-200">
                    <i class="fas fa-phone-alt mr-1"></i> İletişim
                </a>
                <a href="#" class="text-blue-300 hover:text-blue-100 transition duration-200">
                    <i class="fas fa-lock mr-1"></i> Gizlilik Politikası
                </a>
                <a href="#" class="text-blue-300 hover:text-blue-100 transition duration-200">
                    <i class="fas fa-info-circle mr-1"></i> Hakkımızda
                </a>
            </div>
        </div>
    </footer>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>