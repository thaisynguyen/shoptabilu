@extends('admin.layouts.adminmaster')

@section('link_css')
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    {{ HTML::style('public/assets/admintheme/global/plugins/font-awesome/css/font-awesome.css') }}
    {{ HTML::style('public/assets/admintheme/global/plugins/simple-line-icons/simple-line-icons.min.css') }}
    {{ HTML::style('public/assets/admintheme/global/plugins/bootstrap/css/bootstrap.min.css') }}
    {{ HTML::style('public/assets/admintheme/global/plugins/uniform/css/uniform.default.css') }}
    {{ HTML::style('public/assets/admintheme/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css') }}
    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BEGIN PAGE LEVEL PLUGINS -->
    {{ HTML::style('public/assets/admintheme/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css') }}
    {{ HTML::style('public/assets/admintheme/global/plugins/morris/morris.css') }}
    {{ HTML::style('public/assets/admintheme/global/plugins/fullcalendar/fullcalendar.min.css') }}
    {{ HTML::style('public/assets/admintheme/global/plugins/jqvmap/jqvmap/jqvmap.css') }}
    {{ HTML::style('public/assets/admintheme/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}
    {{ HTML::style('public/assets/admintheme/global/plugins/datatables/datatables.min.css') }}
    {{ HTML::style('public/assets/admintheme//global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}
    <!-- END PAGE LEVEL PLUGINS -->

    <!-- BEGIN THEME GLOBAL STYLES -->
    {{ HTML::style('public/assets/admintheme/global/css/components.min.css') }}
    {{ HTML::style('public/assets/admintheme/global/css/plugins.min.css') }}
    <!-- END THEME GLOBAL STYLES -->

    <!-- BEGIN THEME LAYOUT STYLES -->
    {{ HTML::style('public/assets/admintheme/layouts/layout/css/layout.min.css') }}
    {{ HTML::style('public/assets/admintheme/layouts/layout/css/themes/darkblue.min.css') }}
    {{ HTML::style('public/assets/admintheme/global/plugins/jstree/themes/default/style.min.css') }}
    {{ HTML::style('public/assets/admintheme/layouts/layout/css/custom.css') }}
    <!-- END THEME LAYOUT STYLES -->
@stop

@section('body')
<?php
    $username       =  Session::get('susername');
    $nameOfUser     =  Session::get('sname');
    $sDataUser      =  Session::get('sDataUser');
?>
@include('popup.userProfile', array('name' => $nameOfUser))
@include('popup.updateUserProfile', array('name' => $nameOfUser, 'username' => $username))
<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white">
<!-- BEGIN HEADER -->
<div class="page-header navbar navbar-fixed-top">
    <!-- BEGIN HEADER INNER -->
    <div class="page-header-inner ">
        <!-- BEGIN LOGO -->
        <div class="page-logo">
            <a href="index.html">
                <img src="{{url('/public/assets/admintheme/layouts/layout/img/logoKB1.png')}}" alt="logo" class="logo-default" /> </a>
            <div class="menu-toggler sidebar-toggler"> </div>
        </div>
        <!-- END LOGO -->
        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"> </a>
        <!-- END RESPONSIVE MENU TOGGLER -->
        <!-- BEGIN TOP NAVIGATION MENU -->
        @include('admin.layouts.topmenu')
        <!-- END TOP NAVIGATION MENU -->
    </div>
    <!-- END HEADER INNER -->
</div>
<!-- END HEADER -->
<!-- BEGIN HEADER & CONTENT DIVIDER -->
<div class="clearfix"> </div>
<!-- END HEADER & CONTENT DIVIDER -->
<!-- BEGIN CONTAINER -->
<div class="page-container">
    <!-- BEGIN SIDEBAR -->
    <div class="page-sidebar-wrapper">
        <!-- BEGIN SIDEBAR -->
        <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
        <!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
        <div class="page-sidebar navbar-collapse collapse">
            <!-- BEGIN SIDEBAR MENU -->
            <!-- DOC: Apply "page-sidebar-menu-light" class right after "page-sidebar-menu" to enable light sidebar menu style(without borders) -->
            <!-- DOC: Apply "page-sidebar-menu-hover-submenu" class right after "page-sidebar-menu" to enable hoverable(hover vs accordion) sub menu mode -->
            <!-- DOC: Apply "page-sidebar-menu-closed" class right after "page-sidebar-menu" to collapse("page-sidebar-closed" class must be applied to the body element) the sidebar sub menu mode -->
            <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
            <!-- DOC: Set data-keep-expand="true" to keep the submenues expanded -->
            <!-- DOC: Set data-auto-speed="200" to adjust the sub menu slide up/down speed -->
            @include('admin.layouts.leftmenu')
            <!-- END SIDEBAR MENU -->
            <!-- END SIDEBAR MENU -->
        </div>
        <!-- END SIDEBAR -->
    </div>
    <!-- END SIDEBAR -->
    <!-- BEGIN CONTENT -->
    @yield('section')
    <!-- END CONTENT -->
    <!-- BEGIN QUICK SIDEBAR -->
    <a href="javascript:;" class="page-quick-sidebar-toggler">
        <i class="icon-login"></i>
    </a>
    <div class="page-quick-sidebar-wrapper" data-close-on-body-click="false">
        <div class="page-quick-sidebar">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="javascript:;" data-target="#quick_sidebar_tab_1" data-toggle="tab"> Users
                        <span class="badge badge-danger">2</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:;" data-target="#quick_sidebar_tab_2" data-toggle="tab"> Alerts
                        <span class="badge badge-success">7</span>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown"> More
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu pull-right">
                        <li>
                            <a href="javascript:;" data-target="#quick_sidebar_tab_3" data-toggle="tab">
                                <i class="icon-bell"></i> Alerts </a>
                        </li>
                        <li>
                            <a href="javascript:;" data-target="#quick_sidebar_tab_3" data-toggle="tab">
                                <i class="icon-info"></i> Notifications </a>
                        </li>
                        <li>
                            <a href="javascript:;" data-target="#quick_sidebar_tab_3" data-toggle="tab">
                                <i class="icon-speech"></i> Activities </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="javascript:;" data-target="#quick_sidebar_tab_3" data-toggle="tab">
                                <i class="icon-settings"></i> Settings </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <!-- END QUICK SIDEBAR -->
</div>
<!-- END CONTAINER -->

<!-- BEGIN FOOTER -->
<div class="page-footer">
    <!--<div class="page-footer-inner"> 2016 &copy; Kim Kim Bich. All Rights Reserved.
        <a href="http://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes" title="Purchase Metronic just for 27$ and get lifetime updates for free" target="_blank">Purchase Metronic!</a>
    </div>-->
    <div class="scroll-to-top">
        <i class="icon-arrow-up"></i>
    </div>
</div>
<!-- END FOOTER -->

<!-- END THEME LAYOUT SCRIPTS -->
</body>
@stop

@section('link_js')
    <!-- BEGIN CORE PLUGINS -->
    {{ HTML::script('public/assets/admintheme/global/plugins/jquery.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/bootstrap/js/bootstrap.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/js.cookie.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/jquery.blockui.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/uniform/jquery.uniform.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}
            <!-- END CORE PLUGINS -->

    <!-- BEGIN PAGE LEVEL PLUGINS -->
    {{ HTML::script('public/assets/admintheme/global/plugins/bootbox/bootbox.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/scripts/datatable.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/datatables/datatables.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/moment.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/morris/morris.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/morris/raphael-min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/counterup/jquery.waypoints.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/counterup/jquery.counterup.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/amcharts/amcharts/amcharts.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/amcharts/amcharts/serial.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/amcharts/amcharts/pie.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/amcharts/amcharts/radar.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/amcharts/amcharts/themes/light.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/amcharts/amcharts/themes/patterns.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/amcharts/amcharts/themes/chalk.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/amcharts/ammap/ammap.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/amcharts/ammap/maps/js/worldLow.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/amcharts/amstockcharts/amstock.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/fullcalendar/fullcalendar.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/flot/jquery.flot.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/flot/jquery.flot.resize.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/flot/jquery.flot.categories.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/jquery-easypiechart/jquery.easypiechart.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/jquery.sparkline.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}
    <!-- END PAGE LEVEL PLUGINS -->

    <!-- BEGIN THEME GLOBAL SCRIPTS -->

	{{ HTML::script('public/assets/admintheme/global/plugins/jstree/dist/jstree.js') }}
	{{ HTML::script('public/assets/admintheme/global/plugins/jstree/dist/jstree.min.js') }}
	{{ HTML::script('public/assets/admintheme/global/plugins/bootstrap-confirmation/bootstrap-confirmation.min.js') }}
    {{ HTML::script('public/assets/admintheme/global/scripts/app.min.js') }}
	{{ HTML::script('public/assets/admintheme/pages/scripts/ui-confirmations.min.js') }}	
    {{ HTML::script('public/assets/admintheme/pages/scripts/ui-bootbox.min.js') }}
    {{ HTML::script('public/assets/admintheme/pages/scripts/table-datatables-editable.min.js') }}
    {{ HTML::script('public/assets/admintheme/pages/scripts/dashboard.min.js') }}
    {{ HTML::script('public/assets/admintheme/layouts/layout/scripts/layout.min.js') }}
    {{ HTML::script('public/assets/admintheme/layouts/layout/scripts/demo.min.js') }}
    {{ HTML::script('public/assets/admintheme/layouts/global/scripts/quick-sidebar.min.js') }}
    {{ HTML::script('public/assets/scripts/bootstrap-notify.js') }}
    {{ HTML::script('public/assets/scripts/bootstrap-notify.min.js') }}

    {{ HTML::script('public/assets/admintheme/pages/scripts/components-date-time-pickers.min.js') }}


    {{ HTML::script('public/assets/scripts/common.js') }}

    {{ HTML::script('public/assets/scripts/md5.js') }}

    @yield('custom_js')
@stop


