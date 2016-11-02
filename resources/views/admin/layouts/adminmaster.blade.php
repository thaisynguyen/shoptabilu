<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
<head>
	<meta charset="utf-8"/>
	<title>Shoptabilu</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta content="width=device-width, initial-scale=1" name="viewport"/>
	<meta content="" name="description"/>
	<meta content="" name="author"/>

	<!----------------------------------
	* Include css
	----------------------------------->

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
	<!-- END PAGE LEVEL PLUGINS -->
	<!-- BEGIN THEME GLOBAL STYLES -->
	{{ HTML::style('public/assets/admintheme/global/css/components.min.css') }}
	{{ HTML::style('public/assets/admintheme/global/css/plugins.min.css') }}

	<!-- END THEME GLOBAL STYLES -->
	<!-- BEGIN THEME LAYOUT STYLES -->
	{{ HTML::style('public/assets/admintheme/layouts/layout/css/layout.min.css') }}
	{{ HTML::style('public/assets/admintheme/layouts/layout/css/themes/darkblue.min.css') }}

	{{ HTML::style('public/assets/admintheme/layouts/layout/css/custom.css') }}
	<!-- END THEME LAYOUT STYLES -->


    <!----------------------------------
	* Include js
	----------------------------------->


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
			<!-- {{ HTML::script('public/assets/admintheme/global/plugins/jqvmap/jqvmap/jquery.vmap.js') }} -->
	<!-- {{ HTML::script('public/assets/admintheme/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.russia.js') }} -->
	<!-- {{ HTML::script('public/assets/admintheme/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.world.js') }} -->
	<!-- {{ HTML::script('public/assets/admintheme/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.europe.js') }} -->
	<!-- {{ HTML::script('public/assets/admintheme/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.germany.js') }} -->
	<!-- {{ HTML::script('public/assets/admintheme/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.usa.js') }} -->
	<!-- {{ HTML::script('public/assets/admintheme/global/plugins/jqvmap/jqvmap/data/jquery.vmap.sampledata.js') }} -->


	<!-- END PAGE LEVEL PLUGINS -->
	<!-- BEGIN THEME GLOBAL SCRIPTS -->
	{{ HTML::script('public/assets/admintheme/global/scripts/app.min.js') }}
	{{ HTML::script('public/assets/admintheme/pages/scripts/ui-bootbox.min.js') }}
	{{ HTML::script('public/assets/admintheme/pages/scripts/table-datatables-editable.min.js') }}
	{{ HTML::script('public/assets/admintheme/pages/scripts/dashboard.min.js') }}
	{{ HTML::script('public/assets/admintheme/layouts/layout/scripts/layout.min.js') }}
	{{ HTML::script('public/assets/admintheme/layouts/layout/scripts/demo.min.js') }}
	{{ HTML::script('public/assets/admintheme/layouts/global/scripts/quick-sidebar.min.js') }}
	{{ HTML::script('public/assets/scripts/bootstrap-notify.js') }}
	{{ HTML::script('public/assets/scripts/bootstrap-notify.min.js') }}


    {{ HTML::script('public/assets/scripts/common.js') }}


    <!----------------------------------
	* Include js for datepicker
	----------------------------------->


</head>
<body>
	@yield('body')


	{{ HTML::script('public/assets/scripts/md5.js') }}
</body>
</html>