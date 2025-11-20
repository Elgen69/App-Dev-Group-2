<?php
// Start a new session or resume the existing session
session_start();

// Check if the user is already logged in by verifying if 'user_id' is set in the session and greater than 0
if(isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0){
    // If the user is logged in, redirect them to the home page
    header("Location:./");
    // Stop further script execution
    exit;
}

// Include the database connection script
require_once('DBConnection.php');

// Determine the current page to load, default to 'home' if not specified in the URL
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>LOGIN | Julie's POS and Management System</title>

<!-- local CSS/JS you already use (keep these if present in your project) -->
<link rel="stylesheet" href="./css/bootstrap.min.css">
<script src="./js/jquery-3.6.0.min.js"></script>
<script src="./js/popper.min.js"></script>
<script src="./js/bootstrap.min.js"></script>

<style>
/* PAGE RESET */
html,body { height:100%; margin:0; font-family: "Helvetica Neue", Arial, sans-serif; -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale; }

/* Full-screen loading overlay */
#loading-screen{
  position: fixed;
  inset: 0;               /* top:0; right:0; bottom:0; left:0; */
  width: 100vw;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #000;       /* fallback */
  background: rgba(0,0,0,0.9); /* semi-opaque black background */
  z-index: 99999;         /* very high to sit above everything */
  overflow: hidden;
}

/* container around the image to control sizing (keeps gif centered + scaled) */
#loading-screen .loader-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  width: min(90vw, 900px);   /* responsive max width */
  height: min(70vh, 560px);  /* responsive max height */
  padding: 8px;
  box-sizing: border-box;
  border-radius: 8px;
  /* optional glow */
  box-shadow: 0 10px 40px rgba(0,255,90,0.12), 0 2px 6px rgba(0,0,0,0.6);
}

#loading-gif {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
  pointer-events: none;
}

/* full-bleed blurred background image */
.bg-photo {
  position:fixed;
  inset:0;
  background-image: url('./images/Login_blur.jpg'); /* <-- change path if needed (e.g. /mnt/data/your.jpg) */
  background-size:cover;
  background-position:center;
  filter: blur(6px) saturate(1.05);
  transform: scale(1.02);
  z-index:0;
}

/* slight dark overlay to improve contrast with matrix letters */
.bg-overlay {
  position:fixed; inset:0; background: rgba(0,0,0,0.18); z-index:1;
}

/* matrix canvas sits above the image but behind content */
#matrix-canvas {
  position:fixed; inset:0; z-index:2; pointer-events:none; mix-blend-mode:screen;
  opacity:0.95;
}

/* title across top (large, white, soft drop shadow) */
.header-title {
  position:fixed; left:0; right:0; top:10px; z-index:3;
  text-align:center; font-family:'Pacifico', cursive; color:#e6ffea;
  font-size:6vw; line-height:1; text-shadow: 0 4px 18px rgba(0,0,0,0.45), 0 0 40px rgba(16,255,80,0.06);
  pointer-events:none;
}

/* center area for card */
.center-wrap {
  position:relative; z-index:4; min-height:100vh;
  display:flex; align-items:center; justify-content:center; padding:3rem;
}

/* ===========================================
   MORPHING RINGS — Glowing, animated, soft
   =========================================== */

:root{
  --ring-clr: #1bff6a;   /* neon green — change if you want different color */
  --ring-alpha: 0.85;
  --rings-z: 3;
  --card-z: 4;          /* ensure login card has z-index higher than rings */
}

/* wrapper positions rings relative to the page center / card */
.rings-wrap{
  position: absolute;
  left: 50%;
  top: 44%;
  transform: translate(-50%,-50%);
  width: 760px;         /* overall area for rings */
  height: 760px;
  pointer-events: none; /* don't block clicks */
  z-index: var(--rings-z);
  display: block;
}

/* base ring container */
.ring{
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%,-50%);
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  mix-blend-mode: screen; /* nicer glow over background */
}

/* each ring contains three <i> layers that morph and rotate */
.ring i{
  position: absolute;
  inset: 0;
  border: 2px solid rgba(27,255,106,0.28);
  border-radius: 50%;
  transition: border .4s ease, filter .4s ease;
  box-shadow:
    0 0 18px rgba(27,255,106,0.12),
    0 0 45px rgba(27,255,106,0.06);
  filter: blur(.6px);
  background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0));
  pointer-events: none;
}

