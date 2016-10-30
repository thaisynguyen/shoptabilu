@extends('layouts.dashboard')
@section('page_heading','Thêm Mới Kế Hoạch Cho Tổ/Quận/Huyện')
@section('section')
    <div id="wrapper" >
        <div class="row margin-form">

            <div class="col-md-12">
                <div class="col-md-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Phòng/Đài/MBF HCM:</div>
                <div class="col-md-4 form-group">
                    <select class="form-control combobox-99" id="sel1">
                        <option value="all">Tất cả</option>
                        <?php foreach ($company as $row) { ?>
                        <option value="<?php echo $row->id ;?>"><?php echo $row->company_name;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6"></div>
            </div>

            <div class="col-md-12">
                <div class="col-md-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Tổ/Quận/Huyện:</div>
                <div class="col-md-4 form-group">
                    <select class="form-control combobox-99" id="sel1">
                        <option value="all">Tất cả</option>
                        <?php foreach ($area as $row) { ?>
                        <option value="<?php echo $row->id ;?>"><?php echo $row->area_name;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6"></div>
            </div>

            <div class="col-md-12">
                <div class="col-md-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Mục Tiêu:</div>
                <div class="col-md-4 form-group">
                    <select class="form-control combobox-99" id="sel1">
                        <option value="all">Tất cả</option>
                        <?php foreach ($goal_level_one as $row) { ?>
                        <option value="<?php echo $row->id ;?>"><?php echo $row->goal_name;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6"></div>
            </div>

            <div class="col-md-12">
                <div class="col-md-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Loại mục tiêu:</div>
                <div class="col-md-4 form-group">
                    <select class="form-control combobox-99" id="sel1">
                        <option value="1">Càng lớn càng tốt</option>
                        <option value="2">Càng nhỏ càng tốt</option>
                        <option value="3">Đạt</option>
                        <option value="4">Không đạt</option>
                    </select>
                </div>
                <div class="col-md-6"></div>
            </div>

            <div class="col-md-12  height-45-frm">
                <div class="col-md-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Kế hoạch:</div>
                <div class="col-md-4 form-group">
                    <input type="number" class="form-control" id="goal">
                </div>
                <div class="col-md-6"></div>
            </div>

            <div class="col-md-12  height-45-frm">
                <div class="col-md-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Trọng số:</div>
                <div class="col-md-4 form-group">
                    <input type="number" class="form-control" id="goal">
                </div>
                <div class="col-md-6"></div>
            </div>

            <div class="col-md-12">
                <div class="col-md-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Năm:</div>
                <div class="col-md-4 form-group">
                    <select class="form-control text-left combobox-99" id="sel1">
                        <?php $curYear = date('Y'); for($i = $curYear; $i< $curYear+10; $i++) { ?>
                        <option value="<?php echo $i;?>" class="text-left"><?php echo $i;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6"></div>
            </div>

            <div class="col-md-12">
                <div class="col-md-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Tháng:</div>
                <div class="col-md-4 form-group">
                    <select class="form-control text-left combobox-99" id="sel1">
                        <?php for($i=1;$i<=12;$i++) { ?>
                        <option value="<?php echo $i;?>" class="text-left"><?php echo $i;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6"></div>
            </div>

            <div class="col-md-12">
                <div class="col-md-2"></div>
                <div class="col-md-4 form-group">
                    <a href="#"><button type="button" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button></a>
                    <a href="<?= URL::to('manageGoalArea');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
                </div>
                <div class="col-md-6"></div>
            </div>

        </div>
    </div>
@stop