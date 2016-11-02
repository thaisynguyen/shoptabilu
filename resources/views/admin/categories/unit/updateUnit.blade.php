@extends('admin.layouts.admindashboard')
@section('section')
@include('alerts.errors')
<?php
   // echo $row->unit_description; die;
?>
<form action="{{action('CategoriesController@editUnit')}}" method="POST" class="form-horizontal margin-left-20" role="form">
    <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
    <input type="hidden" name="id" value="<?=  $row->id;?>"/>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Mã đơn vị tính: </label>
        <div class="col-sm-10">
            <input type="text" placeholder=" vd: TH" class="col-xs-10 col-sm-5" id="txtUnitCode" name="unit_code" required value="<?php echo $row->unit_code;?>">
            <input type="hidden" class="form-control" id="txtUnitCode" name="unit_code_hide" value="<?php echo $row->unit_code;?>" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tên đơn vị tính: </label>
        <div class="col-sm-10">
            <input type="text" placeholder=" vd: Thẻ" class="col-xs-10 col-sm-5" name="unit_name" required value="<?php echo $row->unit_name;?>">
            <input type="hidden" class="form-control" id="txtUnitName" name="unit_name_hide" value="<?php echo $row->unit_name;?>" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Mô Tả: </label>
        <div class="col-sm-10">
            <input type="text" placeholder=" vd: Thẻ cào" class="col-xs-10 col-sm-5" name="unit_description" value="<?php echo $row->unit_description;?>">
            <input  type="hidden" hidden class="form-control" name="unit_description_hide" value="<?php echo $row->unit_description;?>" >
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15" for="form-field-1"> &nbsp; </label>
        <div class="col-sm-10">
            <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
            <a href="<?= URL::to('unitCategories');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
        </div>
    </div>
</form>
@stop

@section('custom_js')
    <script type="text/javascript">
        $(document).ready(function() {
            $("#txtUnitCode").focus();
        });
    </script>
@stop