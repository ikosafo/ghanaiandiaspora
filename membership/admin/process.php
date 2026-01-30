<?php
// membership/admin/process.php

// Remove script timeout limit (important for slow SMTP like Brevo on localhost)
set_time_limit(0);

require '../config.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Start session for flash messages
session_start();

// ------------------------------------------------------------------
// Main Logic Controller
// ------------------------------------------------------------------

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'danger', 'text' => 'Invalid Registration ID.'];
    header("Location: index.php");
    exit;
}

// Fetch member data
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['flash'] = ['type' => 'danger', 'text' => 'Member record not found.'];
    header("Location: index.php");
    exit;
}

$fullName   = !empty($user['full_name']) ? $user['full_name'] : ($user['first_name'] . ' ' . $user['last_name'] ?? '');
$email      = $user['email'] ?? '—';
$submitted  = date('d M Y H:i', strtotime($user['submitted_at'] ?? 'now'));
$now        = date('d M Y H:i:s');

// Admin email that should receive approval/rejection reports
$admin_verified = 'ikosafo@yahoo.com';

// --- ACTION: APPROVE ---
if ($action === 'approve') {
    
    // 1. Generate membership ID
    $yearShort    = date('y');
    $paddedId     = str_pad($user['id'], 5, '0', STR_PAD_LEFT);
    $nationality  = trim($user['nationality'] ?? 'Ghana');
    $countryCode  = $country_codes[$nationality] ?? 'GH'; // ensure $country_codes is defined in config.php
    $membershipId = "GDU-{$yearShort}{$paddedId}{$countryCode}";

    // 2. Update Database
    $update = $pdo->prepare("
        UPDATE registrations 
        SET status = 'approved', 
            membership_id = ?, 
            membership_issued_at = NOW() 
        WHERE id = ?
    ");
    $update->execute([$membershipId, $id]);

    // 3. Send Emails
    $emailSuccess = true;
    $emailError   = '';

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_username;
        $mail->Password   = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtp_port;

        $mail->Timeout    = 20;
        $mail->SMTPKeepAlive = false;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        // ───────────────────────────────────────────────
        // EMAIL 1: To the MEMBER (applicant)
        // ───────────────────────────────────────────────
        $mail->clearAllRecipients();
        $mail->setFrom($admin_email, 'GDU Europe');
        $mail->addAddress($email, $fullName);

        $mail->Subject = "Membership Approved - Your Official ID Card Sample ($membershipId)";

        $mail->isHTML(true);
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h3>Congratulations, $fullName!</h3>
                <p>Your membership application for the <strong>Ghanaian Diaspora Union in Europe</strong> has been <strong>approved</strong>.</p>
                <p><strong>Membership Number:</strong> <code>$membershipId</code></p>
                <p>Attached to this email are sample images of your official membership ID card.</p>
                <p>These are the official sample layouts every member should expect. 
                Your physical / final digital card will follow this exact design.</p>
                <p>Welcome to the community!</p>
                <hr>
                <p><small>This is an automated message. Please do not reply directly to this email.</small></p>
            </div>
        ";

        // Attach sample images for member
        $frontPath = __DIR__ . '/assets/ids/fp.png';
        $backPath  = __DIR__ . '/assets/ids/bp.png';
        if (file_exists($frontPath)) $mail->addAttachment($frontPath, 'GDU_Membership_Card_Front_Sample.png');
        if (file_exists($backPath))  $mail->addAttachment($backPath,  'GDU_Membership_Card_Back_Sample.png');

        $mail->send();

        // ───────────────────────────────────────────────
        // EMAIL 2: Report to ADMIN
        // ───────────────────────────────────────────────
        $mail->clearAllRecipients();
        $mail->addAddress($admin_verified, 'Admin');

        $mail->Subject = "[GDU Admin] Membership Approved: $fullName ($membershipId)";

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h3>Membership Approval Report</h3>
                <p><strong>Action:</strong> APPROVED</p>
                <p><strong>Member:</strong> $fullName</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Membership ID:</strong> <code>$membershipId</code></p>
                <p><strong>Nationality:</strong> " . htmlspecialchars($user['nationality'] ?? '—') . "</p>
                <p><strong>Submitted:</strong> $submitted</p>
                <p><strong>Approved at:</strong> $now</p>
                <hr>
                <p><small>This is an automated admin notification.</small></p>
            </div>
        ";

        $mail->send();

    } catch (Exception $e) {
        $emailSuccess = false;
        $emailError   = $mail->ErrorInfo;
    }

    if ($emailSuccess) {
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Member approved and emails sent successfully.'];
    } else {
        $_SESSION['flash'] = ['type' => 'warning', 'text' => 'Member approved, but email(s) failed or timed out: ' . htmlspecialchars($emailError) . '<br>Please check SMTP settings or try again later.'];
    }

    header("Location: index.php");
    exit;


// --- ACTION: REJECT ---
} elseif ($action === 'reject') {

    $reason = $_POST['reason'] ?? 'Documentation provided was insufficient or incomplete.';

    // 1. Update Database
    $update = $pdo->prepare("
        UPDATE registrations 
        SET status = 'rejected', 
            membership_id = NULL, 
            rejection_reason = ? 
        WHERE id = ?
    ");
    $update->execute([$reason, $id]);

    // 2. Send Emails
    $emailSuccess = true;
    $emailError   = '';

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_username;
        $mail->Password   = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtp_port;

        $mail->Timeout    = 20;
        $mail->SMTPKeepAlive = false;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        // ───────────────────────────────────────────────
        // EMAIL 1: To the MEMBER (rejection notice)
        // ───────────────────────────────────────────────
        $mail->clearAllRecipients();
        $mail->setFrom($admin_email, 'GDU Europe');
        $mail->addAddress($email, $fullName);

        $mail->Subject = "Update regarding your GDU Membership Application";

        $mail->isHTML(true);
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h3>Hello $fullName,</h3>
                <p>Thank you for your interest in the Ghanaian Diaspora Union in Europe.</p>
                <p>After reviewing your application, we regret to inform you that it has been <strong>rejected</strong> for the following reason:</p>
                <blockquote style='background: #f9f9f9; padding: 15px; border-left: 5px solid #ef4444;'>
                    " . htmlspecialchars($reason) . "
                </blockquote>
                <p>If you believe this is an error or wish to re-apply with corrected information, please contact our secretariat.</p>
            </div>
        ";

        $mail->send();

        // ───────────────────────────────────────────────
        // EMAIL 2: Report to ADMIN
        // ───────────────────────────────────────────────
        $mail->clearAllRecipients();
        $mail->addAddress($admin_verified, 'Admin');

        $mail->Subject = "[GDU Admin] Membership Rejected: $fullName";

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h3>Membership Rejection Report</h3>
                <p><strong>Action:</strong> REJECTED</p>
                <p><strong>Member:</strong> $fullName</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Submitted:</strong> $submitted</p>
                <p><strong>Rejected at:</strong> $now</p>
                <p><strong>Reason:</strong></p>
                <blockquote style='background: #fee2e2; padding: 12px; border-left: 4px solid #ef4444;'>
                    " . htmlspecialchars($reason) . "
                </blockquote>
                <hr>
                <p><small>This is an automated admin notification.</small></p>
            </div>
        ";

        $mail->send();

    } catch (Exception $e) {
        $emailSuccess = false;
        $emailError   = $mail->ErrorInfo;
    }

    if ($emailSuccess) {
        $_SESSION['flash'] = ['type' => 'warning', 'text' => 'Member rejected and emails sent successfully.'];
    } else {
        $_SESSION['flash'] = ['type' => 'warning', 'text' => 'Member rejected, but email(s) failed or timed out: ' . htmlspecialchars($emailError) . '<br>Action completed in database.'];
    }

    header("Location: index.php");
    exit;

} else {
    $_SESSION['flash'] = ['type' => 'danger', 'text' => 'Invalid action request.'];
    header("Location: index.php");
    exit;
}