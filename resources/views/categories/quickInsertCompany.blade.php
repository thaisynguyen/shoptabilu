@extends('layouts.dashboard')
@section('page_heading','Thêm Mới Phòng Ban')
@section('section')

    <form action="{{action('CategoriesController@quickSaveCompany')}}" method="POST">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <div id="wrapper" >
            <div class="row margin-form">
                <div class="col-sm-12 height-45-frm">
                    <div class="col-sm-2 text-label padding-top-frm">Mã phòng ban:</div>
                    <div class="col-sm-4 form-group">
                        <input type="text" class="form-control" name="company_code" required>
                    </div>
                    <div class="col-md-6"></div>
                </div>
                <div class="col-sm-12 height-45-frm">
                    <div class="col-sm-2 text-label padding-top-frm">Tên phòng ban:</div>
                    <div class="col-sm-4 form-group">
                        <input type="text" class="form-control" name="company_name" required>
                    </div>
                    <div class="col-md-6"></div>
                </div>
                <div class="col-sm-12 height-45-frm">
                    <div class="col-sm-2 text-label padding-top-frm">Lãnh đạo đơn vị:</div>
                    <div class="col-sm-4 form-group">
                        <input type="text" class="form-control" name="manager" required>
                    </div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="col-sm-12 height-45-frm">
                    <div class="col-sm-2 text-label padding-top-frm">Số điện thoại:</div>
                    <div class="col-sm-4 form-group">
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="col-sm-6"></div>
                </div>

                <div class="col-sm-12">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-4 form-group">
                        <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                        <a href="<?= URL::to('addEmployee');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
                    </div>
                    <div class="col-sm-6"></div>
                </div>
            </div>
        </div>
    </form>
@stop