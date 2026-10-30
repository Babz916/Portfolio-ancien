<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
logoutAdmin();
header('Location: ' . BASE_URL . '/admin.php');
exit;
