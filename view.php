<?php
include "db.php";


if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

// ─────────────────────────────────────────────────────────────────────────────
// PDF  →  redirect to generate_pdf.php
// ─────────────────────────────────────────────────────────────────────────────
if(isset($_GET['pdf']) && isset($_GET['eval_id'])){
    $eval_id = (int)$_GET['eval_id'];
    $chk = $conn->prepare("SELECT id FROM evaluation WHERE id = ?");
    $chk->bind_param("i", $eval_id);
    $chk->execute();
    $chk->store_result();

    if($chk->num_rows > 0){
        header("Location: generate_pdf.php?id={$eval_id}");
    } else {
        header("Location: view.php");
    }
    exit();
}

// ─────────────────────────────────────────────────────────────────────────────
// FILTERS
// ─────────────────────────────────────────────────────────────────────────────
$dept_filter  = $_GET['dept']   ?? '';
$year_filter  = $_GET['year']   ?? '';
$sem_filter   = $_GET['sem']    ?? '';
$term_filter  = $_GET['term']   ?? '';
$search_query = $_GET['search'] ?? '';

$step = 'dept';
if ($dept_filter !== '')                                                                            $step = 'year';
if ($dept_filter !== '' && $year_filter !== '')                                                     $step = 'sem';
if ($dept_filter !== '' && $year_filter !== '' && $sem_filter !== '')                               $step = 'term';
if ($dept_filter !== '' && $year_filter !== '' && $sem_filter !== '' && $term_filter !== '')        $step = 'records';

$list_url = 'view.php?dept='.urlencode($dept_filter)
          . '&year='.urlencode($year_filter)
          . '&sem='.urlencode($sem_filter)
          . '&term='.urlencode($term_filter);

// ─────────────────────────────────────────────────────────────────────────────
// AUTO-ADD MISSING COLUMNS
// ─────────────────────────────────────────────────────────────────────────────
$existing_cols = [];
$col_check = $conn->query("SHOW COLUMNS FROM evaluation");
if($col_check){ while($col = $col_check->fetch_assoc()) $existing_cols[] = $col['Field']; }
foreach(['year_level','semester','term'] as $col_name){
    if(!in_array($col_name, $existing_cols))
        $conn->query("ALTER TABLE evaluation ADD COLUMN {$col_name} VARCHAR(20) DEFAULT ''");
}

// ─────────────────────────────────────────────────────────────────────────────
// RECORDS QUERY
// ─────────────────────────────────────────────────────────────────────────────
$result = null; $db_error = "";
$total_records = 0; $avg_overall = 0;

