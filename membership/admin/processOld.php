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
    // Standard CR80 Size: 85.6mm x 53.98mm
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

    // --- FRONT PAGE ---
    $pdf->AddPage();
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect(0, 0, 85.6, 53.98, 'F');

    // Subtle Watermark
    $pdf->SetAlpha(0.03);
    $pdf->Image($coatOfArms, 25, 18, 35, 25, 'PNG');
    $pdf->SetAlpha(1.0);

    // LIGHT HEADER (Very pale red/pink)
    $pdf->SetFillColor(255, 242, 242);
    $pdf->Rect(0, 0, 85.6, 14, 'F'); 
    
    // Wide Logo (Left)
    $pdf->Image($logoUrl, 3, 2, 38, 10, 'PNG'); 

    // Ghana Flag (Right)
    $pdf->Image($flagUrl, 70, 3.5, 12, 7, 'JPG');

    // PROFILE PHOTO
    $pdf->Image($photoUrl, 62, 18, 20, 25, '', '', '', false, 300, '', false, false, 1, true);

    // --- DATA FIELDS (Micro-print Design) ---
    $pdf->SetTextColor(30, 30, 30);
    $lblF = 'helvetica'; $lblS = 'B'; $lblSize = 3.6; 
    $valF = 'helvetica'; $valS = '';  $valSize = 5.5; // Reduced values font size

    $currY = 16;
    $lineSpacing = 5.2;

    // 1. SURNAME
    $pdf->SetFont($lblF, $lblS, $lblSize); $pdf->SetXY(5, $currY); $pdf->Cell(40, 2, 'SURNAME', 0, 0);
    $pdf->SetFont($valF, $valS, $valSize);  $pdf->SetXY(5, $currY+1.8); $pdf->Cell(40, 4, strtoupper($member['last_name'] ?? 'DOE'), 0, 1);

    $currY += $lineSpacing;
    // 2. FIRST NAMES & SEX (Same line)
    $pdf->SetFont($lblF, $lblS, $lblSize); 
    $pdf->SetXY(5, $currY);  $pdf->Cell(35, 2, 'FIRST NAMES', 0, 0);
    $pdf->SetXY(45, $currY); $pdf->Cell(10, 2, 'SEX', 0, 0);
    
    $pdf->SetFont($valF, $valS, $valSize);
    $pdf->SetXY(5, $currY+1.8);  $pdf->Cell(35, 4, strtoupper($member['first_name'] ?? 'JOHN KWAME'), 0, 0);
    $pdf->SetXY(45, $currY+1.8); $pdf->Cell(10, 4, strtoupper($member['gender'] ?? 'M'), 0, 1);

    $currY += $lineSpacing;
    // 3. MEMBERSHIP NO. (PIN)
    $pdf->SetFont($lblF, $lblS, $lblSize); $pdf->SetXY(5, $currY); $pdf->Cell(40, 2, 'MEMBERSHIP NO. (PIN)', 0, 0);
    $pdf->SetFont('courier', 'B', 6.5);     $pdf->SetXY(5, $currY+1.8); $pdf->Cell(40, 4, $memId, 0, 1);

    $currY += $lineSpacing;
    // 4. DATE OF BIRTH
    $pdf->SetFont($lblF, $lblS, $lblSize); $pdf->SetXY(5, $currY); $pdf->Cell(40, 2, 'DATE OF BIRTH', 0, 0);
    $pdf->SetFont($valF, $valS, $valSize);  $pdf->SetXY(5, $currY+1.8); $pdf->Cell(40, 4, $member['date_of_birth'] ?? '12.10.1985', 0, 1);

    $currY += $lineSpacing;
    // 5. NATIONALITY
    $pdf->SetFont($lblF, $lblS, $lblSize); $pdf->SetXY(5, $currY); $pdf->Cell(40, 2, 'NATIONALITY', 0, 0);
    $pdf->SetFont($valF, $valS, $valSize);  $pdf->SetXY(5, $currY+1.8); $pdf->Cell(40, 4, strtoupper($member['nationality'] ?? 'GHANAIAN'), 0, 1);

    $currY += $lineSpacing;
    // 6. ISSUED & EXPIRES (Now in the vertical list)
    $issueDate  = date('d.m.Y');
    $expiryDate = date('d.m.Y', strtotime('+5 years'));

    $pdf->SetFont($lblF, $lblS, $lblSize);
    $pdf->SetXY(5, $currY);  $pdf->Cell(20, 2, 'DATE OF ISSUE', 0, 0);
    $pdf->SetXY(30, $currY); $pdf->Cell(20, 2, 'EXPIRY DATE', 0, 0);

    $pdf->SetFont($valF, $valS, $valSize);
    $pdf->SetXY(5, $currY+1.8);  $pdf->Cell(20, 4, $issueDate, 0, 0);
    $pdf->SetXY(30, $currY+1.8); $pdf->Cell(20, 4, $expiryDate, 0, 0);

    // Ultra-thin accent line at the bottom
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Rect(0, 53.5, 85.6, 0.48, 'F');

    // --- BACK PAGE ---
    $pdf->AddPage();
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect(0, 0, 85.6, 53.98, 'F');
    $pdf->SetFillColor(30, 30, 30);
    $pdf->Rect(0, 4, 85.6, 10, 'F'); // Magstripe

    // QR Code
    $style = ['border'=>0, 'padding'=>1, 'fgcolor'=>[0,0,0], 'bgcolor'=>false];
    $pdf->write2DBarcode('https://ghanaiandiaspora.org/v/'.$memId, 'QRCODE,M', 62, 16, 16, 16, $style, 'N');
    
    $pdf->SetFont('helvetica', '', 4.5);
    $pdf->SetXY(5, 18);
    $pdf->MultiCell(50, 3, "This card remains property of GDU Europe. If found, please return to the GDU Secretariat or the nearest Ghana Embassy.\n\nVerified Online: ghanaiandiaspora.org", 0, 'L');

    // MRZ Zone
    $pdf->SetFont('courier', 'B', 8);
    $pdf->SetXY(4, 42);
    $mrz1 = "IDGHA" . str_replace('-', '', $memId);
    $pdf->Cell(0, 4, str_pad($mrz1, 30, "<"), 0, 1, 'L');
    $pdf->SetXY(4, 46);
    $mrz2 = date('ymd', strtotime($member['date_of_birth'] ?? '851012')) . "M" . date('ymd', strtotime($expiryDate)) . "GHA<<<<<";
    $pdf->Cell(0, 4, $mrz2, 0, 1, 'L');

    $tempFile = sys_get_temp_dir() . '/gdu_card_' . $id . '.pdf';
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
        $mail->Subject = "Membership Approved - Your Official ID Card ($membershipId)";
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