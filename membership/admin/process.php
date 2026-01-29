<?php
// session_start(); 
require '../config.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Premium National ID Style Card Generator (Front + Back)
 */
function generateMembershipCardPDF($member, $memId) {
    // CR80 ID-1 size: 85.60 × 53.98 mm
    // 'L' for Landscape
    $pdf = new TCPDF('L', 'mm', array(85.6, 53.98), true, 'UTF-8', false);
    
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Asset URLs
    $flagUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/19/Flag_of_Ghana.svg/1280px-Flag_of_Ghana.svg.png';
    $logoUrl = 'https://www.ghanaiandiaspora.org/wp-content/uploads/2025/04/GDUE-Logo-New-e1744094935301.png';
    $dummyPhoto = 'https://i.pravatar.cc/300?u=' . $memId; // Generates a consistent professional headshot

    // --- FRONT SIDE ---
    $pdf->AddPage();
    
    // 1. Background Base
    $pdf->SetFillColor(252, 252, 254);
    $pdf->Rect(0, 0, 85.6, 53.98, 'F');

    // 2. Ghana Flag Watermark (Central, low opacity)
    $pdf->SetAlpha(0.08);
    $pdf->Image($flagUrl, 10, 8, 65, 38, 'PNG');
    $pdf->SetAlpha(1.0);

    // 3. Header Accents (Ghana Colors)
    $pdf->SetFillColor(0, 107, 63); // Green
    $pdf->Rect(0, 0, 85.6, 1.2, 'F');
    $pdf->SetFillColor(252, 209, 22); // Yellow
    $pdf->Rect(0, 1.2, 85.6, 1.2, 'F');
    $pdf->SetFillColor(206, 17, 38); // Red
    $pdf->Rect(0, 2.4, 85.6, 1.2, 'F');

    // 4. Logo & Identity
    $pdf->Image($logoUrl, 3, 5, 8, 8, 'PNG');
    $pdf->SetTextColor(10, 35, 80);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetXY(12, 5.5);
    $pdf->Cell(50, 4, 'GHANAIAN DIASPORA UNION IN EUROPE', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetXY(12, 8.5);
    $pdf->SetTextColor(150, 0, 0);
    $pdf->Cell(50, 3, 'OFFICIAL MEMBERSHIP IDENTITY CARD', 0, 1, 'L');

    // 5. Member Photo (ID Style)
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Rect(60, 13, 22, 27, 'DF'); // Box for photo
    $pdf->Image($dummyPhoto, 60.5, 13.5, 21, 26, 'JPG');

    // 6. Data Fields
    $pdf->SetTextColor(30, 30, 30);
    
    // Surname/Name
    $pdf->SetFont('helvetica', 'B', 5); $pdf->SetXY(4, 15); $pdf->Cell(20, 3, 'SURNAME', 0, 0);
    $pdf->SetFont('helvetica', 'B', 8); $pdf->SetXY(4, 17.5); $pdf->Cell(50, 4, strtoupper($member['last_name'] ?? 'DOE'), 0, 1);
    
    $pdf->SetFont('helvetica', 'B', 5); $pdf->SetXY(4, 21.5); $pdf->Cell(20, 3, 'FIRST NAMES', 0, 0);
    $pdf->SetFont('helvetica', 'B', 8); $pdf->SetXY(4, 24); $pdf->Cell(50, 4, strtoupper($member['first_name'] ?? 'JOHN KWAME'), 0, 1);

    // ID Number (PIN) - Highlighted
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Rect(4, 29, 35, 6, 'F');
    $pdf->SetFont('helvetica', 'B', 5); $pdf->SetXY(5, 29.5); $pdf->Cell(20, 2, 'MEMBERSHIP NO.', 0, 0);
    $pdf->SetFont('courier', 'B', 9); $pdf->SetXY(5, 31.5); $pdf->Cell(30, 3, $memId, 0, 1);

    // Grid details
    $pdf->SetFont('helvetica', 'B', 4.5); $pdf->SetTextColor(100,100,100);
    $pdf->SetXY(4, 37); $pdf->Cell(15, 3, 'SEX', 0, 0);
    $pdf->SetXY(15, 37); $pdf->Cell(15, 3, 'DATE OF BIRTH', 0, 0);
    $pdf->SetXY(35, 37); $pdf->Cell(15, 3, 'NATIONALITY', 0, 0);

    $pdf->SetFont('helvetica', 'B', 6.5); $pdf->SetTextColor(0,0,0);
    $pdf->SetXY(4, 39.5); $pdf->Cell(15, 3, strtoupper($member['gender'][0] ?? 'M'), 0, 0);
    $pdf->SetXY(15, 39.5); $pdf->Cell(15, 3, $member['dob'] ?? '12.10.1985', 0, 0);
    $pdf->SetXY(35, 39.5); $pdf->Cell(15, 3, 'GHANAIAN', 0, 0);

    // Footer Dates
    $pdf->SetFont('helvetica', '', 4.5); $pdf->SetXY(4, 46); $pdf->Cell(20, 2, 'DATE OF ISSUE', 0, 0);
    $pdf->SetXY(25, 46); $pdf->Cell(20, 2, 'DATE OF EXPIRY', 0, 0);
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetXY(4, 48); $pdf->Cell(20, 3, date('d.m.Y'), 0, 0);
    $pdf->SetXY(25, 48); $pdf->Cell(20, 3, date('d.m.Y', strtotime('+5 years')), 0, 0);

    // Microprint-style line
    $pdf->SetFont('helvetica', '', 3);
    $pdf->SetXY(0, 52.5);
    $pdf->Cell(85.6, 1.5, str_repeat('GHANAIAN DIASPORA UNION IN EUROPE ', 10), 0, 0, 'C');

    // --- BACK SIDE ---
    $pdf->AddPage();
    
    // Background
    $pdf->SetFillColor(250, 250, 250);
    $pdf->Rect(0, 0, 85.6, 53.98, 'F');
    
    // Subtle Flag
    $pdf->SetAlpha(0.05);
    $pdf->Image($flagUrl, 20, 10, 45, 30, 'PNG');
    $pdf->SetAlpha(1.0);

    // Magnetic Stripe Simulation
    $pdf->SetFillColor(40, 40, 40);
    $pdf->Rect(0, 4, 85.6, 9, 'F');

    // Signature Area
    $pdf->SetFont('helvetica', 'I', 5);
    $pdf->SetXY(5, 15);
    $pdf->Cell(40, 3, 'HOLDER SIGNATURE', 0, 1);
    $pdf->SetFillColor(235, 235, 235);
    $pdf->Rect(5, 18, 40, 8, 'F');
    
    // QR Code for verification
    $style = array('border' => 0, 'padding' => 1, 'fgcolor' => array(10,35,80), 'bgcolor' => false);
    $pdf->write2DBarcode('https://ghanaiandiaspora.org/v/'.$memId, 'QRCODE,M', 60, 15, 18, 18, $style, 'N');
    $pdf->SetFont('helvetica', 'B', 4);
    $pdf->SetXY(60, 33);
    $pdf->Cell(18, 3, 'VERIFY AUTHENTICITY', 0, 0, 'C');

    // Official Text
    $pdf->SetFont('helvetica', '', 5);
    $pdf->SetXY(5, 28);
    $pdf->MultiCell(50, 3, "This card remains the property of GDU Europe. If found, please return to the nearest Ghana Embassy or GDU Secretariat.", 0, 'L');

    // MRZ (Machine Readable Zone) - The "National ID" look
    $pdf->SetFont('courier', 'B', 8);
    $pdf->SetXY(4, 40);
    $mrz1 = "IDGHA" . str_replace('-', '', $memId) . "<<<<<<<<<<<<<<";
    $mrz2 = date('ymd', strtotime($member['dob'] ?? '851012')) . "M" . date('ymd', strtotime('+5 years')) . "GHA<<<<<<<<<<<7";
    $pdf->Cell(0, 4, $mrz1, 0, 1, 'L');
    $pdf->SetXY(4, 44);
    $pdf->Cell(0, 4, $mrz2, 0, 1, 'L');

    $pdf->SetFillColor(206, 17, 38);
    $pdf->Rect(0, 52, 85.6, 2, 'F');

    $tempFile = sys_get_temp_dir() . '/card_' . $memId . '.pdf';
    $pdf->Output($tempFile, 'F');
    return $tempFile;
}

// --- LOGIC EXECUTION ---
$id = (int)($_REQUEST['id'] ?? 0);
$action = $_REQUEST['action'] ?? '';

if ($id > 0 && $action === 'approve') {
    $stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if ($user) {
        $membershipId = "GDU-" . date('Y') . "-" . str_pad($user['id'], 4, '0', STR_PAD_LEFT);
        
        // Update DB
        $pdo->prepare("UPDATE registrations SET status='approved', membership_id=? WHERE id=?")
            ->execute([$membershipId, $id]);

        $pdfPath = generateMembershipCardPDF($user, $membershipId);

        // PHPMailer Setup
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
            $mail->addAddress($user['email'], $user['full_name']);
            $mail->Subject = "Membership Approved - $membershipId";
            $mail->isHTML(true);
            $mail->Body = "<h3>Welcome to the Union!</h3><p>Please find your official ID card attached.</p>";
            
            if (file_exists($pdfPath)) {
                $mail->addAttachment($pdfPath, "GDU_Identity_Card.pdf");
            }

            $mail->send();
            @unlink($pdfPath);
            header("Location: index.php?msg=Member Approved and Card Sent");
        } catch (Exception $e) {
            die("Mail Error: " . $mail->ErrorInfo);
        }
    }
}