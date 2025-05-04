<?php
session_start();
session_unset();
session_destroy();
header("Location: hasta_login.php");
exit;
?>