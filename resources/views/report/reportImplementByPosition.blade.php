@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.REPORT') . ' ' . Config::get('constant.RPT_IMPLEMENT_BY_POSITION'))
@section('section')
@include('widgets.timeOption.sortByTime', array('year' => $year,
                                                'link' => "reloadPageWithParam('{{action('ExportExcelController@reportPositionByTime')}}'
                                                             , 'reportPositionByTime'
                                                             , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/slMonthByToMonth/slYearByFromMonth/slYearByToMonth/slCompanyHide/slAreaHide/slPositionHide')"))
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

                    <div class="col-md-12 btnChoose margin-top-1-12" id="cbComp">
                        <select multiple="multiple" class="margin-top-8 width-104" id="slCompany"
                                onchange="changeShowByCompany();">
                            <?php
                            if($accessLevel > 1){
                                foreach($company as $row){
                                if($row->id == $currentCompanyId){?>
                                    <option value="<?php echo $row->company_code ?>" selected>
                                        <?php echo $row->company_name ?>
                                    </option>
                            <?php break;}}} else {
                                foreach($company as $row){?>
                                <option value="<?php echo $row->company_code ?>" selected>
                                    <?php echo $row->company_name ?>
                                </option>
                                <?php }}?>
                        </select>
                        <input type="hidden" id="slCompanyHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportPositionByTime')}}'
                                       , 'reportPositionByTime'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/slMonthByToMonth/' +
                                       'slYearByFromMonth/slYearByToMonth/slCompanyHide/slAreaHide/slPositionHide')">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12 margin-left-1-3">
                    <b class="font-13 marg-top-title">Tổ/Quận/Huyện</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12" id="cbArea">
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
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportPositionByTime')}}'
                                       , 'reportPositionByTime'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/slMonthByToMonth/' +
                                       'slYearByFromMonth/slYearByToMonth/slCompanyHide/slAreaHide/slPositionHide')">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12 margin-left-1-3">
                    <b class="font-13 marg-top-title">Chức danh</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12" id="cbPos">
                        <select multiple="multiple" class="width-104 margin-top-8" id="slPosition"
                                onchange="changeShowByPosition();">
                            <?php foreach($positions as $row){?>
                            <option value="<?php echo $row->position_code ?>" selected>
                                <?php echo $row->position_name ?>
                            </option>
                            <?php }?>
                        </select>
                        <input type="hidden" id="slPositionHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportPositionByTime')}}'
                                       , 'reportPositionByTime'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/slMonthByToMonth/' +
                                       'slYearByFromMonth/slYearByToMonth/slCompanyHide/slAreaHide/slPositionHide')">
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <button id="btnExport" class="btn btn-primary pull-right margin-left-15"
                            onclick="reloadPageWithParam('{{action('ExportExcelController@reportPositionByTime')}}'
                                    , 'reportPositionByTime'
                                    , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/slMonthByToMonth/' +
                                    'slYearByFromMonth/slYearByToMonth/slCompanyHide/slAreaHide/slPositionHide')">
                        <i class="fa fa-sign-out"></i> Xuất Excel
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
                        <div class="col-sm-12 color-red" id="txtResult" hidden>Không tìm thấy kết quả!</div>
                        <div class="col-sm-12 color-red" id="txtResultCountMonth" hidden>Không thể xem dữ liệu quá 12
                            tháng. Vui lòng xuất excel để xem.
                        </div>
                    </div>

                    <div class="tab-pane fade" id="viewChart">
                        @include('widgets.chartOption.chartOption')
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
    {{ HTML::script('public/assets/scripts/data.js') }}
    {{ HTML::script('public/assets/scripts/exporting.js') }}
    {{ HTML::script('public/assets/scripts/modernizr.js') }}

    <script type="text/javascript">

        //paint charts
        function paintCharts(data, arrayCAPGE, dataGoal, isHide){
            if(isHide == 1){
                var isCheckGTI = 0;
            } else {
                var isCheckGTI = document.getElementById("rdGTI").checked;
            }
            var isCheckRdColumnChart = document.getElementById("rdColumnChart").checked;
            var isCheckGBMIP = document.getElementById("rdGBMIP").checked;
            var isCheckRadarChart = document.getElementById("rdRadarChart").checked;
            var isCheckMIP = document.getElementById("rdMIP").checked;

            var myNode = document.getElementById("chartAjax");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }
            var html = '';

            if(isCheckRdColumnChart){
                for(var p=0; p<arrayCAPGE.length; p++){
                    if (p == (arrayCAPGE.length - 1)) {
                        html += '<div class="col-sm-12">' +
                                "<div id='" + arrayCAPGE[p]['company_code']+arrayCAPGE[p]['area_code']+arrayCAPGE[p]['position_code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    } else {
                        html += '<div class="col-sm-12 marg-bottom-1-100">' +
                                "<div id='" + arrayCAPGE[p]['company_code']+arrayCAPGE[p]['area_code']+arrayCAPGE[p]['position_code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    }
                }

            } else {
                for(var p=0; p<arrayCAPGE.length; p++){
                    html += '<div class="col-sm-12">';
                    html += '<div class="panel panel-default">';
                    html += '<div class="panel-heading">';
                    html += '<h3 class="panel-title">' + arrayCAPGE[p]['company_name']+' - '+arrayCAPGE[p]['area_name']+' - '+arrayCAPGE[p]['position_name']+ '</h3>';
                    html += '</div>';
                    html += '<div class="panel-body">';
                    html += "<canvas id='" +arrayCAPGE[p]['company_code']+arrayCAPGE[p]['area_code']+arrayCAPGE[p]['position_code'] + "' height='220' width='350'></canvas>";
                    html += '</div></div></div>';
                }
            }

            $('#chartAjax').append(html);

            //paint charts
            if (isCheckGTI) {
                //view by goal-target-implement dataChart
                for(var p=0; p<arrayCAPGE.length; p++){
                    var arrayChartColumn = [];
                    var arrayGoal = arrayCAPGE[p]['goal'];
                    var goalChild = '';
                    var iCharts = '';
                    var tvCharts = '';
                    for(var g=0;g<arrayGoal.length; g++){
                        var arrayChild = [];
                        arrayChild.push(arrayGoal[g]['goal_name']);
                        goalChild += '"' + arrayGoal[g]['goal_name'] + '",';
                        for(var d=0; d<dataGoal.length; d++){
                            if(dataGoal[d]['company_code'] == arrayCAPGE[p]['company_code'] &&
                                    dataGoal[d]['area_code'] == arrayCAPGE[p]['area_code'] &&
                                    dataGoal[d]['position_code'] == arrayCAPGE[p]['position_code'] &&
                                    dataGoal[d]['goal_code'] == arrayGoal[g]['goal_code']){
                                var tv = formatNumber(dataGoal[d]['targetValue'], 3);
                                var i = formatNumber(dataGoal[d]['implement'], 3);
                                iCharts += '"' + i + '",';
                                tvCharts += '"' + tv + '",';
                                arrayChild.push(tv);
                                arrayChild.push(i);
                                arrayChartColumn.push(arrayChild);
                            }
                        }
                    }
                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(goalChild, tvCharts, iCharts, arrayCAPGE[p]['company_code']+arrayCAPGE[p]['area_code']+arrayCAPGE[p]['position_code'], 'Kế hoạch', 'Thực hiện');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrayCAPGE[p]['company_code']+arrayCAPGE[p]['area_code']+arrayCAPGE[p]['position_code'],
                                'Kế hoạch', 'Thực hiện', arrayCAPGE[p]['company_name']+' - '+arrayCAPGE[p]['area_name']+' - '+arrayCAPGE[p]['position_name']);
                    } else {
                        paintLineChartTVIByMonthGoal(goalChild, tvCharts, iCharts, arrayCAPGE[p]['company_code']+arrayCAPGE[p]['area_code']+arrayCAPGE[p]['position_code'], 'Kế hoạch', 'Thực hiện');
                    }
                }

            } else if (isCheckGBMIP) {
                // view by goal-implement point
                for(var p=0; p<arrayCAPGE.length; p++){
                    var arrayChartColumn = [];
                    var arrayGoal = arrayCAPGE[p]['goal'];
                    var goalChild = '';
                    var ipCharts = '';
                    var bmCharts = '';
                    for(var t=0; t<arrayGoal.length; t++){
                        var arrayChild = [];
                        arrayChild.push(arrayGoal[t]['goal_name']);
                        goalChild += '"' + arrayGoal[t]['goal_name'] + '",';
                        var ip = 0;
                        var bm = 0;
                        for(var d=0; d<dataGoal.length; d++){
                            if(dataGoal[d]['company_code'] == arrayCAPGE[p]['company_code'] &&
                                    dataGoal[d]['area_code'] == arrayCAPGE[p]['area_code'] &&
                                    dataGoal[d]['position_code'] == arrayCAPGE[p]['position_code'] &&
                                    dataGoal[d]['goal_code'] == arrayGoal[t]['goal_code']){
                                ip += formatNumber(dataGoal[d]['implementPoint'], 3);
                                bm += formatNumber(dataGoal[d]['benchmark'], 3);
                            }
                        }
                        arrayChild.push(ip);
                        arrayChild.push(bm);
                        arrayChartColumn.push(arrayChild);
                        ipCharts += '"' +ip + '",';
                        bmCharts += '"' +bm + '",';
                    }
                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, arrayCAPGE[p]['company_code']+arrayCAPGE[p]['area_code']+arrayCAPGE[p]['position_code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrayCAPGE[p]['company_code']+arrayCAPGE[p]['area_code']+arrayCAPGE[p]['position_code'],
                                'Điểm thực hiện', 'Điểm chuẩn', arrayCAPGE[p]['company_name']+' - '+arrayCAPGE[p]['area_name']+' - '+arrayCAPGE[p]['position_name']);
                    } else {
                        paintLineChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, arrayCAPGE[p]['company_code']+arrayCAPGE[p]['area_code']+arrayCAPGE[p]['position_code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    }
                }
            } else if(isCheckMIP){
                // view by goal-implement point
                for(var p=0; p<arrayCAPGE.length; p++){
                    var arrayChartColumn = [];
                    var time = arrayCAPGE[p]['time'];
                    var timeStr = '';
                    var ipCharts = '';
                    var bmCharts = '';
                    for(var t=0; t<time.length; t++){
                        var arrayChild = [];
                        arrayChild.push('Tháng_'+time[t]['month']);
                        timeStr += '"Tháng_' + time[t]['month'] + '",';
                        var ip = 0;
                        var bm = 0;
                        for(var d=0; d<data.length; d++){
                            if(data[d]['company_code'] == arrayCAPGE[p]['company_code'] &&
                                    data[d]['area_code'] == arrayCAPGE[p]['area_code'] &&
                                    data[d]['position_code'] == arrayCAPGE[p]['position_code'] &&
                                    data[d]['month'] == time[t]['month'] &&
                                    data[d]['year'] == time[t]['year']){
                                ip += formatNumber(data[d]['implementPoint'], 3);
                                bm += formatNumber(data[d]['benchmark'], 3);
                            }
                        }
                        arrayChild.push(ip);
                        arrayChild.push(bm);
                        arrayChartColumn.push(arrayChild);
                        ipCharts += '"' + ip + '",';
                        bmCharts += '"' + bm + '",';
                    }
                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(timeStr, ipCharts, bmCharts, arrayCAPGE[p]['company_code']+arrayCAPGE[p]['area_code']+arrayCAPGE[p]['position_code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrayCAPGE[p]['company_code']+arrayCAPGE[p]['area_code']+arrayCAPGE[p]['position_code'],
                                'Điểm thực hiện', 'Điểm chuẩn', arrayCAPGE[p]['company_name']+' - '+arrayCAPGE[p]['area_name']+' - '+arrayCAPGE[p]['position_name']);
                    } else {
                        paintLineChartTVIByMonthGoal(timeStr, ipCharts, bmCharts, arrayCAPGE[p]['company_code']+arrayCAPGE[p]['area_code']+arrayCAPGE[p]['position_code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    }
                }
            } else {
                var myNode = document.getElementById("chartAjax");
                while (myNode.firstChild) {
                    myNode.removeChild(myNode.firstChild);
                }
            }
        }

        //call ajax to load pages to get implement_point of company
        function showData() {
            showStuff('loader');
            var isHide = 0;
            $('#divRdGTI').show();
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
            var position = $('#slPosition').val();
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

            var elemP = document.getElementById("slAreaHide");
            elemP.value = area;

            var elemP = document.getElementById("slCompanyHide");
            elemP.value = company;
            //radio button tháng is checked
            if (isCheckMonth) {
                $('#divRdGTI').hide();
                isHide = 1;
                var elements = document.getElementsByName('view');

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
                    if (area == ' ' || area == null) {
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
                    // get data of time
                    var node = document.getElementById("tblResult");
                    while (node.firstChild) {
                        node.removeChild(node.firstChild);
                    }
                    //get data
                    $.get("getDataByTimeForPosition", {
                        fromMonth: fromMonth, fromYear: fromYear, toMonth: toMonth, toYear: toYear,
                        company: company, area: area, position: position
                    }, function (result) {
                        if (result == false) {
                            $('#tblResult').hide();
                            $('#txtResult').show();
                        } else {
                            $('#txtResult').hide();
                            $('#btnExport').prop("disabled", false);

                            var data = result[0];
                            var arrayCAPGE = result[2];
                            var myNode = document.getElementById("tblResult");
                            while (myNode.firstChild) {
                                myNode.removeChild(myNode.firstChild);
                            }
                            var html = '';
                            if (fromMonth == toMonth && fromYear == toYear) {
                                $('#divRdGTI').show();
                                isHide = 0;
                                var arrayAVG = [];
                                for (var da = 0; da < data.length; da++) {
                                    var arrayChild = [];
                                    var AVG = formatNumber(data[da]['implementPoint'],3);
                                    arrayChild.push(data[da]['company_code']);
                                    arrayChild.push(data[da]['area_code']);
                                    arrayChild.push(data[da]['position_code']);
                                    arrayChild.push(AVG);
                                    arrayAVG.push(arrayChild);
                                }

                                arrayAVG.sort(function(a, b){
                                    return b[3]-a[3]
                                });

                                var oldValue = -1;
                                var arrayAVGSort = [];
                                var rank = 1;
                                for(var av = 0; av < arrayAVG.length; av++){
                                    var arrayChildSort = [];
                                    if(arrayAVG[av][3] < oldValue){
                                        rank = arrayAVGSort.length +1;
                                    }
                                    oldValue = arrayAVG[av][3];
                                    arrayChildSort.push(arrayAVG[av][0]);
                                    arrayChildSort.push(arrayAVG[av][1]);
                                    arrayChildSort.push(arrayAVG[av][2]);
                                    arrayChildSort.push(arrayAVG[av][3]);
                                    arrayChildSort.push(rank);
                                    arrayAVGSort.push(arrayChildSort);
                                }

                                //do something
                                var html = "<table class='table-common'>" +
                                        "<thead>" +
                                        "<tr>" +
                                        "<th class='col-md-1 col-xs-1' >STT</th>" +
                                        "<th class='col-md-2 col-xs-2'>Phòng/Đài/MBF HCM</th>" +
                                        "<th class='col-md-2 col-xs-2'>Tổ/Quận/Huyện</th>" +
                                        "<th class='col-md-2 col-xs-2'>Chức danh</th>" +
                                        "<th class='col-md-2 col-xs-2'>Tháng " + month + "</th>" +
                                        "<th class='col-md-2 col-xs-2'>Điểm trung bình</th>" +
                                        "<th class='col-md-1 col-xs-1'>Xếp hạng</th>" +
                                        "</tr>" +
                                        "</thead>" +
                                        "<tbody>";
                                for (var d = 0; d < data.length; d++) {
                                    var AVG = formatNumber(data[d]['implementPoint'],3);
                                    if ((d + 1) % 2 == 0) {
                                        html += '<tr class="background-color-smoke">';
                                    } else {
                                        html += '<tr>';
                                    }
                                    var rank = 0;
                                    for(var s = 0; s < arrayAVGSort.length; s++){
                                        if(data[d]['company_code'] == arrayAVGSort[s][0] &&
                                                data[d]['area_code'] == arrayAVGSort[s][1] &&
                                                data[d]['position_code'] == arrayAVGSort[s][2] ){
                                            rank = arrayAVGSort[s][4];
                                        }
                                    }
                                    html += '<td class="order-column">' + (d + 1) + '</td>' +
                                            '<td>' + data[d]['company_name'] + '</td>' +
                                            '<td>' + data[d]['area_name'] + '</td>' +
                                            '<td>' + data[d]['position_name'] + '</td>' +
                                            '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(data[d]['implementPoint'], 3) + '</td>' +
                                            '<td class="text-align-right" title="Điểm trung bình của điểm thực hiện của tháng">' + formatNumber(AVG, 3) + '</td>' +
                                            '<td class="order-column" title="Xếp hạng">' + rank + '</td>' +
                                            '</tr>';
                                }
                                html += "</tbody>" +
                                        "</table>";
                            } else {

                                //sort
                                var arrayAVG = [];
                                for (var d = 0; d < arrayCAPGE.length; d++) {
                                    var time =  arrayCAPGE[d]['time'];
                                    var totalIP = 0;

                                    for (var da = 0; da < data.length; da++) {
                                        if ((data[da]['company_code'] == arrayCAPGE[d]['company_code']) &&
                                                (data[da]['area_code'] == arrayCAPGE[d]['area_code']) &&
                                                (data[da]['position_code'] == arrayCAPGE[d]['position_code'])) {
                                            totalIP += formatNumber(data[da]['implementPoint'], 3);
                                        }
                                    }
                                    var arrayChild = [];
                                    var AVG = formatNumber((totalIP / time.length), 3);
                                    arrayChild.push(arrayCAPGE[d]['company_code']);
                                    arrayChild.push(arrayCAPGE[d]['area_code']);
                                    arrayChild.push(arrayCAPGE[d]['position_code']);
                                    arrayChild.push(AVG);
                                    arrayAVG.push(arrayChild);
                                }

                                arrayAVG.sort(function(a, b){
                                    return b[3]-a[3]
                                });

                                var oldValue = -1;
                                var arrayAVGSort = [];
                                var rank = 1;
                                for(var av = 0; av < arrayAVG.length; av++){
                                    var arrayChildSort = [];
                                    if(arrayAVG[av][3] < oldValue){
                                        rank = arrayAVGSort.length +1;
                                    }
                                    oldValue = arrayAVG[av][3];
                                    arrayChildSort.push(arrayAVG[av][0]);
                                    arrayChildSort.push(arrayAVG[av][1]);
                                    arrayChildSort.push(arrayAVG[av][2]);
                                    arrayChildSort.push(arrayAVG[av][3]);
                                    arrayChildSort.push(rank);
                                    arrayAVGSort.push(arrayChildSort);
                                }


                                html += "<table class='table-common'>" +
                                        "<thead>" +
                                        "<tr>" +
                                        "<th class='order-column width-4'>STT</th>" +
                                        "<th class='width-12'>Phòng/ Đài/ MBF HCM</th>" +
                                        "<th class='width-6'>Tổ/ Quận/ Huyện</th>" +
                                        "<th class='width-6'>Chức danh</th>";
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

                                html += "<th>Điểm trung bình</th>" +
                                        "<th>Xếp hạng</th>" +
                                        "</tr>" +
                                        "</thead>" +
                                        "<tbody>";
                                for (var d = 0; d < arrayCAPGE.length; d++) {
                                    var totalIP = 0;
                                    if ((d + 1) % 2 == 0) {
                                        html += '<tr class="background-color-smoke">';
                                    } else {
                                        html += '<tr>';
                                    }
                                    html += '<td class="order-column width-4">' + (d + 1) + '</td>' +
                                            '<td class="width-12">' + arrayCAPGE[d]['company_name'] + '</td>' +
                                            '<td class="width-6">' + arrayCAPGE[d]['area_name'] + '</td>' +
                                            '<td class="width-6">' + arrayCAPGE[d]['position_name'] + '</td>' ;
                                    if (fromYear < toYear) {
                                        for (var f = fromMonth; f <= 12; f++) {
                                            var implementPoint = 0;
                                            for (var da = 0; da < data.length; da++) {
                                                if ((data[da]['month'] == f) && (data[da]['year'] == fromYear) &&
                                                        (data[da]['company_code'] == arrayCAPGE[d]['company_code']) &&
                                                        (data[da]['area_code'] == arrayCAPGE[d]['area_code']) &&
                                                        (data[da]['position_code'] == arrayCAPGE[d]['position_code'])) {
                                                    implementPoint = formatNumber(data[da]['implementPoint'], 3);
                                                    totalIP += implementPoint;
                                                }
                                            }
                                            html += '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(implementPoint, 3) + '</td>';
                                        }
                                        for (var t = 1; t <= toMonth; t++) {
                                            var implementPoint = 0;
                                            for (var da = 0; da < data.length; da++) {
                                                if (data[da]['month'] == t && data[da]['year'] == toYear &&
                                                        data[da]['company_code'] == arrayCAPGE[d]['company_code'] &&
                                                        data[da]['area_code'] == arrayCAPGE[d]['area_code'] &&
                                                        data[da]['position_code'] == arrayCAPGE[d]['position_code']) {
                                                    implementPoint = formatNumber(data[da]['implementPoint'], 3);
                                                    totalIP += implementPoint;
                                                }
                                            }
                                            html += '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(implementPoint, 3) + '</td>';
                                        }
                                    } else if (fromYear == toYear) {
                                        for (var n = fromMonth; n <= toMonth; n++) {
                                            var implementPoint = 0;
                                            for (var da = 0; da < data.length; da++) {
                                                if ((data[da]['month'] == n) && (data[da]['year'] == toYear) &&
                                                        (data[da]['company_code'] == arrayCAPGE[d]['company_code']) &&
                                                        (data[da]['area_code'] == arrayCAPGE[d]['area_code']) &&
                                                        (data[da]['position_code'] == arrayCAPGE[d]['position_code'])) {
                                                    implementPoint = formatNumber(data[da]['implementPoint'], 3);
                                                    totalIP += implementPoint;
                                                }
                                            }
                                            html += '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(implementPoint, 3) + '</td>';
                                        }
                                    }
                                    var AVG = 0;
                                    var rank = 0;
                                    for(var s = 0; s < arrayAVGSort.length; s++){
                                        if(arrayCAPGE[d]['company_code'] == arrayAVGSort[s][0] &&
                                                arrayCAPGE[d]['area_code'] == arrayAVGSort[s][1] &&
                                                arrayCAPGE[d]['position_code'] == arrayAVGSort[s][2] ){
                                            rank = arrayAVGSort[s][4];
                                            AVG = arrayAVGSort[s][3];
                                        }
                                    }
                                    html += '<td class="text-align-right" title="Điểm trung bình của điểm thực hiện của tháng">' + formatNumber(AVG, 3) + '</td>' +
                                            '<td class="order-column" title="Điểm trung bình của điểm thực hiện của tháng">' + rank + '</td>' +
                                            '</tr>';
                                }
                                html += "</tbody>" +
                                        "</table>";
                            }
                            $('#tblResult').append(html);
                            var dataGoal = result[1];
                            paintCharts(data, arrayCAPGE, dataGoal, isHide);
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

                $.get("getDataByTimeForPosition", {
                    month: month, year: year, company: company, area: area, position: position
                }, function (result) {
                    if (result == false) {
                        $('#tblResult').hide();
                        $('#txtResult').show();
                    } else {
                        $('#txtResult').hide();
                        $('#btnExport').prop("disabled", false);
                        var myNode = document.getElementById("tblResult");
                        while (myNode.firstChild) {
                            myNode.removeChild(myNode.firstChild);
                        }
                        var data = result[0];
                        var arrayAVG = [];
                        for (var da = 0; da < data.length; da++) {
                            var arrayChild = [];
                            var AVG = formatNumber(data[da]['implementPoint'],3);
                            arrayChild.push(data[da]['company_code']);
                            arrayChild.push(data[da]['area_code']);
                            arrayChild.push(data[da]['position_code']);
                            arrayChild.push(AVG);
                            arrayAVG.push(arrayChild);
                        }

                        arrayAVG.sort(function(a, b){
                            return b[3]-a[3]
                        });

                        var oldValue = -1;
                        var arrayAVGSort = [];
                        var rank = 1;
                        for(var av = 0; av < arrayAVG.length; av++){
                            var arrayChildSort = [];
                            if(arrayAVG[av][3] < oldValue){
                                rank = arrayAVGSort.length +1;
                            }
                            oldValue = arrayAVG[av][3];
                            arrayChildSort.push(arrayAVG[av][0]);
                            arrayChildSort.push(arrayAVG[av][1]);
                            arrayChildSort.push(arrayAVG[av][2]);
                            arrayChildSort.push(arrayAVG[av][3]);
                            arrayChildSort.push(rank);
                            arrayAVGSort.push(arrayChildSort);
                        }
                        //do something
                        var html = "<table class='table-common'>" +
                                "<thead>" +
                                "<tr>" +
                                "<th class='col-md-1 col-xs-1' >STT</th>" +
                                "<th class='col-md-2 col-xs-2'>Phòng/Đài/MBF HCM</th>" +
                                "<th class='col-md-2 col-xs-2'>Tổ/Quận/Huyện</th>" +
                                "<th class='col-md-2 col-xs-2'>Chức danh</th>" +
                                "<th class='col-md-2 col-xs-2'>Tháng " + month + "</th>" +
                                "<th class='col-md-2 col-xs-2'>Điểm trung bình</th>" +
                                "<th class='col-md-1 col-xs-1'>Xếp hạng</th>" +
                                "</tr>" +
                                "</thead>" +
                                "<tbody>";
                        for (var d = 0; d < data.length; d++) {
                            var AVG = formatNumber(data[d]['implementPoint'],3);
                            if ((d + 1) % 2 == 0) {
                                html += '<tr class="background-color-smoke">';
                            } else {
                                html += '<tr>';
                            }
                            var rank = 0;
                            for(var s = 0; s < arrayAVGSort.length; s++){
                                if(data[d]['company_code'] == arrayAVGSort[s][0] &&
                                        data[d]['area_code'] == arrayAVGSort[s][1] &&
                                        data[d]['position_code'] == arrayAVGSort[s][2] ){
                                    rank = arrayAVGSort[s][4];
                                }
                            }
                            html += '<td class="order-column">' + (d + 1) + '</td>' +
                                    '<td>' + data[d]['company_name'] + '</td>' +
                                    '<td>' + data[d]['area_name'] + '</td>' +
                                    '<td>' + data[d]['position_name'] + '</td>' +
                                    '<td class="text-align-right" title="Điểm thực hiện của tháng">' + formatNumber(data[d]['implementPoint'], 3) + '</td>' +
                                    '<td class="text-align-right" title="Điểm trung bình của điểm thực hiện của tháng">' + formatNumber(AVG, 3) + '</td>' +
                                    '<td class="order-column" title="Xếp hạng">' + rank + '</td>' +
                                    '</tr>';
                        }
                        html += "</tbody>" +
                                "</table>";
                        $('#tblResult').append(html);
                        var arrayCAPGE = result[2];
                        var dataGoal= result[1];
                        paintCharts(data, arrayCAPGE, dataGoal, 0);
                    }
                });
            }
        }

        function loadDataChangeEmpArea() {
            //change company: change area+ change emp
            var company = $('#slCompany').val();

            $.get("getDataEmpAreaForPosition", {company: company}, function (result) {
                //delete all child in div have id= cbArea - cbEmp
                var nodeA = document.getElementById("slArea");
                while (nodeA.firstChild) {
                    nodeA.removeChild(nodeA.firstChild);
                }

                if (result != false) {
                    var dataArea = result[0];

                    var currentArea = "<?php echo $currentAreaId ?>";
                    var accessLevel = "<?php echo $accessLevel ?>";

                    if(accessLevel > 2){
                        var html = '';
                        for (var a = 0; a < dataArea.length; a++) {
                            if(currentArea == dataArea[a]['id']){
                                html += "<option value='" + dataArea[a]['area_code'] + "' selected>" + dataArea[a]['area_name'] + "</option>";
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

        function changeShowByPosition() {
            showData();
        }

        $('#slArea').select2();
        $('#slCompany').multipleSelect();
        $('#slPosition').multipleSelect();
        setCookie("areaChoose",'');
        setCookie("areaDetailChoose",'');

        $(document).ready(function () {
            loadDataChangeEmpArea();
            showData();
            var width = $(window).width();
            if (width < 550) {
                $(".select2").addClass("min-width-325");
                $(".ms-choice").addClass("min-width-98");

            } else {
                $(".ms-choice").remove("min-width-98");
                $(".select2").remove("min-width-325");
            }
        });

    </script>
@stop
