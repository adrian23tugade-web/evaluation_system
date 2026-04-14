<?php
include "db.php";


if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

$sy_query = $conn->query("SELECT * FROM academic_settings WHERE is_active = 1 LIMIT 1");
$active_sy = $sy_query->fetch_assoc();

if(!$active_sy){
    $active_sy = [
        'school_year' => '2025-2026',
        'semester'    => 'OLSHCO'
    ];
}

if(isset($_POST['submit'])){
    $teacher_id      = (int)$_POST['teacher_id'];
    $teacher_name    = mysqli_real_escape_string($conn, $_POST['teacher_name_hidden']);
    $rating_comm     = (int)$_POST['rating_comm'];
    $rating_mastery  = (int)$_POST['rating_mastery'];
    $rating_punctual = (int)$_POST['rating_punctual'];
    $rating_engage   = (int)$_POST['rating_engage'];
    $rating_fairness = (int)$_POST['rating_fairness'];
    $rating_materials= (int)$_POST['rating_materials'];
    $feedback        = mysqli_real_escape_string($conn, $_POST['feedback']);
    $dept            = mysqli_real_escape_string($conn, $_POST['department_selected']);
    $year_level      = mysqli_real_escape_string($conn, $_POST['year_level']);
    $semester        = mysqli_real_escape_string($conn, $_POST['semester']);
    $term            = mysqli_real_escape_string($conn, $_POST['term']);

    /* ── Server-side bad words check via PurgoMalum ── */
    $feedback_clean  = $feedback;
    $purgo_url       = "https://www.purgomalum.com/service/containsprofanity?text=" . urlencode($feedback);
    $purgo_response  = @file_get_contents($purgo_url);

    if($purgo_response === 'true'){
        /* Replace bad words in the stored text */
        $clean_url      = "https://www.purgomalum.com/service/plain?text=" . urlencode($feedback) . "&filltext=***";
        $feedback_clean = @file_get_contents($clean_url);
        if($feedback_clean === false) $feedback_clean = $feedback; /* fallback if API fails */
        $feedback_clean = mysqli_real_escape_string($conn, $feedback_clean);
    }

    $existing_cols = [];
    $col_check = $conn->query("SHOW COLUMNS FROM evaluation");
    if($col_check){ while($col = $col_check->fetch_assoc()) $existing_cols[] = $col['Field']; }
    if(!in_array('year_level',       $existing_cols)) $conn->query("ALTER TABLE evaluation ADD COLUMN year_level       VARCHAR(20) DEFAULT ''");
    if(!in_array('semester',         $existing_cols)) $conn->query("ALTER TABLE evaluation ADD COLUMN semester         VARCHAR(20) DEFAULT ''");
    if(!in_array('term',             $existing_cols)) $conn->query("ALTER TABLE evaluation ADD COLUMN term             VARCHAR(20) DEFAULT ''");
    if(!in_array('rating_engage',    $existing_cols)) $conn->query("ALTER TABLE evaluation ADD COLUMN rating_engage    INT DEFAULT 0");
    if(!in_array('rating_fairness',  $existing_cols)) $conn->query("ALTER TABLE evaluation ADD COLUMN rating_fairness  INT DEFAULT 0");
    if(!in_array('rating_materials', $existing_cols)) $conn->query("ALTER TABLE evaluation ADD COLUMN rating_materials INT DEFAULT 0");

    $conn->query("ALTER TABLE evaluation MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY");

    $average_rating = round(($rating_comm + $rating_mastery + $rating_punctual + $rating_engage + $rating_fairness + $rating_materials) / 6, 2);

    /* Store the cleaned feedback (bad words replaced with ***) */
    $sql = "INSERT INTO evaluation 
                (teacher_name, rating, feedback, rating_comm, rating_mastery, rating_punctual, rating_engage, rating_fairness, rating_materials, department, year_level, semester, term) 
            VALUES 
                ('$teacher_name','$average_rating','$feedback_clean','$rating_comm','$rating_mastery','$rating_punctual','$rating_engage','$rating_fairness','$rating_materials','$dept','$year_level','$semester','$term')";

    if($conn->query($sql) === TRUE){
        header("Location: view.php?dept=" . urlencode($dept));
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Evaluation | OLSHCO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,800;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- External stylesheet -->
    <link rel="stylesheet" href="css/evaluation.css">
</head>
<body>

<!-- HEADER -->
<header>
    <div class="header-brand">
        <img src="uploads/logo.png" alt="Logo">
        <div>
            <h1>Our Lady of the Sacred Heart College</h1>
            <small>
                <i class="fas fa-calendar-alt"></i>
                SY: <?php echo $active_sy['school_year']; ?> &nbsp;|&nbsp; <?php echo $active_sy['semester']; ?>
            </small>
        </div>
    </div>
    <div class="header-right">
        <div class="user-chip">
            <i class="fas fa-user-circle"></i>
            <?php echo htmlspecialchars($_SESSION['username']); ?>
        </div>
    </div>
</header>

<!-- PROGRESS BAR -->
<div class="progress-bar-wrap" id="progress-wrap">
    <div class="prog-step">
        <div class="prog-dot active" id="pd-1">1</div>
        <span class="prog-label active" id="pl-1">Dept</span>
    </div>
    <div class="prog-line" id="pline-1"></div>
    <div class="prog-step">
        <div class="prog-dot" id="pd-2">2</div>
        <span class="prog-label" id="pl-2">Year</span>
    </div>
    <div class="prog-line" id="pline-2"></div>
    <div class="prog-step">
        <div class="prog-dot" id="pd-3">3</div>
        <span class="prog-label" id="pl-3">Semester</span>
    </div>
    <div class="prog-line" id="pline-3"></div>
    <div class="prog-step">
        <div class="prog-dot" id="pd-4">4</div>
        <span class="prog-label" id="pl-4">Term</span>
    </div>
    <div class="prog-line" id="pline-4"></div>
    <div class="prog-step">
        <div class="prog-dot" id="pd-5">5</div>
        <span class="prog-label" id="pl-5">Evaluate</span>
    </div>
</div>

<div class="container">

    <!-- STEP 1: DEPARTMENT -->
    <div id="step-dept" class="anim">
        <div class="step-heading">
            <div class="step-eyebrow">Step 1 of 5</div>
            <h2>Choose Department</h2>
            <p>Select the department you want to evaluate.</p>
        </div>
        <div class="dept-grid">
            <div class="dept-card anim anim-1" data-dept="BSIT" onclick="selectDept('BSIT')">
                <div class="icon-box"><i class="fas fa-laptop-code"></i></div>
                <div class="dept-name">BSIT</div>
                <div class="dept-full">Info Technology</div>
            </div>
            <div class="dept-card anim anim-2" data-dept="BSHM" onclick="selectDept('BSHM')">
                <div class="icon-box"><i class="fas fa-utensils"></i></div>
                <div class="dept-name">BSHM</div>
                <div class="dept-full">Hospitality Mgmt</div>
            </div>
            <div class="dept-card anim anim-3" data-dept="BSEDUC" onclick="selectDept('BSEDUC')">
                <div class="icon-box"><i class="fas fa-chalkboard"></i></div>
                <div class="dept-name">BSEDUC</div>
                <div class="dept-full">Education</div>
            </div>
            <div class="dept-card anim anim-4" data-dept="BSCRIM" onclick="selectDept('BSCRIM')">
                <div class="icon-box"><i class="fas fa-shield-alt"></i></div>
                <div class="dept-name">BSCRIM</div>
                <div class="dept-full">Criminology</div>
            </div>
            <div class="dept-card anim anim-5" data-dept="BSOAD" onclick="selectDept('BSOAD')">
                <div class="icon-box"><i class="fas fa-briefcase"></i></div>
                <div class="dept-name">BSOAD</div>
                <div class="dept-full">Office Admin</div>
            </div>
        </div>
        <div style="margin-top:32px;text-align:center;">
            <a href="index.php" class="back-link"><i class="fas fa-home"></i> Back to Dashboard</a>
        </div>
    </div>

    <!-- STEP 2: YEAR LEVEL -->
    <div id="step-year" style="display:none;">
        <div class="nav-row">
            <button class="back-btn" onclick="goTo('dept')"><i class="fas fa-arrow-left"></i> Change Dept</button>
            <a href="index.php" class="dashboard-link"><i class="fas fa-home"></i> Dashboard</a>
        </div>
        <div id="bc-year" class="breadcrumb"></div>
        <div class="step-heading">
            <div class="step-eyebrow">Step 2 of 5</div>
            <h2>Year Level</h2>
            <p>Choose which year level you belong to.</p>
        </div>
        <div class="sel-grid cols-2x2">
            <div class="sel-card anim anim-1" onclick="selectYear('1st Year')">
                <div class="card-title">1st Year</div>
                <div class="card-sub">Freshmen</div>
            </div>
            <div class="sel-card anim anim-2" onclick="selectYear('2nd Year')">
                <div class="card-title">2nd Year</div>
                <div class="card-sub">Sophomore</div>
            </div>
            <div class="sel-card locked anim anim-3">
                <div class="card-title">3rd Year</div>
                <span class="locked-badge">Coming Soon</span>
            </div>
            <div class="sel-card locked anim anim-4">
                <div class="card-title">4th Year</div>
                <span class="locked-badge">Coming Soon</span>
            </div>
        </div>
    </div>

    <!-- STEP 3: SEMESTER -->
    <div id="step-sem" style="display:none;">
        <div class="nav-row">
            <button class="back-btn" onclick="goTo('year')"><i class="fas fa-arrow-left"></i> Change Year</button>
            <a href="index.php" class="dashboard-link"><i class="fas fa-home"></i> Dashboard</a>
        </div>
        <div id="bc-sem" class="breadcrumb"></div>
        <div class="step-heading">
            <div class="step-eyebrow">Step 3 of 5</div>
            <h2>Semester</h2>
            <p>Which semester are you evaluating for?</p>
        </div>
        <div class="sel-grid cols-2">
            <div class="sel-card anim anim-1" onclick="selectSem('1st Semester')">
                <div class="card-title">1st Semester</div>
                <div class="card-sub">First Half of the Year</div>
            </div>
            <div class="sel-card anim anim-2" onclick="selectSem('2nd Semester')">
                <div class="card-title">2nd Semester</div>
                <div class="card-sub">Second Half of the Year</div>
            </div>
        </div>
    </div>

    <!-- STEP 4: TERM -->
    <div id="step-term" style="display:none;">
        <div class="nav-row">
            <button class="back-btn" onclick="goTo('sem')"><i class="fas fa-arrow-left"></i> Change Semester</button>
            <a href="index.php" class="dashboard-link"><i class="fas fa-home"></i> Dashboard</a>
        </div>
        <div id="bc-term" class="breadcrumb"></div>
        <div class="step-heading">
            <div class="step-eyebrow">Step 4 of 5</div>
            <h2>Select Term</h2>
            <p>Which term would you like to evaluate?</p>
        </div>
        <div class="sel-grid cols-3">
            <div class="sel-card anim anim-1" onclick="selectTerm('Prelim')">
                <div class="card-title">Prelim</div>
                <div class="card-sub">First exam period</div>
            </div>
            <div class="sel-card anim anim-2" onclick="selectTerm('Midterm')">
                <div class="card-title">Midterm</div>
                <div class="card-sub">Middle of semester</div>
            </div>
            <div class="sel-card anim anim-3" onclick="selectTerm('Finals')">
                <div class="card-title">Finals</div>
                <div class="card-sub">End of semester</div>
            </div>
        </div>
    </div>

    <!-- STEP 5: EVALUATION FORM -->
    <div id="step-form" style="display:none;">
        <div class="form-header">
            <span class="deco2"></span>
            <div class="nav-row">
                <button class="back-btn" onclick="goTo('term')"><i class="fas fa-arrow-left"></i> Change Term</button>
                <a href="index.php" class="dashboard-link"><i class="fas fa-home"></i> Dashboard</a>
            </div>
            <div id="bc-form" class="breadcrumb"></div>
            <h2 id="form-title">Faculty Evaluation</h2>
            <p id="form-subtitle" style="font-size:0.78rem;color:rgba(255,255,255,0.5);margin-top:5px;"></p>
        </div>

        <div class="form-body">
            <form method="POST" id="eval-form">
                <input type="hidden" name="department_selected" id="inp-dept">
                <input type="hidden" name="year_level"          id="inp-year">
                <input type="hidden" name="semester"            id="inp-sem">
                <input type="hidden" name="term"                id="inp-term">
                <input type="hidden" name="teacher_name_hidden" id="teacher-name-hidden">

                <div class="input-group">
                    <label><i class="fas fa-chalkboard-teacher"></i> Select Instructor</label>
                    <select name="teacher_id" id="teacher-select" required onchange="updateTeacherName(this)">
                        <option value="" disabled selected>Loading teachers...</option>
                    </select>
                </div>

                <div class="divider"><span>Rate the Instructor</span></div>

                <div class="rating-section-title">
                    <i class="fas fa-star"></i> Performance Ratings
                </div>

                <div class="rating-container">
                    <div class="rating-box">
                        <div class="rating-label-wrap">
                            <span class="rating-num">1</span>
                            <span>Communication &amp; Delivery</span>
                        </div>
                        <div class="stars">
                            <?php for($i=5;$i>=1;$i--): ?>
                                <input type="radio" name="rating_comm" value="<?php echo $i;?>" id="comm<?php echo $i;?>" required>
                                <label for="comm<?php echo $i;?>" class="fas fa-star"></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="rating-box">
                        <div class="rating-label-wrap">
                            <span class="rating-num">2</span>
                            <span>Mastery of Subject</span>
                        </div>
                        <div class="stars">
                            <?php for($i=5;$i>=1;$i--): ?>
                                <input type="radio" name="rating_mastery" value="<?php echo $i;?>" id="mast<?php echo $i;?>" required>
                                <label for="mast<?php echo $i;?>" class="fas fa-star"></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="rating-box">
                        <div class="rating-label-wrap">
                            <span class="rating-num">3</span>
                            <span>Punctuality &amp; Management</span>
                        </div>
                        <div class="stars">
                            <?php for($i=5;$i>=1;$i--): ?>
                                <input type="radio" name="rating_punctual" value="<?php echo $i;?>" id="punc<?php echo $i;?>" required>
                                <label for="punc<?php echo $i;?>" class="fas fa-star"></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="rating-box">
                        <div class="rating-label-wrap">
                            <span class="rating-num">4</span>
                            <span>Student Engagement</span>
                        </div>
                        <div class="stars">
                            <?php for($i=5;$i>=1;$i--): ?>
                                <input type="radio" name="rating_engage" value="<?php echo $i;?>" id="eng<?php echo $i;?>" required>
                                <label for="eng<?php echo $i;?>" class="fas fa-star"></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="rating-box">
                        <div class="rating-label-wrap">
                            <span class="rating-num">5</span>
                            <span>Fairness in Grading</span>
                        </div>
                        <div class="stars">
                            <?php for($i=5;$i>=1;$i--): ?>
                                <input type="radio" name="rating_fairness" value="<?php echo $i;?>" id="fair<?php echo $i;?>" required>
                                <label for="fair<?php echo $i;?>" class="fas fa-star"></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="rating-box">
                        <div class="rating-label-wrap">
                            <span class="rating-num">6</span>
                            <span>Use of Teaching Materials</span>
                        </div>
                        <div class="stars">
                            <?php for($i=5;$i>=1;$i--): ?>
                                <input type="radio" name="rating_materials" value="<?php echo $i;?>" id="mat<?php echo $i;?>" required>
                                <label for="mat<?php echo $i;?>" class="fas fa-star"></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="divider"><span>Additional Feedback</span></div>

                <div class="input-group">
                    <label><i class="fas fa-comment-dots"></i> Your Feedback</label>
                    <textarea
                        name="feedback"
                        id="feedback-input"
                        rows="4"
                        placeholder="Share your thoughts about this instructor..."
                        required
                    ></textarea>

                    <!-- Checking spinner -->
                    <div class="feedback-checking" id="feedback-checking">
                        <span class="spinner"></span>
                        <span>Checking feedback for inappropriate language...</span>
                    </div>

                    <!-- Warning: bad words found (but user can still submit — they're cleaned server-side) -->
                    <div class="feedback-notice warning" id="feedback-warn">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Language notice:</strong> Your feedback may contain inappropriate words.
                            They will be automatically replaced with <code>***</code> before saving.
                            You can edit your message or submit anyway.
                        </div>
                    </div>

                    <!-- Error: API unreachable -->
                    <div class="feedback-notice error" id="feedback-error">
                        <i class="fas fa-wifi"></i>
                        <div>
                            Could not reach the language filter API. Your feedback will be submitted as-is.
                            Please keep your language respectful.
                        </div>
                    </div>
                </div>

                <div class="button-container">
                    <button type="submit" name="submit" id="submit-btn" class="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        <span>Submit Evaluation</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div><!-- .container -->

<script>
/* ══════════════════════════════════════════════════════════
   STEP NAVIGATION
══════════════════════════════════════════════════════════ */
const state = { dept:'', year:'', sem:'', term:'', step:1 };

const STEPS     = { dept:'step-dept', year:'step-year', sem:'step-sem', term:'step-term', form:'step-form' };
const STEP_NUMS = { dept:1, year:2, sem:3, term:4, form:5 };

function updateProgress(stepName) {
    const current = STEP_NUMS[stepName];
    for(let i = 1; i <= 5; i++){
        const dot   = document.getElementById('pd-' + i);
        const label = document.getElementById('pl-' + i);
        dot.className   = 'prog-dot'   + (i < current ? ' done' : (i === current ? ' active' : ''));
        label.className = 'prog-label' + (i === current ? ' active' : '');
        if(i < 5){
            document.getElementById('pline-' + i).className = 'prog-line' + (i < current ? ' done' : '');
        }
    }
}

function showStep(name) {
    Object.values(STEPS).forEach(id => {
        const el = document.getElementById(id);
        el.style.display = 'none';
        el.classList.remove('anim');
    });
    const target = document.getElementById(STEPS[name]);
    target.style.display = 'block';
    void target.offsetWidth;
    target.classList.add('anim');
    updateProgress(name);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function bc(items) {
    return items.map((t, i) =>
        `<span class="bc-item">${t}</span>${i < items.length - 1
            ? ' <i class="fas fa-chevron-right" style="font-size:0.45rem;opacity:0.35;"></i> '
            : ''}`
    ).join('');
}

function selectDept(dept) {
    state.dept = dept;
    document.getElementById('bc-year').innerHTML = bc([dept]);
    showStep('year');
}
function selectYear(year) {
    state.year = year;
    document.getElementById('bc-sem').innerHTML = bc([state.dept, year]);
    showStep('sem');
}
function selectSem(sem) {
    state.sem = sem;
    document.getElementById('bc-term').innerHTML = bc([state.dept, state.year, sem]);
    showStep('term');
}
function selectTerm(term) {
    state.term = term;
    document.getElementById('inp-dept').value = state.dept;
    document.getElementById('inp-year').value = state.year;
    document.getElementById('inp-sem').value  = state.sem;
    document.getElementById('inp-term').value = term;

    document.getElementById('form-title').textContent  = state.dept + ' Faculty Evaluation';
    document.getElementById('form-subtitle').innerHTML =
        `<i class="fas fa-layer-group"></i>&nbsp; ${state.year} &nbsp;&bull;&nbsp; ${state.sem} &nbsp;&bull;&nbsp; ${term}`;
    document.getElementById('bc-form').innerHTML = bc([state.dept, state.year, state.sem, term]);

    showStep('form');
    fetchTeachers(state.dept);
}

function goTo(stepName) { showStep(stepName); }

/* ══════════════════════════════════════════════════════════
   TEACHER LOADER
══════════════════════════════════════════════════════════ */
async function fetchTeachers(dept) {
    const sel = document.getElementById('teacher-select');
    sel.innerHTML = '<option value="" disabled selected>Loading...</option>';
    try {
        const res  = await fetch(`get_teachers.php?dept=${dept}`);
        const data = await res.json();
        sel.innerHTML = '<option value="" disabled selected>Select Instructor...</option>';
        data.forEach(t => {
            const o = document.createElement('option');
            o.value = t.id;
            o.text  = t.name;
            sel.add(o);
        });
    } catch(e) {
        sel.innerHTML = '<option value="" disabled selected>Error loading teachers</option>';
    }
}

function updateTeacherName(sel) {
    document.getElementById('teacher-name-hidden').value = sel.options[sel.selectedIndex].text;
}

/* ══════════════════════════════════════════════════════════
   BAD WORDS CHECK  — PurgoMalum API (client-side preview)
   ──────────────────────────────────────────────────────────
   How it works:
   1. User stops typing for 900 ms (debounce).
   2. We call PurgoMalum's /containsprofanity endpoint with
      the feedback text encoded as a query parameter.
   3. If the response is "true"  → show a yellow warning.
      If the response is "false" → hide all notices.
      If the API is unreachable  → show a soft error notice.
   4. On the server side (PHP above) the same API is called
      again to actually replace bad words before saving.
══════════════════════════════════════════════════════════ */

const feedbackInput  = document.getElementById('feedback-input');
const feedbackWarn   = document.getElementById('feedback-warn');
const feedbackError  = document.getElementById('feedback-error');
const feedbackCheck  = document.getElementById('feedback-checking');
const submitBtn      = document.getElementById('submit-btn');

let debounceTimer = null;

function hideFeedbackNotices() {
    feedbackWarn.style.display  = 'none';
    feedbackError.style.display = 'none';
    feedbackCheck.classList.remove('show');
}

async function checkBadWords(text) {
    if(!text.trim()) { hideFeedbackNotices(); return; }

    /* Show spinner */
    hideFeedbackNotices();
    feedbackCheck.classList.add('show');
    submitBtn.disabled = true;

    try {
        const url      = `https://www.purgomalum.com/service/containsprofanity?text=${encodeURIComponent(text)}`;
        const response = await fetch(url);

        if(!response.ok) throw new Error('API error');

        const result = await response.text();    /* returns "true" or "false" */

        feedbackCheck.classList.remove('show');
        submitBtn.disabled = false;

        if(result.trim() === 'true'){
            feedbackWarn.style.display = 'flex';   /* show yellow warning */
        } else {
            hideFeedbackNotices();                 /* clean — no notice needed */
        }

    } catch(err) {
        /* API unreachable: show soft error, still allow submit */
        feedbackCheck.classList.remove('show');
        feedbackError.style.display = 'flex';
        submitBtn.disabled = false;
    }
}

/* Debounce: wait 900 ms after the user stops typing */
feedbackInput.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => checkBadWords(this.value), 900);
});
</script>
</body>
</html>