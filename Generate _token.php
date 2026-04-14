<?php
/**
 * generate_pdf.php
 * ─────────────────────────────────────────────────────────────────────────────
 * AUTO-DOWNLOADS a real PDF to File Explorer using jsPDF + html2canvas.
 * Zero PHP libraries. Zero Composer. Works on XAMPP out of the box.
 *
 * URL params:
 *   ?id=5           → opens page and auto-downloads PDF
 *   ?id=5&print=1   → opens page and auto-triggers Print dialog
 * ─────────────────────────────────────────────────────────────────────────────
 */

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

// ── Data ──────────────────────────────────────────────────────────────────────
$teacher   = htmlspecialchars($d['teacher_name']);
$dept      = htmlspecialchars($d['department']    ?? '—');
$year      = htmlspecialchars($d['year_level']    ?? '—');
$sem       = htmlspecialchars($d['semester']      ?? '—');
$term      = htmlspecialchars($d['term']          ?? '—');
$feedback  = htmlspecialchars($d['feedback']);
$sentiment = ucfirst(strtolower($d['sentiment'] ?? 'neutral'));

$r_comm      = (int)($d['rating_comm']     ?? 0);
$r_mastery   = (int)($d['rating_mastery']  ?? 0);
$r_punctual  = (int)($d['rating_punctual'] ?? 0);
$r_engage    = (int)($d['rating_engage']   ?? 0);
$r_fairness  = (int)($d['rating_fairness'] ?? 0);
$r_materials = (int)($d['rating_materials']?? 0);

$vals    = array_filter([$r_comm,$r_mastery,$r_punctual,$r_engage,$r_fairness,$r_materials]);
$avg     = count($vals) ? round(array_sum($vals)/count($vals), 2) : round((float)$d['rating'], 2);
$avg_int = (int)round($avg);
$stars   = str_repeat('★', $avg_int) . str_repeat('☆', 5 - $avg_int);

$date_gen = date('F d, Y');
$date_sub = !empty($d['date_submitted']) ? date('F d, Y', strtotime($d['date_submitted'])) : $date_gen;
$mode     = isset($_GET['print']) && $_GET['print'] == '1' ? 'print' : 'pdf';
$filename = "Evaluation_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $d['teacher_name']) . "_EVL{$id}.pdf";

$categories = [
    ['Communication & Delivery',  $r_comm,      '📢'],
    ['Mastery of Subject',        $r_mastery,   '📚'],
    ['Punctuality & Management',  $r_punctual,  '⏰'],
    ['Student Engagement',        $r_engage,    '🤝'],
    ['Fairness in Grading',       $r_fairness,  '⚖️'],
    ['Use of Teaching Materials', $r_materials, '📋'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Evaluation Report — <?php echo $teacher; ?></title>

<!-- jsPDF + html2canvas (CDN, no install needed) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
/* ── Reset ── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
    --maroon:#7a0000;
    --gold:#c9a84c;
    --gold-lt:#e8c96d;
    --cream:#fdf8f0;
    --muted:#7a6a55;
}

body{
    font-family:Arial,Helvetica,sans-serif;
    background:#e6ddd0;
    min-height:100vh;
}

/* ── Toolbar (never in PDF) ── */
.toolbar{
    background:#7a0000;
    padding:10px 28px;
    display:flex;align-items:center;
    justify-content:space-between;gap:12px;flex-wrap:wrap;
}
.toolbar-title{color:#fff;font-size:.85rem;font-weight:700}
.toolbar-btns{display:flex;gap:8px;flex-wrap:wrap}
.tbtn{
    display:inline-flex;align-items:center;gap:6px;
    padding:8px 18px;border-radius:8px;
    font-size:.8rem;font-weight:700;
    cursor:pointer;border:none;text-decoration:none;
    font-family:Arial,sans-serif;transition:opacity .2s;
}
.tbtn:hover{opacity:.85}
.tbtn-pdf  {background:#c9a84c;color:#fff}
.tbtn-print{background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.3)}
.tbtn-back {background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2)}

/* ── PDF generating overlay ── */
#pdf-status{
    display:none;
    position:fixed;inset:0;
    background:rgba(122,0,0,.85);
    z-index:9999;
    flex-direction:column;align-items:center;justify-content:center;gap:18px;
    color:#fff;font-family:Arial,sans-serif;
}
#pdf-status.show{display:flex}
.spinner{
    width:54px;height:54px;
    border:5px solid rgba(255,255,255,.2);
    border-top-color:#e8c96d;
    border-radius:50%;
    animation:spin .8s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}
#pdf-status h3{font-size:1.1rem;font-weight:700}
#pdf-status p {font-size:.78rem;opacity:.7}

/* ── Paper wrapper ── */
.paper-wrap{padding:24px 20px 60px}
#pdf-paper{
    max-width:800px;
    margin:0 auto;
    background:#fff;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 8px 48px rgba(0,0,0,.15);
}

