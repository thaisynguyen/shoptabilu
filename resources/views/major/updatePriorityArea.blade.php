@extends('layouts.dashboard')
@section('page_heading', 'Cập nhật Tỷ trọng Cho Tố/Quận/Huyện')
@section('section')
    @include('alerts.errors')

    <?php

    $idTE           = $priorityArea->id;
    $companyName    = $priorityArea->company_name;
    $areaName       = $priorityArea->area_name;
    $dir            = $priorityArea->month.'/'.$priorityArea->year;
    $goalType       = \Utils\commonUtils::renderGoalTypeName($priorityArea->goal_type);
    $goalName       = $priorityArea->goal_name;
    $importantLevel = $priorityArea->important_level;
    $benchmark      = $priorityArea->benchmark;
    $calBenchmark   = $priorityArea->cal_benchmark;
    $importantLevel = $priorityArea->important_level;

    $defaultData    =
            $idTE
            .','.$priorityArea->company_id
            .','.$priorityArea->area_id
            .','.$priorityArea->month
            .','.$priorityArea->year
            .','.$priorityArea->goal_type
            .','.$priorityArea->goal_id
            .','.$priorityArea->important_level
            .','.$priorityArea->benchmark
            .','.$priorityArea->formula
            .','.$calBenchmark
            .','.$areaName
            .','.$priorityArea->parent_id
        ;
    ?>

    <form action="{{action('MajorController@editPriorityArea')}}" method="POST" class="form-horizontal margin-left-20" role="form">
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
                <a href="<?= URL::to('managePriorityArea/'.$priorityArea->company_id.'/'.$priorityArea->area_id.'/'.$priorityArea->goal_id.'/'.$priorityArea->year.'/'.$priorityArea->month);?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#txtImportantLevel").focus();
        });
    </script>
@stop