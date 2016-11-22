
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
                                    <input id="subject" name="subject" type="text" class="form-control" value="{{$data->subject}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Tên viết tắt</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input id="title"  name="title" type="text" class="form-control" value="{{$data->title}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Địa chỉ</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input id="address" name="address" type="text" class="form-control" value="{{$data->address}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Mã số thuế</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input id="tax_code" name="tax_code" type="text" class="form-control" value="{{$data->tax_code}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Điện thoại</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input id="phone_number" name="phone_number" type="text" class="form-control" value="{{$data->phone_number}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Fax</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input id="fax" name="fax" type="text" class="form-control" value="{{$data->fax}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Email</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input id="email" name="email" type="text" class="form-control" value="{{$data->email}}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-4 control-label">Website</label>
                            <div class="col-md-8">
                                <div class="input-icon right">
                                    <input id="website" name="website" type="text" class="form-control" value="{{$data->website}}"></div>
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
                                    <input id="file" name="file" type="file">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-4 col-md-8">
                                <button id="btnCancel" type="button" class="btn default">Hủy</button>
                                <button id="btnSave" type="button" class="btn blue">Lưu</button>
                                <input type="hidden" id="company_id" required value="{{$data->company_id}}"> </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
    </div>
</div>

