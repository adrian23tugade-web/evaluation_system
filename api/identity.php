<?php
include "db.php";
if(!isset($_SESSION['username'])){ header("Location: login.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Our Identity | OLSHCO Faculty Evaluation</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="css/identity_style.css">
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
    <a href="analytics.php"  class="sb-btn"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
    <a href="identity.php"   class="sb-btn sb-active"><i class="fas fa-landmark"></i><span>Our Identity</span></a>
    <div class="sb-divider"></div>
    <a href="evaluate.php" class="sb-btn sb-gold"><i class="fas fa-plus"></i><span>New Evaluation</span></a>
    <div class="sb-footer">
      <a href="logout.php" class="sb-btn sb-logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>
  </aside>

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="page">

    <!-- Page header -->
    <div class="page-eyebrow"><i class="fas fa-landmark"></i> Institutional Identity</div>
    <h1 class="page-title">Our Identity</h1>
    <p class="page-sub">Rooted in faith and guided by the Oneness of Heart of Jesus and Mary, OLSHCO stands as a community committed to integral human development and transformative education.</p>

    <!-- VMC Cards -->
    <div class="vmc-grid">

      <div class="vmc-card">
        <span class="card-eyebrow"><i class="fas fa-eye"></i> Vision</span>
        <h4>Who We Aspire to Be</h4>
        <p>A diocesan Catholic school community founded on the Oneness of Heart of Jesus and Mary, dedicated to integral human development for a sustainable future.</p>
      </div>

      <div class="vmc-card">
        <span class="card-eyebrow"><i class="fas fa-hand-holding-heart"></i> Mission</span>
        <h4>How We Serve</h4>
        <p>Inspired by and devoted to the Oneness of Heart of Jesus and Mary, OLSHCO is in mission to:</p>
        <ul class="mission-list">
          <li><span class="mission-num">1</span> Nurture a strong program on Christian formation</li>
          <li><span class="mission-num">2</span> Foster a 21st-century learning environment</li>
          <li><span class="mission-num">3</span> Support programs and initiatives for social transformation</li>
        </ul>
      </div>

      <div class="vmc-card flame-card">
        <span class="card-eyebrow"><i class="fas fa-fire-alt"></i> Core Values</span>
        <h4>The FLAME</h4>
        <p>An OLSHCOnian heart is formed by the Oneness of Heart of Jesus and Mary, living out:</p>
        <div class="flame-values">
          <div class="flame-value">
            <span class="fv-letter">F</span>
            <div class="fv-divider"></div>
            <span class="fv-word">Filial Compassion</span>
          </div>
          <div class="flame-value">
            <span class="fv-letter">L</span>
            <div class="fv-divider"></div>
            <span class="fv-word">Leadership</span>
          </div>
          <div class="flame-value">
            <span class="fv-letter">A</span>
            <div class="fv-divider"></div>
            <span class="fv-word">Accountability</span>
          </div>
          <div class="flame-value">
            <span class="fv-letter">M</span>
            <div class="fv-divider"></div>
            <span class="fv-word">Mary-Inspired Obedience</span>
          </div>
          <div class="flame-value">
            <span class="fv-letter">E</span>
            <div class="fv-divider"></div>
            <span class="fv-word">Enduring Discipleship</span>
          </div>
        </div>
      </div>

    </div>

    <!-- Banner quote -->
    <div class="identity-banner">
      <div class="banner-content">
        <img src="uploads/logo.png" alt="OLSHCO" class="banner-logo">
        <div class="banner-text">
          <div class="banner-label">Our Lady of the Sacred Heart College of Olongapo</div>
          <p class="banner-quote">"Educating the mind, forming the heart, and serving the community — in the spirit of the Sacred Heart."</p>
          <span class="banner-attr">Founded on faith. Driven by purpose. Rooted in service.</span>
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