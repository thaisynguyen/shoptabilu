@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.ADD') . ' ' . Config::get('constant.COMPANY'))
@section('section')
@include('alerts.errors')
@include('alerts.success')

    <form action="{{action('CategoriesController@saveCompany')}}" method="POST" class="form-horizontal margin-left-20" role="form">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <div class="form-group col-sm-12">
            <div class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Mã: </div>
            <div class="col-sm-10">
                <input type="text" id="form-field-1" placeholder=" vd: MBF1" class="col-xs-10 col-sm-5" name="company_code" required>
            </div>
        </div>
        <div class="form-group col-sm-12">
            <div class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Tên: </div>
            <div class="col-sm-10">
                <input type="text" id="form-field-2" placeholder=" vd: Mobifone 1" class="col-xs-10 col-sm-5" name="company_name" required>
            </div>
        </div>
        <div class="form-group col-sm-12">
            <div class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Lãnh đạo đơn vị: </div>
            <div class="col-sm-10">
                <input type="text" id="form-field-3" placeholder=" vd: Nguyễn Văn A" class="col-xs-10 col-sm-5" name="manager" required>
            </div>
        </div>
        <div class="form-group col-sm-12">
            <div class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Số điện thoại: </div>
            <div class="col-sm-10">
                <input type="text" id="form-field-4" placeholder=" vd: 090 183 25 79" class="col-xs-10 col-sm-5" name="phone">
            </div>
        </div>
        <div class="form-group col-sm-12">
            <div class="col-sm-2 control-label padding-right-15" for="form-field-1"> &nbsp; </div>
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