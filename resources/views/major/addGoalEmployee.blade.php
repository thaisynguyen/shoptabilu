@extends('layouts.dashboard')
@section('page_heading', 'Cập nhật Kế Hoạch/ Thực hiện Cho Nhân Viên')
@section('section')
    @include('alerts.errors')
    <?php
        use Utils\CommonUtils;
        $goalTypes = CommonUtils::arrGoalType(0);
    $defaultData =
            $objCompanyDB->id
            .','.$objCompanyDB->company_name
            .','.$objAreaDB->id
            .','.$objAreaDB->area_name
            .','.$objPositionDB->id
            .','.$objPositionDB->position_name
            .','.$objGoalDB->id
            .','.$objGoalDB->goal_type
            .','.$objGoalDB->unit_id
            .','.$year
            .','.$month
            .','.$objPositionDB->position_code
            .','.$objEmployeeDB->id
            .','.$objEmployeeDB->name
            .','.$isTQ
            .','.$objGoalDB->formula
            .','.$objGoalDB->goal_type
    ;
    ?>


    <form action="{{action('MajorController@saveGoalEmployee')}}" method="POST" class="form-horizontal margin-left-20" role="form">

        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <input type="hidden" name="defaultData" value="<?= $defaultData;?>"/>

        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Phòng/Đài/MBF HCM: </label>
            <div class="col-sm-10">
                <input type="text" class="col-xs-10 col-sm-5" readonly  value="{{ $objCompanyDB->company_name; }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tổ/Quận/Huyện: </label>
            <div class="col-sm-10">
                <input type="text" class="col-xs-10 col-sm-5" readonly  value="{{ $objAreaDB->area_name; }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Chức danh: </label>
            <div class="col-sm-10">
                <input type="text" class="col-xs-10 col-sm-5" readonly  value="{{ $objPositionDB->position_name; }}">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Nhân viên: </label>
            <div class="col-sm-10">
                <input type="text" class="col-xs-10 col-sm-5" readonly  value="{{ $objEmployeeDB->name; }}">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Năm áp dụng: </label>
            <div class="col-sm-10">
                <input type="text" id="ipuYear" name="ipuYear" class="col-xs-10 col-sm-5" readonly  value="{{ $year }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tháng áp dụng: </label>
            <div class="col-sm-10">
                <input type="text" id="ipuYear" name="ipuYear" class="col-xs-10 col-sm-5" readonly  value="{{ $month }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Loại mục Tiêu: </label>
            <div class="col-sm-10">
                <input type="text" class="col-xs-10 col-sm-5" readonly  value="{{ \Utils\commonUtils::renderGoalTypeName($objGoalDB->goal_type) }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Mục tiêu: </label>
            <div class="col-sm-10">
                <input type="text" class="col-xs-10 col-sm-5" readonly  value="{{ $objGoalDB->goal_name; }}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Đơn vị tính: </label>
            <div class="col-sm-10">
                <input type="text" class="col-xs-10 col-sm-5" readonly  value="{{ $objGoalDB->unit_name; }}">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Kế hoạch: </label>
            <div class="col-sm-10">
                <input type="number" step="0.01" id="txtTargetValue" name="targetValue" class="col-xs-10 col-sm-5"  placeholder="Vd: 1">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Thực hiện: </label>
            <div class="col-sm-10">
                <input type="number" step="0.01" id="txtImplement"  name="implement" class="col-xs-10 col-sm-5"  placeholder="Vd: 1">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15" for="form-field-1"> &nbsp; </label>
            <div class="col-sm-10">
                <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                <a href="{{ url('manageGoalEmployee/'.$objCompanyDB->id.'/'.$objAreaDB->id.'/'.$objPositionDB->id.'/'.$objEmployeeDB->id.'/0/-1/'.$year.'/'.$month) }}"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#txtTargetValue").focus();
        });
    </script>
@stop