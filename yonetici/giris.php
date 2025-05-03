<?php
session_start();
if(isset($_SESSION["yonetici_id"])) {
    header("Location: dashboard.php");
    exit;
}

require_once "../database/db.php";

$error = "";
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $k_ad = trim($_POST["k_ad"]);
    $sifre = trim($_POST["sifre"]);
    
    if(empty($k_ad) || empty($sifre)) {
        $error = "Kullanıcı adı ve şifre gereklidir!";
    } else {
        $stmt = $db->prepare("SELECT * FROM yoneticiler WHERE k_ad = ?");
        $stmt->execute([$k_ad]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($sifre, $user["sifre"])) {
            $_SESSION["yonetici_id"] = $user["id"];
            $_SESSION["k_ad"] = $user["k_ad"];
            $_SESSION["ad_soyad"] = $user["ad_soyad"];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Geçersiz kullanıcı adı veya şifre!";
        }
    }
}
?>

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
            <p class="text-gray-600 mt-2">Yönetici Giriş Paneli</p>
        </div>
        
        <?php if($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2" for="k_ad">Kullanıcı Adı</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" id="k_ad" name="k_ad" class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Kullanıcı adınız">
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