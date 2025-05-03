<?php
include "../database/db.php";

// ID'yi alıyoruz
$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $db->prepare("DELETE FROM raporlar WHERE rapor_no = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_STR); // Eğer rapor_no string ise STR, integer ise INT
    $stmt->execute();

    // Silindikten sonra yönlendiriyoruz
    header("Location: dashboard.php?sayfa=raporlar");
    exit; // Yönlendirme sonrası kod çalışmasın diye exit eklenir
} else {
    echo "ID bulunamadı.";
}
?>
