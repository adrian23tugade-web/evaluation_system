<?php
include __DIR__ . "/../db.php";

// Protect the dashboard
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | OLSHCO</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,800;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon:     #7a0000;
            --maroon-lt:  #c0392b;
            --cream:      #fdf8f0;
            --text:       #1c1108;
            --muted:      #7a6a55;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--text);
            margin: 0; padding: 0;
            display: flex; flex-direction: column; min-height: 100vh;
        }

        header {
            background: var(--maroon);
            padding: 20px 36px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin: 0;
        }

        .btn-logout {
            background: white;
            color: var(--maroon);
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.9rem;
            transition: 0.2s;
        }
        
        .btn-logout:hover {
            background: var(--cream);
        }

        main {
            flex: 1;
            padding: 50px 36px;
            text-align: center;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 30px rgba(122,0,0,0.05);
            max-width: 600px;
            margin: auto;
        }

        .card i {
            font-size: 4rem;
            color: var(--maroon);
            margin-bottom: 20px;
        }

        .card h2 {
            font-family: 'Playfair Display', serif;
            color: var(--maroon);
        }

        .card p {
            color: var(--muted);
            line-height: 1.6;
        }
    </style>
</head>
<body>

<header>
    <h1>Welcome, Admin <?php echo htmlspecialchars($_SESSION['admin_username'] ?? ''); ?></h1>
    <a href="admin_logout.php" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
</header>

<main>
    <div class="card">
        <i class="bi bi-tools"></i>
        <h2>Dashboard Under Construction</h2>
        <p>The system recognized your login successfully! We are currently building out the settings, user management, and evaluation controls for the admin panel.</p>
        <p>Please check back soon once the admin dashboard features go fully live.</p>
    </div>
</main>

</body>
</html>
