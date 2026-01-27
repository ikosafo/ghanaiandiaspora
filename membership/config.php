<?php
session_start();

// Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); 
define('DB_PASS', 'root');
define('DB_NAME', 'ghanfhwj_gha');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// ======================================
// MANUAL PHPMailer
// ======================================
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ======================================
// MANUAL QR Code - bacon/bacon-qr-code (GD PNG output)
// ======================================
/* require __DIR__ . '/BaconQrCode/src/Common/Mode.php';
require __DIR__ . '/BaconQrCode/src/Common/ErrorCorrectionLevel.php';
require __DIR__ . '/BaconQrCode/src/Common/Version.php';
require __DIR__ . '/BaconQrCode/src/Writer.php';
require __DIR__ . '/BaconQrCode/src/Renderer/GDLibRenderer.php';

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
 */
// Brevo SMTP
$smtp_host     = 'smtp-relay.brevo.com';
$smtp_port     = 587;
$smtp_username = 'a0d2f9001@smtp-brevo.com';
$smtp_password = 'xsmtpsib-6540a70a4001d954a570023fe9f99bd1e4fdd55a217bf9506f2946524bab79b4-cC5akmfkNvRXgta4';
$admin_email   = 'ikosafo@gmail.com'; 

// Admin credentials (change password; in production, hash it)
$admin_username = 'admin';
$admin_password = 'super_secure_password_123'; // CHANGE THIS!

// Country codes (add more if needed)
$country_codes = [
    'United States' => 'US',
    'Ghana' => 'GH',
    'United Kingdom' => 'UK',
    'Canada' => 'CA',
];