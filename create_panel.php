<?php
session_start();

// Pastikan pengguna telah login
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

function send_api_request($url, $method, $api_key, $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $api_key",
        "Accept: application/json",
        "Content-Type: application/json",
    ]);
    if (!is_null($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'http_code' => $http_code,
        'response' => $response,
    ];
}

$base_url = 'https://private.vinzzavailable.my.id/api/application';
$api_key_plta = 'ptla_AN1aHeq9dqSgfMaJ6rUTEfRR1lR0oOOZ4lOrI2ZWES4';
$api_key_pltc = 'ptlc_Tw1fSQ2EC0YzdqDIyXjgJBxBZMhqVU98zBV8eAcP9Xv';
$node_id = 1;

// Ambil daftar alokasi valid untuk node
$allocations_response = send_api_request("$base_url/nodes/$node_id/allocations", "GET", $api_key_plta);
if ($allocations_response['http_code'] !== 200) {
    die("Gagal mengambil daftar alokasi: " . $allocations_response['response']);
}
$allocations = json_decode($allocations_response['response'], true);

// Pilih alokasi secara acak dari yang tersedia
$available_allocations = [];
foreach ($allocations['data'] as $allocation) {
    if (!$allocation['attributes']['assigned']) {
        $available_allocations[] = [
            'id' => $allocation['attributes']['id'],
            'address' => $allocation['attributes']['ip'] . ':' . $allocation['attributes']['port'],
        ];
    }
}

if (empty($available_allocations)) {
    die("Tidak ada alokasi yang tersedia pada node ini.");
}

$random_allocation = $available_allocations[array_rand($available_allocations)];

$ram_disk_cpu_config = [
    '1gb' => ['ram' => 1024, 'disk' => 1024, 'cpu' => 30],
    '2gb' => ['ram' => 2048, 'disk' => 2048, 'cpu' => 40],
    '3gb' => ['ram' => 3072, 'disk' => 3072, 'cpu' => 50],
    '4gb' => ['ram' => 4096, 'disk' => 4096, 'cpu' => 60],
    '5gb' => ['ram' => 5120, 'disk' => 5120, 'cpu' => 70],
    '6gb' => ['ram' => 6144, 'disk' => 6144, 'cpu' => 80],
    '7gb' => ['ram' => 7168, 'disk' => 7168, 'cpu' => 90],
    '8gb' => ['ram' => 8192, 'disk' => 8192, 'cpu' => 100],
    '9gb' => ['ram' => 9216, 'disk' => 9216, 'cpu' => 110],
    '10gb' => ['ram' => 10240, 'disk' => 10240, 'cpu' => 120],
    '20gb' => ['ram' => 20480, 'disk' => 20480, 'cpu' => 200],
    'unli' => ['ram' => 0, 'disk' => 0, 'cpu' => 0],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $ram_choice = $_POST['ram_choice'];

    if (!array_key_exists($ram_choice, $ram_disk_cpu_config)) {
        die("Pilihan RAM tidak valid.");
    }
    $config = $ram_disk_cpu_config[$ram_choice];

    $user_data = [
        "email" => $email,
        "username" => $username,
        "first_name" => $username,
        "last_name" => $username,
        "language" => "en",
        "password" => $password,
    ];
    $user_response = send_api_request("$base_url/users", "POST", $api_key_plta, $user_data);
    if ($user_response['http_code'] !== 201) {
        die("Gagal membuat user: " . $user_response['response']);
    }
    $user = json_decode($user_response['response'], true);
    $user_id = $user['attributes']['id'];

    $server_data = [
        "name" => "$username's Server",
        "user" => $user_id,
        "egg" => 15,
        "docker_image" => "ghcr.io/parkervcp/yolks:nodejs_18",
        "startup" => "npm start",
        "environment" => ["CMD_RUN" => "npm start"],
        "limits" => [
            "memory" => $config['ram'],
            "disk" => $config['disk'],
            "cpu" => $config['cpu'],
            "swap" => 0,
            "io" => 500,
        ],
        "feature_limits" => [
            "databases" => 1,
            "allocations" => 1,
            "backups" => 1,
            "cpu_pinning" => 0,
        ],
        "allocation" => [
            "default" => $random_allocation['id'],
        ],
    ];
    $server_response = send_api_request("$base_url/servers", "POST", $api_key_pltc, $server_data);
    if ($server_response['http_code'] !== 201) {
        die("Gagal membuat server: " . $server_response['response']);
    }

    $_SESSION['result'] = [
        'email' => $email,
        'username' => $username,
        'password' => $password,
        'allocation' => $random_allocation['address'],
    ];
    header("Location: result.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Server</title>
    <script>
    document.addEventListener('contextmenu', function (e) {
        e.preventDefault();
    });

    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && (e.key === 'u' || e.key === 'U')) {
            e.preventDefault();
        }
        if (e.ctrlKey && e.shiftKey && (e.key === 'i' || e.key === 'I')) {
            e.preventDefault();
        }
        if (e.key === 'F12') {
            e.preventDefault();
        }
    });
</script>
    <style>
        /* CSS untuk Tampilan Create Panel */
        body {
            background-color: #1e1e1e;
            color: #ffffff;
            font-family: 'Arial', sans-serif;
        }

        .container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #2c2c2c;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="email"],
        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #333;
            border-radius: 3px;
            background-color: #333;
            color: #fff;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        button {
            width: 48%;
            padding: 10px;
            border: none;
            border-radius: 3px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        button[type="submit"] {
            background-color: #007bff;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        button[type="button"] {
            background-color: #6c757d;
        }

        button[type="button"]:hover {
            background-color: #5a6268;
        }

        
        .logout-button {
            width: 48%;
            margin: 20px auto 0;
            background-color: #dc3545;
            display: block;
            text-align: center;
        }

        .logout-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Buat Server</h2>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="ram_choice">RAM:</label>
                <select id="ram_choice" name="ram_choice" required>
                    <option value="1gb">1GB</option>
                    <option value="2gb">2GB</option>
                    <option value="3gb">3GB</option>
                    <option value="4gb">4GB</option>
                    <option value="5gb">5GB</option>
                    <option value="6gb">6GB</option>
                    <option value="7gb">7GB</option>
                    <option value="8gb">8GB</option>
                    <option value="9gb">9GB</option>
                    <option value="10gb">10GB</option>
                    <option value="20gb">20GB</option>
                    <option value="unli">Unli</option>
                </select>
            </div>

            <button type="submit">Buat Server</button>
            <button type="button" onclick="window.location.href='index.php'">Kembali ke Beranda</button>
            </div>
        </form>
        <button class="logout-button" onclick="window.location.href='logout.php'">Logout</button>
    </div>
</body>
</html>
