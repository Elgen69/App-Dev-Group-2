<!-- Modernized Login Page (Front-end Only) → Backend untouched -->
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>LOGIN | Julie's POS</title>
<link rel="stylesheet" href="./css/bootstrap.min.css">
<script src="./js/jquery-3.6.0.min.js"></script>
<script src="./js/bootstrap.min.js"></script>

<style>
body {
  height:100%;
  margin:0;
  font-family: "Inter", sans-serif;
  background: linear-gradient(135deg, #2b0000, #000000);
  color:#fff;
  overflow:hidden;
  position: relative;
}
body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: url('images/Login_blur.jpg') no-repeat center center;
  background-size: cover;
  opacity: 0.;
  z-index: -1;
}

/* red glow background circles */
.bg-circle {
  position:absolute;
  border-radius:50%;
  filter: blur(120px);
  opacity:0.45;
  z-index:-1;
}
#c1 { width:420px; height:420px; background:#a00000; top:-80px; left:-80px; }
#c2 { width:380px; height:380px; background:#ff2b2b; bottom:-120px; right:-80px; }

/* layout */
.container-center {
  min-height:100vh;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:20px;
}

/* glass card */
.login-card {
  width:380px;
  max-width:95%;
  background: rgba(255, 255, 255, 0.06);
  backdrop-filter: blur(20px) saturate(180%);
  border-radius:20px;
  padding:32px;
  border:1px solid rgba(255, 255, 255, 0.15);
  box-shadow:0 10px 40px rgba(0, 0, 0, 0.5);
  animation: fadeIn 0.9s ease;
  transition:0.3s;
}
.login-card:hover {
  border:1px solid rgba(255, 80, 80, 0.45);
  box-shadow:0 12px 48px rgba(255, 25, 25, 0.25);
}

/* Titles */
.login-title {
  font-size:2rem;
  font-weight:700;
  text-align:center;
  margin-bottom:5px;
  color:#ff4d4d;
}
.login-subtext {
  text-align:center;
  opacity:0.85;
  font-size:0.9rem;
  margin-bottom:22px;
}

/* Floating label fields */
.form-group {
  margin-bottom:18px;
}
.form-control {
  background:rgba(255,255,255,0.12);
  border:1px solid rgba(255,255,255,0.15);
  color: white;
  border-radius:10px;
  padding:12px;
  transition:0.2s;
}
.form-control:focus {
  color: white;
  background:rgba(255,255,255,0.18);
  border-color:#ff4d4d;
  box-shadow: 0 0 8px rgba(255, 60, 60, 0.5);
}

/* Red Modern Button */
.btn-main {
  width:100%;
  background:#ff2e2e;
  border:none;
  padding:12px;
  font-weight:600;
  border-radius:12px;
  transition:0.2s;
  color:#fff;
  font-size:1rem;
}
.btn-main:hover {
  background:#d61d1d;
  box-shadow:0 0 12px rgba(255, 50, 50, 0.55);
}

/* links */
.btn-link-custom {
  text-decoration:none;
  font-size:0.85rem;
  opacity:0.8;
  color:#ffaaaa;
  transition:0.2s;
}
.btn-link-custom:hover {
  opacity:1;
  color:#ffdddd;
}

/* subtle fade-in animation */
@keyframes fadeIn {
  from { opacity:0; transform:translateY(16px); }
  to { opacity:1; transform:translateY(0); }
}
</style>
</head>

<body>

<!-- glowing circles -->
<div id="c1" class="bg-circle"></div>
<div id="c2" class="bg-circle"></div>

<div class="container-center">

  <!-- LOGIN PANEL -->
  <div class="login-card" id="login-panel">

    <div class="login-title">Welcome Back</div>
    <div class="login-subtext">Julie's POS & Management System</div>

    <form id="login-form">
      <div class="form-group">
        <label>Username</label>
        <input class="form-control" name="username" required autofocus>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input class="form-control" type="password" name="password" required>
      </div>

      <button class="btn btn-main" type="submit">Login</button>

      <div class="text-center mt-3" style="color:#ffccaa; font-size:0.9rem;">
        <strong>Note:</strong> This login is for employees only.
      </div>

      <div class="text-center mt-3">
        <a href="#" id="forgot-btn" class="btn-link-custom">Forgot Password?</a>
      </div>
    </form>
  </div>

  <!-- FORGOT PASSWORD PANEL -->
  <div class="login-card" id="forgot-panel" style="display:none;">
    <div class="login-title">Reset Password</div>
    <div class="login-subtext">Enter your email to continue</div>

    <form id="forgot-form">
      <div class="form-group">
        <label>Email</label>
        <input class="form-control" type="email" name="email" required>
      </div>

      <div class="form-group">
        <label>New Password</label>
        <input class="form-control" type="password" name="new_password" required>
      </div>

      <div class="form-group">
        <label>Confirm Password</label>
        <input class="form-control" type="password" name="confirm_password" required>
      </div>

      <button class="btn btn-main" type="submit">Reset</button>

      <div class="text-center mt-3">
        <a href="#" id="back-btn" class="btn-link-custom">Back to Login</a>
      </div>
    </form>
  </div>
</div>


<script>
// toggle panels
$("#forgot-btn").click(()=>{
  $("#login-panel").hide();
  $("#forgot-panel").fadeIn();
});
$("#back-btn").click(()=>{
  $("#forgot-panel").hide();
  $("#login-panel").fadeIn();
});

// login ajax
$("#login-form").submit(function(e){
  e.preventDefault();
  $.post('./Actions.php?a=login', $(this).serialize(), function(resp){
    if(resp.status==='success') location.href='./';
    else alert(resp.msg || 'Login failed');
  },'json');
});

// reset ajax
$("#forgot-form").submit(function(e){
  e.preventDefault();
  $.post('./Actions.php?a=reset_password', $(this).serialize(), function(resp){
    if(resp.status==='success'){
      alert('Password updated.');
      $("#back-btn").click();
    } else alert(resp.msg || 'Error');
  },'json');
});
</script>

</body>
</html>
