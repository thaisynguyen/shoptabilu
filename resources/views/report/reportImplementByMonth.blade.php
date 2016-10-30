@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.REPORT') . ' ' . Config::get('constant.RPT_IMPLEMENT_BY_MONTH'))
@section('section')
<?php
$currentCompanyId = Session::get('scompany_id');
$accessLevel = Session::get('saccess_level');
?>
    <div id="wrapper">
        <div class="row margin-form">

            <div class="col-md-12 col-xs-12">
                <div class="col-md-3 col-xs-12">
                    <b class="font-13 marg-top-title">Phòng/Đài/MBF HCM</b><br>

                    <div class="col-md-12 btnChoose margin-top-1-12 margin-left-015" id="divSlCompany">
                        <select multiple="multiple" class="margin-top-8 width-104 " id="slCompany"
                                onchange="changeShowByCompany();">
                            <?php
                            if($accessLevel > 1){
                            foreach($company as $row){
                                if($row->id == $currentCompanyId){?>
                            <option value="<?php echo $row->company_code ?>" selected>
                                <?php echo $row->company_name ?>
                            </option>
                            <?php }}} else {
                                foreach($company as $row){?>
                                <option value="<?php echo $row->company_code ?>" selected>
                                    <?php echo $row->company_name ?>
                                </option>
                                <?php }}?>
                        </select>
                        <input type="hidden" id="slCompanyHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportKPIByMonth')}}'
                                       , 'reportKPIByMonth'
                                       , 'slCompanyHide/slGoalHide/slApplyDate')">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    <b class="font-13 marg-top-title">Mục tiêu</b><br>

                    <div class="col-md-12 btnChoose margin-top-8  margin-top-1-12 margin-left-015" id="divSlGoal">
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
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportKPIByMonth')}}'
                                       , 'reportKPIByMonth'
                                       , 'slCompanyHide/slGoalHide/slApplyDate')">
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                     <b class="font-13">Ngày áp dụng</b><br>
                     <div class="col-md-12 btnChoose margin-top-1-12">
                          <select class="form-control margin-top-8 width-104" id="slApplyDate"
                          onchange="changeShowByApplyDate();"
                           onchange="reloadPageWithParam('{{action('ExportExcelController@reportKPIByMonth')}}'
                                                                  , 'reportKPIByMonth'
                                                                  , 'slCompanyHide/slGoalHide/slApplyDate')">
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
                    <button id="btnExport" class="btn btn-primary pull-right margin-left-15"
                            onclick="reloadPageWithParam('{{action('ExportExcelController@reportKPIByMonth')}}'
                                    , 'reportKPIByMonth'
                                    , 'slCompanyHide/slGoalHide/slApplyDate')"><i
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
                        <div class="col-sm-12 color-red" id="txtResult" hidden>Không tìm thấy kết quả!</div>
                    </div>

                    <div class="tab-pane fade" id="viewChart">
                        @include('widgets.chartOption.chartNotImpOption')
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

    <script type="text/javascript">

        //paint chart
        function paintCharts(arrCompExists, arrDataGoal){
            var isCheckRadarChart = document.getElementById("rdRadarChart").checked;
            var isCheckGBMIP = document.getElementById("rdGBMIP").checked;
            var isCheckRdColumnChart = document.getElementById("rdColumnChart").checked;

            var node = document.getElementById("chartAjax");
            while (node.firstChild) {
                node.removeChild(node.firstChild);
            }

            var htmlChart = '';

            if(isCheckRdColumnChart){
                for (var compE = 0; compE < arrCompExists.length; compE++) {
                    if (compE == (arrCompExists.length - 1)) {
                        htmlChart += '<div class="col-sm-12">' +
                                "<div id='" + arrCompExists[compE]['company_code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    } else {
                        htmlChart += '<div class="col-sm-12 marg-bottom-1-100">' +
                                "<div id='" + arrCompExists[compE]['company_code'] + "' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                    }
                }

            } else {
                for (var compE = 0; compE < arrCompExists.length; compE++) {
                    htmlChart += '<div class="col-sm-12">';
                    htmlChart += '<div class="panel panel-default">';
                    htmlChart += '<div class="panel-heading">';
                    htmlChart += '<h3 class="panel-title">' + arrCompExists[compE]['company_name'] + '</h3>';
                    htmlChart += '</div>';
                    htmlChart += '<div class="panel-body">';
                    htmlChart += "<canvas id='" + arrCompExists[compE]['company_code'] + "' height='220' width='350'></canvas>";
                    htmlChart += '</div></div></div>';
                }
            }

            $('#chartAjax').append(htmlChart);

            //paint charts
            if (isCheckGBMIP) {
                // view by goal-implement point
                for (var emp = 0; emp < arrCompExists.length; emp++) {
                    var arrayChartColumn = [];
                    var goalChild = '';
                    var ipCharts = '';
                    var bmCharts = '';

                    for (var d = 0; d < arrDataGoal.length; d++) {
                            if (arrDataGoal[d]['company_code'] == arrCompExists[emp]['company_code']) {
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
                        paintChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, arrCompExists[emp]['company_code'], 'Điểm thực hiện', 'Điểm chuẩn');
                    } else if(isCheckRdColumnChart){
                        paintChartColumnIPBM(arrayChartColumn, arrCompExists[emp]['company_code'], 'Điểm thực hiện', 'Điểm chuẩn', arrCompExists[emp]['company_name']);
                    } else {
                        paintLineChartTVIByMonthGoal(goalChild, ipCharts, bmCharts, arrCompExists[emp]['company_code'], 'Điểm thực hiện', 'Điểm chuẩn');
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

            var myNode = document.getElementById("tblResult");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }
            $('#btnExport').prop("disabled", true);
            //get value of 9 variable

            var applyDate = $('#slApplyDate').val();
            var company = $('#slCompany').val();
            var goal = $('#slGoal').val();

            var elem = document.getElementById("slCompanyHide");
            elem.value = company;

            var elem = document.getElementById("slGoalHide");
            elem.value = goal;

            //get data
            $.get("getDataReportByMonth", {applyDate: applyDate, company: company, goal: goal}, function (result) {
                        if (result == false) {
                            $('#tblResult').hide();
                            $('#txtResult').show();
                            $('#btnExport').prop("disabled", true);
                        } else {
                            $('#tblResult').show();
                            $('#txtResult').hide();
                            $('#btnExport').prop("disabled", false);
                            var data = result[0];
                            var arrCompExists = result[1];
                            var arrCompNotExists = result[3];
                            var arrDataGoal = result[2];

                            var html = '';
                            html += '<table class="table-common">' +
                                    '<thead>' +
                                    '<tr>' +
                                    '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                                    '<th class="col-md-3 col-sm-3">Phòng/Đài/MBF HCM</th>' +
                                    '<th class="col-md-2 col-sm-2">Điểm thực hiện</th>' +
                                    '<th class="col-md-1 col-sm-1">Xếp hạng</th>' +
                                    '</tr>' +
                                    '</thead>' +
                                    '<tbody>';

                            var index = 0;
                            var rank = 1;
                            var start = 0;
                            var valueBefore = 0;
                            for (var d = 0; d < data.length; d++) {
                                index++;
                                if ((index) % 2 == 0) {
                                    html += '<tr class="background-color-smoke">';
                                } else {
                                    html += '<tr>';
                                }
                                html += '<td class="order-column">' + index + '</td>' +
                                        '<td>' + data[d]['company']['company_name'] + '</td>';

                                var IP = formatNumber( data[d]['ip'] , 3);

                                start++;
                                if ((IP < valueBefore) && (start > 1)) {
                                    rank += 1;
                                }

                                html += '<td class="text-align-right" title="Điểm thực hiện">' + IP + '</td>' +
                                        '<td class="order-column">' + rank + '</td>' +
                                        '</tr>';

                                valueBefore = IP;
                            }
                            for (var cNE = 0; cNE < arrCompNotExists.length; cNE++) {
                                index++;
                                if ((index) % 2 == 0) {
                                    html += '<tr class="background-color-smoke">';
                                } else {
                                    html += '<tr>';
                                }
                                html += '<td class="order-column">' + index + '</td>' +
                                        '<td>' + arrCompNotExists[cNE] + '</td>' +
                                        '<td class="text-align-right" title="Đơn vị không được áp tiêu chí đó">Không</td>' +
                                        '<td class="order-column" title="Đơn vị không được áp tiêu chí đó">Không</td>';
                            }

                            html += '</tbody>' +
                                    '</table>';

                            var node = document.getElementById("tblResult");
                            while (node.firstChild) {
                                node.removeChild(node.firstChild);
                            }

                            $('#tblResult').append(html);

                            //paint chart
                            paintCharts(arrCompExists, arrDataGoal);

                        }
                    });
        }


        //handler when click select to year --> 9
        function changeShowByCompany() {
            showData();
        }

        function changeShowByGoal() {
            showData();
        }

        function changeShowByApplyDate() {
                    showData();
        }

        $('#slApplyDate').select2();
        $('#slGoal').multipleSelect();
        $('#slCompany').multipleSelect();
        setCookie("areaChoose",'');
        setCookie("areaDetailChoose",'');

        $(document).ready(function () {
            $('#divRdMIP').hide();
            document.getElementById("rdGBMIP").checked = true;
            showData();
            //edit show on device
            var width = $(window).width();
            if (width < 550) {
                $(".ms-parent").addClass("min-width-367");
                $(".ms-choice").addClass("width-87");
            } else {
                $(".ms-parent").remove("min-width-367");
                $(".ms-choice").remove("width-87");
            }
        });

    </script>
@stop
