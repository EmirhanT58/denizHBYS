    <?php
    include 'giris_kontrol.php';
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hasta Girişi</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #bae6fd 100%);
            }
            .login-container {
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
            <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg login-container">
                
                <h2 class="text-3xl font-extrabold text-center text-gray-800 mb-8">Hasta Girişi</h2>
                
                <!-- Hata Mesajı -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                        <p><?= htmlspecialchars($_SESSION['error']) ?></p>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Giriş Formu -->
                <form action="giris_kontrol.php" method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-1">TC Kimlik No</label>
                        <input type="text" id="username" name="username" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150" 
                            placeholder="Kullanıcı adı veya TC kimlik no" required>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Şifre</label>
                        <input type="password" id="password" name="password" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150" 
                            placeholder="Şifrenizi girin" required>
                        <div class="flex justify-end mt-1">
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-800">Şifremi Unuttum?</a>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="login" 
                            class="w-full py-3 px-6 text-lg font-semibold text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-md">
                        Giriş Yap
                    </button>

                    <!-- Kayıt Ol Linki -->
                    <div class="text-center mt-4">
                        <p class="text-gray-600">Hesabınız yok mu? 
                            <a href="hasta_kayit.php" class="text-blue-600 font-medium hover:text-blue-800">Kayıt Ol</a>
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