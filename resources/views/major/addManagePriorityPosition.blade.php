@extends('layouts.dashboard')
@section('page_heading','Thêm Mới Trọng Số Cho Chức Danh')
@section('section')

    <div id="wrapper" >
        <div class="row margin-form">

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Phòng ban:</div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="sel1">
                        <option value="all">Tất cả</option>
                        <?php foreach ($company as $row) { ?>
                        <option value="<?php echo $row->id ;?>"><?php echo $row->company_name;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-sm-6"></div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Mục Tiêu:</div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="my_select" >
                        <option value="all" class="option-bold">Tất cả</option>
                        <?php foreach ($goal_level_one as $row1){?>
                        <option value="<?php echo $row1->id; ?>"><?php echo $row1->goal_name ?></option>
                        <?php
                        foreach ($goal as $row2){
                        if($row2->parent_id == $row1->id){?>
                        <option value="<?php echo $row2->id; ?>"><?php echo $row2->goal_name ?></option>
                        <?php }
                        }
                        }?>
                    </select>
                </div>
                <div class="col-sm-6"></div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Chức danh:</div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="sel1">
                        <option value="all">Tất cả</option>
                        <?php foreach ($position as $row) { ?>
                        <option value="<?php echo $row->id ;?>"><?php echo $row->position_name;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-sm-6"></div>
            </div>

            <div class="col-sm-12 height-45-frm">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Trọng số quận:</div>
                <div class="col-sm-4 form-group">
                    <input type="number" class="form-control" id="tax-code">
                </div>
                <div class="col-sm-6"></div>
            </div>

            <div class="col-sm-12 height-45-frm">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">TS theo chức danh:</div>
                <div class="col-sm-4 form-group">
                    <input type="number" class="form-control" id="tax-code">
                </div>
                <div class="col-sm-6"></div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Ngày Áp Dụng:</div>
                <div class="col-sm-4 form-group">
                    <div class="form-group">
                        <div class='input-group date hover-pointer fdatepicker'>
                            <input type='text' class="form-control" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6"></div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 text-label padding-top-frm">&nbsp;</div>
                <div class="col-sm-4 form-group">
                    <a href="#"><button type="button" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button></a>
                    <a href="<?= URL::to('managePriorityPosition/0/0/0/0/0/0');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
                </div>
                <div class="col-sm-6"></div>
            </div>

        </div>
    </div>
@stop