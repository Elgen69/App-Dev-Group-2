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

<!-- keep your existing assets -->
<link rel="stylesheet" href="./css/bootstrap.min.css">
<script src="./js/jquery-3.6.0.min.js"></script>
<script src="./js/popper.min.js"></script>
<script src="./js/bootstrap.min.js"></script>

<!-- Enhanced red-glass UI + animated shards -->
<style>
:root{
  --bg-top:#3a0707;
  --bg-bottom:#510909;
  --card-width:640px;
  --logo-size:220px; /* bigger logo as requested */
  --accent-1:#ff3b30;
  --accent-2:#ff6f63;
  --card-glass: rgba(255,245,245,0.86);
  --card-border: rgba(255,255,255,0.6);
  --text-contrast:#3b0b0b;
}

/* page base */
html,body{
  height:100%;
  margin:0;
  font-family:Inter, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
  background:
    radial-gradient(700px 340px at 6% 12%, rgba(255,60,60,0.12), transparent 12%),
    radial-gradient(520px 300px at 92% 86%, rgba(255,120,120,0.06), transparent 14%),
    linear-gradient(180deg, var(--bg-top) 0%, #2a0303 45%, #1f0202 100%);
  color: #fff;
  overflow-x:hidden;
}

/* keep loading-screen element exactly as-is visually above everything */
#loading-screen{ position: fixed; inset: 0; width:100vw; height:100vh; display:flex; justify-content:center; align-items:center; background: rgba(0,0,0,0.9); z-index:99999; overflow:hidden; }
#loading-screen .loader-wrap{ display:flex; align-items:center; justify-content:center; width:min(90vw,900px); height:min(70vh,560px); padding:8px; box-sizing:border-box; border-radius:8px; box-shadow:0 10px 40px rgba(255,80,80,0.06); }
#loading-gif{ width:100%; height:100%; object-fit:contain; display:block; pointer-events:none; }

/* decorative layers */
.bg-hero {
  position:fixed; inset:0; z-index:0; pointer-events:none;
  background:
    linear-gradient(180deg, rgba(255,15,15,0.06), rgba(0,0,0,0.18));
  filter: blur(6px) saturate(1.1);
}

/* soft grid overlay */
.grid {
  position:fixed; inset:0; z-index:1; pointer-events:none;
  background-image:
    linear-gradient(rgba(255,255,255,0.01) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.01) 1px, transparent 1px);
  background-size: 140px 140px, 140px 140px;
  opacity:0.06;
  mix-blend-mode: overlay;
}

/* bokeh blobs */
.bokeh {
  position:fixed; inset:0; z-index:1; pointer-events:none;
}
.bokeh .blob {
  position:absolute; border-radius:50%;
  filter: blur(36px); opacity:0.12; transform: translate3d(0,0,0);
}

/* diagonal ribbon sheen */
.gloss {
  position:fixed; left:-30%; top:-40%; width:160%; height:180%; z-index:2;
  background: linear-gradient(110deg, rgba(255,255,255,0.02) 0%, rgba(255,255,255,0.08) 48%, rgba(255,255,255,0.02) 100%);
  transform: rotate(-18deg) translateX(-20%); filter: blur(36px) saturate(1.2); opacity:0.9; animation: gloss-move 10s linear infinite; pointer-events:none;
}
@keyframes gloss-move { 0%{ transform: rotate(-18deg) translateX(-24%);} 50%{ transform: rotate(-18deg) translateX(22%);} 100%{ transform: rotate(-18deg) translateX(-24%);} }

/* particle dust */
.particles { position:fixed; inset:0; z-index:2; pointer-events:none; overflow:hidden;}
.particles .dot { position:absolute; width:4px; height:4px; border-radius:50%; background:rgba(255,240,240,0.85); opacity:0.06; }

/* shards container (glass cards) */
.shards { position:fixed; inset:0; z-index:3; pointer-events:none; overflow:hidden; }

