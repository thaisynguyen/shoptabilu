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
            <h3 class="page-title font-green"> Cập Nhật Sản Phẩm </h3>
            <!-- END PAGE TITLE-->

            <div class="row">
                <div class="col-md-12">
                    <form class="form-horizontal form-row-seperated" action="#">
                        <div class="portlet">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-shopping-cart"></i>Mã SP: {{$product->product_code}}</div>
                                <div class="actions btn-set">
                                    <button id="btnBack" type="button" name="btnBack" class="btn btn-secondary-outline">
                                        <i class="fa fa-angle-left"></i> Back</button>                                    
                                    <button id="btnUpdate" type="button" class="btn btn-success">
                                        <i class="fa fa-check"></i> Save</button>
                                </div>
                            </div>

                            <input type="hidden" id="product_id" required value="{{$product->product_id}}"> </div>

                            <div class="portlet-body">
                                <div class="tabbable-bordered">
                                    <ul class="nav nav-tabs">
                                        <li class="active">
                                            <a href="#tab_general" data-toggle="tab">Thông tin chung</a>
                                        </li>
                                        <li>
                                            <a href="#tab_detail" data-toggle="tab">Chi tiết</a>
                                        </li>
                                        <li>
                                            <a href="#tab_images" data-toggle="tab">Hình ảnh</a>
                                        </li>                                        
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab_general">
                                            <div class="form-body">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Mã SP:
																<span class="required"> * </span>
															</label>
															<div class="col-md-9">
																<input type="text" class="form-control" id="product_code" name="product_code" placeholder="" value="{{ $product->product_code }}"></div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Loai SP:
																<span class="required"> * </span>
															</label>
															<div class="col-md-9">
                                                                {{ commonUtils::renderTreeComboBox($arrayProductType, '',$product->product_type_id, 'table-group-action-input form-control input-medium', 'product_type_id', 'product_type_id', '')}}
															</div>
														</div>														
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Tên SP:
																<span class="required"> * </span>
															</label>
															<div class="col-md-9">
																<input type="text" class="form-control" id="product_name" name="product_name" placeholder="" value="{{ $product->product_name }}"></div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Nhà SX:
																<span class="required"> * </span>
															</label>
															<div class="col-md-9">
																{{ commonUtils::renderCombobox($arrayProducer, $product->producer_id, 'table-group-action-input form-control input-medium', 'producer_id', 'producer_id', 'Chọn NSX...')}}																
															</div>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Barcode:</label>
															<div class="col-md-9">
																<input type="text" class="form-control" id="barcode" name="barcode" placeholder="" value="{{ $product->barcode }}"></div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">ĐVT:
																<span class="required"> * </span>
															</label>
															<div class="col-md-9">
																{{ commonUtils::renderCombobox($arrayUnit, $product->base_unit_id, 'table-group-action-input form-control input-medium', 'base_unit_id', 'base_unit_id', 'Chọn ĐVT...')}}
															</div>
														</div>
													</div>
												</div>
												            
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Thương hiệu:</label>
															<div class="col-md-9">
																<input type="text" class="form-control" id="trademark" name="trademark" placeholder="" value="{{ $product->trademark }}"></div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Kiểu mẫu:</label>
															<div class="col-md-9">
																<input type="text" class="form-control" id="model" name="model" placeholder="" value="{{ $product->model }}"></div>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Màu sắc:</label>
															<div class="col-md-9">
																<input type="text" class="form-control" id="color" name="color" placeholder="" value="{{ $product->color }}"></div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Cân nặng:</label>
															<div class="col-md-9">
																<div class="input-group input-daterange" >																									
																	<input type="text" class="form-control" id="weight"  name="weight" placeholder="Cân nặng" value="{{ $product->weight }}">
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
															<div class="col-md-10">
																<div class="input-group input-daterange" >																									
																	<input type="text" class="form-control" id="length"  name="length" placeholder="Dài" value="{{ $product->length }}">
																	<span class="input-group-addon">(cm)</span>																	
																	<input type="text" class="form-control" id="width" name="width" placeholder="Rộng" value="{{ $product->width }}">
																	<span class="input-group-addon">(cm)</span>
																	<input type="text" class="form-control" id="height" name="height" placeholder="Cao" value="{{ $product->height }}">
																	<span class="input-group-addon">(cm)</span>
																</div>
															</div>															
														</div>
													</div>													
												</div>
												
												<div class="form-group">
                                                    <label class="col-md-2 control-label">Mô tả ngắn:</label>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control" id="short_description" name="short_description">{{ $product->short_description }}</textarea>
                                                        <!--span class="help-block"> shown in product listing </span-->
                                                    </div>
                                                </div>
												
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Mô tả dài:</label>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control" id="long_description" name="long_description">{{ $product->long_description }}</textarea>
                                                    </div>
												</div>

                                            </div>
                                        </div>
                                        <div class="tab-pane" id="tab_detail">
                                            <!-- BEGIN EXAMPLE TABLE PORTLET-->
											<div class="portlet light portlet-fit bordered">
												<!--div class="portlet-title">
													<div class="caption">
														<i class="icon-settings font-red"></i>
														<span class="caption-subject font-red sbold uppercase">Editable Table</span>
													</div>
													<div class="actions">
														<div class="btn-group btn-group-devided" data-toggle="buttons">
															<label class="btn btn-transparent red btn-outline btn-circle btn-sm active">
																<input type="radio" name="options" class="toggle" id="option1">Actions</label>
															<label class="btn btn-transparent red btn-outline btn-circle btn-sm">
																<input type="radio" name="options" class="toggle" id="option2">Settings</label>
														</div>
													</div>
												</div-->
												<div class="portlet-body">
													<div class="table-toolbar">
														<div class="row">
															<div class="col-md-6">
																<div class="btn-group">
																	<div class="actions btn-set">
																		<button id="tblProductDetail_new" class="btn green"> Thêm
																			<i class="fa fa-plus"></i>
																		</button>
																		<button id="tblProductDetail_update" class="btn green"> Sửa
																			<i class="fa fa-pencil-square-o"></i>
																		</button>
																		<button id="tblProductDetail_delete" class="btn green"> Xóa
																			<i class="fa fa-minus"></i>
																		</button>
																		<button id="tblProductDetail_save" class="btn green"> Lưu
																			<i class="fa fa-check"></i>
																		</button>
																		<button id="tblProductDetail_cancel" class="btn green"> Hủy
																			<i class="fa fa-remove"></i>
																		</button>
																	</div>
																</div>
															</div>
															<div class="col-md-6">
																<!--div class="btn-group pull-right">
																	<button class="btn green btn-outline dropdown-toggle" data-toggle="dropdown">Tools
																		<i class="fa fa-angle-down"></i>
																	</button>
																	<ul class="dropdown-menu pull-right">
																		<li>
																			<a href="javascript:;"> Print </a>
																		</li>
																		<li>
																			<a href="javascript:;"> Save as PDF </a>
																		</li>
																		<li>
																			<a href="javascript:;"> Export to Excel </a>
																		</li>
																	</ul>
																</div-->
															</div>
														</div>
													</div>
													<table id="tblProductDetail" class="table table-striped table-hover table-bordered">
														<thead>
															<tr>																																
																<th> ID </th>
																<th> Barcode </th>
																<th> Số lượng </th>
																<th> ĐVT </th>
																<th> Tiền tệ </th>																
																<th> Giá mua </th>
																<th> Giá bán </th>
																<th>Ngày áp dụng</th>
																<th> Mã BH </th>
																<th> TG BH </th>																
																<th> Mô tả</th>																
															</tr>
														</thead>
													</table>
												</div>
											</div>
											
											<!-- END EXAMPLE TABLE PORTLET-->
                                        </div>
                                        <div class="tab-pane" id="tab_images">
                                            <div class="alert alert-success margin-bottom-10">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                                                <i class="fa fa-warning fa-lg"></i> Image type and information need to be specified. </div>
                                            <div id="tab_images_uploader_container" class="text-align-reverse margin-bottom-10">
                                                <a id="tab_images_uploader_pickfiles" href="javascript:;" class="btn btn-success">
                                                    <i class="fa fa-plus"></i> Select Files </a>
                                                <a id="tab_images_uploader_uploadfiles" href="javascript:;" class="btn btn-primary">
                                                    <i class="fa fa-share"></i> Upload Files </a>
                                            </div>
                                            <div class="row">
                                                <div id="tab_images_uploader_filelist" class="col-md-6 col-sm-12"> </div>
                                            </div>
                                            <table class="table table-bordered table-hover">
                                                <thead>
                                                <tr role="row" class="heading">
                                                    <th width="8%"> Image </th>
                                                    <th width="25%"> Label </th>
                                                    <th width="8%"> Sort Order </th>
                                                    <th width="10%"> Base Image </th>
                                                    <th width="10%"> Small Image </th>
                                                    <th width="10%"> Thumbnail </th>
                                                    <th width="10%"> </th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td>
                                                        <a href="#" class="fancybox-button" data-rel="fancybox-button">
                                                            <img class="img-responsive" src="{{url('/public/assets/admintheme/pages/media/works/img1.jpg')}}" alt=""> </a>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="product[images][1][label]" value="Thumbnail image"> </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="product[images][1][sort_order]" value="1"> </td>
                                                    <td>
                                                        <label>
                                                            <input type="radio" name="product[images][1][image_type]" value="1"> </label>
                                                    </td>
                                                    <td>
                                                        <label>
                                                            <input type="radio" name="product[images][1][image_type]" value="2"> </label>
                                                    </td>
                                                    <td>
                                                        <label>
                                                            <input type="radio" name="product[images][1][image_type]" value="3" checked> </label>
                                                    </td>
                                                    <td>
                                                        <a href="javascript:;" class="btn btn-default btn-sm">
                                                            <i class="fa fa-times"></i> Remove </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <a href="#" class="fancybox-button" data-rel="fancybox-button">
                                                            <img class="img-responsive" src="{{url('/public/assets/admintheme/pages/media/works/img2.jpg')}}" alt=""> </a>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="product[images][2][label]" value="Product image #1"> </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="product[images][2][sort_order]" value="1"> </td>
                                                    <td>
                                                        <label>
                                                            <input type="radio" name="product[images][2][image_type]" value="1"> </label>
                                                    </td>
                                                    <td>
                                                        <label>
                                                            <input type="radio" name="product[images][2][image_type]" value="2" checked> </label>
                                                    </td>
                                                    <td>
                                                        <label>
                                                            <input type="radio" name="product[images][2][image_type]" value="3"> </label>
                                                    </td>
                                                    <td>
                                                        <a href="javascript:;" class="btn btn-default btn-sm">
                                                            <i class="fa fa-times"></i> Remove </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <a href="#" class="fancybox-button" data-rel="fancybox-button">
                                                            <img class="img-responsive" src="{{url('/public/assets/admintheme/pages/media/works/img3.jpg')}}" alt=""> </a>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="product[images][3][label]" value="Product image #2"> </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="product[images][3][sort_order]" value="1"> </td>
                                                    <td>
                                                        <label>
                                                            <input type="radio" name="product[images][3][image_type]" value="1" checked> </label>
                                                    </td>
                                                    <td>
                                                        <label>
                                                            <input type="radio" name="product[images][3][image_type]" value="2"> </label>
                                                    </td>
                                                    <td>
                                                        <label>
                                                            <input type="radio" name="product[images][3][image_type]" value="3"> </label>
                                                    </td>
                                                    <td>
                                                        <a href="javascript:;" class="btn btn-default btn-sm">
                                                            <i class="fa fa-times"></i> Remove </a>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="tab-pane" id="tab_reviews">
                                            <div class="table-container">
                                                <table class="table table-striped table-bordered table-hover" id="datatable_reviews">
                                                    <thead>
                                                    <tr role="row" class="heading">
                                                        <th width="5%"> Review&nbsp;# </th>
                                                        <th width="10%"> Review&nbsp;Date </th>
                                                        <th width="10%"> Customer </th>
                                                        <th width="20%"> Review&nbsp;Content </th>
                                                        <th width="10%"> Status </th>
                                                        <th width="10%"> Actions </th>
                                                    </tr>
                                                    <tr role="row" class="filter">
                                                        <td>
                                                            <input type="text" class="form-control form-filter input-sm" name="product_review_no"> </td>
                                                        <td>
                                                            <div class="input-group date date-picker margin-bottom-5" data-date-format="dd/mm/yyyy">
                                                                <input type="text" class="form-control form-filter input-sm" readonly name="product_review_date_from" placeholder="From">
                                                                            <span class="input-group-btn">
                                                                                <button class="btn btn-sm default" type="button">
                                                                                    <i class="fa fa-calendar"></i>
                                                                                </button>
                                                                            </span>
                                                            </div>
                                                            <div class="input-group date date-picker" data-date-format="dd/mm/yyyy">
                                                                <input type="text" class="form-control form-filter input-sm" readonly name="product_review_date_to" placeholder="To">
                                                                            <span class="input-group-btn">
                                                                                <button class="btn btn-sm default" type="button">
                                                                                    <i class="fa fa-calendar"></i>
                                                                                </button>
                                                                            </span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-filter input-sm" name="product_review_customer"> </td>
                                                        <td>
                                                            <input type="text" class="form-control form-filter input-sm" name="product_review_content"> </td>
                                                        <td>
                                                            <select name="product_review_status" class="form-control form-filter input-sm">
                                                                <option value="">Select...</option>
                                                                <option value="pending">Pending</option>
                                                                <option value="approved">Approved</option>
                                                                <option value="rejected">Rejected</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <div class="margin-bottom-5">
                                                                <button class="btn btn-sm btn-success filter-submit margin-bottom">
                                                                    <i class="fa fa-search"></i> Search</button>
                                                            </div>
                                                            <button class="btn btn-sm btn-danger filter-cancel">
                                                                <i class="fa fa-times"></i> Reset</button>
                                                        </td>
                                                    </tr>
                                                    </thead>
                                                    <tbody> </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="tab_history">
                                            <div class="table-container">
                                                <table class="table table-striped table-bordered table-hover" id="datatable_history">
                                                    <thead>
                                                    <tr role="row" class="heading">
                                                        <th width="25%"> Datetime </th>
                                                        <th width="55%"> Description </th>
                                                        <th width="10%"> Notification </th>
                                                        <th width="10%"> Actions </th>
                                                    </tr>
                                                    <tr role="row" class="filter">
                                                        <td>
                                                            <div class="input-group date datetime-picker margin-bottom-5" data-date-format="dd/mm/yyyy hh:ii">
                                                                <input type="text" class="form-control form-filter input-sm" readonly name="product_history_date_from" placeholder="From">
                                                                            <span class="input-group-btn">
                                                                                <button class="btn btn-sm default date-set" type="button">
                                                                                    <i class="fa fa-calendar"></i>
                                                                                </button>
                                                                            </span>
                                                            </div>
                                                            <div class="input-group date datetime-picker" data-date-format="dd/mm/yyyy hh:ii">
                                                                <input type="text" class="form-control form-filter input-sm" readonly name="product_history_date_to" placeholder="To">
                                                                            <span class="input-group-btn">
                                                                                <button class="btn btn-sm default date-set" type="button">
                                                                                    <i class="fa fa-calendar"></i>
                                                                                </button>
                                                                            </span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-filter input-sm" name="product_history_desc" placeholder="To" /> </td>
                                                        <td>
                                                            <select name="product_history_notification" class="form-control form-filter input-sm">
                                                                <option value="">Select...</option>
                                                                <option value="pending">Pending</option>
                                                                <option value="notified">Notified</option>
                                                                <option value="failed">Failed</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <div class="margin-bottom-5">
                                                                <button class="btn btn-sm btn-default filter-submit margin-bottom">
                                                                    <i class="fa fa-search"></i> Search</button>
                                                            </div>
                                                            <button class="btn btn-sm btn-danger-outline filter-cancel">
                                                                <i class="fa fa-times"></i> Reset</button>
                                                        </td>
                                                    </tr>
                                                    </thead>
                                                    <tbody> </tbody>
                                                </table>
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