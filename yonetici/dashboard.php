<?php
session_start();
require_once '../vendor/autoload.php'; 

if (!isset($_SESSION["yonetici_id"]) || !isset($_SESSION["k_ad"])) {
    header("Location: giris.php");
    exit;
}

$k_ad = $_SESSION["k_ad"];

$sayfa = isset($_GET["sayfa"]) ? $_GET["sayfa"] : '';
$allowed_pages = ['doktorlar', 'raporlar', 'hastalar', 'randevular', 'doktorcalisma' ,'ayarlar'];
$active_page = in_array($sayfa, $allowed_pages) ? $sayfa : '';
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>denizHBYS - Yönetici Paneli</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#1e3a8a',
                        accent: '#3b82f6',
                        dark: '#1f2937',
                        light: '#f9fafb',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                        info: '#3b82f6'
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid white;
        }
        .sidebar-item:hover:not(.active) {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .content-container {
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 64px); /* Üst bar yüksekliği */
        }
        .main-content {
            flex: 1;
            overflow-y: auto;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Ana Layout -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-gradient-to-b from-primary to-secondary">
                <div class="flex items-center justify-center h-16 px-4 border-b border-blue-800">
                    <div class="flex items-center">
                        <i class="fas fa-hospital text-white text-2xl mr-2"></i>
                        <span class="text-white text-xl font-semibold">denizHBYS</span>
                    </div>
                </div>
                <div class="flex flex-col flex-grow px-4 py-4 overflow-y-auto">
                    <!-- Kullanıcı Bilgileri -->
                    <div class="flex items-center px-4 py-3 mb-6 bg-blue-800 rounded-lg">
                        <div class="relative">
                            <img class="w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($k_ad); ?>&background=3b82f6&color=fff" alt="Profil">
                            <span class="absolute bottom-0 right-0 block w-3 h-3 bg-green-500 rounded-full ring-2 ring-white"></span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white"><?php echo htmlspecialchars($k_ad); ?></p>
                            <p class="text-xs font-medium text-blue-200">Yönetici</p>
                        </div>
                    </div>
                    
                    <!-- Menü -->
                    <nav class="flex-1 space-y-2">
                        <a href="dashboard.php" class="flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg sidebar-item <?php echo empty($active_page) ? 'active' : ''; ?>">
                            <i class="fas fa-home mr-3 text-blue-200"></i>
                            Ana Sayfa
                        </a>
                        <a href="dashboard.php?sayfa=hastalar" class="flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg sidebar-item <?php echo $active_page == 'hastalar' ? 'active' : ''; ?>">
                            <i class="fas fa-user-injured mr-3 text-blue-200"></i>
                            Hasta Yönetimi
                        </a>
                        <a href="dashboard.php?sayfa=doktorlar" class="flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg sidebar-item <?php echo $active_page == 'doktorlar' ? 'active' : ''; ?>">
                            <i class="fas fa-user-md mr-3 text-blue-200"></i>
                            Doktor Yönetimi
                        </a>
                        <a href="dashboard.php?sayfa=randevular" class="flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg sidebar-item <?php echo $active_page == 'randevular' ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-check mr-3 text-blue-200"></i>
                            Randevu Yönetimi
                        </a>
                        <a href="dashboard.php?sayfa=raporlar" class="flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg sidebar-item <?php echo $active_page == 'raporlar' ? 'active' : ''; ?>">
                            <i class="fas fa-file-medical mr-3 text-blue-200"></i>
                            Raporlar
                        </a>
                        <a href="dashboard.php?sayfa=doktorcalisma" class="flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg sidebar-item <?php echo $active_page == 'doktorcalisma' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-user-doctor mr-3"></i>
                            Doktor Çalışma Programı
                        </a>
                    </nav>
                </div>
                <div class="p-4 border-t border-blue-800">
                    <a href="logout.php" class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Çıkış Yap
                    </a>
                </div>
            </div>
        </div>

        <!-- Ana İçerik -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Üst Bar -->
            <header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
                <div class="flex items-center">
                    <button id="mobileMenuButton" class="md:hidden text-gray-500 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="ml-4 text-xl font-semibold text-gray-800">
                        <?php 
                            switch($active_page) {
                                case 'hastalar': echo 'Hasta Yönetimi'; break;
                                case 'doktorlar': echo 'Doktor Yönetimi'; break;
                                case 'randevular': echo 'Randevu Yönetimi'; break;
                                case 'raporlar': echo 'Raporlar'; break;
                                case 'ayarlar': echo 'Sistem Ayarları'; break;
                                case 'doktorcalisma': echo "Doktor Çalışma Programı"; break;

                                default: echo 'Yönetici Paneli'; break;
                            }
                        ?>
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="p-2 text-gray-600 rounded-full hover:bg-gray-100 relative">
                        <i class="fas fa-bell"></i>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    <div class="relative">
                        <button class="flex items-center focus:outline-none">
                            <span class="mr-2 text-sm font-medium text-gray-700"><?php echo htmlspecialchars($k_ad); ?></span>
                            <img class="w-8 h-8 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($k_ad); ?>&background=3b82f6&color=fff" alt="Profil">
                        </button>
                    </div>
                </div>
            </header>

            <!-- İçerik Alanı -->
            <div class="content-container">
                <main class="main-content p-6 bg-gray-50">
                    <?php if(empty($active_page)): ?>
                        <!-- Dashboard İçeriği -->
                        <?php include 'dashboard_content.php'; ?>
                    <?php else: ?>
                        <!-- Dinamik İçerik -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <?php 
                            $page_file = $active_page . '.php';
                            if(file_exists($page_file)) {
                                include $page_file;
                            } else {
                                echo '<div class="text-center py-8">
                                    <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-4"></i>
                                    <h2 class="text-xl font-semibold text-gray-800">Sayfa Bulunamadı</h2>
                                    <p class="text-gray-600 mt-2">İstenen sayfa yüklenemedi.</p>
                                </div>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </main>
                
                <!-- Footer -->
                <footer class="bg-white border-t border-gray-200 py-4">
                    <div class="container mx-auto px-6">
                        <div class="flex flex-col md:flex-row justify-between items-center">
                            <div class="text-sm text-gray-500">
                                &copy; <?php echo date("Y"); ?> denizHBYS. Tüm hakları saklıdır.
                            </div>
                            <div class="flex space-x-4 mt-4 md:mt-0">
                                <a href="#" class="text-sm text-gray-500 hover:text-blue-600">Gizlilik Politikası</a>
                                <a href="#" class="text-sm text-gray-500 hover:text-blue-600">Kullanım Koşulları</a>
                                <a href="#" class="text-sm text-gray-500 hover:text-blue-600">Yardım</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <!-- Scriptler -->
    <script>
        // Mobil menü toggle
        document.getElementById('mobileMenuButton').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('hidden');
        });
    </script>
</body>

</html>
