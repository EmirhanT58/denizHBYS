<?php
date_default_timezone_set('Europe/Istanbul');

// Veritabanı bağlantı bilgileri
$host = "localhost";
$dbname = "denizhbys";
$username = "root";
$password = "";

try {
    // Önce veritabanı bağlantısını oluştur (veritabanı yoksa oluşturmak için dbname kullanmıyoruz)
    $db = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Veritabanını oluştur
    $db->exec("CREATE DATABASE IF NOT EXISTS `$dbname` /*!40100 DEFAULT CHARACTER SET utf16 COLLATE utf16_turkish_ci */ /*!80016 DEFAULT ENCRYPTION='N' */");
    $db->exec("USE `$dbname`");
    
    // Artık normal veritabanı bağlantısını oluştur
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    die("<div class='alert alert-danger'>Veritabanı bağlantı hatası: " . $e->getMessage() . "</div>");
}

function createTables($db) {
    $queries = [
        // tablo yapısı dökülüyor denizhbys.hastaneler (ilk önce bu tablo oluşturulmalı)
        "CREATE TABLE IF NOT EXISTS `hastaneler` (
          `h_id` int NOT NULL AUTO_INCREMENT,
          `h_ad` varchar(100) COLLATE utf16_turkish_ci NOT NULL,
          `il` text COLLATE utf16_turkish_ci,
          `ilce` text COLLATE utf16_turkish_ci,
          `h_adres` text COLLATE utf16_turkish_ci,
          `h_telefon` varchar(20) COLLATE utf16_turkish_ci DEFAULT NULL,
          `h_eposta` varchar(100) COLLATE utf16_turkish_ci DEFAULT NULL,
          `h_aktif` tinyint(1) DEFAULT '1',
          `h_kayit_tarihi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`h_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_turkish_ci",

        // Varsayılan hastane ekleme
        "INSERT IGNORE INTO `hastaneler` (`h_id`, `h_ad`, `il`, `ilce`, `h_adres`, `h_telefon`, `h_eposta`) VALUES
        (1, 'Deniz Hastanesi', 'İstanbul', 'Kadıköy', 'Caferağa Mah. Moda Cad. No:123', '02161234567', 'info@denizhastanesi.com')",

        // tablo yapısı dökülüyor denizhbys.poliklinikler
        "CREATE TABLE IF NOT EXISTS `poliklinikler` (
          `id` int NOT NULL AUTO_INCREMENT,
          `ad` varchar(100) COLLATE utf8mb3_turkish_ci NOT NULL,
          `h_id` int DEFAULT 1,
          PRIMARY KEY (`id`),
          KEY `FK1h_id` (`h_id`),
          CONSTRAINT `FK1h_id` FOREIGN KEY (`h_id`) REFERENCES `hastaneler` (`h_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci",

        // Poliklinik verileri ekleme
        "INSERT IGNORE INTO `poliklinikler` (`id`, `ad`, `h_id`) VALUES
        (1, 'Dahiliye (İç Hastalıkları)', 1),
        (2, 'Kardiyoloji', 1),
        (3, 'Nöroloji', 1),
        (4, 'Psikiyatri', 1),
        (5, 'Cildiye (Dermatoloji)', 1),
        (6, 'Göğüs Hastalıkları', 1),
        (7, 'Enfeksiyon Hastalıkları', 1),
        (8, 'Ortopedi ve Travmatoloji', 1),
        (9, 'Fizik Tedavi ve Rehabilitasyon', 1),
        (10, 'Üroloji', 1),
        (11, 'Kadın Hastalıkları ve Doğum', 1),
        (12, 'Çocuk Sağlığı ve Hastalıkları', 1),
        (13, 'Göz Hastalıkları', 1),
        (14, 'Kulak Burun Boğaz (KBB)', 1),
        (15, 'Genel Cerrahi', 1),
        (16, 'Beyin ve Sinir Cerrahisi', 1),
        (17, 'Plastik, Rekonstrüktif ve Estetik Cerrahi', 1),
        (18, 'Acil Servis', 1),
        (19, 'Ağız ve Diş Sağlığı', 1),
        (20, 'Endokrinoloji ve Metabolizma Hastalıkları', 1),
        (21, 'Nefroloji', 1),
        (22, 'Hematoloji', 1),
        (23, 'Gastroenteroloji', 1),
        (24, 'Radyoloji', 1),
        (25, 'Onkoloji', 1)",

        // tablo yapısı dökülüyor denizhbys.doktorlar
        "CREATE TABLE IF NOT EXISTS `doktorlar` (
          `id` int NOT NULL AUTO_INCREMENT,
          `tc` varchar(11) COLLATE utf8mb3_turkish_ci NOT NULL,
          `ad_soyad` varchar(100) COLLATE utf8mb3_turkish_ci NOT NULL,
          `pol_id` int DEFAULT NULL,
          `h_id` int DEFAULT NULL,
          `unvan` text COLLATE utf8mb3_turkish_ci NOT NULL,
          `telefon` varchar(15) COLLATE utf8mb3_turkish_ci NOT NULL,
          `email` varchar(100) COLLATE utf8mb3_turkish_ci DEFAULT NULL,
          `sifre` varchar(255) COLLATE utf8mb3_turkish_ci DEFAULT NULL,
          `durum` tinyint DEFAULT '1',
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `tc` (`tc`),
          UNIQUE KEY `email` (`email`),
          KEY `FK1pol_id` (`pol_id`),
          KEY `FK2h_id` (`h_id`),
          CONSTRAINT `FK1pol_id` FOREIGN KEY (`pol_id`) REFERENCES `poliklinikler` (`id`),
          CONSTRAINT `FK2h_id` FOREIGN KEY (`h_id`) REFERENCES `hastaneler` (`h_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci",

        // tablo yapısı dökülüyor denizhbys.hastalar
        "CREATE TABLE IF NOT EXISTS `hastalar` (
          `id` int NOT NULL AUTO_INCREMENT,
          `tc` varchar(11) COLLATE utf8mb3_turkish_ci NOT NULL,
          `ad` varchar(50) COLLATE utf8mb3_turkish_ci NOT NULL,
          `soyad` varchar(50) COLLATE utf8mb3_turkish_ci NOT NULL,
          `cinsiyet` enum('Erkek','Kadın','Diğer') COLLATE utf8mb3_turkish_ci NOT NULL,
          `dogum_tarihi` date NOT NULL,
          `telefon` varchar(15) COLLATE utf8mb3_turkish_ci NOT NULL,
          `email` varchar(100) COLLATE utf8mb3_turkish_ci DEFAULT NULL,
          `sifre` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_turkish_ci NOT NULL DEFAULT '0',
          `adres` text COLLATE utf8mb3_turkish_ci,
          `kan_grubu` varchar(6) COLLATE utf8mb3_turkish_ci DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `tc` (`tc`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci",

        // tablo yapısı dökülüyor denizhbys.doktor_programlari
        "CREATE TABLE IF NOT EXISTS `doktor_programlari` (
          `id` int NOT NULL AUTO_INCREMENT,
          `doktor_id` int NOT NULL,
          `poliklinik_id` int NOT NULL,
          `program_turu` enum('haftaici','haftasonu') COLLATE utf16_turkish_ci NOT NULL,
          `mesai_turu` enum('Normal Mesai','Nöbet','İcap Nöbeti','Ek Mesai') COLLATE utf16_turkish_ci NOT NULL DEFAULT 'Normal Mesai',
          `nerde` enum('Poliklinik','Ameliyat','Acil','Eğitim','Kongre','Yoğun Bakım Ünitesi (YBÜ)') CHARACTER SET utf16 COLLATE utf16_turkish_ci DEFAULT NULL,
          `baslangic_saati` time NOT NULL,
          `bitis_saati` time NOT NULL,
          `aktif` tinyint(1) DEFAULT '1',
          `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `poliklinik_id` (`poliklinik_id`),
          KEY `doktor_id` (`doktor_id`,`poliklinik_id`,`program_turu`,`aktif`),
          CONSTRAINT `doktor_programlari_ibfk_1` FOREIGN KEY (`doktor_id`) REFERENCES `doktorlar` (`id`),
          CONSTRAINT `doktor_programlari_ibfk_2` FOREIGN KEY (`poliklinik_id`) REFERENCES `poliklinikler` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_turkish_ci",

        // tablo yapısı dökülüyor denizhbys.h_ziyaretler
        "CREATE TABLE IF NOT EXISTS `h_ziyaretler` (
          `id` int NOT NULL AUTO_INCREMENT,
          `h_takip_kodu` varchar(50) COLLATE utf16_turkish_ci NOT NULL,
          `hasta_id` int NOT NULL,
          `hastane_id` int NOT NULL,
          `poliklinik_id` int NOT NULL,
          `doktor_id` int NOT NULL,
          `tarih` datetime NOT NULL,
          `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `hastane_id` (`hastane_id`),
          KEY `poliklinik_id` (`poliklinik_id`),
          KEY `doktor_id` (`doktor_id`),
          KEY `idx_hasta_id` (`hasta_id`),
          KEY `idx_takip_kodu` (`h_takip_kodu`),
          CONSTRAINT `h_ziyaretler_ibfk_1` FOREIGN KEY (`hastane_id`) REFERENCES `hastaneler` (`h_id`),
          CONSTRAINT `h_ziyaretler_ibfk_2` FOREIGN KEY (`poliklinik_id`) REFERENCES `poliklinikler` (`id`),
          CONSTRAINT `h_ziyaretler_ibfk_3` FOREIGN KEY (`doktor_id`) REFERENCES `doktorlar` (`id`),
          CONSTRAINT `h_ziyaretler_ibfk_4` FOREIGN KEY (`hasta_id`) REFERENCES `hastalar` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_turkish_ci",

        // tablo yapısı dökülüyor denizhbys.icd10_kodlari
        "CREATE TABLE IF NOT EXISTS `icd10_kodlari` (
          `kod` varchar(10) COLLATE utf16_turkish_ci NOT NULL,
          `aciklama` varchar(255) COLLATE utf16_turkish_ci NOT NULL,
          PRIMARY KEY (`kod`),
          KEY `idx_kod` (`kod`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_turkish_ci",
"INSERT INTO `icd10_kodlari` (`kod`, `aciklama`) VALUES
('A00', 'Kolera'),
('A01', 'Tifo ve paratifo'),
('A02', 'Diğer salmonella enfeksiyonları'),
('A03', 'Şigelloz'),
('A04', 'Diğer bakteriyel bağırsak enfeksiyonları'),
('A05', 'Diğer bakteriyel gıda zehirlenmeleri'),
('A06', 'Amiyobiyaz'),
('A07', 'Diğer protozoal bağırsak hastalıkları'),
('A08', 'Viral ve diğer tanımlanmış bağırsak enfeksiyonları'),
('A09', 'Diyare ve gastroenterit'),
('E03', 'Hipotiroidizm'),
('E04', 'Diğer nontoksik guatr'),
('E05', 'Hipertiroidizm'),
('E10', 'Tip 1 diyabetes mellitus'),
('E11', 'Tip 2 diyabetes mellitus'),
('E78', 'Lipid metabolizması bozuklukları'),
('I10', 'Esansiyel (primer) hipertansiyon'),
('I20', 'Angina pektoris'),
('I21', 'Akut miyokard enfarktüsü'),
('I25', 'Kronik iskemik kalp hastalığı'),
('I48', 'Atriyal fibrilasyon ve flutter'),
('I50', 'Kalp yetmezliği'),
('J06', 'Akut üst solunum yolu enfeksiyonları'),
('J18', 'Pnömoni, patojen tanımlanmamış'),
('J20', 'Akut bronşit'),
('J44', 'Diğer kronik obstrüktif akciğer hastalığı'),
('J45', 'Astım'),
('K21', 'Gastroözofageal reflü hastalığı'),
('K25', 'Mide ülseri'),
('K29', 'Gastrit ve duodenit'),
('K57', 'Divertiküler bağırsak hastalığı'),
('K80', 'Kolelitiazis'),
('M15', 'Polioartroz'),
('M17', 'Gonartroz [diz osteoartriti]'),
('M54', 'Bel ağrısı'),
('M79', 'Diğer yumuşak doku hastalıkları, başka yerde sınıflanmamış'),
('N18', 'Kronik böbrek hastalığı'),
('N20', 'Üriner sistem taşı'),
('N39', 'İdrar sistemi diğer bozuklukları'),
('N40', 'Prostat hiperplazisi'),
('S42', 'Omuz ve kol kırığı'),
('S72', 'Femur kırığı'),
('T78', 'İstenmeyen ilaç etkileri'),
('T90', 'Kafa yaralanması sekelleri'),
('Z00', 'Genel muayene ve inceleme'),
('Z01', 'Diğer özel muayene ve incelemeler'),
('Z23', 'Tek başına enfeksiyöz hastalıklar için temas'),
('Z79', 'Uzun süreli ilaç tedavisi')",
        // tablo yapısı dökülüyor denizhbys.ilaclar
        "CREATE TABLE IF NOT EXISTS `ilaclar` (
          `ilac_id` int NOT NULL AUTO_INCREMENT,
          `ilac_barkod` varchar(50) COLLATE utf16_turkish_ci NOT NULL,
          `ilac_ad` varchar(100) COLLATE utf16_turkish_ci NOT NULL,
          `ilac_etken_madde` varchar(100) COLLATE utf16_turkish_ci DEFAULT NULL,
          `ilac_formu` varchar(50) COLLATE utf16_turkish_ci DEFAULT NULL,
          `ilac_dogrulama_kodu` varchar(50) COLLATE utf16_turkish_ci DEFAULT NULL,
          `ilac_firma` varchar(100) COLLATE utf16_turkish_ci DEFAULT NULL,
          `ilac_fiyat` decimal(10,2) DEFAULT NULL,
          `ilac_aktif` tinyint(1) DEFAULT '1',
          PRIMARY KEY (`ilac_id`),
          UNIQUE KEY `ilac_barkod` (`ilac_barkod`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_turkish_ci",


"INSERT INTO `ilaclar` (`ilac_id`, `ilac_barkod`, `ilac_ad`, `ilac_etken_madde`, `ilac_formu`, `ilac_dogrulama_kodu`, `ilac_firma`, `ilac_fiyat`, `ilac_aktif`) VALUES
	(1, '8699871000015', 'Parol 500 mg', 'Parasetamol', 'Tablet', 'PRL5002023', 'Atabay', 25.90, 1),
	(2, '8699871000022', 'Minoset 500 mg', 'Parasetamol', 'Tablet', 'MNS5002023', 'Nobel', 18.75, 1),
	(3, '8699871000039', 'Arveles 25 mg', 'Dexketoprofen', 'Tablet', 'ARV252023', 'Sanovel', 42.50, 1),
	(4, '8699871000046', 'Augmentin 1 g', 'Amoksisilin + Klavulanat', 'Tablet', 'AUG1002023', 'GSK', 89.90, 1),
	(5, '8699871000053', 'Cipro 500 mg', 'Siprofloksasin', 'Tablet', 'CPR5002023', 'Bayer', 65.25, 1),
	(6, '8699871000060', 'Lansor 30 mg', 'Lansoprazol', 'Kapsül', 'LNS302023', 'Sanofi', 54.80, 1),
	(7, '8699871000077', 'Gaviscon Şurup', 'Sodyum aljinat', 'Şurup', 'GVS202023', 'Reckitt', 32.45, 1),
	(8, '8699871000084', 'Glucophage 1000 mg', 'Metformin', 'Tablet', 'GLU1002023', 'Merck', 48.60, 1),
	(9, '8699871000091', 'Diofor 5 mg', 'Amlodipin', 'Tablet', 'DIO52023', 'Abdi İbrahim', 37.90, 1),
	(10, '8699871000107', 'Redoxon C Vitamini', 'Askorbik asit', 'Efervesan tablet', 'RDX202023', 'Bayer', 29.95, 1),
	(11, '8699871000114', 'Peditus Şurup', 'Parasetamol', 'Şurup', 'PDT202023', 'İbrahim Etem', 26.80, 1),
	(12, '8699871000121', 'Voltaren Ampul', 'Diklofenak sodyum', 'Ampul', 'VLT202023', 'Novartis', 15.50, 1)",



        // tablo yapısı dökülüyor denizhbys.randevular
        "CREATE TABLE IF NOT EXISTS `randevular` (
          `id` int NOT NULL AUTO_INCREMENT,
          `hasta_id` int NOT NULL,
          `doktor_id` int NOT NULL,
          `pol_id` int NOT NULL,
          `hastane_id` int DEFAULT NULL,
          `tarih` datetime NOT NULL,
          `tur` enum('Kontrol Muayenesi','Devam Eden Muayene','Normal Muayene') COLLATE utf8mb3_turkish_ci DEFAULT 'Normal Muayene',
          `durum` enum('Beklemede','Onaylandı','İptal Edildi','Tamamlandı') COLLATE utf8mb3_turkish_ci DEFAULT 'Beklemede',
          `aciklama` text CHARACTER SET utf8mb3 COLLATE utf8mb3_turkish_ci,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `hasta_id` (`hasta_id`),
          KEY `doktor_id` (`doktor_id`),
          KEY `pol_id` (`pol_id`),
          KEY `hastane_fk` (`hastane_id`),
          KEY `doktor_id_2` (`doktor_id`,`pol_id`,`tarih`,`durum`),
          CONSTRAINT `hastane_fk` FOREIGN KEY (`hastane_id`) REFERENCES `hastaneler` (`h_id`),
          CONSTRAINT `randevular_ibfk_1` FOREIGN KEY (`hasta_id`) REFERENCES `hastalar` (`id`) ON DELETE CASCADE,
          CONSTRAINT `randevular_ibfk_2` FOREIGN KEY (`doktor_id`) REFERENCES `doktorlar` (`id`) ON DELETE CASCADE,
          CONSTRAINT `randevular_ibfk_3` FOREIGN KEY (`pol_id`) REFERENCES `poliklinikler` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci",

        // tablo yapısı dökülüyor denizhbys.raporlar
        "CREATE TABLE IF NOT EXISTS `raporlar` (
          `rapor_id` int NOT NULL AUTO_INCREMENT,
          `rapor_no` varchar(100) COLLATE utf8mb3_turkish_ci NOT NULL,
          `hasta_id` int NOT NULL,
          `doktor_id` int NOT NULL,
          `rapor_tur` enum('Sağlık Raporu','İlaç Raporu','Heyet Raporu','Epikriz Raporu','İstirahat Raporu','Engelli Raporu','Radyoloji Raporu','Laboratuvar Raporu') CHARACTER SET utf8mb3 COLLATE utf8mb3_turkish_ci NOT NULL,
          `tahlil_sonuclari` text COLLATE utf8mb3_turkish_ci,
          `hekim_gor` text COLLATE utf8mb3_turkish_ci,
          `tarih` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `durum` enum('Onaylandı','Bekliyor','Rededildi','Süresi Doldu') CHARACTER SET utf8mb3 COLLATE utf8mb3_turkish_ci DEFAULT 'Bekliyor',
          PRIMARY KEY (`rapor_id`),
          KEY `hasta_id` (`hasta_id`),
          KEY `doktor_id` (`doktor_id`),
          CONSTRAINT `raporlar_ibfk_1` FOREIGN KEY (`hasta_id`) REFERENCES `hastalar` (`id`) ON DELETE CASCADE,
          CONSTRAINT `raporlar_ibfk_2` FOREIGN KEY (`doktor_id`) REFERENCES `doktorlar` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci",

        // tablo yapısı dökülüyor denizhbys.receteler
        "CREATE TABLE IF NOT EXISTS `receteler` (
          `recete_id` int NOT NULL AUTO_INCREMENT,
          `recete_no` varchar(50) COLLATE utf16_turkish_ci NOT NULL DEFAULT '0',
          `hastane_id` int NOT NULL,
          `pol_id` int NOT NULL,
          `doktor_id` int NOT NULL,
          `hasta_id` int DEFAULT NULL,
          `recete_turu` enum('normal','kırmızı','yeşil','mor','turuncu') COLLATE utf16_turkish_ci DEFAULT 'normal',
          `ilac_id` int NOT NULL,
          `doz` varchar(50) COLLATE utf16_turkish_ci NOT NULL,
          `periyod` varchar(50) COLLATE utf16_turkish_ci DEFAULT NULL,
          `kutu_adet` int DEFAULT '1',
          `aciklama` text COLLATE utf16_turkish_ci,
          `ol_tarih` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
          `recete_durum` enum('aktif','iptal','kullanıldı') COLLATE utf16_turkish_ci DEFAULT 'aktif',
          PRIMARY KEY (`recete_id`),
          KEY `pol_id` (`pol_id`),
          KEY `idx_recete_doktor` (`doktor_id`),
          KEY `idx_recete_hastane` (`hastane_id`),
          KEY `idx_recete_ilac` (`ilac_id`),
          CONSTRAINT `receteler_ibfk_1` FOREIGN KEY (`hastane_id`) REFERENCES `hastaneler` (`h_id`),
          CONSTRAINT `receteler_ibfk_2` FOREIGN KEY (`pol_id`) REFERENCES `poliklinikler` (`id`),
          CONSTRAINT `receteler_ibfk_3` FOREIGN KEY (`doktor_id`) REFERENCES `doktorlar` (`id`),
          CONSTRAINT `receteler_ibfk_4` FOREIGN KEY (`ilac_id`) REFERENCES `ilaclar` (`ilac_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_turkish_ci",

        // tablo yapısı dökülüyor denizhbys.yoneticiler
        "CREATE TABLE IF NOT EXISTS `yoneticiler` (
          `id` int NOT NULL AUTO_INCREMENT,
          `k_ad` varchar(50) COLLATE utf8mb3_turkish_ci NOT NULL,
          `sifre` varchar(255) COLLATE utf8mb3_turkish_ci NOT NULL,
          `ad_soyad` varchar(100) COLLATE utf8mb3_turkish_ci NOT NULL,
          `email` varchar(100) COLLATE utf8mb3_turkish_ci NOT NULL,
          `telefon` varchar(15) COLLATE utf8mb3_turkish_ci DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `k_ad` (`k_ad`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci"
    ];
    
    try {
        // Transaction başlat
        $db->beginTransaction();
        
        // Tüm tabloları oluştur ve verileri ekle
        foreach($queries as $query) {
            $db->exec($query);
        }
        
        // Varsayılan yönetici ekleme
        $checkAdmin = $db->query("SELECT COUNT(*) FROM yoneticiler WHERE k_ad = 'admin'")->fetchColumn();
        if($checkAdmin == 0) {
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $db->exec("INSERT INTO yoneticiler (k_ad, sifre, ad_soyad, email) 
                      VALUES ('admin', '$hashedPassword', 'Sistem Yöneticisi', 'admin@denizhbys.com')");
            echo "<div class='alert alert-success'>Varsayılan yönetici oluşturuldu</div>";
        }
        
        // Transaction'ı tamamla
        $db->commit();
        
        echo "<div class='alert alert-success'>Tablolar başarıyla oluşturuldu</div>";
    } catch(PDOException $e) {
        // Hata durumunda transaction'ı geri al
       
     $e->getMessage();
     
    }
   
}

// Tabloları oluştur (sadece ilk kurulumda)
 createTables($db);
?>