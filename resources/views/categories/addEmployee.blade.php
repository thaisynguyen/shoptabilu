@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.ADD') . ' ' . Config::get('constant.EMPLOYEE'))
@section('section')
@include('alerts.success')
@include('alerts.errors')

    <form action="{{action('CategoriesController@saveEmployee')}}" method="POST">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <div id="wrapper" >
            <div class="row margin-form">
                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Phòng/Đài/MBF HCM:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <select class="form-control combobox-99" id="cboCompany"  required name="company_id"
                                onchange="reloadPageWithParam('{{action('CategoriesController@addEmployee')}}'
                                        , 'addEmployee'
                                        , '/cboCompany')">
                            <?php  foreach($companies as $company){ ?>
                            <option value="<?php echo $company->id;?>" <?php if($company->id == $selectedCompany){  echo 'selected'; } ?>><?php echo $company->company_name;?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-3 col-sm-6">
                        <a role="button" class="btn btn-primary change-color float_left" href="#" data-target=".dialog-add-company" data-toggle="modal">
                            <i class="fa fa-plus" title="Thêm mới" ></i>
                        </a>
                        <div aria-hidden="false" aria-labelledby="delete-confirmation" role="dialog" tabindex="-1" class="modal fade dialog-add-company">
                            <form accept-charset="utf-8" method="post" action="#">
                                <div class="modal-dialog">
                                    <div class="modal-content text-left">
                                        <div class="modal-header">
                                            <button data-dismiss="modal" class="close" type="button btn-sm" id="btnCloseCompany">
                                                <span tabindex="8" aria-hidden="true">×</span>
                                                <span class="sr-only">Đóng</span>
                                            </button>
                                            <strong id="myModalLabel" class="modal-title">Thêm Phòng Ban</strong>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row margin-popup-add">
                                                <div class="col-sm-12">
                                                    <div class="col-sm-3 text-label padding-top-frm" >Mã:</div>
                                                    <div class="col-sm-6 form-group">
                                                        <input type="text" class="form-control" id="company_code" name="company_code" >
                                                    </div>
                                                    <div class="col-md-3"></div>
                                                </div>
                                                <div class="col-sm-12 ">
                                                    <div class="col-sm-3 text-label padding-top-frm">Tên:</div>
                                                    <div class="col-sm-6 form-group">
                                                        <input type="text" class="form-control" id="company_name" name="company_name" >
                                                    </div>
                                                    <div class="col-md-3"></div>
                                                </div>
                                                <div class="col-sm-12 ">
                                                    <div class="col-sm-3 text-label padding-top-frm">Lãnh đạo đơn vị:</div>
                                                    <div class="col-sm-6 form-group">
                                                        <input type="text" class="form-control" id="manager" name="manager" >
                                                    </div>
                                                    <div class="col-sm-3"></div>
                                                </div>
                                                <div class="col-sm-12 ">
                                                    <div class="col-sm-3 text-label padding-top-frm">Số điện thoại:</div>
                                                    <div class="col-sm-6 form-group">
                                                        <input type="text" class="form-control" id="phone" name="phone">
                                                    </div>
                                                    <div class="col-sm-3"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 20px;">
                                                    <div class="col-md-3 text-label padding-top-frm"></div>
                                                    <div class="col-lg-7 " style="margin-left: -15px; color: red;" id="text_company">
                                                    </div>
                                                    <div class="col-md-2"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary btn-save" id="quickSaveCompany"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Tổ/Quận/Huyện:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <select class="form-control combobox-99" id="cboArea" required name="area_id">
                            <?php  foreach($areas as $area){ ?>
                            <option value="<?php echo $area->id;?>"><?php echo $area->area_name;?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-3 col-sm-6">
                        <a role="button" class="btn btn-primary change-color float_left" href="#" data-target=".dialog-add-area" data-toggle="modal">
                            <i class="fa fa-plus" title="Thêm mới" ></i>
                        </a>
                        <div aria-hidden="false" aria-labelledby="delete-confirmation" role="dialog" tabindex="-1" class="modal fade dialog-add-area">
                            <form accept-charset="utf-8" method="post" action="#">
                                <div class="modal-dialog">
                                    <div class="modal-content text-left">
                                        <div class="modal-header">
                                            <button data-dismiss="modal" class="close" type="button btn-sm" id="btnCloseArea">
                                                <span tabindex="8" aria-hidden="true">×</span>
                                                <span class="sr-only">Đóng</span>
                                            </button>
                                            <strong id="myModalLabel" class="modal-title">Thêm Mới Khu Vực</strong>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row margin-popup-add">
                                                <div class="col-sm-12">
                                                    <div class="col-sm-3 text-label " >Mã:</div>
                                                    <div class="col-sm-6 ">
                                                        <input type="text" class="form-control txt-30" id="area_code" name="area_code">
                                                    </div>
                                                    <div class="col-sm-3"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 10px;">
                                                    <div class="col-sm-3 text-label ">Tên:</div>
                                                    <div class="col-sm-6  ">
                                                        <input type="text" class="form-control txt-30" id="area_name" name="area_name">
                                                    </div>
                                                    <div class="col-sm-3"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 20px;">
                                                    <div class="col-md-3 text-label "></div>
                                                    <div class="col-lg-7 " style="margin-left: -15px; color: red;" id="text_area">
                                                    </div>
                                                    <div class="col-md-2"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary btn-save" id="quickSaveArea"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Chức danh:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <select class="form-control combobox-99"  id="slPosition" required name="position_id">
                            <?php  foreach($positions as $position){ ?>
                            <option value="<?php echo $position->id;?>"><?php echo $position->position_name;?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-3 col-sm-6">
                        <a role="button" class="btn btn-primary change-color float_left" href="#" data-target=".dialog-add-position" data-toggle="modal">
                            <i class="fa fa-plus" title="Thêm mới" ></i>
                        </a>
                        <div aria-hidden="false" aria-labelledby="delete-confirmation" role="dialog" tabindex="-1" class="modal fade dialog-add-position">
                            <form accept-charset="utf-8" method="post" action="#">
                                <div class="modal-dialog">
                                    <div class="modal-content text-left">
                                        <div class="modal-header">
                                            <button data-dismiss="modal" class="close" type="button btn-sm" id="btnClosePosition">
                                                <span tabindex="8" aria-hidden="true">×</span>
                                                <span class="sr-only">Đóng</span>
                                            </button>
                                            <strong id="myModalLabel" class="modal-title">Thêm Chức Danh</strong>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row margin-form-add">
                                                <div class="col-sm-12">
                                                    <div class="col-md-3 text-label padding-top-frm">Mã chức danh:</div>
                                                    <div class="col-lg-7 " style="margin-left: -30px;">
                                                        <input type="text" class="form-control txt-30" name="position_code" id="position_code" >
                                                    </div>
                                                    <div class="col-md-2"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 10px;">
                                                    <div class="col-md-3 text-label padding-top-frm">Tên chức danh:</div>
                                                    <div class="col-lg-7 " style="margin-left: -30px;">
                                                        <input type="text" class="form-control txt-30" name="position_name" id="position_name" >
                                                    </div>
                                                    <div class="col-md-2"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 20px;">
                                                    <div class="col-md-3 text-label padding-top-frm" ></div>
                                                    <div class="col-lg-7 " style="margin-left: -30px; color: red;"id="text_position" >
                                                    </div>
                                                    <div class="col-md-2"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary btn-save" id="quickSavePosition"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Nhóm/Cửa hàng:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <select class="form-control combobox-99" id="slGroup" required name="group_id" value="" >
                            <?php  foreach($groups as $group){ ?>
                            <option value="<?php echo $group->id;?>"><?php echo $group->group_name;?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-3 col-sm-6">
                        <a role="button" class="btn btn-primary change-color float_left" href="#" data-target=".dialog-add-group" data-toggle="modal">
                            <i class="fa fa-plus" title="Thêm mới" ></i>
                        </a>
                        <div aria-hidden="false" aria-labelledby="delete-confirmation" role="dialog" tabindex="-1" class="modal fade dialog-add-group">
                            <form accept-charset="utf-8" method="post" action="#">
                                <div class="modal-dialog">
                                    <div class="modal-content text-left">
                                        <div class="modal-header">
                                            <button data-dismiss="modal" class="close" type="button btn-sm" id="btnCloseGroup">
                                                <span tabindex="8" aria-hidden="true">×</span>
                                                <span class="sr-only">Đóng</span>
                                            </button>
                                            <strong id="myModalLabel" class="modal-title">Thêm Nhóm/Cửa hàng</strong>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row margin-form-add">
                                                <div class="col-sm-12">
                                                    <div class="col-md-3 text-label padding-top-frm">Mã:</div>
                                                    <div class="col-lg-6 " style="margin-left: -10px;">
                                                        <input type="text" class="form-control txt-30" id="group_code" name="group_code" >
                                                    </div>
                                                    <div class="col-md-3"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 10px;">
                                                    <div class="col-md-3 text-label padding-top-frm">Tên:</div>
                                                    <div class="col-lg-6 " style="margin-left: -10px;">
                                                        <input type="text" class="form-control txt-30" id="group_name"  name="group_name" >
                                                    </div>
                                                    <div class="col-md-3"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 20px;">
                                                    <div class="col-md-3 text-label padding-top-frm"></div>
                                                    <div class="col-lg-7 " style="margin-left: -9px; color: red;" id="text_group">
                                                    </div>
                                                    <div class="col-md-2"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary btn-save" id="quickSaveGroup"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Mức truy cập:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <select class="form-control combobox-99" id="slAccessLevel" required name="access_level" value="" >
                            <?php  foreach($access_levels as $access_level){ ?>
                            <option value="<?php echo $access_level->id;?>"><?php echo $access_level->access_level_name;?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-3 col-sm-6">
                        <a role="button" class="btn btn-primary change-color float_left" href="#" data-target=".dialog-add-access-level" data-toggle="modal">
                            <i class="fa fa-plus" title="Thêm mới" ></i>
                        </a>
                        <div aria-hidden="false" aria-labelledby="delete-confirmation" role="dialog" tabindex="-1" class="modal fade dialog-add-access-level">
                            <form accept-charset="utf-8" method="post" action="#">
                                <div class="modal-dialog">
                                    <div class="modal-content text-left">
                                        <div class="modal-header">
                                            <button data-dismiss="modal" class="close" type="button btn-sm" id="btnCloseAccessLevel">
                                                <span tabindex="8" aria-hidden="true">×</span>
                                                <span class="sr-only">Đóng</span>
                                            </button>
                                            <strong id="myModalLabel" class="modal-title">Thêm Mức Truy Cập</strong>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row margin-form-add">
                                                <div class="col-sm-12">
                                                    <div class="col-md-3 text-label padding-top-frm">Mã mức truy cập:</div>
                                                    <div class="col-lg-6 " style="margin-left: -10px;">
                                                        <input type="text" class="form-control txt-30" id="access_level_code" name="access_level_code" >
                                                    </div>
                                                    <div class="col-md-3"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 10px;">
                                                    <div class="col-md-3 text-label padding-top-frm">Tên mức truy cập:</div>
                                                    <div class="col-lg-6 " style="margin-left: -10px;">
                                                        <input type="text" class="form-control txt-30" id="access_level_name" name="access_level_name" >
                                                    </div>
                                                    <div class="col-md-3"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 10px;">
                                                    <div class="col-md-3 text-label padding-top-frm">Mức truy cập:</div>
                                                    <div class="col-lg-6" style="margin-left: -10px;">
                                                        <input type="text" class="form-control txt-30" id="level" name="level" >
                                                    </div>
                                                    <div class="col-md-3"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 20px;">
                                                    <div class="col-md-3 text-label padding-top-frm"></div>
                                                    <div class="col-lg-7 " style="margin-left: -9px; color: red;" id="text_al">
                                                    </div>
                                                    <div class="col-md-2"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary btn-save" id="quickSaveAccessLevel"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 height-45-frm">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Mã nhân viên:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <input type="text" class="form-control" id="txtEmployeeCode" required name="code">
                    </div>
                    <div class="col-xs-3 col-sm-6"></div>
                </div>

                <div class="col-sm-12 height-45-frm">
                    <div class="col-xs-12 col-sm-2 text-label padding-right-15 font-label-form text-right">Họ và tên:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <input type="text" class="form-control" required name="name">
                    </div>
                    <div class="col-xs-3 col-sm-6"></div>
                </div>

                <div class="col-sm-12 height-45-frm">
                    <div class="col-xs-12 col-sm-2 text-label padding-right-15 font-label-form text-right">Tên đăng nhập:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <input type="text" class="form-control" required name="username" placeholder="chaunp1991">
                    </div>
                    <div class="col-xs-3 col-sm-6"></div>
                </div>

                <div class="col-sm-12 height-45-frm">
                    <div class="col-xs-12 col-sm-2 text-label padding-right-15 font-label-form text-right">Mật khẩu:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <input type="password" class="form-control" required name="password">
                    </div>
                    <div class="col-xs-3 col-sm-6"></div>
                </div>

                <div class="col-sm-12 height-45-frm">
                    <div class="col-xs-4 col-sm-2 text-label padding-right-15 font-label-form text-right">Chỉ xem:</div>
                    <div class="col-xs-6 col-sm-4 text-left">
                        <input type="checkbox" id="readonly" class="checkbox-readonly chkAdd" name="is_view">
                    </div>
                    <div class="col-xs-2 col-sm-6"></div>
                </div>

                <div class="col-sm-12 height-45-frm">
                    <div class="col-xs-4 col-sm-2 text-label padding-right-15 font-label-form text-right">LDAP:</div>
                    <div class="col-xs-6 col-sm-4">
                        <input type="checkbox" id="readonly" class="checkbox-readonly chkAdd" name="is_ldap" checked>
                    </div>
                    <div class="col-xs-2 col-sm-6"></div>
                </div>

                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2"></div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                        <a href="<?= URL::to('employeeCategories');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
                    </div>
                    <div class="col-xs-3 col-sm-6"></div>
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript">

        $('#quickSavePosition').on('click', function(e){
            var position_name = $('#position_name').val();
            var position_code = $('#position_code').val();
            $.get('quickSavePosition',{position_name: position_name, position_code:position_code}, function(data){
                if (data != false) {
                    $('#position_name').val('');
                    $('#position_code').val('');
                    var option = $("<option></option>").attr("value",data).text(position_name);
                    $('#slPosition').append(option);
                    $("#position_code").focus();
                    $('#text_position').text('Thêm chức danh thành công !');
                } else {
                    $('#text_position').text('Thêm chức danh không thành công !');
                }
            })
        });


        $('#quickSaveCompany').on('click', function(e){
            var company_name = $('#company_name').val();
            var company_code = $('#company_code').val();
            var manager = $('#manager').val();
            var phone = $('#phone').val();
            $.get('quickSaveCompany',{company_name: company_name, company_code:company_code, manager:manager, phone:phone}, function(data){
                if (data != false) {
                    console.log(data);
                    $('#company_name').val('');
                    $('#company_code').val('');
                    $('#manager').val('');
                    $('#phone').val('');
                    var option = $("<option></option>").attr("value",data).text(company_name);
                    $('#slCompany').append(option);
                    $("#company_code").focus();
                    $('#text_company').text('Thêm phòng ban thành công !');
                } else {
                    $('#text_company').text('Thêm phòng ban không thành công !');
                }
            })
        });


        $('#quickSaveAccessLevel').on('click', function(e){
            var access_level_name = $('#access_level_name').val();
            var access_level_code = $('#access_level_code').val();
            var level = $('#level').val();

            console.log(access_level_name+'-'+access_level_code);
            $.get('quickSaveAccessLevel',{access_level_name: access_level_name, access_level_code:access_level_code, level:level}, function(data){
                if (data != false) {
                    $('#access_level_code').val('');
                    $('#access_level_name').val('');
                    $('#level').val('');
                    var option = $("<option></option>").attr("value",data).text(access_level_name);
                    $('#slAccessLevel').append(option);
                    $("#access_level_code").focus();
                    $('#text_al').text('Thêm mức truy cập thành công !');
                } else {
                    $('#text_al').text('Thêm mức truy cập không thành công !');
                }
            })
        });


        $('#quickSaveArea').on('click', function(e){
            var area_name = $('#area_name').val();
            var area_code = $('#area_code').val();
            $.get('quickSaveArea',{area_name: area_name, area_code:area_code}, function(data){
                if (data != false) {
                    $('#area_name').val('');
                    $('#area_code').val('');
                    var option = $("<option></option>").attr("value",data).text(area_name);
                    $('#slArea').append(option);
                    $("#area_code").focus();
                    $('#text_area').text('Thêm khu vực thành công !');
                } else {
                    $('#text_area').text('Thêm khu vực không thành công !');
                }
            })
        });

        $('#quickSaveGroup').on('click', function(e){
            var group_name = $('#group_name').val();
            var group_code = $('#group_code').val();
            $.get('quickSaveGroup',{group_name: group_name, group_code:group_code}, function(data){
                if (data != false) {
                    $('#group_name').val('');
                    $('#group_code').val('');
                    var option = $("<option></option>").attr("value",data).text(group_name);
                    $('#slGroup').append(option);
                    $("#group_code").focus();
                    $('#text_group').text('Thêm group thành công !');
                } else {
                    $('#text_group').text('Thêm group không thành công !');
                }
            })
        });

        $('#btnCloseCompany').on('click', function(e){
            $('#text_company').text('');
        });

        $('#btnClosePosition').on('click', function(e){
            $('#text_position').text('');
        });

        $('#btnCloseArea').on('click', function(e){
            $('#text_area').text('');
        });

        $('#btnCloseGroup').on('click', function(e){
            $('#text_area').text('');
        });

        $('#btnCloseAccessLevel').on('click', function(e){
            $('#text_al').text('');
        });

        $('#btnCloseGroup').on('click', function(e){
            $('#text_group').text('');
        });
        $(document).ready(function() {
            $("#txtEmployeeCode").focus();


            console.log($(window).width());
            $( window ).resize(function() {
                if($(window).width() < 600){
                  //  $('.login-background').css('background-position', '-650px');
                }else{
                 //   $('.login-background').css('background-position', '0');
                }

            });

        });
    </script>
@stop