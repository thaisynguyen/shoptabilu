
<?php
    use Utils\commonUtils;
?>

<div class="row" id="main-content">
    <div class="col-md-12">
        <!-- BEGIN EXAMPLE TABLE PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i> HỒ SƠ CÔNG TY </div>
            </div>
            <div class="portlet-body form">
                <form role="form" class="form-horizontal">
                    <div class="form-body">
                        <div class="form-group">
                            <label class="col-md-4 control-label">Tên</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input type="text" class="form-control" value="{{$data->subject}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Tên viết tắt</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input type="text" class="form-control" value="{{$data->title}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Địa chỉ</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input type="text" class="form-control" value="{{$data->address}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Mã số thuế</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input type="text" class="form-control" value="{{$data->tax_code}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Điện thoại</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input type="text" class="form-control" value="{{$data->phone_number}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Fax</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input type="text" class="form-control" value="{{$data->fax}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Website</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input type="text" class="form-control" value="{{$data->website}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Logo</label>
                            <div class="col-md-8">
                                <div class="input-icon right" class="border: 1px">
                                    <img src="{{url('public/assets/admintheme/upload/images/' . $data->image_name)}}" alt="Logo" width="150" height="150">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">
                                <div class="input-icon right" class="border: 1px">
                                    <input type="file" id="uploadLogo">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
                                <button type="button" class="btn default">Hủy</button>
                                <button type="submit" class="btn blue">Lưu</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
    </div>
</div>

