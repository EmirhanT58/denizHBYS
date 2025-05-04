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
<nav class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <div class="h-8 w-8 rounded-md bg-primary-600 flex items-center justify-center mr-2">
                        <i class="fas fa-heartbeat text-white text-sm"></i>
                    </div>
                    <span class="text-xl font-semibold text-gray-800">DenizHBYS</span>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="ml-3 relative">
                    <div class="flex items-end space-x-2">
                        <div class="text-right hidden md:block">
                            <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($doctor_ad) ?></p>
                            <p class="text-xs text-gray-500">Doktor</p>
                        </div>
                        <div class="h-8 w-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center">
                            <i class="fas fa-user-md text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>