<?php
date_default_timezone_set('Europe/Istanbul');  // Türkiye için zaman dilimi ayarı

// Veritabanı bağlantı dosyası
$host = "localhost";
$dbname = "denizhbys";
$username = "root";
$password = "";
date_default_timezone_set('Europe/Istanbul');

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}


?>