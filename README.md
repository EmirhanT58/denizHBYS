<div align="center">

# 🏥 denizHBYS — Hastane Bilgi Yönetim Sistemi

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)

*Modern, modüler, hızlı ve kullanıcı dostu bir web tabanlı Hastane Bilgi Yönetim Sistemi.*

</div>

---

## 📖 İçindekiler
- [Proje Hakkında](#-proje-hakkında)
- [Temel Modüller ve Özellikler](#-temel-modüller-ve-özellikler)
- [Kullanılan Teknolojiler](#-kullanılan-teknolojiler)
- [Sistem Gereksinimleri](#-sistem-gereksinimleri)
- [Kurulum Adımları](#-kurulum-adımları)
- [Proje Görselleri](#-proje-görselleri)
- [Geliştirici](#-geliştirici)
- [Lisans](#-lisans)

---

## 📋 Proje Hakkında

**denizHBYS**, hastanelerdeki idari ve tıbbi süreçlerin dijital ortamda hatasız, hızlı ve güvenli bir şekilde yönetilmesini sağlayan kapsamlı bir **Hastane Bilgi Yönetim Sistemi (HBYS)** otomasyonudur. 

Proje; hasta kabulden muayeneye, poliklinik takibinden reçete işlemlerine kadar temel sağlık hizmeti akışlarını tek bir çatı altında toplar. Sade mimarisi ve modern arayüzü sayesinde kolayca özelleştirilebilir ve geliştirilebilir.

---

## 🚀 Temel Modüller ve Özellikler

- 👨‍⚕️ **Doktor ve Personel Yönetimi:** Doktor unvanları, branş eşleştirmeleri ve çalışma takvimi yönetimi.
- 🧑‍ريض **Hasta Kabul ve Kayıt:** Yeni hasta kaydı oluşturma, TC kimlik / dosya numarası ile arama ve geçmiş tetkik takibi.
- 📅 **Muayene ve Randevu Sistemi:** Poliklinik bazlı randevu planlama, aktif sıra takibi ve muayene durumu yönetimi.
- 💊 **Reçete ve İlaç Modülü:** Muayene sonrasında reçete oluşturma, ilaç listeleme ve geçmiş reçeteleri görüntüleme.
- 🔐 **Rol Tabanlı Yetkilendirme (RBAC):** Yönetici, Doktor, Vezne/Sekreter gibi farklı roller için güvenli yetki seviyeleri ve oturum yönetimi.

---

## 🛠️ Kullanılan Teknolojiler

Proje geliştirilirken güncel ve performanslı web teknolojileri tercih edilmiştir:

* **Backend:** PHP (Nesne Yönelimli Programlama / Modüler Mimari)
* **Veritabanı:** MySQL / MariaDB (İlişkisel Veritabanı Yönetimi)
* **Frontend:** HTML5, CSS3, JavaScript (ES6+)
* **Stil / Arayüz:** Tailwind CSS / Bootstrap
* **Sunucu Ortamı:** Apache / Nginx (XAMPP, WAMP veya MAMP uyumlu)

---

## 💻 Sistem Gereksinimleri

Projeyi kendi bilgisayarınızda çalıştırmak için aşağıdaki bileşenlerin kurulu olması gerekir:
- **PHP:** `v8.0` veya üzeri
- **MySQL / MariaDB:** `v5.7` veya üzeri
- **Web Sunucusu:** Apache (XAMPP önerilir)
- **Tarayıcı:** Güncel herhangi bir web tarayıcısı (Chrome, Edge, Firefox vb.)

---

## ⚙️ Kurulum Adımları

Projeyi yerel ortamınızda (Localhost) çalıştırmak için adım adım şu işlemleri takip edin:

### 1. Depoyu Klonlayın
Terminal veya komut satırını açarak projeyi bilgisayarınıza indirin:
```bash
git clone [https://github.com/EmirhanT58/denizHBYS.git](https://github.com/EmirhanT58/denizHBYS.git)
