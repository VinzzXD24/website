<?php
session_start();

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

// Fetching users
$users_response = send_api_request("$base_url/users", "GET", $api_key_plta);
if ($users_response['http_code'] !== 200) {
    die("Gagal mengambil daftar pengguna: " . $users_response['response']);
}
$users = json_decode($users_response['response'], true);

// Fetching servers
$servers_response = send_api_request("$base_url/servers", "GET", $api_key_pltc);
if ($servers_response['http_code'] !== 200) {
    die("Gagal mengambil daftar server: " . $servers_response['response']);
}
$servers = json_decode($servers_response['response'], true);

// Mendapatkan IP pengguna
$user_ip = $_SERVER['REMOTE_ADDR'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hallo, Reseller Vinzz Official</title>
    <style>
        body {
            background-color: #2c2c2c;
            color: #ffffff;
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        h1 {
            color: #f39c12;
        }
        .container {
            margin: auto;
            width: 80%;
        }
        .button {
            display: inline-block;
            background-color: #3498db;
            color: #ffffff;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .list {
            list-style-type: none;
            padding: 0;
            text-align: left;
            margin-top: 20px;
        }
        .list-item {
            background-color: #333;
            margin: 5px 0;
            padding: 15px;
            border-radius: 8px;
            position: relative;
        }
        .list-item h3 {
            margin: 0;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .list-item .toggle-btn {
            cursor: pointer;
            color: #3498db;
            font-size: 18px;
        }
        .details {
            display: none;
            padding-top: 5px;
        }
        .ip-display {
            margin-top: 20px;
            font-size: 18px;
            color: #f39c12;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hallo, Reseller Vinzz Official</h1>
        <p>Selamat Datang Di Website Create Panel Private. Saya Harap Kamu Dapat Menggunakan Dengan Baik Dan Tidak Asal Create Server Ya.</p>
        <p>Terimakasih <br>© Vinzz Official.</p>
        <a href="create_panel.php" class="button">Create Panel</a>
        <div class="ip-display">
            <strong>IP Anda:</strong> <?php echo $user_ip; ?>
        </div>
        <h2>Daftar Pengguna</h2>
        <div class="list">
            <div class="list-item">
                <h3>
                    <span class="toggle-btn">></span> Daftar Pengguna
                </h3>
                <div class="details">
                    <?php 
                    $user_counter = 1;
                    foreach ($users['data'] as $user): ?>
                        <li><?php echo $user_counter++ . ". " . $user['attributes']['username']; ?></li>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <h2>Daftar Server</h2>
        <div class="list">
            <div class="list-item">
                <h3>
                    <span class="toggle-btn">></span> Daftar Server
                </h3>
                <div class="details">
                    <?php 
                    $server_counter = 1;
                    foreach ($servers['data'] as $server): 
                        $server_attributes = $server['attributes'];
                        $limits = $server_attributes['limits'];
                        $status = $server_attributes['status'] == "online" ? "Online" : "Offline";
                    ?>
                        <li>
                            <?php echo $server_counter++ . ". " . $server_attributes['name']; ?>
                            <br>
                            RAM: <?php echo $limits['memory'] ? $limits['memory'] . " MB" : "Unlimited"; ?>, 
                            Disk: <?php echo $limits['disk'] ? $limits['disk'] . " MB" : "Unlimited"; ?>, 
                            CPU: <?php echo $limits['cpu'] ? $limits['cpu'] . "%" : "Unlimited"; ?>, 
                            Status: <?php echo $status; ?>
                        </li>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toggles = document.querySelectorAll('.toggle-btn');
            toggles.forEach(function(toggle) {
                toggle.addEventListener('click', function() {
                    var details = toggle.closest('.list-item').querySelector('.details');
                    details.style.display = details.style.display === 'none' ? 'block' : 'none';
                    toggle.textContent = toggle.textContent === '>' ? 'v' : '>';
                });
            });
        });
    </script>
</body>
</html>
