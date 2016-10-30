@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.REPORT'). ' ' .Config::get('constant.RPT_KPI_RESULT'))
@section('section')
@include('alerts.errors')
@include('widgets.timeOption.sortByTime', array('year' => $year,
                                                'link' => "reloadPageWithParam('{{action('ExportExcelController@reportDataKPIResult')}}'
                                                                         , 'reportDataKPIResult'
                                                                         , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/slMonthByToMonth/slYearByFromMonth/slYearByToMonth/slCompany/slArea/slPositionHide/slGroupHide/slEmpHide/slGoalHide')"))
<?php
$currentCompanyId = Session::get('scompany_id');
$currentAreaId = Session::get('sarea_id');
$accessLevel = Session::get('saccess_level');
?>
    <div id="wrapper">
        <div class="row margin-form">
            <div class="col-md-12 col-xs-12 ">
                <div class="col-md-3 col-xs-12">
                    <b class="font-13 marg-top-title">Phòng/Đài/MBF HCM</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12 margin-left-015" id="divSlCompany">
                        <select class="form-control margin-top-8 width-104" id="slCompany"
                                onchange="changeShowByCompany();"
                                onchange="reloadPageWithParam('{{action('ExportExcelController@reportDataKPIResult')}}'
                                        , 'reportDataKPIResult'
                                        , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                        'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                        'slCompany/slArea/slPositionHide/slGroupHide/slEmpHide/slGoalHide')">
                            <?php
                            if($accessLevel > 1){
                            foreach($company as $row){
                                if($row->id == $currentCompanyId){?>
                                <option value="<?php echo $row->company_code ?>">
                                    <?php echo $row->company_name ?>
                                </option>
                            <?php }}} else {
                                foreach($company as $row){?>
                                <option value="<?php echo $row->company_code ?>">
                                    <?php echo $row->company_name ?>
                                </option>
                            <?php }}?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <b class="font-13 marg-top-title">Tổ/Quận/Huyện</b><br>
                    <div class="col-md-12 btnChoose margin-top-1-12 margin-left-015" id="cbArea">
                        <select class="form-control margin-top-8 width-104" id="slArea" onchange="changeShowByArea();"
                                onchange="reloadPageWithParam('{{action('ExportExcelController@reportDataKPIResult')}}'
                                        , 'reportDataKPIResult'
                                        , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                        'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                        'slCompany/slArea/slPositionHide/slGroupHide/slEmpHide/slGoalHide')">
                            <?php
                            if(isset($_COOKIE['areaChoose']) && $_COOKIE['areaChoose'] != '') {
                            if(isset($area)){
                            foreach($area as $a){
                            if($a->area_code == $_COOKIE['areaChoose']){ ?>
                            <option value="<?php echo $_COOKIE['areaChoose']?>"><?php echo $a->area_name ?></option>
                            <?php
                            break;
                            }
                            }?>
                            <?php  }

                            } else {?>
                            <option value="No">Chọn một khu vực</option>
                            <?php    }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-xs-12" id="divPos">
                    <b class="font-13 marg-top-title">Chức danh</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12 ">
                        <select multiple="multiple" class="margin-top-8 width-104" id="slPosition"
                                onchange="changeShowByPosition();">
                            <?php foreach($positions as $row){?>
                            <option value="<?php echo $row->position_code ?>"selected>
                                <?php echo $row->position_name ?>
                            </option>
                            <?php }?>
                        </select>
                        <input  type="hidden" id="slPositionHide" value="1"
                                onchange="reloadPageWithParam('{{action('ExportExcelController@reportDataKPIResult')}}'
                                        , 'reportDataKPIResult'
                                        , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                        'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                        'slCompany/slArea/slPositionHide/slGroupHide/slEmpHide/slGoalHide')">

                    </div>
                </div>
            </div>

            <div class="col-md-12 col-xs-12 ">
                <div class="col-md-3 col-xs-12">
                    <b class="font-13 marg-top-title">Nhóm</b><br>

                    <div class="col-md-12 col-xs-6 btnChoose margin-top-1-12 margin-left-015" ID="cbEmp">
                        <select multiple="multiple" class="margin-top-8 classEmpCombo js-example-basic-single width-104"
                                id="slGroup" onchange="changeShowByGroup();">
                            <?php foreach($group as $row){?>
                            <option value="<?php echo $row->group_code ?>"selected>
                                <?php echo $row->group_name ?>
                            </option>
                            <?php }?>
                        </select>
                        <input type="hidden" id="slGroupHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportDataKPIResult')}}'
                                       , 'reportDataKPIResult'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slArea/slPositionHide/slGroupHide/slEmpHide/slGoalHide')">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <b class="font-13 marg-top-title">Nhân viên</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12 margin-left-015">
                        <select multiple="multiple"
                                class="form-control width-104 margin-top-8 classEmpCombo js-example-basic-single"
                                id="slEmp" onchange="changeShowByEmp();">
                            <option value="1" selected><b>Tất cả</b></option>
                        </select>
                        <input type="hidden" id="slEmpHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportDataKPIResult')}}'
                                       , 'reportDataKPIResult'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slArea/slPositionHide/slGroupHide/slEmpHide/slGoalHide')">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12" id="divGoal">
                    <b class="font-13 marg-top-title">Mục tiêu</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12" ID="cbGoal">
                        <select multiple="multiple" class="width-104 margin-top-8" id="slGoal"
                                onchange="changeShowByGoal();">
                            <?php foreach ($gOnes as $gOne){?>
                            <optgroup value="<?php echo $gOne->goal_code; ?>" label="
                            <?php echo $gOne->goal_name; ?>" class="optiongroup">
                                <?php
                                foreach ($gTwos as $gTwo) {
                                if($gTwo->parent_id == $gOne->id) {?>
                                <option value="<?php echo $gTwo->goal_code; ?>" selected>
                                    <?php echo $gTwo->goal_name; ?>
                                </option>
                                <?php }
                                }?>
                            </optgroup>
                            <?php }?>
                        </select>
                        <input type="hidden" id="slGoalHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportDataKPIResult')}}'
                                       , 'reportDataKPIResult'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slArea/slPositionHide/slGroupHide/slEmpHide/slGoalHide')">
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <span class="pull-right pading-right-22">
                    <button id="btnExport" class="btn btn-primary pull-right margin-left-15"
                            onclick="reloadPageWithParam('{{action('ExportExcelController@reportDataKPIResult')}}'
                                    , 'reportDataKPIResult'
                                    , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                    'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                    'slCompany/slArea/slPositionHide/slGroupHide/slEmpHide/slGoalHide')"><i
                                class="fa fa-sign-out"></i> Xuất Excel
                    </button>
                </span>
            </div>
            <div class="col-md-12 col-xs-12">
                <ul id="myTab" class="nav nav-tabs ">
                    <li class="active"><a href="#viewData" data-toggle="tab">Xem dữ liệu</a></li>
                    <li><a href="#viewChart" data-toggle="tab">Xem biểu dồ</a></li>
                </ul>
                <div id="myTabContent" class="tab-content">
                    <div class="tab-pane fade in active" id="viewData">
                        <div class="col-sm-12" id="tblResult"></div>
                        <div class="col-sm-12 color-red" id="txtResult" hidden>Không tìm thấy kết quả!</div>
                        <div class="col-sm-12 color-red" id="txtResultCountMonth" hidden>Không thể xem dữ liệu của nhiều
                            tháng hoặc dữ liệu của nhiều chức danh. Vui lòng xuất excel để xem.
                        </div>
                    </div>

                    <div class="tab-pane fade" id="viewChart">
                        @include('widgets.chartOption.chartOption')
                        <div class="col-md-12 col-xs-12 margin-top-40" id="chartAjax">
                        </div>
                        <div class="col-md-12 col-xs-12 margin-top-40" id="idTable" hidden="hidden"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!------------------------------------------------------------------------------------------------------->
    <!--
        * Popup to show message
        -->
    <div class="modal fade gray-darker " id="warningWhenShowData" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content ">
                <div class="modal-body">
                    <a type="button" data-dismiss="modal"
                       style=" margin-bottom: 5%;margin-right: 1%; z-index: 20; cursor: pointer;">
                        <span class="glyphicon glyphicon-remove pull-right white" aria-hidden="true"></span>
                    </a>

                    <div class="row" id="content"></div>
                </div>
                <div class="modal-footer" id="btnExport">
                    <button type="button" class="btn btn-default btn-primary" data-dismiss="modal"
                            id="btnExportInformation">Đồng ý
                    </button>
                    <button type="button" class="btn btn-default btn-primary btnExit" data-dismiss="modal" id="btnExit">
                        Thoát
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!------------------------------------------------------------------------------------------------------->
    {{ HTML::script('resources/js/Chart.js') }}
    <!-- <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet"/>
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script> -->
    {{ HTML::style('public/assets/stylesheets/select2.min.css') }}
    {{ HTML::script('public/assets/scripts/select2.min.js') }}
    {{ HTML::style('public/assets/stylesheets/implement.css') }}
    {{ HTML::style('public/assets/stylesheets/multiple-select.css') }}
    {{ HTML::script('public/assets/scripts/multiple-select.js') }}
    {{ HTML::script('public/assets/scripts/highcharts.js') }}
    {{ HTML::script('public/assets/scripts/data.js') }}
    {{ HTML::script('public/assets/scripts/exporting.js') }}

    <script type="text/javascript">
        //paint charts
        function paintCharts(dataEmpParentChild, dataEmp, dataBM){
            var isCheckRdColumnChart = document.getElementById("rdColumnChart").checked;
            var isCheckGTI = document.getElementById("rdGTI").checked;
            var isCheckGBMIP = document.getElementById("rdGBMIP").checked;
            var isCheckRadarChart = document.getElementById("rdRadarChart").checked;
            var isCheckMIP = document.getElementById("rdMIP").checked;
            var myNode = document.getElementById("chartAjax");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }
            var html = '';

           if(isCheckRdColumnChart){
                for (var emp = 0; emp < dataEmpParentChild.length; emp++) {
                    if (emp == (dataEmpParentChild.length - 1)) {
                        html += '<div class="col-sm-12">' +
                                "<div id='" + dataEmpParentChild[emp]['code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    } else {
                        html += '<div class="col-sm-12 marg-bottom-1-100">' +
                                "<div id='" + dataEmpParentChild[emp]['code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    }
                }

            } else {
                for (var emp = 0; emp < dataEmpParentChild.length; emp++) {
                    html += '<div class="col-sm-12">';
                    html += '<div class="panel panel-default">';
                    html += '<div class="panel-heading">';
                    html += '<h3 class="panel-title">' + dataEmpParentChild[emp]['name'] + '</h3>';
                    html += '</div>';
                    html += '<div class="panel-body">';
                    html += "<canvas id='" + dataEmpParentChild[emp]['code'] + "' height='220' width='350'></canvas>";
                    html += '</div></div></div>';
                }
            }

            $('#chartAjax').append(html);

            //paint charts
            if (isCheckGTI) {
                //view by goal-target-implement dataChart
                for (var emp = 0; emp < dataEmpParentChild.length; emp++) {
                    var arrayChartColumn = [];
                    var goalChild = '';
                    var tvCharts = '';
                    var iCharts = '';
                    var goal = dataEmpParentChild[emp]['child'];
                    for (var t = 0; t < goal.length; t++) {
                        var arrayChild = [];
                        arrayChild.push(goal[t]['goal']['goal_name']);
                        goalChild += '"' + goal[t]['goal']['goal_name'] + '",';
                        var i = formatNumber(goal[t]['i'], 3);
                        var tv = formatNumber(goal[t]['tv'], 3);

                        arrayChild.push(tv);
                        arrayChild.push(i);
                        arrayChartColumn.push(arrayChild);
                        tvCharts += '"' + tv + '", ';
                        iCharts += '"' + i + '", ';
                    }
                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(goalChild, tvCharts, iCharts, dataEmpParentChild[emp]['code'], 'Kế hoạch', 'Thực hiện');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, dataEmpParentChild[emp]['code'], 'Kế hoạch', 'Thực hiện', dataEmpParentChild[emp]['name']);
                    } else {
                        paintLineChartTVIByMonthGoal(goalChild, tvCharts, iCharts, dataEmpParentChild[emp]['code'], 'Kế hoạch', 'Thực hiện');
                    }
                }

            } else if (isCheckGBMIP) {
                // view by goal-implement point
                for (var emp = 0; emp < dataEmpParentChild.length; emp++) {
                    var arrayChartColumn = [];
                    var goalChild = '';
                    var ipCharts = '';
                    var bmCharts = '';
                    var goal = dataEmpParentChild[emp]['child'];
                    for (var t = 0; t < goal.length; t++) {
                        var arrayChild = [];
                        arrayChild.push(goal[t]['goal']['goal_name']);
                        goalChild += '"' + goal[t]['goal']['goal_name'] + '",';
                        var ip = formatNumber(goal[t]['ip'], 3);
                        var bm = formatNumber(goal[t]['goal']['benchmark'], 3);

                        arrayChild.push(ip);
                        arrayChild.push(bm);
                        arrayChartColumn.push(arrayChild);
                        ipCharts += '"' + ip + '", ';
                        bmCharts += '"' + bm + '", ';
                    }
                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, dataEmpParentChild[emp]['code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, dataEmpParentChild[emp]['code'], 'Điểm thực hiện', 'Điểm chuẩn', dataEmpParentChild[emp]['name']);
                    } else {
                        paintLineChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, dataEmpParentChild[emp]['code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    }
                }
            } else if(isCheckMIP){
                for (var emp = 0; emp < dataEmpParentChild.length; emp++) {
                    var arrayChartColumn = [];
                    var goalChild = '';
                    var ipCharts = '';
                    var bmCharts = '';
                    var time = dataEmpParentChild[emp]['time'];
                     var arrayChild = [];
                     arrayChild.push('Tháng_'+time[0]['month']);
                     goalChild += '"Tháng_' + time[0]['month'] + '",';
                    var goal = dataEmpParentChild[emp]['child'];
                    var ip = 0;
                    var bm = 0;
                    for (var t = 0; t < goal.length; t++) {
                        ip += goal[t]['ip'];
                        bm += goal[t]['goal']['benchmark'];
                    }
                    ip = formatNumber(ip, 3);
                    bm = formatNumber(bm, 3);
                    arrayChild.push(ip);
                    arrayChild.push(bm);
                    arrayChartColumn.push(arrayChild);
                    ipCharts += '"' + ip + '", ';
                    bmCharts += '"' + bm + '", ';
                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, dataEmpParentChild[emp]['code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, dataEmpParentChild[emp]['code'], 'Điểm thực hiện', 'Điểm chuẩn', dataEmpParentChild[emp]['name']);
                    } else {
                        paintLineChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, dataEmpParentChild[emp]['code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    }
                }
            }
        }

        //call ajax to load pages to get implement_point of company
        function showData() {
            $('#btnExport').prop("disabled", true);
            var myNode = document.getElementById("chartAjax");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }

            //get value of 9 variable
            var month = parseInt($('#slMonthByMonth').val());
            var year = parseInt($('#slYearByMonth').val());
            var fromMonth = parseInt($('#slMonthByFromMonth').val());
            var fromYear = parseInt($('#slYearByFromMonth').val());
            var toMonth = parseInt($('#slMonthByToMonth').val());
            var toYear = parseInt($('#slYearByToMonth').val());
            var company = $('#slCompany').val();
            var area = getCookie("areaChoose").toString().trim();
            var position = $('#slPosition').val();
            var group = $('#slGroup').val();
            var emp = $('#slEmp').val();
            var goal = $('#slGoal').val();

            var isCheckMonth = document.getElementById("rdSelectByFromMonth").checked;
            var strPosition = '';
            if (position != null) {
                for (var p = 0; p < position.length; p++) {
                    var arrName = position[p].split("/");
                    var nameSuccess = position[p];
                    if (arrName.length > 1) {
                        nameSuccess = '';
                        var arrName = position[p].split("");
                        for (var n = 0; n < arrName.length; n++) {
                            if (arrName[n] == '/') {
                                nameSuccess += '*';
                            } else {
                                nameSuccess += arrName[n];
                            }
                        }
                    }

                    if (p == (position.length - 1)) {
                        strPosition += nameSuccess;
                    } else {
                        strPosition += nameSuccess + ',';
                    }
                }
            }

            var elemP = document.getElementById("slPositionHide");
            elemP.value = strPosition;

            var elemP = document.getElementById("slGroupHide");
            elemP.value = group;

            var elemP = document.getElementById("slEmpHide");
            elemP.value = emp;

            var elemP = document.getElementById("slGoalHide");
            elemP.value = goal;
            //radio button tháng is checked
            if (isCheckMonth) {
                var countMonth = ((toYear - fromYear) * 12 + (toMonth - fromMonth + 1));
                if ((toYear < fromYear) || ((toYear == fromYear) && (toMonth < fromMonth))) {
                    $('#tblResult').hide();
                    $('#txtResult').hide();
                    $('#txtResultCountMonth').hide();
                    $('#warningWhenShowData').modal('show');
                    $('.btnExit').hide();
                    $('#content').html("");
                    var html = '<div class=" col-lg-11 font-size-base" style=" margin-left: 5%;padding-top: 7px;">' +
                            '<span style="padding-left: 5px; "><b>Vui lòng chọn thời gian bắt đầu nhỏ hơn thời gian kết thúc.</b></span></div>';
                    $('#content').append(html);
                    $('#btnExportInformation').on('click', function (e) {
                        $('#warningImportGoalArea').modal('hide');
                    });
                } else if(fromYear == -1 || toYear == -1){
                    $('#txtResultCountMonth').hide();
                    $('#txtResult').show();
                    $('#tblResult').hide();
                    $('#btnExport').prop("disabled", true);
                } else if ((countMonth > 1) || (position != null && (position.length) > 1)) {
                    if (area == 'No') {
                        $('#btnExport').prop("disabled", true);
                        $('#tblResult').hide();
                        $('#txtResult').show();
                        $('#txtResultCountMonth').hide();
                    } else {
                        $('#btnExport').prop("disabled", false);
                        // export if countMonth greater than 12
                        $('#tblResult').hide();
                        $('#txtResult').hide();
                        $('#txtResultCountMonth').show();
                    }
                } else {
                    $('#txtResultCountMonth').hide();
                    $('#txtResult').hide();
                    $('#tblResult').show();

                    var node = document.getElementById("tblResult");
                    while (node.firstChild) {
                        node.removeChild(node.firstChild);
                    }
                    //get data
                    $.get("getDataByTimeForKPIResult", {
                        fromMonth: fromMonth, fromYear: fromYear, toMonth: toMonth, toYear: toYear,
                        company: company, area: area, group: group,
                        position: position, emp: emp, goal: goal
                    }, function (result) {

                        if (result == false) {
                            $('#tblResult').hide();
                            $('#txtResult').show();
                            $('#btnExport').prop("disabled", true);
                        } else {
                            $('#txtResult').hide();
                            $('#btnExport').prop("disabled", false);

                            var dataEmp = result[0];
                            var dataEmpParentChild = result[1];
                            var dataBM = result[2];

                            var myNode = document.getElementById("tblResult");
                            while (myNode.firstChild) {
                                myNode.removeChild(myNode.firstChild);
                            }
                            var htmlEmp = '';
                            for (var ePC = 0; ePC < dataEmpParentChild.length; ePC++) {
                                var IP = 0;
                                var BM = 0;
                                var arrayParent = dataEmpParentChild[ePC]['goal_parent'];
                                htmlEmp += '<table class="table-common">' +
                                        '<thead>' +
                                        '<tr>' +
                                        '<th colspan="8"><b>' + dataEmpParentChild[ePC]['name'] + '</b></th>' +
                                        '</tr>' +
                                        '<tr>' +
                                        '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                                        '<th class="col-md-3 col-sm-3 order-column">Tiên mục tiêu</th>' +
                                        '<th class="col-md-1 col-sm-1 order-column">Loại mục tiêu</th>' +
                                        '<th class="col-md-2 col-sm-2 order-column">Kế hoạch</th>' +
                                        '<th class="col-md-1 col-sm-1 order-column">Trọng số</th>' +
                                        '<th class="col-md-1 col-sm-1 order-column">Điểm chuẩn</th>' +
                                        '<th class="col-md-2 col-sm-2 order-column">Thực hiện</th>' +
                                        '<th class="col-md-1 col-sm-1 order-column">Điểm thực hiện</th>' +
                                        '</tr>' +
                                        '</thead>' +
                                        '<tbody>';
                                for (var aPa = 0; aPa < arrayParent.length; aPa++) {
                                    var index = 0;
                                    var html = '';
                                    var IPP = 0;
                                    var BMM = 0;
                                    for (var dE = 0; dE < dataEmp.length; dE++) {
                                        if ((dataEmp[dE]['parent_id'] == arrayParent[aPa]['id']) &&
                                                (dataEmp[dE]['code'] == dataEmpParentChild[ePC]['code'])) {
                                            var ip = formatNumber(dataEmp[dE]['implementPoint'], 3);
                                            if (ip > 0 || dataEmp[dE]['targetValue'] > 0 || dataEmp[dE]['important_level'] > 0 ||
                                                    dataEmp[dE]['benchmark'] > 0 || dataEmp[dE]['implement'] > 0) {
                                                index++;
                                                if (index % 2 == 0) {
                                                    html += '<tr class="background-color-smoke">';
                                                } else {
                                                    html += '<tr>';
                                                }
                                                html += '<td class="order-column">' + index + '</td>' +
                                                        '<td>' + dataEmp[dE]['goal_name'] + '</td>' +
                                                        '<td class="order-column">' + renderGoalType(dataEmp[dE]['goal_type']) + '</td>' +
                                                        '<td class="text-align-right" title="Kế hoạch của tháng">' + formatBigNumber(dataEmp[dE]['targetValue']) + '</td>' +
                                                        '<td class="text-align-right" title="Trọng số của mục tiêu">' + dataEmp[dE]['important_level'] + '</td>' +
                                                        '<td class="text-align-right" title="Điểm chuẩn của mục tiêu">' + formatNumber(dataEmp[dE]['benchmark'], 3) + '</td>' +
                                                        '<td class="text-align-right" title="Thực hiện của tháng">' + formatBigNumber(dataEmp[dE]['implement']) + '</td>' +
                                                        '<td class="text-align-right" title="Điểm thực hiện của tháng">' + (ip) + '</td>' +
                                                        '</tr>';
                                                IP += ip;
                                                IPP += ip;
                                                BM += dataEmp[dE]['benchmark'];
                                                BMM += dataEmp[dE]['benchmark'];
                                            }
                                        }
                                    }

                                    if (html != '') {
                                        htmlEmp += '<tr class="color-parent">' +
                                                '<td colspan="3" title="Tên mục tiêu cha">' + arrayParent[aPa]['goal_name'] + '</td>' +
                                                '<td></td>'+
                                                '<td></td>'+
                                                '<td class=" text-align-right" title="Tổng điểm thực hiện của mục tiêu cha">' + formatNumber(BMM, 3) + '</td>' +
                                                '<td></td>'+
                                                '<td class=" text-align-right" title="Tổng điểm thực hiện của mục tiêu cha">' + formatNumber(IPP, 3) + '</td>' +
                                                '</tr>';
                                        htmlEmp += html;
                                    }
                                }

                                htmlEmp += '<tr>' +
                                        '<td colspan="3" class="order-column"><b>TỔNG CỘNG</b></td>' +
                                        '<td></td>'+
                                        '<td></td>'+
                                        '<td class="text-align-right" title="Tổng điểm chuẩn"><b>' + formatNumber(BM, 3) + '</b></td>' +
                                         '<td></td>'+
                                        '<td class="text-align-right" title="Tổng điểm thực hiện"><b>' + formatNumber(IP, 3) + '</b></td>' +
                                        '</tr>' + '</tbody>' +
                                        '</table>'
                            }
                            $('#tblResult').append(htmlEmp);

                            //paint chart for each emp
                            paintCharts(dataEmpParentChild, dataEmp, dataBM );

                        }
                    });
                }
            } else {
                if (year == -1){
                    $('#btnExport').prop("disabled", true);
                    $('#tblResult').hide();
                    $('#txtResult').show();
                    $('#txtResultCountMonth').hide();
                }else if((position != null) && ((position.length) > 1)) {
                    if (area == 'No') {
                        $('#btnExport').prop("disabled", true);
                        $('#tblResult').hide();
                        $('#txtResult').show();
                        $('#txtResultCountMonth').hide();
                    } else {
                        $('#btnExport').prop("disabled", false);
                        // export if countMonth greater than 12
                        $('#tblResult').hide();
                        $('#txtResult').hide();
                        $('#txtResultCountMonth').show();
                    }
                } else {
                    $('#txtResultCountMonth').hide();
                    $('#tblResult').show();
                    // get data of time
                    var node = document.getElementById("tblResult");
                    while (node.firstChild) {
                        node.removeChild(node.firstChild);
                    }
                    //get data
                    $.get("getDataByTimeForKPIResult", {
                        month: month, year: year, company: company, area: area, group: group,
                        position: position, emp: emp, goal: goal
                    }, function (result) {
                        if (result == false) {
                            $('#tblResult').hide();
                            $('#txtResult').show();
                        } else {
                            var myNode = document.getElementById("tblResult");
                            while (myNode.firstChild) {
                                myNode.removeChild(myNode.firstChild);
                            }
                            $('#txtResult').hide();
                            $('#btnExport').prop("disabled", false);
                            var dataEmp = result[0];
                            var dataEmpParentChild = result[1];//id-goal_name-goal_code
                            var dataBM  = result[2];//id-goal_name-goal_code
                            var htmlEmp = '';
                            for (var ePC = 0; ePC < dataEmpParentChild.length; ePC++) {
                                htmlEmp += '<table class="table-common">' +
                                        '<thead>' +
                                        '<tr>' +
                                        '<th colspan="8"><b>' + dataEmpParentChild[ePC]['name'] + '</b></th>' +
                                        '</tr>' +
                                        '<tr>' +
                                        '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                                        '<th class="col-md-3 col-sm-3 order-column">Tiên mục tiêu</th>' +
                                        '<th class="col-md-1 col-sm-1 order-column">Loại mục tiêu</th>' +
                                        '<th class="col-md-2 col-sm-2 order-column">Kế hoạch</th>' +
                                        '<th class="col-md-1 col-sm-1 order-column">Trọng số</th>' +
                                        '<th class="col-md-1 col-sm-1 order-column">Điểm chuẩn</th>' +
                                        '<th class="col-md-2 col-sm-2 order-column">Thực hiện</th>' +
                                        '<th class="col-md-1 col-sm-1 order-column">Điểm thực hiện</th>' +
                                        '</tr>' +
                                        '</thead>' +
                                        '<tbody>';

                                var arrayParent = dataEmpParentChild[ePC]['goal'];
                                var totalIP = 0;
                                var totalBM = 0;
                                for (var aPa = 0; aPa < arrayParent.length; aPa++) {
                                    var dataParent = arrayParent[aPa]['parent'];
                                    var dataChild = arrayParent[aPa]['child'];
                                    var htmlChild = '';
                                    var sumIP = 0;

                                    for(var z = 0; z < dataChild.length; z++){
                                        if ((z+1) % 2 == 0) {
                                            htmlChild += '<tr class="background-color-smoke">';
                                        } else {
                                            htmlChild += '<tr>';
                                        }
                                        htmlChild += '<td class="order-column">' + (z+1) + '</td>' +
                                                '<td>' + dataChild[z]['goal']['goal_name'] + '</td>' +
                                                '<td class="order-column">' + renderGoalType(dataChild[z]['goal']['goal_type']) + '</td>' +
                                                '<td class="text-align-right" title="Kế hoạch của tháng">' + formatBigNumber(dataChild[z]['tv']) + '</td>' +
                                                '<td class="text-align-right" title="Trọng số của mục tiêu">' + dataChild[z]['goal']['important_level'] + '</td>' +
                                                '<td class="text-align-right" title="Điểm chuẩn của mục tiêu">' + formatNumber(dataChild[z]['goal']['benchmark'], 3) + '</td>' +
                                                '<td class="text-align-right" title="Thực hiện của tháng">' + formatBigNumber(dataChild[z]['i']) + '</td>' +
                                                '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(dataChild[z]['ip'], 3) + '</td>' +
                                                '</tr>';

                                        sumIP += dataChild[z]['ip'];
                                    }

                                    if(htmlChild != ''){
                                        htmlEmp +=  '<tr class="color-parent">' +
                                                    '<td colspan="3">' + dataParent['goal_name'] + '</td>' +
                                                    '<td></td>' +
                                                    '<td></td>' +
                                                    '<td class="text-align-right" title="Điểm chuẩn của mục tiêu">' + formatNumber(dataParent['benchmark'], 3) + '</td>' +
                                                    '<td></td>' +
                                                    '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(sumIP, 3) + '</td>' +
                                                    '</tr>';
                                        htmlEmp += htmlChild;
                                        totalIP += formatNumber(sumIP, 3);
                                        totalBM += dataParent['benchmark'];
                                    }
                                }

                                htmlEmp += '<tr>' +
                                        '<td colspan="3" class="order-column"><b>TỔNG CỘNG</b></td>' +
                                        '<td></td>' +
                                        '<td></td>' +
                                        '<td class="text-align-right" title="Tổng điểm chuẩn"><b>' + formatNumber(totalBM, 3) + '</b></td>' +
                                        '<td></td>' +
                                        '<td class="text-align-right" title="Tổng điểm thực hiện"><b>' + formatNumber(totalIP, 3) + '</b></td>' +
                                        '</tr>' + '</tbody>' +
                                        '</table>'
                            }

                            $('#tblResult').append(htmlEmp);
                            //paint charts
                            paintCharts(dataEmpParentChild, dataEmp, dataBM);

                        }
                    });
                }
            }
        }

        function loadDataChangeEmpArea() {
            //change company: change area+ change emp
            var company = $('#slCompany').val();
            var areaChoose = getCookie("areaChoose").toString();
            areaChoose = areaChoose.trim();

            $.get("getDataEmpAreaForRKPI", {company: company}, function (result) {
                //delete all child in div have id= cbArea - cbEmp
                var nodeA = document.getElementById("slArea");
                while (nodeA.firstChild) {
                    nodeA.removeChild(nodeA.firstChild);
                }

                var nodeE = document.getElementById("slEmp");
                while (nodeE.firstChild) {
                    nodeE.removeChild(nodeE.firstChild);
                }

                if (result != false) {
                    var dataArea = result;
                    var currentArea = "<?php echo $currentAreaId ?>";
                    var accessLevel = "<?php echo $accessLevel ?>";
                    var html = '<option value="No">Chọn một khu vực</option>';
                    if(areaChoose == '' || areaChoose == null){
                        if(accessLevel > 2){
                            for (var a = 0; a < dataArea.length; a++) {
                                if(dataArea[a]['id'] == currentArea){
                                    html += "<option value='" + dataArea[a]['area_code'] + "'>" + dataArea[a]['area_name'] + "</option>";
                                    break;
                                }

                            }
                        } else {
                            for (var a = 0; a < dataArea.length; a++) {
                                html += "<option value='" + dataArea[a]['area_code'] + "'>" + dataArea[a]['area_name'] + "</option>";
                            }
                        }
                    } else {
                        if(accessLevel > 2){
                            for (var a = 0; a < dataArea.length; a++) {
                                if(currentArea == dataArea[a]['id']){
                                    if(dataArea[a]['area_code'] == areaChoose){
                                        html += "<option value='" + dataArea[a]['area_code'] + "' selected = '1'>" + dataArea[a]['area_name'] + "</option>";
                                    } else {
                                        html += "<option value='" + dataArea[a]['area_code'] + "'>" + dataArea[a]['area_name'] + "</option>";
                                    }
                                    break;
                                }

                            }
                        } else {
                            for (var a = 0; a < dataArea.length; a++) {
                                if(dataArea[a]['area_code'] == areaChoose){
                                    html += "<option value='" + dataArea[a]['area_code'] + "' selected = '1'>" + dataArea[a]['area_name'] + "</option>";
                                } else {
                                    html += "<option value='" + dataArea[a]['area_code'] + "'>" + dataArea[a]['area_name'] + "</option>";
                                }
                            }
                        }
                    }

                    $('#slArea').append(html);
                    loadDataChangeEmp();

                } else {
                    var html = '<option value="No">Chọn một khu vực</option>';
                    $('#slArea').append(html);
                }
            });
        }

        function loadDataChangeEmp() {
            var company = $('#slCompany').val();
            var position = $('#slPosition').val();
            var area = getCookie("areaChoose").toString().trim();
            var group = $('#slGroup').val();

            $.get("getDataEmpForRKPI", {
                company: company,
                position: position,
                area: area,
                group: group
            }, function (result) {
                var nodeE = document.getElementById("slEmp");
                while (nodeE.firstChild) {
                    nodeE.removeChild(nodeE.firstChild);
                }

                if (result != false) {
                    var dataEmp = result;
                    var html = '<option value="1" selected><b>Tất cả</b></option>';
                    for (var e = 0; e < dataEmp.length; e++) {
                        if(dataEmp[e]['id'] != 0 && dataEmp[e]['id'] != 1 && dataEmp[e]['id'] != 2 && dataEmp[e]['id'] != 3){
                            html += "<option value='" + dataEmp[e]['code'] + "'>" + dataEmp[e]['name'] + "</option>";
                        }
                    }
                    $('#slEmp').append(html);
                } else {
                    var html = '<option value="1" selected><b>Tất cả</b></option>';
                    $('#slEmp').append(html);
                }
            });
        }

        //handler when click select to year --> 9
        function changeShowByCompany() {
            loadDataChangeEmpArea();
            showData();
        }

        function changeShowByArea() {
            var area = $('#slArea').val();
            setCookie("areaChoose",area);
            loadDataChangeEmp();
            showData();
        }

        function changeShowByPosition() {
            loadDataChangeEmp();
            showData();
        }

        function changeShowByGroup() {
            loadDataChangeEmp();
            showData();
        }

        function changeShowByEmp() {
            showData();
        }

        function changeShowByGoal() {
            showData();
        }

        $('#slArea').select2();
        $('#slGoal').multipleSelect();
        $('#slEmp').select2();
        $('#slCompany').select2();
        $('#slPosition').multipleSelect();
        $('#slGroup').multipleSelect();
        setCookie("areaDetailChoose",'');

        $(document).ready(function () {
            loadDataChangeEmpArea();
            loadDataChangeEmp();
            showData();

            //edit show on device
            var width = $(window).width();
            if (width < 550) {
                $(".ms-parent").addClass("min-width-360");
                $(".select2").addClass("min-width-322");
                $("#divPos").remove("margin-left-9");
                $("#divGoal").remove("margin-left-9");
            } else {
                $(".ms-parent").remove("min-width-360");
                $(".select2").remove("min-width-322");
                $("#divPos").addClass("margin-left-9");
                $("#divGoal").addClass("margin-left-9");
            }
        });
    </script>
@stop