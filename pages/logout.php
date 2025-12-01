<?php
// pages/logout.php - Proses logout pengguna

ob_start(); // Start output buffering
session_start();
require_once '../includes/functions.php';

$user_id = $_SESSION['user_id'] ?? null;
logout_user();

// Catat aktivitas logout jika user_id tersedia
if ($user_id) {
    log_activity($user_id, 'Logout', 'Pengguna logout dari sistem');
}

header('Location: ../?page=login');
ob_end_clean(); // Clean the output buffer
exit;