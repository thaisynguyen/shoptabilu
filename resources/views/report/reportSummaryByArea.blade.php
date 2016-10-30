@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.REPORT'). ' ' .Config::get('constant.RPT_SUMMARY_BY_AREA'))
@section('section')
@include('alerts.errors')
@include('widgets.timeOption.sortByTime', array('link' => "reloadPageWithParam('{{action('ExportExcelController@reportDataKPIResult')}}'
                                                                             , 'reportDataKPIResult'
                                                                             , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/slMonthByToMonth/slYearByFromMonth/slYearByToMonth/slCompanyHide/slAreaHide/slGoalHide')"))

    <div id="wrapper" >
        <div class="row margin-form">
            <div class="col-md-12 col-xs-12">
                <div class="col-md-3 col-xs-6 marg-top-title " id="divLblComp">
                    <b class="font-13">Phòng/Đài/MBF HCM</b>
                </div>
                <div class="col-md-3 col-xs-6 marg-top-title " id="labelArea">
                    <b class="font-13 ">Tổ/Quận/Huyện</b>
                </div>
            </div>

            <div class="col-md-12 col-xs-12">
                <div class="col-md-3">
                    <div class="col-md-12 col-xs-6 btnChoose combobox-margin-left-42 margin-top-1-12" id="divSlCompany">
                        <select multiple="multiple" class="margin-top-8 width-102" id="slCompany" onchange="changeShowByCompany();">
                            <?php foreach($company as $row){?>
                            <option value="<?php echo $row->company_code ?>" selected><?php echo $row->company_name ?></option>
                            <?php }?>
                        </select>
                        <input type="hidden" id="slCompanyHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportPositionByTime')}}'
                                       , 'reportPositionByTime'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/slMonthByToMonth/' +
                                       'slYearByFromMonth/slYearByToMonth/slCompanyHide/slAreaHide/slGoalHide')">
                    </div>
                </div>

                <div class="col-md-3 margin-form">
                    <div class="col-md-12 col-xs-6 btnChoose combobox-margin-left-area margin-top-1-12" id="cbArea">
                        <select multiple="multiple" class="form-control margin-top-8 width-area" id="slArea" onchange="changeShowByArea();">
                            <option value="1" selected>Tất cả</option>
                        </select>
                        <input type="hidden" id="slAreaHide" value="1"
                               onchange="reloadPageWithParam('{{action('ExportExcelController@reportPositionByTime')}}'
                                       , 'reportPositionByTime'
                                       , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/slMonthByToMonth/' +
                                       'slYearByFromMonth/slYearByToMonth/slCompanyHide/slAreaHide/slEmpHide')">
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-xs-12">
                <div class="col-md-3 col-xs-6 marg-top-title" id="divLblPosi" >
                    <b class="font-13">Mục tiêu</b>
                </div>
                <div class="col-md-3 col-xs-6 marg-top-title" id="labelArea">
                    <b class="font-13 margin-left-7"></b>
                </div>
            </div>
            <div class="col-md-12 col-xs-12">
                <div class="col-md-3">
                    <div class="col-md-12 col-xs-6 btnChoose combobox-margin-left-42 margin-top-1-12">
                        <select multiple="multiple" class="width-area margin-top-8" id="slGoal" onchange="changeShowByGoal();">
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
                                       'slCompany/slAreaHide/slGoalHide')">
                    </div>
                </div>
                <div class="col-md-3 margin-form">
                </div>
            </div>

            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <button id="btnExport" class="btn btn-primary pull-right marg-bottom-10 margin-left-15"
                            onclick="reloadPageWithParam('{{action('ExportExcelController@reportDataKPIResult')}}'
                                    , 'reportDataKPIResult'
                                    , 'txtRadioChecked/slMonthByMonth/slYearByMonth/slMonthByFromMonth/' +
                                    'slMonthByToMonth/slYearByFromMonth/slYearByToMonth/' +
                                    'slCompany/slAreaHide/slGoalHide')"><i
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
                        <div class="col-sm-12 color-red" id="txtResultCountMonth" hidden>Không thể xem dữ liệu của nhiều tháng hoặc nhiều chức danh. Vui lòng xuất excel để xem!.</div>
                    </div>

                    <div class="tab-pane fade"  id="viewChart">
                        @include('widgets.chartOption.chartOption')
                        <div class="col-md-12 col-xs-12 margin-top-40" id="chartAjax">
                        </div>
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
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>
    {{ HTML::style('public/assets/stylesheets/implement.css') }}
    {{ HTML::style('public/assets/stylesheets/multiple-select.css') }}
    {{ HTML::script('public/assets/scripts/multiple-select.js') }}

    <script type="text/javascript">
        function renderGoalType(number){
            var result = '';
            switch (number){
                case 1:
                    result = 'Càng lớn càng tốt';
                    break;
                case 2:
                    result = 'Càng nhỏ càng tốt';
                    break;
                case 3:
                    result = 'Đạt';
                    break;
                case 4:
                    result = 'Không đạt';
                    break;
            }
            return result;
        }

        //call ajax to load pages to get implement_point of company
        function showData(){

        }

        function loadDataChangeEmpArea(){
            //change company: change area+ change emp
            var company = $('#slCompany').val();

            $.get("getDataAreaForSum",{company: company},function(result){
                //delete all child in div have id= cbArea - cbEmp
                var nodeA = document.getElementById("slArea");
                while (nodeA.firstChild) {
                    nodeA.removeChild(nodeA.firstChild);
                }

                //console.log(result);
                if(result != false){
                    var dataArea = result;
                    var html = '<option value="1" selected>Tất cả</option>';
                    for(var a=0; a<dataArea.length; a++){
                        html += "<option value='"+dataArea[a]['area_code']+"'>"+dataArea[a]['area_name']+"</option>";
                    }
                    $('#slArea').append(html);

                } else {
                    var html = '<option value="1" selected>Tất cả</option>';
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
            showData();
        }

        function changeShowByGoal(){
            showData();
        }

        $('#slArea').select2();
        $('#slGoal').multipleSelect();
        $('#slCompany').multipleSelect();
        $('#slYearByToMonth').select2();
        $('#slYearByFromMonth').select2();
        $('#slMonthByToMonth').select2();
        $('#slMonthByFromMonth').select2();
        $('#slYearByMonth').select2();
        $('#slMonthByMonth').select2();

        $(document).ready(function () {
            loadDataChangeEmpArea();
            showData();

            //edit show on device
            var width= $( window ).width();
            if(width < 550){
                $("#divSlCompany").addClass("width-cb-comp");
                $("#cbArea").addClass("width-cb-area");
                $("#divLblComp").remove("margin-left-1-3");
                $("#labelArea").remove("margin-left-1-6");
                $("#divLblPosi").remove("margin-left-1-3");
                $(".ms-parent").addClass("width-1-80");
            }else{
                $("#divSlCompany").remove("width-cb-comp");
                $("#cbArea").remove("width-cb-area");
                $("#divLblComp").addClass("margin-left-1-3");
                $("#labelArea").addClass("margin-left-1-6");
                $("#divLblPosi").addClass("margin-left-1-3");
                $(".ms-parent").remove("width-1-80");
            }
        });
    </script>
@stop