/* ── Header band ── */
.ph{
    background:linear-gradient(135deg,#7a0000 0%,#3d0000 100%);
    padding:26px 32px 24px;
    position:relative;overflow:hidden;
}
.ph-deco1{
    position:absolute;top:-50px;right:-50px;
    width:200px;height:200px;
    border:2px solid rgba(201,168,76,.1);border-radius:50%;pointer-events:none;
}
.ph-deco2{
    position:absolute;bottom:-40px;left:40px;
    width:140px;height:140px;
    border:1.5px solid rgba(255,255,255,.05);border-radius:50%;pointer-events:none;
}
.ph-accent{
    height:3px;
    background:linear-gradient(90deg,#c9a84c,#e8c96d,#c9a84c);
    margin-bottom:20px;border-radius:2px;
}
.ph-logo-row{display:flex;align-items:center;gap:12px;margin-bottom:18px}
.ph-logo{
    width:46px;height:46px;
    background:rgba(255,255,255,.12);
    border:2px solid rgba(255,255,255,.25);
    border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:1.35rem;flex-shrink:0;
}
.ph-school strong{display:block;color:#fff;font-size:.9rem}
.ph-school small {display:block;color:rgba(255,255,255,.5);font-size:.68rem;margin-top:2px}
.ph-main{display:flex;align-items:flex-start;justify-content:space-between;gap:20px}
.ph-left{flex:1;min-width:0}
.ph-teacher{
    font-size:1.6rem;font-weight:900;color:#fff;
    line-height:1.2;margin-bottom:10px;word-break:break-word;
}
.ph-tags{display:flex;flex-wrap:wrap;gap:6px}
.ph-tag{
    background:rgba(255,255,255,.1);
    border:1px solid rgba(255,255,255,.18);
    border-radius:20px;padding:3px 11px;
    font-size:.66rem;color:rgba(255,255,255,.75);white-space:nowrap;
}
.ph-score{text-align:center;flex-shrink:0}
.ph-score-lbl  {font-size:.54rem;text-transform:uppercase;letter-spacing:2px;color:rgba(255,255,255,.4);margin-bottom:3px}
.ph-score-num  {font-size:2.7rem;font-weight:900;color:#fff;line-height:1}
.ph-score-denom{font-size:.82rem;font-weight:400;opacity:.4}
.ph-score-stars{color:#e8c96d;font-size:.95rem;letter-spacing:3px;margin-top:4px}

/* ── Section ── */
.section{padding:20px 32px;border-bottom:1px solid #f0ebe2}
.section:last-child{border-bottom:none}
.sec-label{
    font-size:.6rem;font-weight:700;letter-spacing:2px;
    text-transform:uppercase;color:#c9a84c;margin-bottom:14px;
    display:flex;align-items:center;gap:8px;
}
.sec-label::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,rgba(201,168,76,.35),transparent)}

/* ── Category grid ── */
.cat-section{background:#fdf8f0}
.cat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.cat-card{
    background:#fff;border:1.5px solid rgba(122,0,0,.08);
    border-radius:12px;padding:13px 15px;
    position:relative;overflow:hidden;
}
.cat-card::before{
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:linear-gradient(90deg,#7a0000,#c9a84c);
}
.cc-icon {font-size:1.05rem;margin-bottom:4px}
.cc-name {font-size:.58rem;font-weight:700;color:#7a6a55;text-transform:uppercase;letter-spacing:.8px;margin-bottom:5px;line-height:1.3}
.cc-score{font-size:1.55rem;font-weight:900;color:#7a0000;line-height:1;margin-bottom:4px}
.cc-score small{font-size:.7rem;font-weight:400;color:#7a6a55}
.cc-bar {height:5px;background:rgba(122,0,0,.08);border-radius:3px;overflow:hidden;margin-bottom:5px}
.cc-fill{height:100%;background:linear-gradient(90deg,#7a0000,#c9a84c);border-radius:3px}
.cc-stars{font-size:.6rem;color:#c9a84c;letter-spacing:2px}

/* ── Feedback ── */
.feedback-box{
    background:#fdf8f0;
    border-left:4px solid #7a0000;
    border-radius:0 10px 10px 0;
    padding:16px 20px;
    font-size:.85rem;font-style:italic;
    line-height:1.75;color:#3a2a1a;margin-bottom:12px;
}
.sentiment-row{display:flex;align-items:center;gap:10px;font-size:.73rem;color:#7a6a55}
.badge{font-size:.68rem;font-weight:700;padding:3px 12px;border-radius:20px}
.badge.positive{background:#e8f5ee;color:#1a7a3c}
.badge.negative{background:#fde8e8;color:#c0392b}
.badge.neutral {background:#f0ebe2;color:#7a6a55}

/* ── Info table ── */
.info-table{width:100%;border-collapse:collapse;font-size:.81rem}
.info-table td{padding:8px 12px;border:1px solid #ede6d8}
.info-table td:first-child{background:#fdf8f0;font-weight:700;color:#7a0000;width:36%}

/* ── Footer ── */
.pf{
    background:linear-gradient(90deg,#f3ead9,#ede3cc);
    padding:13px 32px;
    display:flex;align-items:center;justify-content:space-between;
    font-size:.67rem;color:#7a6a55;
}
.pf strong{color:#7a0000}

/* ── Print mode ── */
@media print{
    *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
    html,body{background:#fff!important}
    .toolbar,#pdf-status{display:none!important}
    .paper-wrap{padding:0!important}
    #pdf-paper{border-radius:0!important;box-shadow:none!important;max-width:100%!important}
    @page{size:A4 portrait;margin:8mm}
}
</style>
</head>
<body>

<!-- Generating overlay -->
<div id="pdf-status">
    <div class="spinner"></div>
    <h3>Generating PDF…</h3>
    <p>Saving to your Downloads folder automatically.</p>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <span class="toolbar-title">📄 Evaluation Report &nbsp;·&nbsp; #EVL-<?php echo $id; ?> &nbsp;·&nbsp; <?php echo $teacher; ?></span>
    <div class="toolbar-btns">
        <a href="view_details.php?id=<?php echo $id; ?>" class="tbtn tbtn-back">← Back</a>
        <button onclick="doPrint()" class="tbtn tbtn-print">🖨️ Print</button>
        <button onclick="downloadPDF()" class="tbtn tbtn-pdf">⬇️ Save as PDF</button>
    </div>
</div>

<!-- ═══ PAPER — this is what gets captured into the PDF ═══ -->
<div class="paper-wrap">
<div id="pdf-paper">

    <!-- Header -->
    <div class="ph">
        <div class="ph-deco1"></div>
        <div class="ph-deco2"></div>
        <div class="ph-accent"></div>
        <div class="ph-logo-row">
            <div class="ph-logo">🏫</div>
            <div class="ph-school">
                <strong>Our Lady of the Sacred Heart College of Olongapo</strong>
                <small>Faculty Evaluation System &nbsp;·&nbsp; Official Report</small>
            </div>
        </div>
        <div class="ph-main">
            <div class="ph-left">
                <div class="ph-teacher"><?php echo $teacher; ?></div>
                <div class="ph-tags">
                    <span class="ph-tag">🏛 <?php echo $dept; ?></span>
                    <span class="ph-tag">🎓 <?php echo $year; ?></span>
                    <span class="ph-tag">📅 <?php echo $sem; ?></span>
                    <span class="ph-tag">🚩 <?php echo $term; ?></span>
                    <span class="ph-tag">📆 <?php echo $date_sub; ?></span>
                </div>
            </div>
            <div class="ph-score">
                <div class="ph-score-lbl">Overall Rating</div>
                <div class="ph-score-num"><?php echo number_format($avg,2); ?><span class="ph-score-denom"> / 5</span></div>
                <div class="ph-score-stars"><?php echo $stars; ?></div>
            </div>
        </div>
    </div>

    <!-- 6 Categories -->
    <div class="section cat-section">
        <div class="sec-label">📊 Category Breakdown</div>
        <div class="cat-grid">
            <?php foreach($categories as [$label,$val,$emoji]):
                $pct = $val > 0 ? ($val/5)*100 : 0;
                $ss  = str_repeat('★',$val).str_repeat('☆',5-$val);
            ?>
            <div class="cat-card">
                <div class="cc-icon"><?php echo $emoji; ?></div>
                <div class="cc-name"><?php echo $label; ?></div>
                <div class="cc-score"><?php echo $val ?: '—'; ?><small><?php echo $val?' / 5':''; ?></small></div>
                <div class="cc-bar"><div class="cc-fill" style="width:<?php echo $pct; ?>%"></div></div>
                <div class="cc-stars"><?php echo $ss; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Feedback -->
    <div class="section">
        <div class="sec-label">💬 Student Feedback</div>
        <div class="feedback-box">"<?php echo nl2br($feedback); ?>"</div>
        <div class="sentiment-row">
            🤖 AI Sentiment:
            <span class="badge <?php echo strtolower($sentiment); ?>"><?php echo $sentiment; ?></span>
        </div>
    </div>

    <!-- Details table -->
    <div class="section">
        <div class="sec-label">📋 Evaluation Details</div>
        <table class="info-table">
            <tr><td>Evaluation ID</td><td>#EVL-<?php echo $d['id']; ?></td></tr>
            <tr><td>Teacher</td><td><?php echo $teacher; ?></td></tr>
            <tr><td>Department</td><td><?php echo $dept; ?></td></tr>
            <tr><td>Year Level</td><td><?php echo $year; ?></td></tr>
            <tr><td>Semester</td><td><?php echo $sem; ?></td></tr>
            <tr><td>Term</td><td><?php echo $term; ?></td></tr>
            <tr><td>Date Submitted</td><td><?php echo $date_sub; ?></td></tr>
            <tr><td>Overall Rating</td><td><strong style="color:#7a0000"><?php echo number_format($avg,2); ?> / 5.00</strong> &nbsp; <?php echo $stars; ?></td></tr>
        </table>
    </div>

    <!-- Footer band -->
    <div class="pf">
        <span><strong>OLSHCO</strong> Faculty Evaluation System</span>
        <span>Generated: <?php echo $date_gen; ?></span>
        <span>#EVL-<?php echo $d['id']; ?> &nbsp;·&nbsp; Confidential</span>
    </div>

</div><!-- #pdf-paper -->
</div><!-- .paper-wrap -->

<script>
const FILENAME = <?php echo json_encode($filename); ?>;
const MODE     = <?php echo json_encode($mode); ?>;

/* ── Print ────────────────────────────────────────────── */
function doPrint() {
    window.print();
}

/* ── Save as PDF ──────────────────────────────────────── */
async function downloadPDF() {
    const status = document.getElementById('pdf-status');
    status.classList.add('show');

    try {
        const { jsPDF } = window.jspdf;
        const paper     = document.getElementById('pdf-paper');

        // Capture the paper div at 2× for crisp output
        const canvas = await html2canvas(paper, {
            scale           : 2,
            useCORS         : true,
            allowTaint      : true,
            logging         : false,
            backgroundColor : '#ffffff',
            windowWidth     : paper.scrollWidth,
            windowHeight    : paper.scrollHeight,
        });

        // A4 in mm
        const A4_W = 210;
        const A4_H = 297;

        const pdf    = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const imgW   = A4_W;
        const imgH   = (canvas.height * A4_W) / canvas.width;   // proportional height in mm

        // If content fits on one page
        if (imgH <= A4_H) {
            const imgData = canvas.toDataURL('image/jpeg', 0.96);
            pdf.addImage(imgData, 'JPEG', 0, 0, imgW, imgH);
        } else {
            // Multi-page: slice the canvas per A4 page
            const pagePixelH = Math.floor((A4_H / imgH) * canvas.height);
            let sliceTop = 0;

            while (sliceTop < canvas.height) {
                const sliceH = Math.min(pagePixelH, canvas.height - sliceTop);

                const slice = document.createElement('canvas');
                slice.width  = canvas.width;
                slice.height = sliceH;
                slice.getContext('2d').drawImage(canvas, 0, -sliceTop);

                const sliceData = slice.toDataURL('image/jpeg', 0.96);
                const sliceMmH  = (sliceH / canvas.height) * imgH;

                if (sliceTop > 0) pdf.addPage();
                pdf.addImage(sliceData, 'JPEG', 0, 0, imgW, sliceMmH);

                sliceTop += sliceH;
            }
        }

        pdf.save(FILENAME);

    } catch (err) {
        console.error('PDF error:', err);
        alert('Could not generate PDF.\n\n' + err.message +
              '\n\nMake sure you are connected to the internet (CDN libraries needed).');
    } finally {
        status.classList.remove('show');
    }
}

/* ── Auto-trigger on load ─────────────────────────────── */
window.addEventListener('load', () => {
    if (MODE === 'pdf') {
        setTimeout(downloadPDF, 900);   // wait for full render
    } else if (MODE === 'print') {
        setTimeout(doPrint, 500);
    }
});
</script>

</body>
</html>