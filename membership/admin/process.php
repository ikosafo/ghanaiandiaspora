<?php
require '../config.php';

// Security: only logged-in admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if ($id <= 0 || !in_array($action, ['approve', 'reject'])) {
    die("Invalid request");
}

// Fetch the registration record
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Record not found");
}

$applicant_email = $row['email'];
$applicant_name  = $row['full_name'];

// Prepare PHPMailer
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = $smtp_host;
$mail->SMTPAuth   = true;
$mail->Username   = $smtp_username;
$mail->Password   = $smtp_password;
$mail->SMTPSecure = 'tls';
$mail->Port       = $smtp_port;
$mail->setFrom($admin_email, 'GDU Membership Admin');
$mail->addAddress($applicant_email, $applicant_name);
$mail->isHTML(true);

if ($action === 'approve') {
    // Generate membership ID: GDU + YEAR + 4-digit sequential + country code
    $year = date('Y');
    $country = $row['nationality'];
    $code = $country_codes[$country] ?? 'XX';

    // Get next number for this year
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE membership_id LIKE ?");
    $countStmt->execute(["GDU$year%"]);
    $count = $countStmt->fetchColumn() + 1;
    $seq = str_pad($count, 4, '0', STR_PAD_LEFT);

    $mem_id = "GDU{$year}{$seq}{$code}";

    // Generate QR code using bacon-qr-code + GD
    $renderer = new GDLibRenderer(300); // 300x300 pixels
    $writer = new Writer($renderer);

    $qr_path = __DIR__ . '/../qrcards/' . $mem_id . '.png';

    // Write QR code to file
    $writer->writeFile($mem_id, $qr_path);

    // Update database
    $updateStmt = $pdo->prepare("
        UPDATE registrations 
        SET status = 'approved', 
            membership_id = ?, 
            membership_qr_path = ? 
        WHERE id = ?
    ");
    $updateStmt->execute([$mem_id, "qrcards/{$mem_id}.png", $id]);

    // Send approval email with embedded QR
    $mail->Subject = 'GDU Membership Approved';
    $mail->Body = "
        <h2>Congratulations, {$applicant_name}!</h2>
        <p>Your membership application has been <strong>approved</strong>.</p>
        <p><strong>Membership ID:</strong> <b>{$mem_id}</b></p>
        <p><img src='cid:qr_code' alt='Membership QR Code' style='max-width:250px;'></p>
        <p>Keep this QR code safe for verification purposes.</p>
        <p>Thank you for joining GDU!</p>
    ";
    $mail->addEmbeddedImage($qr_path, 'qr_code', 'membership-qr.png');

} elseif ($action === 'reject') {
    $reason = trim($_POST['reason'] ?? 'Not specified');

    // Update database
    $updateStmt = $pdo->prepare("
        UPDATE registrations 
        SET status = 'rejected', 
            rejection_reason = ? 
        WHERE id = ?
    ");
    $updateStmt->execute([$reason, $id]);

    // Send rejection email
    $mail->Subject = 'GDU Membership Application Rejected';
    $mail->Body = "
        <h2>Dear {$applicant_name},</h2>
        <p>Your membership application has been <strong>rejected</strong>.</p>
        <p><strong>Reason:</strong> " . nl2br(htmlspecialchars($reason)) . "</p>
        <p>If you believe this is a mistake or have questions, please reply to this email.</p>
        <p>Best regards,<br>GDU Team</p>
    ";
}

try {
    $mail->send();
    $status_message = ($action === 'approve') ? "Approved and email sent" : "Rejected and email sent";
} catch (Exception $e) {
    $status_message = "Action completed but email failed: " . $mail->ErrorInfo;
}

// Redirect back to dashboard with message
header("Location: dashboard.php?msg=" . urlencode($status_message));
exit;