if($step === 'records'){
    $sql    = "SELECT * FROM evaluation WHERE department=? AND year_level=? AND semester=? AND term=?";
    $params = [$dept_filter, $year_filter, $sem_filter, $term_filter];
    $types  = "ssss";
    if($search_query !== ''){ $sql .= " AND teacher_name LIKE ?"; $params[] = "%$search_query%"; $types .= "s"; }
    $sql .= " ORDER BY id DESC";

    $stmt = $conn->prepare($sql);
    if($stmt === false){
        $db_error = "Query error: ".$conn->error;
    } else {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result        = $stmt->get_result();
        $total_records = $result->num_rows;
    }

    $stmt_avg = $conn->prepare("SELECT AVG(rating) as avg_r FROM evaluation WHERE department=? AND year_level=? AND semester=? AND term=?");
    if($stmt_avg){
        $stmt_avg->bind_param("ssss", $dept_filter, $year_filter, $sem_filter, $term_filter);
        $stmt_avg->execute();
        $avg_overall = round($stmt_avg->get_result()->fetch_assoc()['avg_r'] ?? 0, 2);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// DEPT CONFIG
// ─────────────────────────────────────────────────────────────────────────────
$dept_config = [
    'BSIT'   => ['icon'=>'fa-laptop-code', 'color'=>'#1a7a3c', 'shadow'=>'rgba(26,122,60,0.3)',   'label'=>'Info Technology'],
    'BSHM'   => ['icon'=>'fa-utensils',    'color'=>'#b8860b', 'shadow'=>'rgba(184,134,11,0.3)',  'label'=>'Hospitality Mgmt'],
    'BSEDUC' => ['icon'=>'fa-chalkboard',  'color'=>'#1a4a8a', 'shadow'=>'rgba(26,74,138,0.3)',   'label'=>'Education'],
    'BSCRIM' => ['icon'=>'fa-shield-alt',  'color'=>'#8b0000', 'shadow'=>'rgba(139,0,0,0.3)',     'label'=>'Criminology'],
    'BSOAD'  => ['icon'=>'fa-briefcase',   'color'=>'#c05c00', 'shadow'=>'rgba(192,92,0,0.3)',    'label'=>'Office Admin'],
];

$step_nums   = ['dept'=>1,'year'=>2,'sem'=>3,'term'=>4,'records'=>5];
$step_labels = ['Dept','Year','Semester','Term','Records'];
$current_step_num = $step_nums[$step] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Records | OLSHCO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,800;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/view.css">
</head>
<body>

<!-- ══════════ HEADER ══════════ -->
<header>
    <div class="header-brand">
        <img src="uploads/logo.png" alt="Logo">
        <div>
            <h1>Our Lady of the Sacred Heart College</h1>
            <small><i class="fas fa-eye"></i> Evaluation Records Viewer</small>
        </div>
    </div>
    <div class="user-chip">
        <i class="fas fa-user-circle"></i>
        <?php echo htmlspecialchars($_SESSION['username']); ?>
    </div>
</header>

<!-- ══════════ PROGRESS BAR ══════════ -->
<div class="progress-bar-wrap">
    <?php for($si = 1; $si <= 5; $si++):
        $dot_class = $si < $current_step_num ? 'done' : ($si === $current_step_num ? 'active' : '');
        $lbl_class = $si === $current_step_num ? 'active' : '';
    ?>
        <div class="prog-step">
            <div class="prog-dot <?php echo $dot_class; ?>"><?php echo $si; ?></div>
            <span class="prog-label <?php echo $lbl_class; ?>"><?php echo $step_labels[$si - 1]; ?></span>
        </div>
        <?php if($si < 5): $line_class = $si < $current_step_num ? 'done' : ''; ?>
        <div class="prog-line <?php echo $line_class; ?>"></div>
        <?php endif; ?>
    <?php endfor; ?>
</div>

<!-- ══════════ MAIN ══════════ -->
<div class="wrapper">

    <?php if($step === 'dept'): ?>
    <!-- STEP 1: DEPARTMENT -->
    <div class="anim">
        <div class="step-heading">
            <div class="step-eyebrow">Step 1</div>
            <h2>Select Department</h2>
            <p>Choose a department to view evaluation records.</p>
        </div>
        <div class="dept-grid">
            <?php $di = 1; foreach($dept_config as $code => $cfg): ?>
                <div class="dept-card anim anim-<?php echo $di++; ?>"
                     data-dept="<?php echo $code; ?>"
                     onclick="window.location.href='view.php?dept=<?php echo $code; ?>'">
                    <div class="icon-box"><i class="fas <?php echo $cfg['icon']; ?>"></i></div>
                    <div class="dept-name"><?php echo $code; ?></div>
                    <div class="dept-full"><?php echo $cfg['label']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="margin-top:28px;text-align:center;">
            <a href="index.php" class="back-link"><i class="fas fa-home"></i> Back to Dashboard</a>
        </div>
    </div>

    <?php elseif($step === 'year'): ?>
    <!-- STEP 2: YEAR LEVEL -->
    <div class="anim">
        <div class="nav-row">
            <a href="view.php" class="back-btn-small"><i class="fas fa-arrow-left"></i> Change Dept</a>
            <a href="index.php" class="dashboard-link"><i class="fas fa-home"></i> Dashboard</a>
        </div>
        <div class="breadcrumb">
            <span class="bc-item"><?php echo $dept_filter; ?></span>
            <i class="fas fa-chevron-right bc-sep"></i><span>Year Level</span>
        </div>
        <div class="step-heading">
            <div class="step-eyebrow">Step 2</div>
            <h2>Year Level</h2>
            <p>Which year level do you want to view?</p>
        </div>
        <div class="sel-grid cols-2" style="max-width:500px;margin:0 auto;">
            <div class="sel-card anim anim-1" onclick="window.location.href='view.php?dept=<?php echo urlencode($dept_filter); ?>&year=<?php echo urlencode('1st Year'); ?>'">
                <div class="card-title">1st Year</div><div class="card-sub">Freshmen</div>
            </div>
            <div class="sel-card anim anim-2" onclick="window.location.href='view.php?dept=<?php echo urlencode($dept_filter); ?>&year=<?php echo urlencode('2nd Year'); ?>'">
                <div class="card-title">2nd Year</div><div class="card-sub">Sophomore</div>
            </div>
            <div class="sel-card locked anim anim-3">
                <div class="card-title">3rd Year</div><span class="locked-badge">Coming Soon</span>
            </div>
            <div class="sel-card locked anim anim-4">
                <div class="card-title">4th Year</div><span class="locked-badge">Coming Soon</span>
            </div>
        </div>
    </div>

    <?php elseif($step === 'sem'): ?>
    <!-- STEP 3: SEMESTER -->
    <div class="anim">
        <div class="nav-row">
            <a href="view.php?dept=<?php echo urlencode($dept_filter); ?>" class="back-btn-small"><i class="fas fa-arrow-left"></i> Change Year</a>
            <a href="index.php" class="dashboard-link"><i class="fas fa-home"></i> Dashboard</a>
        </div>
        <div class="breadcrumb">
            <span class="bc-item"><?php echo $dept_filter; ?></span>
            <i class="fas fa-chevron-right bc-sep"></i>
            <span class="bc-item"><?php echo $year_filter; ?></span>
            <i class="fas fa-chevron-right bc-sep"></i><span>Semester</span>
        </div>
        <div class="step-heading">
            <div class="step-eyebrow">Step 3</div>
            <h2>Semester</h2>
            <p>Which semester would you like to view?</p>
        </div>
        <div class="sel-grid cols-2" style="max-width:500px;margin:0 auto;">
            <div class="sel-card anim anim-1" onclick="window.location.href='view.php?dept=<?php echo urlencode($dept_filter); ?>&year=<?php echo urlencode($year_filter); ?>&sem=1st+Semester'">
                <div class="card-title">1st Semester</div><div class="card-sub">First Half of the Year</div>
            </div>
            <div class="sel-card anim anim-2" onclick="window.location.href='view.php?dept=<?php echo urlencode($dept_filter); ?>&year=<?php echo urlencode($year_filter); ?>&sem=2nd+Semester'">
                <div class="card-title">2nd Semester</div><div class="card-sub">Second Half of the Year</div>
            </div>
        </div>
    </div>

    <?php elseif($step === 'term'): ?>
    <!-- STEP 4: TERM -->
    <div class="anim">
        <div class="nav-row">
            <a href="view.php?dept=<?php echo urlencode($dept_filter); ?>&year=<?php echo urlencode($year_filter); ?>" class="back-btn-small"><i class="fas fa-arrow-left"></i> Change Semester</a>
            <a href="index.php" class="dashboard-link"><i class="fas fa-home"></i> Dashboard</a>
        </div>
        <div class="breadcrumb">
            <span class="bc-item"><?php echo $dept_filter; ?></span>
            <i class="fas fa-chevron-right bc-sep"></i>
            <span class="bc-item"><?php echo $year_filter; ?></span>
            <i class="fas fa-chevron-right bc-sep"></i>
            <span class="bc-item"><?php echo $sem_filter; ?></span>
            <i class="fas fa-chevron-right bc-sep"></i><span>Term</span>
        </div>
        <div class="step-heading">
            <div class="step-eyebrow">Step 4</div>
            <h2>Select Term</h2>
            <p>Which term would you like to view?</p>
        </div>
        <div class="sel-grid cols-3" style="max-width:560px;margin:0 auto;">
            <?php
            $terms = ['Prelim'=>'First exam period','Midterm'=>'Middle of semester','Finals'=>'End of semester'];
            $ai = 1;
            foreach($terms as $tname => $tsub):
                $term_url = 'view.php?dept='.urlencode($dept_filter).'&year='.urlencode($year_filter).'&sem='.urlencode($sem_filter).'&term='.urlencode($tname);
            ?>
            <div class="sel-card anim anim-<?php echo $ai++; ?>" onclick="window.location.href='<?php echo $term_url; ?>'">
                <div class="card-title"><?php echo $tname; ?></div>
                <div class="card-sub"><?php echo $tsub; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php elseif($step === 'records'): ?>
    <!-- STEP 5: RECORDS -->
    <div class="records-view anim">

        <!-- Stats -->
        <div class="stats-bar">
            <div class="stat-pill">
                <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <div class="stat-label">Total Evaluations</div>
                    <div class="stat-value"><?php echo $total_records; ?></div>
                    <div class="stat-sub"><?php echo "$dept_filter &bull; $year_filter"; ?></div>
                </div>
            </div>
            <div class="stat-pill">
                <div class="stat-icon"><i class="fas fa-star"></i></div>
                <div>
                    <div class="stat-label">Overall Avg Rating</div>
                    <div class="stat-value"><?php echo $avg_overall ?: '—'; ?></div>
                    <div class="stat-sub"><?php echo $avg_overall ? 'Out of 5.00' : 'No data yet'; ?></div>
                </div>
            </div>
            <div class="stat-pill">
                <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                <div>
                    <div class="stat-label">Period</div>
                    <div class="stat-value" style="font-size:1rem;"><?php echo $term_filter; ?></div>
                    <div class="stat-sub"><?php echo $sem_filter; ?></div>
                </div>
            </div>
        </div>

        <!-- View header -->
        <div class="view-header">
            <div class="back-row" style="margin-bottom:0;flex-wrap:wrap;">
                <a href="view.php?dept=<?php echo urlencode($dept_filter); ?>&year=<?php echo urlencode($year_filter); ?>&sem=<?php echo urlencode($sem_filter); ?>" class="back-btn-small">
                    <i class="fas fa-arrow-left"></i> Change Term
                </a>
                <div class="breadcrumb" style="margin-bottom:0;">
                    <span class="bc-item"><?php echo $dept_filter; ?></span>
                    <i class="fas fa-chevron-right bc-sep"></i>
                    <span class="bc-item"><?php echo $year_filter; ?></span>
                    <i class="fas fa-chevron-right bc-sep"></i>
                    <span class="bc-item"><?php echo $sem_filter; ?></span>
                    <i class="fas fa-chevron-right bc-sep"></i>
                    <span class="bc-item"><?php echo $term_filter; ?></span>
                </div>
            </div>
            <div class="filter-group">
                <input type="text" id="teacherSearch"
                       value="<?php echo htmlspecialchars($search_query); ?>"
                       placeholder="Search teacher..."
                       onkeydown="if(event.key==='Enter') applySearch()">
                <button class="search-btn" onclick="applySearch()"><i class="fas fa-search"></i></button>
            </div>
        </div>

        <?php if($db_error !== ''): ?>
        <div class="no-records" style="color:#c00;">
            <i class="fas fa-exclamation-triangle" style="font-size:2rem;margin-bottom:8px;display:block;"></i>
            <p><?php echo htmlspecialchars($db_error); ?></p>
        </div>
        <?php endif; ?>

        <!-- Cards -->
        <div class="records-container">
            <?php if($result && $result->num_rows > 0):
                $ci = 0;
                while($row = $result->fetch_assoc()):
                    $sc         = (int)round((float)$row['rating']);
                    $rd         = number_format((float)$row['rating'], 1);
                    $detail_url = 'view_details.php?id=' . $row['id'] . '&back=' . urlencode($list_url);
                    $pdf_url    = 'generate_pdf.php?id=' . $row['id'];
                    $np         = explode(' ', trim($row['teacher_name']));
                    $ac         = strtoupper(substr(end($np), 0, 1));
                    $dc         = 'anim-' . (($ci % 5) + 1);
                    $ci++;

                    $r_comm = (int)($row['rating_comm']     ?? 0);
                    $r_mast = (int)($row['rating_mastery']  ?? 0);
                    $r_punc = (int)($row['rating_punctual'] ?? 0);
                    $r_eng  = (int)($row['rating_engage']   ?? 0);
                    $r_fair = (int)($row['rating_fairness'] ?? 0);
                    $r_mat  = (int)($row['rating_materials']?? 0);

                    $bars = [
                        'Comm'     => $r_comm,
                        'Mastery'  => $r_mast,
                        'Punctual' => $r_punc,
                        'Engage'   => $r_eng,
                        'Fairness' => $r_fair,
                        'Materials'=> $r_mat,
                    ];
            ?>
                <a class="teacher-card anim <?php echo $dc; ?>" href="<?php echo $detail_url; ?>">

                    <!-- Banner -->
                    <div class="tc-banner">
                        <span class="tc-id-tag">#EVL-<?php echo $row['id']; ?></span>
                        <div class="tc-avatar"><?php echo $ac; ?></div>
                        <div class="tc-name"><?php echo htmlspecialchars($row['teacher_name']); ?></div>
                        <div class="tc-dept-tag"><?php echo htmlspecialchars($row['department'] ?? ''); ?></div>
                    </div>

                    <!-- Body -->
                    <div class="tc-body">
                        <div class="tc-stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="<?php echo ($i <= $sc) ? 'fas' : 'far'; ?> fa-star <?php echo ($i <= $sc) ? 'star-full' : 'star-empty'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="tc-rating"><?php echo $rd; ?></div>
                        <div class="tc-rating-sub">out of 5.00</div>

                        <div class="tc-bars">
                            <?php foreach($bars as $lbl => $val): ?>
                            <div class="tc-bar-row">
                                <span class="tc-bar-label"><?php echo $lbl; ?></span>
                                <div class="tc-bar-track">
                                    <div class="tc-bar-fill" style="width:<?php echo ($val / 5) * 100; ?>%"></div>
                                </div>
                                <span class="tc-bar-val"><?php echo $val; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="tc-cta">
                            View Full Details <i class="fas fa-arrow-right" style="font-size:0.6rem;"></i>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="tc-actions" onclick="event.preventDefault();event.stopPropagation();">
                        <a class="tc-btn tc-btn-pdf"
                           href="<?php echo $pdf_url; ?>"
                           target="_blank"
                           onclick="event.stopPropagation();">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>

                </a>
            <?php endwhile; ?>
            <?php else: ?>
                <div class="no-records">
                    <i class="fas fa-inbox" style="font-size:3rem;color:#ddd;display:block;margin-bottom:14px;"></i>
                    <p>No evaluation records found for<br>
                       <strong><?php echo "$dept_filter — $year_filter — $sem_filter — $term_filter"; ?></strong>
                    </p>
                </div>
            <?php endif; ?>
        </div>

    </div>
    <?php endif; ?>

</div><!-- .wrapper -->

<script>
function applySearch() {
    const s = document.getElementById('teacherSearch').value;
    window.location.href = `view.php?dept=<?php echo urlencode($dept_filter); ?>&year=<?php echo urlencode($year_filter); ?>&sem=<?php echo urlencode($sem_filter); ?>&term=<?php echo urlencode($term_filter); ?>&search=${encodeURIComponent(s)}`;
}
</script>
</body>
</html>