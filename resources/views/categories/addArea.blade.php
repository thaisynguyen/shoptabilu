@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.ADD') . ' ' . Config::get('constant.AREA'))
@section('section')
@include('alerts.errors')
@include('alerts.success')

<form action="{{action('CategoriesController@saveArea')}}" method="POST" class="form-horizontal margin-left-20" role="form">
    <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
    <div class="form-group">
        <label class="col-sm-3 control-label padding-right-15 font-label-form" for="form-field-1"> Mã Phòng\Đài\MBF HCM: </label>
        <div class="col-sm-4">
            <select  class="form-control margin-top-8 width-98" id="txtCompanyCode" name="company_code">
                <?php foreach($companies as $row){?>
                <option value="<?php echo $row->company_code ?>"><?php echo $row->company_name ?></option>
                <?php }?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label padding-right-15 font-label-form" for="form-field-1"> Mã Tổ/Quận/Huyện: </label>
        <div class="col-sm-9">
            <input type="text" placeholder=" vd: Q1" class="col-xs-10 col-sm-5" id="txtAreaCode" name="area_code" required autofocus>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label padding-right-15 font-label-form" for="form-field-1"> Tên Tổ/Quận/Huyện: </label>
        <div class="col-sm-9">
            <input type="text" placeholder=" vd: Quận 1" class="col-xs-10 col-sm-5" name="area_name" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label padding-right-15" for="form-field-1"> &nbsp; </label>
        <div class="col-sm-9">
            <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
            <a href="<?= URL::to('areaCategories');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
        </div>
    </div>
</form>
    <script>
        var width= $( window ).width();
        if(width < 550){
            $("#txtCompanyCode").addClass("width-83");
        }else{
            $("#txtCompanyCode").removeClass("width-83");
        }

    </script>
@stop