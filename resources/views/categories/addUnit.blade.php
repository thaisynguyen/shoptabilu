@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.ADD') . ' ' . Config::get('constant.UNIT'))
@section('section')
@include('alerts.errors')
@include('alerts.success')

<form action="{{action('CategoriesController@saveUnit')}}" method="POST" class="form-horizontal margin-left-20" role="form">
    <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Mã đơn vị tính: </label>
        <div class="col-sm-10">
            <input type="text" placeholder=" vd: TH" class="col-xs-10 col-sm-5" id="txtUnitCode" name="unit_code" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tên đơn vị tính: </label>
        <div class="col-sm-10">
            <input type="text" placeholder=" vd: Thẻ" class="col-xs-10 col-sm-5" name="unit_name" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Mô Tả: </label>
        <div class="col-sm-10">
            <input type="text" placeholder=" vd: Thẻ cào" class="col-xs-10 col-sm-5" name="unit_description">
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

{{ HTML::script('assets/scripts/common.js') }}
<script>
    $( document ).ready(function() {
        $("#txtUnitCode").focus();
        //console.log(browserName());
        if(browserName()=='Firefox'){
            $("#button-bottom").attr('style',  'padding-top: 35px;');
        }else{
            $("#button-bottom").attr('style',  'padding-top: 20px;');
        }
    });
</script>
@stop