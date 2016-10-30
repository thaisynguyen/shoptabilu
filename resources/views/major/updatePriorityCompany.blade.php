@extends('layouts.dashboard')
@section('page_heading', 'Cập nhật Tỷ trọng Cho Phòng/Đài/MBF HCM')
@section('section')
    @include('alerts.errors')

    <?php

    $idTE           = $priorityCompany->id;
    $companyName    = $priorityCompany->company_name;
    $dir            = \Utils\commonUtils::formatDate($priorityCompany->apply_date);
    $goalType       = \Utils\commonUtils::renderGoalTypeName($priorityCompany->goal_type);
    $goalName       = $priorityCompany->goal_name;
    $importantLevel = $priorityCompany->important_level;
    $benchmark      = $priorityCompany->benchmark;
    $calBenchmark   = $priorityCompany->cal_benchmark;
    $importantLevel = $priorityCompany->important_level;
    $targetValue    = ($priorityCompany->target_value != 0) ? \Utils\commonUtils::formatFloatValue($priorityCompany->target_value, 4) : 0;

    $defaultData    =
            $idTE
            .','.$priorityCompany->company_id
            .','.$priorityCompany->apply_date
            .','.$priorityCompany->goal_type
            .','.$priorityCompany->goal_id
            .','.$priorityCompany->important_level
            .','.$priorityCompany->benchmark
            .','.$priorityCompany->formula
            .','.$calBenchmark
            .','.$companyName
            .','.$priorityCompany->parent_id
            .','.$targetValue
    ;
    ?>

    <form action="{{action('MajorController@editPriorityCompany')}}" method="POST" class="form-horizontal margin-left-20" role="form">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <input type="hidden" name="defaultData" value="<?= $defaultData;?>"/>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Phòng/Đài/MBF HCM: </label>
            <div class="col-sm-10">
                <input type="text" id="" class="col-xs-10 col-sm-5" readonly value="{{ $companyName }}">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Ngày áp dụng: </label>
            <div class="col-sm-10">
                <input type="text" id="" class="col-xs-10 col-sm-5" readonly  value="{{ $dir }}">
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
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Kế hoạch: </label>
            <div class="col-sm-10">
                <input type="number" step="0.0001" id="txtTargetValue" name="txtTargetValue" class="col-xs-10 col-sm-5"  value="{{ $targetValue }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tỷ trọng: </label>
            <div class="col-sm-10">
                <input type="text" id="txtImportantLevel" name="txtImportantLevel" class="col-xs-10 col-sm-5"  value="{{ $importantLevel }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Điểm chuẩn: </label>
            <div class="col-sm-10">
                <input type="number" id="" readonly name="" class="col-xs-10 col-sm-5"  value="{{ $benchmark }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Điểm chuẩn Phân bổ: </label>
            <div class="col-sm-10">
                <input type="number" id=""  name="" readonly class="col-xs-10 col-sm-5"  value="{{ $calBenchmark }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15" for="form-field-1"> &nbsp; </label>
            <div class="col-sm-10">
                <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                <a href="#"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#txtTargetValue").focus();
        });
    </script>
@stop