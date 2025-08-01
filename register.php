<?php
require 'mysql/connect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer via Composer

// --- Configuration ---
// It's highly recommended to store these in a separate config file
// and NOT commit them to version control.
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'asahavey9@gmail.com'); // CHANGE THIS
define('SMTP_PASSWORD', 'tprprfpkwaefeurs'); // CHANGE THIS - USE APP-SPECIFIC PASSWORD FOR GMAIL
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

define('APP_NAME', 'Artapura Roleplay');
define('VERIFICATION_BASE_URL', 'http://localhost:8080/verify.php'); // CHANGE THIS TO YOUR ACTUAL DOMAIN

$pesan = '';
$message_type = ''; // 'success' or 'error'

// Establish database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ucp = trim($_POST['ucp']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    // Input validation
    if (empty($ucp) || empty($email) || empty($password) || empty($confirm)) {
        $pesan = "Semua field harus diisi.";
        $message_type = 'error';
    } elseif ($password !== $confirm) {
        $pesan = "Konfirmasi password tidak cocok!";
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = "Format email tidak valid.";
        $message_type = 'error';
    } else {
        // Check if UCP or Email already exists
        $check = $conn->prepare("SELECT * FROM playerucp WHERE ucp = ? OR email = ?");
        $check->bind_param("ss", $ucp, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $existingUser = $result->fetch_assoc();
            if ($existingUser['ucp'] === $ucp) {
                $pesan = "Nama pengguna (UCP) sudah digunakan!";
            } else {
                $pesan = "Email sudah digunakan!";
            }
            $message_type = 'error';
        } else {
            // Hash password securely
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $otp = rand(100000, 999999); // Generate OTP

            // Insert new user into database
            // Set DiscordID, verified, reset_token, reset_expiration, phone to their default/NULL values
            $stmt = $conn->prepare("INSERT INTO playerucp (ucp, email, password, salt, verifycode, DiscordID, verified, reset_token, reset_expiration, phone) VALUES (?, ?, ?, ?, ?, NULL, 0, NULL, NULL, NULL)");
            
            // Note: password_hash() handles salting internally, so the 'salt' column might not be strictly necessary
            // if you only use password_hash(). If you need a separate salt for other legacy reasons, generate one.
            // For now, I'll pass an empty string for salt as it's not used by password_hash().
            $empty_salt = ''; // Placeholder for salt column if it must exist
            $stmt->bind_param("ssssi", $ucp, $email, $hashedPassword, $empty_salt, $otp);

            if ($stmt->execute()) {
                // Send verification email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = SMTP_HOST;
                    $mail->SMTPAuth = true;
                    $mail->Username = SMTP_USERNAME;
                    $mail->Password = SMTP_PASSWORD;
                    $mail->SMTPSecure = SMTP_SECURE;
                    $mail->Port = SMTP_PORT;
                    
                    // Remove these if connecting to a standard SMTP server like Gmail
                    // $mail->SMTPOptions = [
                    //     'ssl' => [
                    //         'verify_peer' => false,
                    //         'verify_peer_name' => false,
                    //         'allow_self_signed' => true
                    //     ]
                    // ];

                    $mail->setFrom(SMTP_USERNAME, APP_NAME); // Use your authenticated email as 'from'
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Verifikasi Akun Anda - ' . APP_NAME;

                    $verificationUrl = VERIFICATION_BASE_URL . "?email=" . urlencode($email);

                    $mail->Body = "
                      <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                        <h2 style='color: #FFCE1B;'>". APP_NAME ." - Verifikasi Akun</h2>
                        <p>Halo <strong>" . htmlspecialchars($ucp) . "</strong>,</p>
                        <p>Terima kasih telah mendaftar di <strong>". APP_NAME ."</strong>. Silakan gunakan kode berikut untuk memverifikasi akun Anda:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                          <span style='font-size: 28px; font-weight: bold; color: #333; background: #f5f5f5; padding: 15px 30px; border-radius: 8px; display: inline-block; letter-spacing: 4px;'>". htmlspecialchars($otp) ."</span>
                        </div>
                        <p style='text-align: center;'>
                          <a href='" . htmlspecialchars($verificationUrl) . "' style='background-color: #FFCE1B; color: #000; text-decoration: none; padding: 12px 25px; border-radius: 8px; font-weight: bold;'>Verifikasi Sekarang</a>
                        </p>
                        <p>Jika tombol tidak bekerja, salin dan buka tautan ini di browser Anda:</p>
                        <p style='font-size: 14px; color: #555;'>". htmlspecialchars($verificationUrl) ."</p>
                        <hr style='margin: 30px 0;'>
                        <p style='font-size: 13px; color: #999;'>Email ini dikirim otomatis oleh sistem UCP ". APP_NAME .". Jangan balas email ini.</p>
                      </div>
                    ";

                    $mail->send();
                    // Redirect only on successful email send
                    header("Location: verify.php?email=" . urlencode($email));
                    exit;
                } catch (Exception $e) {
                    $pesan = "Gagal mengirim email verifikasi. Silakan coba lagi nanti.";
                    $message_type = 'error';
                    error_log("Email sending failed: " . $mail->ErrorInfo . " for " . $email); // Log actual error
                }
            } else {
                $pesan = "Gagal mendaftar. Silakan coba lagi nanti.";
                $message_type = 'error';
                error_log("Database insert failed: " . $stmt->error); // Log actual error
            }
        }
        $check->close();
    }
}
$conn->close(); // Close connection
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Basic styling for demonstration. Integrate with your assets/style.css */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            color: #FFCE1B;
            margin-bottom: 20px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        button[type="submit"] {
            background-color: #FFCE1B;
            color: #000;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #e0b800;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: bold;
        }
        .message.error {
            background-color: #fdd;
            color: #c00;
            border: 1px solid #c00;
        }
        .message.success {
            background-color: #dff;
            color: #080;
            border: 1px solid #080;
        }
        p {
            margin-top: 20px;
            font-size: 14px;
        }
        p a {
            color: #FFCE1B;
            text-decoration: none;
            font-weight: bold;
        }
        p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Daftar Akun Baru</h2>
    <?php if ($pesan): ?>
        <div class="message <?php echo htmlspecialchars($message_type); ?>"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="ucp" placeholder="Nama Pengguna (UCP)" value="<?= isset($_POST['ucp']) ? htmlspecialchars($_POST['ucp']) : '' ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm" placeholder="Konfirmasi Password" required>
        <button type="submit">Daftar</button>
    </form>
    <p>Sudah punya akun? <a href="index.php">Login di sini</a></p>
</div>
</body>
</html>
