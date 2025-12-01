<?php
// test_redirect.php - File untuk menguji redirect

session_start();

if (isset($_POST['submit'])) {
    // Coba redirect ke halaman lain
    $_SESSION['message'] = 'Redirect berhasil dilakukan!';
    header('Location: index.php');
    exit;
}

echo "<h2>Test Redirect</h2>";
echo "<form method='post'>";
echo "<input type='text' name='test' placeholder='Masukkan teks'>";
echo "<input type='submit' name='submit' value='Submit'>";
echo "</form>";

if (isset($_SESSION['message'])) {
    echo "<p style='color: green;'>".$_SESSION['message']."</p>";
    unset($_SESSION['message']);
}
?>