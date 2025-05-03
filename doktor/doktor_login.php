<?php
session_start();
require_once "../database/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tc = $_POST["tc"];
    $sifre = $_POST["sifre"];

    $stmt = $db->prepare("SELECT * FROM doktorlar WHERE tc = ?");
    $stmt->execute([$tc]);
    $doktor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doktor && password_verify($sifre, $doktor["sifre"])) {
        $_SESSION["doktor_id"] = $doktor["id"];
        $_SESSION["doktor_ad"] = $doktor["ad_soyad"];
        $_SESSION["doktor_pol_id"] = $doktor["pol_id"];
        header("Location: doktor_dashboard.php");
        exit;
    } else {
        $error = "TC veya şifre hatalı!";
    }
}
?>

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
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>denizHBYS - Yönetici Girişi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
        }
        .login-box {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="login-box bg-white rounded-xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <i class="fas fa-hospital text-4xl text-blue-600 mb-3"></i>
            <h1 class="text-3xl font-bold text-gray-800">denizHBYS</h1>
            <p class="text-gray-600 mt-2">Doktor Giriş Paneli</p>
        </div>
        
        <?php if($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2" for="k_ad">TCKN</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" id="tc" name="tc" class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="TC numaranız">
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2" for="sifre">Şifre</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" id="sifre" name="sifre" class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Şifreniz">
                </div>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                Giriş Yap
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-600">
            <p>Sistem yöneticisi değil misiniz? <a href="../doktor/doktor_login.php" class="text-blue-600 hover:underline">Doktor girişi</a></p>
        </div>
    </div>
</body>
</html>
