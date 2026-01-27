<?php
session_start();
require '../config.php';

// Very important: load Composer's autoloader (same as in public index.php)
require_once '../vendor/autoload.php';   // ← this line is probably missing!

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id     = $_POST['id']    ?? $_GET['id']    ?? 0;
$reason = trim($_POST['reason'] ?? '');

if (!$id || !is_numeric($id)) {
    die("Invalid request");
}

// Get the current registration
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Registration not found");
}

$newStatus = '';
$emailBody = '';

if ($action === 'approve') {
    $newStatus = 'approved';
    $emailBody = "
        <h2>Your registration has been APPROVED!</h2>
        <p>Dear {$row['full_name']},</p>
        <p>Your membership application has been reviewed and approved.</p>
        <p>Welcome to the Ghana Diaspora Union!</p>
        <p>Thank you,<br>Ghana Diaspora Team</p>
    ";
} elseif ($action === 'reject') {
    if (empty($reason)) {
        die("Rejection reason is required");
    }
    $newStatus = 'rejected';
    $emailBody = "
        <h2>Your registration has been REJECTED</h2>
        <p>Dear {$row['full_name']},</p>
        <p>Unfortunately, your application was not approved.</p>
        <p><strong>Reason:</strong> " . htmlspecialchars($reason) . "</p>
        <p>If you have questions, feel free to contact us.</p>
        <p>Thank you,<br>Ghana Diaspora Team</p>
    ";
} else {
    die("Invalid action");
}

// Update status in database
$stmt = $pdo->prepare("UPDATE registrations SET status = ? WHERE id = ?");
$stmt->execute([$newStatus, $id]);

// Send email to the applicant
try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = $smtp_host;
    $mail->SMTPAuth   = true;
    $mail->AuthType   = 'PLAIN';
    $mail->Username   = $smtp_username;
    $mail->Password   = $smtp_password;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = $smtp_port;

    // From admin
    $mail->setFrom($admin_email, 'Ghana Diaspora Membership');
    $mail->addAddress($row['email'], $row['full_name']);

    $mail->isHTML(true);
    $mail->Subject = "Your Ghana Diaspora Membership Application - " . ucfirst($newStatus);
    $mail->Body    = $emailBody;

    $mail->send();

    // Redirect back with success
    header("Location: index.php?msg=" . urlencode(ucfirst($newStatus) . " successfully! Email sent."));
    exit;

} catch (Exception $e) {
    // Log error but still redirect
    error_log("Email failed in process.php: " . $mail->ErrorInfo);
    header("Location: index.php?msg=" . urlencode(ucfirst($newStatus) . " done, but email failed."));
    exit;
}