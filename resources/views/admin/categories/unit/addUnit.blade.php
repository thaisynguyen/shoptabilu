@extends('admin.layouts.admindashboard')
@section('section')
@include('alerts.errors')
@include('alerts.success')
<div class="page-content-wrapper">
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->

        <!-- BEGIN PAGE BAR -->
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <a href="{{url('/adminhome')}}">Trang chủ</a>
                    <i class="fa fa-circle"></i>
                </li>
                <li>
                    <a href="#">Danh mục</a>
                    <i class="fa fa-circle"></i>
                </li>
                <li>
                    <span>Đơn vị tính</span>
                </li>
            </ul>
        </div>
        <!-- END PAGE BAR -->
        </BR>
        <div class="row">
            <div class="col-md-12">
                <form action="{{action('CategoriesController@saveUnit')}}" method="POST" role="form">
                    <input type="hidden" name="_token" value="<?= csrf_token();?>"/>


                    <div class="portlet box blue ">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-gift"></i> Thêm Mới Đơn Vị Tính </div>
                            <div class="tools">
                                <a href="" class="collapse"> </a>
                                <a href="#portlet-config" data-toggle="modal" class="config"> </a>
                                <a href="" class="reload"> </a>
                                <a href="" class="remove"> </a>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form role="form">
                                <div class="form-body">
                                    <div class="form-group clear-margin">
                                        <label class="control-label">Mã</label>
                                        <input type="text" class="form-control" id="txtUnitCode">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Tên</label>
                                        <input type="text" class="form-control" id="txtUnitName">
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <a href="<?= URL::to('unitCategories');?>"><button type="button" class="btn default">Cancel</button></a>
                                    <button type="submit" class="btn green">Lưu</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>


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