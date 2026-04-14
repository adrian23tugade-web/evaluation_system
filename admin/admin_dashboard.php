<?php
ob_start();
include "../db.php";

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}



$total_res  = $conn->query("SELECT COUNT(*) as total FROM evaluation");
$total_data = $total_res->fetch_assoc();

$avg_res  = $conn->query("SELECT AVG(rating) as average FROM evaluation");
$avg_data = $avg_res->fetch_assoc();

$dept_res2 = $conn->query("SELECT COUNT(DISTINCT department) as depts FROM evaluation");
$dept_data = $dept_res2->fetch_assoc();

$dist_res = $conn->query("SELECT rating, COUNT(*) as cnt FROM evaluation GROUP BY rating ORDER BY rating DESC");
$dist_data = [];
while($d = $dist_res->fetch_assoc()) $dist_data[] = $d;
$dist_map = [];
foreach($dist_data as $d) $dist_map[(int)$d['rating']] = (int)$d['cnt'];

$top_res = $conn->query("SELECT teacher_name, department, AVG(rating) as avg_r, COUNT(*) as cnt FROM evaluation GROUP BY teacher_name, department ORDER BY avg_r DESC LIMIT 5");
$top_teachers = [];
while($t = $top_res->fetch_assoc()) $top_teachers[] = $t;

$dept_chart_res = $conn->query("SELECT department, COUNT(*) as cnt, AVG(rating) as avg_r FROM evaluation GROUP BY department ORDER BY avg_r DESC");
$dept_chart = [];
while($dc = $dept_chart_res->fetch_assoc()) $dept_chart[] = $dc;

$tc_res = $conn->query("SELECT COUNT(DISTINCT teacher_name) as tc FROM evaluation");
$tc = $tc_res->fetch_assoc();

// ── BUILD EVALUATION TREE: dept → teacher → year_level → [evals] ──────────
$eval_tree = [];
$all_evals_res = $conn->query("SELECT * FROM evaluation ORDER BY department, teacher_name, year_level, id DESC");
while($ev = $all_evals_res->fetch_assoc()) {
    $dept    = $ev['department']  ?: 'Unassigned';
    $teacher = $ev['teacher_name'];
    $year    = $ev['year_level']  ?: 'Unspecified';
    if (!isset($eval_tree[$dept][$teacher])) {
        $eval_tree[$dept][$teacher] = ['ratings' => [], 'years' => []];
    }
    $eval_tree[$dept][$teacher]['ratings'][] = (float)$ev['rating'];
    $eval_tree[$dept][$teacher]['years'][$year][] = $ev;
}
// Pre-compute averages
foreach ($eval_tree as $dept => &$teachers) {
    foreach ($teachers as $tname => &$tdata) {
        $tdata['avg_r'] = count($tdata['ratings'])
            ? round(array_sum($tdata['ratings']) / count($tdata['ratings']), 2) : 0;
        $tdata['total'] = count($tdata['ratings']);
        ksort($tdata['years']);
    }
    unset($tdata);
    uasort($teachers, fn($a,$b) => $b['avg_r'] <=> $a['avg_r']);
}
unset($teachers);
ksort($eval_tree);

