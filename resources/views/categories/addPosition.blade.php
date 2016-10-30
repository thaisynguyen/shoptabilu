@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.ADD') . ' ' . Config::get('constant.POSITION'))
@section('section')
@include('alerts.errors')
@include('alerts.success')

<form action="{{action('CategoriesController@savePosition')}}" method="POST" class="form-horizontal margin-left-20" role="form">
    <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Mã chức danh: </label>
        <div class="col-sm-10">
            <input type="text" placeholder=" vd: QT" class="col-xs-10 col-sm-5" id="txtPositionCode" name="position_code" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tên chức danh: </label>
        <div class="col-sm-10">
            <input type="text" placeholder=" vd: Quận Trưởng" class="col-xs-10 col-sm-5" name="position_name" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15" for="form-field-1"> &nbsp; </label>
        <div class="col-sm-10">
            <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
            <a href="<?= URL::to('positionCategories');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function() {
        $("#txtPositionCode").focus();
    });
</script>
@stop