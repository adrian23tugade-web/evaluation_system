<?php
include "db.php";
if(!isset($_SESSION['username'])){ header("Location: login.php"); exit(); }


$total_evals = 0; $avg_rating = 0; $total_teachers = 0;
$r = $conn->query("SELECT COUNT(*) as c FROM evaluation");
if($r) $total_evals = $r->fetch_assoc()['c'];
$r2 = $conn->query("SELECT AVG(rating) as a FROM evaluation");
if($r2) $avg_rating = round($r2->fetch_assoc()['a'] ?? 0, 2);
$r3 = $conn->query("SELECT COUNT(*) as c FROM teachers");
if($r3) $total_teachers = $r3->fetch_assoc()['c'];

$dept_counts = []; $dept_avgs = [];
$depts_list = ['BSIT','BSHM','BSEDUC','BSCRIM','BSOAD'];
$dept_full  = ['BSIT'=>'Information Technology','BSHM'=>'Hospitality Management','BSEDUC'=>'Education','BSCRIM'=>'Criminology','BSOAD'=>'Office Administration'];
foreach($depts_list as $d){
    $rd = $conn->prepare("SELECT COUNT(*) as c, AVG(rating) as a FROM evaluation WHERE department = ?");
    $rd->bind_param("s", $d); $rd->execute();
    $row_d = $rd->get_result()->fetch_assoc();
    $dept_counts[$d] = (int)$row_d['c'];
    $dept_avgs[$d]   = round((float)($row_d['a'] ?? 0), 1);
}
$max_dept = max(array_values($dept_counts) ?: [1]);

$ring1_pct = $total_evals   > 0 ? min(100, round($total_evals / 100 * 100)) : 0;
$ring2_pct = $avg_rating    > 0 ? round(($avg_rating / 5) * 100) : 0;
$ring3_pct = $total_teachers> 0 ? min(100, round($total_teachers / 50 * 100)) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Statistics | OLSHCO Faculty Evaluation</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="css/statistics_style.css">
</head>
<body>

<header class="site-header">
  <div class="header-brand">
    <button class="sidebar-toggle" id="sidebarToggleBtn" aria-label="Toggle sidebar"><i class="fas fa-bars"></i></button>
    <img src="uploads/logo.png" alt="OLSHCO Logo">
    <div class="brand-text">
      <span class="brand-name">Our Lady of the Sacred Heart College</span>
      <span class="brand-sub">Faculty Evaluation System</span>
    </div>
  </div>
  <div class="header-actions">
    <div class="user-chip"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></div>
  </div>
</header>

