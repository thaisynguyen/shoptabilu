@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.UPDATE') . ' ' . Config::get('constant.GROUP'))
@section('section')
@include('alerts.errors')

<form action="{{action('CategoriesController@editGroup')}}" method="POST" class="form-horizontal margin-left-20" role="form">
    <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
    <input type="hidden" name="id" value="<?= $row->id;?>"/>
    <div id="wrapper" >
        <div class="row margin-form">
            <div class="col-sm-12">
                <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Tổ/Quận/Huyện:</div>
                <div class="col-xs-9 col-sm-4 form-group">
                    <select class="form-control combobox-99" id="cboArea"  required name="area_id">
                        <?php  foreach($area as $a){
                                if($a->id == $row->area_id){ ?>
                                    <option value="<?php echo $a->id?>" selected><?php echo $a->area_name ?></option>
                               <?php } else { ?>
                                    <option value="<?php echo $a->id?>"><?php echo $a->area_name ?></option>
                               <?php }
                         } ?>
                    </select>
                </div>
                <div class="col-xs-3 col-sm-6">
                </div>
            </div>

            <div class="col-sm-12 height-45-frm">
                <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Mã nhóm:</div>
                <div class="col-xs-9 col-sm-4 form-group">
                    <input type="text" placeholder=" vd: G1" class="form-control" id="txtGroupCode" name="group_code" required value="<?php echo $row->group_code; ?>">
                    <input type="hidden" class="form-control" name="group_code_hide" value="<?php echo $row->group_code; ?>" required>
                </div>
                <div class="col-xs-3 col-sm-6"></div>
            </div>

            <div class="col-sm-12 height-45-frm">
                <div class="col-xs-12 col-sm-2 text-label padding-right-15 font-label-form text-right">Tên nhóm:</div>
                <div class="col-xs-9 col-sm-4 form-group">
                    <input type="text" placeholder=" vd: Nhóm 1" class="form-control" name="group_name" required value="<?php echo $row->group_name; ?>">
                    <input type="hidden" class="form-control" name="group_name_hide" value="<?php echo $row->group_name; ?>" required>
                </div>
                <div class="col-xs-3 col-sm-6"></div>
            </div>

            <div class="col-sm-12 header">
                <div class="col-xs-12 col-sm-2"></div>
                <div class="col-xs-9 col-sm-4 form-group">
                    <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                    <a href="<?= URL::to('groupCategories');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
                </div>
                <div class="col-xs-3 col-sm-6"></div>
            </div>

        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function() {
        $("#txtGroupCode").focus();
    });
</script>
@stop