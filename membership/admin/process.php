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

    // FRONT
    $pdf->AddPage();
    $pdf->SetFillColor(248, 249, 250);
    $pdf->Rect(0, 0, 85.6, 53.98, 'F');

    // Watermarks
    $pdf->SetAlpha(0.07);
    $pdf->Image($coatOfArms, 12, 5, 62, 44, 'PNG');
    $pdf->SetAlpha(0.06);
    $pdf->Image($flagUrl, 0, 0, 85.6, 54, 'JPG');
    $pdf->SetAlpha(1.0);

    // Header
    $pdf->SetFillColor(206, 17, 38);
    $pdf->Rect(0, 0, 85.6, 7, 'F');
    $pdf->Image($logoUrl, 2, 1.2, 9, 9, 'PNG');

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetXY(13, 1.8);
    $pdf->Cell(0, 4, 'GHANAIAN DIASPORA UNION', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetXY(13, 5.2);
    $pdf->Cell(0, 4, 'IN EUROPE', 0, 1, 'L');

    $pdf->Image($flagUrl, 73, 1, 10, 6, 'JPG');

    // Photo
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetDrawColor(180, 180, 180);
    $pdf->Rect(58, 11, 24, 30, 'DF');
    $pdf->Image($photoUrl, 59, 12, 22, 28, '', '', '', false, 300, '', false, false, 0, true);

    // Data
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 5.5); $pdf->SetXY(4, 12); $pdf->Cell(20, 3, 'SURNAME', 0, 0);
    $pdf->SetFont('helvetica', 'B', 9);   $pdf->SetXY(4, 15); $pdf->Cell(50, 5, strtoupper($member['last_name'] ?? 'DOE'), 0, 1);

    $pdf->SetFont('helvetica', 'B', 5.5); $pdf->SetXY(4, 19); $pdf->Cell(20, 3, 'FIRST NAMES', 0, 0);
    $pdf->SetFont('helvetica', 'B', 9);   $pdf->SetXY(4, 22); $pdf->Cell(50, 5, strtoupper($member['first_name'] ?? 'JOHN KWAME'), 0, 1);

    $pdf->SetFillColor(230, 230, 240);
    $pdf->Rect(4, 28, 48, 7, 'F');
    $pdf->SetFont('helvetica', 'B', 5.5); $pdf->SetXY(5, 28.5); $pdf->Cell(25, 3, 'MEMBERSHIP NO. (PIN)', 0, 0);
    $pdf->SetFont('courier', 'B', 10);    $pdf->SetXY(5, 31.5); $pdf->Cell(45, 4, $memId, 0, 1);

    $pdf->SetFont('helvetica', 'B', 5); $pdf->SetTextColor(80,80,80);
    $pdf->SetXY(4, 37); $pdf->Cell(18, 3, 'SEX', 0, 0);
    $pdf->SetXY(22, 37); $pdf->Cell(22, 3, 'DATE OF BIRTH', 0, 0);
    $pdf->SetXY(44, 37); $pdf->Cell(22, 3, 'NATIONALITY', 0, 0);

    $pdf->SetFont('helvetica', '', 7.5); $pdf->SetTextColor(0,0,0);
    $pdf->SetXY(4, 40);   $pdf->Cell(18, 4, strtoupper($member['gender'] ?? 'M'), 0, 0);
    $pdf->SetXY(22, 40);  $pdf->Cell(22, 4, $member['date_of_birth'] ?? '12.10.1985', 0, 0);
    $pdf->SetXY(44, 40);  $pdf->Cell(22, 4, strtoupper($member['nationality'] ?? 'GHANAIAN'), 0, 0);

    $issueDate  = date('d.m.Y');
    $expiryDate = date('d.m.Y', strtotime('+5 years'));

    $pdf->SetFont('helvetica', 'B', 5); 
    $pdf->SetXY(4, 45); $pdf->Cell(30, 3, 'PASSPORT NO.', 0, 0);
    $pdf->SetFont('courier', '', 7); 
    $pdf->SetXY(4, 47.5); $pdf->Cell(40, 4, $member['ghana_passport_number'] ?? '---', 0, 1);

    $pdf->SetFont('helvetica', 'B', 5);
    $pdf->SetXY(48, 45); $pdf->Cell(20, 3, 'DATE OF ISSUE', 0, 0);
    $pdf->SetXY(48, 47.5); $pdf->Cell(20, 3, $issueDate, 0, 0);
    $pdf->SetXY(68, 45); $pdf->Cell(20, 3, 'EXPIRY DATE', 0, 0);
    $pdf->SetXY(68, 47.5); $pdf->Cell(20, 3, $expiryDate, 0, 0);

    $pdf->SetFont('helvetica', '', 3);
    $pdf->SetXY(0, 51.8);
    $pdf->Cell(85.6, 2, str_repeat('GHANAIAN DIASPORA UNION IN EUROPE • ', 8), 0, 0, 'C');

    // BACK
    $pdf->AddPage();
    $pdf->SetFillColor(245, 245, 245);
    $pdf->Rect(0, 0, 85.6, 53.98, 'F');

    $pdf->SetAlpha(0.08);
    $pdf->Image($coatOfArms, 18, 8, 50, 38, 'PNG');
    $pdf->SetAlpha(1.0);

    $pdf->SetFillColor(30, 30, 30);
    $pdf->Rect(0, 3, 85.6, 8, 'F');

    $pdf->SetFont('helvetica', 'I', 5.5);
    $pdf->SetXY(5, 13);
    $pdf->Cell(40, 4, 'HOLDER SIGNATURE', 0, 1);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Rect(5, 17, 42, 9, 'F');

    $style = ['border'=>0, 'padding'=>1, 'fgcolor'=>[0,51,102], 'bgcolor'=>false];
    $pdf->write2DBarcode('https://ghanaiandiaspora.org/v/' . urlencode($memId), 'QRCODE,M', 58, 12, 20, 20, $style, 'N');
    $pdf->SetFont('helvetica', 'B', 5);
    $pdf->SetXY(58, 33);
    $pdf->Cell(20, 3, 'VERIFY ONLINE', 0, 0, 'C');

    $pdf->SetFont('helvetica', '', 5);
    $pdf->SetXY(5, 28);
    $pdf->MultiCell(50, 3, "This card is the property of the Ghanaian Diaspora Union in Europe. If found, please return to the nearest Ghana Embassy or GDU Secretariat.", 0, 'L');

    $pdf->SetFont('courier', 'B', 8.5);
    $pdf->SetXY(4, 39);
    $cleanId = str_replace('-', '', $memId);
    $mrz1 = "IDGHA" . $cleanId . str_repeat('<', max(0, 15 - strlen($cleanId)));
    $pdf->Cell(0, 4, $mrz1, 0, 1, 'L');

    $pdf->SetXY(4, 43);
    $dob = date('ymd', strtotime($member['date_of_birth'] ?? '1985-10-12'));
    $exp = date('ymd', strtotime($expiryDate));
    $mrz2 = $dob . "M" . $exp . "GHA" . str_repeat('<', 14);
    $pdf->Cell(0, 4, $mrz2, 0, 1, 'L');

    $pdf->SetFillColor(206, 17, 38);
    $pdf->Rect(0, 52, 85.6, 1.98, 'F');

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