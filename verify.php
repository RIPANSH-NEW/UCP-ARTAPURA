<?php
require 'mysql/connect.php';

$pesan = '';
$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    $stmt = $conn->prepare("SELECT * FROM playerucp WHERE email = ? AND verifycode = ?");
    $stmt->bind_param("si", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $conn->query("UPDATE playerucp SET verified = 1 WHERE email = '$email'");
        $pesan = "Verifikasi berhasil! Silakan <a href='index.php'>login</a>.";
    } else {
        $pesan = "Kode verifikasi salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Email</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h2>Verifikasi Email</h2>
    <?php if ($pesan): ?>
        <div class="info"><?= $pesan ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input type="text" name="otp" placeholder="Masukkan kode OTP" required>
        <button type="submit">Verifikasi</button>
    </form>
</div>
</body>
</html>