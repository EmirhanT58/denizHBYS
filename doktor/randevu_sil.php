<?php
// randevu_sil.php
require_once '../database/db.php';
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = $db->prepare("DELETE FROM randevular WHERE id = ?");
    $sql->execute([$id]);

    // Silme sonrası yönlendirme
    header("Location: randevular.php");
    exit;
} else {
    echo "Geçersiz istek.";
}
?>