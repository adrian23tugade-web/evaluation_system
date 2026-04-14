<?php
include "db.php";


if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['username'];
        header("Location: index.php");
    } else {
        $error = "Invalid username or password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | OLSHCO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;0,900;1,500&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* ── BACKGROUND ── */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        /* Right-side maroon panel (mirrored from register) */
        .bg-layer::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 46%;
            height: 100%;
            background: linear-gradient(200deg, var(--maroon) 60%, #4a0000 100%);
            clip-path: polygon(18% 0, 100% 0, 100% 100%, 0 100%);
        }

        .bg-layer::after {
            content: '';
            position: absolute;
            top: 0; right: calc(46% - 6px);
            width: 6px;
            height: 100%;
            background: linear-gradient(180deg, var(--gold-lt), var(--gold), var(--gold-lt));
            transform: skewX(-6deg);
            opacity: 0.7;
        }

        .deco-circle {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(201,168,76,0.15);
            pointer-events: none;
        }
        .deco-circle-1 { width: 360px; height: 360px; top: -100px; right: -80px; }
        .deco-circle-2 { width: 180px; height: 180px; bottom: 80px; right: 50px; border-color: rgba(201,168,76,0.2); }
        .deco-circle-3 { width: 70px; height: 70px; top: 40%; right: 29%; border-color: rgba(255,255,255,0.1); }

        /* Subtle diagonal lines on cream side */
        .deco-lines {
            position: absolute;
            top: 0; left: 0;
            width: 54%;
            height: 100%;
            background-image: repeating-linear-gradient(
                -55deg,
                transparent,
                transparent 40px,
                rgba(122,0,0,0.025) 40px,
                rgba(122,0,0,0.025) 41px
            );
            pointer-events: none;
        }

        .grain {
            position: fixed;
            inset: 0;
            z-index: 1;
            opacity: 0.025;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
            background-size: 200px;
            pointer-events: none;
        }

        .right-panel {
            position: fixed;
            top: 0; right: 0;
            width: 42%;
            height: 100%;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            text-align: center;
        }

        .school-logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            filter: drop-shadow(0 4px 20px rgba(0,0,0,0.4)) brightness(1.05);
            margin-bottom: 24px;
            animation: floatLogo 4s ease-in-out infinite;
        }

        @keyframes floatLogo {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }

        .right-panel h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.25;
            margin-bottom: 12px;
        }
        .right-panel h1 em {
            font-style: italic;
            color: var(--gold-lt);
        }

        .right-divider {
            width: 48px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            margin: 16px auto;
        }

        .right-panel p {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.5);
            line-height: 1.7;
            max-width: 240px;
        }

        .panel-stat {
            margin-top: 36px;
            display: flex;
            gap: 20px;
        }
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }
        .stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--gold-lt);
        }
        .stat-lbl {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
        }
        .stat-divider {
            width: 1px;
            background: rgba(255,255,255,0.1);
            align-self: stretch;
        }

        /* ── AUTH CARD ── */
        .auth-wrapper {
            position: relative;
            z-index: 3;
            width: 58%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 48px;
        }

        .auth-card {
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.6s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .card-eyebrow {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 10px;
        }
        .card-eyebrow::before {
            content: '';
            display: block;
            width: 20px;
            height: 2px;
            background: var(--gold);
            border-radius: 2px;
        }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--maroon);
            line-height: 1.1;
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }

        .card-subtitle {
            font-size: 0.84rem;
            color: var(--muted);
            margin-bottom: 32px;
        }

        /* ── ERROR MESSAGE ── */
        .error-msg {
            display: flex;
            align-items: center;
            gap: 9px;
            background: #fff0f0;
            border: 1.5px solid rgba(192,57,43,0.2);
            border-left: 4px solid var(--maroon-lt);
            border-radius: 10px;
            padding: 11px 15px;
            font-size: 0.83rem;
            color: var(--maroon-lt);
            font-weight: 500;
            margin-bottom: 20px;
            animation: shake 0.4s ease;
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%      { transform: translateX(-6px); }
            40%      { transform: translateX(6px); }
            60%      { transform: translateX(-4px); }
            80%      { transform: translateX(4px); }
        }

        /* ── SUCCESS MESSAGE ── */
        .success-msg {
            display: flex;
            align-items: center;
            gap: 9px;
            background: #f0fff5;
            border: 1.5px solid rgba(39,174,96,0.2);
            border-left: 4px solid #27ae60;
            border-radius: 10px;
            padding: 11px 15px;
            font-size: 0.83rem;
            color: #1a7a3c;
            font-weight: 500;
            margin-bottom: 20px;
        }

        /* ── FIELDS ── */
        .field {
            margin-bottom: 16px;
            animation: fadeIn 0.5s ease both;
        }
        .field:nth-child(1) { animation-delay: 0.05s; }
        .field:nth-child(2) { animation-delay: 0.12s; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(-10px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .field label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--maroon);
            margin-bottom: 7px;
        }

        .field-inner {
            position: relative;
            display: flex;
            align-items: center;
        }

        .field-inner i.field-icon {
            position: absolute;
            left: 14px;
            font-size: 0.85rem;
            color: var(--muted);
            transition: color 0.2s;
            pointer-events: none;
            z-index: 1;
        }

        .field-inner input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1.5px solid var(--cream-dark);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            color: var(--text);
            background: #fff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .field-inner input::placeholder { color: #c5b89a; }

        .field-inner input:focus {
            border-color: var(--maroon);
            box-shadow: 0 0 0 3px rgba(122,0,0,0.07);
        }

        .field-inner:focus-within .field-icon { color: var(--maroon); }

        .toggle-pw {
            position: absolute;
            right: 13px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.82rem;
            color: var(--muted);
            padding: 4px;
            transition: color 0.2s;
            z-index: 2;
        }
        .toggle-pw:hover { color: var(--maroon); }

        /* ── FORGOT ── */
        .field-meta {
            display: flex;
            justify-content: flex-end;
            margin-top: 6px;
        }
        .forgot-link {
            font-size: 0.75rem;
            color: var(--muted);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .forgot-link:hover { color: var(--maroon); }

        /* ── SUBMIT ── */
        .submit-btn {
            width: 100%;
            margin-top: 8px;
            padding: 14px;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-mid));
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 0.2px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(122,0,0,0.28);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.08), transparent);
            pointer-events: none;
        }

        .submit-btn::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), var(--gold-lt), var(--gold));
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(122,0,0,0.35);
        }
        .submit-btn:active { transform: translateY(0); }

        .auth-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 0.82rem;
            color: var(--muted);
        }
        .auth-footer a {
            color: var(--maroon);
            font-weight: 700;
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: border-color 0.2s;
        }
        .auth-footer a:hover { border-color: var(--maroon); }

        /* ── RESPONSIVE ── */
        @media (max-width: 700px) {
            .right-panel { display: none; }
            .bg-layer::before, .bg-layer::after { display: none; }
            .auth-wrapper { width: 100%; padding: 30px 24px; }
            .auth-card { max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="bg-layer">
    <div class="deco-lines"></div>
    <div class="deco-circle deco-circle-1"></div>
    <div class="deco-circle deco-circle-2"></div>
    <div class="deco-circle deco-circle-3"></div>
</div>
<div class="grain"></div>

<!-- Right Panel -->
<div class="right-panel">
    <img src="uploads/logo.png" alt="OLSHCO Logo" class="school-logo">
    <h1>Our Lady of the<br><em>Sacred Heart</em><br>College</h1>
    <div class="right-divider"></div>
    <p>Faculty Evaluation System — empowering students to shape academic excellence.</p>
    <div class="panel-stat">
        <div class="stat-item">
            <span class="stat-num">5</span>
            <span class="stat-lbl">Depts</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num">3</span>
            <span class="stat-lbl">Terms</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num">★5</span>
            <span class="stat-lbl">Rating</span>
        </div>
    </div>
</div>

<!-- Auth Card -->
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="card-eyebrow">Student Portal</div>
        <h2 class="card-title">Welcome Back</h2>
        <p class="card-subtitle">Sign in to your account to continue.</p>

        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'registered'): ?>
        <div class="success-msg">
            <i class="fas fa-check-circle"></i>
            Account created successfully! You can now log in.
        </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
        <div class="error-msg">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST">

            <div class="field">
                <label for="username">Username</label>
                <div class="field-inner">
                    <input type="text" id="username" name="username"
                           placeholder="Enter your username" required
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <i class="fas fa-user field-icon"></i>
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="field-inner">
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password" required>
                    <i class="fas fa-lock field-icon"></i>
                    <button type="button" class="toggle-pw" onclick="togglePw()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <div class="field-meta">
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>
            </div>

            <button name="login" class="submit-btn" type="submit">
                <i class="fas fa-sign-in-alt"></i>
                <span>Sign In</span>
            </button>

        </form>

        <p class="auth-footer">Don't have an account? <a href="register.php">Create one here</a></p>

    </div>
</div>

<script>
    function togglePw() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
</script>

</body>
</html>