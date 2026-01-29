<?php
// membership/admin/process.php

require '../config.php';
require_once '../vendor/autoload.php';

use TCPDF;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Generates the Membership PDF Card
 */
function generateMembershipCardPDF($member, $memId) {
    // ID-1 Standard Size (85.6mm x 53.98mm)
    $pdf = new TCPDF('L', 'mm', [85.6, 53.98], true, 'UTF-8', false);
    
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Assets
    $flagUrl      = 'https://img.freepik.com/premium-photo/flag-ghana-textured-fabric_134398-1416.jpg?semt=ais_hybrid&w=740&q=80';
    $logoUrl      = 'https://www.ghanaiandiaspora.org/wp-content/uploads/2025/04/GDUE-Logo-New-e1744094935301.png';
    $coatOfArms   = 'https://thumbs.peoplesgdarchive.org/static/media-items/image/29685/upto-1440x667/65e14fc4/1/image.png';
    $defaultPhoto = 'https://static.vecteezy.com/system/resources/thumbnails/052/239/909/small/serious-bald-businessman-in-black-suit-with-red-tie-on-transparent-background-png.png';

    $photoUrl = !empty($member['photo_url']) ? $member['photo_url'] : $defaultPhoto;

    // --- FRONT SIDE ---
    $pdf->AddPage();
    
    // Background
    $pdf->SetFillColor(250, 250, 250);
    $pdf->Rect(0, 0, 85.6, 53.98, 'F');

    // Watermarks
    $pdf->SetAlpha(0.06);
    $pdf->Image($coatOfArms, 22, 12, 40, 30, 'PNG');
    $pdf->SetAlpha(1.0);

    // 1. Header (Increased height and lighter red/brand color)
    $pdf->SetFillColor(220, 30, 45); // A slightly lighter, more vibrant red
    $pdf->Rect(0, 0, 85.6, 11, 'F'); // Increased height to 11mm

    // Logo (Increased width and height for lengthy text)
    $pdf->Image($logoUrl, 1.5, 1, 14, 9, 'PNG'); // Width 14mm, Height 9mm

    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetXY(16, 2.5);
    $pdf->Cell(0, 4, 'GHANAIAN DIASPORA UNION', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetXY(16, 5.8);
    $pdf->Cell(0, 4, 'IN EUROPE', 0, 1, 'L');

    // Small Ghana Flag in header
    $pdf->Image($flagUrl, 72, 2.5, 10, 6, 'JPG');

    // 2. Photo Area (Right Side)
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Rect(57, 14, 25, 31, 'DF'); // Professional Border
    $pdf->Image($photoUrl, 58, 15, 23, 29, '', '', '', false, 300, '', false, false, 0, true);

    // 3. Information Layout (Vertical Stack)
    $pdf->SetTextColor(0, 0, 0);
    $leftCol = 4;
    $currY = 13.5;

    // Helper to draw vertical labels
    $fields = [
        ['SURNAME', strtoupper($member['last_name'] ?? 'DOE'), 7],
        ['FIRST NAMES', strtoupper($member['first_name'] ?? 'JOHN KWAME'), 7],
    ];

    foreach($fields as $f) {
        $pdf->SetFont('helvetica', 'B', 4.5);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY($leftCol, $currY);
        $pdf->Cell(40, 3, $f[0], 0, 1);
        $currY += 2.5;
        
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($leftCol, $currY);
        $pdf->Cell(40, 4, $f[1], 0, 1);
        $currY += 5;
    }

    // Membership Bar
    $pdf->SetFillColor(235, 235, 245);
    $pdf->Rect($leftCol, $currY, 50, 7.5, 'F');
    $pdf->SetFont('helvetica', 'B', 4.5);
    $pdf->SetXY($leftCol + 1, $currY + 0.5);
    $pdf->Cell(30, 3, 'MEMBERSHIP NO. (PIN)', 0, 0);
    $pdf->SetFont('courier', 'B', 9);
    $pdf->SetXY($leftCol + 1, $currY + 3);
    $pdf->Cell(48, 4, $memId, 0, 1);
    $currY += 8.5;

    // Small Details Stacked
    $smallFields = [
        ['SEX', strtoupper($member['gender'] ?? 'MALE')],
        ['DATE OF BIRTH', $member['date_of_birth'] ?? '1985-11-30'],
        ['NATIONALITY', strtoupper($member['nationality'] ?? 'UNITED KINGDOM')]
    ];

    foreach($smallFields as $sf) {
        $pdf->SetFont('helvetica', 'B', 4.5);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY($leftCol, $currY);
        $pdf->Cell(20, 3, $sf[0], 0, 0);
        
        $pdf->SetFont('helvetica', 'B', 6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($leftCol + 18, $currY);
        $pdf->Cell(30, 3, $sf[1], 0, 1);
        $currY += 3.2;
    }

    // Horizontal Row for Dates (Bottom)
    $pdf->SetFont('helvetica', 'B', 4.5);
    $pdf->SetTextColor(100, 100, 100);
    
    $issueDate = date('d.m.Y');
    $expiryDate = date('d.m.Y', strtotime('+5 years'));

    $pdf->SetXY($leftCol, 46);
    $pdf->Cell(20, 3, 'DATE OF ISSUE', 0, 0);
    $pdf->SetXY($leftCol + 25, 46);
    $pdf->Cell(20, 3, 'EXPIRY DATE', 0, 0);

    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($leftCol, 48.5);
    $pdf->Cell(20, 3, $issueDate, 0, 0);
    $pdf->SetXY($leftCol + 25, 48.5);
    $pdf->Cell(20, 3, $expiryDate, 0, 0);

    // Footer Micro-text
    $pdf->SetFont('helvetica', '', 3.5);
    $pdf->SetXY(0, 52);
    $pdf->Cell(85.6, 2, str_repeat('GHANAIAN DIASPORA UNION IN EUROPE • ', 6), 0, 0, 'C');

    // --- BACK SIDE (Keep as is, but adjusted colors to match) ---
    $pdf->AddPage();
    $pdf->SetFillColor(245, 245, 245);
    $pdf->Rect(0, 0, 85.6, 53.98, 'F');
    // ... [Rest of your back-side code stays the same] ...
    
    $tempFile = sys_get_temp_dir() . '/gdu_card_' . str_replace(['-', ' '], '_', $memId) . '.pdf';
    $pdf->Output($tempFile, 'F');
    return $tempFile;
}

// ------------------------------------------------------------------
// Main Logic Controller
// ------------------------------------------------------------------

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($id <= 0) {
    die("Error: Invalid Registration ID.");
}

// Fetch member data
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Error: Member record not found.");
}

$fullName = !empty($user['full_name']) ? $user['full_name'] : ($user['first_name'] . ' ' . $user['last_name']);

// --- ACTION: APPROVE ---
if ($action === 'approve') {
    
    // 1. Generate membership ID
    $yearShort    = date('y');
    $paddedId     = str_pad($user['id'], 5, '0', STR_PAD_LEFT);
    $nationality  = trim($user['nationality'] ?? 'Ghana');
    $countryCode  = $country_codes[$nationality] ?? 'GH';
    $membershipId = "GDU-{$yearShort}{$paddedId}{$countryCode}";

    // 2. Update Database
    $update = $pdo->prepare("UPDATE registrations SET status = 'approved', membership_id = ?, membership_issued_at = NOW() WHERE id = ?");
    $update->execute([$membershipId, $id]);

    // 3. Create PDF
    $pdfPath = generateMembershipCardPDF($user, $membershipId);

    // 4. Send Email
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_username;
        $mail->Password   = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtp_port;

        $mail->setFrom($admin_email, 'GDU Europe');
        $mail->addAddress($user['email'], $fullName);
        $mail->Subject = "Membership Approved – Your Official ID Card ($membershipId)";
        $mail->isHTML(true);
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h3>Congratulations, $fullName!</h3>
                <p>Your membership application for the <strong>Ghanaian Diaspora Union in Europe</strong> has been approved.</p>
                <p><strong>Membership Number:</strong> <code>$membershipId</code></p>
                <p>Please find your official digital membership card attached. This card is valid for 5 years from today.</p>
                <p>Welcome to the community!</p>
                <hr>
                <p><small>This is an automated message. Please do not reply directly to this email.</small></p>
            </div>
        ";

        if (file_exists($pdfPath)) {
            $mail->addAttachment($pdfPath, "GDU_Membership_Card_$membershipId.pdf");
        }

        $mail->send();
        @unlink($pdfPath); // Clean up temp file

        header("Location: index.php?msg=success&text=Member+approved+and+card+sent");
        exit;
    } catch (Exception $e) {
        die("Approval successful, but email failed. Mailer Error: " . $mail->ErrorInfo);
    }

// --- ACTION: REJECT ---
} elseif ($action === 'reject') {
    $reason = $_POST['reason'] ?? 'Documentation provided was insufficient or incomplete.';

    // 1. Update Database
    $update = $pdo->prepare("UPDATE registrations SET status = 'rejected', rejection_reason = ? WHERE id = ?");
    $update->execute([$reason, $id]);

    // 2. Inform User via Email
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_username;
        $mail->Password   = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtp_port;

        $mail->setFrom($admin_email, 'GDU Europe');
        $mail->addAddress($user['email'], $fullName);
        $mail->Subject = "Update regarding your GDU Membership Application";
        $mail->isHTML(true);
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h3>Hello $fullName,</h3>
                <p>Thank you for your interest in the Ghanaian Diaspora Union in Europe.</p>
                <p>After reviewing your application, we regret to inform you that it has been <strong>rejected</strong> for the following reason:</p>
                <blockquote style='background: #f9f9f9; padding: 15px; border-left: 5px solid #ef4444;'>
                    $reason
                </blockquote>
                <p>If you believe this is an error or wish to re-apply with corrected information, please contact our secretariat.</p>
            </div>
        ";

        $mail->send();
        header("Location: index.php?msg=warning&text=Member+rejected+and+notified");
        exit;
    } catch (Exception $e) {
        // Even if email fails, the DB is updated, so we redirect
        header("Location: index.php?msg=warning&text=Member+rejected+but+email+failed");
        exit;
    }

} else {
    die("Error: Invalid action request.");
}