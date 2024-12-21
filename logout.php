<?php
session_start(); // Memulai sesi
session_destroy(); // Menghapus semua data sesi
header('Location: index.php'); // Mengarahkan pengguna ke halaman index.php
exit; // Menghentikan eksekusi script
?>
