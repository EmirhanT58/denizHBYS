<?php
require_once "../../database/db.php";

$ilce = $_GET['ilce'] ?? '';
if ($ilce) {
    $query = $db->prepare("SELECT h_id, h_ad FROM hastaneler WHERE ilce = ? ORDER BY h_ad ASC");
    $query->execute([$ilce]);
    
    echo '<option value="">Hastane Seçiniz</option>';
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="' . htmlspecialchars($row['h_id']) . '">' . htmlspecialchars($row['h_ad']) . '</option>';
    }
} else {
    echo '<option value="">İlçe Seçiniz</option>';
}
?>