/* ring1 — largest */
.ring1{ width:760px; height:760px; }
.ring1 i:nth-child(1){
  border-radius: 38% 62% 63% 37% / 41% 44% 56% 59%;
  animation: r1-rot 18s linear infinite, r1-morph 7s ease-in-out infinite alternate;
  border-color: rgba(27,255,106,0.65);
  box-shadow: 0 0 26px rgba(27,255,106,0.18), 0 0 80px rgba(27,255,106,0.06);
}
.ring1 i:nth-child(2){
  border-radius: 41% 44% 56% 59% / 38% 62% 63% 37%;
  animation: r1-rot-rev 26s linear infinite, r1-morph-2 9s ease-in-out infinite alternate;
  border-color: rgba(27,255,106,0.46);
  transform-origin: 50% 50%;
  opacity: .85;
}
.ring1 i:nth-child(3){
  border-radius: 37% 63% 39% 61% / 58% 44% 56% 42%;
  animation: r1-rot 22s linear infinite, r1-morph-3 11s ease-in-out infinite alternate;
  border-color: rgba(27,255,106,0.28);
  filter: blur(1.6px);
}

/* ring2 — medium */
.ring2{ width:560px; height:560px; }
.ring2 i:nth-child(1){
  border-radius: 36% 64% 60% 40% / 46% 40% 60% 54%;
  animation: r2-rot 14s linear infinite, r2-morph 6s ease-in-out infinite alternate;
  border-color: rgba(27,255,106,0.6);
  box-shadow: 0 0 22px rgba(27,255,106,0.14), 0 0 60px rgba(27,255,106,0.05);
}
.ring2 i:nth-child(2){
  border-radius: 42% 58% 54% 46% / 38% 62% 63% 37%;
  animation: r2-rot-rev 20s linear infinite, r2-morph-2 8s ease-in-out infinite alternate;
  border-color: rgba(27,255,106,0.45);
}
.ring2 i:nth-child(3){
  border-radius: 50% 50% 40% 60% / 50% 40% 60% 50%;
  animation: r2-rot 16s linear infinite, r2-morph-3 10s ease-in-out infinite alternate;
  border-color: rgba(27,255,106,0.25);
}

/* ring3 — smallest */
.ring3{ width:360px; height:360px; }
.ring3 i:nth-child(1){
  border-radius: 50% 45% 55% 50% / 50% 50% 40% 60%;
  animation: r3-rot 10s linear infinite, r3-morph 5s ease-in-out infinite alternate;
  border-color: rgba(27,255,106,0.75);
  box-shadow: 0 0 30px rgba(27,255,106,0.2);
}
.ring3 i:nth-child(2){
  border-radius: 45% 55% 60% 40% / 60% 40% 50% 50%;
  animation: r3-rot-rev 12s linear infinite, r3-morph-2 7s ease-in-out infinite alternate;
  border-color: rgba(27,255,106,0.55);
}
.ring3 i:nth-child(3){
  border-radius: 56% 44% 46% 54% / 41% 59% 61% 39%;
  animation: r3-rot 8s linear infinite, r3-morph-3 9s ease-in-out infinite alternate;
  border-color: rgba(27,255,106,0.32);
  filter: blur(1px);
}

/* hover: intensify border (if you ever want hover effect; pointer-events:none prevents hover by default) */
.rings-wrap:hover .ring i { 
  border-width: 3px;
  filter: drop-shadow(0 0 28px rgba(27,255,106,0.85));
}

/* ROTATION keyframes (some clockwise, some reversed) */
@keyframes r1-rot { to { transform: translate(-50%,-50%) rotate(360deg); } }
@keyframes r1-rot-rev { to { transform: translate(-50%,-50%) rotate(-360deg); } }
@keyframes r2-rot { to { transform: translate(-50%,-50%) rotate(360deg); } }
@keyframes r2-rot-rev { to { transform: translate(-50%,-50%) rotate(-360deg); } }
@keyframes r3-rot { to { transform: translate(-50%,-50%) rotate(360deg); } }
@keyframes r3-rot-rev { to { transform: translate(-50%,-50%) rotate(-360deg); } }

/* MORPHING keyframes (vary border-radius to produce organic morphing) */
@keyframes r1-morph {
  0%   { border-radius: 38% 62% 63% 37% / 41% 44% 56% 59%; transform: translate(-50%,-50%) scale(1); opacity: .94; }
  50%  { border-radius: 55% 45% 48% 52% / 43% 57% 47% 53%; transform: translate(-50%,-50%) scale(1.02); opacity: .85; }
  100% { border-radius: 40% 60% 60% 40% / 50% 40% 60% 50%; transform: translate(-50%,-50%) scale(0.99); opacity: .9; }
}
@keyframes r1-morph-2 {
  0%   { border-radius: 41% 44% 56% 59%/38% 62% 63% 37%; transform: translate(-50%,-50%) rotate(0); }
  50%  { border-radius: 35% 65% 45% 55% / 60% 30% 60% 50%; transform: translate(-50%,-50%) rotate(10deg); }
  100% { border-radius: 45% 55% 52% 48% / 40% 60% 40% 60%; transform: translate(-50%,-50%) rotate(-10deg); }
}
@keyframes r1-morph-3 {
  0% { border-radius: 37% 63% 39% 61% / 58% 44% 56% 42%; }
  50%{ border-radius: 48% 52% 49% 51% / 50% 50% 50% 50%; }
  100%{ border-radius: 35% 65% 60% 40% / 45% 55% 45% 55%; }
}