/* shard base */
.shard {
  position:absolute; will-change: transform, opacity; border-radius:6px;
  background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
  border: 1px solid rgba(255,255,255,0.08);
  box-shadow: 0 8px 20px rgba(0,0,0,0.45), 0 2px 8px rgba(255,40,40,0.06);
  backdrop-filter: blur(5px) saturate(1.05);
  transform-origin:center; opacity:0.9;
  transition: transform .6s ease;
}
.shard.large { border-radius:10px; box-shadow: 0 18px 48px rgba(0,0,0,0.55); }

/* drift animation */
@keyframes shard-drift {
  0% { transform: translateY(0) rotate(0deg) scale(1); opacity:0; }
  10% { opacity:0.9; }
  50% { transform: translateY(-16vh) rotate(30deg) scale(1.02); opacity:0.8; }
  100% { transform: translateY(-34vh) rotate(65deg) scale(0.98); opacity:0; }
}

/* center layout */
.center-wrap { position:relative; z-index:6; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:3rem; }

/* card stage and big logo */
.card-stage { position:relative; width:100%; max-width:var(--card-width); display:flex; align-items:center; justify-content:center; flex-direction:column; gap:1rem; z-index:7; }

/* oversized circular logo that overlaps the card */
.hero-logo {
  width: var(--logo-size); height: var(--logo-size); border-radius:999px;
  background: radial-gradient(circle at 30% 20%, #fff 0%, #fff8f8 18%, rgba(255,255,255,0.92) 40%), linear-gradient(180deg, rgba(255,255,255,0.95), rgba(255,250,250,0.9));
  display:flex; align-items:center; justify-content:center; margin-top:-calc(var(--logo-size)/2);
  box-shadow: 0 26px 80px rgba(0,0,0,0.6), 0 8px 28px rgba(255,60,60,0.08);
  border: 10px solid rgba(255,255,255,0.78); z-index:9;
}
.hero-logo img{ width:86%; height:86%; object-fit:contain; display:block; border-radius:999px; }

/* glassy login card with stronger contrast */
.login-card {
  width:100%;
  border-radius:22px;
  padding:2rem 2rem 1.6rem 2rem;
  background: linear-gradient(180deg, rgba(255,245,245,0.96), rgba(255,240,240,0.9));
  border: 1px solid var(--card-border);
  box-shadow: 0 40px 100px rgba(10,4,4,0.6), inset 0 1px 0 rgba(255,255,255,0.5);
  backdrop-filter: blur(12px) saturate(1.15);
  -webkit-backdrop-filter: blur(12px) saturate(1.15);
  position:relative; z-index:8; color:var(--text-contrast);
}

/* subtle red inner glow at bottom */
.login-card::before {
  content:"";
  position:absolute; left:18px; right:18px; bottom:10px; height:60px;
  background: radial-gradient(60% 60% at 50% 60%, rgba(255,90,90,0.06), transparent 50%);
  border-radius:12px; pointer-events:none;
}

/* title */
.brand-title { font-size:22px; font-weight:800; color:var(--text-contrast); text-align:center; margin-top: calc(var(--logo-size)/2 - 20px); text-shadow: 0 6px 24px rgba(0,0,0,0.35); }

/* modern inputs */
.field { position:relative; margin:0.9rem 0; }
.input-ghost {
  width:100%; padding:14px 14px; border-radius:14px; border:none;
  background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(255,250,250,0.96));
  color:var(--text-contrast); font-size:15px; outline:none; box-shadow: 0 14px 34px rgba(10,4,4,0.12);
  transition: transform .12s ease, box-shadow .14s ease;
}
.input-ghost::placeholder{ color: rgba(60,10,10,0.35); }
.input-ghost:focus{ transform: translateY(-2px); box-shadow: 0 22px 48px rgba(255,60,60,0.12); }

/* labels */
.field label { display:block; font-size:13px; color: rgba(60,10,10,0.95); margin-bottom:8px; font-weight:800; letter-spacing:.2px; }

