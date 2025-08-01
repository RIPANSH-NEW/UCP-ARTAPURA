<?php
session_start();
require 'mysql/connect.php';


if (!isset($_SESSION['ucp'])) {
  header("Location: index.php");
  exit;
}

$ucp = $_SESSION['ucp'];


$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $character_name = $_POST['nama_ic'] ?? '';
  $story = $_POST['cerita_ck'] ?? '';

  if (empty($character_name) || empty($story)) {
    $error = "Semua kolom wajib diisi.";
  } else {
    // Cek karakter milik UCP
    $stmtCheck = $conn->prepare("SELECT * FROM players WHERE username = ? AND ucp = ?");
    $stmtCheck->bind_param("ss", $character_name, $ucp);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows == 0) {
      $error = "Karakter tidak ditemukan atau bukan milik Anda.";
    } else {
      // Cek apakah sudah pernah request dan masih pending
      $stmtDuplicate = $conn->prepare("SELECT id FROM ck_requests WHERE ucp = ? AND character_name = ? AND status = 'pending'");
      $stmtDuplicate->bind_param("ss", $ucp, $character_name);
      $stmtDuplicate->execute();
      $resultDuplicate = $stmtDuplicate->get_result();

      if ($resultDuplicate->num_rows > 0) {
        $error = "Anda sudah mengajukan request CK untuk karakter ini dan masih dalam proses.";
      } else {
        $stmt = $conn->prepare("INSERT INTO ck_requests (ucp, character_name, story) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $ucp, $character_name, $story);
        if ($stmt->execute()) {
          $success = "Permintaan CK berhasil dikirim.";
        } else {
          $error = "Gagal mengirim permintaan.";
        }
        $stmt->close();
      }

      $stmtDuplicate->close();
    }

    $stmtCheck->close();
  }
}

// Ambil semua karakter milik UCP
$stmtChar = $conn->prepare("SELECT username FROM players WHERE ucp = ?");
$stmtChar->bind_param("s", $ucp);
$stmtChar->execute();
$resultChar = $stmtChar->get_result();
$characters = [];
while ($row = $resultChar->fetch_assoc()) {
  $characters[] = $row['username'];
}
$stmtChar->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Request CK - Artapura RP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/dashboard.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<!-- Tombol Menu -->
<button id="menuToggle" aria-label="Toggle Menu">☰ Request CK</button>

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
  <div class="card">
    <h2><i data-lucide="skull"></i> Request Character Kill (CK)</h2>
    <p>Gunakan form ini untuk mengajukan penghapusan karakter secara permanen (CK).</p>

    <?php if ($success): ?>
      <div class="success"><?= $success ?></div>
    <?php elseif ($error): ?>
      <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form class="form" method="POST">
      <label for="nama_ic">Pilih Nama IC</label>
      <select id="nama_ic" name="nama_ic" required>
        <option value="">-- Pilih Karakter --</option>
        <?php foreach ($characters as $char): ?>
          <option value="<?= htmlspecialchars($char) ?>"><?= htmlspecialchars($char) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="cerita_ck">Tuliskan Cerita CK Anda</label>
      <textarea id="cerita_ck" name="cerita_ck" rows="6" placeholder="Tuliskan alasan dan kronologi CK secara lengkap..." required></textarea>

      <button type="submit">Kirim Permintaan</button>
    </form>
  </div>
</main>

<!-- Script -->
<script>
  const toggle = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');

  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    toggle.classList.toggle('active');
  });

  document.addEventListener('click', (e) => {
    if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
      sidebar.classList.remove('active');
      toggle.classList.remove('active');
    }
  });

  lucide.createIcons();
</script>

</body>
</html>