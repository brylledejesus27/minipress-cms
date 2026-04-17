<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: /minipress-cms/login.php");
    exit();
}
?>