<div class="layout">

  <aside class="sidebar" id="sidebar">
    <div class="sb-brand">
    </div>
    <div class="sb-section-label">Navigation</div>
    <a href="index.php" class="sb-btn"><i class="fas fa-house"></i><span>Dashboard</span></a>
    <a href="view.php"      class="sb-btn"><i class="fas fa-eye"></i><span>View Evaluations</span></a>
    <div class="sb-divider"></div>
    <div class="sb-section-label">Data</div>
    <a href="statistics.php" class="sb-btn sb-active"><i class="fas fa-chart-pie"></i><span>Statistics</span></a>
    <a href="analytics.php"  class="sb-btn"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
    <a href="identity.php"   class="sb-btn"><i class="fas fa-landmark"></i><span>Our Identity</span></a>
    <div class="sb-divider"></div>
    <a href="evaluate.php" class="sb-btn sb-gold"><i class="fas fa-plus"></i><span>New Evaluation</span></a>
    <div class="sb-footer">
      <a href="logout.php" class="sb-btn sb-logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>
  </aside>

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="page">

    <!-- Page header -->
    <div class="page-eyebrow"><i class="fas fa-chart-pie"></i> Numbers at a Glance</div>
    <h1 class="page-title">Statistics</h1>
    <p class="page-sub">A high-level summary of all evaluation data across faculty, departments, and rating performance.</p>

    <!-- Stat cards -->
    <div class="stats-grid">

      <div class="stat-card">
        <div class="stat-top">
          <span class="stat-badge"><i class="fas fa-clipboard-check"></i> Evaluations</span>
          <svg class="stat-ring" viewBox="0 0 48 48">
            <circle class="ring-bg"            cx="24" cy="24" r="18"/>
            <circle class="ring-fill ring-maroon" cx="24" cy="24" r="18" data-pct="<?php echo $ring1_pct; ?>"/>
            <text class="ring-text" fill="#7a0000" x="24" y="24"><?php echo $total_evals > 99 ? '99+' : $total_evals; ?></text>
          </svg>
        </div>
        <div class="stat-value">
          <span class="cnt-val" data-target="<?php echo $total_evals; ?>" data-dec="0">0</span>
        </div>
        <div class="stat-label">Total Evaluations</div>
        <div class="stat-sub">All submitted records to date</div>
        <span class="stat-wm"><i class="fas fa-clipboard-check"></i></span>
      </div>

      <div class="stat-card">
        <div class="stat-top">
          <span class="stat-badge"><i class="fas fa-star"></i> Rating</span>
          <svg class="stat-ring" viewBox="0 0 48 48">
            <circle class="ring-bg"          cx="24" cy="24" r="18"/>
            <circle class="ring-fill ring-gold" cx="24" cy="24" r="18" data-pct="<?php echo $ring2_pct; ?>"/>
            <text class="ring-text" fill="#c9a84c" x="24" y="24"><?php echo $avg_rating ?: '—'; ?></text>
          </svg>
        </div>
        <div class="stat-value">
          <span class="cnt-val" data-target="<?php echo $avg_rating; ?>" data-dec="1">0.0</span>
          <span class="stat-unit">/ 5</span>
        </div>
        <div class="stat-label">Average Rating</div>
        <div class="stat-sub">Across all departments</div>
        <span class="stat-wm"><i class="fas fa-star"></i></span>
      </div>

      <div class="stat-card">
        <div class="stat-top">
          <span class="stat-badge"><i class="fas fa-chalkboard-teacher"></i> Faculty</span>
          <svg class="stat-ring" viewBox="0 0 48 48">
            <circle class="ring-bg"           cx="24" cy="24" r="18"/>
            <circle class="ring-fill ring-teal"  cx="24" cy="24" r="18" data-pct="<?php echo $ring3_pct; ?>"/>
            <text class="ring-text" fill="#2e8b6e" x="24" y="24"><?php echo $total_teachers; ?></text>
          </svg>
        </div>
        <div class="stat-value">
          <span class="cnt-val" data-target="<?php echo $total_teachers; ?>" data-dec="0">0</span>
        </div>
        <div class="stat-label">Active Faculty</div>
        <div class="stat-sub">Currently active across all depts</div>
        <span class="stat-wm"><i class="fas fa-chalkboard-teacher"></i></span>
      </div>

    </div>

    <!-- Dept Breakdown -->
    <div class="dept-breakdown">
      <div class="db-header">
        <div class="db-title"><i class="fas fa-layer-group"></i> Evaluations by Department</div>
      </div>
      <?php foreach($dept_counts as $dept => $cnt):
        $pct = $max_dept > 0 ? round(($cnt / $max_dept) * 100) : 0;
        $avg = $dept_avgs[$dept];
        $full = $dept_full[$dept] ?? '';
      ?>
      <div class="db-row">
        <div class="db-dept-wrap">
          <span class="db-dept"><?php echo $dept; ?></span>
          <span class="db-dept-full"><?php echo $full; ?></span>
        </div>
        <div class="db-track"><div class="db-fill" data-w="<?php echo $pct; ?>"></div></div>
        <span class="db-count"><?php echo $cnt; ?></span>
        <span class="db-avg"><?php echo $avg > 0 ? '★ '.$avg : '—'; ?></span>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<script>
(function(){
  var btn=document.getElementById('sidebarToggleBtn');
  var sb=document.getElementById('sidebar');
  var ov=document.getElementById('sidebarOverlay');
  function t(){ sb.classList.toggle('open'); ov.classList.toggle('show'); }
  btn.addEventListener('click',t); ov.addEventListener('click',t);
})();

(function(){
  const C = 2 * Math.PI * 18;
  document.querySelectorAll('.ring-fill').forEach(function(ring){
    var pct = parseFloat(ring.dataset.pct) || 0;
    ring.style.strokeDasharray  = C + ' ' + C;
    ring.style.strokeDashoffset = C;
    setTimeout(function(){ ring.style.strokeDashoffset = C - (C * pct / 100); }, 120);
  });

  document.querySelectorAll('.db-fill').forEach(function(bar){
    bar.style.width = '0%';
    setTimeout(function(){ bar.style.width = (bar.dataset.w || '0') + '%'; }, 180);
  });

  document.querySelectorAll('.cnt-val').forEach(function(el){
    var target = parseFloat(el.dataset.target) || 0;
    var dec    = parseInt(el.dataset.dec) || 0;
    var start  = null;
    (function step(ts){
      if(!start) start = ts;
      var p = Math.min((ts - start) / 1400, 1);
      var ease = 1 - Math.pow(1 - p, 3);
      el.textContent = (target * ease).toFixed(dec);
      if(p < 1) requestAnimationFrame(step);
      else el.textContent = target.toFixed(dec);
    })(performance.now());
  });
})();
</script>
</body>
</html>