@extends('layouts.dashboard')
@section('page_heading','Thêm Mới Mục Tiêu Cấp 1')
@section('section')

<form action="{{action('CategoriesController@quickSaveGoalLevelOneCategories')}}" method="POST">
    <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
    <div id="wrapper" >
        <div class="row margin-form">
            <div class="col-sm-12 height-45-frm">
                <div class="col-sm-1 text-label padding-top-frm">Mã mục tiêu:</div>
                <div class="col-sm-4 form-group">
                    <input type="text" class="form-control" name="level_one_code" required>
                </div>
                <div class="col-sm-7"></div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-1 text-label ">Tên mục tiêu:</div>
                <div class="col-sm-4 form-group">
                    <input type="text" class="form-control" name="level_one_name" required>
                </div>
                <div class="col-sm-7"></div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-1"></div>
                <div class="col-sm-4 form-group">
                    <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                    <a href="<?= URL::to('addGoalLevelTwoCategories');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
                </div>
                <div class="col-sm-7"></div>
            </div>
        </div>
    </div>
</form>
@stop