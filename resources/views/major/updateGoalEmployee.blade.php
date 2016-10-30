@extends('layouts.dashboard')
@section('page_heading', 'Cập nhật Kế Hoạch/ Thực hiện Cho Nhân Viên')
@section('section')
    @include('alerts.errors')

    <?php

        $idTE           = $targetEmployeeDB->id;
        $companyName    = $targetEmployeeDB->company_name;
        $areaName       = $targetEmployeeDB->area_name;
        $positionName   = $targetEmployeeDB->position_name;
        $employeeName   = $targetEmployeeDB->name;
        $dir            = $targetEmployeeDB->month.'/'.$targetEmployeeDB->year;
        $goalType       = \Utils\commonUtils::renderGoalTypeName($targetEmployeeDB->goal_type);
        $goalName       = $targetEmployeeDB->goal_name;
        $unitName       = $targetEmployeeDB->unit_name;
        $importantLevel = $targetEmployeeDB->important_level;
        $implement      = $targetEmployeeDB->implement;
        $targetValue    = $targetEmployeeDB->target_value;
        $importantLevel = $targetEmployeeDB->important_level;
        //$targetValue    = \Utils\commonUtils::formatFloatValue($targetEmployeeDB[0]->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT);
        //$implement      = \Utils\commonUtils::formatFloatValue($targetEmployeeDB[0]->implement, \Utils\commonUtils::NUMBER_AFTER_DOT);
        $disable = ($targetEmployeeDB->formula != \Utils\commonUtils::FORMULA_TU_NHAP && \Utils\commonUtils::compareTwoString($targetEmployeeDB->position_code, \Utils\commonUtils::POSITION_CODE_TQ) == 1) ? 'readonly' : '';
        $defaultData    =   $idTE
                            .','.$targetEmployeeDB->company_id
                            .','.$targetEmployeeDB->area_id
                            .','.$targetEmployeeDB->position_id
                            .','.$targetEmployeeDB->user_id
                            .','.$targetEmployeeDB->month
                            .','.$targetEmployeeDB->year
                            .','.$targetEmployeeDB->goal_type
                            .','.$targetEmployeeDB->goal_id
                            .','.$targetEmployeeDB->unit_id
                            .','.$importantLevel
                            .','.$targetValue
                            .','.$targetEmployeeDB->implement
                            .','.$targetEmployeeDB->benchmark
                            .','.$targetEmployeeDB->position_code
                            .','.$targetEmployeeDB->formula
                            .','.$targetEmployeeDB->cal_benchmark
                            .','.$areaName
        ;
    ?>

    <form action="{{action('MajorController@editGoalEmployee')}}" method="POST" class="form-horizontal margin-left-20" role="form">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <input type="hidden" name="defaultData" value="<?= $defaultData;?>"/>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Phòng/Đài/MBF HCM: </label>
            <div class="col-sm-10">
                <input type="text" id="" class="col-xs-10 col-sm-5" readonly value="{{ $companyName }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tổ/Quận/Huyện: </label>
            <div class="col-sm-10">
                <input type="text" id="" class="col-xs-10 col-sm-5" readonly value="{{ $areaName }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Chức danh: </label>
            <div class="col-sm-10">
                <input type="text" id="" class="col-xs-10 col-sm-5" readonly value="{{ $positionName }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tháng/ Năm áp dụng: </label>
            <div class="col-sm-10">
                <input type="text" id="" class="col-xs-10 col-sm-5"readonly  value="{{ $dir }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Nhân viên: </label>
            <div class="col-sm-10">
                <input type="text" id="" class="col-xs-10 col-sm-5" readonly value="{{ $employeeName }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Loại mục Tiêu: </label>
            <div class="col-sm-10">
                <input type="text" id="" class="col-xs-10 col-sm-5" readonly value="{{ $goalType }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Mục tiêu: </label>
            <div class="col-sm-10">
                <input type="text" id="" class="col-xs-10 col-sm-5" readonly value="{{ $goalName }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Đơn vị tính: </label>
            <div class="col-sm-10">
                <input type="text" id="" class="col-xs-10 col-sm-5" readonly value="{{ $unitName }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tỷ trọng: </label>
            <div class="col-sm-10">
                <input type="text" id="" class="col-xs-10 col-sm-5" readonly value="{{ $importantLevel }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Kế hoạch: </label>
            <div class="col-sm-10">
                <input type="number" step="0.0001" id="txtTargetValue" name="txtTargetValue" class="col-xs-10 col-sm-5"  value="{{ $targetValue }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Thực hiện: </label>
            <div class="col-sm-10">
                <input type="number" id="txtImplement" step="0.0001" name="txtImplement" class="col-xs-10 col-sm-5"  value="{{ $implement }}" <?php echo $disable;?>>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15" for="form-field-1"> &nbsp; </label>
            <div class="col-sm-10">
                <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                <a href="<?= URL::to('manageGoalEmployee/'.$targetEmployeeDB->company_id.'/'.$targetEmployeeDB->area_id.'/'.$targetEmployeeDB->position_id.'/'.$targetEmployeeDB->user_id.'/'.$targetEmployeeDB->goal_id.'/'.$targetEmployeeDB->goal_type.'/'.$targetEmployeeDB->year.'/'.$targetEmployeeDB->month);?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#txtTargetValue").focus();
        });
    </script>
@stop