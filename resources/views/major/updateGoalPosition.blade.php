@extends('layouts.dashboard')
@section('page_heading', 'Cập nhật Kế Hoạch/ Thực hiện Cho Chức Danh')
@section('section')
    @include('alerts.errors')

    <?php

        $idTE           = $targetPositionDB[0]->id;
        $companyName    = $targetPositionDB[0]->company_name;
        $areaName       = $targetPositionDB[0]->area_name;
        $positionName   = $targetPositionDB[0]->position_name;
        $dir            = $targetPositionDB[0]->month.'/'.$targetPositionDB[0]->year;
        $goalType       = \Utils\commonUtils::renderGoalTypeName($targetPositionDB[0]->goal_type);
        $goalName       = $targetPositionDB[0]->goal_name;
        $unitName       = $targetPositionDB[0]->unit_name;
        $importantLevel = $targetPositionDB[0]->important_level;
        $implement      = $targetPositionDB[0]->implement;
        $targetValue    = $targetPositionDB[0]->target_value;
        $importantLevel = $targetPositionDB[0]->important_level;
        //$targetValue    = \Utils\commonUtils::formatFloatValue($targetEmployeeDB[0]->target_value, \Utils\commonUtils::NUMBER_AFTER_DOT);
        //$implement      = \Utils\commonUtils::formatFloatValue($targetEmployeeDB[0]->implement, \Utils\commonUtils::NUMBER_AFTER_DOT);
        $defaultData    =   $idTE
                            .','.$targetPositionDB[0]->company_id
                            .','.$targetPositionDB[0]->area_id
                            .','.$targetPositionDB[0]->position_id
                            .','.$targetPositionDB[0]->month
                            .','.$targetPositionDB[0]->year
                            .','.$targetPositionDB[0]->goal_type
                            .','.$targetPositionDB[0]->goal_id
                            .','.$targetPositionDB[0]->unit_id
                            .','.$targetPositionDB[0]->important_level
                            .','.$targetPositionDB[0]->target_value
                            .','.$targetPositionDB[0]->implement
                            .','.$targetPositionDB[0]->benchmark
                            .','.$targetPositionDB[0]->position_code
                            .','.$targetPositionDB[0]->formula
                            .','.$targetPositionDB[0]->cal_benchmark
                            .','.$areaName
        ;
    ?>

    <form action="{{action('MajorController@editGoalPosition')}}" method="POST" class="form-horizontal margin-left-20" role="form">
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
                <input type="number" id="txtImplement"  name="txtImplement" readonly class="col-xs-10 col-sm-5"  value="{{ $implement }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15" for="form-field-1"> &nbsp; </label>
            <div class="col-sm-10">
                <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                <a href="<?= URL::to('manageGoalPosition/'.$targetPositionDB[0]->company_id.'/'.$targetPositionDB[0]->area_id.'/'.$targetPositionDB[0]->position_id.'/'.$targetPositionDB[0]->goal_id.'/'.$targetPositionDB[0]->goal_type.'/'.$targetPositionDB[0]->year.'/'.$targetPositionDB[0]->month);?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#txtTargetValue").focus();
        });
    </script>
@stop