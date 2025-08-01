<?php
session_start();
$conn = new mysqli("127.0.0.1", "root", "root", "artapura_rp");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
  $id = intval($_POST['id']);
  $stmt = $conn->prepare("UPDATE cs_requests SET status = 'Ditolak' WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
}

header("Location: admin.php");
exit;
?>