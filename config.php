<?php
$host = 'localhost';
$dbname = 'allufymy_db_panel';
$username = 'allufymy_panel'; // Sesuaikan dengan database Anda
$password = 'Davinn24@'; // Sesuaikan dengan database Anda

try {
    $connection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