// Dept summaries
$dept_summaries = [];
foreach ($eval_tree as $dept => $teachers) {
    $all_ratings = [];
    foreach ($teachers as $t) $all_ratings = array_merge($all_ratings, $t['ratings']);
    $dept_summaries[$dept] = [
        'teachers' => count($teachers),
        'total'    => count($all_ratings),
        'avg'      => count($all_ratings) ? round(array_sum($all_ratings)/count($all_ratings),2) : 0,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | OLSHCO Faculty Evaluation</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,700;0,9..144,900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="admin_style.css">
</head>
<body>

<!-- ══════════ LOGO AREA ══════════ -->
<div class="header-logo-area">
  <img src="../uploads/logo.png" alt="OLSHCO Logo" class="logo-img"
       onerror="this.style.display='none';document.getElementById('logo-fallback').style.display='flex';">
  <div id="logo-fallback" class="logo-mark" style="display:none;">O</div>
  <div class="logo-text"><h1>OLSHCO</h1><span>Admin Panel</span></div>
</div>

<!-- ══════════ HEADER ══════════ -->
<header>
  <div class="header-search">
    <i class="bi bi-search"></i>
    <input type="text" placeholder="Search evaluations, faculty, departments…">
  </div>
  <div class="header-right">
    <div class="hdr-divider"></div>
    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?></div>
    <div>
      <div class="hdr-name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Administrator'); ?></div>
      <div class="hdr-role">System Administrator</div>
    </div>
  </div>
</header>

<!-- ══════════ SIDEBAR ══════════ -->
<nav class="sidebar">
  <div class="sidebar-label">Main</div>
  <button class="nav-item active" onclick="showSection('dashboard', this)">
    <i class="bi bi-grid-fill"></i> Dashboard
  </button>
  <div class="sidebar-divider"></div>
  <div class="sidebar-label">Evaluations</div>
  <button class="nav-item" onclick="showSection('evaluations', this)">
    <i class="bi bi-table"></i> View Evaluations
    <span class="nav-badge"><?php echo $total_data['total']; ?></span>
  </button>
  <button class="nav-item" onclick="showSection('departments', this)">
    <i class="bi bi-building"></i> By Department
    <span class="nav-badge"><?php echo $dept_data['depts']; ?></span>
  </button>
  <div class="sidebar-divider"></div>
  <div class="sidebar-label">Reports</div>
  <button class="nav-item" onclick="showSection('summary', this)">
    <i class="bi bi-bar-chart-fill"></i> Summary Report
  </button>
  <div class="sidebar-footer">
    <a href="admin_logout.php" class="nav-item logout-nav">
      <i class="bi bi-box-arrow-left"></i> Logout
    </a>
  </div>
</nav>

<!-- ══════════ MAIN ══════════ -->
<main class="main">

<!-- ████ DASHBOARD ████ -->
<div class="section active" id="sec-dashboard">
  <div class="page-header">
    <div class="page-header-left">
      <h2>Performance Overview</h2>
      <p><i class="bi bi-shield-fill-check"></i> Student identities are fully anonymized.</p>
    </div>
    <div class="live-badge">Live Data</div>
  </div>
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-card-header">
        <div class="stat-icon-wrap maroon"><i class="bi bi-file-earmark-bar-graph-fill"></i></div>
        <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> Active</div>
      </div>
      <div class="stat-value"><?php echo $total_data['total']; ?></div>
      <div class="stat-label">Total Evaluations</div>
      <div class="stat-bar"><div class="stat-bar-fill" style="width:72%"></div></div>
    </div>
    <div class="stat-card">
      <div class="stat-card-header">
        <div class="stat-icon-wrap gold"><i class="bi bi-star-fill"></i></div>
        <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> +0.3</div>
      </div>
      <div class="stat-value"><?php echo number_format($avg_data['average'] ?? 0, 2); ?><sub> / 5</sub></div>
      <div class="stat-label">Overall Avg Rating</div>
      <div class="stat-bar"><div class="stat-bar-fill" style="width:<?php echo ($avg_data['average'] ?? 0)/5*100; ?>%"></div></div>
    </div>
    <div class="stat-card">
      <div class="stat-card-header">
        <div class="stat-icon-wrap muted"><i class="bi bi-building-fill"></i></div>
        <div class="stat-trend flat">Departments</div>
      </div>
      <div class="stat-value"><?php echo $dept_data['depts']; ?></div>
      <div class="stat-label">Active Departments</div>
      <div class="stat-bar"><div class="stat-bar-fill" style="width:60%"></div></div>
    </div>
    <div class="stat-card">
      <div class="stat-card-header">
        <div class="stat-icon-wrap green"><i class="bi bi-person-check-fill"></i></div>
        <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> Rated</div>
      </div>
      <div class="stat-value"><?php echo $tc['tc']; ?></div>
      <div class="stat-label">Faculty Evaluated</div>
      <div class="stat-bar"><div class="stat-bar-fill" style="width:80%"></div></div>
    </div>
  </div>
  <div class="grid-2-1">
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title"><div class="panel-title-icon gold"><i class="bi bi-bar-chart-fill"></i></div>Department Performance</div>
        <span class="panel-tag">Avg Rating by Dept</span>
      </div>
      <div class="panel-body"><div class="chart-wrap" style="height:220px;"><canvas id="deptBarChart"></canvas></div></div>
    </div>
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title"><div class="panel-title-icon"><i class="bi bi-reception-4"></i></div>Teaching Metrics</div>
        <span class="panel-tag">Radar</span>
      </div>
      <div class="panel-body">
        <div class="radar-row">
          <div class="radar-wrap"><canvas id="radarChart" height="190"></canvas></div>
          <div class="radar-legend">
            <?php
            $metrics = [['Teaching Quality',87,'#7a0000'],['Student Feedback',74,'#c9a84c'],['Curriculum',80,'#9b0000'],['Engagement',68,'#e8c96d'],['Assessment',77,'#7a6a55']];
            foreach($metrics as $m): ?>
            <div class="radar-metric">
              <div class="metric-dot" style="background:<?php echo $m[2]; ?>"></div>
              <div class="metric-info">
                <div class="metric-label"><?php echo $m[0]; ?></div>
                <div class="metric-bar-bg"><div class="metric-bar-fill" style="width:<?php echo $m[1]; ?>%;background:<?php echo $m[2]; ?>;"></div></div>
              </div>
              <div class="metric-val"><?php echo $m[1]; ?>%</div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="grid-1-1">
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title"><div class="panel-title-icon gold"><i class="bi bi-trophy-fill"></i></div>Top Rated Faculty</div>
        <span class="panel-tag">Highest Average</span>
      </div>
      <div class="panel-body">
        <div class="teacher-list">
          <?php foreach($top_teachers as $i => $t):
            $initials = implode('', array_map(fn($w)=>strtoupper($w[0]), array_slice(explode(' ',$t['teacher_name']),0,2)));
          ?>
          <div class="teacher-item">
            <div class="teacher-rank <?php echo $i===0?'gold-rank':''; ?>"><?php echo $i+1; ?></div>
            <div class="teacher-avatar"><?php echo $initials; ?></div>
            <div class="teacher-info">
              <div class="teacher-name"><?php echo htmlspecialchars($t['teacher_name']); ?></div>
              <div class="teacher-dept"><?php echo htmlspecialchars($t['department']); ?></div>
            </div>
            <div style="text-align:right;">
              <div class="teacher-score"><?php echo number_format($t['avg_r'],2); ?></div>
              <div class="teacher-count"><?php echo $t['cnt']; ?> evals</div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if(empty($top_teachers)): ?><p style="color:var(--muted);font-size:0.82rem;font-style:italic;">No faculty data yet.</p><?php endif; ?>
        </div>
      </div>
    </div>
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title"><div class="panel-title-icon"><i class="bi bi-distribute-vertical"></i></div>Rating Distribution</div>
        <span class="panel-tag">1–5 Stars</span>
      </div>
      <div class="panel-body">
        <div class="chart-wrap" style="height:170px;margin-bottom:16px;"><canvas id="doughnutChart"></canvas></div>
        <div class="dist-list">
          <?php
          $max_cnt = max(array_values($dist_map) ?: [1]);
          for($star=5;$star>=1;$star--):
            $cnt=$dist_map[$star]??0; $pct=$max_cnt>0?($cnt/$max_cnt*100):0;
          ?>
          <div class="dist-row">
            <div class="dist-label"><?php echo $star; ?><i class="bi bi-star-fill star-icon"></i></div>
            <div class="dist-bar-bg"><div class="dist-bar-fill" style="width:<?php echo $pct; ?>%"></div></div>
            <div class="dist-cnt"><?php echo $cnt; ?></div>
          </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </div>
</div><!-- /dashboard -->


<!-- ████ VIEW EVALUATIONS — dept tabs → teacher accordion → year groups ████ -->
<div class="section" id="sec-evaluations">
  <div class="page-header">
    <div class="page-header-left">
      <h2>Faculty Evaluations</h2>
      <p><i class="bi bi-layers-fill"></i> Grouped by department. Click any teacher row to expand their evaluations.</p>
    </div>
  </div>

  <?php if(empty($eval_tree)): ?>
  <div class="panel"><div class="panel-body" style="text-align:center;padding:60px 20px;color:var(--muted);">
    <i class="bi bi-inbox" style="font-size:2.5rem;display:block;margin-bottom:12px;"></i>No evaluation records found.
  </div></div>
  <?php else: ?>

  <!-- ── DEPT TABS ── -->
  <div class="ev-dept-tabs">
    <?php $di=0; foreach($eval_tree as $dept => $teachers): $ds=$dept_summaries[$dept]; ?>
    <button class="ev-dept-tab <?php echo $di===0?'active':''; ?>"
            onclick="switchDeptTab(<?php echo $di; ?>, this)">
      <span class="ev-tab-dname"><?php echo htmlspecialchars($dept); ?></span>
      <span class="ev-tab-chips">
        <span class="ev-tab-chip"><?php echo $ds['teachers']; ?> faculty</span>
        <span class="ev-tab-chip"><?php echo $ds['total']; ?> evals</span>
        <span class="ev-tab-chip ev-tab-avg"><?php echo $ds['avg']; ?> avg</span>
      </span>
    </button>
    <?php $di++; endforeach; ?>
  </div>

  <!-- ── DEPT PANELS ── -->
  <?php $di=0; foreach($eval_tree as $dept => $teachers): $ds=$dept_summaries[$dept]; ?>
  <div class="ev-dept-panel <?php echo $di===0?'active':''; ?>" id="evpanel-<?php echo $di; ?>">

    <!-- Dept stats bar -->
    <div class="ev-dept-summary">
      <div class="ev-summary-stat"><i class="bi bi-person-lines-fill"></i><span><strong><?php echo $ds['teachers']; ?></strong> Faculty Members</span></div>
      <div class="ev-summary-stat"><i class="bi bi-clipboard-data-fill"></i><span><strong><?php echo $ds['total']; ?></strong> Total Evaluations</span></div>
      <div class="ev-summary-stat"><i class="bi bi-star-fill" style="color:var(--gold)"></i><span><strong><?php echo $ds['avg']; ?></strong> / 5.00 Dept Average</span></div>
      <div class="ev-search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Filter teachers…"
               oninput="filterTeachers('evpanel-<?php echo $di; ?>', this.value)">
      </div>
    </div>

    <!-- Teacher accordion -->
    <div class="ev-teacher-list">
      <?php foreach($teachers as $tname => $tdata):
        $initials = implode('', array_map(fn($w)=>strtoupper($w[0]), array_slice(explode(' ',$tname),0,2)));
        $avg_r    = $tdata['avg_r'];
        $total    = $tdata['total'];
        $avg_int  = (int)round($avg_r);
        $stars_f  = str_repeat('★',$avg_int);
        $stars_e  = str_repeat('★',5-$avg_int);
        // year counts for pills
        $year_counts = [];
        foreach($tdata['years'] as $yr => $evs) $year_counts[$yr] = count($evs);
        arsort($year_counts);
        if($avg_r>=4){$perf='Excellent';$pcls='excellent';}
        elseif($avg_r>=3){$perf='Good';$pcls='good';}
        else{$perf='Needs Work';$pcls='needs';}
      ?>
      <div class="ev-teacher-row" data-name="<?php echo strtolower(htmlspecialchars($tname)); ?>">

        <!-- Clickable header -->
        <div class="ev-teacher-header" onclick="toggleTeacher(this)">
          <div class="ev-teacher-avatar"><?php echo $initials; ?></div>

          <div class="ev-teacher-info">
            <div class="ev-teacher-name"><?php echo htmlspecialchars($tname); ?></div>
            <!-- Year level breakdown pills -->
            <div class="ev-year-pills">
              <?php foreach($year_counts as $yr => $cnt): ?>
              <span class="ev-year-pill">
                <?php echo htmlspecialchars($yr); ?>
                <span class="ev-year-cnt"><?php echo $cnt; ?></span>
              </span>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="ev-teacher-stats">
            <div class="ev-teacher-rating">
              <span class="ev-r-num"><?php echo number_format($avg_r,2); ?></span>
              <div class="ev-r-stars">
                <span class="stars"><?php echo $stars_f; ?></span><span class="stars-gray"><?php echo $stars_e; ?></span>
              </div>
            </div>
            <div class="ev-teacher-meta">
              <span class="ev-eval-count"><i class="bi bi-clipboard-check"></i> <?php echo $total; ?> eval<?php echo $total!==1?'s':''; ?></span>
              <span class="dept-perf-badge <?php echo $pcls; ?>"><?php echo $perf; ?></span>
            </div>
          </div>

          <div class="ev-chevron"><i class="bi bi-chevron-down"></i></div>
        </div>

        <!-- Expandable body: evaluations grouped by year level -->
        <div class="ev-teacher-body">
          <?php foreach($tdata['years'] as $yr => $evs): ?>
          <div class="ev-year-group">
            <div class="ev-year-label">
              <i class="bi bi-mortarboard-fill"></i>
              <?php echo htmlspecialchars($yr); ?>
              <span class="ev-year-count"><?php echo count($evs); ?> student<?php echo count($evs)!==1?'s':''; ?> evaluated</span>
            </div>
            <div class="tbl-wrap">
              <table class="ev-inner-table">
                <thead>
                  <tr>
                    <th>Ref</th>
                    <th>Semester</th>
                    <th>Term</th>
                    <th>Rating</th>
                    <th>Comm</th>
                    <th>Mastery</th>
                    <th>Punctual</th>
                    <th>Engage</th>
                    <th>Fairness</th>
                    <th>Materials</th>
                    <th>Sentiment</th>
                    <th>Feedback</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($evs as $ev):
                    $r=(int)round((float)$ev['rating']);
                    $sf=str_repeat('★',$r); $se=str_repeat('★',5-$r);
                    $sent=strtolower($ev['sentiment']??'neutral');
                  ?>
                  <tr>
                    <td><span class="cell-id">#<?php echo $ev['id']; ?></span></td>
                    <td><span class="ev-meta-chip"><?php echo htmlspecialchars($ev['semester']?:'—'); ?></span></td>
                    <td><span class="ev-meta-chip"><?php echo htmlspecialchars($ev['term']?:'—'); ?></span></td>
                    <td>
                      <div class="rating-chip">
                        <span class="rating-num"><?php echo $ev['rating']; ?></span>
                        <div><span class="stars"><?php echo $sf; ?></span><span class="stars-gray"><?php echo $se; ?></span></div>
                      </div>
                    </td>
                    <td><span class="ev-cat-score"><?php echo $ev['rating_comm']     ?? '—'; ?></span></td>
                    <td><span class="ev-cat-score"><?php echo $ev['rating_mastery']  ?? '—'; ?></span></td>
                    <td><span class="ev-cat-score"><?php echo $ev['rating_punctual'] ?? '—'; ?></span></td>
                    <td><span class="ev-cat-score"><?php echo $ev['rating_engage']   ?? '—'; ?></span></td>
                    <td><span class="ev-cat-score"><?php echo $ev['rating_fairness'] ?? '—'; ?></span></td>
                    <td><span class="ev-cat-score"><?php echo $ev['rating_materials']?? '—'; ?></span></td>
                    <td><span class="badge <?php echo $sent; ?>"><?php echo ucfirst($sent); ?></span></td>
                    <td><span class="cell-feedback"><?php echo htmlspecialchars($ev['feedback']); ?></span></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div><!-- /ev-year-group -->
          <?php endforeach; ?>
        </div><!-- /ev-teacher-body -->

      </div><!-- /ev-teacher-row -->
      <?php endforeach; ?>
    </div><!-- /ev-teacher-list -->

  </div><!-- /ev-dept-panel -->
  <?php $di++; endforeach; ?>

  <?php endif; ?>
</div><!-- /evaluations -->


<!-- ████ BY DEPARTMENT ████ -->
<div class="section" id="sec-departments">
  <div class="page-header">
    <div class="page-header-left"><h2>By Department</h2><p><i class="bi bi-building"></i> Evaluation analytics per academic department.</p></div>
  </div>
  <div class="dept-grid">
  <?php
  $dr=$conn->query("SELECT department, COUNT(*) as cnt, AVG(rating) as avg_r FROM evaluation GROUP BY department ORDER BY avg_r DESC");
  if($dr && $dr->num_rows>0){
    while($d=$dr->fetch_assoc()){
      $avg=(float)$d['avg_r']; $pct=$avg/5*100;
      if($avg>=4){$perf='Excellent';$cls='excellent';}
      elseif($avg>=3){$perf='Good';$cls='good';}
      else{$perf='Needs Work';$cls='needs';}
      echo "<div class='dept-card'>
        <div class='dept-card-top'><div class='dept-icon-bg'><i class='bi bi-building'></i></div><span class='dept-perf-badge {$cls}'>{$perf}</span></div>
        <div class='dept-name'>".htmlspecialchars($d['department'])."</div>
        <div class='dept-stats'>
          <div><div class='dept-stat-value'>".number_format($avg,2)."</div><div class='dept-stat-label'>Avg Rating</div></div>
          <div class='dept-divider'></div>
          <div><div class='dept-stat-value'>{$d['cnt']}</div><div class='dept-stat-label'>Evaluations</div></div>
        </div>
        <div class='dept-mini-bar'><div class='dept-mini-bar-fill' style='width:{$pct}%;'></div></div>
      </div>";
    }
  } else { echo "<p style='color:var(--muted);font-size:0.85rem;font-style:italic;'>No department data yet.</p>"; }
  ?>
  </div>
</div><!-- /departments -->


<!-- ████ SUMMARY REPORT ████ -->
<div class="section" id="sec-summary">
  <div class="page-header">
    <div class="page-header-left"><h2>Summary Report</h2><p><i class="bi bi-bar-chart-fill"></i> Aggregate statistics across all departments.</p></div>
  </div>
  <div class="summary-kpi">
    <div class="kpi-block"><div class="kpi-val"><?php echo $total_data['total']; ?></div><div class="kpi-label">Total Evaluations</div></div>
    <div class="kpi-block"><div class="kpi-val accent"><?php echo number_format($avg_data['average']??0,2); ?></div><div class="kpi-label">Overall Average / 5</div></div>
    <div class="kpi-block"><div class="kpi-val"><?php echo $dept_data['depts']; ?></div><div class="kpi-label">Departments</div></div>
  </div>
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title"><div class="panel-title-icon gold"><i class="bi bi-bar-chart-fill"></i></div>Department Summary</div>
      <span class="panel-tag">Performance Ranking</span>
    </div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>Department</th><th>Total Evaluations</th><th>Average Rating</th><th>Rating Bar</th><th>Performance</th></tr></thead>
        <tbody>
        <?php
        $sr=$conn->query("SELECT department, COUNT(*) as cnt, AVG(rating) as avg_r FROM evaluation GROUP BY department ORDER BY avg_r DESC");
        if($sr && $sr->num_rows>0){
          while($s=$sr->fetch_assoc()){
            $avg=(float)$s['avg_r']; $pct=$avg/5*100;
            if($avg>=4){$perf='Excellent';$cls='excellent';}
            elseif($avg>=3){$perf='Good';$cls='good';}
            else{$perf='Needs Improvement';$cls='needs';}
            echo "<tr>
              <td><span class='cell-dept'>".htmlspecialchars($s['department'])."</span></td>
              <td>{$s['cnt']}</td>
              <td><span class='rating-num' style='font-size:1rem;'>".number_format($avg,2)."</span> <span style='color:var(--muted);font-size:0.72rem;'>/ 5</span></td>
              <td style='min-width:130px;'><div class='dist-bar-bg'><div class='dist-bar-fill' style='width:{$pct}%;'></div></div></td>
              <td><span class='perf-badge {$cls}'>{$perf}</span></td>
            </tr>";
          }
        } else { echo "<tr class='no-data'><td colspan='5'>No data yet.</td></tr>"; }
        ?>
        </tbody>
      </table>
    </div>
  </div>
</div><!-- /summary -->

</main>

<script>
/* ── SECTION NAVIGATION ── */
function showSection(name, el) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.getElementById('sec-' + name)?.classList.add('active');
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  if (el) el.classList.add('active');
}

/* ── DEPT TAB SWITCHER ── */
function switchDeptTab(idx, btn) {
  document.querySelectorAll('.ev-dept-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.ev-dept-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('evpanel-' + idx)?.classList.add('active');
}

/* ── TEACHER ACCORDION ── */
function toggleTeacher(headerEl) {
  const row  = headerEl.closest('.ev-teacher-row');
  const body = row.querySelector('.ev-teacher-body');
  const wasOpen = row.classList.contains('open');

  // Close all siblings first
  row.closest('.ev-teacher-list').querySelectorAll('.ev-teacher-row.open').forEach(r => {
    r.classList.remove('open');
    r.querySelector('.ev-teacher-body').style.maxHeight = '0';
  });

  if (!wasOpen) {
    row.classList.add('open');
    body.style.maxHeight = body.scrollHeight + 'px';
    // Allow scroll growth inside
    setTimeout(() => { if(row.classList.contains('open')) body.style.maxHeight = 'none'; }, 320);
  }
}

/* ── TEACHER SEARCH ── */
function filterTeachers(panelId, q) {
  const lq = q.toLowerCase().trim();
  document.getElementById(panelId)?.querySelectorAll('.ev-teacher-row').forEach(row => {
    row.style.display = (!lq || (row.dataset.name||'').includes(lq)) ? '' : 'none';
  });
}

/* ── CHARTS ── */
const deptLabels = <?php echo json_encode(array_column($dept_chart,'department')); ?>;
const deptAvgs   = <?php echo json_encode(array_map(fn($d)=>round((float)$d['avg_r'],2),$dept_chart)); ?>;
const distCounts = <?php $arr=[];for($i=1;$i<=5;$i++)$arr[]=$dist_map[$i]??0;echo json_encode($arr); ?>;

Chart.defaults.color='#7a6a55'; Chart.defaults.font.family="'Sora',sans-serif";

new Chart(document.getElementById('deptBarChart'),{type:'bar',data:{labels:deptLabels,datasets:[{label:'Avg Rating',data:deptAvgs,backgroundColor:'rgba(122,0,0,0.12)',borderColor:'#7a0000',borderWidth:1.5,borderRadius:8,borderSkipped:false,hoverBackgroundColor:'rgba(201,168,76,0.22)',hoverBorderColor:'#c9a84c'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.y} / 5`}}},scales:{x:{grid:{color:'rgba(122,0,0,0.05)'},ticks:{maxRotation:30,font:{size:10}}},y:{min:0,max:5,grid:{color:'rgba(122,0,0,0.05)'},ticks:{stepSize:1,font:{size:10}}}}}});

new Chart(document.getElementById('radarChart'),{type:'radar',data:{labels:['Teaching','Feedback','Curriculum','Engagement','Assessment'],datasets:[{label:'Score',data:[87,74,80,68,77],backgroundColor:'rgba(122,0,0,0.07)',borderColor:'rgba(122,0,0,0.55)',borderWidth:2,pointBackgroundColor:'#7a0000',pointBorderColor:'#fff',pointRadius:4}]},options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{r:{min:0,max:100,grid:{color:'rgba(122,0,0,0.07)'},angleLines:{color:'rgba(122,0,0,0.07)'},ticks:{display:false},pointLabels:{font:{size:10},color:'#7a6a55'}}}}});

const nonZero=distCounts.some(v=>v>0);
new Chart(document.getElementById('doughnutChart'),{type:'doughnut',data:{labels:['1 ★','2 ★','3 ★','4 ★','5 ★'],datasets:[{data:nonZero?distCounts:[1,1,1,1,1],backgroundColor:['#c0392b','#c9a84c80','#c9a84c','#9b0000','#7a0000'],borderColor:'#fff',borderWidth:3,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'66%',plugins:{legend:{position:'right',labels:{boxWidth:10,font:{size:11},color:'#7a6a55',padding:10}}}}});
</script>
</body>
</html>