/* buttons */
.btn-primary {
  background: linear-gradient(180deg, var(--accent-1), var(--accent-2));
  border:none; color:#fff; font-weight:800; border-radius:14px; padding:12px 20px;
  box-shadow: 0 16px 48px rgba(255,60,60,0.18);
}
.btn-primary:active{ transform: translateY(1px); box-shadow: 0 10px 30px rgba(255,60,60,0.12); }
.btn-ghost { background: transparent; color:var(--text-contrast); border:1px solid rgba(120,20,20,0.06); padding:10px 14px; border-radius:12px; }

/* helper text */
.footer-note { font-size:13px; color: rgba(60,10,10,0.75); text-align:center; margin-top:12px; }

/* forgot */
.forgot-wrap { display:none; width:100%; border-radius:16px; padding:1.1rem; background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(255,250,250,0.88)); border:1px solid rgba(255,255,255,0.6); box-shadow:0 16px 48px rgba(0,0,0,0.28); z-index:8; }

/* responsive tweaks */
@media (max-width:720px){
  :root{ --card-width:92vw; --logo-size:170px; }
  .hero-logo{ margin-top:-85px; }
  .login-card{ padding:1.1rem; border-radius:16px; }
  .brand-title{ font-size:18px; }
}
</style>
</head>

<!-- Loading screen must remain exactly as-is -->
<div id="loading-screen">
    <img id="loading-gif" src="./images/Julies Loading.gif" alt="Loading...">
</div>

<body>

<!-- decorative layers -->
<div class="bg-hero" aria-hidden="true"></div>
<div class="grid" aria-hidden="true"></div>
<div class="bokeh" id="bokeh" aria-hidden="true"></div>
<div class="gloss" aria-hidden="true"></div>
<div class="particles" id="particles" aria-hidden="true"></div>
<div class="shards" id="shards"></div>

<div class="center-wrap">
  <div class="card-stage">

    <!-- big logo overlaps card -->
    <div class="hero-logo" aria-hidden="true">
      <img src="./images/tiny_logo.png" alt="Julie's Logo">
    </div>

    <div class="login-card" id="login-panel">
      <div style="text-align:center;margin-top:8px;">
        <div class="brand-title">Julie's POS and Management System</div>
      </div>

      <form id="login-form" autocomplete="off" style="margin-top:12px;">
        <div class="field">
          <label for="username">Username</label>
          <input id="username" name="username" autofocus class="input-ghost" placeholder="Enter username" required />
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" class="input-ghost" placeholder="Enter password" required />
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.6rem;">
          <button type="button" id="forgot-btn" class="btn-ghost">Forgot?</button>
          <button type="submit" class="btn-primary">Login</button>
        </div>

        <div class="footer-note">Employees only — contact admin for access</div>
      </form>
    </div>

    <div class="forgot-wrap" id="forgot-panel" aria-hidden="true">
      <form id="forgot-form">
        <div style="text-align:center;margin-bottom:.6rem;color:#7b1010;font-weight:700;">Reset password — enter email & new password</div>

        <div class="field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" class="input-ghost" placeholder="you@example.com" required />
        </div>

        <div class="field">
          <label for="new_password">New Password</label>
          <input id="new_password" name="new_password" type="password" class="input-ghost" placeholder="New password" required />
        </div>

        <div class="field">
          <label for="confirm_password">Confirm Password</label>
          <input id="confirm_password" name="confirm_password" type="password" class="input-ghost" placeholder="Confirm password" required />
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.6rem;">
          <button type="button" id="back-btn" class="btn-ghost">Back</button>
          <button type="submit" class="btn-primary">Reset Password</button>
        </div>
      </form>
    </div>

  </div>
</div>

