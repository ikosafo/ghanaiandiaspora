<?php
set_time_limit(300);

require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name              = trim($_POST['full_name'] ?? '');
    $email                  = trim($_POST['email'] ?? '');
    $gender                 = $_POST['gender'] ?? '';
    $date_of_birth          = $_POST['date_of_birth'] ?? '';
    $whatsapp_number        = trim($_POST['whatsapp_number'] ?? '');
    $ghana_passport_number  = trim($_POST['ghana_passport_number'] ?? '');
    $nationality            = trim($_POST['nationality'] ?? '');
    $current_address_diaspora = trim($_POST['current_address_diaspora'] ?? '');
    $current_address_ghana  = trim($_POST['current_address_ghana'] ?? '');
    $emergency_contact      = trim($_POST['emergency_contact'] ?? '');
    $emergency_phone        = trim($_POST['emergency_phone'] ?? '');
    $admin_email_new = 'membership@ghanaiandiaspora.org';

    if (!empty($full_name) && filter_var($email, FILTER_VALIDATE_EMAIL) &&
        in_array($gender, ['Male', 'Female']) &&
        !empty($date_of_birth) && !empty($whatsapp_number) &&
        !empty($ghana_passport_number) && !empty($nationality) &&
        !empty($current_address_diaspora) && !empty($emergency_contact) &&
        !empty($emergency_phone)) {

        try {
            // 1. Insert into database
            $stmt = $pdo->prepare("
                INSERT INTO registrations 
                (full_name, email, gender, date_of_birth, whatsapp_number, ghana_passport_number, nationality, 
                 current_address_diaspora, current_address_ghana, emergency_contact_person, emergency_phone_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $full_name, $email, $gender, $date_of_birth, $whatsapp_number, $ghana_passport_number,
                $nationality, $current_address_diaspora, $current_address_ghana, $emergency_contact, $emergency_phone
            ]);

            // 2. Prepare email
            $mail = new PHPMailer(true);

            // Debug capture
            $debug_log = '';

            $mail->SMTPDebug  = 2;                      // ← change to 0 in production
            $mail->Timeout    = 30;
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

            // IMPORTANT: Use your admin email as From (verified in Brevo)
            // User's email goes to Reply-To
            $mail->setFrom($admin_email, 'Ghanaian Diaspora Membership');
            $mail->addReplyTo($email, $full_name);
            $mail->addAddress($admin_email_new);

            $mail->isHTML(true);
            $mail->Subject = 'New Membership Registration - ' . $full_name;

            $mail->Body = '
                <h2>New Membership Submission</h2>
                <p><strong>Full Name:</strong> ' . htmlspecialchars($full_name) . '</p>
                <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
                <p><strong>Gender:</strong> ' . htmlspecialchars($gender) . '</p>
                <p><strong>Date of Birth:</strong> ' . htmlspecialchars($date_of_birth) . '</p>
                <p><strong>WhatsApp:</strong> ' . htmlspecialchars($whatsapp_number) . '</p>
                <p><strong>Ghana Passport Number:</strong> ' . htmlspecialchars($ghana_passport_number) . '</p>
                <p><strong>Current Diaspora Country:</strong> ' . htmlspecialchars($nationality) . '</p>
                <p><strong>Address in Diaspora:</strong><br>' . nl2br(htmlspecialchars($current_address_diaspora)) . '</p>
                <p><strong>Address in Ghana:</strong><br>' . nl2br(htmlspecialchars($current_address_ghana)) . '</p>
                <p><strong>Emergency Contact:</strong> ' . htmlspecialchars($emergency_contact) . '</p>
                <p><strong>Emergency Phone:</strong> ' . htmlspecialchars($emergency_phone) . '</p>
                <hr>
                <small>Sent from: ' . $_SERVER['REMOTE_ADDR'] . ' at ' . date('Y-m-d H:i:s') . '</small>';

            $mail->send();

            $message = '<div class="message success">Registration submitted successfully! Awaiting approval.</div>';

        } catch (Exception $e) {
            $message = '<div class="message error">
                <strong>Failed to send notification email:</strong><br>
                ' . htmlspecialchars($mail->ErrorInfo ?? $e->getMessage()) . '
                <details style="margin-top:10px;">
                    <summary>Show technical debug log</summary>
                    <pre style="background:#f8f8f8; padding:10px; font-size:0.9em; overflow:auto; max-height:300px;">' 
                    . htmlspecialchars($debug_log ?: 'No debug captured') . 
                    '</pre>
                </details>
            </div>';
        } catch (PDOException $e) {
            $message = '<div class="message error">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $message = '<div class="message error">Please fill all required fields correctly.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Member</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        :root {
            --primary: #0066ff;
            --primary-dark: #0052cc;
            --accent: #ff6b00;
            --text: #1a1a2e;
            --text-light: #4a4a68;
            --bg: #f8f9fc;
            --card-bg: rgba(255, 255, 255, 0.97);
            --border: #d0d8ff;
            --shadow: 0 12px 40px rgba(0, 0, 60, 0.10);
            --input-bg: #ffffff;
            --error: #e63946;
            --success: #00c853;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "DM Sans", sans-serif;
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 40px 30px;
            border: 1px solid rgba(255,255,255,0.4);
        }
        h1 {
            font-family: 'Anton', sans-serif;
            font-size: 2.8rem;
            text-align: center;
            color: var(--text);
            margin-bottom: 12px;
            letter-spacing: -1.5px;
        }
        .form-note {
            text-align: center;
            color: var(--text-light);
            font-size: 1rem;
            margin-bottom: 40px;
        }
        .required-note { color: var(--error); }
        .input-group {
            position: relative;
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1.05rem;
        }
        .input-group input,
        .input-group textarea,
        .choices__inner {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1.5px solid var(--border);
            border-radius: 14px;
            font-size: 1rem;
            background: var(--input-bg);
            transition: all 0.25s ease;
        }
        .input-group textarea {
            padding: 12px 16px 12px 48px;
            min-height: 92px;
            resize: vertical;
        }
        .input-group input:focus,
        .input-group textarea:focus,
        .choices__inner:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0,102,255,0.14);
        }
        .input-icon {
            position: absolute;
            left: 16px;
            top: 55%;
            transform: translateY(-50%);
            color: var(--primary);
            pointer-events: none;
            z-index: 2;
        }
        .textarea-icon {
            top: 30% !important;
            z-index: 0;
        }
        .input-group.textarea .input-icon {
            top: 20px;
            transform: none;
        }
        .error-message {
            color: var(--error);
            font-size: 0.9rem;
            margin-top: 6px;
            min-height: 1.2em;
            visibility: hidden;
            line-height: 1.3;
        }
        .input-group.invalid .error-message {
            visibility: visible;
        }
        .input-group.invalid input,
        .input-group.invalid textarea,
        .input-group.invalid .choices__inner {
            border-color: var(--error);
            animation: shake 0.4s;
        }
        .input-group.valid input:not(:placeholder-shown),
        .input-group.valid textarea:not(:placeholder-shown),
        .input-group.valid .choices__inner {
            border-color: var(--success);
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-6px); }
            20%, 40%, 60%, 80% { transform: translateX(6px); }
        }
        .radio-group {
            display: flex;
            gap: 36px;
            margin: 12px 0 28px;
        }
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        button {
            width: 100%;
            padding: 18px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1.3rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 36px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(255,107,0,0.3);
        }
        button:hover {
            background: #e65c00;
            transform: translateY(-3px);
            box-shadow: 0 14px 36px rgba(255,107,0,0.4);
        }
        .message {
            padding: 18px;
            border-radius: 14px;
            margin-bottom: 32px;
            text-align: center;
            font-weight: 600;
            font-size: 1.05rem;
        }
        .success { background: #e6ffe6; color: #006600; }
        .error { background: #ffe6e6; color: #990000; }
        .choices__inner {
            padding-left: 48px !important;
            min-height: 52px;
        }
        .choices__list--dropdown {
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            border: 1px solid #d0d8ff;
        }
        @media (max-width: 600px) {
            .container { padding: 32px 22px; }
            h1 { font-size: 2.3rem; }
            .radio-group { flex-direction: column; gap: 14px; }
        }
        div.choices {
            margin-bottom: 8px !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Become a Member</h1>
        <p class="form-note">
            Your email address will not be published. Required fields are marked <span class="required-note">*</span>
        </p>

        <?php if ($message): ?>
            <?= $message ?>
        <?php endif; ?>

        <form method="POST" id="registrationForm" novalidate>
            <div class="input-group" data-validate="required">
                <label for="full_name">Full Name <span class="required-note">*</span></label>
                <i data-feather="user" class="input-icon"></i>
                <input type="text" id="full_name" name="full_name" required>
                <div class="error-message">Please enter your full name</div>
            </div>
            <div class="input-group" data-validate="email">
                <label for="email">Email Address <span class="required-note">*</span></label>
                <i data-feather="mail" class="input-icon"></i>
                <input type="email" id="email" name="email" required>
                <div class="error-message">Please enter a valid email address</div>
            </div>
            <label>Gender <span class="required-note">*</span></label>
            <div class="radio-group">
                <label><input type="radio" name="gender" value="Male" required> Male</label>
                <label><input type="radio" name="gender" value="Female"> Female</label>
            </div>
            <div class="error-message" style="margin-top:6px; display:none;" id="genderError">Please select your gender</div>
            <div class="input-group" data-validate="date">
                <label for="date_of_birth">Date of Birth <span class="required-note">*</span> (yyyy-mm-dd)</label>
                <i data-feather="calendar" class="input-icon"></i>
                <input type="text" id="date_of_birth" name="date_of_birth" placeholder="yyyy-mm-dd" required>
                <div class="error-message">Please enter a valid date of birth</div>
            </div>
            <div class="input-group" data-validate="phone">
                <label for="whatsapp_number">WhatsApp Number <span class="required-note">*</span></label>
                <i data-feather="phone" class="input-icon"></i>
                <input type="tel" id="whatsapp_number" name="whatsapp_number" required pattern="[0-9+\-\s()]{8,}">
                <div class="error-message">Please enter a valid phone number (min 8 digits)</div>
            </div>
            <div class="input-group" data-validate="required">
                <label for="ghana_passport_number">Ghana Passport Number <span class="required-note">*</span></label>
                <i data-feather="credit-card" class="input-icon"></i>
                <input type="text" id="ghana_passport_number" name="ghana_passport_number" required>
                <div class="error-message">Please enter your passport number</div>
            </div>
            <div class="input-group" data-validate="select">
                <label for="nationality">Current Diaspora Country <span class="required-note">*</span></label>
                <i data-feather="globe" class="input-icon"></i>
                <select id="nationality" name="nationality" required>
                    <option value="">Select country...</option>
                    <option value="Afghanistan">🇦🇫 Afghanistan</option>
                    <option value="Albania">🇦🇱 Albania</option>
                    <option value="Algeria">🇩🇿 Algeria</option>
                    <option value="Andorra">🇦🇩 Andorra</option>
                    <option value="Angola">🇦🇴 Angola</option>
                    <option value="Antigua and Barbuda">🇦🇬 Antigua and Barbuda</option>
                    <option value="Argentina">🇦🇷 Argentina</option>
                    <option value="Armenia">🇦🇲 Armenia</option>
                    <option value="Australia">🇦🇺 Australia</option>
                    <option value="Austria">🇦🇹 Austria</option>
                    <option value="Azerbaijan">🇦🇿 Azerbaijan</option>
                    <option value="Bahamas">🇧🇸 Bahamas</option>
                    <option value="Bahrain">🇧🇭 Bahrain</option>
                    <option value="Bangladesh">🇧🇩 Bangladesh</option>
                    <option value="Barbados">🇧🇧 Barbados</option>
                    <option value="Belarus">🇧🇾 Belarus</option>
                    <option value="Belgium">🇧🇪 Belgium</option>
                    <option value="Belize">🇧🇿 Belize</option>
                    <option value="Benin">🇧🇯 Benin</option>
                    <option value="Bhutan">🇧🇹 Bhutan</option>
                    <option value="Bolivia">🇧🇴 Bolivia</option>
                    <option value="Bosnia and Herzegovina">🇧🇦 Bosnia and Herzegovina</option>
                    <option value="Botswana">🇧🇼 Botswana</option>
                    <option value="Brazil">🇧🇷 Brazil</option>
                    <option value="Brunei">🇧🇳 Brunei</option>
                    <option value="Bulgaria">🇧🇬 Bulgaria</option>
                    <option value="Burkina Faso">🇧🇫 Burkina Faso</option>
                    <option value="Burundi">🇧🇮 Burundi</option>
                    <option value="Cambodia">🇰🇭 Cambodia</option>
                    <option value="Cameroon">🇨🇲 Cameroon</option>
                    <option value="Canada">🇨🇦 Canada</option>
                    <option value="Cape Verde">🇨🇻 Cape Verde</option>
                    <option value="Central African Republic">🇨🇫 Central African Republic</option>
                    <option value="Chad">🇹🇩 Chad</option>
                    <option value="Chile">🇨🇱 Chile</option>
                    <option value="China">🇨🇳 China</option>
                    <option value="Colombia">🇨🇴 Colombia</option>
                    <option value="Comoros">🇰🇲 Comoros</option>
                    <option value="Congo">🇨🇬 Congo</option>
                    <option value="Costa Rica">🇨🇷 Costa Rica</option>
                    <option value="Croatia">🇭🇷 Croatia</option>
                    <option value="Cuba">🇨🇺 Cuba</option>
                    <option value="Cyprus">🇨🇾 Cyprus</option>
                    <option value="Czech Republic">🇨🇿 Czech Republic</option>
                    <option value="Denmark">🇩🇰 Denmark</option>
                    <option value="Djibouti">🇩🇯 Djibouti</option>
                    <option value="Dominica">🇩🇲 Dominica</option>
                    <option value="Dominican Republic">🇩🇴 Dominican Republic</option>
                    <option value="East Timor">🇹🇱 East Timor</option>
                    <option value="Ecuador">🇪🇨 Ecuador</option>
                    <option value="Egypt">🇪🇬 Egypt</option>
                    <option value="El Salvador">🇸🇻 El Salvador</option>
                    <option value="Equatorial Guinea">🇬🇶 Equatorial Guinea</option>
                    <option value="Eritrea">🇪🇷 Eritrea</option>
                    <option value="Estonia">🇪🇪 Estonia</option>
                    <option value="Eswatini">🇸🇿 Eswatini</option>
                    <option value="Ethiopia">🇪🇹 Ethiopia</option>
                    <option value="Fiji">🇫🇯 Fiji</option>
                    <option value="Finland">🇫🇮 Finland</option>
                    <option value="France">🇫🇷 France</option>
                    <option value="Gabon">🇬🇦 Gabon</option>
                    <option value="Gambia">🇬🇲 Gambia</option>
                    <option value="Georgia">🇬🇪 Georgia</option>
                    <option value="Germany">🇩🇪 Germany</option>
                    <option value="Ghana">🇬🇭 Ghana</option>
                    <option value="Greece">🇬🇷 Greece</option>
                    <option value="Grenada">🇬🇩 Grenada</option>
                    <option value="Guatemala">🇬🇹 Guatemala</option>
                    <option value="Guinea">🇬🇳 Guinea</option>
                    <option value="Guinea-Bissau">🇬🇼 Guinea-Bissau</option>
                    <option value="Guyana">🇬🇾 Guyana</option>
                    <option value="Haiti">🇭🇹 Haiti</option>
                    <option value="Honduras">🇭🇳 Honduras</option>
                    <option value="Hungary">🇭🇺 Hungary</option>
                    <option value="Iceland">🇮🇸 Iceland</option>
                    <option value="India">🇮🇳 India</option>
                    <option value="Indonesia">🇮🇩 Indonesia</option>
                    <option value="Iran">🇮🇷 Iran</option>
                    <option value="Iraq">🇮🇶 Iraq</option>
                    <option value="Ireland">🇮🇪 Ireland</option>
                    <option value="Israel">🇮🇱 Israel</option>
                    <option value="Italy">🇮🇹 Italy</option>
                    <option value="Jamaica">🇯🇲 Jamaica</option>
                    <option value="Japan">🇯🇵 Japan</option>
                    <option value="Jordan">🇯🇴 Jordan</option>
                    <option value="Kazakhstan">🇰🇿 Kazakhstan</option>
                    <option value="Kenya">🇰🇪 Kenya</option>
                    <option value="Kiribati">🇰🇮 Kiribati</option>
                    <option value="Kosovo">🇽🇰 Kosovo</option>
                    <option value="Kuwait">🇰🇼 Kuwait</option>
                    <option value="Kyrgyzstan">🇰🇬 Kyrgyzstan</option>
                    <option value="Laos">🇱🇦 Laos</option>
                    <option value="Latvia">🇱🇻 Latvia</option>
                    <option value="Lebanon">🇱🇧 Lebanon</option>
                    <option value="Lesotho">🇱🇸 Lesotho</option>
                    <option value="Liberia">🇱🇷 Liberia</option>
                    <option value="Libya">🇱🇾 Libya</option>
                    <option value="Liechtenstein">🇱🇮 Liechtenstein</option>
                    <option value="Lithuania">🇱🇹 Lithuania</option>
                    <option value="Luxembourg">🇱🇺 Luxembourg</option>
                    <option value="Madagascar">🇲🇬 Madagascar</option>
                    <option value="Malawi">🇲🇼 Malawi</option>
                    <option value="Malaysia">🇲🇾 Malaysia</option>
                    <option value="Maldives">🇲🇻 Maldives</option>
                    <option value="Mali">🇲🇱 Mali</option>
                    <option value="Malta">🇲🇹 Malta</option>
                    <option value="Marshall Islands">🇲🇭 Marshall Islands</option>
                    <option value="Mauritania">🇲🇷 Mauritania</option>
                    <option value="Mauritius">🇲🇺 Mauritius</option>
                    <option value="Mexico">🇲🇽 Mexico</option>
                    <option value="Micronesia">🇫🇲 Micronesia</option>
                    <option value="Moldova">🇲🇩 Moldova</option>
                    <option value="Monaco">🇲🇨 Monaco</option>
                    <option value="Mongolia">🇲🇳 Mongolia</option>
                    <option value="Montenegro">🇲🇪 Montenegro</option>
                    <option value="Morocco">🇲🇦 Morocco</option>
                    <option value="Mozambique">🇲🇿 Mozambique</option>
                    <option value="Myanmar">🇲🇲 Myanmar</option>
                    <option value="Namibia">🇳🇦 Namibia</option>
                    <option value="Nauru">🇳🇷 Nauru</option>
                    <option value="Nepal">🇳🇵 Nepal</option>
                    <option value="Netherlands">🇳🇱 Netherlands</option>
                    <option value="New Zealand">🇳🇿 New Zealand</option>
                    <option value="Nicaragua">🇳🇮 Nicaragua</option>
                    <option value="Niger">🇳🇪 Niger</option>
                    <option value="Nigeria">🇳🇬 Nigeria</option>
                    <option value="North Korea">🇰🇵 North Korea</option>
                    <option value="North Macedonia">🇲🇰 North Macedonia</option>
                    <option value="Norway">🇳🇴 Norway</option>
                    <option value="Oman">🇴🇲 Oman</option>
                    <option value="Pakistan">🇵🇰 Pakistan</option>
                    <option value="Palau">🇵🇼 Palau</option>
                    <option value="Panama">🇵🇦 Panama</option>
                    <option value="Papua New Guinea">🇵🇬 Papua New Guinea</option>
                    <option value="Paraguay">🇵🇾 Paraguay</option>
                    <option value="Peru">🇵🇪 Peru</option>
                    <option value="Philippines">🇵🇭 Philippines</option>
                    <option value="Poland">🇵🇱 Poland</option>
                    <option value="Portugal">🇵🇹 Portugal</option>
                    <option value="Qatar">🇶🇦 Qatar</option>
                    <option value="Romania">🇷🇴 Romania</option>
                    <option value="Russia">🇷🇺 Russia</option>
                    <option value="Rwanda">🇷🇼 Rwanda</option>
                    <option value="Saint Kitts and Nevis">🇰🇳 Saint Kitts and Nevis</option>
                    <option value="Saint Lucia">🇱🇨 Saint Lucia</option>
                    <option value="Saint Vincent and the Grenadines">🇻🇨 Saint Vincent and the Grenadines</option>
                    <option value="Samoa">🇼🇸 Samoa</option>
                    <option value="San Marino">🇸🇲 San Marino</option>
                    <option value="Sao Tome and Principe">🇸🇹 Sao Tome and Principe</option>
                    <option value="Saudi Arabia">🇸🇦 Saudi Arabia</option>
                    <option value="Senegal">🇸🇳 Senegal</option>
                    <option value="Serbia">🇷🇸 Serbia</option>
                    <option value="Seychelles">🇸🇨 Seychelles</option>
                    <option value="Sierra Leone">🇸🇱 Sierra Leone</option>
                    <option value="Singapore">🇸🇬 Singapore</option>
                    <option value="Slovakia">🇸🇰 Slovakia</option>
                    <option value="Slovenia">🇸🇮 Slovenia</option>
                    <option value="Solomon Islands">🇸🇧 Solomon Islands</option>
                    <option value="Somalia">🇸🇴 Somalia</option>
                    <option value="South Africa">🇿🇦 South Africa</option>
                    <option value="South Korea">🇰🇷 South Korea</option>
                    <option value="South Sudan">🇸🇸 South Sudan</option>
                    <option value="Spain">🇪🇸 Spain</option>
                    <option value="Sri Lanka">🇱🇰 Sri Lanka</option>
                    <option value="Sudan">🇸🇩 Sudan</option>
                    <option value="Suriname">🇸🇷 Suriname</option>
                    <option value="Sweden">🇸🇪 Sweden</option>
                    <option value="Switzerland">🇨🇭 Switzerland</option>
                    <option value="Syria">🇸🇾 Syria</option>
                    <option value="Taiwan">🇹🇼 Taiwan</option>
                    <option value="Tajikistan">🇹🇯 Tajikistan</option>
                    <option value="Tanzania">🇹🇿 Tanzania</option>
                    <option value="Thailand">🇹🇭 Thailand</option>
                    <option value="Togo">🇹🇬 Togo</option>
                    <option value="Tonga">🇹🇴 Tonga</option>
                    <option value="Trinidad and Tobago">🇹🇹 Trinidad and Tobago</option>
                    <option value="Tunisia">🇹🇳 Tunisia</option>
                    <option value="Turkey">🇹🇷 Turkey</option>
                    <option value="Turkmenistan">🇹🇲 Turkmenistan</option>
                    <option value="Tuvalu">🇹🇻 Tuvalu</option>
                    <option value="Uganda">🇺🇬 Uganda</option>
                    <option value="Ukraine">🇺🇦 Ukraine</option>
                    <option value="United Arab Emirates">🇦🇪 United Arab Emirates</option>
                    <option value="United Kingdom">🇬🇧 United Kingdom</option>
                    <option value="United States">🇺🇸 United States</option>
                    <option value="Uruguay">🇺🇾 Uruguay</option>
                    <option value="Uzbekistan">🇺🇿 Uzbekistan</option>
                    <option value="Vanuatu">🇻🇺 Vanuatu</option>
                    <option value="Vatican City">🇻🇦 Vatican City</option>
                    <option value="Venezuela">🇻🇪 Venezuela</option>
                    <option value="Vietnam">🇻🇳 Vietnam</option>
                    <option value="Yemen">🇾🇪 Yemen</option>
                    <option value="Zambia">🇿🇲 Zambia</option>
                    <option value="Zimbabwe">🇿🇼 Zimbabwe</option>
                </select>
                <div class="error-message">Please select your current country</div>
            </div>
            <div class="input-group textarea" data-validate="required">
                <label for="current_address_diaspora">Current Address In Diaspora <span class="required-note">*</span></label>
                <i data-feather="home" class="input-icon textarea-icon"></i>
                <textarea id="current_address_diaspora" name="current_address_diaspora" rows="3" required></textarea>
                <div class="error-message">Please enter your diaspora address</div>
            </div>
            <div class="input-group textarea">
                <label for="current_address_ghana">Current Address In Ghana</label>
                <i data-feather="file-text" class="input-icon textarea-icon"></i>
                <textarea id="current_address_ghana" name="current_address_ghana" rows="3"></textarea>
            </div>
            <div class="input-group" data-validate="required">
                <label for="emergency_contact">Emergency Contact Person <span class="required-note">*</span></label>
                <i data-feather="user-check" class="input-icon"></i>
                <input type="text" id="emergency_contact" name="emergency_contact" required>
                <div class="error-message">Please enter emergency contact name</div>
            </div>
            <div class="input-group" data-validate="phone">
                <label for="emergency_phone">Emergency Contact Phone Number <span class="required-note">*</span></label>
                <i data-feather="phone-call" class="input-icon"></i>
                <input type="tel" id="emergency_phone" name="emergency_phone" required pattern="[0-9+\-\s()]{8,}">
                <div class="error-message">Please enter a valid emergency phone number</div>
            </div>
            <button type="submit">Register →</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        feather.replace();
        flatpickr("#date_of_birth", {
            dateFormat: "Y-m-d",
            maxDate: "today",
            theme: "light"
        });
        const choices = new Choices('#nationality', {
            searchEnabled: true,
            itemSelectText: '',
            searchPlaceholderValue: 'Type to search country...',
            noResultsText: 'No country found',
            shouldSort: true,
            position: 'bottom',
            resetScrollPosition: false,
        });

        const form = document.getElementById('registrationForm');
        const genderError = document.getElementById('genderError');

        function validateField(field) {
            const group = field.closest('.input-group');
            if (!group) return true;
            const type = group.dataset.validate;
            let isValid = true;
            if (type === 'required') {
                isValid = field.value.trim() !== '';
            } else if (type === 'email') {
                isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value.trim());
            } else if (type === 'phone') {
                isValid = field.value.trim().length >= 8 && /^[0-9+\-\s()]+$/.test(field.value.trim());
            } else if (type === 'date') {
                isValid = /^\d{4}-\d{2}-\d{2}$/.test(field.value.trim());
            } else if (type === 'select') {
                isValid = field.value !== '';
            }
            group.classList.remove('valid', 'invalid');
            if (isValid && field.value.trim() !== '') {
                group.classList.add('valid');
            } else if (!isValid && (field.dataset.touched || form.submitted)) {
                group.classList.add('invalid');
            }
            return isValid;
        }

        function validateRadio() {
            const checked = document.querySelector('input[name="gender"]:checked');
            const isValid = !!checked;
            genderError.style.display = isValid ? 'none' : 'block';
            return isValid;
        }

        form.querySelectorAll('input, textarea, select').forEach(el => {
            el.addEventListener('blur', () => {
                el.dataset.touched = true;
                validateField(el);
            });
            el.addEventListener('input', () => {
                if (form.submitted || el.dataset.touched) {
                    validateField(el);
                }
            });
        });

        choices.passedElement.element.addEventListener('change', () => {
            const group = choices.passedElement.element.closest('.input-group');
            if (group) {
                const isValid = choices.getValue(true) !== '';
                group.classList.toggle('invalid', !isValid);
                group.classList.toggle('valid', isValid);
            }
        });

        form.addEventListener('submit', e => {
            const button = form.querySelector('button[type="submit"]');
            button.disabled = true;
            button.innerText = 'Saving...';
            form.submitted = true;
            let isFormValid = true;

            form.querySelectorAll('[data-validate]').forEach(group => {
                const field = group.querySelector('input, textarea, select');
                if (field) {
                    const valid = validateField(field);
                    if (!valid) isFormValid = false;
                }
            });

            const genderValid = validateRadio();
            if (!genderValid) isFormValid = false;

            if (!isFormValid) {
                e.preventDefault();
                let messageDiv = document.getElementById('formMessage');
                if (!messageDiv) {
                    messageDiv = document.createElement('div');
                    messageDiv.id = 'formMessage';
                    messageDiv.className = 'message error';
                    form.parentNode.insertBefore(messageDiv, form);
                }
                messageDiv.textContent = 'Please fill all required fields correctly.';
                messageDiv.style.display = 'block';
                setTimeout(() => { messageDiv.style.display = 'none'; }, 5000);
                const firstInvalid = form.querySelector('.invalid');
                if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                button.disabled = false;
                button.innerText = 'Register →';
            }
        });
    </script>
</body>
</html>