<?php
session_start();
session_unset();
session_destroy();
header("Location: doktor_login.php");
exit;
?>