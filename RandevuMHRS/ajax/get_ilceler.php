<?php
require_once "../../database/db.php";

$il = $_GET['il'] ?? '';
if ($il) {
    $query = $db->prepare("SELECT DISTINCT ilce FROM hastaneler WHERE il = ? ORDER BY ilce ASC");
    $query->execute([$il]);
    
    echo '<option value="">İlçe Seçiniz</option>';
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="' . htmlspecialchars($row['ilce']) . '">' . htmlspecialchars($row['ilce']) . '</option>';
    }
} else {
    echo '<option value="">İl Seçiniz</option>';
}
?>