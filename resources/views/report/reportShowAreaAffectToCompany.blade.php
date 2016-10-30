@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.RPT_AREA_AFFECT_COMPANY'))
@section('section')
@include('alerts.errors')

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
                                onchange="reloadPageWithParam('{{action('ExportExcelController@exportAreaAffectToComp')}}'
                                        , 'exportAreaAffectToComp'
                                        , 'slCompany/slAreaHide/slGoalHide/slApplyDate')">
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
                               onchange="reloadPageWithParam('{{action('ExportExcelController@exportAreaAffectToComp')}}'
                                                                                 , 'exportAreaAffectToComp'
                                                                                 , 'slCompany/slAreaHide/slGoalHide/slApplyDate')">

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
                               onchange="reloadPageWithParam('{{action('ExportExcelController@exportAreaAffectToComp')}}'
                                                                                 , 'exportAreaAffectToComp'
                                                                                 , 'slCompany/slAreaHide/slGoalHide/slApplyDate')">
                    </div>
                </div>

                <div class="col-md-2 col-xs-12">
                    <b class="font-13">Ngày áp dụng</b><br>
                        <div class="col-md-12 btnChoose margin-top-1-12" style="">
                             <select class="form-control margin-top-8 width-108" id="slApplyDate"
                                                             onchange="changeShowByApplyDate();"
                                                             onchange="reloadPageWithParam('{{action('ExportExcelController@exportAreaAffectToComp')}}'
                                                                                                               , 'exportAreaAffectToComp'
                                                                                                               , 'slCompany/slAreaHide/slGoalHide/slApplyDate')">
                                 <?php
                                    foreach($apply_date as $appDate){?>
                                        <option value="{{$appDate->apply_date}}">{{ \Utils\commonUtils::formatDate($appDate->apply_date)}}</option>
                                 <?php }

                                 ?>
                                </select>
                        </div>
                </div>
            </div>

            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <button id="btnExport" class="btn btn-primary pull-right margin-left-15"
                            onclick = "reloadPageWithParam('{{action('ExportExcelController@exportAreaAffectToComp')}}'
                                                                               , 'exportAreaAffectToComp'
                                                                               , 'slCompany/slAreaHide/slGoalHide/slApplyDate')"><i
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
                        <div class="col-md-12 col-xs-12 margin-top-40" id="chartAjax">
                        </div>
                        <div class="col-md-12 col-xs-12 margin-top-40" id="idTable" hidden=""></div>

                    </div>
                </div>
            </div>
        </div>
    </div>

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

        function paintChart(data, labelIP, title, id, index1, index2){
                var node = document.getElementById("idTable");
                while (node.firstChild) {
                    node.removeChild(node.firstChild);
                }
                var html = ' <table id="datatable">'+
                        '<thead>'+
                        '<tr>'+
                        '<th></th>'+
                        '<th>'+labelIP+'</th>'+
                        '</tr>'+
                        '</thead>'+
                        '<tbody>';
                for(var d = 0; d<data.length; d++){
                var areaName = data[d][index1].replace(" ", "_");
                    html += '<tr>' +
                            '<td>'+areaName+'</td>' +
                            '<td>'+formatNumber(data[d][index2], 3)+'</td>' +
                            '</tr>';
                }
                html += '</tbody>' +
                        '</table>';

                $('#idTable').append(html);

                $(function () {
                    var chart = new Highcharts.Chart({
                        data: {
                            table: 'datatable'
                        },
                        chart: {
                            type: 'pie',
                            renderTo: id,
                            options3d: {
                                enabled: true,
                                alpha: 45,
                                beta: 0
                            }
                        },
                        title: {
                            text: title
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                depth: 35,
                                dataLabels: {
                                    enabled: true,
                                    format: '{point.name}'
                                }
                            }
                        },
                        yAxis: {
                            allowDecimals: false,
                            title: {
                                text: ''
                            }
                        },
                        credits: {
                            enabled: false
                        },
                        tooltip: {
                            formatter: function () {
                                return '<b>' + this.series.name + ': '+ formatBigNumber(this.point.y) + '</b><br/>';
                            }
                        }
                    });
                });
            }

        //call ajax to load pages to get implement_point of company
        function showData(){
            $('#btnExport').prop("disabled", true);
            var myNode = document.getElementById("chartAjax");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }

            var myNode = document.getElementById("idTable");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }

             var myNode = document.getElementById("tblResult");
            while (myNode.firstChild) {
                myNode.removeChild(myNode.firstChild);
            }

             $('#txtResult').hide();

            //get value of 9 variable
             var company = $('#slCompany').val();
             var area = $('#slArea').val();
             var goal = $('#slGoal').val();
             var applyDate = $('#slApplyDate').val();

             var elemP = document.getElementById("slAreaHide");
             elemP.value = area;

             var elemP = document.getElementById("slGoalHide");
             elemP.value = goal;

             if(area == null || goal == null || applyDate == '0000-00-00'){
                $('#txtResult').show();
             } else {
                    $.get("getDataAreaAffectComp", {company: company, area: area, goal: goal, applyDate: applyDate},
                    function (result) {
                         if (result == false) {
                               $('#txtResult').show();
                         } else {
                             $('#btnExport').prop("disabled", false);
                            var html = '<table class="table-common">' +
                                        '<thead>' +
                                        '<th class="col-md-1 col-sm-1 order-column">STT</th>' +
                                        '<th class="col-md-5 col-sm-5 order-column">Tổ/Quận/Huyện</th>' +
                                        '<th class="col-md-3 col-sm-3 order-column">Điểm chuẩn</th>' +
                                        '<th class="col-md-3 col-sm-3 order-column">Điểm thực hiện</th>' +
                                        '</tr>' +
                                        '</thead>' +
                                        '<tbody>';

                            for(var i = 0; i< result.length; i++){
                                if((i+1)%2 == 0){
                                    html += '<tr class="background-color-smoke">';
                                } else {
                                    html += '<tr>';
                                }

                                html += '<td class="order-column">'+(i+1)+'</td>'+
                                        '<td>'+result[i]['area_name']+'</td>'+
                                        '<td class="text-align-right">'+formatNumber(result[i]['bm'], 3)+'</td>'+
                                        '<td class="text-align-right">'+formatNumber(result[i]['ip'], 3)+'</td></tr>';
                            }
                            var myNode = document.getElementById("tblResult");
                            while (myNode.firstChild) {
                                myNode.removeChild(myNode.firstChild);
                            }
                            $('#tblResult').append(html);

                            var html = '';
                            html += '<div class="col-sm-12">' +
                                    "<div id='bm' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>"+
                                    '<div class="col-sm-12">' +
                                    "<div id='ip' style='min-width: 310px; height: 600px; margin: 0 auto'></div></div>";
                            $('#chartAjax').append(html);
                            paintChart(result, 'Điểm chuẩn', 'Biểu Đồ Điểm Chuẩn', 'bm', 'area_name', 'bm');
                            paintChart(result, 'Điểm thực hiện', 'Biểu Đồ Điểm Thực Hiện', 'ip', 'area_name', 'ip');
                         }
                    });
             }

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
                    var currentArea = "{{$currentAreaId}}";
                    var accessLevel = "{{$accessLevel}}";

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

        function changeShowByApplyDate(){
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
        $('#slApplyDate').select2();
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