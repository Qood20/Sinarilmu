<?php
// test_includes.php - File untuk menguji apakah semua include berfungsi dengan benar

echo "<h2>Testing File Includes</h2>\n";

try {
    // Test include dari root
    require_once 'includes/functions.php';
    echo "<p style='color: green;'>✓ Root include (includes/functions.php) works</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Root include failed: " . $e->getMessage() . "</p>\n";
}

try {
    // Test include dari dashboard
    require_once 'dashboard/pages/home.php';
    echo "<p style='color: green;'>✓ Dashboard include (dashboard/pages/home.php) works</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Dashboard include failed: " . $e->getMessage() . "</p>\n";
}

try {
    // Test include dari admin
    require_once 'admin/pages/dashboard.php';
    echo "<p style='color: green;'>✓ Admin include (admin/pages/dashboard.php) works</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Admin include failed: " . $e->getMessage() . "</p>\n";
}

echo "<p>Testing completed.</p>\n";
?>