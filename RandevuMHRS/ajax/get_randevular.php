<?php
session_start();
require_once "../../database/db.php";

// Hasta ID'sini oturumdan al
$hasta_id = $_SESSION['hasta_id'] ?? null;

if (!$hasta_id) {
    echo json_encode(['error' => 'Oturum bulunamadı']);
    exit();
}

try {
    $randevuQuery = $db->prepare("
        SELECT r.id, r.tarih, r.durum,
               d.ad_soyad AS doktor_ad,
               p.ad AS poliklinik_ad, h.h_ad AS hastane_ad
        FROM randevular r
        JOIN doktorlar d ON r.doktor_id = d.id
        JOIN poliklinikler p ON d.pol_id = p.id
        JOIN hastaneler h ON d.h_id = h.h_id
        WHERE r.hasta_id = ?
        ORDER BY r.tarih DESC
    ");
    $randevuQuery->execute([$hasta_id]);

    if ($randevuQuery->rowCount() > 0) {
        $output = '';
        while ($row = $randevuQuery->fetch(PDO::FETCH_ASSOC)) {
            $durumClass = $row['durum'] == 'Onaylandı' ? 'text-green-600' : 'text-yellow-600';
            
            $output .= '<tr>';
            $output .= '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($row['tarih']) . '</td>';
            $output .= '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($row['hastane_ad']) . '</td>';
            $output .= '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($row['poliklinik_ad']) . '</td>';
            $output .= '<td class="px-6 py-4 whitespace-nowrap">' . htmlspecialchars($row['doktor_ad']) . '</td>';
            $output .= '<td class="px-6 py-4 whitespace-nowrap ' . $durumClass . '">' . htmlspecialchars($row['durum']) . '</td>';
            $output .= '<td class="px-6 py-4 whitespace-nowrap">';
            if ($row['durum'] != 'İptal Edildi') {
                $output .= '<button onclick="randevuIptal(' . $row['id'] . ')" class="text-red-600 hover:text-red-900">İptal Et</button>';
            }
            $output .= '</td>';
            $output .= '</tr>';
        }
        echo $output;
    } else {
        echo '<tr><td colspan="7" class="text-center py-4">Randevu bulunamadı</td></tr>';
    }
} catch (PDOException $e) {
    echo '<tr><td colspan="7" class="text-center py-4">Veritabanı hatası: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
}
?>