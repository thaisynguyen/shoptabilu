@extends('layouts.dashboard')
@section('page_heading','Thêm Mới Khu Vực')
@section('section')

    <form action="{{action('CategoriesController@quickSaveArea')}}" method="POST">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <div id="wrapper" >
            <div class="row margin-form">
                <div class="col-sm-12">
                    <div class="col-md-2 text-label padding-top-frm" >Mã khu vực:</div>
                    <div class="col-lg-5 margin-txt ">
                        <input type="text" class="form-control txt-30" name="area_code" required>
                    </div>
                    <div class="col-md-5"></div>
                </div>
                <div class="col-sm-12" style="margin-top: 10px;">
                    <div class="col-md-2 text-label padding-top-frm">Tên khu vực:</div>
                    <div class="col-lg-5 margin-txt ">
                        <input type="text" class="form-control txt-30" name="area_name" required>
                    </div>
                    <div class="col-md-5"></div>
                </div>
                <div class="col-sm-12">
                    <div class="col-lg-1 text-label"></div>
                    <div class="col-lg-11">
                        <br>
                        <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                        <a href="<?= URL::to('addEmployee');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop