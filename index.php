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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | OLSHCO Faculty Evaluation</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="css/dashboard_style.css">
</head>
<body>

<!-- HEADER -->
<header class="site-header">
  <div class="header-brand">
    <button class="sidebar-toggle" id="sidebarToggleBtn" aria-label="Toggle sidebar">
      <i class="fas fa-bars"></i>
    </button>
    <img src="uploads/logo.png" alt="OLSHCO Logo">
    <div class="brand-text">
      <span class="brand-name">Our Lady of the Sacred Heart College</span>
      <span class="brand-sub">Faculty Evaluation System</span>
    </div>
  </div>
  <div class="header-actions">
    <div class="user-chip">
      <i class="fas fa-user-circle"></i>
      <?php echo htmlspecialchars($_SESSION['username']); ?>
    </div>
  </div>
</header>

<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sb-brand">
    </div>
    <div class="sb-section-label">Navigation</div>
    <a href="index.php" class="sb-btn sb-active"><i class="fas fa-house"></i><span>Dashboard</span></a>
    <a href="view.php"      class="sb-btn"><i class="fas fa-eye"></i><span>View Evaluations</span></a>
    <div class="sb-divider"></div>
    <div class="sb-section-label">Data</div>
    <a href="statistics.php" class="sb-btn"><i class="fas fa-chart-pie"></i><span>Statistics</span></a>
    <a href="analytics.php"  class="sb-btn"><i class="fas fa-chart-bar"></i><span>Analytics</span></a>
    <a href="identity.php"   class="sb-btn"><i class="fas fa-landmark"></i><span>Our Identity</span></a>
    <div class="sb-divider"></div>
    <a href="evaluate.php" class="sb-btn sb-gold"><i class="fas fa-plus"></i><span>New Evaluation</span></a>
    <div class="sb-footer">
      <a href="logout.php" class="sb-btn sb-logout"><i class="fas fa-sign-out-alt"></i><span>Login</span></a>
    </div>
  </aside>

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- PAGE -->
  <div class="page">

    <!-- ════ HERO: welcome + slider ════ -->
    <div class="hero-section reveal">

      <!-- Welcome text -->
      <div class="welcome-block">
        <div class="hero-eyebrow">Faculty Evaluation System</div>
        <h2 class="hero-title">
          Welcome back, <em><?php echo htmlspecialchars($_SESSION['username']); ?>.</em>
        </h2>
        <p class="hero-desc">
          Empowering OLSHCO to grow through honest, structured feedback.
          Every evaluation you submit helps shape a stronger faculty
          and a richer learning environment for every student.
        </p>
      </div>

      <!-- Slider -->
      <div class="slider-outer">

        <div class="slider-frame">
          <!-- Decorative corner accents -->
          <span class="corner tl"></span>
          <span class="corner tr"></span>
          <span class="corner bl"></span>
          <span class="corner br"></span>

          <div class="slider-viewport">
            <div class="slider-track" id="sliderTrack">
              <div class="slide"><img src="uploads/hero1.jpg" alt="IT Department"></div>
              <div class="slide"><img src="uploads/hero2.jpg" alt="Education Department"></div>
              <div class="slide"><img src="uploads/hero3.jpg" alt="Criminology"></div>
              <div class="slide"><img src="uploads/hero4.jpg" alt="Hospitality Management"></div>
              <div class="slide"><img src="uploads/hero5.jpg" alt="Office Administration"></div>
              <div class="slide"><img src="uploads/hero6.jpg" alt="IT Department"></div>
              <div class="slide"><img src="uploads/hero7.jpg" alt="Education Department"></div>
              <div class="slide"><img src="uploads/hero8.jpg" alt="Criminology"></div>
              <div class="slide"><img src="uploads/hero9.jpg" alt="Hospitality Management"></div>
              <div class="slide"><img src="uploads/hero10.jpg" alt="Office Administration"></div>
            </div>
          </div>

          <!-- Arrows -->
          <button class="sl-arrow sl-prev" id="sliderPrev" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button class="sl-arrow sl-next" id="sliderNext" aria-label="Next">
            <i class="fas fa-chevron-right"></i>
          </button>

          <!-- Progress bar -->
          <div class="slider-prog-wrap">
            <div class="slider-prog-fill" id="sliderProg"></div>
          </div>
        </div>

        <!-- Slider footer -->
        <div class="slider-footer">
          <div class="slider-dots">
            <button class="sdot active" data-idx="0" aria-label="Slide 1"></button>
            <button class="sdot" data-idx="1" aria-label="Slide 2"></button>
            <button class="sdot" data-idx="2" aria-label="Slide 3"></button>
            <button class="sdot" data-idx="3" aria-label="Slide 4"></button>
            <button class="sdot" data-idx="4" aria-label="Slide 5"></button>
          </div>
          <div class="slider-caption" id="sliderCaption">Information Technology</div>
        </div>

      </div><!-- .slider-outer -->
    </div><!-- .hero-section -->

    <!-- ════ WHO CAN EVALUATE ════ -->
    <div class="section-divider reveal"><span>Who Can Evaluate</span></div>

    <div class="who-grid">
      <div class="who-card reveal reveal-d1">
        <div class="who-icon"><i class="fas fa-user-graduate"></i></div>
        <h4>Students</h4>
        <p>Evaluate teachers of subjects you are currently enrolled in for the semester. Your voice directly shapes the quality of instruction you receive.</p>
      </div>
      <div class="who-card reveal reveal-d2">
        <div class="who-icon"><i class="fas fa-user-tie"></i></div>
        <h4>Supervisors</h4>
        <p>Assess the performance and professional conduct of your direct subordinates based on observable classroom behavior and departmental standards.</p>
      </div>
      <div class="who-card reveal reveal-d3">
        <div class="who-icon"><i class="fas fa-users"></i></div>
        <h4>Faculty</h4>
        <p>Complete a self-evaluation reflecting on your own teaching practice, and submit peer evaluations for colleagues within your department.</p>
      </div>
    </div>

    <!-- ════ ABOUT THE SYSTEM ════ -->
    <div class="section-divider reveal"><span>About the System</span></div>

    <div class="info-row">
      <div class="info-block reveal reveal-d1">
        <div class="ib-icon"><i class="fas fa-clipboard-list"></i></div>
        <div class="ib-body">
          <h4>What is Faculty Evaluation?</h4>
          <p>A structured process for collecting feedback on the quality of instruction and professional conduct of educators. At OLSHCO, the system is designed to be thorough, fair, and growth-oriented — empowering teachers to reflect, improve, and excel.</p>
        </div>
      </div>
      <div class="info-block reveal reveal-d2">
        <div class="ib-icon"><i class="fas fa-bullseye"></i></div>
        <div class="ib-body">
          <h4>Purpose &amp; Goals</h4>
          <p>Recognize exemplary teaching, identify areas for professional development, and ensure accountability across all departments. Results inform promotions, training programs, curriculum improvements, and accreditation reports.</p>
        </div>
      </div>
      <div class="info-block reveal reveal-d1">
        <div class="ib-icon"><i class="fas fa-shield-alt"></i></div>
        <div class="ib-body">
          <h4>Confidentiality &amp; Integrity</h4>
          <p>All responses are strictly anonymized before being reviewed by administrators. Individual feedback is never disclosed to the faculty member being evaluated, encouraging honest and meaningful responses.</p>
        </div>
      </div>
    </div>

    <!-- ════ CRITERIA ════ -->
    <div class="section-divider reveal"><span>Evaluation Criteria</span></div>

    <div class="criteria-grid">
      <div class="criteria-card reveal reveal-d1">
        <div class="cc-icon"><i class="fas fa-chalkboard-teacher"></i></div>
        <h5>Teaching Effectiveness</h5>
        <p>Clarity of instruction, use of varied teaching strategies, and ability to engage students in meaningful active learning.</p>
      </div>
      <div class="criteria-card reveal reveal-d2">
        <div class="cc-icon"><i class="fas fa-clock"></i></div>
        <h5>Punctuality &amp; Attendance</h5>
        <p>Consistent presence in class, timeliness in returning graded work, and reliable availability during consultation hours.</p>
      </div>
      <div class="criteria-card reveal reveal-d3">
        <div class="cc-icon"><i class="fas fa-comments"></i></div>
        <h5>Communication Skills</h5>
        <p>Clear articulation of lessons, active listening, and the delivery of constructive, encouraging feedback to students.</p>
      </div>
      <div class="criteria-card reveal reveal-d1">
        <div class="cc-icon"><i class="fas fa-book-open"></i></div>
        <h5>Mastery of Subject</h5>
        <p>Demonstrated depth of knowledge in the discipline and ability to connect academic theory to real-world professional practice.</p>
      </div>
      <div class="criteria-card reveal reveal-d2">
        <div class="cc-icon"><i class="fas fa-users"></i></div>
        <h5>Classroom Management</h5>
        <p>Creating a structured, respectful, and inclusive learning environment where every student feels safe and valued.</p>
      </div>
      <div class="criteria-card reveal reveal-d3">
        <div class="cc-icon"><i class="fas fa-award"></i></div>
        <h5>Professional Conduct</h5>
        <p>Adherence to institutional policies, ethical standards, and exemplary professional behavior inside and beyond the classroom.</p>
      </div>
    </div>

    <!-- ════ HOW IT WORKS ════ -->
    <div class="section-divider reveal"><span>How It Works</span></div>

    <div class="steps-grid">
      <div class="step-card reveal reveal-d1">
        <div class="step-num">01</div>
        <h5>Sign In</h5>
        <p>Access using your institutional credentials. Your role determines which evaluations you may complete this semester.</p>
      </div>
      <div class="step-card reveal reveal-d2">
        <div class="step-num">02</div>
        <h5>Select Faculty</h5>
        <p>Choose from teachers assigned to your course or department. The system filters only eligible evaluations for you.</p>
      </div>
      <div class="step-card reveal reveal-d3">
        <div class="step-num">03</div>
        <h5>Fill the Form</h5>
        <p>Rate each criterion honestly using the 1–5 scale. Add optional comments to support your quantitative ratings.</p>
      </div>
      <div class="step-card reveal reveal-d1">
        <div class="step-num">04</div>
        <h5>Submit &amp; Confirm</h5>
        <p>Review carefully before confirming. Once submitted, responses are locked, anonymized, and forwarded securely.</p>
      </div>
    </div>

    <!-- ════ RATING SCALE ════ -->
    <div class="section-divider reveal"><span>Rating Scale Reference</span></div>

    <div class="rating-scale reveal">
      <div class="rs-row rs-header">
        <span>Score</span><span>Label</span><span>What It Means</span>
      </div>
      <div class="rs-row">
        <span class="rs-score rs-5">5</span>
        <span class="rs-label">Outstanding</span>
        <span class="rs-desc">Consistently exceeds all expectations across every dimension.</span>
      </div>
      <div class="rs-row">
        <span class="rs-score rs-4">4</span>
        <span class="rs-label">Very Satisfactory</span>
        <span class="rs-desc">Frequently surpasses expectations with only minor gaps.</span>
      </div>
      <div class="rs-row">
        <span class="rs-score rs-3">3</span>
        <span class="rs-label">Satisfactory</span>
        <span class="rs-desc">Reliably meets expectations in most observable areas.</span>
      </div>
      <div class="rs-row">
        <span class="rs-score rs-2">2</span>
        <span class="rs-label">Fair</span>
        <span class="rs-desc">Partially meets expectations; notable areas for improvement.</span>
      </div>
      <div class="rs-row">
        <span class="rs-score rs-1">1</span>
        <span class="rs-label">Poor</span>
        <span class="rs-desc">Does not meet expectations; significant concerns observed.</span>
      </div>
    </div>

    <!-- ════ REMINDERS ════ -->
    <div class="section-divider reveal"><span>Important Reminders</span></div>

    <div class="reminder-grid">
      <div class="reminder-card reveal reveal-d1">
        <i class="fas fa-redo-alt"></i>
        <h6>One submission only</h6>
        <p>Evaluations are locked once submitted. Review all responses carefully before confirming your answers.</p>
      </div>
      <div class="reminder-card reveal reveal-d2">
        <i class="fas fa-calendar-alt"></i>
        <h6>Evaluation window</h6>
        <p>The system is only active during the designated period set by the registrar's office each semester.</p>
      </div>
      <div class="reminder-card reveal reveal-d3">
        <i class="fas fa-user-secret"></i>
        <h6>Your identity is protected</h6>
        <p>All responses are fully anonymized before they reach any administrator or faculty member.</p>
      </div>
      <div class="reminder-card reveal reveal-d1">
        <i class="fas fa-ban"></i>
        <h6>Be honest &amp; fair</h6>
        <p>Malicious or false submissions may result in disciplinary action per institutional policy.</p>
      </div>
    </div>

  </div><!-- .page -->