/* r2 morphs */
@keyframes r2-morph { 0%{border-radius:36% 64% 60% 40% / 46% 40% 60% 54%} 50%{border-radius:52% 48% 44% 56% / 56% 44% 46% 54%} 100%{border-radius:38% 62% 58% 42% / 50% 50% 40% 60%} }
@keyframes r2-morph-2 { 0%{border-radius:42% 58% 54% 46% / 38% 62% 63% 37%} 50%{border-radius:50% 50% 60% 40% / 45% 55% 35% 65%} 100%{border-radius:42% 58% 49% 51% / 40% 60% 50% 50%} }
@keyframes r2-morph-3 { 0%{border-radius:50% 50% 40% 60% / 50% 40% 60% 50%} 50%{border-radius:60% 40% 50% 50% / 45% 55% 55% 45%} 100%{border-radius:48% 52% 45% 55% / 50% 50% 40% 60%} }

/* r3 morphs */
@keyframes r3-morph { 0%{border-radius:50% 45% 55% 50% / 50% 50% 40% 60%} 50%{border-radius:40% 60% 70% 30% / 60% 40% 40% 60%} 100%{border-radius:55% 45% 45% 55% / 40% 60% 60% 40%} }
@keyframes r3-morph-2 { 0%{border-radius:45% 55% 60% 40% / 60% 40% 50% 50%} 50%{border-radius:50% 50% 40% 60% / 50% 40% 60% 50%} 100%{border-radius:42% 58% 54% 46% / 60% 40% 50% 50%} }
@keyframes r3-morph-3 { 0%{border-radius:56% 44% 46% 54% / 41% 59% 61% 39%} 50%{border-radius:48% 52% 52% 48% / 50% 50% 45% 55%} 100%{border-radius:58% 42% 44% 56% / 40% 60% 60% 40%} }

/* make sure login card is above rings */
.login-card, .card, .card-body, .card .card-body, #login-panel {
  position: relative;
  z-index: var(--card-z) !important;
}


/* login card */
.login-card {
  width:420px; max-width:94vw;
  background: linear-gradient(135deg, rgba(212,111,201,0.95), rgba(116,185,252,0.95), rgba(85,239,215,0.95));
  border-radius:12px; padding:1.2rem 1.3rem; box-shadow: 0 10px 30px rgba(0,0,0,0.38);
  border: 1px solid rgba(255,255,255,0.25);
    position: relative;
    z-index: 4;   /* ABOVE the rings */
}

/* subtle glass inner */
.login-card .card-inner {
  background: rgba(255,255,255,0.06); border-radius:8px; padding:10px;
}

/* small instructions */
.login-card small { display:block; text-align:center; color:rgba(255,255,255,0.92); margin-bottom:.6rem; }

/* inputs */
.form-control {
  border-radius:6px;
  border: none;
  padding:10px 12px;
  font-size:14px;
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
}
label { color: rgba(255,255,255,0.9); font-weight:600; font-size:.85rem; }

/* fancy buttons */
.btn-primary {
  background:#1b6b1b; border:none; color:white; font-weight:600;
  border-radius:8px; padding:8px 14px;
}
.btn-secondary {
  background: rgba(30,30,30,0.6); color:#fff; border:none; border-radius:8px;
}

/* Forgot / login row */
.actions-row { display:flex; gap:.5rem; justify-content:flex-end; margin-top:.6rem; }

/* smaller faded footer note */
.footer-note { font-size:12px; color:rgba(255,255,255,0.75); text-align:center; margin-top:10px; }

/* forgot password card (hidden by default) - share same style */
.forgot-wrap { display:none; width:420px; max-width:94vw; }

/* RESPONSIVE */
@media (max-width:540px){
  .header-title { font-size:10vw; top:6px; }
  .login-card { padding:.9rem; }
}
</style>
</head>

<div id="loading-screen">
    <img id="loading-gif" src="./images/Julies Loading.gif" alt="Loading...">
</div>

<body>

<!-- blurred bakery photo behind everything -->
<div class="bg-photo" aria-hidden="true"></div>
<div class="bg-overlay" aria-hidden="true"></div>

<!-- matrix canvas (green rain) -->
<canvas id="matrix-canvas"></canvas>

<!-- big title -->
<div class="header-title">Julie's POS and Management System</div>

<!-- main center -->
<div class="center-wrap">
     <!-- RINGS -->
