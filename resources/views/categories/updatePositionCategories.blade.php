@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.UPDATE') . ' ' . Config::get('constant.POSITION'))
@section('section')
@include('alerts.errors')
<form action="{{action('CategoriesController@editPosition')}}" method="POST" class="form-horizontal margin-left-20" role="form">
    <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
    <input type="hidden" name="id" value="<?php echo $row->id;?>"/>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Mã chức danh: </label>
        <div class="col-sm-10">
            <input type="text" placeholder=" vd: QT" class="col-xs-10 col-sm-5" id="txtPositionCode" name="position_code" required value="<?php echo $row->position_code;?>" readonly>
            <input type="hidden" class="form-control txt-30" id="txtPositionCode" name="position_code_hide" value="<?php echo $row->position_code;?>" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tên chức danh: </label>
        <div class="col-sm-10">
            <input type="text" placeholder=" vd: Quận Trưởng" class="col-xs-10 col-sm-5" name="position_name" required value="<?php echo $row->position_name;?>">
            <input type="hidden" class="form-control txt-30" name="position_name_hide" value="<?php echo $row->position_name;?>" required>
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