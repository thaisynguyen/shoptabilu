@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.REPORT'). ' ' .Config::get('constant.RPT_DETAIL_BY_AREA'))
@section('section')
@include('alerts.errors')
@include('widgets.timeOption.sortByTime', array('year' => $year,
                                                'link' => "reloadPageWithParam('{{action('ExportExcelController@reportDetailArea')}}'
                                                                             , 'reportDetailArea'
                                                                             , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/slMonthByToMonth/slYearByFromMonth/slYearByToMonth/slCompany/slAreaHide/slGoalHide/codeChoose')"))
<?php
$currentCompanyId = Session::get('scompany_id');
$currentAreaId = Session::get('sarea_id');
$accessLevel = Session::get('saccess_level');
?>
    <div id="wrapper">
        <div class="row margin-form">
            <div class="col-md-12 col-xs-12">
                <div class="col-md-3 col-xs-12">
                    <b class="font-13">Phòng/Đài/MBF HCM</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12 divSlCompany margin-left-015" id="divSlCompany">
                        <select class="form-control margin-top-8 width-104" id="slCompany"
                                onchange="changeShowByCompany();"
                                onchange="reloadPageWithParam('{{action('ExportExcelController@reportDetailArea')}}'
                                        , 'reportDetailArea'
                                        , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                        'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                        'slCompany/slAreaHide/slGoalHide/codeChoose')">
                            <?php
                            if($accessLevel > 1){
                            foreach($company as $row){
                            if($row->id == $currentCompanyId){?>
                                <option value="<?php echo $row->company_code ?>"><?php echo $row->company_name ?></option>
                            <?php break;}}} else {
                                foreach($company as $row){?>
                                    <option value="<?php echo $row->company_code ?>">
                                        <?php echo $row->company_name ?>
                                    </option>
                            <?php }}?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 margin-form col-xs-12">
                    <b class="font-13 lb_emp">Tổ/Quận/Huyện</b><br>

                    <div class="col-md-12 btnChoose combobox-margin-left-area margin-top-1-12" id="cbArea">
                        <select multiple="multiple" class="form-control margin-top-8 width-104" id="slArea"
                                onchange="changeShowByArea();">
                            <?php
                            if($accessLevel > 2){
                            for($a = 0; $a<count($area); $a++){
                            if($currentAreaId == $area[$a]->id){?>
                            <option value="<?php echo $area[$a]->area_code?>" selected><?php echo $area[$a]->area_name?></option>
                            <?php }}} else {?>
                            <option value="1" selected>Tất cả</option>
                            <?php
                            for($a = 0; $a<count($area); $a++){?>
                            <option value="<?php echo $area[$a]->area_code?>" ><?php echo $area[$a]->area_name?></option>
                            <?php }}?>
                        </select>
                        <input type="hidden" id="slAreaHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportDetailArea')}}'
                                       , 'reportDetailArea'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slAreaHide/slGoalHide/codeChoose')">

                        <input type="hidden" id="codeChoose" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportDetailArea')}}'
                                       , 'reportDetailArea'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slAreaHide/slGoalHide/codeChoose')">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <b class="font-13">Mục tiêu</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12 goal" style="">
                        <select multiple="multiple" class="margin-top-8 width-102" id="slGoal"
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
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportDetailArea')}}'
                                       , 'reportDetailArea'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slAreaHide/slGoalHide/codeChoose')">
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <button id="btnExport" class="btn btn-primary pull-right margin-left-15"
                            onclick="reloadPageWithParam('{{action('ExportExcelController@reportDetailArea')}}'
                                    , 'reportDetailArea'
                                    , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                    'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                    'slCompany/slAreaHide/slGoalHide/codeChoose')"><i
                                class="fa fa-sign-out"></i> Xuất Excel
                    </button>
                </span>
            </div>
            <div class="col-md-12 col-xs-12">
                <ul id="myTab" class="nav nav-tabs ">
                    <li class="active"><a href="#viewData" data-toggle="tab">Xem dữ liệu</a></li>
                    <li><a href="#viewChart" data-toggle="tab">Xem biểu đồ</a></li>
                </ul>
                <div id="myTabContent" class="tab-content">
                    <div class="tab-pane fade in active" id="viewData">
                        <div class="col-sm-12" id="tblResult"></div>
                        <div class="col-sm-12" id="tblResultEachArea"></div>
                        <div class="col-sm-12 color-red" id="txtResult" hidden>Không tìm thấy kết quả!</div>
                        <div class="col-sm-12 color-red" id="txtResultCountMonth" hidden>Không thể xem dữ liệu quá 12
                            tháng. Vui lòng xuất excel để xem!.
                        </div>
                    </div>

                    <div class="tab-pane fade" id="viewChart">
                        @include('widgets.chartOption.chartNotImpOption')
                        @include('widgets.chartOption.showSummary')
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
    {{ HTML::style('public/assets/stylesheets/select2.min.css') }}
    {{ HTML::script('public/assets/scripts/select2.min.js') }}
    {{ HTML::style('public/assets/stylesheets/implement.css') }}
    {{ HTML::style('public/assets/stylesheets/multiple-select.css') }}
    {{ HTML::script('public/assets/scripts/multiple-select.js') }}
    {{ HTML::script('public/assets/scripts/highcharts.js') }}
    {{ HTML::script('public/assets/scripts/highcharts-3d.js') }}
    {{ HTML::script('public/assets/scripts/data.js') }}
    {{ HTML::script('public/assets/scripts/exporting.js') }}

    <script type="text/javascript">

        function paintCharts(data, arrayArea, dataGoal, $dataBM) {
            var isCheckMIP = document.getElementById("rdMIP").checked;
            var isCheckGBMIP = document.getElementById("rdGBMIP").checked;

            var isCheckRadarChart = document.getElementById("rdRadarChart").checked;
            var isCheckRdColumnChart = document.getElementById("rdColumnChart").checked;
            var isCheckLineChart = document.getElementById("rdLineChart").checked;
            var isCheckCkbSummary = document.getElementById("ckbSummaryChart").checked;

            var myNode = document.getElementById("chartAjax");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }

            var html = '';
            if(isCheckRdColumnChart){
                if(isCheckCkbSummary){
                    html += '<div class="col-sm-12">' +
                            "<div id='summary' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                }
                for (var a = 0; a < arrayArea.length; a++) {
                    if (a == (arrayArea.length - 1)) {
                        html += '<div class="col-sm-12">' +
                                "<div id='" + arrayArea[a]['area_code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    } else {
                        html += '<div class="col-sm-12 marg-bottom-1-100">' +
                                "<div id='" + arrayArea[a]['area_code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    }
                }

            } else if(isCheckRadarChart || isCheckLineChart){
                if(isCheckCkbSummary){
                    html += '<div class="col-sm-12">';
                    html += '<div class="panel panel-default">';
                    html += '<div class="panel-heading">';
                    html += '<h3 class="panel-title">Biểu Đồ Tổng Quan</h3>';
                    html += '</div>';
                    html += '<div class="panel-body">';
                    html += "<canvas id='summary' height='220' width='350'></canvas>";
                    html += '</div></div></div>';
                }
                for (var a = 0; a < arrayArea.length; a++) {
                    html += '<div class="col-sm-12">';
                    html += '<div class="panel panel-default">';
                    html += '<div class="panel-heading">';
                    html += '<h3 class="panel-title">' + arrayArea[a]['area_name'] + '</h3>';
                    html += '</div>';
                    html += '<div class="panel-body">';
                    html += "<canvas id='" + arrayArea[a]['area_code'] + "' height='220' width='350'></canvas>";
                    html += '</div></div></div>';
                }
            }

            $('#chartAjax').append(html);

            //summary
            if(isCheckCkbSummary){
                var areaStr = '';
                var ipCharts = '';
                var benchmarkCharts = '';
                var arrayChartColumn = [];
                var arrayChartColumnBM = [];
                for (var a = 0; a < arrayArea.length; a++) {
                    var areaName = arrayArea[a]['area_name'].replace(" ", "_");
                    var child = [];
                    var childBM = [];
                    childBM.push(areaName);
                    child.push(areaName);
                    areaStr += '"' + areaName + '",';
                    var ip = 0;
                    var bm = 0;
                    for (var d = 0; d < data.length; d++) {
                        if (data[d]['area_code'] == arrayArea[a]['area_code']) {
                            ip += formatNumber(data[d]['implementPoint'], 3);
                        }
                    }

                    for (var d = 0; d < $dataBM.length; d++) {
                        if ($dataBM[d]['area_code'] == arrayArea[a]['area_code']) {
                            bm = formatNumber($dataBM[d]['benchmark'], 3);
                            break;
                        }
                    }

                    child.push(ip);
                    child.push(bm);
                    childBM.push(bm);
                    arrayChartColumn.push(child);
                    arrayChartColumnBM.push(childBM);
                    ipCharts += '"' + ip + '",';
                    benchmarkCharts += '"' + bm + '",';
                }

                if(isCheckRadarChart){
                    paintChartTVIByMonthGoal(areaStr, ipCharts, benchmarkCharts, 'summary', 'Điểm thực hiện', 'Điểm chuẩn');
                } else if(isCheckRdColumnChart){
                    paintChartColumnIPBM(arrayChartColumn, 'summary', 'Điểm thực hiện', 'Điểm chuẩn', 'Biểu Đồ Tổng Quan');
                } else if(isCheckLineChart){
                    paintLineChartTVIByMonthGoal(areaStr, ipCharts, benchmarkCharts, 'summary', 'Điểm thực hiện', 'Điểm chuẩn');
                }
            }

            //charts option
            if (isCheckGBMIP) {
                // view by goal-implement point
                for (var a = 0; a < arrayArea.length; a++) {
                    var arrayChartColumn = [];
                    var goalChild = '';
                    var ipCharts = '';
                    var bmCharts = '';
                    var arrGoal = arrayArea[a]['goal'];
                    for (var t = 0; t < arrGoal.length; t++) {
                        var arrayChild = [];
                        arrayChild.push(arrGoal[t]['goal_name']);
                        goalChild += '"' + arrGoal[t]['goal_name'] + '",';
                        var ip = 0;
                        var bm = 0;
                        for (var d = 0; d < dataGoal.length; d++) {
                            if (dataGoal[d]['goal_code'] == arrGoal[t]['goal_code'] &&
                                    dataGoal[d]['area_code'] == arrayArea[a]['area_code']) {
                                ip += formatNumber(dataGoal[d]['implementPoint'], 3);
                                bm += formatNumber(dataGoal[d]['benchmark'], 3);
                            }
                        }
                        arrayChild.push(ip);
                        arrayChild.push(bm);
                        arrayChartColumn.push(arrayChild);
                        ipCharts += '"' + ip + '",';
                        bmCharts += '"' + bm + '",';
                    }
                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, arrayArea[a]['area_code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrayArea[a]['area_code'], 'Điểm thực hiện', 'Điểm chuẩn', arrayArea[a]['area_name']);
                    } else if(isCheckLineChart){
                        paintLineChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, arrayArea[a]['area_code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    }
                }
            } else if(isCheckMIP){
                for (var a = 0; a < arrayArea.length; a++) {
                    var arrayChartColumn = [];
                    var timeStr = '';
                    var ipCharts = '';
                    var bmCharts = '';
                    var arrTime = arrayArea[a]['time'];
                    for (var ti = 0; ti < arrTime.length; ti++) {
                        var arrayChild = [];
                        arrayChild.push('Tháng_'+arrTime[ti]['month']);
                        timeStr += '"Tháng_' + arrTime[ti]['month'] + '",';
                        var ip = 0;
                        var bm = 0;
                        for (var da = 0; da < data.length; da++) {
                            if (data[da]['month'] == arrTime[ti]['month'] && data[da]['year'] == arrTime[ti]['year'] &&
                                    data[da]['area_code'] == arrayArea[a]['area_code']) {
                                ip += formatNumber(data[da]['implementPoint'], 3);
                                bm += formatNumber(data[da]['benchmark'], 3);
                            }
                        }
                        arrayChild.push(ip);
                        arrayChild.push(bm);
                        arrayChartColumn.push(arrayChild);
                        ipCharts += '"' + ip + '",';
                        bmCharts += '"' + bm + '",';
                    }
                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(timeStr, ipCharts, bmCharts, arrayArea[a]['area_code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrayArea[a]['area_code'], 'Điểm thực hiện', 'Điểm chuẩn', arrayArea[a]['area_name']);
                    } else if(isCheckLineChart){
                        paintLineChartTVIByMonthGoal(timeStr, ipCharts, bmCharts, arrayArea[a]['area_code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    }
                }
            }
        }

        //call ajax to load pages to get implement_point of company
        function showData() {
            $('#tblResultEachArea').hide();
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
            var area = $('#slArea').val();
            var goal = $('#slGoal').val();

            var isCheckMonth = document.getElementById("rdSelectByFromMonth").checked;

            var elemP = document.getElementById("slAreaHide");
            elemP.value = area;

            var elemP = document.getElementById("slGoalHide");
            elemP.value = goal;

            var isAll = 0;
            for (var a = 0; a < area.length; a++) {
                if (area[a] == 1) {
                    isAll = 1;
                }
            }
            if (isCheckMonth) {
                var countMonth = ((toYear - fromYear) * 12 + (toMonth - fromMonth + 1));
                if ((toYear < fromYear) || ((toYear == fromYear) && (toMonth < fromMonth))) {
                    $('#tblResult').hide();
                    $('#txtResult').hide();
                    $('#txtResultCountMonth').hide();
                    $('#warningWhenShowData').modal('show');
                    $('.btnExit').hide();
                    $('#content').html("");
                    html = '<div class=" col-lg-11 font-size-base" style=" margin-left: 5%;padding-top: 7px;">' +
                            '<span style="padding-left: 5px; "><b>Vui lòng chọn thời gian bắt đầu nhỏ hơn thời gian kết thúc.</b></span></div>';
                    $('#content').append(html);
                    $('#btnExportInformation').on('click', function (e) {
                        $('#warningImportGoalArea').modal('hide');
                    });
                } else if (countMonth > 12) {
                    $('#btnExport').prop("disabled", false);
                    $('#tblResult').hide();
                    $('#txtResult').hide();
                    $('#txtResultCountMonth').show();
                } else if (area == null) {
                    $('#btnExport').prop("disabled", true);
                    $('#tblResult').hide();
                    $('#txtResult').show();
                    $('#txtResultCountMonth').hide();
                } else {
                    $('#btnExport').prop("disabled", true);
                    $('#txtResultCountMonth').hide();
                    $('#txtResult').hide();
                    $('#tblResult').show();

                    var node = document.getElementById("tblResult");
                    while (node.firstChild) {
                        node.removeChild(node.firstChild);
                    }
                    //get data
                    $.get("getDataOfReportDetailByArea", {
                        fromMonth: fromMonth, fromYear: fromYear, toMonth: toMonth, toYear: toYear,
                        company: company, area: area, goal: goal
                    }, function (result) {
                    console.log(result);
                        if (result == false) {
                            $('#btnExport').prop("disabled", true);
                            $('#tblResult').hide();
                            $('#txtResult').show();
                            $('#txtResultCountMonth').hide();
                        } else {
                            var myNode = document.getElementById("tblResult");
                            while (myNode.firstChild) {
                                myNode.removeChild(myNode.firstChild);
                            }
                            $('#btnExport').prop("disabled", false);
                            $('#tblResult').show();
                            $('#txtResult').hide();
                            $('#txtResultCountMonth').hide();
                            var data = result[0];
                            var arrayArea = result[1];
                            var html = '';
                            if (fromMonth == toMonth && fromYear == toYear) {
                                html += '<table class="table-common">' +
                                        '<thead>' +
                                        '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                                        '<th class="col-md-5 col-sm-5 order-column">Tổ/Quận/Huyện</th>' +
                                        '<th class="col-md-5 col-sm-5">Tháng ' + fromMonth + '</th>' +
                                        '<th class="col-md-1 col-sm-1 order-column">Xuất</th>' +
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
                                            '<td><a onclick="viewEachArea(\'' + fromMonth + '\', \'' + fromYear + '\', \'' + company + '\', \'' + data[d]['area_code'] + '\', \'' + data[d]['area_name'] + '\');" ' +
                                            'role="button" title="Xem chi tiết">' +
                                            data[d]['area_name']
                                            + '</a></td>' +
                                            '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(data[d]['implementPoint'], 3) + '</td>' +
                                            '<td class="order-column" title="Chọn tổ/quận/huyện để xuất dữ liệu">' +
                                            '<input type="checkbox" class="ckbExport" onclick="clickCheckBox();" value="' + data[d]['area_code'] + '">' +
                                            '</td></tr>';
                                }

                                html += '</tbody>' +
                                        '</table>';
                            } else {
                                html += '<table class="table-common">' +
                                        '<thead>' +
                                        '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                                        '<th class="col-md-3 col-sm-3 order-column">Tổ/Quận/Huyện</th>';
                                if (fromYear < toYear) {
                                    for (f = fromMonth; f < 12; f++) {
                                        html += "<th class='order-column'>Tháng " + f + "</th>";
                                    }
                                    html += "<th class='order-column'>Tháng 12-" + fromYear + "</th>";
                                    for (t = 1; t < toMonth; t++) {
                                        html += "<th class='order-column'>Tháng " + t + "</th>";
                                    }
                                    html += "<th class='order-column'>Tháng" + toMonth + '-' + toYear + "</th>";
                                } else if (fromYear == toYear) {
                                    for (n = fromMonth; n <= toMonth; n++) {
                                        html += "<th class='order-column'>Tháng " + n + "</th>";
                                    }
                                }
                                html += '<th class="col-md-1 col-sm-1 order-column">Xuất</th>' +
                                        '</tr>' +
                                        '</thead>' +
                                        '<tbody>';
                                for (var p = 0; p < arrayArea.length; p++) {
                                    if ((d + 1) % 2 == 0) {
                                        html += '<tr class="background-color-smoke">';
                                    } else {
                                        html += '<tr>';
                                    }
                                    html += '<td class="order-column">' + (p + 1) + '</td>' +
                                            '<td><a onclick="viewMultiArea(\'' + fromMonth + '\', \'' + fromYear + '\', \'' + toMonth + '\', \'' + toYear + '\', \'' + company + '\',' +
                                            ' \'' + arrayArea[p]['area_code'] + '\', \'' + arrayArea[p]['area_name'] + '\');" ' +
                                            'role="button" title="Xem chi tiết">' +
                                            arrayArea[p]['area_name']
                                            + '</a>' +
                                            '</td>';

                                    if (fromYear < toYear) {
                                        for (var f = fromMonth; f <= 12; f++) {
                                            var ip = 0;
                                            for (var d = 0; d < data.length; d++) {
                                                if (data[d]['area_code'] == arrayArea[p]['area_code'] &&
                                                        data[d]['month'] == f && data[d]['year'] == fromYear) {
                                                    ip = formatNumber(data[d]['implementPoint'], 3);
                                                }
                                            }
                                            html += '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(ip, 3) + '</td>';
                                        }
                                        for (var t = 1; t <= toMonth; t++) {
                                            var ip = 0;
                                            for (var d = 0; d < data.length; d++) {
                                                if (data[d]['area_code'] == arrayArea[p]['area_code'] &&
                                                        data[d]['month'] == t && data[d]['year'] == toYear) {
                                                    ip = formatNumber(data[d]['implementPoint'], 3);
                                                }
                                            }
                                            html += '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(ip, 3) + '</td>';
                                        }
                                    } else if (fromYear == toYear) {
                                        for (var n = fromMonth; n <= toMonth; n++) {
                                            var ip = 0;
                                            for (var d = 0; d < data.length; d++) {
                                                if (data[d]['area_code'] == arrayArea[p]['area_code'] &&
                                                        data[d]['month'] == n && data[d]['year'] == toYear) {
                                                    ip = formatNumber(data[d]['implementPoint'], 3);
                                                }
                                            }
                                            html += '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(ip, 3) + '</td>';
                                        }
                                    }

                                    html += '<td class="order-column" title="Chọn tổ/quận/huyện để xuất dữ liệu">' +
                                            '<input type="checkbox" class="ckbExport" onclick="clickCheckBox();" value="' + arrayArea[p]['area_code'] + '">' +
                                            '</td></tr>';
                                }

                                html += '</tbody>' +
                                        '</table>';
                            }
                            $('#tblResult').append(html);

                            // charts
                            var dataGoal = result[2];
                            var $dataBM = result[3];
                            paintCharts(data, arrayArea, dataGoal, $dataBM);
                        }
                    });
                }
            } else {
                if (area == null) {
                    $('#btnExport').prop("disabled", true);
                    $('#tblResult').hide();
                    $('#txtResult').show();
                    $('#txtResultCountMonth').hide();
                } else {
                    $('#btnExport').prop("disabled", true);
                    $('#txtResultCountMonth').hide();
                    $('#txtResult').hide();
                    $('#tblResult').show();

                    // get data of time
                    var node = document.getElementById("tblResult");
                    while (node.firstChild) {
                        node.removeChild(node.firstChild);
                    }
                    //get data
                    $.get("getDataOfReportDetailByArea", {
                        month: month,
                        year: year,
                        company: company,
                        area: area,
                        goal: goal
                    }, function (result) {
                        if (result == false) {
                            $('#tblResult').hide();
                            $('#txtResult').show();
                            $('#btnExport').prop("disabled", true);
                        } else {
                            $('#txtResult').hide();
                            $('#btnExport').prop("disabled", false);
                            var myNode = document.getElementById("tblResult");
                            while (myNode.firstChild) {
                                myNode.removeChild(myNode.firstChild);
                            }

                            var data = result[0];
                            var html = '<table class="table-common">' +
                                    '<thead>' +
                                    '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                                    '<th class="col-md-5 col-sm-5 order-column">Tổ/Quận/Huyện</th>' +
                                    '<th class="col-md-5 col-sm-5">Tháng ' + month + '</th>' +
                                    '<th class="col-md-1 col-sm-1 order-column">Xuất</th>' +
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
                                        '<td><a onclick="viewEachArea(\'' + month + '\', \'' + year + '\', \'' + company + '\', \'' + data[d]['area_code'] + '\', \'' + data[d]['area_name'] + '\');" ' +
                                        'role="button" title="Chọn chức danh để xuất dữ liệu">' +
                                        data[d]['area_name']
                                        + '</a></td>' +
                                        '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(data[d]['implementPoint'], 3) + '</td>' +
                                        '<td class="order-column" title="Chọn chức danh để xuất dữ liệu">' +
                                        '<input type="checkbox" class="ckbExport" onclick="clickCheckBox();" value="' + data[d]['area_code'] + '">' +
                                        '</td></tr>';
                            }

                            html += '</tbody>' +
                                    '</table>';
                            $('#tblResult').append(html);

                            // charts
                            var arrayArea = result[1];
                            var dataGoal = result[2];
                            var $dataBM = result[3];
                            paintCharts(data, arrayArea, dataGoal, $dataBM);
                        }
                    });
                }
            }
        }

        function viewEachArea(month, year, company, area, areaName) {
            var month = parseInt(month);
            var year = parseInt(year);
            $("html, body").animate({ scrollTop: $(document).height()*2});
            $('#tblResultEachArea').show();
            var myNode = document.getElementById("tblResultEachArea");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }
            var goal = $('#slGoal').val();
            $.get("getDataOfEachArea", {
                month: month, year: year, goal: goal,
                company: company, area: area
            }, function (result) {
                // show data when the result is different false
                var data = result[0];
                var parent = result[1];

                var html = '<table class="table-common">' +
                        '<thead>' +
                        '<tr>' +
                        '<th colspan="10" class="">' + areaName + '</th>' +
                        '</tr>' +
                        '<tr>' +
                        '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Mã</th>' +
                        '<th class="col-md-3 col-sm-3">Tên mục tiêu</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Loại mục tiêu</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Kế hoạch</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Đơn vị tính</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Tỷ trọng</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Điểm chuẩn</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Điểm thực hiện</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Tỷ lệ đạt</th>' +
                        '</tr>' +
                        '</thead>' +
                        '<tbody>';

                for (var p = 0; p < parent.length; p++) {
                    var sumIP = 0;
                    var sumBM = 0;
                    var htmlChild = '';
                    var index = 0;
                    for (var d = 0; d < data.length; d++) {
                        if (data[d]['parent_id'] == parent[p]['id']) {
                            var il = data[d]['important_level'];
                            var bm = data[d]['benchmark'];
                            var tv = data[d]['target_value'];
                            var cp = data[d]['real_percent'] * 100;
                            var ip = data[d]['implement_point'];

                            if (il > 0 || bm > 0 || tv > 0 || i > 0 || ip > 0) {
                                index++;
                                if (index % 2 == 0) {
                                    htmlChild += '<tr class="background-color-smoke">';
                                } else {
                                    htmlChild += '<tr>';
                                }
                                htmlChild += '<td class="order-column">' + index + '</td>' +
                                        '<td>' + data[d]['goal_code'] + '</td>' +
                                        '<td>' + data[d]['goal_name'] + '</td>' +
                                        '<td class="order-column">' + renderGoalType(data[d]['goal_type']) + '</td>' +
                                        '<td class="text-align-right">' + formatBigNumber(tv) + '</td>' +
                                        '<td >' + data[d]['unit_name'] + '</td>' +
                                        '<td class="text-align-right">' + il + '</td>' +
                                        '<td class="text-align-right">' + formatNumber(bm, 3) + '</td>' +
                                        '<td class="text-align-right">' + formatNumber(ip, 3) + '</td>' +
                                        '<td class="text-align-right">' + formatNumber(cp, 2) + '%</td>';
                                sumIP += formatNumber(ip, 3);
                                sumBM += formatNumber(bm, 3);
                            }
                        }
                    }

                    if (htmlChild != '') {
                        html += '<tr class="color-parent">' +
                                '<td colspan="7">' + parent[p]['goal_name'] + '</td>' +
                                '<td class="text-align-right">' + formatNumber(sumBM, 3) + '</td>' +
                                '<td class="text-align-right">' + formatNumber(sumIP, 3) + '</td>' +
                                '<td></td>' +
                                '</tr>';
                        html += htmlChild;
                    }
                }
                html += '</tbody>' +
                        '</table>';
                $('#tblResultEachArea').append(html);
            });


        }

        function viewMultiArea(fromMonth, fromYear, toMonth, toYear, company, area, areaName) {
            var fromMonth = parseInt(fromMonth);
            var fromYear = parseInt(fromYear);
            var toMonth = parseInt(toMonth);
            var toYear = parseInt(toYear);
            $("html, body").animate({ scrollTop: $(document).height()*2});
            var myNode = document.getElementById("tblResultEachArea");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }
            $('#tblResultEachArea').show();
            var goal = $('#slGoal').val();

            $.get("getDataOfMultiArea", {
                fromMonth: fromMonth, fromYear: fromYear, toMonth: toMonth, toYear: toYear,
                goal: goal, company: company, area: area
            }, function (result) {
                var data = result[0];
                var arrGoal = result[1];
                var countMonth = ((toYear - fromYear) * 12 + (toMonth - fromMonth + 1));

                var html = '<table class="table-common">' +
                        '<thead>' +
                        '<tr>' +
                        '<th colspan="' + (countMonth + 2) + '" class="order-column">' + areaName + '</th>' +
                        '</tr>' +
                        '<tr>' +
                        '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                        '<th class="col-md-3 col-sm-3 order-column">Tên chỉ tiêu</th>';

                if (fromYear < toYear) {
                    for (var f = fromMonth; f < 12; f++) {
                        html += "<th class='order-column'>Tháng " + f + "</th>";
                    }
                    html += "<th class='order-column'>Tháng 12-" + fromYear + "</th>";
                    for (var t = 1; t < toMonth; t++) {
                        html += "<th class='order-column'>Tháng " + t + "</th>";
                    }
                    html += "<th class='order-column'>Tháng" + toMonth + '-' + toYear + "</th>";
                } else if (fromYear == toYear) {
                    for (var n = fromMonth; n <= toMonth; n++) {
                        html += "<th class='order-column'>Tháng " + n + "</th>";
                    }
                }
                html += '</tr>' +
                        '</thead>' +
                        '<tbody>';

                for (var g = 0; g < arrGoal.length; g++) {
                    var isZero = 0;
                    var arrChild = arrGoal[g]['goal_child'];
                    var index = 0;
                    var htmlChild = '';
                    for (var c = 0; c < arrChild.length; c++) {
                        var htmlIP = '';
                        if (fromYear < toYear) {
                            for (var f = fromMonth; f <= 12; f++) {
                                var ip = 0;
                                for (var d = 0; d < data.length; d++) {
                                    if (data[d]['goal_code'] == arrChild[c]['goal_code'] && data[d]['month'] == f && data[d]['year'] == fromYear) {
                                        ip = formatNumber(data[d]['implementPoint'], 3);
                                    }
                                }
                                if (ip > 0) {
                                    isZero = 1;
                                }
                                htmlIP += "<td class='text-align-right'>" + formatNumber(ip, 3) + "</td>";
                            }

                            for (var t = 1; t <= toMonth; t++) {
                                var ip = 0;
                                for (var d = 0; d < data.length; d++) {
                                    if (data[d]['goal_code'] == arrChild[c]['goal_code'] && data[d]['month'] == t && data[d]['year'] == toYear) {
                                        ip = formatNumber(data[d]['implementPoint'], 3);
                                    }
                                }
                                if (ip > 0) {
                                    isZero = 1;
                                }
                                htmlIP += "<td class='text-align-right'>" + formatNumber(ip, 3) + "</td>";
                            }

                        } else if (fromYear == toYear) {
                            for (var n = fromMonth; n <= toMonth; n++) {
                                var ip = 0;
                                for (var d = 0; d < data.length; d++) {
                                    if (data[d]['goal_code'] == arrChild[c]['goal_code'] && data[d]['month'] == n && data[d]['year'] == fromYear) {
                                        ip = formatNumber(data[d]['implementPoint'], 3);
                                    }
                                }
                                if (ip > 0) {
                                    isZero = 1;
                                }
                                htmlIP += "<td class='text-align-right'>" + formatNumber(ip, 3) + "</td>";
                            }
                        }

                        if (isZero == 1) {
                            index++;
                            if (index % 2 == 0) {
                                htmlChild += '<tr class="background-color-smoke">';
                            } else {
                                htmlChild += '<tr>';
                            }
                            htmlChild += '<td class="order-column">' + index + '</td>' +
                                    '<td>' + arrChild[c]['goal_name'] + '</td>';
                            htmlChild += htmlIP + '</tr>';
                        }

                    }

                    if (htmlChild != '') {
                        html += '<tr class="color-parent">' +
                                '<td colspan="2">' + arrGoal[g]['goal_name'] + '</td>';

                        if (fromYear < toYear) {
                            for (var f = fromMonth; f <= 12; f++) {
                                var sumIP = 0;
                                for (var d = 0; d < data.length; d++) {
                                    if (data[d]['parent_id'] == arrGoal[g]['id'] && data[d]['month'] == f && data[d]['year'] == fromYear) {
                                        sumIP += formatNumber(data[d]['implementPoint'], 3);
                                    }
                                }
                                html += "<td class='text-align-right'>" + formatNumber(sumIP, 3) + "</td>";
                            }

                            for (var t = 1; t <= toMonth; t++) {
                                var sumIP = 0;
                                for (var d = 0; d < data.length; d++) {
                                    if (data[d]['parent_id'] == arrGoal[g]['id'] && data[d]['month'] == t && data[d]['year'] == toYear) {
                                        sumIP += formatNumber(data[d]['implementPoint'], 3);
                                    }
                                }
                                html += "<td class='text-align-right'>" + formatNumber(sumIP, 3) + "</td>";
                            }

                        } else if (fromYear == toYear) {
                            for (var n = fromMonth; n <= toMonth; n++) {
                                var sumIP = 0;
                                for (var d = 0; d < data.length; d++) {
                                    if (data[d]['parent_id'] == arrGoal[g]['id'] && data[d]['month'] == n && data[d]['year'] == fromYear) {
                                        sumIP += formatNumber(data[d]['implementPoint'], 3);
                                    }
                                }
                                html += "<td class='text-align-right'>" + formatNumber(sumIP, 3) + "</td>";
                            }
                        }

                        html += '</tr>' + htmlChild;
                    }
                }

                html += '</tbody>' +
                        '</table>';

                $('#tblResultEachArea').append(html);
            });
        }

        function loadDataChangeEmpArea() {
            //change company: change area+ change emp
            var company = $('#slCompany').val();

            $.get("getDataEmpAreaForRKPI", {company: company}, function (result) {
                //delete all child in div have id= cbArea - cbEmp
                var nodeA = document.getElementById("slArea");
                while (nodeA.firstChild) {
                    nodeA.removeChild(nodeA.firstChild);
                }

                if (result != false) {
                    var dataArea = result;
                    var currentArea = <?php echo $currentAreaId ?>;
                    var accessLevel = <?php echo $accessLevel ?>;

                    if(accessLevel > 2){
                        var html = '';
                        for (var a = 0; a < dataArea.length; a++) {
                            if(dataArea[a]['area_code'] == currentArea){
                                html += "<option value='" + dataArea[a]['area_code'] + "'>" + dataArea[a]['area_name'] + "</option>";
                                break;
                            }

                        }
                    } else {
                        var html = '<option value="1" selected>Tất cả</option>';
                        for (var a = 0; a < dataArea.length; a++) {
                            html += "<option value='" + dataArea[a]['area_code'] + "'>" + dataArea[a]['area_name'] + "</option>";
                        }
                    }

                    $('#slArea').append(html);

                } else {
                    var html = '<option value="1" selected>Tất cả</option>';
                    $('#slArea').append(html);
                }
            });
        }

        //handler when click select to year --> 9
        function changeShowByCompany() {
            loadDataChangeEmpArea();
            showData();
        }

        function changeShowByArea() {
            showData();
        }

        function changeShowByGoal() {
            showData();
        }

        $('#slArea').select2();
        $('#slGoal').multipleSelect();
        $('#slCompany').select2();
        setCookie("areaChoose",'');
        setCookie("areaDetailChoose",'');

        $(document).ready(function () {
            showData();
            //edit show on device
            var width = $(window).width();
            if (width < 550) {
                $(".select2").addClass("min-width-325");
                $(".goal").addClass("margin-left-0-18");
                $(".ms-choice").addClass("min-width-98");
            } else {
                $(".goal").remove("margin-left-0-18");
                $(".ms-choice").remove("min-width-98");
                $(".select2").remove("min-width-325");
            }
        });
    </script>
@stop