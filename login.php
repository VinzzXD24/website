<?php
session_start();

// URL ke file JSON di GitHub
$json_url = "https://raw.githubusercontent.com/VinzzXD24/main/refs/heads/main/users.json"; // Ganti dengan URL JSON Anda

// Ambil data JSON
$json_data = file_get_contents($json_url);
if ($json_data === false) {
    die("Gagal mengambil data pengguna dari GitHub.");
}

// Decode JSON
$users = json_decode($json_data, true)['users'];

// Dapatkan IP Address pengguna
$client_ip = $_SERVER['REMOTE_ADDR'];

// Cek login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $found = false;

    foreach ($users as $user) {
        // Periksa jika username adalah "admin" dengan pengecualian
        if ($username === 'admin' && $password === 'Davinn24') {
            $_SESSION['logged_in'] = true;
            header('Location: create_panel.php');
            exit;
        }

        if ($user['username'] === $username) {
            // Cek kedaluwarsa
            if (strtotime($user['expires']) < time()) {
                $error_message = "Akun telah kedaluwarsa.";
                break;
            }

            // Cek password
            if ($user['password'] === $password) {
                // Cek IP Address
                if (!in_array($client_ip, $user['allowed_ips'])) {
                    $error_message = "Login tidak diizinkan dari IP Anda.";
                    break;
                }

                // Jika semua valid
                $_SESSION['logged_in'] = true;
                header('Location: create_panel.php');
                exit;
            } else {
                $error_message = "Password salah!";
            }

            $found = true;
            break;
        }
    }

    if (!$found) {
        $error_message = "Username tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .login-container button:hover {
            background: #0056b3;
        }
        .login-container .button-link {
            text-decoration: none;
            color: #007bff;
            font-size: 14px;
            margin-top: 10px;
            display: inline-block;
        }
        .login-container .button-link:hover {
            text-decoration: underline;
        }
        .error {
            color: #ff0000;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login Panel</h2>
        <?php if (isset($error_message)) : ?>
            <p class="error"><?= $error_message; ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <a href="index.php" class="button-link">Cek IP Address</a>
    </div>
</body>
</html>
