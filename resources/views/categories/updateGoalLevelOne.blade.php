@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.UPDATE') . ' ' . Config::get('constant.GOAL_1'))
@section('section')
@include('alerts.errors')
<form action="{{action('CategoriesController@editGoalLevelOne')}}" method="POST" class="form-horizontal margin-left-20" role="form">
    <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
    <input type="hidden" name="id" value="<?= $row->id;?>"/>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Mã Mục Tiêu: </label>
        <div class="col-sm-10">
            <input type="text" placeholder=" vd: KH" class="col-xs-10 col-sm-5" id="txtGoalCode" name="goal_code" required value="<?php echo $row->goal_code; ?>">
            <input type="hidden" class="form-control" name="goal_code_hide" id="txtGoalCode" value="<?php echo $row->goal_code; ?>" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tên Mục Tiêu: </label>
        <div class="col-sm-10">
            <input type="text" placeholder=" vd: Khách hàng" class="col-xs-10 col-sm-5" name="goal_name" required value="<?php echo $row->goal_name; ?>">
            <input type="hidden" class="form-control" name="goal_name_hide" value="<?php echo $row->goal_name; ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15" for="form-field-1"> &nbsp; </label>
        <div class="col-sm-10">
            <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
            <a href="<?= URL::to('goalLevelOneCategories');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function() {
        $("#txtGoalCode").focus();
    });
</script>
@stop