<?php
require_once __DIR__ . '/../includes/config.php';

// Destroy session
session_destroy();

// Redirect to pelapor login
header('Location: ' . BASE_URL . 'pelapor/login.php');
exit;
?>
