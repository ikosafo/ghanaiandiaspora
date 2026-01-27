<?php
// admin-login.php
//session_start();
require '../config.php'; 

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic protection against timing attacks (compare strings in constant time)
    if (hash_equals($username, $admin_username) && hash_equals($password, $admin_password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_last_activity'] = time();
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Login | Ghana Diaspora</title>
    
    <!-- Google Fonts - Modern & Professional -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;       /* Blue - professional & trustworthy */
            --primary-dark: #1d4ed8;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --text-muted: #64748b;
            --error: #ef4444;
            --border: #e2e8f0;
            --shadow-sm: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: var(--card);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }

        .header {
            background: var(--primary);
            color: white;
            padding: 32px 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .form-content {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #f9fafb;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
            background: white;
        }

        .error-message {
            color: var(--error);
            font-size: 0.875rem;
            margin-top: 8px;
            display: block;
            min-height: 1.2em;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 12px;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .footer {
            text-align: center;
            padding: 24px;
            border-top: 1px solid var(--border);
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .form-content, .header {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="header">
            <img src="./assets/images/logo.png" alt="Ghana Diaspora Logo" style="height: 50px; margin-bottom: 16px;">
            <h1>Admin Portal</h1>
            <p>Secure access for administrators</p>
        </div>

        <div class="form-content">
            <?php if ($error): ?>
                <div class="error-message" style="color: var(--error); font-weight: 500; margin-bottom: 20px; text-align: center;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autocomplete="username"
                        placeholder="Enter your username"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="••••••••"
                    >
                </div>

                <button type="submit" name="login" class="btn-login">
                    Sign In
                </button>
            </form>
        </div>

        <div class="footer">
            &copy; <?php echo date('Y'); ?> Ghanaian Diaspora Union in Europe • All rights reserved
        </div>
    </div>

</body>
</html>