<?php
session_start();
require 'mysql/connect.php';

// Cek apakah user sudah login
if (!isset($_SESSION['ucp'])) {
    header("Location: index.php");
    exit;
}

$ucp = $_SESSION['ucp'];

// Koneksi ke database

// Data server SAMP
$server_ip = "204.10.193.207"; // Ganti sesuai IP server
$server_port = 7009;

// Ambil info server SAMP
function getSampInfo($ip, $port) {
    $socket = @fsockopen("udp://$ip", $port, $errno, $errstr, 2);
    if (!$socket) return false;

    $packet = 'SAMP';
    foreach (explode('.', $ip) as $octet) {
        $packet .= chr((int)$octet);
    }
    $packet .= chr($port & 0xFF);
    $packet .= chr($port >> 8);
    $packet .= 'i';

    fwrite($socket, $packet);
    $response = fread($socket, 2048);
    fclose($socket);

    if (strlen($response) > 11) {
        $offset = 11;

        $players = ord($response[$offset++]);
        $max_players = ord($response[$offset++]);

        $strlen_hostname = ord($response[$offset++]);
        $hostname = substr($response, $offset, $strlen_hostname);
        $offset += $strlen_hostname;

        if ($offset < strlen($response)) {
            $strlen_language = ord($response[$offset++]);

            if (($offset + $strlen_language) <= strlen($response)) {
                $language = substr($response, $offset, $strlen_language);
            } else {
                $language = "Unknown";
            }
        } else {
            $language = "Unknown";
        }

        return [
            'hostname'     => $hostname,
            'players'      => $players,
            'max_players'  => $max_players,
            'online'       => true
        ];
    }

    return false;
}

// Ambil data server
$server_info = getSampInfo($server_ip, $server_port);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Artapura RP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/dashboard.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .card {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      margin-bottom: 20px;
    }
    .status-online { color: green; font-weight: bold; }
    .status-offline { color: red; font-weight: bold; }
  </style>
</head>
<body>

<button id="menuToggle" aria-label="Toggle Menu">☰ Dashboard</button>

<aside class="sidebar" id="sidebar">
  <div class="logo" style="display: flex; align-items: center; gap: 10px; padding: 10px;">
    <img src="assets/image/artapura.png" alt="Logo" style="width: 40px; height: 40px;" />
    <span style="font-size: 20px; font-weight: bold; color: #FFCE1B;">Artapura RP</span>
  </div>
  <nav>
    <ul>
      <li><a href="dashboard.php"><i data-lucide="home"></i> Beranda</a></li>
      <li><a href="karakter.php"><i data-lucide="user"></i> Karakter</a></li>
      <li><a href="request-ck.php"><i data-lucide="skull"></i> Request CK</a></li>
      <li><a href="request-cs.php"><i data-lucide="theater"></i> Request CS</a></li>
      <?php if (isset($_SESSION['is_admin'])):?>
      <li><a href="admin.php"><i data-lucide="settings"></i> Admin Panel</a></li>
      <?php endif; ?>
      <li><a href="logout.php" class="logout"><i data-lucide="log-out"></i> Logout</a></li>
    </ul>
  </nav>
</aside>

<main class="content">
  <!-- Card 1: Selamat datang -->
  <div class="card">
    <h2>Selamat datang, <?= htmlspecialchars($ucp) ?>!</h2>
    <p>Gunakan menu untuk mengelola karakter dan akun Anda.</p>
  </div>

  <!-- Card 2: Status Server -->
  <div class="card">
    <h3>Status Server</h3>
    <?php if ($server_info): ?>
      <p>Status: <span class="status-online">Online</span></p>
      <p>Hostname: <?= htmlspecialchars($server_info['hostname']) ?></p>
      <p>Players: <?= (int)$server_info['players'] ?>/<?= (int)$server_info['max_players'] ?></p>
    <?php else: ?>
      <p>Status: <span class="status-offline">Offline</span></p>
      <p>Hostname: -</p>
      <p>Players: -</p>
    <?php endif; ?>
    <a class="play">Play</a>
  </div>
</main>

<script>
  const toggle = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');

  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
  });

  document.addEventListener('click', (e) => {
    if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
      sidebar.classList.remove('active');
    }
  });

  lucide.createIcons();
</script>
<script src="assets/script.js"></script>
</body>
</html>