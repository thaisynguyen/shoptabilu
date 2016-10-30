@extends('layouts.dashboard')
@section('page_heading', 'KẾ HOẠCH THEO TỔ/QUẬN/HUYỆN')
@section('section')
    @include('alerts.errors')
    @include('alerts.success')
    <?php
    use Utils\CommonUtils;
    $goalTypes = CommonUtils::arrGoalType(0);
    $view = \Utils\commonUtils::checkIsView();
    $currentCompanyId = Session::get('scompany_id');
    $currentAreaId = Session::get('sarea_id');
    $accessLevel = Session::get('saccess_level');

    ?>
    <div id="wrapper">
        <div class="row margin-form">

            <div class="col-sm-12">
                <div class="col-sm-3 control-label padding-top-frm padding-right-15 font-label-form text-right">
                    Phòng/Đài/MBF HCM (*):
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboCompany"
                            onchange="reloadPageWithParam('{{action('ReportController@reportPlanByArea')}}'
                                    , 'reportPlanByArea'
                                    , 'cboCompany/cboArea/cboMonth/cboYear/cboGoal')">
                        <option value="0"> --- Chọn Phòng/Đài/MBF HCM --- </option>
                        <?php
                        if($accessLevel > 1){
                        foreach ($companies as $company) {
                        if($currentCompanyId == $company->id){?>
                        <option value="<?php echo $company->id;?>" <?php if ($company->id == $selectedCompany) {echo 'selected';} ?>>
                            <?php echo $company->company_name;?>
                        </option>
                        <?php }}} else {
                        foreach ($companies as $company) {?>
                        <option value="<?php echo $company->id;?>" <?php if ($company->id == $selectedCompany) {echo 'selected';} ?>>
                            <?php echo $company->company_name;?>
                        </option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="col-sm-5"></div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-3 control-label padding-top-frm padding-right-15 font-label-form text-right">
                    Tổ/Quận/Huyện(*):
                </div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboArea"
                            onchange="reloadPageWithParam('{{action('ReportController@reportPlanByArea')}}'
                                    , 'reportPlanByArea'
                                    , 'cboCompany/cboArea/cboMonth/cboYear/cboGoal')">
                        <?php

                        foreach ($areas as $area) {
                            $selected = $area->id == $selectedArea ? "selected" : ""; ?>
                            <option value="<?php echo $area->id;?>" {{ $selected }}>
                                <?php echo $area->area_name;?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-sm-5"></div>

            </div>

            <div class="col-sm-12">

                <div class="col-sm-3 control-label padding-top-frm padding-right-15 font-label-form text-right">Tháng (*):
                </div>
                <div class="col-sm-2 form-group">
                    <select class="form-control text-left combobox-99" id="cboMonth"
                            onchange="reloadPageWithParam('{{action('ReportController@reportPlanByArea')}}'
                                    , 'reportPlanByArea'
                                    , 'cboCompany/cboArea/cboMonth/cboYear/cboGoal')">
                        <?php for($m = 1 ; $m <= 12 ; $m++) { ?>
                        <option value="<?php echo $m;?>" class="text-left" <?php if ($m == $selectedMonth) {
                            echo 'selected';
                        } ?>><?php echo $m;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-sm-7"></div>
            </div>

            <div class="col-sm-12">

                <div class="col-sm-3 control-label padding-top-frm padding-right-15 font-label-form text-right">Năm (*):
                </div>

                <div class="col-sm-2 form-group">
                    <select class="form-control text-left combobox-99" id="cboYear"
                            onchange="reloadPageWithParam('{{action('ReportController@reportPlanByArea')}}'
                                    , 'reportPlanByArea'
                                    , 'cboCompany/cboArea/cboMonth/cboYear/cboGoal')">
                        <?php $arrYear = \Utils\commonUtils::getArrYear($dataYears) ; foreach($arrYear as $year) { ?>
                        <option value="<?php echo $year;?>" class="text-left" <?php if ($year == $selectedYear) {
                            echo 'selected';
                        } ?>><?php echo $year;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-sm-7"></div>
            </div>
            <div class="col-sm-12">

                <div class="col-sm-3 control-label padding-top-frm padding-right-15 font-label-form text-right">Mục tiêu:
                </div>
                <div class="col-sm-8 form-group">
                    <select class="form-control combobox-99" id="cboGoal"
                            onchange="reloadPageWithParam('{{action('ReportController@reportPlanByArea')}}'
                                    , 'reportPlanByArea'
                                    , 'cboCompany/cboArea/cboMonth/cboYear/cboGoal')">
                        <option value="0">Tất cả</option>
                        <?php foreach ($gOnes as $gOne) { ?>
                        <option value="<?php echo $gOne->id;?>" <?php if ($gOne->id == $selectedGoal) {
                            echo 'selected';
                        } ?>><?php echo $gOne->goal_name;?></option>
                        <?php
                        foreach ($gTwos as $gTwo){
                        if($gTwo->parent_id == $gOne->id){?>
                        <option value="<?php echo $gTwo->id; ?>" <?php if($gTwo->id == $selectedGoal){  echo 'selected'; } ?> class="margin-left-20">
                            <?php echo $gTwo->goal_name; ?>
                        </option>
                        <?php }
                        } ?>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-sm-1"></div>
            </div>
            <div class="col-sm-12">

                <div class="col-sm-3 control-label padding-top-frm padding-right-15 font-label-form text-right">
                </div>

                <div class="col-sm-2 form-group">
                    <a title="Báo cáo in lưu theo tháng" role="button" id="expGoalArea" class="btn btn-primary "
                       onclick="reloadPageWithParam('{{action('ExportExcelController@exportPlanByArea')}}'
                               , 'exportPlanByArea'
                               , 'cboCompany/cboArea/cboYear/cboMonth/cboGoal')"><i
                                class="fa fa-sign-out"></i> Xuất Excel</a>
                </div>
                <div class="col-sm-7"></div>
            </div>
        </div>
    </div>

    <script>
        $( document ).ready(function() {

            var valArea     = $('#cboArea').val();
            var valCompany  = $('#cboCompany').val();
            var valYear   = $('#cboYear').val();
            var valMonth    = $('#cboMonth').val();

            if(valArea == 0 && valCompany != 0 ){
                $('#NoteExport').removeClass('hidden');
            }

            /*--------------------------------------------------------------------------------------------------------*/
            if(
                    valYear         == 0
                    || valMonth     == 0
                    || valCompany   == 0
                    || valArea      == 0
            ){
                $('#expGoalArea').attr('disabled','disabled');
            }else{
                $('#expGoalArea').removeAttr('disabled');
            }

            /*--------------------------------------------------------------------------------------------------------*/
        });
    </script>


@stop