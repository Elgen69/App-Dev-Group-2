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
<link rel="stylesheet" href="./Font-Awesome-master/css/all.min.css">
<script src="./js/jquery-3.6.0.min.js"></script>
<script src="./js/popper.min.js"></script>
<script src="./js/bootstrap.min.js"></script>

<style>
/* PAGE RESET */
html,body { height:100%; margin:0; font-family: "Helvetica Neue", Arial, sans-serif; -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale; }

/* Full-screen loading overlay (kept for a slight loader) */
#loading-screen{ position: fixed; inset: 0; width: 100vw; height: 100vh; display:flex; justify-content:center; align-items:center; background: rgba(255,255,255,0.95); z-index:99999; overflow:hidden; }

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

/* full-bleed blurred background image (static) */
.bg-photo { position:fixed; inset:0; background-image: url('./images/Login_blur.jpg'); background-size:cover; background-position:center; filter: blur(6px) saturate(1.05); transform: scale(1.02); z-index:0; }

/* slight dark overlay to improve contrast */
.bg-overlay { position:fixed; inset:0; background: rgba(0,0,0,0.18); z-index:1; }

/* matrix canvas removed */
#matrix-canvas { display:none; }

/* brand title (simple centered) positioned between top and login card) */
.header-title { position:fixed; left:0; right:0; top:30vh; z-index:3; text-align:center; font-family:'Pacifico', cursive; color: #ffffff; font-size:28px; line-height:1; -webkit-text-stroke: .6px rgba(0,0,0,0.6); text-shadow: 0 1px 2px rgba(0,0,0,0.6); pointer-events:none; }

/* center area for card */
.center-wrap { position:relative; z-index:4; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem; }

/* Decorative background removed to match other pages */
:root{ --card-z: 4; --content-bg:#f6f7fb; --card-bg:#ffffff; --text-color:#0b1220; }

.login-card, .card, .card-body, .card .card-body, #login-panel {
  position: relative;
  z-index: var(--card-z) !important;
}


/* login card */
.login-card { width:420px; max-width:94vw; background: #023047; color: #ffffff; border-radius:12px; padding:1.2rem 1.3rem; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.06); position: relative; z-index: 4; }

/* subtle inner */
.login-card .card-inner { border-radius:8px; padding:10px; }

/* small instructions */
.login-card small { display:block; text-align:center; color:rgba(255,255,255,0.92); margin-bottom:.6rem; }

/* inputs (scoped to login-card) */
.login-card .form-control { border-radius:6px; background:#ffffff; color: #0b1220; border: 1px solid rgba(0,0,0,0.08); padding:10px 12px; font-size:14px; }
label { color: rgba(0,0,0,0.75); font-weight:600; font-size:.85rem; }
/* login card labels */
.login-card label { color: rgba(255,255,255,0.92); }

/* fancy buttons */
.btn-primary { background: linear-gradient(90deg, #2d8a2d, #198a4b); border:none; color:white; font-weight:600; border-radius:8px; padding:8px 14px; }
/* secondary button inside login card should be visible on the dark background */
.login-card .btn-secondary { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.95); border: 1px solid rgba(255,255,255,0.06); border-radius:8px; }

/* Forgot / login row */
.actions-row { display:flex; gap:.5rem; justify-content:flex-end; margin-top:.6rem; }

/* smaller faded footer note */
.footer-note { font-size:12px; color:rgba(255,255,255,0.72); text-align:center; margin-top:10px; }

/* forgot password card (hidden by default) - share same style */
.forgot-wrap { display:none; width:420px; max-width:94vw; }

/* RESPONSIVE */
@media (max-width:540px){
  .header-title { font-size:18px; top:20vh; }
  .login-card { padding:.9rem; }
}
</style>
</head>

<div id="loading-screen">
    <img id="loading-gif" src="./images/Julies Loading.gif" alt="Loading...">
</div>

<body>

<!-- full-bleed blurred background photo (static) and dark overlay -->
<div class="bg-photo" aria-hidden="true"></div>
<div class="bg-overlay" aria-hidden="true"></div>

<!-- big title -->
<div class="header-title">Julie's POS and Management System</div>

<!-- main center -->
<div class="center-wrap">
  <!-- login panel -->
  <div class="card login-card" id="login-panel">
    <div class="card-inner">
      <form id="login-form" autocomplete="off">
        <div class="text-center mb-2"><img src="./images/tiny_logo.png" alt="Julie's" style="width:120px; height:auto;"></div>
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
  <div class="card login-card forgot-wrap" id="forgot-panel">
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
/* Matrix removed for uniformity with other pages */

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
