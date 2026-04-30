<?php
session_start();
session_destroy();
header("Location: /minipress-cms/login.php");
exit();
?>