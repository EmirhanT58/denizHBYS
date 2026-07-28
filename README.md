<div align="center">

# 🏥 denizHBYS — Hastane Bilgi Yönetim Sistemi

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)

### Modern, güvenli, modüler ve kullanıcı dostu web tabanlı Hastane Bilgi Yönetim Sistemi

Hasta kayıtları, randevu yönetimi, doktor işlemleri, reçete oluşturma ve yönetim süreçlerini tek bir platform üzerinden yönetebilmeniz için geliştirilmiştir.

</div>

---

# 📖 İçindekiler

- [Proje Hakkında](#-proje-hakkında)
- [Temel Özellikler](#-temel-özellikler)
- [Modüller](#-modüller)
- [Kullanılan Teknolojiler](#-kullanılan-teknolojiler)
- [Sistem Gereksinimleri](#-sistem-gereksinimleri)
- [Kurulum](#-kurulum)
- [Klasör Yapısı](#-klasör-yapısı)
- [Güvenlik](#-güvenlik)
- [Katkıda Bulunma](#-katkıda-bulunma)
- [Lisans](#-lisans)

---

# 📋 Proje Hakkında

**denizHBYS**, hastane ve kliniklerin günlük işleyişini dijital ortama taşıyan kapsamlı bir **Hastane Bilgi Yönetim Sistemi (HBYS)** projesidir.

Sistem;

- Hasta kayıtlarını
- Doktor işlemlerini
- Poliklinik yönetimini
- Randevu planlamasını
- Reçete oluşturmayı
- Yönetici işlemlerini

tek bir merkezden yönetebilmek amacıyla geliştirilmiştir.

Modern PHP mimarisi kullanılarak hazırlanan sistem; güvenli, ölçeklenebilir ve geliştirmeye açık bir yapıya sahiptir.

---

# ✨ Temel Özellikler

- 👤 Hasta kayıt sistemi
- 📅 Online randevu yönetimi
- 👨‍⚕️ Doktor paneli
- 👩‍💼 Sekreter paneli
- 👑 Yönetici paneli
- 💊 Reçete oluşturma
- 📋 Muayene geçmişi
- 🏥 Poliklinik yönetimi
- 📊 Dashboard istatistikleri
- 🔒 Rol tabanlı yetkilendirme (RBAC)
- 🛡️ SQL Injection koruması
- 🛡️ XSS koruması
- 📱 Responsive tasarım
- ⚡ PDO tabanlı güvenli veritabanı işlemleri

---

# 🚀 Modüller

## 👑 Yönetici (Admin)

Yönetici paneli sayesinde sistem tamamen kontrol edilebilir.

### Özellikler

- Personel yönetimi
- Doktor yönetimi
- Sekreter yönetimi
- Kullanıcı yetkilendirme
- Poliklinik ekleme
- Poliklinik düzenleme
- Poliklinik silme
- Dashboard istatistikleri
- Aktif hasta sayısı
- Aktif doktor sayısı
- Günlük randevu sayısı
- Sistem yönetimi

---

## 👨‍⚕️ Doktor

Doktor paneli günlük hasta işlemlerini kolaylaştırır.

### Özellikler

- Günlük randevular
- Hasta geçmişi
- Muayene bilgileri
- Teşhis oluşturma
- Reçete oluşturma
- Muayene durumunu güncelleme

Durumlar:

- Bekliyor
- Muayene Edildi
- İptal

---

## 👩‍💼 Sekreter / Vezne

Hasta kayıt ve randevu süreçlerini yönetir.

### Özellikler

- Yeni hasta kaydı
- Hasta güncelleme
- Hasta arama
- Randevu oluşturma
- Doktor seçimi
- Poliklinik seçimi
- Hasta giriş işlemleri

---

# 🔐 Güvenlik

Sistem güvenliği ön planda tutularak geliştirilmiştir.

## Güvenlik Özellikleri

- PDO Prepared Statement kullanımı
- SQL Injection koruması
- XSS filtreleme
- Session kontrolü
- Yetki kontrolü
- Rol bazlı erişim
- Güvenli oturum yönetimi

---

# 🛠 Kullanılan Teknolojiler

## Backend

- PHP 8+
- Object Oriented Programming (OOP)
- MVC Mimarisi
- PDO

## Frontend

- HTML5
- CSS3
- JavaScript (ES6+)
- AJAX

## Tasarım

- Tailwind CSS *(veya Bootstrap)*
- Font Awesome
- Google Fonts

## Veritabanı

- MySQL
- MariaDB

## Sunucu

- Apache
- XAMPP
- WAMP
- MAMP

---

# 💻 Sistem Gereksinimleri

| Gereksinim | Sürüm |
|------------|-------|
| PHP | 8.0+ |
| MySQL | 5.7+ |
| MariaDB | Güncel |
| Apache | Güncel |
| Tarayıcı | Chrome, Edge, Firefox, Safari |

---

# ⚙️ Kurulum

## 1. Projeyi İndirin

```bash
git clone https://github.com/EmirhanT58/denizHBYS.git
```

---

## 2. Projeyi Sunucu Dizini İçine Taşıyın

**XAMPP**

```
C:/xampp/htdocs/denizHBYS/
```

**WAMP**

```
C:/wamp/www/denizHBYS/
```

---

## 3. Apache ve MySQL'i Başlatın

XAMPP veya WAMP üzerinden aşağıdaki servisleri çalıştırın.

- Apache
- MySQL

---

## 4. Veritabanını Oluşturun

phpMyAdmin'i açın.

```
http://localhost/phpmyadmin
```

Yeni bir veritabanı oluşturun.

```
deniz_hbys
```

Karakter seti:

```
utf8mb4_general_ci
```

---

## 5. SQL Dosyasını İçe Aktarın

```
database/
└── denizHBYS.sql
```

Dosyasını seçin ve **Import (İçe Aktar)** işlemini gerçekleştirin.

---

## 6. Veritabanı Ayarlarını Yapın

`database/db.php`

```php
<?php

$host = "localhost";
$dbname = "deniz_hbys";
$username = "root";
$password = "";

try {

    $db = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e){

    die("Veritabanı bağlantı hatası : ".$e->getMessage());

}

?>
```

---

## 7. Projeyi Çalıştırın

Tarayıcınızdan açın.

```
http://localhost/denizHBYS
```

Varsayılan yönetici hesabınız veya oluşturduğunuz kullanıcı ile giriş yapabilirsiniz. (Otomatik admin hesabı gelmektedir)

---

# 📂 Klasör Yapısı

```text
denizHBYS/
│
├── RandevuMHRS/            # Randevu alma modülü ve AJAX işlemleri
│   ├── ajax/               # Veritabanından veri çeken AJAX endpoint'leri
│   │   ├── get_doktorlar.php
│   │   ├── get_hastaneler.php
│   │   ├── get_randevular.php
│   │   └── ...
│   ├── dashboard.php
│   ├── randevu_al.php
│   └── r_logout.php
│
├── css/                    # Stil ve Tailwind konfigürasyonu
│   └── tailwind.config.js
│
├── database/               # Veritabanı bağlantı ve kurulum dosyaları
│   ├── db.php              # Ana veritabanı bağlantısı
│   ├── denizHBYS.sql       # Veritabanı yedeği
│   ├── ilkkurulum.php
│   └── tarihsaat.php
│
├── doktor/                 # Doktor paneli ve modülleri
│   ├── doktor_dashboard.php
│   ├── doktor_login.php
│   ├── randevular.php
│   ├── recete.php
│   ├── raporlar.php
│   └── ...
│
├── hasta/                  # Hasta paneli ve modülleri
│   ├── hasta_panel.php
│   ├── hasta_login.php
│   ├── randevularim.php
│   ├── recetelerim.php
│   ├── raporlarim.php
│   └── ...
│
├── yonetici/               # Yönetici (Admin) paneli ve ayarlar
│   ├── dashboard.php
│   ├── doktorlar.php
│   ├── hastalar.php
│   ├── randevular.php
│   ├── raporlar.php
│   ├── ayarlar.php
│   └── ...
│
├── README.md               # Proje dökümantasyonu (Bu dosya)
├── composer.json           # PHP bağımlılık yönetimi
└── composer.lock
```

---

# 🔒 Güvenlik

Sistem aşağıdaki güvenlik standartlarını temel almaktadır.

- PDO Prepared Statements
- Session Authentication
- RBAC Yetkilendirme
- SQL Injection Koruması
- XSS Koruması
- Input Validation
- Output Escaping

---

# 🤝 Katkıda Bulunma

Katkılarınızı memnuniyetle karşılıyorum.

1. Bu projeyi **Fork** edin.

```bash
git fork
```

2. Yeni bir branch oluşturun.

```bash
git checkout -b feature/YeniOzellik
```

3. Değişikliklerinizi commit edin.

```bash
git commit -m "Yeni özellik eklendi"
```

4. Branch'i gönderin.

```bash
git push origin feature/YeniOzellik
```

5. Pull Request oluşturun.

---

# 👨‍💻 Geliştirici

**Emirhan T.**

GitHub

> https://github.com/EmirhanT58

---

# 📄 Lisans

Bu proje **MIT License** ile lisanslanmıştır.

Detaylı bilgi için **LICENSE** dosyasına göz atabilirsiniz.

---

<div align="center">

### ⭐ Projeyi beğendiyseniz yıldız vermeyi unutmayın!

Made with ❤️ using PHP & MySQL

</div>