<script>
$(function(){
  $('#forgot-btn').click(function(){ $('#login-panel').fadeOut(180, function(){ $('#forgot-panel').fadeIn(220); }); });
  $('#back-btn').click(function(){ $('#forgot-panel').fadeOut(160, function(){ $('#login-panel').fadeIn(200); }); });

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

/* bokeh blobs & particles generator */
(function(){
  // bokeh blobs
  var b = document.getElementById('bokeh');
  if(b){
    var colors = ['rgba(255,90,90,0.08)','rgba(255,120,120,0.06)','rgba(255,60,60,0.07)'];
    for(let i=0;i<8;i++){
      var el = document.createElement('div');
      el.className = 'blob';
      var size = Math.round(Math.random()*260)+80;
      el.style.width = el.style.height = size+'px';
      el.style.left = Math.round(Math.random()*100)+'%';
      el.style.top = Math.round(Math.random()*100)+'%';
      el.style.background = colors[Math.floor(Math.random()*colors.length)];
      el.style.opacity = (Math.random()*0.12)+0.04;
      b.appendChild(el);
    }
  }

  // particles dots
  var p = document.getElementById('particles');
  if(p){
    for(let i=0;i<30;i++){
      var d = document.createElement('div');
      d.className = 'dot';
      d.style.left = Math.round(Math.random()*100)+'%';
      d.style.top = Math.round(Math.random()*100)+'%';
      d.style.opacity = (Math.random()*0.08)+0.02;
      p.appendChild(d);
    }
  }
})();

/* enhanced shards generator with parallax */
(function(){
  var container = document.getElementById('shards');
  if(!container) return;
  function rand(min,max){ return Math.random()*(max-min)+min; }
  var max = 18;
  for(var i=0;i<max;i++){
    (function(i){
      var el = document.createElement('div');
      var sz = Math.round(rand(28,130));
      el.className = 'shard' + (Math.random()>0.8 ? ' large' : '');
      el.style.width = sz + 'px';
      el.style.height = Math.round(sz * rand(0.5,1.3)) + 'px';
      el.style.left = (rand(-10,110)) + '%';
      el.style.top = (rand(10,90)) + '%';
      el.style.opacity = rand(0.3,0.95);
      el.style.transform = 'rotate(' + rand(-40,40) + 'deg)';
      var dur = rand(12,34);
      var delay = rand(-dur, 6);
      el.style.animation = 'shard-drift ' + dur + 's linear ' + delay + 's infinite';
      el.style.borderRadius = Math.round(rand(4,16)) + 'px';
      el.style.boxShadow = '0 ' + Math.round(rand(4,30)) + 'px ' + Math.round(rand(8,50)) + 'px rgba(0,0,0,0.45), inset 0 1px 10px rgba(255,255,255,0.02)';
      // subtle inner tint
      el.style.background = 'linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,240,240,0.02)), linear-gradient(180deg, rgba(255,80,80,0.03), rgba(140,20,20,0.02))';
      container.appendChild(el);
    })(i);
  }

  // occasional respawn to keep variety
  setInterval(function(){
    var nodes = container.children;
    if(nodes.length === 0) return;
    var idx = Math.floor(Math.random()*nodes.length);
    var node = nodes[idx];
    if(!node) return;
    node.style.left = (rand(-12,112)) + '%';
    node.style.top = (rand(30,98)) + '%';
    node.style.opacity = rand(0.25,0.95);
    node.style.width = Math.round(rand(24,130)) + 'px';
    node.style.height = Math.round(rand(18,140)) + 'px';
    node.style.animation = 'shard-drift ' + rand(12,36) + 's linear ' + rand(0,6) + 's infinite';
    node.style.transform = 'rotate(' + rand(-40,40) + 'deg)';
  }, 2800);

  // gentle mouse parallax
  var shards = Array.prototype.slice.call(document.querySelectorAll('.shard'));
  document.addEventListener('mousemove', function(e){
    var cx = window.innerWidth/2, cy = window.innerHeight/2;
    var dx = (e.clientX - cx)/cx, dy = (e.clientY - cy)/cy;
    shards.forEach(function(s, idx){
      var depth = (idx % 6) / 8 + 0.125;
      s.style.transform = 'translate3d(' + (-dx * depth * 18) + 'px,' + (-dy * depth * 10) + 'px,0) rotate(' + (parseFloat((s.style.transform.match(/-?\d+(\.\d+)?/)||[0])[0]) || 0) + 'deg)';
    });
  }, {passive:true});
})();

// KEEP loading-screen fade script (preserved behaviour)
$(document).ready(function() {
    setTimeout(() => {
        $('#loading-screen').fadeOut('slow');
    }, 1500);
});
</script>
</body>
</html>
