<?php
require_once __DIR__ . '/../includes/config.php';

// Destroy session
session_destroy();

// Redirect to login
header('Location: ' . BASE_URL . 'admin/login.php');
exit;
?>
