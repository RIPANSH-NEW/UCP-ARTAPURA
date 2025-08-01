<?php
session_start();
require 'mysql/connect.php';

// Validasi login dan hak akses admin
if (
    !isset($_SESSION['ucp']) ||
    $_SESSION['aloginpin'] === "0"
) {
    header("Location: dashboard.php");
    exit;
}

$ucp = $_SESSION['ucp'];

// Ambil data request CK & CS
$ck_result = $conn->query("SELECT * FROM ck_requests ORDER BY id DESC");
$cs_result = $conn->query("SELECT * FROM cs_requests ORDER BY id DESC");

// Ambil semua karakter
$char_result = $conn->query("SELECT * FROM players ORDER BY reg_id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Artapura RP</title>
  <link rel="stylesheet" href="../assets/dashboard.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .story-preview {
      max-height: 100px;
      overflow: hidden;
      cursor: pointer;
    }
    .modal {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }
    .modal-content {
      background: white;
      padding: 20px;
      width: 80%;
      max-width: 600px;
      border-radius: 8px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
      position: relative;
    }
    .close {
      position: absolute;
      top: 10px; right: 15px;
      font-size: 24px;
      cursor: pointer;
    }
    button {
      background-color: #FFCE1B;
      border: none;
      padding: 6px 12px;
      margin: 2px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
      color: black;
      transition: background-color 0.2s ease-in-out;
    }
    button:hover {
      background-color: #e6b900;
    }
    button.reject {
      background-color: #ff4d4d;
      color: white;
    }
    button.reject:hover {
      background-color: #cc0000;
    }
  </style>
</head>
<body>

<!-- Tombol toggle sidebar -->
<button id="menuToggle">☰<i data-lucide="user"></i>Karakter Saya</button>

<!-- Sidebar Navigasi -->
<aside class="sidebar" id="sidebar">
  <h1 class="logo">Artapura RP</h1>
  <nav>
    <ul>
      <li><a href="dashboard.php"><i data-lucide="home"></i> Beranda</a></li>
      <li><a href="karakter.php"><i data-lucide="user"></i> Karakter</a></li>
      <li><a href="request-ck.php"><i data-lucide="skull"></i> Request CK</a></li>
      <li><a href="request-cs.php"><i data-lucide="theater"></i> Request CS</a></li>
      <li><a href="admin.php" class="active"><i data-lucide="shield"></i> Admin Panel</a></li>
      <li><a href="logout.php" class="logout"><i data-lucide="log-out"></i> Logout</a></li>
    </ul>
  </nav>
</aside>

<!-- Konten -->
<div class="content">
  <div class="card">
    <h2>Selamat datang, <?= htmlspecialchars($ucp) ?>!</h2>
    <p>Gunakan menu untuk mengelola karakter dan akun Anda.</p>
  </div>

  <!-- CK -->
  <h1>Request Character Kill (CK)</h1>
  <div class="card">
    <?php if ($ck_result && $ck_result->num_rows > 0): ?>
      <table style="width:100%; border-collapse: collapse;">
        <tr>
          <th>UCP</th>
          <th>Karakter</th>
          <th>Story</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
        <?php while ($row = $ck_result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['ucp']) ?></td>
          <td><?= htmlspecialchars($row['character_name']) ?></td>
          <td>
            <div class="story-preview" onclick="showFullStory(`<?= htmlspecialchars(addslashes($row['story'])) ?>`)">
              <?= nl2br(htmlspecialchars(substr($row['story'], 0, 100))) ?>...
            </div>
          </td>
          <td><?= htmlspecialchars($row['status']) ?></td>
          <td>
            <form method="post" action="acc_ck.php" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit">Acc</button>
            </form>
            <form method="post" action="reject_ck.php" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button class="reject" type="submit">Tolak</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>Tidak ada request CK.</p>
    <?php endif; ?>
  </div>

  <!-- CS -->
  <h1>Request Character Story (CS)</h1>
  <div class="card">
    <?php if ($cs_result && $cs_result->num_rows > 0): ?>
      <table style="width:100%; border-collapse: collapse;">
        <tr>
          <th>UCP</th>
          <th>Karakter</th>
          <th>Story</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
        <?php while ($row = $cs_result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['ucp']) ?></td>
          <td><?= htmlspecialchars($row['character_name']) ?></td>
          <td>
            <div class="story-preview" onclick="showFullStory(`<?= htmlspecialchars(addslashes($row['story'])) ?>`)">
              <?= nl2br(htmlspecialchars(substr($row['story'], 0, 100))) ?>...
            </div>
          </td>
          <td><?= htmlspecialchars($row['status']) ?></td>
          <td>
            <form method="post" action="acc_cs.php" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit">Acc</button>
            </form>
            <form method="post" action="reject_cs.php" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button class="reject" type="submit">Tolak</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>Tidak ada request CS.</p>
    <?php endif; ?>
  </div>

  <!-- Daftar Karakter -->
  <h1>List Karakter</h1>
  <div class="card">
    <?php if ($char_result && $char_result->num_rows > 0): ?>
      <table style="width:100%; border-collapse: collapse;">
        <tr>
          <th>No</th>
          <th>UCP</th>
          <th>Karakter</th>
          <th>Uang</th>
          <th>Level</th>
        </tr>
        <?php 
        $no = 1;
        while ($char = $char_result->fetch_assoc()): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($char['ucp']) ?></td>
          <td><?= htmlspecialchars($char['username']) ?></td>
          <td><?= $char['money2'] ?></td>
          <td><?= $char['level'] ?></td>
        </tr>
        <?php endwhile; ?>
      </table>
    <?php else: ?>
      <p>Tidak ada karakter ditemukan.</p>
    <?php endif; ?>
  </div>
</div>

<!-- Modal untuk full story -->
<div id="storyModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>Full Story</h3>
    <div id="storyContent" style="white-space: pre-wrap; max-height:400px; overflow-y:auto;"></div>
  </div>
</div>

<script>
function showFullStory(text) {
  document.getElementById('storyContent').innerText = text;
  document.getElementById('storyModal').style.display = 'flex';
}
function closeModal() {
  document.getElementById('storyModal').style.display = 'none';
}
</script>

</body>
</html>