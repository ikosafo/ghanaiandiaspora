<?php
// test-mailer.php - Fixed version with full debug capture

require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Increase timeout for testing
set_time_limit(300);

echo "<h2>Brevo SMTP Test (Fixed Debug Capture)</h2>";
echo "<pre>Time: " . date('Y-m-d H:i:s') . " GMT\n";
echo "Host: $smtp_host | Port: $smtp_port | Secure: tls\n";
echo "Username: $smtp_username\n\n</pre>";

$mail = new PHPMailer(true);

// Variable to hold ALL debug output
$debug_log = '';

try {
    // === DEBUG SETTINGS ===
    $mail->SMTPDebug  = 2;  // Full client + server conversation
    $mail->Timeout    = 30;

    // Capture debug to variable instead of echoing directly
    $mail->Debugoutput = function($str, $level) use (&$debug_log) {
        $debug_log .= "[$level] " . htmlspecialchars($str) . "\n";
    };

    $mail->isSMTP();
    $mail->Host       = $smtp_host;
    $mail->SMTPAuth   = true;
     $mail->AuthType = 'PLAIN';
    $mail->Username   = $smtp_username;
    $mail->Password   = $smtp_password;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = $smtp_port;

    // Use a verified sender email (avoids some rejections)
    $test_recipient = 'd';  
    $mail->setFrom($admin_email, 'Test Mailer - Ghanaian Diaspora');
    $mail->addAddress($test_recipient);

    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test from Local WAMP';
    $mail->Body    = '<h2>Test Email</h2><p>Sent at ' . date('Y-m-d H:i:s') . ' GMT</p><p>If you see this, SMTP works!</p>';

    echo "<p>Sending to: $test_recipient ...</p>";

    $mail->send();

    echo '<div style="color: green; font-weight: bold; font-size: 1.2em;">SUCCESS! Test email sent.</div>';

} catch (Exception $e) {
    echo '<div style="color: red; font-weight: bold; font-size: 1.2em;">FAILED</div>';
    echo '<p><strong>Final Error Summary:</strong> ' . htmlspecialchars($mail->ErrorInfo) . '</p>';
    echo '<p><strong>Exception Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '<h3>Full Debug Log (this is what we need):</h3>';
echo '<pre style="background: #f8f8f8; padding: 15px; border: 1px solid #ddd; overflow: auto; max-height: 600px;">';
echo htmlspecialchars($debug_log ?: 'No debug output captured (connection may have failed before debug started).');
echo '</pre>';

?>

<p><a href="test-mailer.php">Run test again</a> | <a href="index.php">Back to form</a></p>