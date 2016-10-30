@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.UPDATE') . ' ' . Config::get('constant.AREA'))
@section('section')
@include('alerts.errors')
<form action="{{action('CategoriesController@editArea')}}" method="POST" class="form-horizontal margin-left-20" role="form">
    <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
    <input type="hidden" name="id" value="<?php echo $row->id;?>"/>
    <div class="form-group">
        <label class="col-sm-3 control-label padding-right-15 font-label-form" for="form-field-1"> Mã Phòng\Đài\MBF HCM: </label>
        <div class="col-sm-4">
            <select  class="form-control margin-top-8 width-98" id="txtCompanyCode" name="company_code">
                <option value="<?php echo $companyCode->company_code ?>"><?php echo $companyCode->company_name?></option>
                <?php foreach($company as $rowC){
                        if($rowC->company_code != $companyCode->company_code){?>
                             <option value="<?php echo $rowC->company_code ?>"><?php echo $rowC->company_name ?></option>
                <?php }
                 }?>
            </select>
            <input type="hidden" class="col-xs-10 col-sm-5" id="txtAreaCodeHide" name="company_code_hide" value="<?php echo $companyCode->company_code; ?>" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label padding-right-15 font-label-form" for="form-field-1"> Mã Tổ/Quận/Huyện: </label>
        <div class="col-sm-9">
            <input type="text" placeholder=" vd: Q1" class="col-xs-10 col-sm-5" id="txtAreaCode" name="area_code" required value="<?php echo $row->area_code; ?>">
            <input type="hidden" class="col-xs-10 col-sm-5" id="txtAreaCodeHide" name="area_code_hide" value="<?php echo $row->area_code; ?>" required>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label padding-right-15 font-label-form" for="form-field-1"> Tên Tổ/Quận/Huyện: </label>
        <div class="col-sm-9">
            <input type="text" placeholder=" vd: Quận 1" class="col-xs-10 col-sm-5" name="area_name" required value="<?php echo $row->area_name; ?>">
            <input type="hidden" class="col-xs-10 col-sm-5" name="area_name_hide" value="<?php echo $row->area_name; ?>" required>
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