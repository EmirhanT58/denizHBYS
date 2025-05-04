-- --------------------------------------------------------
-- Sunucu:                       127.0.0.1
-- Sunucu sürümü:                8.4.3 - MySQL Community Server - GPL
-- Sunucu İşletim Sistemi:       Win64
-- HeidiSQL Sürüm:               12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- denizhbys için veritabanı yapısı dökülüyor
CREATE DATABASE IF NOT EXISTS `denizhbys` /*!40100 DEFAULT CHARACTER SET utf16 COLLATE utf16_turkish_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `denizhbys`;

-- tablo yapısı dökülüyor denizhbys.doktorlar
CREATE TABLE IF NOT EXISTS `doktorlar` (
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci;

-- denizhbys.doktorlar: ~5 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `doktorlar` (`id`, `tc`, `ad_soyad`, `pol_id`, `h_id`, `unvan`, `telefon`, `email`, `sifre`, `durum`, `created_at`) VALUES
	(1, '111111111', 'Hakan Dal', 1, 1, 'Prof. Dr.', '2345678543', 'h.dal@denizhbys.com', '$2y$10$6vfqhOrg4aGwndH/toRFpODf2ZDeUH0GOx1y4RTb/LLNNFaMpTwTi', 1, '2025-04-22 16:53:23'),
	(2, '112233', 'Kader Gülen', 13, 1, 'Doç. Dr.', '552 442 55 32', 'k.gulen@denizhbys.com', '$2y$10$l7OsR0jnyYzvBbfF.T/HAOMH7dbkI/8e0fbjiNsZwnhhlkJUjmpuC', 1, '2025-05-02 17:11:59'),
	(3, '112234', 'Yaren Urak', 5, 1, 'Uzm. Dr', '554 232 32 42', 'y.urak@denizhbys.com', '$2y$10$4UaS19OYluXEeMLa7KQ6V.DsUO2xAA3z5dIh/w6tdRZ0Ca0G6qa5e', 1, '2025-05-04 14:21:29'),
	(4, '12342', 'Recep Kar', 6, 1, 'Uzm. Dr', '552 442 52 53', 'r.kar@denizhbys.com', '$2y$10$TRVofPDTySHKFEVANpCdbONJV9/gFg2F3srwzIs5EfEGBYyd5KV9a', 1, '2025-05-04 14:44:20'),
	(6, '1123545', 'Efe Yurt', 16, 1, 'Op. Dr.', '552 523 52 32', 'e.kurt@denizhbys.com', '$2y$10$5cHd0XYxdcNE4XeMCFqVC.XlsLsRi9T2tWiJzTdwv13asHXZYfU1a', 1, '2025-05-04 14:47:14');

-- tablo yapısı dökülüyor denizhbys.doktor_programlari
CREATE TABLE IF NOT EXISTS `doktor_programlari` (
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
  KEY `nerde` (`nerde`),
  KEY `doktor_id_2` (`doktor_id`,`poliklinik_id`,`program_turu`,`aktif`),
  KEY `nerde_2` (`nerde`),
  KEY `doktor_id_3` (`doktor_id`,`poliklinik_id`,`program_turu`,`aktif`),
  KEY `nerde_3` (`nerde`),
  KEY `doktor_id_4` (`doktor_id`,`poliklinik_id`,`program_turu`,`aktif`),
  KEY `nerde_4` (`nerde`),
  CONSTRAINT `doktor_programlari_ibfk_1` FOREIGN KEY (`doktor_id`) REFERENCES `doktorlar` (`id`),
  CONSTRAINT `doktor_programlari_ibfk_2` FOREIGN KEY (`poliklinik_id`) REFERENCES `poliklinikler` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf16 COLLATE=utf16_turkish_ci;

-- denizhbys.doktor_programlari: ~5 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `doktor_programlari` (`id`, `doktor_id`, `poliklinik_id`, `program_turu`, `mesai_turu`, `nerde`, `baslangic_saati`, `bitis_saati`, `aktif`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 'haftaici', 'Normal Mesai', 'Poliklinik', '08:30:00', '17:30:00', 1, '2025-05-02 19:57:52', '2025-05-02 19:57:52'),
	(2, 2, 13, 'haftaici', 'Normal Mesai', 'Poliklinik', '08:30:00', '17:30:00', 1, '2025-05-03 20:30:21', '2025-05-03 20:30:21'),
	(4, 6, 16, 'haftaici', 'Normal Mesai', 'Poliklinik', '08:30:00', '17:30:00', 1, '2025-05-04 15:06:01', '2025-05-04 15:06:01'),
	(5, 3, 5, 'haftaici', 'Normal Mesai', 'Poliklinik', '08:30:00', '17:30:00', 1, '2025-05-04 15:14:08', '2025-05-04 15:14:08'),
	(6, 4, 6, 'haftaici', 'Normal Mesai', 'Poliklinik', '08:30:00', '17:30:00', 1, '2025-05-04 15:30:36', '2025-05-04 15:30:36');

-- tablo yapısı dökülüyor denizhbys.hastalar
CREATE TABLE IF NOT EXISTS `hastalar` (
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci;

-- denizhbys.hastalar: ~3 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `hastalar` (`id`, `tc`, `ad`, `soyad`, `cinsiyet`, `dogum_tarihi`, `telefon`, `email`, `sifre`, `adres`, `kan_grubu`, `created_at`) VALUES
	(2, '112233', 'Eren', 'Çiğdem', 'Erkek', '2008-05-05', '5534232453', 'erencigdem@gmail.com', '$2y$10$34MKgVLN7/NBptOjpo0rredyKAs08yTLlYbJS8bCG0XZa34vO8Xa2', 'Kocaeli / Darıca', 'AB RH+', '2025-04-28 19:26:22'),
	(3, '111222', 'Emirhan', 'Tan', 'Erkek', '2007-01-01', '5524234232', 'emirhantan@gmail.com', '$2y$10$AWoBYSTzv9mV4Y496BYUBO3LXRin2aVLP5ybwUpSq2dlmz/1qgZPy', 'Kocaeli/Darıca', 'AB RH+', '2025-05-01 15:08:34'),
	(4, '111233', 'Emirhan', 'Yerlikaya', 'Erkek', '2000-03-12', '5523212323', 'emircany@gmail.com', '$2y$10$npqn.M3zFqYni3lixPsGveHfjFKWqixiPOh1I37Pwc8FoMODE3Q3y', NULL, NULL, '2025-05-04 15:21:12');

-- tablo yapısı dökülüyor denizhbys.hastaneler
CREATE TABLE IF NOT EXISTS `hastaneler` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_turkish_ci;

-- denizhbys.hastaneler: ~1 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `hastaneler` (`h_id`, `h_ad`, `il`, `ilce`, `h_adres`, `h_telefon`, `h_eposta`, `h_aktif`, `h_kayit_tarihi`) VALUES
	(1, 'Kocaeli Şehir Hastanesi', 'Kocaeli', 'İzmit', 'Kocaeli Şehir Hastanesi, Tavşantepe, Akif Sk. No:63, 41060 İzmit/Kocaeli', '(0262) 225 27 00', NULL, 1, '2025-05-03 19:44:50');

-- tablo yapısı dökülüyor denizhbys.h_ziyaretler
CREATE TABLE IF NOT EXISTS `h_ziyaretler` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_turkish_ci;

-- denizhbys.h_ziyaretler: ~0 rows (yaklaşık) tablosu için veriler indiriliyor

-- tablo yapısı dökülüyor denizhbys.icd10_kodlari
CREATE TABLE IF NOT EXISTS `icd10_kodlari` (
  `kod` varchar(10) COLLATE utf16_turkish_ci NOT NULL,
  `aciklama` varchar(255) COLLATE utf16_turkish_ci NOT NULL,
  PRIMARY KEY (`kod`),
  KEY `idx_kod` (`kod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_turkish_ci;

-- denizhbys.icd10_kodlari: ~48 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `icd10_kodlari` (`kod`, `aciklama`) VALUES
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
	('Z79', 'Uzun süreli ilaç tedavisi');

-- tablo yapısı dökülüyor denizhbys.ilaclar
CREATE TABLE IF NOT EXISTS `ilaclar` (
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf16 COLLATE=utf16_turkish_ci;

-- denizhbys.ilaclar: ~12 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `ilaclar` (`ilac_id`, `ilac_barkod`, `ilac_ad`, `ilac_etken_madde`, `ilac_formu`, `ilac_dogrulama_kodu`, `ilac_firma`, `ilac_fiyat`, `ilac_aktif`) VALUES
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
	(12, '8699871000121', 'Voltaren Ampul', 'Diklofenak sodyum', 'Ampul', 'VLT202023', 'Novartis', 15.50, 1);

-- tablo yapısı dökülüyor denizhbys.poliklinikler
CREATE TABLE IF NOT EXISTS `poliklinikler` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ad` varchar(100) COLLATE utf8mb3_turkish_ci NOT NULL,
  `h_id` int DEFAULT (1),
  PRIMARY KEY (`id`),
  KEY `FK1h_id` (`h_id`),
  CONSTRAINT `FK1h_id` FOREIGN KEY (`h_id`) REFERENCES `hastaneler` (`h_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci;

-- denizhbys.poliklinikler: ~25 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `poliklinikler` (`id`, `ad`, `h_id`) VALUES
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
	(25, 'Onkoloji', 1);

-- tablo yapısı dökülüyor denizhbys.randevular
CREATE TABLE IF NOT EXISTS `randevular` (
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci;

-- denizhbys.randevular: ~4 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `randevular` (`id`, `hasta_id`, `doktor_id`, `pol_id`, `hastane_id`, `tarih`, `tur`, `durum`, `aciklama`, `created_at`) VALUES
	(2, 3, 1, 1, 1, '2025-05-01 23:12:23', 'Normal Muayene', 'Tamamlandı', NULL, '2025-05-01 16:14:17'),
	(3, 2, 1, 1, 1, '2025-05-05 09:00:00', 'Normal Muayene', 'Onaylandı', '', '2025-05-01 21:32:10'),
	(4, 3, 2, 13, 1, '2025-05-06 11:00:00', 'Normal Muayene', 'İptal Edildi', NULL, '2025-05-04 01:25:36'),
	(10, 3, 3, 5, 1, '2025-05-06 08:45:00', 'Normal Muayene', 'Onaylandı', NULL, '2025-05-04 14:52:09');

-- tablo yapısı dökülüyor denizhbys.raporlar
CREATE TABLE IF NOT EXISTS `raporlar` (
  `rapor_id` int NOT NULL AUTO_INCREMENT,
  `rapor_no` varchar(100) COLLATE utf8mb3_turkish_ci NOT NULL,
  `hasta_id` int NOT NULL,
  `doktor_id` int NOT NULL,
  `rapor_tur` enum('Sağlık Raporu','İlaç Raporu','Heyet Raporu','Epikriz Raporu','İstirahat Raporu','Engelli Raporu','Radyoloji Raporu','Laboratuvar Raporu') CHARACTER SET utf8mb3 COLLATE utf8mb3_turkish_ci NOT NULL,
  `tahlil_sonuclari` text COLLATE utf8mb3_turkish_ci,
  `hekim_gor` text COLLATE utf8mb3_turkish_ci,
  `tarih` timestamp NOT NULL DEFAULT (now()),
  `durum` enum('Onaylandı','Bekliyor','Rededildi','Süresi Doldu') CHARACTER SET utf8mb3 COLLATE utf8mb3_turkish_ci DEFAULT 'Bekliyor',
  PRIMARY KEY (`rapor_id`),
  KEY `hasta_id` (`hasta_id`),
  KEY `doktor_id` (`doktor_id`),
  CONSTRAINT `raporlar_ibfk_1` FOREIGN KEY (`hasta_id`) REFERENCES `hastalar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `raporlar_ibfk_2` FOREIGN KEY (`doktor_id`) REFERENCES `doktorlar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci;

-- denizhbys.raporlar: ~3 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `raporlar` (`rapor_id`, `rapor_no`, `hasta_id`, `doktor_id`, `rapor_tur`, `tahlil_sonuclari`, `hekim_gor`, `tarih`, `durum`) VALUES
	(3, '2025050220-619082-4', 3, 2, 'Sağlık Raporu', 'EKLENCEK', 'Eklencek', '2025-05-02 17:56:00', 'Onaylandı'),
	(4, '2025050221-439735-9', 2, 1, 'Sağlık Raporu', 'YOK', 'YOK', '2025-05-01 21:00:00', 'Onaylandı'),
	(5, '2025050316-249827-6', 3, 1, 'İstirahat Raporu', 'YOK', 'Hastanın 1 gün istirahat yapılması uygun görülmüştür.\r\nSaygılarımla', '2025-05-02 21:00:00', 'Onaylandı');

-- tablo yapısı dökülüyor denizhbys.receteler
CREATE TABLE IF NOT EXISTS `receteler` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_turkish_ci;

-- denizhbys.receteler: ~1 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `receteler` (`recete_id`, `recete_no`, `hastane_id`, `pol_id`, `doktor_id`, `hasta_id`, `recete_turu`, `ilac_id`, `doz`, `periyod`, `kutu_adet`, `aciklama`, `ol_tarih`, `recete_durum`) VALUES
	(1, '2IG3C2SIOF', 1, 1, 1, 3, 'normal', 1, '1x2', '1', 1, NULL, '2025-05-03 19:48:00', 'aktif');

-- tablo yapısı dökülüyor denizhbys.yoneticiler
CREATE TABLE IF NOT EXISTS `yoneticiler` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci;

-- denizhbys.yoneticiler: ~1 rows (yaklaşık) tablosu için veriler indiriliyor
INSERT INTO `yoneticiler` (`id`, `k_ad`, `sifre`, `ad_soyad`, `email`, `telefon`, `created_at`) VALUES
	(1, 'admin', '$2y$10$0ouGL0c9Iq6TfWgOi0AIaeDqtPT2QTyn5q2r8Z16O/U5kUMGIK49a', 'Sistem Yöneticisi', 'admin@denizhbys.com', NULL, '2025-04-22 16:01:05');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
