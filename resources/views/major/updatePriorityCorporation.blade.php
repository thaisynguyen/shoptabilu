@extends('layouts.dashboard')
@section('page_heading', 'Cập nhật Tỷ trọng Cho Công ty Mobifone')
@section('section')
    @include('alerts.errors')

    <?php

    $id             = $priorityCorporation->id;
    $corporationName= $priorityCorporation->corporation_name;
    $dir            = \Utils\commonUtils::formatDate($priorityCorporation->apply_date);
    $goalType       = \Utils\commonUtils::renderGoalTypeName($priorityCorporation->goal_type);
    $goalName       = $priorityCorporation->goal_name;
    $importantLevel = $priorityCorporation->important_level;
    $benchmark      = $priorityCorporation->benchmark;
    $importantLevel = $priorityCorporation->important_level;
    $targetValue    = ($priorityCorporation->target_value != 0) ? \Utils\commonUtils::formatFloatValue($priorityCorporation->target_value, 4) : 0;

    $defaultData    =
            $id
            .','.$priorityCorporation->corporation_id
            .','.$priorityCorporation->apply_date
            .','.$priorityCorporation->goal_type
            .','.$priorityCorporation->goal_id
            .','.$priorityCorporation->important_level
            .','.$priorityCorporation->benchmark
            .','.$priorityCorporation->formula
            .','.$corporationName
            .','.$priorityCorporation->parent_id
            .','.$targetValue
    ;
    ?>

    <form action="{{action('MajorController@editPriorityCorporation')}}" method="POST" class="form-horizontal margin-left-20" role="form">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <input type="hidden" name="defaultData" value="<?= $defaultData;?>"/>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Công ty: </label>
            <div class="col-sm-10">
                <input type="text" id="" class="col-xs-10 col-sm-5" readonly value="{{ $corporationName }}">
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
                <input type="number" id="txtImportantLevel" name="txtImportantLevel" class="col-xs-10 col-sm-5"  value="{{ $importantLevel }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Điểm chuẩn: </label>
            <div class="col-sm-10">
                <input type="number" id="" readonly name="" class="col-xs-10 col-sm-5"  value="{{ $benchmark }}">
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