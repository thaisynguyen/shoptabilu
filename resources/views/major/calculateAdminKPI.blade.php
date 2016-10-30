@extends('layouts.dashboard')
@section('page_heading', 'Tính KPI')
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
    $sDataUser  = Session::get('sDataUser');
    ?>

    <form action="{{action('MajorController@saveAdminKPI')}}" method="POST" class="form-horizontal margin-left-20" role="form">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="col-sm-3 control-label padding-top-frm padding-right-15 font-label-form text-right">
                Phòng/Đài/MBF HCM (*):
            </div>
            <div class="col-sm-4 form-group">
                <select class="form-control combobox-99" id="cboCompany" name="company_id"
                        onchange="reloadPageWithParam('{{action('MajorController@calculateAdminKPI')}}'
                                , 'calculateAdminKPI'
                                , 'cboCompany/cboArea/cboYear/cboMonth')">
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
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="col-sm-3 control-label padding-right-15 font-label-form text-right">
                Tổ/Quận/Huyện(*):
            </div>
            <div class="col-sm-4 form-group">
                <select class="form-control combobox-99" id="cboArea" name="area_id">
                    <option value="All">Tất cả</option>
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
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="col-sm-3 control-label padding-top-frm padding-right-15 font-label-form text-right">Tháng (*):
            </div>
            <div class="col-sm-2 form-group">
                <select class="form-control text-left combobox-99" id="cboMonth" name="month">
                    <option value="All">Tất cả</option>
                    <?php for($m = 1 ; $m <= 12 ; $m++) { ?>
                    <option value="<?php echo $m;?>" class="text-left" <?php if ($m == $selectedMonth) {
                        echo 'selected';
                    } ?>><?php echo $m;?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-7"></div>
        </div>
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="col-sm-3 control-label padding-top-frm padding-right-15 font-label-form text-right">Năm (*):
            </div>

            <div class="col-sm-2 form-group">
                <select class="form-control text-left combobox-99" id="cboYear" name="year">
                    <?php

                        $arrYear = \Utils\commonUtils::getArrYear($dataYears) ;
                        foreach($arrYear as $year) {
                        $selected = ($year == $selectedYear) ? 'selected' : '';
                    ?>
                    <option value="<?php echo $year;?>" class="text-left" <?php echo $selected; ?>><?php echo $year;?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-7"></div>
        </div>
        <div class="col-sm-12 col-md-12 col-lg-12">
            <label class="col-sm-3 control-label padding-right-15" for="form-field-1"> &nbsp; </label>
            <div class="col-sm-3 text-left npd-left">
                <button type="submit" class="btn btn-primary btn-save" id="calKPI" data-target=".dialog-waiting-cal-kpi-1" data-toggle="modal"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Tính KPI&nbsp;&nbsp;</button>
                @include('popup.waitingCalKPI', array('calId' =>1))
            </div>
            <div class="col-sm-6"></div>
        </div>
    </form>



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
            ){
                $('#calKPI').attr('disabled','disabled');
            }else{
                $('#calKPI').removeAttr('disabled');
            }

            /*--------------------------------------------------------------------------------------------------------*/
        });
    </script>


@stop
