@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.UPDATE') . ' ' . Config::get('constant.COMPANY'))
@section('section')
@include('alerts.errors')
    <form action="{{action('CategoriesController@editCompany')}}" method="POST" class="form-horizontal margin-left-20" role="form">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <input type="hidden" name="id" value="<?= $row->id;?>"/>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Mã: </label>
            <div class="col-sm-10">
                <input type="text" id="txtCompanyCode" placeholder=" vd: KD" class="col-xs-10 col-sm-5" name="company_code" required value="<?php echo $row->company_code; ?>" disabled>
                <input type="hidden" class="form-control" name="company_code_hide" id="txtCompanyCodeHide" value="<?php echo $row->company_code; ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tên: </label>
            <div class="col-sm-10">
                <input type="text" id="form-field-2" placeholder=" vd: Phòng Kinh Doanh" class="col-xs-10 col-sm-5" name="company_name" required value="<?php echo $row->company_name; ?>">
                <input type="hidden" class="form-control" name="company_name_hide" value="<?php echo $row->company_name; ?>" required >
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Lãnh đạo đơn vị: </label>
            <div class="col-sm-10">
                <input type="text" id="form-field-3" placeholder=" vd: Nguyễn Văn A" class="col-xs-10 col-sm-5" name="manager" value="<?php echo $row->manager; ?>" required>
                <input type="hidden" class="form-control" name="manager_hide" value="<?php echo $row->manager; ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Số điện thoại: </label>
            <div class="col-sm-10">
                <input type="text" id="form-field-4" placeholder=" vd: 090 183 25 79" class="col-xs-10 col-sm-5" name="phone" value="<?php echo $row->phone; ?>">
                <input type="hidden" class="form-control" name="phone_hide" value="<?php echo $row->phone; ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15" for="form-field-1"> &nbsp; </label>
            <div class="col-sm-10">
                <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                <a href="<?= URL::to('listCompanies');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#txtCompanyCode").focus();
        });
    </script>
@stop