@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.REPORT'). ' ' .Config::get('constant.RPT_IMPLEMENT_BY_EMPLOYEE'))
@section('section')
@include('widgets.timeOption.sortByTime', array('year' => $year,
                                                'link' => "reloadPageWithParam('{{action('ExportExcelController@reportEmpByTimes')}}'
                                                                 , 'reportEmpByTimes'
                                                                 , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/slMonthByToMonth/slYearByFromMonth/slYearByToMonth/slCompany/slAreaHide/slPositionHide/slGroupHide/empHide/goalHide/codeChoose')"))

<?php
$currentCompanyId = Session::get('scompany_id');
$currentAreaId = Session::get('sarea_id');
$accessLevel = Session::get('saccess_level');
?>
<div id="loader" class="se-pre-con"></div>
<div id="wrapper">
        <div class="row margin-form">
            <div class="col-md-12 col-xs-12">
                <div class="col-md-3 col-xs-12">
                    <b class="font-13 marg-top-title">Phòng/Đài/MBF HCM</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12 margin-left-015" id="divSlCompany">
                        <select class="form-control margin-top-8 width-104" id="slCompany"
                                onchange="changeShowByCompany();"
                                onchange="reloadPageWithParam('{{action('ExportExcelController@reportEmpByTimes')}}'
                                        , 'reportEmpByTimes'
                                        , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                        'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                        'slCompany/slAreaHide/slPositionHide/slGroupHide/empHide/goalHide/codeChoose')">
                            <?php
                            if($accessLevel > 1){
                            foreach($company as $row){
                                if($row->id == $currentCompanyId){?>
                                    <option value="<?php echo $row->company_code ?>">
                                        <?php echo $row->company_name ?>
                                    </option>
                            <?php break;}}} else {
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
                        <select multiple="multiple" class="margin-top-8 width-104" id="slArea"
                                onchange="changeShowByArea();">
                            <option value="1" selected>Tất cả</option>
                        </select>
                        <input type="hidden" id="slAreaHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportEmpByTimes')}}'
                                       , 'reportEmpByTimes'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slAreaHide/slPositionHide/slGroupHide/empHide/goalHide/codeChoose')">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12" id="divPos">
                    <b class="font-13 marg-top-title">Chức danh</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12">
                        <select multiple="multiple" class="width-104 margin-top-8" id="slPosition"
                                onchange="changeShowByPosition();">
                            <?php foreach($positions as $row){?>
                            <option value="<?php echo $row->position_code ?>" selected>
                                <?php echo $row->position_name ?>
                            </option>
                            <?php }?>
                        </select>

                        <input type="hidden" id="slPositionHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportEmpByTimes')}}'
                                       , 'reportEmpByTimes'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slAreaHide/slPositionHide/slGroupHide/empHide/goalHide/codeChoose')">
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-xs-12 ">
                <div class="col-md-3 col-xs-12">
                    <b class="font-13 marg-top-title">Nhóm</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12 margin-left-015" ID="cbEmp">
                        <select multiple="multiple"
                                class="width-104 margin-top-8 js-example-basic-single" id="slGroup"
                                onchange="changeShowByGroup();">
                            <?php foreach($group as $row){?>
                            <option value="<?php echo $row->group_code ?>" selected>
                                <?php echo $row->group_name ?>
                            </option>
                            <?php }?>
                        </select>
                        <input type="hidden" id="slGroupHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportEmpByTimes')}}'
                                       , 'reportEmpByTimes'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slAreaHide/slPositionHide/slGroupHide/empHide/goalHide/codeChoose')">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <b class="font-13 marg-top-title">Nhân viên</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12 margin-left-015">
                        <select multiple="multiple"
                                class="width-104 margin-top-8 js-example-basic-single h-emp" id="slEmp"
                                onchange="changeShowByEmp();">
                            <option value="1" selected>Tất cả</option>
                        </select>
                        <input type="hidden" id="empHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportEmpByTimes')}}'
                                       , 'reportEmpByTimes'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slAreaHide/slPositionHide/slGroupHide/empHide/goalHide/codeChoose')">
                        <input type="hidden" id="codeChoose" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportEmpByTimes')}}'
                                       , 'reportEmpByTimes'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slAreaHide/slPositionHide/slGroupHide/empHide/goalHide/codeChoose')">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12" id="divEmp">
                    <b class="font-13 marg-top-title">Mục tiêu</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12" id="cbGoal">
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
                        <input type="hidden" id="goalHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportEmpByTimes')}}'
                                       , 'reportEmpByTimes'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slAreaHide/slPositionHide/slGroupHide/empHide/goalHide/codeChoose')">
                    </div>
                </div>
            </div>

            <div class="col-md-12 col-xs-12">
                <span class="pull-right pading-right-22">
                    <button id="btnExport" class="btn btn-primary pull-right margin-left-15"
                            onclick="reloadPageWithParam('{{action('ExportExcelController@reportEmpByTimes')}}'
                                    , 'reportEmpByTimes'
                                    , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                    'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                    'slCompany/slAreaHide/slPositionHide/slGroupHide/empHide/goalHide/codeChoose')"><i
                                class="fa fa-sign-out"></i> Xuất Excel
                    </button>
                </span>
            </div>

            <div class="col-md-12 col-xs-12">
                <ul id="myTab" class="nav nav-tabs ">
                    <li class="active"><a href="#viewData" data-toggle="tab">Xem dữ liệu
                            </a></li>
                    <li><a href="#viewChart" data-toggle="tab">Xem biểu đồ</a></li>
                </ul>
                <div id="myTabContent" class="tab-content">
                    <div class="tab-pane fade in active" id="viewData">
                        <div class="col-sm-12" id="tblResult"></div>
                        <div class="col-sm-12" id="tblResultEachEmp"></div>
                        <div class="col-sm-12 color-red" id="txtResult" hidden>Không tìm thấy kết quả!</div>
                        <div class="col-sm-12 color-red" id="txtResultCountMonth" hidden>Không thể xem dữ liệu quá 12
                            tháng. Vui lòng xuất excel để xem.
                        </div>
                    </div>

                    <div class="tab-pane fade" id="viewChart">
                        @include('widgets.chartOption.chartOption')
                        @include('widgets.chartOption.showSummary')
                        <div class="col-md-12 col-xs-12 margin-top-40" id="chartAjax">
                        </div>
                        <div class="col-md-12 col-xs-12 margin-top-40" id="idTable" hidden></div>
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
                <div class="modal-footer">
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
    {{ HTML::style('public/assets/stylesheets/select2.min.css') }}
    {{ HTML::script('public/assets/scripts/select2.min.js') }}
    {{ HTML::style('public/assets/stylesheets/implement.css') }}
    {{ HTML::style('public/assets/stylesheets/multiple-select.css') }}
    {{ HTML::script('public/assets/scripts/multiple-select.js') }}
    {{ HTML::script('public/assets/scripts/highcharts.js') }}
    {{ HTML::script('public/assets/scripts/data.js') }}
    {{ HTML::script('public/assets/scripts/exporting.js') }}
    {{ HTML::script('public/assets/scripts/modernizr.js') }}

    <script type="text/javascript">
        //paint chart
        function paintCharts(arrayUniqueEmp, dataChart, data, isHide, dataBM){
        if(isHide == 1){
                var isCheckGTI = 0;
            } else {
                var isCheckGTI = document.getElementById("rdGTI").checked;
            }
            var isCheckRdColumnChart = document.getElementById("rdColumnChart").checked;
            var isCheckGBMIP = document.getElementById("rdGBMIP").checked;
            var isCheckRadarChart = document.getElementById("rdRadarChart").checked;
            var isCheckMIP = document.getElementById("rdMIP").checked;

            var isCheckCkbSummary = document.getElementById("ckbSummaryChart").checked;

            var myNodeTb = document.getElementById("chartAjax");
            while (myNodeTb.firstChild) {
                myNodeTb.removeChild(myNodeTb.firstChild);
            }
            var html = '';
            if(isCheckRdColumnChart){
                if(isCheckCkbSummary && isCheckGTI == 0){
                    html += '<div class="col-sm-12">' +
                            "<div id='summary' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                }
                for (var emp = 0; emp < arrayUniqueEmp.length; emp++) {
                    if (emp == (arrayUniqueEmp.length - 1)) {
                        html += '<div class="col-sm-12">' +
                                "<div id='" + arrayUniqueEmp[emp]['code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    } else {
                        html += '<div class="col-sm-12 marg-bottom-1-100">' +
                                "<div id='" + arrayUniqueEmp[emp]['code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    }
                }

            } else {
                if(isCheckCkbSummary && isCheckGTI == 0){
                    html += '<div class="col-sm-12">';
                    html += '<div class="panel panel-default">';
                    html += '<div class="panel-heading">';
                    html += '<h3 class="panel-title">Biểu Đồ Tổng Quan</h3>';
                    html += '</div>';
                    html += '<div class="panel-body">';
                    html += "<canvas id='summary' height='220' width='350'></canvas>";
                    html += '</div></div></div>';
                }
                for (var emp = 0; emp < arrayUniqueEmp.length; emp++) {
                    html += '<div class="col-sm-12">';
                    html += '<div class="panel panel-default">';
                    html += '<div class="panel-heading">';
                    html += '<h3 class="panel-title">' + arrayUniqueEmp[emp]['name'] + '</h3>';
                    html += '</div>';
                    html += '<div class="panel-body">';
                    html += "<canvas id='" + arrayUniqueEmp[emp]['code'] + "' height='220' width='350'></canvas>";
                    html += '</div></div></div>';
                }
            }

            $('#chartAjax').append(html);

            //paint summary
            if(isCheckCkbSummary && isCheckGTI == 0){
                var ipSt = '';
                var bmSt = '';
                var empSt = '';
                var arrayChartColumn = [];
                for (var emp = 0; emp < arrayUniqueEmp.length; emp++) {
                    var child = [];
                    child.push(arrayUniqueEmp[emp]['name']);
                    empSt += '"' + arrayUniqueEmp[emp]['name'] + '",';
                    var ip = 0;
                    for (var tc = 0; tc < data.length; tc++) {
                        if (data[tc]['code'] == arrayUniqueEmp[emp]['code']) {
                            ip += formatNumber(data[tc]['implementPoint'], 3);
                        }
                    }

                    var bm = 0;
                    for (var tc = 0; tc < dataBM.length; tc++) {
                        if (dataBM[tc]['position_code'] == arrayUniqueEmp[emp]['position_code'] &&
                            dataBM[tc]['area_code'] == arrayUniqueEmp[emp]['area_code']) {
                            bm = formatNumber(dataBM[tc]['bm'], 3);
                            break;
                        }
                    }

                    child.push(ip);
                    child.push(bm);
                    arrayChartColumn.push(child);
                    ipSt += '"' + ip + '", ';
                    bmSt += '"' + bm + '", ';
                }

                if(isCheckRadarChart){
                    paintChartTVIByMonthGoal(empSt, ipSt, bmSt, 'summary', 'Điểm thực hiện', 'Điểm chuẩn');
                } else if(isCheckRdColumnChart){
                    paintChartColumnIPBM(arrayChartColumn, 'summary', 'Điểm thực hiện', 'Điểm chuẩn', 'Biểu Đồ Tổng Quan');
                } else {
                    paintLineChartTVIByMonthGoal(empSt, ipSt, bmSt, 'summary', 'Điểm thực hiện', 'Điểm chuẩn');
                }
            }

            //paint charts
            if (isCheckGTI) {
                //view by goal-target-implement dataChart
                for (var emp = 0; emp < arrayUniqueEmp.length; emp++) {
                    var arrayChartColumn = [];
                    var i = '';
                    var tv = '';
                    var goalLabel = '';
                    for (var tc = 0; tc < dataChart.length; tc++) {
                        if (dataChart[tc]['code'] == arrayUniqueEmp[emp]['code']) {
                            var arrayChild = [];
                            goalLabel += '"' + dataChart[tc]['goal_name'] + '",';
                            tv += '"' + formatNumber(dataChart[tc]['targetValue'] , 3) + '", ';
                            i += '"' + formatNumber(dataChart[tc]['implement'], 3) + '", ';

                            arrayChild.push(dataChart[tc]['goal_name']);
                            arrayChild.push(formatNumber(dataChart[tc]['targetValue'] , 3));
                            arrayChild.push(formatNumber(dataChart[tc]['implement'], 3));
                            arrayChartColumn.push(arrayChild);
                        }
                    }
                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(goalLabel, tv, i, arrayUniqueEmp[emp]['code'], 'Kế hoạch', 'Thực hiện');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrayUniqueEmp[emp]['code'], 'Kế hoạch', 'Thực hiện', arrayUniqueEmp[emp]['name']);
                    } else {
                        paintLineChartTVIByMonthGoal(goalLabel, tv, i, arrayUniqueEmp[emp]['code'], 'Kế hoạch', 'Thực hiện');
                    }
                }

            } else if (isCheckGBMIP) {
                // view by goal-implement point
                for (var emp = 0; emp < arrayUniqueEmp.length; emp++) {
                    var arrayChartColumn = [];
                    var ipSt = '';
                    var bmSt = '';
                    var monthSt = '';
                    for (var tc = 0; tc < dataChart.length; tc++) {
                        if (dataChart[tc]['code'] == arrayUniqueEmp[emp]['code']) {
                            var arrayChild = [];
                            monthSt += '"' + dataChart[tc]['goal_name'] + '",';
                            ipSt += '"' + formatNumber( dataChart[tc]['implementPoint'], 3) + '", ';
                            bmSt += '"' + formatNumber( dataChart[tc]['benchmark'], 3) + '", ';

                            arrayChild.push(dataChart[tc]['goal_name']);
                            arrayChild.push(formatNumber( dataChart[tc]['implementPoint'], 3));
                            arrayChild.push(formatNumber( dataChart[tc]['benchmark'], 3));
                            arrayChartColumn.push(arrayChild);
                        }
                    }
                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(monthSt, ipSt,bmSt, arrayUniqueEmp[emp]['code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrayUniqueEmp[emp]['code'], 'Điểm thực hiện', 'Điểm chuẩn', arrayUniqueEmp[emp]['name']);
                    } else {
                        paintLineChartTVIByMonthGoal(monthSt, ipSt, bmSt, arrayUniqueEmp[emp]['code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    }
                }
            } else if (isCheckMIP) {
                // view by month-implement point
                for (var emp = 0; emp < arrayUniqueEmp.length; emp++) {
                    var arrayChartColumn = [];
                    var ipSt = '';
                    var bmSt = '';
                    var monthSt = '';
                    var time = arrayUniqueEmp[emp]['time'];
                    for(var t=0; t<time.length; t++){
                        var arrayChild = [];
                        arrayChild.push('Tháng_'+time[t]['month']);
                        monthSt += '"Tháng_' + time[t]['month'] + '",';
                        var ip = 0;
                        for (var tc = 0; tc < data.length; tc++) {
                            if (data[tc]['code'] == arrayUniqueEmp[emp]['code'] &&
                                    data[tc]['month'] == time[t]['month'] &&
                                    data[tc]['year'] == time[t]['year'] ) {
                                ip += formatNumber( data[tc]['implementPoint'] , 3);
                            }
                        }

                        var bm = 0;
                        for (var tc = 0; tc < dataBM.length; tc++) {
                            if (dataBM[tc]['position_code'] == arrayUniqueEmp[emp]['position_code'] &&
                                dataBM[tc]['area_code'] == arrayUniqueEmp[emp]['area_code']) {
                                bm = formatNumber(dataBM[tc]['bm'], 3);
                                break;
                            }
                        }

                        arrayChild.push(ip);
                        arrayChild.push(bm);
                        arrayChartColumn.push(arrayChild);
                        ipSt += '"' + ip + '", ';
                        bmSt += '"' + bm + '", ';
                    }

                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(monthSt, ipSt, bmSt, arrayUniqueEmp[emp]['code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrayUniqueEmp[emp]['code'], 'Điểm thực hiện', 'Điểm chuẩn', arrayUniqueEmp[emp]['name']);
                    } else {
                        paintLineChartTVIByMonthGoal(monthSt, ipSt, bmSt, arrayUniqueEmp[emp]['code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    }
                }
            } else {
                var myNode = document.getElementById("chartAjax");
                while (myNode.firstChild) {
                    myNode.removeChild(myNode.firstChild);
                }
            }
            stopStuffChart('loader');
        }

        //call ajax to load pages to get implement_point of company
        function showData() {
            showStuff('loader');

            var isHide = 0;
            $('#divRdGTI').show();
            $('#btnExport').prop("disabled", true);
            $('#tblResultEachEmp').hide();
            var myNode = document.getElementById("chartAjax");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }

            var myNodeTb = document.getElementById("tblResult");
            while (myNodeTb.firstChild) {
                myNodeTb.removeChild(myNodeTb.firstChild);
            }
            //get value of 9 variable
            var month = parseInt($('#slMonthByMonth').val());
            var year = parseInt($('#slYearByMonth').val());
            var fromMonth = parseInt($('#slMonthByFromMonth').val());
            var fromYear = parseInt($('#slYearByFromMonth').val());
            var toMonth = parseInt($('#slMonthByToMonth').val());
            var toYear = parseInt($('#slYearByToMonth').val());
            var company = $('#slCompany').val();
            var position = $('#slPosition').val();
            var group = $('#slGroup').val();
            var emp = $('#slEmp').val();
            var goal = $('#slGoal').val();
            var area = $('#slArea').val();

            var elem = document.getElementById("goalHide");
            elem.value = goal;

            var elem = document.getElementById("empHide");
            elem.value = emp;

            var elem = document.getElementById("slAreaHide");
            elem.value = area;

            var strPosition = '';
            if(position != null){
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
            var elem = document.getElementById("slPositionHide");
            elem.value = strPosition;

            var elem = document.getElementById("slGroupHide");
            elem.value = group;

            var isCheckMonth = document.getElementById("rdSelectByFromMonth").checked;

            //radio button tháng is checked
            if (isCheckMonth) {
                $('#divRdGTI').hide();
                isHide = 1;
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
                    stopStuff('loader');
                } else if (countMonth > 12) {
                    if (area == '' || area == null || emp == '' || emp == null || goal == '' || emp == null) {
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
                    stopStuff('loader');
                } else {
                    $('#txtResultCountMonth').hide();
                    $('#txtResult').hide();
                    $('#tblResult').show();
                    // get data of time
                    //get data
                    $.get("getDataByTimeForEmp", {
                        fromMonth: fromMonth, fromYear: fromYear, toMonth: toMonth, toYear: toYear,
                        company: company, area: area, group: group,
                        position: position, emp: emp, goal: goal
                    }, function (result) {
                        if (result == false) {
                            $('#tblResult').hide();
                            $('#txtResult').show();
                            $('#btnExport').prop("disabled", true);
                            stopStuff('loader');
                        } else {
                            $('#txtResult').hide();
                            $('#btnExport').prop("disabled", false);

                            if(fromMonth == toMonth && fromYear == toYear){
                                $('#divRdGTI').show();
                                isHide = 0;
                            }
                            var myNodeTb = document.getElementById("tblResult");
                            while (myNodeTb.firstChild) {
                                myNodeTb.removeChild(myNodeTb.firstChild);
                            }
                            var data = result[0];
                            var arrayEmpPosUnique = result[1];
                            var html = '<table class="table-common">' +
                                    '<thead>' +
                                    '<tr>' +
                                    '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                                    '<th class="col-md-2 col-sm-2">Họ và tên</th>' +
                                    '<th class="col-md-2 col-sm-2">Chức danh</th>' +
                                    '<th class="col-md-1 col-sm-1">Tổ/Quận/Huyện</th>';
                            if (fromYear < toYear) {
                                for (f = fromMonth; f < 12; f++) {
                                    html += "<th>Tháng " + f + "</th>";
                                }
                                html += "<th>Tháng 12-" + fromYear + "</th>";
                                for (t = 1; t < toMonth; t++) {
                                    html += "<th>Tháng " + t + "</th>";
                                }
                                html += "<th>Tháng" + toMonth + '-' + toYear + "</th>";
                            } else if (fromYear == toYear) {
                                for (n = fromMonth; n <= toMonth; n++) {
                                    html += "<th>Tháng " + n + "</th>";
                                }
                            }

                            html += '<th>Xuất</th>' +
                                    '</tr>' +
                                    '</thead>' +
                                    '<tbody>';

                            for (var EPU = 0; EPU < arrayEmpPosUnique.length; EPU++) {
                                if ((EPU + 1) % 2 == 0) {
                                    html += '<tr class="background-color-smoke">';
                                } else {
                                    html += '<tr>';
                                }

                                html += '<td class="order-column">' + (EPU + 1) + '</td>' +
                                        '<td>' +
                                        '<a onclick="viewEachEmpMultiMonth(\'' + fromMonth + '\', \'' + toMonth + '\', \'' + fromYear + '\', ' +
                                        '\'' + toYear + '\', \'' + company + '\', \'' + arrayEmpPosUnique[EPU]['area_code'] + '\', ' +
                                        '\'' + arrayEmpPosUnique[EPU]['position_code'] + '\',' +
                                        ' \'' + arrayEmpPosUnique[EPU]['group_code'] + '\', \'' + arrayEmpPosUnique[EPU]['code'] + '\',' +
                                        ' \'' + arrayEmpPosUnique[EPU]['name'] + '\');"' +
                                        ' role="button" title="Xem chi tiết">' + arrayEmpPosUnique[EPU]['name'] +
                                        '</a>' +
                                        '</td>' +
                                        '<td>' + arrayEmpPosUnique[EPU]['position_name'] + '</td>' +
                                        '<td>' + arrayEmpPosUnique[EPU]['area_name'] + '</td>';

                                if (fromYear < toYear) {
                                    for (var f = fromMonth; f <= 12; f++) {
                                        var IP = 0;
                                        for (var d = 0; d < data.length; d++) {
                                            if ((data[d]['month'] == f) &&
                                                    (data[d]['year'] == fromYear) &&
                                                    (data[d]['position_code'] == arrayEmpPosUnique[EPU]['position_code']) &&
                                                    (data[d]['code'] == arrayEmpPosUnique[EPU]['code']) &&
                                                    (data[d]['area_code'] == arrayEmpPosUnique[EPU]['area_code'])) {
                                                IP = data[d]['implementPoint'];
                                            }
                                        }
                                        html += '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(IP, 3) + '</td>';
                                    }
                                    for (var t = 1; t <= toMonth; t++) {
                                        var IP = 0;
                                        for (var d = 0; d < data.length; d++) {
                                            if ((data[d]['month'] == t) &&
                                                    (data[d]['year'] == toYear) &&
                                                    (data[d]['position_code'] == arrayEmpPosUnique[EPU]['position_code']) &&
                                                    (data[d]['code'] == arrayEmpPosUnique[EPU]['code']) &&
                                                    (data[d]['area_code'] == arrayEmpPosUnique[EPU]['area_code'])) {
                                                IP = data[d]['implementPoint'];
                                            }
                                        }
                                        html += '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(IP, 3) + '</td>';
                                    }
                                } else if (fromYear == toYear) {
                                    for (var n = fromMonth; n <= toMonth; n++) {
                                        var IP = 0;
                                        for (var d = 0; d < data.length; d++) {
                                            if ((data[d]['month'] == n) &&
                                                    (data[d]['year'] == fromYear) &&
                                                    (data[d]['position_code'] == arrayEmpPosUnique[EPU]['position_code']) &&
                                                    (data[d]['code'] == arrayEmpPosUnique[EPU]['code']) &&
                                                    (data[d]['area_code'] == arrayEmpPosUnique[EPU]['area_code'])) {
                                                IP = data[d]['implementPoint'];
                                            }
                                        }
                                        html += '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(IP, 3) + '</td>';
                                    }
                                }
                                html += '<td class="order-column" title="Chọn nhân viên để xuất dữ liệu"><input type="checkbox" class="ckbExport" onclick="clickCheckBox();" value="' + arrayEmpPosUnique[EPU]["code"] + '" ></td>' +
                                        '</tr>';
                            }

                            html += '</tbody>' +
                                    '</table>';
                            $('#tblResult').append(html);

                            //paint chart
                            var arrayUniqueEmp = result[2];
                            var dataChart = result[3];
                            var dataBM = result[4];
                            paintCharts(arrayUniqueEmp, dataChart, data, isHide, dataBM);
                        }
                    });
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
                $.get("getDataByTimeForEmp", {
                    month: month, year: year, company: company, area: area, group: group,
                    position: position, emp: emp, goal: goal
                }, function (result) {
                    if (result == false) {
                        $('#tblResult').hide();
                        $('#txtResult').show();
                        $('#btnExport').prop("disabled", true);
                        stopStuff('loader');
                    } else {
                        $('#txtResult').hide();
                        $('#btnExport').prop("disabled", false);
                        var myNodeTb = document.getElementById("tblResult");
                        while (myNodeTb.firstChild) {
                            myNodeTb.removeChild(myNodeTb.firstChild);
                        }

                        var data = result[0];
                        var html = '<table class="table-common">' +
                                '<thead>' +
                                '<tr>' +
                                '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                                '<th class="col-md-3 col-sm-3">Họ và tên</th>' +
                                '<th class="col-md-3 col-sm-3">Chức danh</th>' +
                                '<th class="col-md-2 col-sm-2">Tổ/Quận/Huyện</th>' +
                                '<th class="col-md-2 col-sm-2">Tháng ' + month + '</th>' +
                                '<th class="col-md-1 col-sm-1">Xuất</th>' +
                                '</tr>' +
                                '</thead>' +
                                '<tbody>';

                        for (var d = 0; d < data.length; d++) {
                            if ((d + 1) % 2 == 0) {
                                html += '<tr class="background-color-smoke">';
                            } else {
                                html += '<tr>';
                            }

                            html += '<td class="order-column">' + (d + 1) + '</td>' +
                                    '<td>' +
                                    '<a onclick="viewEachEmp(\'' + month + '\', \'' + year + '\', \'' + company + '\', \'' + data[d]['area_code'] + '\', ' +
                                    '\'' + data[d]['position_code'] + '\', \'' + data[d]['group_code'] + '\',' +
                                    ' \'' + data[d]['code'] + '\', \'' + data[d]['name'] + '\');" ' +
                                    'role="button" title="Xem chi tiết">' + data[d]['name'] +
                                    '</a>' +
                                    '</td>' +
                                    '<td>' + data[d]['position_name'] + '</td>' +
                                    '<td>' + data[d]['area_name'] + '</td>' +
                                    '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(data[d]['implementPoint'], 3) + '</td>' +
                                    '<td class="order-column" title="Chọn nhân viên để xuất dữ liệu"><input type="checkbox" class="ckbExport" onclick="clickCheckBox();" value="' + data[d]['code'] + '"></td>' +
                                    '</tr>';
                        }
                        html += '</tbody>' +
                                '</table>';
                        $('#tblResult').append(html);

                        //paint chart
                        var arrayUniqueEmp = result[2];
                        var dataChart = result[3];
                        var dataBM = result[4];
                        paintCharts(arrayUniqueEmp, dataChart, data, 0, dataBM);

                    }
                });

            }
        }

        function viewEachEmp(month, year, comp, area, pos, group, emp, name) {
            var month = parseInt(month);
            var year = parseInt(year);
            $("html, body").animate({ scrollTop: $(document).height()*2});
            var node = document.getElementById("tblResultEachEmp");
            while (node.firstChild) {
                node.removeChild(node.firstChild);
            }
            $('#tblResultEachEmp').show();
            var goal = $('#slGoal').val();
            $.get("getDataByTimeForEachEmp", {
                month: month, year: year, emp: emp, goal: goal,
                company: comp, area: area, position: pos, group: group
            }, function (result) {

                var name = result[1];
                var data = result[0];
                var arrParentGoal = result[4];
                // make table
                var html = '';
                html += '<table class="table-common">' +
                        '<thead>' +
                        '<tr>' +
                        '<th colspan="5" class="order-column">' + name + '</th>' +
                        '</tr>' +
                        '<tr>' +
                        '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                        '<th class="col-md-3 col-sm-3">Tiêu chí</th>' +
                        '<th class="col-md-3 col-sm-3">Kế hoạch</th>' +
                        '<th class="col-md-3 col-sm-3">Thực hiện</th>' +
                        '<th class="col-md-2 col-sm-2">Điểm thực hiện</th>' +
                        '</tr>' +
                        '</thead>' +
                        '<tbody>';
                var sumImplementPoint = 0;
                for (var pa = 0; pa < arrParentGoal.length; pa++) {
                    var index = 1;
                    var htmlEachGoal = ' ';
                    var IPParent = 0;
                    for (var d = 0; d < data.length; d++) {
                        if (arrParentGoal[pa]['id'] == data[d]['parent_id']) {
                            var TV = formatNumber( data[d]['targetValue'], 3);
                            var I = formatNumber( data[d]['implement'], 3);
                            var IP = formatNumber( data[d]['implementPoint'], 3);
                            sumImplementPoint += IP;
                            IPParent += IP;
                            if ((index) % 2 == 0) {
                                htmlEachGoal += '<tr class="background-color-smoke">';
                            } else {
                                htmlEachGoal += '<tr>';
                            }

                            if (TV != 0 || I != 0 || IP != 0) {
                                htmlEachGoal += '<td class="order-column">' + index + '</td>' +
                                        '<td>' + data[d]['goal_name'] + '</td>' +
                                        '<td class="order-column text-align-right" title="Kế hoạch của tháng">' + formatBigNumber(TV) + '</td>' +
                                        '<td class="order-column text-align-right" title="Thực hiện của tháng">' + formatBigNumber(I) + '</td>' +
                                        '<td class="order-column text-align-right" title="Điểm thực hiện của tháng">' + IP + '</td>' +
                                        '</tr>';
                                index++;
                            }
                        }
                    }

                    if (index > 1) {
                        html += '<tr class="color-parent">' +
                                '<td colspan="4">' + arrParentGoal[pa]['goal_name'] + '</td>' +
                                '<td class="text-align-right" title="Tổng điểm thực hiện của mục tiêu cha">' + formatNumber(IPParent, 3) + '</td>' +
                                '</tr>';
                        html += htmlEachGoal;
                    }
                }

                var sumImplementPointRound = formatNumber( sumImplementPoint, 3);
                html += '<tr>' +
                        '<td class="order-column background-color-smoke" colspan="4"><b>ĐIỂM THÁNG</b></td>' +
                        '<td class="text-align-right" title="Tổng điểm thực hiện của tháng"><b>' + formatNumber(sumImplementPointRound, 3) + '</b></td>' +
                        '</tr>' +
                        '</tbody>' +
                        '</table>';

                $('#tblResultEachEmp').append(html);
            });
        }

        function viewEachEmpMultiMonth(fromMonth, toMonth, fromYear, toYear, comp, area, pos, group, emp, name) {
            var fromMonth = parseInt(fromMonth);
            var fromYear = parseInt(fromYear);
            var toMonth = parseInt(toMonth);
            var toYear = parseInt(toYear);
            $("html, body").animate({ scrollTop: $(document).height()*2});
            var node = document.getElementById("tblResultEachEmp");
            while (node.firstChild) {
                node.removeChild(node.firstChild);
            }
            $('#tblResultEachEmp').show();
            var goal = $('#slGoal').val();

            $.get("getDataByTimeForEachEmp", {
                fromMonth: fromMonth, toMonth: toMonth, fromYear: fromYear, toYear: toYear,
                emp: emp, goal: goal, company: comp, area: area, position: pos, group: group
            }, function (result) {

                var data = result[0];
                var arrUniqueGoal = result[2];
                var name = result[1];
                var arrParentGoal = result[4];

                // if 1 month ....or multi month ...
                if ((fromMonth == toMonth) && (fromYear == toYear)) {
                    var html = '';
                    html += '<table class="table-common">' +
                            '<thead>' +
                            '<tr>' +
                            '<th colspan="5" class="order-column">' + name + '</th>' +
                            '</tr>' +
                            '<tr>' +
                            '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                            '<th class="col-md-3 col-sm-3">Tiêu chí</th>' +
                            '<th class="col-md-3 col-sm-3">Kế hoạch</th>' +
                            '<th class="col-md-3 col-sm-3">Thực hiện</th>' +
                            '<th class="col-md-2 col-sm-2">Điểm thực hiện</th>' +
                            '</tr>' +
                            '</thead>' +
                            '<tbody>';
                    var sumImplementPoint = 0;
                    for (var pa = 0; pa < arrParentGoal.length; pa++) {
                        var index = 1;
                        var htmlGoal = '';
                        var IPP = 0;
                        for (var d = 0; d < data.length; d++) {
                            if (arrParentGoal[pa]['id'] == data[d]['parent_id']) {
                                var TV = formatNumber( data[d]['targetValue'], 3);
                                var I = formatNumber( data[d]['implement'], 3);
                                var IP = formatNumber( data[d]['implementPoint'], 3);
                                sumImplementPoint += IP;
                                IPP += IP;
                                if ((index) % 2 == 0) {
                                    htmlGoal += '<tr class="background-color-smoke">';
                                } else {
                                    htmlGoal += '<tr>';
                                }

                                if (TV != 0 || I != 0 || IP != 0) {
                                    htmlGoal += '<td class="order-column">' + index + '</td>' +
                                            '<td>' + data[d]['goal_name'] + '</td>' +
                                            '<td class="order-column text-align-right" title="Kế hoạch của tháng">' + formatBigNumber(TV) + '</td>' +
                                            '<td class="order-column text-align-right" title="Thực hiện của tháng">' + formatBigNumber(I) + '</td>' +
                                            '<td class="order-column text-align-right" title="Điểm thực hiện của tháng">' + IP + '</td>' +
                                            '</tr>';
                                    index++;
                                }
                            }
                        }

                        if (index > 1) {
                            html += '<tr class="color-parent">' +
                                    '<td colspan="4">' + arrParentGoal[pa]['goal_name'] + '</td>' +
                                    '<td class="text-align-right" title="Tổng điểm thực hiện của mục tiêu cha">' + IPP + '</td>' +
                                    '</tr>';
                            html += htmlGoal;
                        }
                    }

                    var sumImplementPointRound = formatNumber( sumImplementPoint, 3);
                    html += '<tr class="background-color-smoke">' +
                            '<td class="order-column" colspan="4"><b>ĐIỂM THÁNG</b></td>' +
                            '<td class="text-align-right" title="Tổng điểm thực hiện của tháng"><b>' + sumImplementPointRound + '</b></td>' +
                            '</tr>' +
                            '</tbody>' +
                            '</table>';

                    $('#tblResultEachEmp').append(html);

                } else {
                    //get IP of goal parent of each month
                    var arrayParentIP = [];
                    for (var par = 0; par < arrParentGoal.length; par++) {
                        if (fromYear < toYear) {
                            for (var f = fromMonth; f <= 12; f++) {
                                var IP = 0;
                                for (var d = 0; d < data.length; d++) {
                                    if (data[d]['parent_id'] == arrParentGoal[par]['id'] &&
                                            data[d]['month'] == f && data[d]['year'] == fromYear) {
                                        IP += data[d]['implementPoint'];
                                    }
                                }

                                if (IP > 0) {
                                    var arrayEachGoalP = [];
                                    arrayEachGoalP.push(f);
                                    arrayEachGoalP.push(fromYear);
                                    arrayEachGoalP.push(arrParentGoal[par]['goal_code']);
                                    arrayEachGoalP.push(IP);
                                    arrayParentIP.push(arrayEachGoalP);
                                }
                            }
                            for (var t = 1; t <= toMonth; t++) {
                                var IP = 0;
                                for (var d = 0; d < data.length; d++) {
                                    if (data[d]['parent_id'] == arrParentGoal[par]['id'] &&
                                            data[d]['month'] == t && data[d]['year'] == toYear) {
                                        IP += data[d]['implementPoint'];
                                    }
                                }

                                if (IP > 0) {
                                    var arrayEachGoalP = [];
                                    arrayEachGoalP.push(t);
                                    arrayEachGoalP.push(toYear);
                                    arrayEachGoalP.push(arrParentGoal[par]['goal_code']);
                                    arrayEachGoalP.push(IP);
                                    arrayParentIP.push(arrayEachGoalP);
                                }
                            }
                        } else if (fromYear == toYear) {
                            for (var n = fromMonth; n <= toMonth; n++) {
                                var IP = 0;
                                for (var d = 0; d < data.length; d++) {
                                    if (data[d]['parent_id'] == arrParentGoal[par]['id'] &&
                                            data[d]['month'] == n && data[d]['year'] == fromYear) {
                                        IP += data[d]['implementPoint'];
                                    }
                                }

                                if (IP > 0) {
                                    var arrayEachGoalP = [];
                                    arrayEachGoalP.push(n);
                                    arrayEachGoalP.push(fromYear);
                                    arrayEachGoalP.push(arrParentGoal[par]['goal_code']);
                                    arrayEachGoalP.push(IP);
                                    arrayParentIP.push(arrayEachGoalP);
                                }
                            }
                        }
                    }

                    var countMonth = ((toYear - fromYear) * 12 + (toMonth - fromMonth + 1));
                    var html = '';
                    html = "<table class='table-common'>" +
                            "<thead>" +
                            "<tr>" +
                            '<th colspan="' + (countMonth + 2) + '" class="order-column">' + name + '</th>' +
                            '</tr>' +
                            '<tr>' +
                            "<th>STT</th>" +
                            "<th class='col-md-3'>Tên chỉ tiêu</th>";
                    if (fromYear < toYear) {
                        for (f = fromMonth; f < 12; f++) {
                            html += "<th>Tháng " + f + "</th>";
                        }
                        html += "<th>Tháng 12-" + fromYear + "</th>";
                        for (t = 1; t < toMonth; t++) {
                            html += "<th>Tháng " + t + "</th>";
                        }
                        html += "<th>Tháng" + toMonth + '-' + toYear + "</th>";
                    } else if (fromYear == toYear) {
                        for (n = fromMonth; n <= toMonth; n++) {
                            html += "<th>Tháng " + n + "</th>";
                        }
                    }

                    html += "</tr>" +
                            "</thead>" +
                            "<tbody>";

                    //get data for all goal, all month
                    for (var pa = 0; pa < arrParentGoal.length; pa++) {
                        var isZero = 1;
                        var htmlP = '';
                        htmlP += '<tr class="color-parent" >' +
                                '<td colspan="2">' + arrParentGoal[pa]['goal_name'] + '</td>';
                        if (fromYear < toYear) {
                            for (var f = fromMonth; f < 12; f++) {
                                var IPTotal = 0;
                                for (var IPP = 0; IPP < arrayParentIP.length; IPP++) {
                                    if (arrParentGoal[pa]['goal_code'] == arrayParentIP[IPP][2] &&
                                            arrayParentIP[IPP][0] == f &&
                                            arrayParentIP[IPP][1] == fromYear) {
                                        IPTotal = formatNumber( arrayParentIP[IPP][3], 3);
                                    }
                                }
                                if (IPTotal > 0) {
                                    isZero = 0;
                                }
                                htmlP += "<td class='text-align-right' title='Tổng điểm thực hiện của mục tiêu cha'>" + formatNumber( IPTotal, 3) + "</td>";
                            }

                            for (var t = 1; t < toMonth; t++) {
                                var IPTotal = 0;
                                for (var IPP = 0; IPP < arrayParentIP.length; IPP++) {
                                    if (arrParentGoal[pa]['goal_code'] == arrayParentIP[IPP][2] &&
                                            arrayParentIP[IPP][0] == t &&
                                            arrayParentIP[IPP][1] == toYear) {
                                        IPTotal = formatNumber( arrayParentIP[IPP][3], 3);
                                    }
                                }
                                if (IPTotal > 0) {
                                    isZero = 0;
                                }
                                htmlP += "<td class='text-align-right' title='Tổng điểm thực hiện của mục tiêu cha'>" + formatNumber( IPTotal, 3) + "</td>";
                            }
                        } else if (fromYear == toYear) {
                            for (var n = fromMonth; n <= toMonth; n++) {
                                var IPTotal = 0;
                                for (var IPP = 0; IPP < arrayParentIP.length; IPP++) {
                                    if (arrParentGoal[pa]['goal_code'] == arrayParentIP[IPP][2] &&
                                            arrayParentIP[IPP][0] == n &&
                                            arrayParentIP[IPP][1] == fromYear) {
                                        IPTotal = formatNumber( arrayParentIP[IPP][3], 3);
                                    }
                                }
                                if (IPTotal > 0) {
                                    isZero = 0;
                                }
                                htmlP += "<td class='text-align-right' title='Tổng điểm thực hiện của mục tiêu cha'>" + formatNumber(IPTotal, 3) + "</td>";
                            }
                        }
                        if (isZero == 0) {
                            html += htmlP;
                            html += '</tr>';

                            var index = 1;
                            for (var gC = 0; gC < arrUniqueGoal.length; gC++) {
                                if (arrUniqueGoal[gC]['parent_id'] == arrParentGoal[pa]['id']) {
                                    var htmlGoalC = '';
                                    var isNull = 1;
                                    if ((index) % 2 == 0) {
                                        htmlGoalC += '<tr class="background-color-smoke">';
                                    } else {
                                        htmlGoalC += '<tr>';
                                    }
                                    htmlGoalC += '<td class="order-column">' + (index) + '</td>' +
                                            '<td>' + arrUniqueGoal[gC]['goal_name'] + '</td>';
                                    if (fromYear < toYear) {
                                        for (f = fromMonth; f <= 12; f++) {
                                            var implementPoint = 0;
                                            for (var d = 0; d < data.length; d++) {
                                                if (data[d]['goal_code'] == arrUniqueGoal[gC]['goal_code'] &&
                                                        data[d]['month'] == f && data[d]['year'] == fromYear) {
                                                    implementPoint = formatNumber( data[d]['implementPoint'], 3);
                                                }
                                            }
                                            if (implementPoint > 0) {
                                                isNull = 0;
                                            }
                                            htmlGoalC += '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber( implementPoint, 3) + '</td>';
                                        }

                                        for (t = 1; t < toMonth; t++) {
                                            var implementPoint = 0;
                                            for (var d = 0; d < data.length; d++) {
                                                if (data[d]['goal_code'] == arrUniqueGoal[gC]['goal_code'] &&
                                                        data[d]['month'] == t && data[d]['year'] == toYear) {
                                                    implementPoint = formatNumber( data[d]['implementPoint'], 3);
                                                }
                                            }
                                            if (implementPoint > 0) {
                                                isNull = 0;
                                            }
                                            htmlGoalC += '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber( implementPoint, 3) + '</td>';
                                        }

                                    } else if (fromYear == toYear) {
                                        for (n = fromMonth; n <= toMonth; n++) {
                                            var implementPoint = 0;
                                            for (var d = 0; d < data.length; d++) {
                                                if (data[d]['goal_code'] == arrUniqueGoal[gC]['goal_code'] &&
                                                        data[d]['month'] == n && data[d]['year'] == toYear) {
                                                    implementPoint = formatNumber( data[d]['implementPoint'], 3);
                                                }
                                            }
                                            if (implementPoint > 0) {
                                                isNull = 0;
                                            }
                                            htmlGoalC += '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber( implementPoint, 3) + '</td>';
                                        }
                                    }
                                    htmlGoalC += '</tr>';
                                    if (isNull == 0) {
                                        html += htmlGoalC;
                                        index++;
                                    }
                                }
                            }
                        }
                    }

                    //get sum implement point  by: goal_name-goal_code-month-year-sumIP
                    html += '</tr>' +
                            '<td colspan="2" class="order-column background-color-smoke"><b>ĐIỂM THÁNG</b></td>';
                    if (fromYear < toYear) {
                        for (f = fromMonth; f <= 12; f++) {
                            var implementPoint = 0;
                            for (var d = 0; d < data.length; d++) {
                                if (data[d]['month'] == f && data[d]['year'] == fromYear) {
                                    implementPoint += formatNumber(data[d]['implementPoint'], 3);
                                }
                            }
                            html += '<td class="text-align-right" title="Tổng điểm thực hiện của tháng"><b>' + formatNumber(implementPoint, 3) + '</b></td>';
                        }

                        for (t = 1; t < toMonth; t++) {
                            var implementPoint = 0;
                            for (var d = 0; d < data.length; d++) {
                                if (data[d]['month'] == t && data[d]['year'] == toYear) {
                                    implementPoint += formatNumber(data[d]['implementPoint'], 3);
                                }
                            }
                            html += '<td class="text-align-right" title="Tổng điểm thực hiện của tháng"><b>' + formatNumber(implementPoint, 3) + '</b></td>';
                        }

                    } else if (fromYear == toYear) {
                        for (n = fromMonth; n <= toMonth; n++) {
                            var implementPoint = 0;
                            for (var d = 0; d < data.length; d++) {
                                if (data[d]['month'] == n && data[d]['year'] == toYear) {
                                    implementPoint += formatNumber(data[d]['implementPoint'], 3);
                                }
                            }
                            html += '<td class="text-align-right" title="Tổng điểm thực hiện của tháng"><b>' + formatNumber(implementPoint, 3) + '</b></td>';
                        }
                    }
                    html += "</tr>"
                    "</tbody>" +
                    "</table>";
                    $('#tblResultEachEmp').append(html);
                }

            });
        }

        function loadDataChangeEmpArea() {
            //change company: change area+ change emp
            var company = $('#slCompany').val();
            var position = $('#slPosition').val();
            var group = $('#slGroup').val();

            $.get("getDataEmpArea", {company: company, position: position, group: group}, function (result) {
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
                    var dataArea = result[0];
                    var dataEmp = result[1];

                    var currentArea = "<?php echo $currentAreaId ?>";
                    var accessLevel = "<?php echo $accessLevel ?>";

                    if(accessLevel > 2){
                        var html = '';
                        for (var a = 0; a < dataArea.length; a++) {
                            if(dataArea[a]['id'] == currentArea){
                                html += "<option value='" + dataArea[a]['area_code'] + "' selected>" +
                                        dataArea[a]['area_name'] +
                                        "</option>";
                                break;
                            }
                        }
                    } else {
                        var html = '<option value="1" selected>Tất cả</option>';
                        for (var a = 0; a < dataArea.length; a++) {
                            html += "<option value='" + dataArea[a]['area_code'] + "'>" +
                                    dataArea[a]['area_name'] +
                                    "</option>";

                        }
                    }

                    $('#slArea').append(html);

                    var html = '<option value="1" selected>Tất cả</option>';
                    for (var e = 0; e < dataEmp.length; e++) {
                        if(dataEmp[e]['id'] != 0 && dataEmp[e]['id'] != 1 && dataEmp[e]['id'] != 2 && dataEmp[e]['id'] != 3){
                            html += "<option value='" + dataEmp[e]['code'] + "'>" +
                                                            dataEmp[e]['name'] +
                                                            "</option>";
                        }

                    }
                    $('#slEmp').append(html);

                } else {
                    var html = '<option value="1" selected>' +
                            'Tất cả' +
                            '</option>';
                    $('#slArea').append(html);
                    $('#slEmp').append(html);
                }
            });

        }

        function loadDataChangeEmp() {
            var company = $('#slCompany').val();
            var position = $('#slPosition').val();
            var area = $('#slArea').val();
            var group = $('#slGroup').val();

            $.get("getDataEmp", {company: company, position: position, area: area, group: group}, function (result) {
                var nodeE = document.getElementById("slEmp");
                while (nodeE.firstChild) {
                    nodeE.removeChild(nodeE.firstChild);
                }

                if (result != false) {
                    var dataEmp = result;
                    var html = '<option value="1" selected>Tất cả</option>';
                    for (var e = 0; e < dataEmp.length; e++) {
                        if(dataEmp[e]['id'] != 0 && dataEmp[e]['id'] != 1 && dataEmp[e]['id'] != 2 && dataEmp[e]['id'] != 3){
                            html += "<option value='" + dataEmp[e]['code'] + "'>" + dataEmp[e]['name'] + "</option>";
                        }
                    }
                    $('#slEmp').append(html);
                } else {
                    var html = '<option value="1" selected>Tất cả</option>';
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
        setCookie("areaChoose",'');
        setCookie("areaDetailChoose",'');

        $(document).ready(function () {
            // add selected class to selected li
            loadDataChangeEmpArea();
            loadDataChangeEmp();
            showData();
            //edit show on device
            var width = $(window).width();
            if (width < 550) {
                $(".ms-parent").addClass("min-width-365");
                $(".select2").addClass("min-width-326");
                $("#divPos").remove("margin-left-1-6");
                $("#divEmp").remove("margin-left-1-6");
            } else {
                $(".ms-parent").remove("min-width-365");
                $(".select2").remove("min-width-326");
                $("#divPos").addClass("margin-left-1-6");
                $("#divEmp").addClass("margin-left-1-6");
            }
        });

    </script>
@stop
