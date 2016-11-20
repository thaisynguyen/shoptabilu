@extends('admin.layouts.admindashboard')

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
            <h3 class="page-title"> Products Edit
                <small>products edit</small>
            </h3>
            <!-- END PAGE TITLE-->

            <div class="row">
                <div class="col-md-12">
                    <form class="form-horizontal form-row-seperated" action="#">
                        <div class="portlet">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-shopping-cart"></i>Mã SP: {{$product->product_id}}</div>
                                <div class="actions btn-set">
                                    <button id="btnBack" type="button" name="btnBack" class="btn btn-secondary-outline">
                                        <i class="fa fa-angle-left"></i> Back</button>                                    
                                    <button id="btnUpdate" class="btn btn-success">
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
															<label class="col-md-3 control-label">Mã SP:</label>
															<div class="col-md-9">
																<input type="text" class="form-control" name="product_code" placeholder="" value="{{ $product->product_code }}"></div>
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
																<input type="text" class="form-control" name="product_name" placeholder="" value="{{ $product->product_name }}"></div>
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
																<input type="text" class="form-control" name="barcode" placeholder="" value="{{ $product->barcode }}"></div>
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
																<input type="text" class="form-control" name="trademark" placeholder="" value="{{ $product->trademark }}"></div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Kiểu mẫu:</label>
															<div class="col-md-9">
																<input type="text" class="form-control" name="model" placeholder="" value="{{ $product->model }}"></div>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Màu sắc:</label>
															<div class="col-md-9">
																<input type="text" class="form-control" name="color" placeholder="" value="{{ $product->color }}"></div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Cân nặng:</label>
															<div class="col-md-9">
																<input type="text" class="form-control" name="weight" placeholder="" value="{{ $product->weight }}"></div>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<label class="col-md-2 control-label">Kích thước:</label>
															<div class="col-md-10">
																<div class="input-group input-daterange" >
																	<input type="text" class="form-control" name="length" placeholder="Dài" value="{{ $product->length }}">
																	<span class="input-group-addon"> x </span>
																	<input type="text" class="form-control" name="width" placeholder="Rộng" value="{{ $product->width }}">
																	<span class="input-group-addon"> x </span>
																	<input type="text" class="form-control" name="height" placeholder="Cao" value="{{ $product->height }}">
																</div>
															</div>
															
														</div>
													</div>													
												</div>
												
												<div class="form-group">
                                                    <label class="col-md-2 control-label">Mô tả ngắn:</label>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control" name="short_description">{{ $product->short_description }}</textarea>
                                                        <span class="help-block"> shown in product listing </span>
                                                    </div>
                                                </div>
												
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Mô tả dài:</label>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control" name="long_description">{{ $product->long_description }}</textarea>
                                                    </div>
												</div>
                                                
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="tab_detail">
                                            <div class="form-body">
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Meta Title:</label>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control maxlength-handler" name="product[meta_title]" maxlength="100" placeholder="">
                                                        <span class="help-block"> max 100 chars </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Meta Keywords:</label>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control maxlength-handler" rows="8" name="product[meta_keywords]" maxlength="1000"></textarea>
                                                        <span class="help-block"> max 1000 chars </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-2 control-label">Meta Description:</label>
                                                    <div class="col-md-10">
                                                        <textarea class="form-control maxlength-handler" rows="8" name="product[meta_description]" maxlength="255"></textarea>
                                                        <span class="help-block"> max 255 chars </span>
                                                    </div>
                                                </div>
                                            </div>
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

    {{ HTML::script('public/assets/scripts/categories/product.js') }}
@stop