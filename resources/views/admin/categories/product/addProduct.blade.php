@extends('admin.layouts.adminproduct')

@section('section')
    @include('alerts.errors')
    @include('alerts.success')

    <?php
        use Utils\commonUtils;        
    ?>
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
                        <span>Sản phẩm</span>
                    </li>
                </ul>
            </div>
            <!-- END PAGE BAR -->
            </BR>

            <!-- BEGIN PAGE TITLE-->
            <h3 class="page-title font-green"> Thêm Sản Phẩm</h3>
            <!-- END PAGE TITLE-->

            <div class="row">
                <div class="col-md-12">
                    <form class="form-horizontal form-row-seperated" action="#">
                        <div class="portlet">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-shopping-cart"></i>Nhập thông tin SP mới</div>
                                <div class="actions btn-set">
                                    <button id="btnBack" type="button" name="btnBack" class="btn btn-secondary-outline">
                                        <i class="fa fa-angle-left"></i> Back</button>                                    
                                    <button id="btnAdd" type="button" class="btn btn-success">
                                        <i class="fa fa-check"></i> Save</button>
                                </div>
                            </div>

                            <div class="portlet-body">
                                <div class="tabbable-bordered">
											<div class="form-body">
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-2 control-label">Mã SP:
																<span class="required"> * </span>
															</label>
															<div class="col-md-4">
																<input type="text" class="form-control" id="product_code" name="product_code" placeholder="" value=""></div>
																												
															<label class="col-md-2 control-label">Loai SP:
																<span class="required"> * </span>
															</label>
															<div class="col-md-4">
																{{ commonUtils::renderTreeComboBox($arrayProductType, '', -1 , 'table-group-action-input form-control input-medium', 'product_type_id', 'product_type_id', '')}}
															</div>
														</div>
													</div>													
												</div>
												
												<div class="row">													
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-2 control-label">Tên SP:
																<span class="required"> * </span>
															</label>
															<div class="col-md-4">
																<input type="text" class="form-control" id="product_name" name="product_name" placeholder="" value=""></div>												
														
															<label class="col-md-2 control-label">Nhà SX:
																<span class="required"> * </span>
															</label>
															<div class="col-md-4">
																{{ commonUtils::renderCombobox($arrayProducer, -1, 'table-group-action-input form-control input-medium', 'producer_id', 'producer_id', 'Chọn NSX...')}}																
															</div>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-2 control-label">Barcode:</label>
															<div class="col-md-4">
																<input type="text" class="form-control" id="barcode" name="barcode" placeholder="" value=""></div>
													
															<label class="col-md-2 control-label">ĐVT:
																<span class="required"> * </span>
															</label>
															<div class="col-md-4">
																{{ commonUtils::renderCombobox($arrayUnit, -1, 'table-group-action-input form-control input-medium', 'base_unit_id', 'base_unit_id', 'Chọn ĐVT...')}}
															</div>
														</div>
													</div>
												</div>
												            
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-2 control-label">Thương hiệu:</label>
															<div class="col-md-4">
																<input type="text" class="form-control" id="trademark" name="trademark" placeholder="" value=""></div>
														
															<label class="col-md-2 control-label">Kiểu mẫu:</label>
															<div class="col-md-4">
																<input type="text" class="form-control" id="model" name="model" placeholder="" value=""></div>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-2 control-label">Màu sắc:</label>
															<div class="col-md-4">
																<input type="text" class="form-control" id="color" name="color" placeholder="" value=""></div>

															<label class="col-md-2 control-label">Cân nặng:</label>
															<div class="col-md-4">
																<div class="input-group input-daterange" >																									
																	<input type="text" class="form-control" id="weight"  name="weight" placeholder="Cân nặng" value="">
																	<span class="input-group-addon">(kg)</span>																																		
																</div>															
															</div>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-2 control-label">Kích thước:</label>
															<div class="col-md-5">
																<div class="input-group input-daterange" >																									
																	<input type="text" class="form-control" id="length"  name="length" placeholder="Dài" value="">
																	<span class="input-group-addon">(cm)</span>																	
																	<input type="text" class="form-control" id="width" name="width" placeholder="Rộng" value="">
																	<span class="input-group-addon">(cm)</span>
																	<input type="text" class="form-control" id="height" name="height" placeholder="Cao" value="">
																	<span class="input-group-addon">(cm)</span>
																</div>
															</div>															
														</div>
													</div>													
												</div>
												
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-2 control-label">Mô tả ngắn:</label>
															<div class="col-md-10">
																<textarea class="form-control" id="short_description" name="short_description"></textarea>
																<!--span class="help-block"> shown in product listing </span-->
															</div>
														</div>
													</div>
												</div>																							
												
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-2 control-label">Mô tả dài:</label>
															<div class="col-md-10">
																<textarea class="form-control" id="long_description" name="long_description"></textarea>
															</div>
														</div>
													</div>
												</div>

                                            </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        <!-- END CONTENT BODY -->
    </div>
@stop

@section('custom_js')
    <script>
        var path = '{{url('/')}}';
    </script>

    {{ HTML::script('public/assets/scripts/categories/productDetail.js') }}
@stop