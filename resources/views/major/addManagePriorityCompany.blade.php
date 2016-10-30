@extends('layouts.dashboard')
@section('page_heading','Thêm Mới Trọng Số Cho Phòng/Đài/MBF HCM')
@section('section')

    <div id="wrapper" >
        <div class="row margin-form">
            <div class="col-sm-12" style="margin-left: 20px;">
                <div class="col-sm-2 col-sm-offset-2 form-group"  >
                    <div class="radio"  id="radGoal1">
                        <input type="radio" class="radio-inline" name="optradio" checked id="goalLevel1"> <label class="padding-radio">Mục Tiêu Cấp 1</label>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="radio" id="radGoal2">
                        <input type="radio" class="radio-inline" name="optradio" id="goalLevel2"> <label class="padding-radio">Mục Tiêu Cấp 2</label>
                    </div>
                </div>
                <div class="col-sm-8"></div>
            </div>
            <hr class="divider">

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Phòng/Đài/MBF HCM:</div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="sel1" onchange="changeGoalLevelOne();">
                        <option value="all">Tất cả</option>
                        <?php foreach ($company as $row) { ?>
                            <option value="<?php echo $row->id ;?>"><?php echo $row->company_name;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-sm-6"></div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Mục tiêu cấp 1:</div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="selLevelOne">
                        <option value="all">Tất cả</option>
                        <?php foreach ($goal_level_one as $row) { ?>
                            <option value="<?php echo $row->id ;?>"><?php echo $row->goal_name;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-sm-6"></div>
            </div>

            <div class="col-sm-12 hidden" id="input-goal2">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Mục tiêu cấp 2:</div>
                <div class="col-sm-4 form-group">
                    <select class="form-control combobox-99" id="sel1">
                        <option value="all">Tất cả</option>
                        <?php
                            foreach ($goal as $row) {
                                if($row->parent_id == 0){
                                } else{
                        ?>
                            <option value="<?php echo $row->id ;?>"><?php echo $row->goal_name;?></option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="col-sm-6"></div>
            </div>

            <div class="col-sm-12" style=" margin-bottom: 13px;">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Trọng Số:</div>
                <div class="col-sm-4 ">
                    <input type="number" class="form-control" id="goal">
                </div>
                <div class="col-sm-6"></div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2 control-label padding-top-frm padding-right-15 font-label-form text-right">Ngày áp dụng:</div>
                <div class="col-sm-4 form-group">
                    <div class='input-group date hover-pointer fdatepicker'>
                        <input type='text' class="form-control" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                    </div>
                </div>
                <div class="col-sm-6"></div>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-2"></div>
                <div class="col-sm-4 form-group">
                    <a href="#"><button type="button" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button></a>
                    <a href="<?=URL::to('managePriorityCompany/0/0/0');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
                </div>
                <div class="col-sm-6"></div>
            </div>

            <script >
                $( document ).ready(function() {
                    $('#radGoal2').click(function(e) {
                        $("#input-goal2").addClass('display').removeClass('hidden');
                    })
                    $('#radGoal1').click(function(e) {
                        $("#input-goal2").addClass('hidden').removeClass('display');
                    })
                });
            </script>

        </div>
    </div>
@stop