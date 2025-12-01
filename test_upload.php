<?php
// test_upload.php - Halaman uji upload untuk debugging

session_start();

echo "<h2>Tes Upload File</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Data:</h3>";
    print_r($_POST);
    
    echo "<h3>FILES Data:</h3>";
    print_r($_FILES);
    
    if (isset($_FILES['file_upload'])) {
        echo "<h3>Detail File:</h3>";
        echo "Name: " . $_FILES['file_upload']['name'] . "<br>";
        echo "Type: " . $_FILES['file_upload']['type'] . "<br>";
        echo "Size: " . $_FILES['file_upload']['size'] . "<br>";
        echo "Temp: " . $_FILES['file_upload']['tmp_name'] . "<br>";
        echo "Error: " . $_FILES['file_upload']['error'] . "<br>";
    }
} else {
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="file_upload" />';
    echo '<input type="submit" value="Upload" />';
    echo '</form>';
}

echo "<br><a href='?'>Refresh</a>";
?>