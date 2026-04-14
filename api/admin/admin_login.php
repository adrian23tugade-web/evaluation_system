<?php
include __DIR__ . "/../db.php";

// Already logged in? Go to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}



$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Please fill in all fields.";
    } else {
        // Fetch admin from DB
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $admin = $res->fetch_assoc();

            // Verify hashed password
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']        = $admin['id'];
                $_SESSION['admin_username']  = $admin['username'];
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | OLSHCO</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,800;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon:     #7a0000;
            --maroon-mid: #9b0000;
            --maroon-lt:  #c0392b;
            --gold:       #c9a84c;
            --gold-lt:    #e8c96d;
            --cream:      #fdf8f0;
            --cream-dark: #f3ead9;
            --text:       #1c1108;
            --muted:      #7a6a55;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ══════════ HEADER ══════════ */
        header {
            position: relative;
            background: var(--maroon);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 36px;
            height: 66px;
            box-shadow: 0 3px 20px rgba(122,0,0,0.4);
            z-index: 10;
        }
        header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-lt), var(--gold));
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .header-left img {
            width: 42px; height: 42px;
            object-fit: contain;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3));
        }
        .header-left h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            line-height: 1.2;
        }
        .header-left h1 span {
            display: block;
            font-size: 0.62rem;
            font-family: 'DM Sans', sans-serif;
            font-weight: 400;
            color: rgba(255,255,255,0.5);
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        /* ══════════ MAIN ══════════ */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid rgba(122,0,0,0.1);
            box-shadow: 0 8px 40px rgba(122,0,0,0.1);
            padding: 44px 40px 36px;
            width: 100%;
            max-width: 420px;
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--maroon), var(--gold), var(--maroon));
        }

        .login-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-lt));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 6px 20px rgba(122,0,0,0.3);
        }
        .login-icon i {
            font-size: 1.8rem;
            color: #fff;
        }

        .login-title {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-title h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--maroon);
            margin-bottom: 6px;
        }
        .login-title p {
            font-size: 0.8rem;
            color: var(--muted);
        }

        /* ══════════ FORM ══════════ */
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 7px;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 1rem;
            pointer-events: none;
            transition: color 0.2s;
        }
        .input-wrap input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.5px solid #e8e0d0;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            color: var(--text);
            background: var(--cream);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .input-wrap input:focus {
            border-color: var(--maroon);
            box-shadow: 0 0 0 3px rgba(122,0,0,0.08);
            background: #fff;
        }
        .input-wrap input:focus + i,
        .input-wrap:focus-within i {
            color: var(--maroon);
        }

        /* toggle password */
        .toggle-pw {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            font-size: 1rem;
            padding: 0;
            pointer-events: all;
            transition: color 0.2s;
        }
        .toggle-pw:hover { color: var(--maroon); }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-lt));
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: 0.5px;
            transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(122,0,0,0.3);
        }
        .btn-login:hover {
            opacity: 0.92;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(122,0,0,0.4);
        }
        .btn-login:active {
            transform: translateY(0);
        }

        /* ══════════ ERROR ══════════ */
        .alert-error {
            background: #fff0f0;
            border: 1px solid #f5c6c6;
            border-left: 4px solid #c0392b;
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 0.82rem;
            color: #7a0000;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            animation: shake 0.3s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25%       { transform: translateX(-6px); }
            75%       { transform: translateX(6px); }
        }

        /* ══════════ FOOTER ══════════ */
        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 0.74rem;
            color: var(--muted);
        }
        .login-footer i { color: var(--gold); }

        footer {
            text-align: center;
            padding: 16px;
            font-size: 0.74rem;
            color: var(--muted);
            border-top: 1px solid var(--cream-dark);
        }
    </style>
</head>
<body>

<header>
    <div class="header-left">
        <img src="../uploads/logo.png" alt="Logo"> <!-- ← FIXED -->
        <h1>
            OLSHCO Admin Panel
            <span>Faculty Evaluation System</span>
        </h1>
    </div>
</header>

<main>
    <div class="login-card">

        <div class="login-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <div class="login-title">
            <h2>Administrator Login</h2>
            <p>Restricted access — authorized personnel only</p>
        </div>

        <?php if ($error): ?>
        <div class="alert-error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <i class="bi bi-person-fill"></i>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        autocomplete="username"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="bi bi-lock-fill"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-pw" onclick="togglePw()" id="toggleBtn">
                        <i class="bi bi-eye-fill" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>
        </form>

        <div class="login-footer">
            <i class="bi bi-shield-check"></i> Secure login — session protected
        </div>

    </div>
</main>

<footer>
    &copy; <?php echo date('Y'); ?> OLSHCO Faculty Evaluation System. All rights reserved.
</footer>

<script>
function togglePw() {
    const input   = document.getElementById('password');
    const icon    = document.getElementById('eyeIcon');
    const showing = input.type === 'text';
    input.type    = showing ? 'password' : 'text';
    icon.className = showing ? 'bi bi-eye-fill' : 'bi bi-eye-slash-fill';
}
</script>

</body>
</html>