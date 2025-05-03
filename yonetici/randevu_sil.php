<?
  $sil=$db->prepare("DELETE * FROM randevular WHERE id = ?");
  $sil->execute([
    $id
  ])
?>