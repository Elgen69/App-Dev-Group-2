<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location:./login.php");
    exit;
}
require_once('DBConnection.php');
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
if($_SESSION['type'] != 1 && in_array($page,array('maintenance','products','stocks'))){
    header("Location:./");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucwords(str_replace('_','',$page)) ?> | Julie's Management System</title>
    <link rel="stylesheet" href="./Font-Awesome-master/css/all.min.css">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./select2/css/select2.min.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/popper.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="./DataTables/datatables.min.css">
    <script src="./DataTables/datatables.min.js"></script>
    <script src="./select2/js/select2.full.min.js"></script>
    <script src="./Font-Awesome-master/js/all.min.js"></script>
    <script src="./js/script.js"></script>
    <style>
        :root{
              --sidebar-width: 350px;
    --radius: 12px;
    --card-bg: rgba(255, 255, 255, 0.6);
    --text-color: #000000ff;
    --link-color: #a7a7a7ff;
    --sidebar-glow: 0.35;
        }
        /* Dark mode variables */
        body.dark-mode{
            --sidebar-bg-start:#071012;
            --sidebar-bg-end:#022233;
            --sidebar-accent:#ffd166;
            --content-bg:#0f1720;
            --text-color:#e9eef6;
            --muted:#9aa6b2;
            --card-bg:#14202a;
        }

        html, body {
    height: 100%;
    margin: 0;
    font-family: "Segoe UI", Roboto, Arial, sans-serif;
    background: #fae4daff;
    color: var(--text-color);
    overflow-x: hidden;
}
#page-container {
    margin-left: var(--sidebar-width);
    padding: 1.75rem;
    min-height: 100vh;
    box-sizing: border-box;
}

        /* Sidebar: fixed, full height, non-scrollable */
        .app-sidebar {
    position: fixed;
    left: 0; top: 0; bottom: 0;
    width: var(--sidebar-width);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.25rem 0.75rem;
    background: linear-gradient(180deg, #651010ff, #1b0000);
    color: var(--link-color);
    box-shadow: 6px 0 25px rgba(0,0,0,0.55), inset 0 0 25px rgba(255,0,0,0.12);
    backdrop-filter: blur(20px) saturate(160%);
    border-right: 1px solid rgba(255,0,0,0.12);
    overflow: hidden;
    transition: all 0.3s ease;
}
        /* Big centered logo area */
        .sidebar-header{
            width:100%;
            display:flex;
            align-items:center;
            justify-content:center;
            flex-direction:column;
            gap:.5rem;
            padding: .5rem 0 1.25rem;
        }
        .sidebar-logo{
            width: 160px;
            height: auto;
            object-fit: contain;
            border-radius:12px;
            transition: transform .22s ease, box-shadow .22s ease;
        }
        .sidebar-header .brand-title{
            font-weight:800;
            color:var(--link-color);
            letter-spacing:.6px;
            font-size:1.05rem;
            display:block;
            margin-top:6px;
        }

        /* Nav list - vertical and spread */
        .sidebar-nav{
            width:100%;
            display:flex;
            flex-direction:column;
            gap:10px;
            flex:1 1 auto;
            justify-content:flex-start;
            padding:0 8px;
        }
        .sidebar-nav a{
            display:flex;
            align-items:center;
            gap:.8rem;
            padding:.9rem .9rem;
            border-radius:10px;
            text-decoration:none;
            color:var(--link-color);
            font-weight:600;
            transition: all .18s ease;
            background:transparent;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.02);
        }
        .sidebar-nav a i{ min-width:26px; text-align:center; font-size:1.05rem; color:var(--link-color); }

        .sidebar-nav a:hover{
            transform: translateX(4px);
            background: linear-gradient(90deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
            color:#fff;
            box-shadow: 0 8px 24px rgba(2,24,40,0.35);
        }
        .sidebar-nav a.active{
            color: var(--text-color);
            background: linear-gradient(90deg, rgba(255,255,255,0.92), rgba(255,255,255,0.78));
            box-shadow: 0 12px 30px rgba(0,0,0,0.18);
            transform: translateX(4px);
        }
        .sidebar-nav a.active i{ color: var(--sidebar-accent); }

        /* Optional small buttons group under logo */
        .sidebar-actions{
            width:100%;
            display:flex;
            flex-direction:column;
            gap:.5rem;
            padding: .5rem 8px 1rem;
        }
        .sidebar-actions .btn{
            width:100%;
            border-radius:8px;
            padding:.55rem .6rem;
            font-weight:700;
        }

        /* content area sits to the right of sidebar and scrolls */
        

        /* keep bootstrap modal, cards readable in dark mode */
        .card{
            background:var(--card-bg) !important;
            color:var(--text-color) !important;
        }
        .navbar, .custom-navbar { background:transparent; box-shadow:none; }

        /* small screens: sidebar collapses to top bar */
        @media (max-width: 767.98px){
            .app-sidebar{
                position:relative;
                width:100%;
                height:auto;
                flex-direction:row;
                align-items:center;
                padding:.5rem;
            }
            .sidebar-header{ flex-direction:row; gap:.5rem; height:auto; padding:0; }
            .sidebar-logo{ width:120px; }
            #page-container{ margin-left:0; padding:.75rem; }
            .sidebar-nav{ flex-direction:row; gap:.5rem; overflow-x:auto; padding: .5rem 0; }
            .sidebar-nav a{ white-space:nowrap; padding:.5rem .7rem; border-radius:6px; }
            .sidebar-actions{ display:none; }
        }

        /* scrollbar styles for content only */
        #page-container::-webkit-scrollbar{ width:9px; }
        #page-container::-webkit-scrollbar-thumb{ background: rgba(0,0,0,0.18); border-radius:8px; }

        /* preserve some existing helper styles from original for compatibility */
        .thumbnail-img{
            width:50px;
            height:50px;
            margin:2px
        }
        .truncate-1 {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
        }
        .truncate-3 {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        .modal-priority {
            z-index: 1050;
        }
        .modal-dialog {
            max-width: 800px; 
        }
        .modal-dialog.large {
            width: 80% !important;
            max-width: unset;
        }
        .modal-dialog.mid-large {
            width: 50% !important;
            max-width: unset;
        }
        @media (max-width:720px){
            .modal-dialog.large {
                width: 100% !important;
                max-width: unset;
            }
            .modal-dialog.mid-large {
                width: 100% !important;
                max-width: unset;
            }  
        }
        img.display-image {
            width: 100%;
            height: 45vh;
            object-fit: cover;
            background: black;
        }
        /* small tweaks for badges used */
        .badge-available {
            background-color: #03943fff; 
            color: #ffffff; 
            padding: ;
            border-radius: 1000px ;
            font-size: 0.1rem;
        }
        .badge-unavailable {
            background-color: #dc3545; 
            color: #ffffff; 
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <main>
    <!-- replace top nav with fixed left sidebar -->
    <aside class="app-sidebar" aria-label="Main navigation">
        <div class="sidebar-header">
            <a href="./" class="d-block text-center" style="width:100%;">
                <img src="./images/tiny_logo.png" alt="Julie's" class="sidebar-logo">
            </a>
            <span class="brand-title">Julie's Bakery</span>
        </div>

        <nav class="sidebar-nav" role="navigation">
            <a class="<?php echo ($page == 'home')? 'active' : '' ?>" href="./?page=home"><i class="fa fa-home"></i> Home</a>
            <?php if($_SESSION['type'] == 1): ?>
            <a class="<?php echo ($page == 'products')? 'active' : '' ?>" href="./?page=products"><i class="fa fa-shopping-cart"></i> Products</a>
            <a class="<?php echo ($page == 'stocks')? 'active' : '' ?>" href="./?page=stocks"><i class="fa fa-cube"></i> Stocks</a>
            <a class="<?php echo ($page == 'users')? 'active' : '' ?>" href="./?page=users"><i class="fa fa-users"></i> Users</a>
            <a class="<?php echo ($page == 'maintenance')? 'active' : '' ?>" href="./?page=maintenance"><i class="fa fa-wrench"></i> Maintenance</a>
            <?php endif; ?>
            <?php if (in_array($_SESSION['type'], [1, 0, NULL])): ?>
            <a class="<?php echo ($page == 'manage_shifts')? 'active' : '' ?>" href="./?page=manage_shifts"><i class="fa fa-clock"></i> Shift</a>
            <?php endif; ?>
            <a class="<?php echo ($page == 'sales')? 'active' : '' ?>" href="./?page=sales"><i class="fa fa-dollar-sign"></i> POS</a>
            <a class="<?php echo ($page == 'sales_report')? 'active' : '' ?>" href="./?page=sales_report"><i class="fa fa-chart-bar"></i> Sales</a>
        </nav>

        <div class="sidebar-actions">
            <div class="form-check form-switch d-flex align-items-center justify-content-between">
                <label class="form-check-label" for="darkModeToggleSmall">Dark Mode</label>
                <input class="form-check-input" type="checkbox" id="darkModeToggleSmall">
            </div>
            <div class="text-center">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle w-100" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        Hello <?php echo $_SESSION['fullname'] ?>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item" href="./?page=manage_account">Manage Account</a></li>
                        <li><a class="dropdown-item" href="./Actions.php?a=logout">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </aside>
    
    <div id="page-container">
        <?php 
            if(isset($_SESSION['flashdata'])):
        ?>
        <div class="dynamic_alert alert alert-<?php echo $_SESSION['flashdata']['type'] ?> rounded-0 shadow">
        <div class="float-end"><a href="javascript:void(0)" class="text-dark text-decoration-none" onclick="$(this).closest('.dynamic_alert').hide('slow').remove()">x</a></div>
            <?php echo $_SESSION['flashdata']['msg'] ?>
        </div>
        <?php unset($_SESSION['flashdata']) ?>
        <?php endif; ?>
        <?php
            include $page.'.php';
        ?>
    </div>
    </main>
    <div class="modal fade" id="uni_modal" role='dialog' data-bs-backdrop="static" data-bs-keyboard="true">
        <div class="modal-dialog modal-md modal-dialog-centered rounded-0" role="document">
        <div class="modal-content rounded-0">
            <div class="modal-header py-2">
            <h5 class="modal-title"></h5>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer py-1">
            <button type="button" class="btn btn-sm rounded-0 btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
            <button type="button" class="btn btn-sm rounded-0 btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
        </div>
    </div>
    <div class="modal fade" id="uni_modal_secondary" role='dialog' data-bs-backdrop="static" data-bs-keyboard="true">
        <div class="modal-dialog modal-md modal-dialog-centered  rounded-0" role="document">
        <div class="modal-content rounded-0">
            <div class="modal-header py-2">
            <h5 class="modal-title"></h5>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer py-1">
            <button type="button" class="btn btn-sm rounded-0 btn-primary" id='submit' onclick="$('#uni_modal_secondary form').submit()">Save</button>
            <button type="button" class="btn btn-sm rounded-0 btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
        </div>
    </div>
    <div class="modal fade" id="confirm_modal" role='dialog'>
        <div class="modal-dialog modal-md modal-dialog-centered  rounded-0" role="document">
        <div class="modal-content rounded-0 rounded-0">
            <div class="modal-header py-2">
            <h5 class="modal-title">Confirmation</h5>
        </div>
        <div class="modal-body">
            <div id="delete_content"></div>
        </div>
        <div class="modal-footer py-1">
            <button type="button" class="btn btn-primary btn-sm rounded-0" id='confirm' onclick="">Continue</button>
            <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
        </div>
    </div>

<script>
    // dark mode control: toggles class on body so CSS variables change globally
    (function(){
        const smallToggle = document.getElementById('darkModeToggleSmall');
        const saved = localStorage.getItem('darkMode') === 'true';
        if(saved) document.body.classList.add('dark-mode');
        if(smallToggle) smallToggle.checked = saved;

        function toggle(e){
            const on = e ? e.target.checked : !document.body.classList.contains('dark-mode');
            document.body.classList.toggle('dark-mode', on);
            localStorage.setItem('darkMode', on);
            // update any other switches if present
            const other = document.getElementById('darkModeToggle');
            if(other) other.checked = on;
        }

        if(smallToggle) smallToggle.addEventListener('change', toggle);

        // keep legacy toggle if present on page
        const legacy = document.getElementById('darkModeToggle');
        if(legacy) {
            legacy.checked = saved;
            legacy.addEventListener('change', toggle);
        }
    })();
</script>
</body>
</html>
