<?php
session_start();
require 'mysql/connect.php';

    if (isset($_SESSION['ucp'])) {
        header("Location: dashboard.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $password_input = $_POST['password'];

        if (empty($username) || empty($password_input)) {
            $pesan = "Nama pengguna dan kata sandi tidak boleh kosong!";
        } else {
            $sql = "SELECT ucp, password, salt, extrac, aloginpin FROM playerucp WHERE LOWER(ucp) = LOWER(?) LIMIT 1";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                error_log("Prepare gagal: " . $conn->error);
                $pesan = "Terjadi kesalahan sistem. Silakan coba lagi.";
            } else {
                $stmt->bind_param("s", $username);

                if (!$stmt->execute()) {
                    error_log("Eksekusi gagal: " . $stmt->error);
                    $pesan = "Terjadi kesalahan sistem. Silakan coba lagi.";
                } else {
                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {
                        $row = $result->fetch_assoc();
                        $salt = $row['salt'];
                        $hashed_password = strtoupper(hash("sha256", $password_input . $salt));

                        if ($hashed_password === $row['password']) {
                            if ((int)$row['extrac'] === 1) {
                                $pesan = "Akun ini telah dibanned.";
                            } else {
                                session_regenerate_id(true);
                                $_SESSION['ucp'] = $row['ucp'];
                                header("Location: dashboard.php");
                                exit();
                            }
                        } else {
                            $pesan = "Nama pengguna atau kata sandi salah!";
                        }
                    } else {
                        $pesan = "Nama pengguna atau kata sandi salah!";
                    }
                }

                $stmt->close();
            }
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Artapura RP</title>
    <link rel="stylesheet" href="./assets/style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($pesan)): ?>
            <div class="error"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <input type="text" name="username" placeholder="UCP" required aria-label="Nama Pengguna">
            <input type="password" name="password" placeholder="Password" required aria-label="Kata Sandi">
            <button type="submit">Login</button>
        </form>
        <p>Belum punya akun? <a href="register.html">Daftar</a></p>
    </div>
</body>
</html>