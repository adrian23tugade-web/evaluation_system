<?php
include "db.php";


if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

$back_url = isset($_GET['back']) ? $_GET['back'] : 'view.php';

if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM evaluation WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if(!$data){ die("Record not found."); }
} else {
    header("Location: view.php");
    exit();
}

// ── All 6 category ratings ──
$r_comm      = (int)($data['rating_comm']     ?? 0);
$r_mastery   = (int)($data['rating_mastery']  ?? 0);
$r_punctual  = (int)($data['rating_punctual'] ?? 0);
$r_engage    = (int)($data['rating_engage']   ?? 0);
$r_fairness  = (int)($data['rating_fairness'] ?? 0);
$r_materials = (int)($data['rating_materials']?? 0);

// ── Overall rating ──
$cats = array_filter([$r_comm,$r_mastery,$r_punctual,$r_engage,$r_fairness,$r_materials]);
$rating_raw  = count($cats) ? array_sum($cats) / count($cats) : (float)$data['rating'];
$rating      = round($rating_raw, 1);
$rating_full = (int)round($rating_raw);

$sentiment = strtolower($data['sentiment'] ?? 'neutral');

// Date
$date_str = '';
if(!empty($data['date_submitted'])){
    $ts = strtotime($data['date_submitted']);
    $date_str = $ts ? date('F d, Y', $ts) : $data['date_submitted'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Detail | OLSHCO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,800;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon:     #7a0000;
            --maroon-mid: #9b0000;
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
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: radial-gradient(circle, rgba(122,0,0,0.05) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none; z-index: 0;
        }

        /* ── HEADER ── */
        header {
            position: relative; z-index: 100;
            background: var(--maroon);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px; height: 66px;
            box-shadow: 0 4px 24px rgba(122,0,0,0.35);
        }
        header::after {
            content: ''; position: absolute;
            bottom: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-lt), var(--gold));
        }
        .header-inner { display: flex; align-items: center; gap: 13px; }
        .header-inner img { width: 42px; height: 42px; object-fit: contain; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.3)); }
        .header-inner h1 { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 600; color: #fff; }
        .user-chip {
            display: flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.18);
            border-radius: 24px; padding: 5px 14px 5px 10px;
            color: #fff; font-size: 0.8rem; font-weight: 500;
        }
        .user-chip i { color: var(--gold-lt); }

        /* ── WRAPPER ── */
        .wrapper {
            position: relative; z-index: 1;
            max-width: 780px;
            margin: 32px auto;
            padding: 0 20px 60px;
        }

        /* ── NAV ROW ── */
        .nav-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px; flex-wrap: wrap; gap: 10px;
        }
        .back-btn {
            display: inline-flex; align-items: center; gap: 7px;
            background: #fff; border: 1.5px solid rgba(122,0,0,0.15);
            color: var(--maroon); border-radius: 10px; padding: 8px 18px;
            font-size: 0.82rem; font-weight: 600; text-decoration: none;
            font-family: 'DM Sans', sans-serif; transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .back-btn:hover { background: var(--maroon); color: #fff; border-color: var(--maroon); }
        .record-badge {
            background: rgba(122,0,0,0.06); color: var(--maroon);
            border-radius: 20px; padding: 5px 14px;
            font-size: 0.72rem; font-weight: 700; letter-spacing: 0.8px;
        }

        /* ── DETAIL CARD ── */
        .detail-card {
            background: #fff;
            border-radius: 20px;
            border: 1.5px solid rgba(122,0,0,0.08);
            box-shadow: 0 4px 28px rgba(0,0,0,0.07);
            overflow: hidden;
        }

        /* ── HERO BANNER ── */
        .hero-banner {
            background: linear-gradient(135deg, var(--maroon) 0%, #3d0000 100%);
            padding: 28px 32px 24px;
            position: relative; overflow: hidden;
        }
        .hero-banner::before {
            content: '';
            position: absolute; top: -50px; right: -50px;
            width: 200px; height: 200px;
            border: 2px solid rgba(201,168,76,0.12); border-radius: 50%;
        }
        .hero-banner::after {
            content: '';
            position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-lt), var(--gold));
        }
        .hero-top {
            display: flex; align-items: flex-start;
            justify-content: space-between; gap: 20px;
            margin-bottom: 20px; flex-wrap: wrap;
        }
        .teacher-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem; font-weight: 800; color: #fff;
            line-height: 1.15; margin-bottom: 8px;
        }
        .meta-tags { display: flex; flex-wrap: wrap; gap: 7px; }
        .meta-tag {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
            border-radius: 20px; padding: 4px 12px;
            font-size: 0.71rem; color: rgba(255,255,255,0.75); font-weight: 500;
        }
        .meta-tag i { color: var(--gold-lt); font-size: 0.65rem; }

        /* Overall rating block */
        .overall-block { text-align: center; flex-shrink: 0; }
        .overall-block .ol-label  { font-size: 0.6rem; text-transform: uppercase; letter-spacing: 2px; color: rgba(255,255,255,0.45); margin-bottom: 4px; }
        .overall-block .ol-score  { font-family: 'Playfair Display', serif; font-size: 3.2rem; font-weight: 800; color: #fff; line-height: 1; }
        .overall-block .ol-denom  { font-size: 1rem; font-weight: 400; opacity: 0.5; }
        .overall-block .ol-stars  { color: var(--gold-lt); font-size: 1.1rem; letter-spacing: 3px; margin-top: 5px; }

        /* ── 6 CATEGORY BREAKDOWN ── */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            padding: 22px 32px;
            border-bottom: 1px solid #f0ebe2;
            background: #fdf8f0;
        }
        @media(max-width:560px){ .category-grid { grid-template-columns: repeat(2,1fr); } }

        .cat-card {
            background: #fff;
            border: 1.5px solid rgba(122,0,0,0.07);
            border-radius: 14px;
            padding: 14px 16px;
            position: relative; overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .cat-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--maroon), var(--gold));
        }
        .cat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(122,0,0,0.08); }
        .cat-card .cc-label  { font-size: 0.65rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px; }
        .cat-card .cc-score  { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 800; color: var(--maroon); line-height: 1; margin-bottom: 6px; }
        .cat-card .cc-score span { font-size: 0.8rem; font-weight: 400; color: var(--muted); }
        .cc-bar-track { height: 5px; background: rgba(122,0,0,0.07); border-radius: 3px; overflow: hidden; }
        .cc-bar-fill  { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--maroon), var(--gold)); }
        .cc-stars { font-size: 0.7rem; color: var(--gold); margin-top: 5px; letter-spacing: 2px; }

        /* ── CARD BODY ── */
        .card-body { padding: 24px 32px; }

        .section-label {
            font-size: 0.67rem; font-weight: 700; letter-spacing: 1.5px;
            text-transform: uppercase; color: var(--gold);
            margin-bottom: 12px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-label::after {
            content: ''; flex: 1; height: 1px;
            background: linear-gradient(90deg, rgba(201,168,76,0.3), transparent);
        }

        .feedback-box {
            background: var(--cream);
            border-left: 4px solid var(--maroon);
            border-radius: 0 12px 12px 0;
            padding: 18px 22px;
            font-size: 0.88rem;
            color: var(--text);
            line-height: 1.75;
            font-style: italic;
            margin-bottom: 20px;
        }

        .sentiment-row { display: flex; align-items: center; gap: 10px; }
        .sentiment-label { font-size: 0.75rem; color: var(--muted); font-weight: 600; }
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 0.74rem; font-weight: 700;
            padding: 4px 14px; border-radius: 20px;
        }
        .badge.positive { background: #e8f5ee; color: #1a7a3c; }
        .badge.negative { background: #fde8e8; color: #c0392b; }
        .badge.neutral  { background: #f0ebe2; color: var(--muted); }

        /* ── FOOTER ── */
        .card-footer {
            padding: 18px 32px;
            background: var(--cream);
            border-top: 1px solid #f0ebe2;
            display: flex; gap: 10px; flex-wrap: wrap;
        }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 22px; border-radius: 10px;
            font-family: 'DM Sans', sans-serif; font-size: 0.84rem; font-weight: 600;
            cursor: pointer; border: none; text-decoration: none; transition: all 0.22s;
        }
        .btn-back {
            background: #fff; color: var(--muted);
            border: 1.5px solid #e0d8cc;
        }
        .btn-back:hover { border-color: var(--maroon); color: var(--maroon); transform: translateY(-1px); }
        .btn-print {
            background: rgba(122,0,0,0.06); color: var(--maroon);
            border: 1.5px solid rgba(122,0,0,0.15);
        }
        .btn-print:hover { background: var(--maroon); color: #fff; border-color: var(--maroon); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(122,0,0,0.2); }
        .btn-pdf {
            background: var(--maroon); color: #fff;
            box-shadow: 0 3px 12px rgba(122,0,0,0.25);
        }
        .btn-pdf:hover { background: var(--maroon-mid); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(122,0,0,0.3); }

        /* ── PRINT STYLES ── */
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            html, body { height: auto; overflow: visible; }
            body { background: #fff !important; }
            body::before { display: none; }
            header, .nav-row, .card-footer { display: none !important; }
            .wrapper { margin: 0 !important; padding: 0 !important; max-width: 100% !important; }
            .detail-card { box-shadow: none !important; border: none !important; border-radius: 0 !important; }
            .hero-banner { border-radius: 0 !important; }
            .detail-card { page-break-inside: avoid; }
            @page { margin: 10mm; size: A4 portrait; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-inner">
        <img src="uploads/logo.png" alt="Logo">
        <h1>Evaluation Full Report</h1>
    </div>
    <div class="user-chip">
        <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
    </div>
</header>

<div class="wrapper">

    <!-- Nav row -->
    <div class="nav-row">
        <a href="<?php echo htmlspecialchars($back_url); ?>" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
        <span class="record-badge">#EVL-<?php echo $data['id']; ?></span>
    </div>

    <div class="detail-card">

        <!-- Hero Banner -->
        <div class="hero-banner">
            <div class="hero-top">
                <div>
                    <h2 class="teacher-name"><?php echo htmlspecialchars($data['teacher_name']); ?></h2>
                    <div class="meta-tags">
                        <span class="meta-tag"><i class="fas fa-building"></i> <?php echo htmlspecialchars($data['department'] ?? '—'); ?></span>
                        <span class="meta-tag"><i class="fas fa-user-graduate"></i> <?php echo htmlspecialchars($data['year_level'] ?? '—'); ?></span>
                        <span class="meta-tag"><i class="fas fa-calendar-check"></i> <?php echo htmlspecialchars($data['semester'] ?? '—'); ?></span>
                        <span class="meta-tag"><i class="fas fa-flag"></i> <?php echo htmlspecialchars($data['term'] ?? '—'); ?></span>
                        <?php if($date_str): ?>
                        <span class="meta-tag"><i class="far fa-calendar-alt"></i> <?php echo $date_str; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="overall-block">
                    <div class="ol-label">Overall Rating</div>
                    <div class="ol-score"><?php echo $rating; ?><span class="ol-denom"> / 5</span></div>
                    <div class="ol-stars">
                        <?php for($i=1;$i<=5;$i++) echo ($i<=$rating_full)?'★':'☆'; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6 Category Cards -->
        <div class="category-grid">
            <?php
            $cats_list = [
                ['Communication & Delivery',   $r_comm,      'fas fa-comments'],
                ['Mastery of Subject',          $r_mastery,   'fas fa-book'],
                ['Punctuality & Management',    $r_punctual,  'fas fa-clock'],
                ['Student Engagement',          $r_engage,    'fas fa-users'],
                ['Fairness in Grading',         $r_fairness,  'fas fa-balance-scale'],
                ['Use of Teaching Materials',   $r_materials, 'fas fa-chalkboard'],
            ];
            foreach($cats_list as [$label, $val, $icon]):
                $pct = $val > 0 ? ($val/5)*100 : 0;
                $stars = str_repeat('★', $val) . str_repeat('☆', 5-$val);
            ?>
            <div class="cat-card">
                <div class="cc-label"><i class="<?php echo $icon; ?>" style="color:var(--gold);margin-right:4px;"></i><?php echo $label; ?></div>
                <div class="cc-score"><?php echo $val ?: '—'; ?><span><?php echo $val ? ' / 5' : ''; ?></span></div>
                <div class="cc-bar-track"><div class="cc-bar-fill" style="width:<?php echo $pct; ?>%"></div></div>
                <div class="cc-stars"><?php echo $stars; ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Feedback -->
        <div class="card-body">
            <div class="section-label"><i class="fas fa-comment-dots"></i> Student Feedback</div>
            <div class="feedback-box">
                "<?php echo nl2br(htmlspecialchars($data['feedback'])); ?>"
            </div>
            <div class="sentiment-row">
                <span class="sentiment-label"><i class="fas fa-robot"></i>&nbsp; AI Sentiment:</span>
                <span class="badge <?php echo $sentiment; ?>"><?php echo ucfirst($sentiment); ?></span>
            </div>
        </div>

        <!-- Footer — Print and PDF are SEPARATE buttons -->
        <div class="card-footer">
            <a href="<?php echo htmlspecialchars($back_url); ?>" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <button onclick="window.print()" class="btn btn-print">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="generate_pdf.php?id=<?php echo $data['id']; ?>" target="_blank" class="btn btn-pdf">
                <i class="fas fa-file-pdf"></i> Save as PDF
            </a>
        </div>

    </div>
</div>

</body>
</html>