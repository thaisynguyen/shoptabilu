@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.UPDATE') . ' ' . Config::get('constant.EMPLOYEE'))
@section('section')
@include('alerts.errors')

    <form action="{{action('CategoriesController@editEmployee')}}" method="POST">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <input type="hidden" name="id" value="<?= $row->id;?>"/>
        <div id="wrapper" >
            <div class="row margin-form">
                <div class="col-sm-12">
                    <div class="col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Phòng/Đài/MBF HCM:</div>
                    <div class="col-sm-4 form-group">
                        <select class="form-control combobox-99" id="cboCompany" required name="company_id"

                                onchange="reloadPage('{{action('CategoriesController@updateEmployee')}}'
                                        , '{{$row->id}}'
                                        , '{{$page}}')">
                            <?php  foreach($companies as $company){ ?>
                            <option value="<?php echo $company->id;?>" <?php if($company->id == $selectedCompany) echo 'selected'; ?>><?php echo $company->company_name;?></option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="company_id_hide" value="<?php echo $row->company_id;?>" >
                    </div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Tổ/Quận/Huyện:</div>
                    <div class="col-sm-4 form-group">
                        <select class="form-control combobox-99" id="cboArea" required name="area_id">
                            <?php  foreach($areas as $area){ ?>
                            <option value="<?php echo $area->id;?>" <?php if($area->id == $selectedArea) echo 'selected'; ?>><?php echo $area->area_name;?></option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="area_id_hide" value="<?php echo $row->area_id;?>" >
                    </div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Chức danh:</div>
                    <div class="col-sm-4 form-group">
                        <select class="form-control combobox-99"  required name="position_id">
                            <?php  foreach($positions as $position){ ?>
                            <option value="<?php echo $position->id;?>" <?php if($position->id == $selectedPosition) echo 'selected'; ?>><?php echo $position->position_name;?></option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="position_id_hide" value="<?php echo $row->position_id;?>" >
                    </div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Nhóm/Cửa hàng:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <select class="form-control combobox-99" id="slGroup" required name="group_id" value="" >
                            <?php  foreach($groups as $group){ ?>
                            <option value="<?php echo $group->id; ?>"  <?php if($group->id == $selectedGroup){  echo 'selected'; } ?>><?php echo $group->group_name; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <input type="hidden" name="group_id_hide" value="<?php echo $row->position_id;?>" >
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Mức truy cập:</div>
                    <div class="col-sm-4 form-group">
                        <select class="form-control combobox-99" required name="access_level">
                            <?php  foreach($access_levels as $access_level){ ?>
                            <option value="<?php echo $access_level->id;?>" <?php if($access_level->id == $selectedAccessLevel) echo 'selected'; ?>><?php echo $access_level->access_level_name;?></option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="access_level_hide" value="<?php echo $row->access_level;?>" >
                    </div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="col-sm-12 height-45-frm">
                    <div class="col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Mã nhân viên:</div>
                    <div class="col-sm-4 form-group">
                        <input type="text" class="form-control" id="txtEmployeeCode" required name="code" value="<?php echo $row->code; ?>">
                        <input type="hidden" class="form-control" id="txtEmployeeCode" required name="code_hide" value="<?php echo $row->code; ?>">
                    </div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="col-sm-12 height-45-frm">
                    <div class="col-sm-2 text-label padding-right-15 font-label-form text-right">Họ và tên:</div>
                    <div class="col-sm-4 form-group">
                        <input type="text" class="form-control" required name="name" value="<?php echo $row->name; ?>">
                        <input type="hidden" class="form-control" required name="name_hide" value="<?php echo $row->name; ?>">
                    </div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="col-sm-12 height-45-frm">
                    <div class="col-sm-2 text-label padding-right-15 font-label-form text-right">Tên đăng nhập:</div>
                    <div class="col-sm-4 form-group">
                        <input type="text" class="form-control" required name="username" value="<?php echo $row->username; ?>">
                        <input type="hidden" class="form-control" required name="username_hide" value="<?php echo $row->username; ?>">
                    </div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="col-sm-12 height-45-frm">
                    <div class="col-sm-2 text-label padding-right-15 font-label-form text-right">Mật khẩu:</div>
                    <div class="col-sm-4 form-group">
                        <input class="form-control"  name="password" type="password">
                        <input class="form-control"  name="password_hide" type="hidden" >
                        <input class="form-control"  name="currentPage" value="{{ $page }}" type="hidden" >
                    </div>
                    <div class="col-sm-6"></div>
                </div>
               <div class="col-sm-12 height-45-frm">
                    <div class="col-sm-2 text-label padding-right-15 font-label-form text-right">Ngày nghỉ việc:</div>
                    <div class="col-sm-4 form-group">
                        <input class="form-control"  name="terminate_date" type="text" value="{{ \Utils\commonUtils::formatDate($row->terminate_date)}}">
                        <input class="form-control"  name="terminate_date_hide" type="hidden" value="{{ \Utils\commonUtils::formatDate($row->terminate_date)}}">
                    </div>
                    <div class="col-sm-6"></div>
                </div>

                <div class="col-sm-12 height-45-frm">
                    <div class="col-sm-2 text-label padding-right-15 font-label-form text-right">Chỉ xem:</div>
                    <div class="col-sm-1 " style="margin-left: -3% !important;">
                        <input type="checkbox" class="checkbox-readonly" name="is_view" <?php $is_view = $row->is_view; if($is_view == 1){ echo "checked";}?> id="is_view">
                        <input type="checkbox" hidden class="checkbox-readonly" name="is_view_hide" value="<?php echo $row->is_view; ?>" id="is_view">
                    </div>
                    <div class="col-sm-9"></div>
                </div>
                <div class="col-sm-12 height-45-frm">
                    <div class="col-sm-2 text-label padding-right-15 font-label-form text-right">LDAP:</div>
                    <div class="col-sm-1 " style="margin-left: -3% !important;">
                        <input type="checkbox" class="checkbox-readonly" name="is_ldap" <?php $is_ldap = $row->ldap; if($is_ldap == 1){ echo "checked";}?> id="is_ldap">
                        <input type="checkbox" hidden class="checkbox-readonly" name="ldap1" value="<?php echo $row->ldap; ?>" id="ldap1">
                    </div>
                    <div class="col-sm-9"></div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-4 form-group">
                        <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                        <a href="<?= URL::to('employeeCategories?page=') . $page;?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
                    </div>
                    <div class="col-sm-6"></div>
                </div>

            </div>
        </div>
        </div>
    </form>
    <script>
        $( document ).ready(function() {
            $("#txtEmployeeCode").focus();

        });

        function reloadPage(url, id, page){
            var strPos = url.indexOf('updateEmployee');
            var url = url.substr(0, strPos);

            window.location.href = url + 'updateEmployee/' + id + '/' + page + '/' + $('#cboCompany').val();
        }
    </script>
@stop