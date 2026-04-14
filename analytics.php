<?php
include "db.php";
if(!isset($_SESSION['username'])){ header("Location: login.php"); exit(); }


$dept_avgs = [];
$depts_list = ['BSIT','BSHM','BSEDUC','BSCRIM','BSOAD'];
foreach($depts_list as $d){
    $rd = $conn->prepare("SELECT AVG(rating) as a FROM evaluation WHERE department = ?");
    $rd->bind_param("s", $d); $rd->execute();
    $dept_avgs[$d] = round((float)($rd->get_result()->fetch_assoc()['a'] ?? 0), 1);
}

$top_teachers = [];
$rt = $conn->query("SELECT teacher_name, department, AVG(rating) as avg_r, COUNT(*) as evals
                    FROM evaluation GROUP BY teacher_name, department
                    HAVING evals >= 1 ORDER BY avg_r DESC LIMIT 5");
if($rt) while($t = $rt->fetch_assoc()) $top_teachers[] = $t;

$monthly = [];
for($m = 5; $m >= 0; $m--){
    $label = date('M', strtotime("-$m months"));
    $ym    = date('Y-m', strtotime("-$m months"));
    $rm = $conn->prepare("SELECT COUNT(*) as c FROM evaluation WHERE DATE_FORMAT(date_submitted,'%Y-%m') = ?");
    if($rm){ $rm->bind_param("s",$ym); $rm->execute(); $monthly[$label]=(int)$rm->get_result()->fetch_assoc()['c']; }
    else { $monthly[$label]=0; }
}

$bar_labels = json_encode(array_keys($dept_avgs));
$bar_data   = json_encode(array_values($dept_avgs));
$bar_config = "{type:'bar',data:{labels:{$bar_labels},datasets:[{label:'Avg Rating',data:{$bar_data},backgroundColor:['rgba(122,0,0,0.85)','rgba(201,168,76,0.85)','rgba(122,0,0,0.6)','rgba(201,168,76,0.6)','rgba(46,139,110,0.75)'],borderRadius:8,borderSkipped:false}]},options:{plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{color:'#7a6a55',font:{size:11}}},y:{min:0,max:5,grid:{color:'rgba(122,0,0,0.05)'},ticks:{color:'#7a6a55',font:{size:11},stepSize:1}}},animation:false}}";
$chart_bar_url = "https://quickchart.io/chart?c=".urlencode($bar_config)."&w=520&h=210&bkg=white&f=DM+Sans";

$line_labels = json_encode(array_keys($monthly));
$line_data   = json_encode(array_values($monthly));
$line_config = "{type:'line',data:{labels:{$line_labels},datasets:[{label:'Submissions',data:{$line_data},borderColor:'#7a0000',borderWidth:2.5,backgroundColor:'rgba(122,0,0,0.07)',pointBackgroundColor:'#7a0000',pointBorderColor:'#fff',pointBorderWidth:2,pointRadius:5,fill:true,tension:0.4}]},options:{plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{color:'#7a6a55',font:{size:11}}},y:{min:0,grid:{color:'rgba(122,0,0,0.05)'},ticks:{color:'#7a6a55',font:{size:11},stepSize:1}}},animation:false}}";
$chart_line_url = "https://quickchart.io/chart?c=".urlencode($line_config)."&w=520&h=210&bkg=white&f=DM+Sans";

$doughnut_labels=[]; $doughnut_data=[];
foreach($dept_avgs as $d=>$a){ if($a>0){ $doughnut_labels[]=$d; $doughnut_data[]=$a; } }
$chart_donut_url = "";
if(!empty($doughnut_data)){
    $dl=json_encode($doughnut_labels); $dd=json_encode($doughnut_data);
    $donut_config="{type:'doughnut',data:{labels:{$dl},datasets:[{data:{$dd},backgroundColor:['rgba(122,0,0,0.85)','rgba(201,168,76,0.85)','rgba(122,0,0,0.55)','rgba(201,168,76,0.55)','rgba(46,139,110,0.75)'],borderColor:'#fff',borderWidth:3,hoverOffset:6}]},options:{cutout:'58%',plugins:{legend:{display:true,position:'right',labels:{color:'#7a6a55',font:{size:10},padding:10,boxWidth:10,usePointStyle:true,pointStyle:'circle'}},tooltip:{callbacks:{label:function(c){return ' '+c.label+': '+c.parsed+' avg'}}}},animation:false}}";
    $chart_donut_url="https://quickchart.io/chart?c=".urlencode($donut_config)."&w=440&h=190&bkg=white&f=DM+Sans";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analytics | OLSHCO Faculty Evaluation</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="css/analytics_style.css">
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
    <a href="statistics.php" class="sb-btn"><i class="fas fa-chart-pie"></i><span>Statistics</span></a>
    <a href="analytics.php"  class="sb-btn sb-active"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
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
    <div style="margin-bottom:32px;">
      <div class="page-eyebrow"><i class="fas fa-chart-bar"></i> Data &amp; Insights</div>
      <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.5rem,2vw,2rem);font-weight:800;color:var(--maroon);line-height:1.15;margin-bottom:6px;">Analytics Overview</h1>
      <p style="font-size:0.84rem;color:var(--muted);line-height:1.7;max-width:520px;">Department performance, submission trends, and faculty rankings — all in one view.</p>
    </div>

    <div class="analytics-grid">

      <div class="chart-card">
        <div class="chart-head">
          <div class="chart-title"><i class="fas fa-chart-bar"></i> Ratings per Department</div>
          <span class="chart-badge">Avg / 5</span>
        </div>
        <div class="chart-body">
          <div class="chart-img-wrap">
            <img src="<?php echo $chart_bar_url; ?>" alt="Ratings per Department" loading="lazy">
          </div>
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-head">
          <div class="chart-title"><i class="fas fa-chart-line"></i> Monthly Submissions</div>
          <span class="chart-badge">Last 6 months</span>
        </div>
        <div class="chart-body">
          <div class="chart-img-wrap">
            <img src="<?php echo $chart_line_url; ?>" alt="Monthly Submissions" loading="lazy">
          </div>
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-head">
          <div class="chart-title"><i class="fas fa-trophy"></i> Top Rated Faculty</div>
          <span class="chart-badge">Min 1 eval</span>
        </div>
        <div class="chart-body">
          <?php if(!empty($top_teachers)): ?>
          <table class="top-table">
            <?php foreach($top_teachers as $i => $t):
              $rankClass = $i===0?'gold':($i===1?'silver':($i===2?'bronze':''));
              $avg_r = round((float)$t['avg_r'],1);
              $stars = (int)round($avg_r);
            ?>
            <tr>
              <td style="width:38px;"><span class="rank-badge <?php echo $rankClass; ?>"><?php echo $i+1; ?></span></td>
              <td>
                <div class="top-name"><?php echo htmlspecialchars($t['teacher_name']); ?></div>
                <div class="top-dept"><?php echo htmlspecialchars($t['department']); ?> &bull; <?php echo $t['evals']; ?> eval<?php echo $t['evals']>1?'s':''; ?></div>
              </td>
              <td style="text-align:right;">
                <div class="top-score-wrap">
                  <div class="top-stars"><?php for($s=1;$s<=5;$s++) echo '<i class="fas fa-star'.($s<=$stars?' empty':'').'"></i>'; ?></div>
                  <span class="top-score"><?php echo $avg_r; ?></span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </table>
          <?php else: ?>
          <div class="no-data-msg"><i class="fas fa-inbox"></i> No evaluation data yet</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-head">
          <div class="chart-title"><i class="fas fa-chart-pie"></i> Avg Rating Distribution</div>
          <span class="chart-badge">By department</span>
        </div>
        <div class="chart-body">
          <div class="chart-img-wrap">
            <?php if($chart_donut_url): ?>
            <img src="<?php echo $chart_donut_url; ?>" alt="Rating Distribution" loading="lazy">
            <?php else: ?>
            <div class="no-data-msg"><i class="fas fa-inbox"></i> No data yet</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

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
</script>
</body>
</html>