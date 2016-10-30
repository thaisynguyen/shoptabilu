@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.REPORT'). ' ' .Config::get('constant.RPT_DETAIL_BY_POSITION'))
@section('section')
    @include('alerts.errors')
    @include('widgets.timeOption.sortByTime', array('year' => $year,
                                                    'link' => "reloadPageWithParam('{{action('ExportExcelController@reportDetailByPosition')}}'
                                                                         , 'reportDetailByPosition'
                                                                         , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/slMonthByToMonth/slYearByFromMonth/slYearByToMonth/slCompany/slArea/slPositionHide/slGoalHide/codeChoose')"))
    <?php
    $currentCompanyId = Session::get('scompany_id');
    $currentAreaId = Session::get('sarea_id');
    $accessLevel = Session::get('saccess_level');
    ?>
    <div id="wrapper" >
        <div class="row margin-form">
            <div class="col-md-12">
                <div class="col-md-3 col-xs-12">
                    <b class="font-13 marg-top-title">Phòng/Đài/MBF HCM</b><br>
                    <div class="col-md-12 btnChoose margin-top-1-12 margin-left-015" id="divSlCompany">
                        <select  class="form-control margin-top-8 width-104" id="slCompany" onchange="changeShowByCompany();"
                                 onchange="reloadPageWithParam('{{action('ExportExcelController@reportDetailByPosition')}}'
                                         , 'reportDetailByPosition'
                                         , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                         'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                         'slCompany/slArea/slPositionHide/slGoalHide/codeChoose')">
                            <?php
                            if($accessLevel > 1){
                            foreach($company as $row){
                                if($currentCompanyId == $row->id){?>
                                    <option value="<?php echo $row->company_code ?>">
                                        <?php echo $row->company_name ?>
                                    </option>
                            <?php break; }}} else {
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
                    <div class="col-md-12 btnChoose combobox-margin-left-area margin-top-1-12 margin-left-0-18" id="cbArea">
                        <select  class="form-control margin-top-8 width-104" id="slArea" onchange="changeShowByArea();"
                                 onchange="reloadPageWithParam('{{action('ExportExcelController@reportDetailByPosition')}}'
                                         , 'reportDetailByPosition'
                                         , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                         'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                         'slCompany/slArea/slPositionHide/slGoalHide/codeChoose')">
                            <?php
                            if(isset($_COOKIE['areaDetailChoose']) && $_COOKIE['areaDetailChoose'] != '') {
                            if(isset($area)){
                            foreach($area as $a){
                            if($a->area_code == $_COOKIE['areaDetailChoose']){ ?>
                            <option value="<?php echo $_COOKIE['areaDetailChoose']?>"><?php echo $a->area_name ?></option>
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

                <div class="col-md-3 col-xs-12">
                    <b class="font-13 margin-left-1-3">Chức danh</b><br>
                    <div class="col-md-12 margin-top-1-12" id="divPos">
                        <select multiple="multiple" class="margin-top-8 width-104" id="slPosition" onchange="changeShowByPosition();">
                            <?php foreach($positions as $row){?>
                            <option value="<?php echo $row->position_code ?>" selected>
                                <?php echo $row->position_name ?>
                            </option>
                            <?php }?>
                        </select>
                        <input type="hidden" id="slPositionHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportDetailByPosition')}}'
                                       , 'reportDetailByPosition'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slArea/slPositionHide/slGoalHide/codeChoose')">

                        <input type="hidden" id="codeChoose" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportDetailByPosition')}}'
                                       , 'reportDetailByPosition'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slArea/slPositionHide/slGoalHide/codeChoose')">
                    </div>
                </div>

                <div class="col-md-3  col-xs-12">
                    <b class="font-13 marg-top-title">Mục tiêu</b><br>
                    <div class="col-md-12 btnChoose margin-top-1-12" ID="cbEmp">
                        <select multiple="multiple" class="margin-top-8 width-104" id="slGoal" onchange="changeShowByGoal();">
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
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportDetailByPosition')}}'
                                       , 'reportDetailByPosition'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                       'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                       'slCompany/slArea/slPositionHide/slGoalHide/codeChoose')">
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <button id="btnExport" class="btn btn-primary pull-right margin-left-15"
                            onclick="reloadPageWithParam('{{action('ExportExcelController@reportDetailByPosition')}}'
                                    , 'reportDetailByPosition'
                                    , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                    'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                    'slCompany/slArea/slPositionHide/slGoalHide/codeChoose')"><i
                                class="fa fa-sign-out"></i> Xuất Excel</button>
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
                        <div class="col-sm-12" id="tblResultEachPos"></div>
                        <div class="col-sm-12 color-red" id="txtResult" hidden>Không tìm thấy kết quả!</div>
                        <div class="col-sm-12 color-red" id="txtResultCountMonth" hidden>Không thể xem dữ liệu quá 12 tháng. Vui lòng xuất excel để xem!.</div>
                    </div>

                    <div class="tab-pane fade"  id="viewChart">
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
    <div class="modal fade gray-darker " id="warningWhenShowData" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
        <div class="modal-dialog" role="document">
            <div class="modal-content ">
                <div class="modal-body">
                    <a type="button" data-dismiss="modal" style=" margin-bottom: 5%;margin-right: 1%; z-index: 20; cursor: pointer;">
                        <span class="glyphicon glyphicon-remove pull-right white"  aria-hidden="true"></span>
                    </a>
                    <div class="row" id="content" ></div>
                </div>
                <div class="modal-footer" id="btnExport">
                    <button type="button" class="btn btn-default btn-primary" data-dismiss="modal" id="btnExportInformation">Đồng ý</button>
                    <button type="button" class="btn btn-default btn-primary btnExit" data-dismiss="modal" id="btnExit">Thoát</button>
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

    <script type="text/javascript">

        //paint chart
        function paintCharts(arrayPosition, dataGoal, data, isHide, dataBM){
            if(isHide == 1){
                var isCheckGTI = 0;
            } else {
                var isCheckGTI = document.getElementById("rdGTI").checked;
            }

            var isCheckGBMIP = document.getElementById("rdGBMIP").checked;
            var isCheckMIP = document.getElementById("rdMIP").checked;

            var isCheckRadarChart = document.getElementById("rdRadarChart").checked;
            var isCheckRdColumnChart = document.getElementById("rdColumnChart").checked;
            var isCheckCkbSummary = document.getElementById("ckbSummaryChart").checked;

            var myNode = document.getElementById("chartAjax");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }
            var html = '';
            if(isCheckRdColumnChart){
                if(isCheckCkbSummary && isCheckGTI == 0){
                    html += '<div class="col-sm-12">' +
                            "<div id='summary' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                }
                for(var p=0; p<arrayPosition.length; p++){
                    if (p == (arrayPosition.length - 1)) {
                        html += '<div class="col-sm-12">' +
                                "<div id='" + arrayPosition[p]['position_code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    } else {
                        html += '<div class="col-sm-12 marg-bottom-1-100">' +
                                "<div id='" + arrayPosition[p]['position_code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
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
                for(var p=0; p<arrayPosition.length; p++){
                    html += '<div class="col-sm-12">';
                    html += '<div class="panel panel-default">';
                    html += '<div class="panel-heading">';
                    html += '<h3 class="panel-title">' + arrayPosition[p]['position_name']+ '</h3>';
                    html += '</div>';
                    html += '<div class="panel-body">';
                    html += "<canvas id='" +  arrayPosition[p]['position_code'] + "' height='220' width='350'></canvas>";
                    html += '</div></div></div>';
                }
            }

            $('#chartAjax').append(html);

            //paint summary
            if(isCheckCkbSummary && isCheckGTI == 0){
                var posStr = '';
                var ipCharts = '';
                var bmCharts = '';
                var arrayChartColumn = [];
                for(var p=0; p<arrayPosition.length; p++){
                    var child = [];
                    child.push(arrayPosition[p]['position_name']);
                    posStr += '"' + arrayPosition[p]['position_name'] + '",';
                    var ip = 0;

                    for(var d=0; d<data.length; d++){
                        if(data[d]['position_code'] == arrayPosition[p]['position_code']){
                            ip += data[d]['implementPoint'];
                        }
                    }
                     var bm = 0;
                    for(var d=0; d<dataBM .length; d++){
                        if(dataBM [d]['position_code'] == arrayPosition[p]['position_code']){
                            bm = dataBM[d]['bm'];
                        }
                    }

                    child.push(ip);
                    child.push(bm);
                    arrayChartColumn.push(child);
                    ipCharts += '"' + formatNumber(ip, 3) + '",';
                    bmCharts += '"' + formatNumber(bm, 3) + '",';
                }

                if(isCheckRadarChart){
                    paintChartTVIByMonthGoal(posStr, ipCharts, bmCharts, 'summary', 'Điểm thực hiện', 'Điểm chuẩn');
                } else if(isCheckRdColumnChart){
                    paintChartColumnIPBM(arrayChartColumn, 'summary', 'Điểm thực hiện', 'Điểm chuẩn', 'Biểu Đồ Tổng Quan');
                } else {
                    paintLineChartTVIByMonthGoal(posStr, ipCharts, bmCharts, 'summary', 'Điểm thực hiện', 'Điểm chuẩn');
                }
            }

            //pain charts
            if (isCheckGTI) {
                //view by goal-target-implement dataChart
                for(var p=0; p<arrayPosition.length; p++){
                    var arrayChartColumn = [];
                    var arrayGoal = arrayPosition[p]['goal'];
                    var goalChild = '';
                    var iCharts = '';
                    var tvCharts = '';
                    for(var g=0;g<arrayGoal.length; g++){
                        var arrayChild = [];
                        arrayChild.push(arrayGoal[g]['goal_name']);
                        goalChild += '"' + arrayGoal[g]['goal_name'] + '",';
                        for(var d=0; d<dataGoal.length; d++){
                            if(dataGoal[d]['position_code'] == arrayPosition[p]['position_code'] &&
                                    dataGoal[d]['goal_code'] == arrayGoal[g]['goal_code']){
                                var i = formatNumber(dataGoal[d]['implement'], 3);
                                var tv = formatNumber(dataGoal[d]['targetValue'], 3);
                                arrayChild.push(tv);
                                arrayChild.push(i);
                                arrayChartColumn.push(arrayChild);
                                iCharts += '"' + i+ '",';
                                tvCharts += '"' + tv + '",';
                            }
                        }
                    }
                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(goalChild, tvCharts, iCharts, arrayPosition[p]['position_code'], 'Kế hoạch', 'Thực hiện' );
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrayPosition[p]['position_code'], 'Kế hoạch', 'Thực hiện', arrayPosition[p]['position_name']);
                    } else {
                        paintLineChartTVIByMonthGoal(goalChild, tvCharts, iCharts, arrayPosition[p]['position_code'], 'Kế hoạch', 'Thực hiện' );
                    }
                }

            } else if (isCheckGBMIP) {
                // view by goal-implement point
                for(var p=0; p<arrayPosition.length; p++){
                    var arrayChartColumn = [];
                    var arrayGoal = arrayPosition[p]['goal'];
                    var goalChild = '';
                    var ipCharts = '';
                    var bmCharts = '';
                    for(var t=0;t<arrayGoal.length; t++){
                        var arrayChild = [];
                        arrayChild.push(arrayGoal[t]['goal_name']);
                        goalChild += '"' + arrayGoal[t]['goal_name'] + '",';
                        var ip = 0;
                        var bm = 0;
                        for(var d=0; d<dataGoal.length; d++){
                            if(dataGoal[d]['position_code'] == arrayPosition[p]['position_code'] &&
                                    dataGoal[d]['goal_code'] == arrayGoal[t]['goal_code']){
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
                        paintChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, arrayPosition[p]['position_code'], 'Điểm thực hiện', 'Điểm chuẩn' );
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrayPosition[p]['position_code'], 'Điểm thực hiện', 'Điểm chuẩn', arrayPosition[p]['position_name']);
                    } else {
                        paintLineChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, arrayPosition[p]['position_code'], 'Điểm thực hiện', 'Điểm chuẩn' );
                    }
                }
            } else if(isCheckMIP){
                for(var p=0; p<arrayPosition.length; p++){
                    var arrayChartColumn = [];
                    var time = arrayPosition[p]['time'];
                    var timeStr = '';
                    var ipCharts = '';
                    var bmCharts = '';
                    for(var t=0; t<time.length; t++){
                        var arrayChild = [];
                        arrayChild.push('Tháng'+time[t]['month']);
                        timeStr += '"Tháng ' + time[t]['month'] + '",';
                        var ip = 0;
                        var bm = 0;
                        for(var d=0; d<data.length; d++){
                            if(data[d]['position_code'] == arrayPosition[p]['position_code'] &&
                                    data[d]['month'] == time[t]['month'] &&
                                    data[d]['year'] == time[t]['year'] ){
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
                        paintChartTVIByMonthGoal(timeStr, ipCharts, bmCharts, arrayPosition[p]['position_code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrayPosition[p]['position_code'], 'Điểm thực hiện', 'Điểm chuẩn', arrayPosition[p]['position_name']);
                    } else {
                        paintLineChartTVIByMonthGoal(timeStr, ipCharts, bmCharts, arrayPosition[p]['position_code'], 'Điểm thực hiện', 'Điểm chuẩn');
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
        function showData(){
            var isHide = 0;
            $('#divRdGTI').show();
            $('#tblResultEachPos').hide();
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
            var area = getCookie("areaDetailChoose").toString().trim();
            var position = $('#slPosition').val();
            var goal = $('#slGoal').val();

            var isCheckMonth = document.getElementById("rdSelectByFromMonth").checked;
            var strPosition = '';
            if(position != null) {
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

            var elemP = document.getElementById("slGoalHide");
            elemP.value = goal;

            if(isCheckMonth) {
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
                } else if(area == 'No') {
                    $('#btnExport').prop("disabled", true);
                    $('#tblResult').hide();
                    $('#txtResult').show();
                    $('#txtResultCountMonth').hide();
                } else  if (countMonth > 12) {
                    $('#btnExport').prop("disabled", false);
                    $('#tblResult').hide();
                    $('#txtResult').hide();
                    $('#txtResultCountMonth').show();
                } else {
                    $('#btnExport').prop("disabled", false);
                    $('#tblResult').show();
                    $('#txtResult').hide();
                    $('#txtResultCountMonth').hide();

                    var node = document.getElementById("tblResult");
                    while (node.firstChild) {
                        node.removeChild(node.firstChild);
                    }
                    //get data
                    $.get("getDataOfReportDetailByPosition", {
                        fromMonth: fromMonth, fromYear: fromYear, toMonth: toMonth, toYear: toYear,
                        company: company, area: area,
                        position: position, goal: goal
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
                            var data = result[0];
                            var arrayPosition = result[1];
                            var dataGoal = result[2];
                            var dataBM = result[3];
                            // show data of table
                            if(fromMonth == toMonth && fromYear == toYear){
                                $('#divRdGTI').show();
                                isHide = 0;
                                var html = '<table class="table-common">'+
                                        '<thead>'+
                                        '<th class="col-md-1 col-sm-1 order-column">STT</th>'+
                                        '<th class="col-md-5 col-sm-5 order-column">Chức danh</th>'+
                                        '<th class="col-md-5 col-sm-5">Tháng '+month+'</th>'+
                                        '<th class="col-md-1 col-sm-1 order-column">Xuất</th>'+
                                        '</tr>'+
                                        '</thead>'+
                                        '<tbody>';
                                for(var d = 0; d < data.length; d++){
                                    if ((d + 1) % 2 == 0) {
                                        html += '<tr class="background-color-smoke">';
                                    } else {
                                        html += '<tr>';
                                    }
                                    html += '<td class="order-column">'+(d+1)+'</td>' +
                                            '<td><a onclick="viewEachPosition(\'' + fromMonth + '\', \'' + fromYear + '\', \'' + company + '\', \'' + area + '\', \'' + data[d]['position_code'] + '\', \'' + data[d]['position_name'] + '\');" ' +
                                            'role="button" title="Xem chi tiết">'+
                                            data[d]['position_name']
                                            +'</a></td>' +
                                            '<td class="text-align-right" title="Điểm thực hiện của tháng">'+ formatNumber(data[d]['implementPoint'], 3)+'</td>' +
                                            '<td class="order-column" title="Chọn chức danh để xuất dữ liệu">' +
                                            '<input type="checkbox" class="ckbExport" onclick="clickCheckBox();" value="'+data[d]['position_code']+'">' +
                                            '</td></tr>';
                                }

                                html += '</tbody>' +
                                        '</table>';
                                $('#tblResult').append(html);

                            } else {
                                var elements = document.getElementsByName('view');

                                var html = '<table class="table-common">'+
                                        '<thead>'+
                                        '<th class="col-md-1 col-sm-1 order-column">STT</th>'+
                                        '<th class="col-md-5 col-sm-5 order-column">Chức danh</th>';
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
                                html += '<th class="col-md-1 col-sm-1 order-column">Xuất</th>'+
                                        '</tr>'+
                                        '</thead>'+
                                        '<tbody>';
                                for(var p = 0; p < arrayPosition.length; p++){
                                    if ((d + 1) % 2 == 0) {
                                        html += '<tr class="background-color-smoke">';
                                    } else {
                                        html += '<tr>';
                                    }
                                    html += '<td class="order-column">'+(p+1)+'</td>' +
                                            '<td><a onclick="viewMultiPosition(\'' + fromMonth + '\', \'' + fromYear + '\', \'' + toMonth + '\', \'' + toYear + '\', \'' + company + '\', \'' + area + '\',' +
                                            ' \'' + arrayPosition[p]['position_code'] + '\', \'' + arrayPosition[p]['position_name'] + '\');" ' +
                                            'role="button" title="Xem chi tiết">'+
                                            arrayPosition[p]['position_name']
                                            +'</a>' +
                                            '</td>';

                                    if (fromYear < toYear) {
                                        for (var f = fromMonth; f <= 12; f++) {
                                            var ip = 0;
                                            for(var d = 0; d < data.length; d++){
                                                if(data[d]['position_code'] == arrayPosition[p]['position_code'] &&
                                                        data[d]['month'] == f && data[d]['year'] == fromYear){
                                                    ip = formatNumber(data[d]['implementPoint'], 3);
                                                }
                                            }
                                            html += '<td class="text-align-right" title="Điểm thực hiện của tháng">'+formatNumber(ip, 3)+'</td>';
                                        }
                                        for (var t = 1; t <= toMonth; t++) {
                                            var ip = 0;
                                            for(var d = 0; d < data.length; d++){
                                                if(data[d]['position_code'] == arrayPosition[p]['position_code'] &&
                                                        data[d]['month'] == t && data[d]['year'] == toYear){
                                                    ip = formatNumber(data[d]['implementPoint'], 3);
                                                }
                                            }
                                            html += '<td class="text-align-right" title="Điểm thực hiện của tháng">'+formatNumber(ip, 3)+'</td>';
                                        }
                                    } else if (fromYear == toYear) {
                                        for (var n = fromMonth; n <= toMonth; n++) {
                                            var ip = 0;
                                            for(var d = 0; d < data.length; d++){
                                                if(data[d]['position_code'] == arrayPosition[p]['position_code'] &&
                                                        data[d]['month'] == n && data[d]['year'] == toYear){
                                                    ip = formatNumber(data[d]['implementPoint'], 3);
                                                }
                                            }
                                            html += '<td class="text-align-right" title="Điểm thực hiện của tháng">'+formatNumber(ip, 3)+'</td>';
                                        }
                                    }

                                    html += '<td class="order-column" title="Chọn chức danh để xuất dữ liệu">' +
                                            '<input type="checkbox" class="ckbExport" onclick="clickCheckBox();" value="'+arrayPosition[p]['position_code']+'">' +
                                            '</td></tr>';
                                }

                                html += '</tbody>' +
                                        '</table>';
                                $('#tblResult').append(html);
                            }
                            paintCharts(arrayPosition, dataGoal, data, isHide, dataBM);
                        }
                    });
                }
            } else {
                if(area == 'No'){
                    $('#btnExport').prop("disabled", true);
                    $('#tblResult').hide();
                    $('#txtResult').show();
                    $('#txtResultCountMonth').hide();
                } else {
                    $('#btnExport').prop("disabled", false);
                    $('#tblResult').show();
                    $('#txtResult').hide();
                    $('#txtResultCountMonth').hide();

                    // get data of time
                    var node = document.getElementById("tblResult");
                    while (node.firstChild) {
                        node.removeChild(node.firstChild);
                    }
                    //get data
                    $.get("getDataOfReportDetailByPosition", {
                        month: month, year: year, company: company, area: area,
                        position: position, goal: goal
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

                            var data = result[0];
                            var html = '<table class="table-common">'+
                                    '<thead>'+
                                    '<th class="col-md-1 col-sm-1 order-column">STT</th>'+
                                    '<th class="col-md-5 col-sm-5 order-column">Chức danh</th>'+
                                    '<th class="col-md-5 col-sm-5">Tháng '+month+'</th>'+
                                    '<th class="col-md-1 col-sm-1 order-column">Xuất</th>'+
                                    '</tr>'+
                                    '</thead>'+
                                    '<tbody>';
                            for(var d = 0; d < data.length; d++){
                                if ((d + 1) % 2 == 0) {
                                    html += '<tr class="background-color-smoke">';
                                } else {
                                    html += '<tr>';
                                }
                                html += '<td class="order-column">'+(d+1)+'</td>' +
                                        '<td><a onclick="viewEachPosition(\'' + month + '\', \'' + year + '\', \'' + company + '\', \'' + area + '\', \'' + data[d]['position_code'] + '\', \'' + data[d]['position_name'] + '\');" ' +
                                        'role="button" title="Xem chi tiết">'+
                                        data[d]['position_name']
                                        +'</a></td>' +
                                        '<td class="text-align-right" title="Điểm thực hiện của tháng">'+formatNumber(data[d]['implementPoint'], 3)+'</td>' +
                                        '<td class="order-column" title="Chọn chức danh để xuất dữ liệu">' +
                                        '<input type="checkbox" class="ckbExport" onclick="clickCheckBox();" value="'+data[d]['position_code']+'">' +
                                        '</td></tr>';
                            }

                            html += '</tbody>' +
                                    '</table>';
                            $('#tblResult').append(html);
                            //paint charts
                            //paint chart for each emp
                            var arrayPosition = result[1];
                            var dataGoal = result[2];
                            var dataBM = result[3];
                            paintCharts(arrayPosition, dataGoal, data, 0, dataBM );

                        }
                    });
                }
            }
        }

        function viewEachPosition(month, year, company, area, position, positionName){
            var month = parseInt(month);
            var year = parseInt(year);
            $("html, body").animate({ scrollTop: $(document).height()*2});
            var myNode = document.getElementById("tblResultEachPos");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }
            $('#tblResultEachPos').show();
            var goal = $('#slGoal').val();

            $.get("getDataForEachPosition", {
                month: month, year: year,goal: goal,
                company: company, area: area, position: position
            }, function (result) {
                var data = result[0];
                var parent = result[1];

                var html = '<table class="table-common">' +
                        '<thead>' +
                        '<tr>' +
                        '<th colspan="10" class="order-column">'+positionName+'</th>' +
                        '</tr>' +
                        '<tr>' +
                        '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Mã</th>' +
                        '<th class="col-md-3 col-sm-3">Tên chỉ tiêu</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Loại mục tiêu</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Tỷ trọng</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Điểm chuẩn KPI</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Kế hoạch</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Đơn vị tính</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Thực hiện</th>' +
                        '<th class="col-md-1 col-sm-1 order-column">Điểm thực hiện</th>' +
                        '</tr>' +
                        '</thead>' +
                        '<tbody>';

                for (var p = 0; p < parent.length; p++) {
                    var sumIP = 0;
                    var htmlChild = '';
                    var index = 0;
                    for (var d = 0; d < data.length; d++) {
                        if (data[d]['parent_id'] == parent[p]['id']) {
                            var il = data[d]['important_level'];
                            var bm = data[d]['cal_benchmark'];
                            var tv = data[d]['target_value'];
                            var i = data[d]['implement'];
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
                                        '<td class="text-align-right">' + il + '</td>' +
                                        '<td class="text-align-right">' + formatBigNumber(bm, 3) + '</td>' +
                                        '<td class="text-align-right">' + formatBigNumber(tv) + '</td>' +
                                        '<td>' + data[d]['unit_name'] + '</td>' +
                                        '<td class="text-align-right">' + formatBigNumber(i) + '</td>' +
                                        '<td class="text-align-right">' + formatNumber(ip, 3) + '</td>';
                                sumIP += formatNumber(ip, 3);
                            }
                        }
                    }

                    if (htmlChild != '') {
                        html += '<tr class="color-parent">' +
                                '<td colspan="4">' + parent[p]['goal_name'] + '</td>' +
                                '<td></td>' +
                                '<td class="text-align-right">' + formatNumber(parent[p][['bm']], 3) + '</td>' +
                                '<td></td>' +
                                '<td></td>' +
                                '<td></td>' +
                                '<td class="text-align-right">' + formatNumber(sumIP, 3) + '</td>' +
                                '</tr>';
                        html += htmlChild;
                    }
                }
                html += '</tbody>' +
                        '</table>';
                $('#tblResultEachPos').append(html);
            });
        }

        function viewMultiPosition(fromMonth, fromYear, toMonth, toYear, company, area, position, positionName){
            var fromMonth = parseInt(fromMonth);
            var fromYear = parseInt(fromYear);
            var toMonth = parseInt(toMonth);
            var toYear = parseInt(toYear);
            $("html, body").animate({ scrollTop: $(document).height()*2});
            var myNode = document.getElementById("tblResultEachPos");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }
            $('#tblResultEachPos').show();
            var goal = $('#slGoal').val();

            $.get("getDataForMultiPosition", {
                fromMonth: fromMonth, fromYear: fromYear, toMonth: toMonth, toYear: toYear, goal: goal,
                company: company, area: area, position: position
            }, function (result) {
                var data = result[0];
                var arrGoal = result[1];
                var countMonth = ((toYear - fromYear) * 12 + (toMonth - fromMonth + 1));
                var html = '<table class="table-common">' +
                        '<thead>' +
                        '<tr>' +
                        '<th colspan="'+(countMonth+2)+'" class="order-column">'+positionName+'</th>' +
                        '</tr>' +
                        '<tr>' +
                        '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                        '<th class="col-md-3 col-sm-3 order-column">Tên chỉ tiêu</th>' ;

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

                for(var g = 0; g < arrGoal.length; g++){
                    var isZero = 0;
                    var arrChild = arrGoal[g]['goal_child'];
                    var index = 0;
                    var htmlChild = '';
                    for(var c = 0; c < arrChild.length; c++){
                        var htmlIP = '';
                        if (fromYear < toYear) {
                            for (var f = fromMonth; f <= 12; f++) {
                                var ip = 0;
                                for(var d=0; d<data.length; d++){
                                    if(data[d]['goal_code'] == arrChild[c]['goal_code'] && data[d]['month'] == f && data[d]['year'] == fromYear){
                                        ip = formatNumber(data[d]['implementPoint'], 3);
                                    }
                                }
                                if(ip>0){
                                    isZero = 1;
                                }
                                htmlIP += "<td class='text-align-right'>" + formatNumber(ip, 3)+ "</td>";
                            }

                            for (var t = 1; t <= toMonth; t++) {
                                var ip = 0;
                                for(var d=0; d<data.length; d++){
                                    if(data[d]['goal_code'] == arrChild[c]['goal_code'] && data[d]['month'] == t && data[d]['year'] == toYear){
                                        ip = formatNumber(data[d]['implementPoint'], 3);
                                    }
                                }
                                if(ip>0){
                                    isZero = 1;
                                }
                                htmlIP += "<td class='text-align-right'>" + formatNumber(ip, 3) + "</td>";
                            }

                        } else if (fromYear == toYear) {
                            for (var n = fromMonth; n <= toMonth; n++) {
                                var ip = 0;
                                for(var d=0; d<data.length; d++){
                                    if(data[d]['goal_code'] == arrChild[c]['goal_code'] && data[d]['month'] == n && data[d]['year'] == fromYear){
                                        ip = formatNumber(data[d]['implementPoint'], 3);
                                    }
                                }
                                if(ip>0){
                                    isZero = 1;
                                }
                                htmlIP += "<td class='text-align-right'>" + formatNumber(ip, 3) + "</td>";
                            }
                        }

                        if(isZero){
                            index++;
                            if (index % 2 == 0) {
                                htmlChild += '<tr class="background-color-smoke">';
                            } else {
                                htmlChild += '<tr>';
                            }
                            htmlChild += '<td class="order-column">' + index + '</td>' +
                                    '<td>' + arrChild[c]['goal_name'] + '</td>';
                            htmlChild += htmlIP+'</tr>';
                        }

                    }

                    if(htmlChild != ''){
                        html += '<tr class="color-parent">' +
                                '<td colspan="2">'+arrGoal[g]['goal_name']+'</td>';

                        if (fromYear < toYear) {
                            for (var f = fromMonth; f <= 12; f++) {
                                var sumIP = 0;
                                for(var d=0; d<data.length; d++){
                                    if(data[d]['parent_id'] == arrGoal[g]['id'] && data[d]['month'] == f && data[d]['year'] == fromYear){
                                        sumIP += formatNumber(data[d]['implementPoint'], 3);
                                    }
                                }
                                html += "<td class='text-align-right'>" + formatNumber(sumIP, 3)+ "</td>";
                            }

                            for (var t = 1; t <= toMonth; t++) {
                                var sumIP = 0;
                                for(var d=0; d<data.length; d++){
                                    if(data[d]['parent_id'] == arrGoal[g]['id'] && data[d]['month'] == t && data[d]['year'] == toYear){
                                        sumIP += formatNumber(data[d]['implementPoint'], 3);
                                    }
                                }
                                html += "<td class='text-align-right'>" + formatNumber(sumIP, 3) + "</td>";
                            }

                        } else if (fromYear == toYear) {
                            for (var n = fromMonth; n <= toMonth; n++) {
                                var sumIP = 0;
                                for(var d=0; d<data.length; d++){
                                    if(data[d]['parent_id'] == arrGoal[g]['id'] && data[d]['month'] == n && data[d]['year'] == fromYear){
                                        sumIP += formatNumber(data[d]['implementPoint'], 3);
                                    }
                                }
                                html += "<td class='text-align-right'>" + formatNumber(sumIP, 3)+ "</td>";
                            }
                        }

                        html += '</tr>'+htmlChild;
                    }

                }
                html += '</tbody>' +
                        '</table>';
                $('#tblResultEachPos').append(html);
            });
        }

        function loadDataChangeEmpArea(){
            //change company: change area+ change emp
            var company = $('#slCompany').val();
            var areaChoose = getCookie("areaDetailChoose").toString();
            areaChoose = areaChoose.trim();

            $.get("getDataEmpAreaForRKPI",{company: company},function(result){
                //delete all child in div have id= cbArea - cbEmp
                var nodeA = document.getElementById("slArea");
                while (nodeA.firstChild) {
                    nodeA.removeChild(nodeA.firstChild);
                }

                if(result != false){
                    var dataArea = result;
                    var html = '<option value="No">Chọn một khu vực</option>';
                    var currentArea = <?php echo $currentAreaId ?>;
                    var accessLevel = <?php echo $accessLevel ?>;

                    if(accessLevel > 2){
                        if(areaChoose == '' || areaChoose == null){
                            for (var a = 0; a < dataArea.length; a++) {
                                if(currentArea == dataArea[a]['id']){
                                    html += "<option value='" + dataArea[a]['area_code'] + "'>" + dataArea[a]['area_name'] + "</option>";
                                    break;
                                }

                            }
                        } else {
                            for (var a = 0; a < dataArea.length; a++) {
                                if(currentArea == dataArea[a]['id']){
                                    if(dataArea[a]['area_code'] == areaChoose){
                                        html += "<option value='" + dataArea[a]['area_code'] + "' selected = '1'>" + dataArea[a]['area_name'] + "</option>";
                                    } else {
                                        html += "<option value='" + dataArea[a]['area_code'] + "'>" + dataArea[a]['area_name'] + "</option>";
                                    }
                                }
                            }
                        }
                    } else {
                        if(areaChoose == '' || areaChoose == null){
                            for (var a = 0; a < dataArea.length; a++) {
                                html += "<option value='" + dataArea[a]['area_code'] + "'>" + dataArea[a]['area_name'] + "</option>";
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

                } else {
                    var html = '<option value="No">Chọn một khu vực</option>';
                    $('#slArea').append(html);
                }
            });
        }

        //handler when click select to year --> 9
        function changeShowByCompany(){
            loadDataChangeEmpArea();
            showData();
        }

        function changeShowByArea(){
            var area = $('#slArea').val();
            setCookie("areaDetailChoose",area);
            showData();
        }

        function changeShowByPosition(){
            showData();
        }

        function changeShowByGoal(){
            showData();
        }

        $('#slArea').select2();
        $('#slGoal').multipleSelect();
        $('#slCompany').select2();
        $('#slPosition').multipleSelect();
        setCookie("areaChoose",'');

        $(document).ready(function () {
            loadDataChangeEmpArea();
            showData();

            //edit show on device
            var width= $( window ).width();
            if(width < 550){
               $(".select2").addClass("min-width-326");
                $(".ms-choice").addClass("min-width-100");
                $(".ms-parent").addClass("min-width-352");
                $("#divPos").addClass("margin-left-0-20");
                $("#divPos").remove("margin-left-0-11");
            }else{
                $(".select2").remove("min-width-326");
                $(".ms-choice").remove("min-width-100");
                $("#divPos").remove("margin-left-0-20");
                $("#divPos").addClass("margin-left-0-11");
                $(".ms-parent").remove("min-width-352");
            }
        });
    </script>
@stop