<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Oturum kontrolü
if (!isset($_SESSION['doktor_id'])) {
    header("Location: doktor_login.php");
    exit();
}
// Doktor ID'sini oturumdan al
$doctor_id = $_SESSION['doktor_id'] ?? 0;
$doctor_ad = $_SESSION['doktor_ad'] ?? '';

?>
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
<div class="sidebar w-64 hidden md:block border-r border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center">
                        <i class="fas fa-user-md text-sm"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($doctor_ad); ?></p>
                        <p class="text-xs text-gray-500">Doktor Paneli</p>
                    </div>
                </div>
            </div>
            <nav class="p-4">
                <ul class="space-y-1">
                    <li>
                        <a href="doktor_dashboard.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-home mr-3 text-primary-500"></i>
                            Ana Sayfa
                        </a>
                    </li>
                    <li>
                        <a href="hastalar.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-user-injured mr-3 text-gray-500"></i>
                            Hasta Yönetimi
                        </a>
                    </li>
                    <li>
                        <a href="randevular.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-calendar-check mr-3 text-gray-500"></i>
                            Randevu Yönetimi
                        </a>
                    </li>
                    <li>
                        <a href="raporlar.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-file-medical mr-3 text-gray-500"></i>
                            Raporlar
                        </a>
                    </li>
                    <li>
                        <a href="icd10sorgula.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-search mr-3 text-gray-500"></i>
                            ICD-10 Sorgu
                        </a>
                    </li>
                    <li>
                        <a href="ayarlar.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-primary-50 hover:text-primary-700">
                            <i class="fas fa-cog mr-3 text-gray-500"></i>
                            Ayarlar
                        </a>
                    </li>
                    <li class="pt-4 border-t border-gray-200">
                        <a href="../logout.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-700 hover:bg-red-50 hover:text-red-700">
                            <i class="fas fa-sign-out-alt mr-3 text-gray-500"></i>
                            Çıkış Yap
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
