<?php
include "db.php";


if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { die("Missing evaluation ID."); }

$stmt = $conn->prepare("SELECT * FROM evaluation WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$d = $stmt->get_result()->fetch_assoc();
if (!$d) { die("Evaluation record #$id not found."); }

$teacher   = htmlspecialchars($d['teacher_name']);
$dept      = htmlspecialchars($d['department']    ?? '—');
$year      = htmlspecialchars($d['year_level']    ?? '—');
$sem       = htmlspecialchars($d['semester']      ?? '—');
$term      = htmlspecialchars($d['term']          ?? '—');
$feedback  = nl2br(htmlspecialchars($d['feedback']));
$sentiment = ucfirst(strtolower($d['sentiment'] ?? 'neutral'));

$r_comm      = (int)($d['rating_comm']     ?? 0);
$r_mastery   = (int)($d['rating_mastery']  ?? 0);
$r_punctual  = (int)($d['rating_punctual'] ?? 0);
$r_engage    = (int)($d['rating_engage']   ?? 0);
$r_fairness  = (int)($d['rating_fairness'] ?? 0);
$r_materials = (int)($d['rating_materials']?? 0);

$cats_vals = array_filter([$r_comm,$r_mastery,$r_punctual,$r_engage,$r_fairness,$r_materials]);
$avg = count($cats_vals) ? round(array_sum($cats_vals) / count($cats_vals), 2) : round((float)$d['rating'], 2);
$avg_full = (int)round($avg);

$stars_html = '';
for ($i = 1; $i <= 5; $i++) {
    $stars_html .= $i <= $avg_full ? '★' : '☆';
}

$date_gen   = date('F d, Y');
$date_sub   = !empty($d['date_submitted']) ? date('F d, Y', strtotime($d['date_submitted'])) : $date_gen;

$auto_print = isset($_GET['print']) && $_GET['print'] == '1';

$categories = [
    ['Communication & Delivery',  $r_comm,      ''],
    ['Mastery of Subject',        $r_mastery,   ''],
    ['Punctuality & Management',  $r_punctual,  ''],
    ['Student Engagement',        $r_engage,    ''],
    ['Fairness in Grading',       $r_fairness,  ''],
    ['Use of Teaching Materials', $r_materials, ''],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Report — <?php echo $teacher; ?> | OLSHCO</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --maroon: #7a0000;
            --gold:   #c9a84c;
            --cream:  #fdf8f0;
            --muted:  #7a6a55;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            background: #f5f0e8;
            color: #1c1108;
        }

        .toolbar {
            background: #7a0000;
            padding: 12px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }
        .toolbar span {
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .toolbar-btns { display: flex; gap: 10px; }
        .tbtn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px; border-radius: 8px; font-size: 0.82rem;
            font-weight: 700; cursor: pointer; border: none; text-decoration: none;
            transition: opacity 0.2s;
        }
        .tbtn:hover { opacity: 0.85; }
        .tbtn-print { background: #c9a84c; color: #fff; }
        .tbtn-back  { background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25); }

        .paper {
            max-width: 780px;
            margin: 24px auto;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 40px rgba(0,0,0,0.12);
        }

        .ph {
            background: linear-gradient(135deg, #7a0000 0%, #3d0000 100%);
            padding: 28px 36px 22px;
            position: relative;
        }
        .ph::after {
            content: '';
            position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #c9a84c, #e8c96d, #c9a84c);
        }
        .ph-logo-row {
            display: flex; align-items: center; gap: 14px; margin-bottom: 18px;
        }
        .ph-logo-circle {
            width: 48px; height: 48px; background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.3); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .ph-school { color: #fff; }
        .ph-school strong { font-size: 1rem; display: block; }
        .ph-school small  { font-size: 0.72rem; color: rgba(255,255,255,0.65); }

        .ph-main {
            display: flex; align-items: flex-start;
            justify-content: space-between; gap: 20px;
        }
        .ph-teacher-name {
            font-size: 1.7rem; font-weight: 900; color: #fff;
            line-height: 1.15; margin-bottom: 8px;
        }
        .ph-tags { display: flex; flex-wrap: wrap; gap: 6px; }
        .ph-tag {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.18);
            border-radius: 20px; padding: 3px 12px;
            font-size: 0.68rem; color: rgba(255,255,255,0.75);
        }

        .ph-rating-box { text-align: center; flex-shrink: 0; }
        .ph-rating-label { font-size: 0.58rem; text-transform: uppercase; letter-spacing: 2px; color: rgba(255,255,255,0.45); margin-bottom: 4px; }
        .ph-rating-num   { font-size: 3rem; font-weight: 900; color: #fff; line-height: 1; }
        .ph-rating-denom { font-size: 0.9rem; font-weight: 400; opacity: 0.5; }
        .ph-stars        { color: #e8c96d; font-size: 1.1rem; letter-spacing: 3px; margin-top: 4px; }

        .sec-label {
            font-size: 0.65rem; font-weight: 700; letter-spacing: 2px;
            text-transform: uppercase; color: #c9a84c;
            margin-bottom: 14px;
            display: flex; align-items: center; gap: 8px;
        }
        .sec-label::after {
            content: ''; flex: 1; height: 1px;
            background: linear-gradient(90deg, rgba(201,168,76,0.4), transparent);
        }

        .cat-section {
            background: #fdf8f0;
            border-bottom: 1px solid #f0ebe2;
            padding: 22px 32px;
        }
        .cat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .cat-card {
            background: #fff;
            border: 1.5px solid rgba(122,0,0,0.08);
            border-radius: 12px;
            padding: 14px 16px;
            position: relative; overflow: hidden;
        }
        .cat-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #7a0000, #c9a84c);
        }
        .cc-icon   { font-size: 1.2rem; margin-bottom: 4px; }
        .cc-name   { font-size: 0.62rem; font-weight: 700; color: #7a6a55; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
        .cc-score  { font-size: 1.7rem; font-weight: 900; color: #7a0000; line-height: 1; margin-bottom: 4px; }
        .cc-score small { font-size: 0.75rem; font-weight: 400; color: #7a6a55; }
        .cc-bar    { height: 5px; background: rgba(122,0,0,0.07); border-radius: 3px; overflow: hidden; margin-bottom: 5px; }
        .cc-fill   { height: 100%; background: linear-gradient(90deg, #7a0000, #c9a84c); border-radius: 3px; }
        .cc-stars  { font-size: 0.65rem; color: #c9a84c; letter-spacing: 2px; }

        .feedback-section { padding: 22px 32px; border-bottom: 1px solid #f0ebe2; }
        .feedback-box {
            background: #fdf8f0;
            border-left: 4px solid #7a0000;
            border-radius: 0 10px 10px 0;
            padding: 16px 20px;
            font-size: 0.88rem;
            font-style: italic;
            line-height: 1.75;
            color: #3a2a1a;
            margin-bottom: 14px;
        }
        .sentiment-row { display: flex; align-items: center; gap: 10px; font-size: 0.75rem; color: #7a6a55; }
        .badge {
            font-size: 0.72rem; font-weight: 700;
            padding: 3px 12px; border-radius: 20px;
        }
        .badge.positive { background: #e8f5ee; color: #1a7a3c; }
        .badge.negative { background: #fde8e8; color: #c0392b; }
        .badge.neutral  { background: #f0ebe2; color: #7a6a55; }

        .info-section { padding: 20px 32px; border-bottom: 1px solid #f0ebe2; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td {
            padding: 8px 14px;
            font-size: 0.82rem;
            border: 1px solid #ede6d8;
        }
        .info-table td:first-child {
            background: #fdf8f0;
            font-weight: 700;
            color: #7a0000;
            width: 38%;
        }

        .pf {
            background: #f3ead9;
            padding: 14px 32px;
            display: flex; align-items: center; justify-content: space-between;
            font-size: 0.7rem; color: #7a6a55;
        }
        .pf strong { color: #7a0000; }
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            html, body { background: #fff !important; }
            .toolbar { display: none !important; }
            .paper   { margin: 0 !important; border-radius: 0 !important; box-shadow: none !important; max-width: 100% !important; }
            @page { size: A4 portrait; margin: 10mm; }
        }
    </style>
</head>
<body>

<?php if ($auto_print): ?>
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 600));</script>
<?php endif; ?>

<div class="toolbar">
    <span> Evaluation Report — #EVL-<?php echo $id; ?></span>
    <div class="toolbar-btns">
        <a href="view_details.php?id=<?php echo $id; ?>" class="tbtn tbtn-back">← Back</a>
        <button onclick="window.print()" class="tbtn tbtn-print">Print / Save PDF</button>
    </div>
</div>

<div class="paper">

    <div class="ph">
        <div class="ph-logo-row">
            <img src="uploads/logo.png" alt="Logo">
            <div class="ph-school">
                <strong>Our Lady of the Sacred Heart College of Olongapo</strong>
                <small>Faculty Evaluation System — Official Report</small>
            </div>
        </div>
        <div class="ph-main">
            <div>
                <div class="ph-teacher-name"><?php echo $teacher; ?></div>
                <div class="ph-tags">
                    <span class="ph-tag"> <?php echo $dept; ?></span>
                    <span class="ph-tag"> <?php echo $year; ?></span>
                    <span class="ph-tag"> <?php echo $sem; ?></span>
                    <span class="ph-tag"> <?php echo $term; ?></span>
                </div>
            </div>
            <div class="ph-rating-box">
                <div class="ph-rating-label">Overall Rating</div>
                <div class="ph-rating-num"><?php echo number_format($avg, 2); ?><span class="ph-rating-denom"> / 5</span></div>
                <div class="ph-stars"><?php echo $stars_html; ?></div>
            </div>
        </div>
    </div>

    <div class="cat-section">
        <div class="sec-label">Category Breakdown</div>
        <div class="cat-grid">
            <?php foreach ($categories as [$label, $val, $emoji]):
                $pct   = $val > 0 ? ($val / 5) * 100 : 0;
                $stars = str_repeat('★', $val) . str_repeat('☆', 5 - $val);
            ?>
            <div class="cat-card">
                <div class="cc-icon"><?php echo $emoji; ?></div>
                <div class="cc-name"><?php echo $label; ?></div>
                <div class="cc-score"><?php echo $val ?: '—'; ?><small><?php echo $val ? ' / 5' : ''; ?></small></div>
                <div class="cc-bar"><div class="cc-fill" style="width:<?php echo $pct; ?>%"></div></div>
                <div class="cc-stars"><?php echo $stars; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="feedback-section">
        <div class="sec-label"> Student Feedback</div>
        <div class="feedback-box">"<?php echo $feedback; ?>"</div>
        <div class="sentiment-row">
            <span class="badge <?php echo strtolower($sentiment); ?>"><?php echo $sentiment; ?></span>
        </div>
    </div>

    <div class="info-section">
        <div class="sec-label"> Evaluation Details</div>
        <table class="info-table">
            <tr><td>Evaluation ID</td><td>#EVL-<?php echo $d['id']; ?></td></tr>
            <tr><td>Teacher</td><td><?php echo $teacher; ?></td></tr>
            <tr><td>Department</td><td><?php echo $dept; ?></td></tr>
            <tr><td>Year Level</td><td><?php echo $year; ?></td></tr>
            <tr><td>Semester</td><td><?php echo $sem; ?></td></tr>
            <tr><td>Term</td><td><?php echo $term; ?></td></tr>
            <tr><td>Date Submitted</td><td><?php echo $date_sub; ?></td></tr>
            <tr><td>Overall Rating</td><td><strong style="color:#7a0000;"><?php echo number_format($avg, 2); ?> / 5.00</strong> &nbsp; <?php echo $stars_html; ?></td></tr>
        </table>
    </div>

    <div class="pf">
        <span><strong>OLSHCO</strong> Faculty Evaluation System</span>
        <span>Generated on <?php echo $date_gen; ?></span>
        <span>Report #EVL-<?php echo $d['id']; ?></span>
    </div>

</div>

</body>
</html>