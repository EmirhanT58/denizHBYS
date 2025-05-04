<?php
// sil.php

require_once '../database/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = $db->prepare("DELETE FROM doktor_programlari WHERE id = ?");
    $sql->execute([$id]);

    // Silme sonrası yönlendirme
    header("Location: dashboard.php");
    exit;
} else {
    echo "Geçersiz istek.";
}
?>