</div><!-- .layout -->

<script>
/* ── Sidebar ── */
(function(){
  var btn=document.getElementById('sidebarToggleBtn'),
      sb =document.getElementById('sidebar'),
      ov =document.getElementById('sidebarOverlay');
  function t(){ sb.classList.toggle('open'); ov.classList.toggle('show'); }
  btn.addEventListener('click',t);
  ov.addEventListener('click',t);
})();

/* ── Slider ── */
(function(){
  var track   = document.getElementById('sliderTrack'),
      fill    = document.getElementById('sliderProg'),
      caption = document.getElementById('sliderCaption'),
      dots    = document.querySelectorAll('.sdot'),
      caps    = ['Information Technology','Education','Criminology','Hospitality Management','Office Administration'],
      total   = caps.length,
      cur     = 0,
      dur     = 4000,
      timer   = null;

  function goTo(idx){
    cur = (idx + total) % total;
    track.style.transform = 'translateX(-'+(cur*100)+'%)';
    dots.forEach(function(d,i){ d.classList.toggle('active', i===cur); });
    caption.style.opacity='0';
    caption.style.transform='translateY(5px)';
    setTimeout(function(){
      caption.textContent = caps[cur];
      caption.style.opacity='1';
      caption.style.transform='translateY(0)';
    },180);
    fill.style.transition='none';
    fill.style.width='0%';
    setTimeout(function(){
      fill.style.transition='width '+dur+'ms linear';
      fill.style.width='100%';
    },40);
  }

  function start(){ timer=setInterval(function(){ goTo(cur+1); },dur); }
  function reset(){ clearInterval(timer); start(); }

  document.getElementById('sliderPrev').addEventListener('click',function(){ goTo(cur-1); reset(); });
  document.getElementById('sliderNext').addEventListener('click',function(){ goTo(cur+1); reset(); });
  dots.forEach(function(d){
    d.addEventListener('click',function(){ goTo(parseInt(d.dataset.idx,10)); reset(); });
  });

  goTo(0); start();
})();

/* ── Scroll reveal ── */
(function(){
  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if(e.isIntersecting){ e.target.classList.add('revealed'); obs.unobserve(e.target); }
    });
  },{ threshold:0.1 });
  document.querySelectorAll('.reveal').forEach(function(el){ obs.observe(el); });
})();

/* ── Counter ── */
(function(){
  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if(!e.isIntersecting) return;
      var el=e.target, target=parseFloat(el.dataset.target)||0, dec=parseInt(el.dataset.decimal)||0, s=null;
      (function step(ts){
        if(!s) s=ts;
        var p=Math.min((ts-s)/1600,1);
        el.textContent = dec ? (p*target).toFixed(dec) : Math.floor(p*target);
        if(p<1) requestAnimationFrame(step);
        else el.textContent = dec ? target.toFixed(dec) : target;
      })(performance.now());
      obs.unobserve(el);
    });
  },{ threshold:0.5 });
  document.querySelectorAll('.counter').forEach(function(c){ obs.observe(c); });
})();
</script>
</body>
</html>