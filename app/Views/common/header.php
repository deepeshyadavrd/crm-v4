<!doctype html>
<html class="no-js " lang="en">

<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<meta name="description" content="Urbanwood CRM">
<meta name="robots" content="noindex">
<title>CRM ~ Urbanwood</title>
<link rel="icon" href="favicon.ico" type="image/x-icon">
<!-- Favicon-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dynatable/0.3.1/jquery.dynatable.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?=base_url('assets');?>/plugins/morrisjs/morris.css"/>
<!-- Custom Css -->
<link href="<?=base_url('assets');?>/plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" />
<link rel="stylesheet" href="<?=base_url('assets');?>/css/main.css">
<link rel="stylesheet" href="<?=base_url('assets');?>/css/hm-style.css">
<link rel="stylesheet" href="<?=base_url('assets');?>/css/color_skins.css">
<!-- Multi Select Css -->
<link rel="stylesheet" href="<?=base_url('assets');?>/plugins/multi-select/css/multi-select.css">
<!-- Bootstrap Select Css -->
<link rel="stylesheet" href="<?=base_url('assets');?>/plugins/bootstrap-select/css/bootstrap-select.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" />


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<style>
    .clicked { background-color: red; }
    .page-loader-wrapper2 {
    z-index: 99999999;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background: #eee;
    overflow: hidden;
    text-align: center;
}
.searchmain {
    display:none;
    position:absolute;
    background: white;
    max-width: 400px;
    top: 50px;
    box-shadow: 0 10px 50px 0 rgba(0, 0, 0, .2);
    font-size: 14px;
    list-style: none;    
    padding-left: 0;
    max-height: 500px;
    overflow: scroll;
}
ul.menu li a {
    padding: 10px 15px;
    text-decoration: none;
    -moz-transition: .5s;
    -o-transition: .5s;
    -webkit-transition: .5s;
    transition: .5s;
    display: block;
}
.icon-circle{
    width: 36px;
    height: 36px;
    -webkit-border-radius: 50%;
    -moz-border-radius: 50%;
    -ms-border-radius: 50%;
    border-radius: 50%;
    color: #fff;
    vertical-align: top;
    display: inline-block;
    text-align: center;
}
.icon-circle i {
font-size: 18px;
line-height: 36px;
}
.menu-info{
    color: black;
    font-size: 14px;
    display: inline-block;
}
.menu-info h4 {
    font-size: 14px;
    color: #424242;
    font-weight: 400;
    margin: 0 !important;
    line-height: 1.45em;
}
</style>
</head>

<body class="theme-cyan index2">
<!-- Page Loader -->
<div class="page-loader-wrapper">
    <div class="loader">
        <div class="m-t-30"><img class="zmdi-hc-spin" src="<?=base_url('assets');?>/images/logo.svg" width="48" height="48" alt="Compass"></div>
        <p>Please wait...</p>
    </div>
</div>

<!-- Overlay For Sidebars -->
<div class="overlay"></div>

<!-- Top Bar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid d-flex align-items-center">        
        <div class="navbar-brand">
            <a href="javascript:void(0);" class="h-bars"></a>
            <a class="navbar-brand" href="<?=base_url();?>"><img src="<?=base_url('assets');?>/images/urbanwoodlogo.png" width="160" alt="Compass"></a>
        </div>
        <!-- Center: Search -->
        <div class="d-flex flex-grow-1 mx-lg-4 my-2 my-lg-0" style="max-width:500px;">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search..." id="input_value">

            <ul class="searchmain" id="searchmain" >
                    <li class="body" style="min-width: 400px;">
                        <ul class="menu list-unstyled" id="searchlist" style="padding: 10px 4px 10px 3px;">
                        <div class="page-loader-wrapper2" style="position:relative;">
                            <div class="loader">
                                <div class="m-t-30"><img class="zmdi-hc-spin" src="assets/images/logo.svg" width="48" height="48" alt="Compass"></div>
                                <p>Start Typing</p>
                             </div>
                        </div>
                        </ul>
                    </li>
                </ul>
          </div>
        </div>
        <!-- <div class="me-3">
            
        </div> -->
        <ul class="nav ms-auto align-items-center">
            <li class="nav-item me-3"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productSearchModal">
                Search Product
            </button></li>
            <li class="nav-item me-3"><?php echo session()->get('username'); ?></li>
            <li class="nav-item me-3"><a href="<?=base_url('leads');?>" class="mega-menu" data-close="true"><i class="zmdi zmdi-filter-list"></i><span id="leadCount" style="
        position: absolute;
        top: 3px;
        background: red;
        color: white;
        border-radius: 50%;
        padding: 3px 7px;
        font-size: 12px;
        display: none;
    ">0</span></a></li>
            <li class="nav-item dropdown me-3"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button"><i class="zmdi zmdi-notifications"></i>
                <div class="notify"><span class="heartbit"></span><span class="point"></span></div>
                </a>
                <ul class="dropdown-menu dropdown-menu-right slideDown">
                    <li class="header">Reminders</li>
                    <li class="body">
                        <ul class="menu list-unstyled" id="reminders">
                            <li> <a href="javascript:void(0);">
                                <div class="icon-circle bg-blue"><i class="zmdi zmdi-account"></i></div>
                                <div class="menu-info">
                                    <h4>8 New Members joined</h4>
                                    <p><i class="zmdi zmdi-time"></i> 14 mins ago </p>
                                </div>
                                </a> </li>
                            
                        </ul>
                    </li>
                    <li class="footer"> <a href="javascript:void(0);">View All Notifications</a> </li>
                </ul>
            </li>
            <li class="nav-item me-3">
                <a href="javascript:void(0);" class="fullscreen hidden-sm-down" data-provide="fullscreen" data-close="true"><i class="zmdi zmdi-fullscreen"></i></a>
            </li>
            <li class="nav-item me-3"><a href="<?=base_url('auth/logout');?>" class="mega-menu" data-close="true"><i class="zmdi zmdi-power"></i></a></li>
            <li  class="nav-item"><a href="javascript:void(0);" class="js-right-sidebar" data-close="true"><i class="zmdi zmdi-settings zmdi-hc-spin"></i></a></li>
            
        </ul>
    </div>
</nav>

<div class="menu-container">
    <div class="menu">
        <ul>
            <?php if($user_type ==1){ ?>
                <li><a href="<?=base_url();?>">Dashboard</a></li>
                <li><a href="<?=base_url('orders');?>">Orders</a> </li>
                <li><a href="<?=base_url('users');?>">Users</a> </li>
                <li><a href="<?=base_url('tickets');?>">Tickets</a> </li>
                <li><a href="<?=base_url('leads');?>">Leads</a> </li>
                <li><a href="<?=base_url('inventory');?>">Inventory</a> </li>
                <li><a href="<?=base_url('reports');?>">Reports</a> </li>
            <?php }elseif($user_type==11){ ?>
                 <li><a href="<?=base_url();?>">Dashboard</a></li>
                 <li><a href="<?=base_url('tickets');?>">Tickets</a> </li>
            <?php }elseif(in_array($user_type, [12, 14, 17, 21])){ ?>
                <li><a href="<?=base_url('orders');?>">Orders</a> </li>
                <li><a href="<?=base_url('leads');?>">Leads</a> </li>
                
                <li><a href="<?=base_url('reports');?>">Reports</a> </li>
            <?php } ?>
        </ul>
    </div>
</div>