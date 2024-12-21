<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['result'])) {
    die("Data tidak ditemukan.");
}

$result = $_SESSION['result'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pembuatan Server</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Hasil Pembuatan Server</h2>
        <p>Nama Pengguna: <?php echo htmlspecialchars($result['username']); ?></p>
        <p>Email: <?php echo htmlspecialchars($result['email']); ?></p>
        <p>Kata Sandi: <?php echo htmlspecialchars($result['password']); ?></p>
        <p>Alokasi IP: <?php echo htmlspecialchars($result['allocation']); ?></p>
        <a href="https://private.vinzzavailable.my.id">Login ke Panel</a>
    </div>
</body>
</html>
