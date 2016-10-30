@extends('layouts.dashboard')
@section('page_heading', 'Cập nhật Kế Hoạch Cho Tổ/Quận/Huyện')
@section('section')
@include('alerts.errors')

    <?php
        $compId = $targetAreaDB[0]->company_id;
        $areaId = $targetAreaDB[0]->area_id;
        $goalId = $targetAreaDB[0]->goal_id;
        $month = $targetAreaDB[0]->month;
        $year = $targetAreaDB[0]->year;

        $idTE           = $targetAreaDB[0]->id;
        $companyName    = $targetAreaDB[0]->company_name;
        $areaName       = $targetAreaDB[0]->area_name;
        $dir            = $month.'/'.$year;
        $goalType       = \Utils\commonUtils::renderGoalTypeName($targetAreaDB[0]->goal_type);
        $goalName       = $targetAreaDB[0]->goal_name;
        $unitName       = $targetAreaDB[0]->unit_name;
        $importantLevel = $targetAreaDB[0]->important_level;
        $benchmark      = $targetAreaDB[0]->benchmark;
        $implementPoint = $targetAreaDB[0]->implement_point;
        $targetValue    = $targetAreaDB[0]->target_value;
        $importantLevel = $targetAreaDB[0]->important_level;
        $defaultData    =   $idTE
                            .','.$compId
                            .','.$areaId
                            .','.$goalId
                            .','.$month
                            .','.$year
                            .','.$targetValue
        ;
    ?>

    <form action="{{action('MajorController@editGoalArea')}}" method="POST" class="form-horizontal margin-left-20" role="form">
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
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Điểm chuẩn: </label>
            <div class="col-sm-10">
                <input type="number" id="txtImplement"  name="txtImplement" readonly class="col-xs-10 col-sm-5"  value="{{ $benchmark }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Điểm thực hiện: </label>
            <div class="col-sm-10">
                <input type="number" id="txtImplement"  name="txtImplement" readonly class="col-xs-10 col-sm-5"  value="{{ $implementPoint }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15" for="form-field-1"> &nbsp; </label>
            <div class="col-sm-10">
                <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                <a href="<?= URL::to('manageGoalArea/'.$compId.'/'.$areaId.'/'.$goalId.'/0/'.$year.'/'.$month);?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#txtTargetValue").focus();
        });
    </script>
@stop