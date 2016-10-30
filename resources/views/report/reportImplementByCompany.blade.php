@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.REPORT') . ' ' . Config::get('constant.RPT_IMPLEMENT_BY_COMPANY'))
@section('section')
<?php
$currentCompanyId = Session::get('scompany_id');
$accessLevel = Session::get('saccess_level');
?>
    <div id="wrapper">
        <div class="row margin-form">
            <div class="col-md-12 col-xs-12">
                <div class="col-md-3">
                    <b class="font-13 margin-left-5 marg-top-title">Phòng/Đài/MBF HCM</b><br>

                    <div class="col-md-12 col-xs-12 btnChoose margin-top-1-12">
                        <select multiple="multiple" class="margin-top-8 width-104" id="slCompany"
                                onchange="changeShowByCompany();">
                            <?php
                            if($accessLevel > 1){
                            foreach($company as $row){
                                if($row->id == $currentCompanyId){?>
                            <option value="<?= $row->company_code ?>" selected>
                                <?php echo $row->company_name ?>
                            </option>
                            <?php break;}}} else {
                                foreach($company as $row){?>
                                <option value="<?= $row->company_code ?>" selected>
                                    <?php echo $row->company_name ?>
                                </option>
                                <?php }}?>
                        </select>

                        <input type="hidden" id="slCompanyHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportCompanyByTimes')}}'
                                       , 'reportCompanyByTimes'
                                       , 'slCompanyHide/slApplyDate')">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <b class="font-13">Ngày áp dụng</b><br>
                        <div class="col-md-12 btnChoose margin-top-1-12">
                             <select class="form-control margin-top-8 width-104" id="slApplyDate"
                                  onchange="changeShowByApplyDate();"
                                  onchange="reloadPageWithParam('{{action('ExportExcelController@reportCompanyByTimes')}}'
                                                                         , 'reportCompanyByTimes'
                                                                         , 'slCompanyHide/slApplyDate')">
                                 <?php
                                    foreach($applyDate as $app){?>
                                        <option value="<?=$app->apply_date ?>">{{ \Utils\commonUtils::formatDate($app->apply_date)}}</option>
                                 <?php }
                                 ?>
                             </select>
                        </div>
                </div>


            </div>

            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <button id="btnExport" role="button" class="btn btn-primary pull-right margin-left-15"
                       onclick="reloadPageWithParam('{{action('ExportExcelController@reportCompanyByTimes')}}'
                               , 'reportCompanyByTimes'
                               , 'slCompanyHide/slApplyDate')"><i
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
                        <div class="col-sm-12 color-red" id="txtResult" hidden>Không tìm thấy kết quả!</div>
                    </div>

                    <div class="tab-pane fade" id="viewChart">
                        @include('widgets.chartOption.chartNotImpOption')
                        @include('widgets.chartOption.showSummary')
                        <div class="col-md-12 col-xs-12 margin-top-40" id="chartAjax"></div>
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
        {{ HTML::script('public/assets/scripts/highcharts-3d.js') }}
        {{ HTML::script('public/assets/scripts/data.js') }}
        {{ HTML::script('public/assets/scripts/exporting.js') }}

    <script type="text/javascript">

        //paint charts
        function paintCharts(arrCompany, data, arrDataGoal){
            var isCheckGBMIP = document.getElementById("rdGBMIP").checked;
            var isCheckRadarChart = document.getElementById("rdRadarChart").checked;
            var isCheckRdColumnChart = document.getElementById("rdColumnChart").checked;
            var isCheckCkbSummary = document.getElementById("ckbSummaryChart").checked;

            var node = document.getElementById("chartAjax");
            while (node.firstChild) {
                node.removeChild(node.firstChild);
            }
            var htmlChart = '';
            if(isCheckRdColumnChart){
                if(isCheckCkbSummary){
                    htmlChart += '<div class="col-sm-12">' +
                                    "<div id='summary' style='min-width: 310px; height: 600px; margin: 0 auto'></div>" +
                                 "</div>";
                }
                for (var emp = 0; emp < arrCompany.length; emp++) {
                    if(emp == (arrCompany.length - 1)){
                        htmlChart += '<div class="col-sm-12">' +
                                "<div id='" + arrCompany[emp]['company_code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    } else {
                        htmlChart += '<div class="col-sm-12 marg-bottom-1-100">' +
                                "<div id='" + arrCompany[emp]['company_code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    }
                }
            } else {
                if(isCheckCkbSummary){
                    htmlChart += '<div class="col-sm-12">';
                    htmlChart += '<div class="panel panel-default">';
                    htmlChart += '<div class="panel-heading">';
                    htmlChart += '<h3 class="panel-title">Biểu Đồ Tổng Quan</h3>';
                    htmlChart += '</div>';
                    htmlChart += '<div class="panel-body">';
                    htmlChart += "<canvas id='summary' height='220' width='350'></canvas>";
                    htmlChart += '</div></div></div>';
                }

                for (var emp = 0; emp < arrCompany.length; emp++) {
                    htmlChart += '<div class="col-sm-12">';
                    htmlChart += '<div class="panel panel-default">';
                    htmlChart += '<div class="panel-heading">';
                    htmlChart += '<h3 class="panel-title">' + arrCompany[emp]['company_name'] + '</h3>';
                    htmlChart += '</div>';
                    htmlChart += '<div class="panel-body">';
                    htmlChart += "<canvas id='" + arrCompany[emp]['company_code'] + "' height='220' width='350'></canvas>";
                    htmlChart += '</div></div></div>';
                }
            }
            $('#chartAjax').append(htmlChart);

            //paint charts
            //summary: điểm thực hiện, điểm chuẩn
            if(isCheckCkbSummary){
                var comp = '';
                var ipCharts = '';
                var bmCharts = '';
                var arrayChartColumn = [];
                for (var emp = 0; emp < data.length; emp++){
                    var child = [];
                    var re = data[emp]['company']['company_name'].replace('1', '_1');
                    child.push(re);
                    comp += '"' + data[emp]['company']['company_name'] + '",';

                    child.push(data[emp]['ip']);
                    child.push(data[emp]['bm']);
                    arrayChartColumn.push(child);
                    ipCharts += '"' + data[emp]['ip'] + '", ';
                    bmCharts += '"' + data[emp]['bm'] + '", ';
                }

                console.log(arrayChartColumn);

                if(isCheckRadarChart){
                    paintChartTVIByMonthGoal(comp, ipCharts, bmCharts, 'summary', 'Điểm thực hiện', 'Điểm chuẩn');
                } else if(isCheckRdColumnChart){
                    paintChartColumnIPBM(arrayChartColumn, 'summary', 'Điểm thực hiện', 'Điểm chuẩn', 'Biểu Đồ Tổng Quan');
                } else {
                    paintLineChartTVIByMonthGoal(comp, ipCharts, bmCharts, 'summary', 'Điểm thực hiện', 'Điểm chuẩn');
                }
            }

             if (isCheckGBMIP) {
                // view by goal-implement point
                for (var emp = 0; emp < arrCompany.length; emp++) {
                    var arrayChartColumn = [];
                    var goalChild = '';
                    var ipCharts = '';
                    var bmCharts = '';

                    for (var d = 0; d < arrDataGoal.length; d++) {
                            if (arrDataGoal[d]['company_code'] == arrCompany[emp]['company_code']) {
                                var arrayChild = [];
                                goalChild += '"' + arrDataGoal[d]['goal_name'] + '",';
                                arrayChild.push(arrDataGoal[d]['goal_name']);
                                arrayChild.push(formatNumber(arrDataGoal[d]['ip'], 3));
                                arrayChild.push(formatNumber(arrDataGoal[d]['bm'], 3));
                                ipCharts += '"' + formatNumber(arrDataGoal[d]['ip'], 3) + '", ';
                                bmCharts += '"' + formatNumber(arrDataGoal[d]['bm'], 3) + '", ';
                                arrayChartColumn.push(arrayChild);
                            }
                        }

                    if(isCheckRadarChart){
                        paintChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, arrCompany[emp]['company_code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrCompany[emp]['company_code'], 'Điểm thực hiện', 'Điểm chuẩn', arrCompany[emp]['company_name']);
                    } else {
                        paintLineChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, arrCompany[emp]['company_code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    }
                }
            }
        }

        //call ajax to load pages to get implement_point of company
        function showData() {
            var myNode = document.getElementById("chartAjax");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }
            $('#btnExport').prop("disabled", true);
            //hide fist data table
            $('#tblResultFirst').hide();

            //get value of 9 variable
            var applyDate = $('#slApplyDate').val();
            var company = $('#slCompany').val();

            var elemP = document.getElementById("slCompanyHide");
            elemP.value = company;

            // get data of time
                $.get("getDataByTime", {applyDate: applyDate, company: company}, function (resultMonth) {
                    if (!resultMonth) {
                        $('#tblResult').hide();
                        $('#txtResult').show();
                    } else {
                        var data = resultMonth[0];
                        $('#btnExport').prop("disabled", false);
                        $('#txtResult').hide();
                        $('#tblResult').show();
                        $('#tblResult').html('');
                        var html = "<table class='table-common'>" +
                                "<thead>" +
                                "<tr>" +
                                "<th class='col-md-1 col-sm-1 order-column'>STT</th>" +
                                "<th class='col-md-3 col-sm-3'>Phòng/Đài/MBF HCM</th>" +
                                "<th class='col-md-3 col-sm-3'>Điểm thực hiện</th>" +
                                "<th class='col-md-2 col-sm-2'>Xếp hạng</th>" +
                                "</tr>" +
                                "</thead>" +
                                "<tbody>";
                        var rank = 1;
                        var valueBefore = 0;
                        var index = 0;
                        var start = 0;
                        for (var i = 0; i < data.length; i++) {
                            index++;
                            start++;
                            if (data[i]['ip'] < valueBefore && start > 1) {
                                rank += 1;
                            }

                            if (index % 2 == 0) {
                                html += '<tr class="background-color-smoke">';
                            } else {
                                html += '<tr>';
                            }
                            html += "<td class='order-column'>" + index + "</td>" +
                                    "<td>" + data[i]['company']['company_name'] + "</td>" +
                                    "<td class='text-align-right' title='Điểm thực hiện của tháng'>" + formatNumber(data[i]['ip'], 3) + "</td>" +
                                    "<td class='order-column'>" + rank + "</td>" +
                                    "</tr>";
                            valueBefore = data[i]['ip'];
                        }
                        html += "</tbody>" +
                                "</table>";
                        $('#tblResult').append(html);

                        //get data for chart
                        var arrCompany = resultMonth[1];
                        var arrDataGoal = resultMonth[2];
                        paintCharts(arrCompany, data, arrDataGoal);
                    }
                });
        }

        //handler when click select to year --> 9
        function changeShowByCompany() {
            showData();
        }

        function changeShowByApplyDate() {
            showData();
        }

        $('#slCompany').multipleSelect();
        $('#slApplyDate').select2();
        setCookie("areaChoose",'');
        setCookie("areaDetailChoose",'');

        $(document).ready(function () {
            $('#divRdMIP').hide();
            document.getElementById("rdGBMIP").checked = true;
            showData();
            var width = $(window).width();
            if (width < 550) {
                $(".ms-parent").addClass("min-width-128");
            } else {
                $(".ms-parent").remove("min-width-128");
            }
        });

    </script>
@stop