<div class="rings-wrap" aria-hidden="true">
  <div class="ring ring1"><i></i><i></i><i></i></div>
  <div class="ring ring2"><i></i><i></i><i></i></div>
  <div class="ring ring3"><i></i><i></i><i></i></div>
</div>

  <!-- login panel -->
  <div class="login-card" id="login-panel">
    <div class="card-inner">
      <form id="login-form" autocomplete="off">
        <small>Please enter your credentials.</small>

        <div class="mb-2">
          <label for="username">Username</label>
          <input id="username" name="username" autofocus class="form-control" required />
        </div>

        <div class="mb-2">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" class="form-control" required />
        </div>

        <div class="actions-row">
          <button type="button" id="forgot-btn" class="btn btn-secondary">Forgot Password?</button>
          <button type="submit" class="btn btn-primary">Login</button>
        </div>

        <div class="footer-note">Employees only — contact admin for access</div>
      </form>
    </div>
  </div>

  <!-- forgot -->
  <div class="login-card forgot-wrap" id="forgot-panel">
    <div class="card-inner">
      <form id="forgot-form">
        <small>Reset password — enter email + new password</small>

        <div class="mb-2">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" class="form-control" required />
        </div>

        <div class="mb-2">
          <label for="new_password">New Password</label>
          <input id="new_password" name="new_password" type="password" class="form-control" required />
        </div>

        <div class="mb-2">
          <label for="confirm_password">Confirm Password</label>
          <input id="confirm_password" name="confirm_password" type="password" class="form-control" required />
        </div>

        <div class="actions-row">
          <button type="button" id="back-btn" class="btn btn-secondary">Back to Login</button>
          <button type="submit" class="btn btn-primary">Reset Password</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
/* Matrix effect on canvas — green falling characters */
(function(){
  const canvas = document.getElementById('matrix-canvas');
  const ctx = canvas.getContext('2d');
  let width, height, columns, size = 16;
  const letters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz@#$%^&*()*&^%";
  let drops = [];

  function resize(){
    width = canvas.width = window.innerWidth;
    height = canvas.height = window.innerHeight;
    columns = Math.floor(width / size) + 1;
    drops = Array(columns).fill(1).map(()=> Math.floor(Math.random() * height / size));
  }
  window.addEventListener('resize', resize);
  resize();

  function draw(){
    // translucent black to give trailing effect
    ctx.fillStyle = 'rgba(0, 0, 0, 0.06)';
    ctx.fillRect(0,0,width,height);

    ctx.font = size + 'px monospace';
    for (let i=0;i<columns;i++){
      const text = letters.charAt(Math.floor(Math.random() * letters.length));
      const x = i * size;
      const y = drops[i] * size;
      // gradient green glow per char
      ctx.fillStyle = 'rgba(140,255,120,0.95)';
      ctx.fillText(text, x, y);

      // lighten head
      ctx.fillStyle = 'rgba(200,255,200,0.95)';
      ctx.fillText(text, x, y - size/2);

      if (y > height && Math.random() > 0.975) drops[i] = 0;
      drops[i]++;
    }
    requestAnimationFrame(draw);
  }
  requestAnimationFrame(draw);
})();

/* simple UI behaviors (no external dependency required beyond jQuery already loaded) */
$(function(){
  // show forgot / back
  $('#forgot-btn').click(function(){
    $('#login-panel').hide();
    $('#forgot-panel').show();
  });
  $('#back-btn').click(function(){
    $('#forgot-panel').hide();
    $('#login-panel').show();
  });

  // login ajax
  $('#login-form').on('submit', function(e){
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]').prop('disabled', true).text('Logging...');
    $.ajax({
      url: './Actions.php?a=login',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(resp){
        if (resp && resp.status == 'success') {
          location.replace('./');
        } else {
          alert(resp && resp.msg ? resp.msg : 'Login failed');
        }
      },
      error: function(){ alert('Network error'); },
      complete: function(){ $btn.prop('disabled', false).text('Login'); }
    });
  });

  // forgot form submit
  $('#forgot-form').on('submit', function(e){
    e.preventDefault();
    var $btn = $(this).find('button[type="submit"]').prop('disabled', true).text('Saving...');
    $.ajax({
      url: './Actions.php?a=reset_password',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(resp){
        if (resp && resp.status == 'success') {
          alert('Password reset. Please login.');
          $('#back-btn').click();
        } else {
          alert(resp && resp.msg ? resp.msg : 'Reset failed');
        }
      },
      error: function(){ alert('Network error'); },
      complete: function(){ $btn.prop('disabled', false).text('Reset Password'); }
    });
  });
});

$(document).ready(function() {
    setTimeout(() => {
        $('#loading-screen').fadeOut('slow');
    }, 1500);
});


</script>
</body>
</html>
