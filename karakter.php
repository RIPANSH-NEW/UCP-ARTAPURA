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
// Mapping job ID ke nama
$jobNames = [
  1 => "Mechanic",
  2 => "Lumber Jack",
  3 => "Trucker",
  4 => "Penambang Batu",
  5 => "Production",
  6 => "Farmer",
  7 => "Courier",
  8 => "Smuggler",
  9 => "Baggage Airport",
  10 => "Pemotong Ayam",
  11 => "Merchant Filler",
  12 => "Farm Markisa",
  13 => "Penambang Minyak",
  14 => "Taxi",
  15 => "Pemerah Susu"
];

$csstatus = [
  -1 => "Pending",
  0 => "Denied",
  1 => "Active"
  ];
  

// Ambil karakter milik UCP yang sedang login
$sql = "SELECT username, level, money2, bmoney2, job, job2, characterstory, skin FROM players WHERE ucp = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
  die("Query prepare gagal: " . $conn->error);
}
$stmt->bind_param("s", $ucp);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Karakter</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/dashboard.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .status-1,
    .status-0,
    .status--1 {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 0.85em;
      color: white;
    }
    
    .status-1 { background-color: green; }
    .status-0 { background-color: red; }
    .status--1 { background-color: orange; }
    
    .character-info {
      display: flex;
      align-items: center;
      gap: 25px;
    }

    .character-info img.skin-preview {
      width: 64px;
      height: 64px;
      border-radius: 8px;
      border: 1px solid #ccc;
      object-fit: contain;
    }
  </style>
</head>
<body>

<!-- Tombol Menu -->
<button id="menuToggle" aria-label="Toggle Menu">☰ Karakter</button>

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

<!-- Konten -->
<main class="content">
  <p>Berikut detail karakter yang terdaftar dalam akun Anda:</p>

  <?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
      <?php
        // Skin & Job
        $skinId = intval($row['skin']);
        $skinUrl = "https://assets.open.mp/assets/images/skins/{$skinId}.png";
        
        $csId = intval($row['characterstory']);
        $characterstory = isset($csstatus[$csId]) ? $csstatus[$csId] : 'None';
        
        $statusColor = match($csId) {
    1   => 'status-1',    // Active = hijau
    0   => 'status-0',    // Denied = merah
    -1  => 'status--1',   // Pending = oranye
    default => 'status-0'
};
        
        $jobId = intval($row['job']);
        $jobName = isset($jobNames[$jobId]) ? $jobNames[$jobId] : 'None';
        
        $jobId2 = intval($row['job2']);
        $jobName2 = isset($jobNames[$jobId2]) ? $jobNames[$jobId2] : 'None';
      ?>
      <div class="card">
        <div class="character-info">
          <img src="<?= $skinUrl ?>" alt="Skin <?= $skinId ?>" class="skin-preview" onerror="this.src='https://assets.open.mp/assets/images/skins/0.png'">
          <div>
            <h3><?= htmlspecialchars($row['username']) ?></h3>
            <ul>
              <li><strong>Level:</strong> <?= $row['level'] ?></li>
              <li><strong>Uang:</strong> <strong style="color: green;">$<?= number_format($row['money2'], 0, ',', '.') ?></strong></li>
              <li><strong>Bank:</strong> <strong style="color: green;">$<?= number_format($row['bmoney2'], 0, ',', '.') ?></strong></li>
              <li><strong>Pekerjaan 1:</strong> <?= htmlspecialchars($jobName) ?></li>
              <li><strong>Pekerjaan 2:</strong> <?= htmlspecialchars($jobName2) ?></li>
              <li>
              <strong>Character Story:</strong>
              <span class="<?= $statusColor ?>">
                <?= htmlspecialchars($characterstory) ?>
              </span>
            </li>
              </ul>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>Tidak ada karakter ditemukan untuk akun ini.</p>
  <?php endif; ?>
</main>

<!-- Script -->
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

  lucide.createIcons(); // render semua ikon Lucide
</script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>