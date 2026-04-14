<?php
include "db.php";

if(isset($_POST['register'])){
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = md5($_POST['password']);

    $sql = "INSERT INTO users(fullname, username, email, password) VALUES('$fullname', '$username', '$email', '$password')";
    
    if($conn->query($sql)){
        header("Location: login.php?msg=registered");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Us | OLSHCO</title>
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

        /* ── DECORATIVE BACKGROUND ── */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        /* Large maroon panel on the left */
        .bg-layer::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 46%;
            height: 100%;
            background: linear-gradient(160deg, var(--maroon) 60%, #4a0000 100%);
            clip-path: polygon(0 0, 100% 0, 82% 100%, 0 100%);
        }

        /* Gold accent strip */
        .bg-layer::after {
            content: '';
            position: absolute;
            top: 0; left: calc(46% - 6px);
            width: 6px;
            height: 100%;
            background: linear-gradient(180deg, var(--gold-lt), var(--gold), var(--gold-lt));
            transform: skewX(-6deg);
            opacity: 0.7;
        }

        /* Decorative circles on maroon side */
        .deco-circle {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(201,168,76,0.15);
            pointer-events: none;
        }
        .deco-circle-1 { width: 320px; height: 320px; top: -80px; left: -80px; }
        .deco-circle-2 { width: 200px; height: 200px; bottom: 60px; left: 30px; border-color: rgba(201,168,76,0.2); }
        .deco-circle-3 { width: 80px; height: 80px; top: 45%; left: 28%; border-color: rgba(255,255,255,0.1); }

        /* Grain texture overlay */
        .grain {
            position: fixed;
            inset: 0;
            z-index: 1;
            opacity: 0.025;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
            background-size: 200px;
            pointer-events: none;
        }

        /* ── LEFT PANEL CONTENT ── */
        .left-panel {
            position: fixed;
            top: 0; left: 0;
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

        .left-panel h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.25;
            margin-bottom: 12px;
            letter-spacing: -0.01em;
        }

        .left-panel h1 em {
            font-style: italic;
            color: var(--gold-lt);
        }

        .left-divider {
            width: 48px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            margin: 16px auto;
        }

        .left-panel p {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.5);
            line-height: 1.7;
            max-width: 240px;
        }

        .left-badge {
            margin-top: 36px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(201,168,76,0.12);
            border: 1px solid rgba(201,168,76,0.25);
            border-radius: 30px;
            padding: 6px 16px;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--gold-lt);
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        /* ── MAIN CARD ── */
        .auth-wrapper {
            position: relative;
            z-index: 3;
            margin-left: 42%;
            width: 58%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 48px;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
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

        /* ── FORM FIELDS ── */
        .field {
            margin-bottom: 16px;
            animation: fadeIn 0.5s ease both;
        }
        .field:nth-child(1) { animation-delay: 0.05s; }
        .field:nth-child(2) { animation-delay: 0.10s; }
        .field:nth-child(3) { animation-delay: 0.15s; }
        .field:nth-child(4) { animation-delay: 0.20s; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(10px); }
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

        .field-inner i {
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

        .field-inner input:focus + i,
        .field-inner:focus-within i {
            color: var(--maroon);
        }

        /* Fix icon order — icon is after input in DOM but positioned absolute */
        .field-inner input:focus ~ i { color: var(--maroon); }

        /* ── PASSWORD TOGGLE ── */
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

        /* ── FOOTER LINK ── */
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

        /* ── STRENGTH METER ── */
        .strength-bar {
            display: flex;
            gap: 4px;
            margin-top: 8px;
        }
        .strength-seg {
            flex: 1;
            height: 3px;
            border-radius: 2px;
            background: var(--cream-dark);
            transition: background 0.3s;
        }
        .strength-label {
            font-size: 0.68rem;
            color: var(--muted);
            margin-top: 4px;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 700px) {
            .left-panel { display: none; }
            .bg-layer::before { display: none; }
            .bg-layer::after { display: none; }
            .auth-wrapper { margin-left: 0; width: 100%; padding: 30px 24px; }
            .auth-card { max-width: 100%; }
        }
    </style>
</head>
<body>

<!-- Background -->
<div class="bg-layer">
    <div class="deco-circle deco-circle-1"></div>
    <div class="deco-circle deco-circle-2"></div>
    <div class="deco-circle deco-circle-3"></div>
</div>
<div class="grain"></div>

<!-- Left Panel -->
<div class="left-panel">
    <img src="uploads/logo.png" alt="OLSHCO Logo" class="school-logo">
    <h1>Our Lady of the<br><em>Sacred Heart</em><br>College</h1>
    <div class="left-divider"></div>
    <p>Join the Faculty Evaluation System and help shape the quality of education.</p>
    <div class="left-badge">
        <i class="fas fa-shield-alt"></i> Secure Portal
    </div>
</div>

<!-- Auth Card -->
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="card-eyebrow">New Account</div>
        <h2 class="card-title">Create Account</h2>
        <p class="card-subtitle">Fill in your details to get started.</p>

        <form method="POST" id="registerForm">

            <div class="field">
                <label for="fullname">Full Name</label>
                <div class="field-inner">
                    <input type="text" id="fullname" name="fullname" placeholder="e.g. Juan dela Cruz" required>
                    <i class="fas fa-user-tag"></i>
                </div>
            </div>

            <div class="field">
                <label for="username">Username</label>
                <div class="field-inner">
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                    <i class="fas fa-at"></i>
                </div>
            </div>

            <div class="field">
                <label for="email">Email Address</label>
                <div class="field-inner">
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="field-inner">
                    <input type="password" id="password" name="password" placeholder="Create a strong password" required oninput="checkStrength(this.value)">
                    <i class="fas fa-lock"></i>
                    <button type="button" class="toggle-pw" onclick="togglePw()" id="toggleBtn">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <div class="strength-bar">
                    <div class="strength-seg" id="seg1"></div>
                    <div class="strength-seg" id="seg2"></div>
                    <div class="strength-seg" id="seg3"></div>
                    <div class="strength-seg" id="seg4"></div>
                </div>
                <div class="strength-label" id="strengthLabel"></div>
            </div>

            <button name="register" class="submit-btn" type="submit">
                <i class="fas fa-user-plus"></i>
                <span>Create My Account</span>
            </button>

        </form>

        <p class="auth-footer">Already have an account? <a href="login.php">Sign in here</a></p>

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

    function checkStrength(val) {
        const segs   = [1,2,3,4].map(i => document.getElementById('seg'+i));
        const label  = document.getElementById('strengthLabel');
        const colors = ['#c0392b','#e67e22','#f1c40f','#27ae60'];
        const labels = ['Weak','Fair','Good','Strong'];

        let score = 0;
        if (val.length >= 8)              score++;
        if (/[A-Z]/.test(val))            score++;
        if (/[0-9]/.test(val))            score++;
        if (/[^A-Za-z0-9]/.test(val))    score++;

        segs.forEach((s, i) => {
            s.style.background = i < score ? colors[score - 1] : 'var(--cream-dark)';
        });
        label.textContent = val.length > 0 ? labels[score - 1] || '' : '';
        label.style.color  = score > 0 ? colors[score - 1] : 'var(--muted)';
    }
</script>

</body>
</html>