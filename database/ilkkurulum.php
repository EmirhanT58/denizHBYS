<?php

function createTables($db) {
    $queries = [
        "CREATE TABLE IF NOT EXISTS yoneticiler (
            id INT AUTO_INCREMENT PRIMARY KEY,
            k_ad VARCHAR(50) NOT NULL UNIQUE,
            sifre VARCHAR(255) NOT NULL,
            ad_soyad VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            telefon VARCHAR(15),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS doktorlar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tc VARCHAR(11) UNIQUE NOT NULL,
            ad_soyad VARCHAR(100) NOT NULL,
            uzmanlik VARCHAR(100) NOT NULL,
            telefon VARCHAR(15) NOT NULL,
            email VARCHAR(100) UNIQUE,
            sifre VARCHAR(255),
            durum TINYINT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS hastalar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tc VARCHAR(11) UNIQUE NOT NULL,
            ad VARCHAR(50) NOT NULL,
            soyad VARCHAR(50) NOT NULL,
            cinsiyet ENUM('Erkek','Kadın','Diğer') NOT NULL,
            dogum_tarihi DATE NOT NULL,
            telefon VARCHAR(15) NOT NULL,
            email VARCHAR(100),
            adres TEXT,
            kan_grubu VARCHAR(5),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS randevular (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hasta_id INT NOT NULL,
            doktor_id INT NOT NULL,
            tarih DATETIME NOT NULL,
            durum ENUM('Beklemede','Onaylandı','İptal Edildi','Tamamlandı') DEFAULT 'Beklemede',
            aciklama TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (hasta_id) REFERENCES hastalar(id) ON DELETE CASCADE,
            FOREIGN KEY (doktor_id) REFERENCES doktorlar(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS raporlar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hasta_id INT NOT NULL,
            doktor_id INT NOT NULL,
            baslik VARCHAR(255) NOT NULL,
            icerik TEXT NOT NULL,
            tahlil_sonuclari TEXT,
            recete TEXT,
            tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (hasta_id) REFERENCES hastalar(id) ON DELETE CASCADE,
            FOREIGN KEY (doktor_id) REFERENCES doktorlar(id) ON DELETE CASCADE
        )"
    ];
    
    try {
        foreach($queries as $query) {
            $db->exec($query);
        }
        
        // Varsayılan yönetici ekleme
        $checkAdmin = $db->query("SELECT COUNT(*) FROM yoneticiler WHERE k_ad = 'admin'")->fetchColumn();
        if($checkAdmin == 0) {
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $db->exec("INSERT INTO yoneticiler (k_ad, sifre, ad_soyad, email) 
                      VALUES ('admin', '$hashedPassword', 'Sistem Yöneticisi', 'admin@denizhbys.com')");
        }
    } catch(PDOException $e) {
        die("Tablo oluşturma hatası: " . $e->getMessage());
    }
}

// Tabloları oluştur (sadece ilk kurulumda)
createTables($db);
?>