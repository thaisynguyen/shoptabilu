@extends('admin.layouts.admindashboard')

@section('section')
    @include('alerts.errors')
    @include('alerts.success')

    <?php
        use Utils\commonUtils;
        $curpage =  $data->currentPage();
    ?>
    <div class="page-content-wrapper">
        <!-- BEGIN CONTENT BODY -->
        <div class="page-content">
            <!-- BEGIN PAGE HEADER-->

            <!-- BEGIN PAGE BAR -->
            <div class="page-bar">
                <ul class="page-breadcrumb">
                    <li>
                        <a href="{{url('/adminhome')}}">Trang chủ</a>
                        <i class="fa fa-caret-right"></i>
                    </li>
                    <li>
                        <a href="#">Danh mục</a>
                        <i class="fa fa-caret-right"></i>
                    </li>
                    <li>
                        <span>Sản phẩm</span>
                    </li>
                </ul>
            </div>
            <!-- END PAGE BAR -->
            </BR>

                    <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption font-green">
                                <i class="icon-briefcase font-green"></i>
                                <span class="caption-subject bold uppercase">Danh mục Sản phẩm</span>
                            </div>
                            <div class="tools"> </div>
                        </div>
                        <div class="portlet-body">
                            <table id="tblProduct" class="table table-striped table-bordered table-hover dt-responsive nowrap" width="100%">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th >Mã SP</th>
                                    <th >Tên SP</th>
                                    <th >Barcode</th>
                                    <th >Loại</th>
                                    <th >Nhà SX</th>
                                    <th >Dài (cm)</th>
                                    <th >Rộng (cm)</th>
                                    <th >Cao (cm)</th>
                                    <th >Cân nặng (gam)</th>

                                    <th class="all"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th></th>
                                    <td>NK001</td>
                                    <td>Nikon D90</td>
                                    <td>aawef234234</td>
                                    <td>Nikon</td>
                                    <td>Nikon</td>
                                    <td>20</td>
                                    <td>10</td>
                                    <td>5</td>
                                    <td>5.3</td>

                                    <td>										
										<a href="javascript:;" class="btn btn-icon-only red" data-toggle="confirmation" data-original-title="Are you sure ?" title="" data-placement="top"><i class="fa fa-trash"></i></a>
                                        <a href="#" class="btn btn-icon-only blue"><i class="fa fa-edit"></i></a>
                                    </td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- END EXAMPLE TABLE PORTLET-->

        </div>
        <!-- END CONTENT BODY -->
    </div>
@stop

@section('custom_js')
    {{ HTML::script('public/assets/scripts/categories/product.js') }}
@stop