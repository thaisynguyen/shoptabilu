@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.ADD') . ' ' . Config::get('constant.GOAL_2'))
@section('section')
@include('alerts.errors')
@include('alerts.success')

    <?php
        use Utils\commonUtils;
       // print_r($goalType);die();
    ?>
    <form action="{{action('CategoriesController@saveGoalLevelTwo')}}" method="post">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <div id="wrapper" >
            <div class="row margin-form">
                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Mục tiêu:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <select class="form-control combobox-99" id="selGoalOne" name="parent_id" required>
                            <?php foreach ($data as $row) { ?>
                                <option value="<?php echo $row->id;?>"><?php echo $row->goal_name;?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-3 col-sm-6">
                        <a role="button" id="addGoal" class="btn btn-primary change-color float_left" href="#" data-target=".dialog-add-goal-level-one" data-toggle="modal";?>
                            <i class="fa fa-plus"></i>
                        </a>
                        <div aria-hidden="false" aria-labelledby="delete-confirmation" role="dialog" tabindex="-1" class="modal fade dialog-add-goal-level-one">
                            <form accept-charset="utf-8" method="post" action="#">
                                <div class="modal-dialog">
                                    <div class="modal-content text-left">
                                        <div class="modal-header">
                                            <button data-dismiss="modal" class="close" type="button btn-sm" id="btnCloseGoal">
                                                <span tabindex="8" aria-hidden="true">×</span>
                                                <span class="sr-only">Đóng</span>
                                            </button>
                                            <strong id="myModalLabel" class="modal-title">Thêm Mục Tiêu</strong>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row margin-popup-add">
                                                <div class="col-sm-12">
                                                    <div class="col-sm-3 text-label " >Mã mục tiêu:</div>
                                                    <div class="col-sm-6 ">
                                                        <input type="text" class="form-control txt-30" id="goalCode" >
                                                    </div>
                                                    <div class="col-sm-3"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 10px;">
                                                    <div class="col-sm-3 text-label ">Tên mục tiêu:</div>
                                                    <div class="col-sm-6  ">
                                                        <input type="text" class="form-control txt-30" id="goalName" >
                                                    </div>
                                                    <div class="col-sm-3"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 20px;">
                                                    <div class="col-md-3 text-label "></div>
                                                    <div class="col-lg-7 " style="margin-left: -15px; color: red;" id="text_goal">
                                                    </div>
                                                    <div class="col-md-2"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary btn-save" id="quickSaveGoal"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Đơn vị tính:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <select class="form-control combobox-99" id="slUnit" name="unit_id" required>
                            <?php foreach ($unit as $row) { ?>
                            <option value="<?php echo $row->id;?>"><?php echo $row->unit_name;?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-3 col-sm-6">
                        <a role="button" id="addGoal" class="btn btn-primary change-color float_left" href="#" data-target=".dialog-add-unit" data-toggle="modal";?>
                            <i class="fa fa-plus"></i>
                        </a>
                        <div aria-hidden="false" aria-labelledby="delete-confirmation" role="dialog" tabindex="-1" class="modal fade dialog-add-unit">
                            <form accept-charset="utf-8" method="post" action="#">
                                <div class="modal-dialog">
                                    <div class="modal-content text-left">
                                        <div class="modal-header">
                                            <button data-dismiss="modal" class="close" type="button btn-sm" id="btnCloseUnit">
                                                <span tabindex="8" aria-hidden="true">×</span>
                                                <span class="sr-only">Đóng</span>
                                            </button>
                                            <strong id="myModalLabel" class="modal-title">Thêm Đơn Vị Tính</strong>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row margin-popup-add">
                                                <div class="col-sm-12">
                                                    <div class="col-sm-3 text-label " >Mã:</div>
                                                    <div class="col-sm-6 ">
                                                        <input type="text" class="form-control txt-30" id="unitCode" >
                                                    </div>
                                                    <div class="col-sm-3"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 10px;">
                                                    <div class="col-sm-3 text-label ">Tên:</div>
                                                    <div class="col-sm-6  ">
                                                        <input type="text" class="form-control txt-30" id="unitName" >
                                                    </div>
                                                    <div class="col-sm-3"></div>
                                                </div>
                                                <div class="col-sm-12" style="margin-top: 20px;">
                                                    <div class="col-md-3 text-label "></div>
                                                    <div class="col-lg-7 " style="margin-left: -15px; color: red;" id="text_unit">
                                                    </div>
                                                    <div class="col-md-2"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary btn-save" id="quickSaveUnit"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Loại mục tiêu:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <select class="form-control combobox-99" id="sel1" name="goal_type" required>
                            <?php foreach ($goalType  as $row => $value) { ?>
                            <option value="<?php echo $value['id'];?>"><?php echo $value['name'];?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-3 col-sm-6">
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Công thức:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <select class="form-control combobox-99" id="selFomula" name="formula" required
                                onchange="getDescriptionByFormula($('#selFomula').val(), $('#lblDescription'))">
                            <?php foreach ($formula  as $row => $value) { ?>
                            <option value="<?php echo $value['id'];?>"><?php echo $value['name'];?></option>
                            <?php } ?>
                        </select>


                    </div>
                    <div class="col-xs-3 col-sm-6">
                        <label id="lblDescription" class="padding-top-frm font-label-form text-right"></label>
                    </div>
                </div>

                <div class="col-sm-12 height-45-frm">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Mã mục tiêu:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <input type="text" class="form-control" id="txtGoalCode" name="goal_code" autofocus required>
                    </div>
                    <div class="col-xs-3 col-sm-6"></div>
                </div>

                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2 text-label padding-right-15 font-label-form text-right">Tên mục tiêu:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <input type="text" class="form-control" name="goal_name" required>
                    </div>
                    <div class="col-xs-3 col-sm-6"></div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-2 col-xs-12"></div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                        <a href="<?= URL::to('goalLevelTwoCategories/0');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
                    </div>
                    <div class="col-xs-3 col-sm-6"></div>
                </div>
            </div>
        </div>
    </form>
<script>
    $('#quickSaveGoal').on('click', function(e){
        var code = $('#goalName').val();
        var name = $('#goalCode').val();

        $.get('quickSaveGoal',{code: code, name: name}, function(data){
            if (data != false) {
                $('#goalName').val('');
                $('#goalCode').val('');
                var option = $("<option></option>").attr("value",data).text(name);
                $('#selGoalOne').append(option);
                $("#goalCode").focus();
                $('#text_goal').text('Thêm mới mục tiêu thành công !');
            } else {
                $('#text_goal').text('Thêm mới mục tiêu không thành công !');
            }
        })
    });


    $('#quickSaveUnit').on('click', function(e){
        var name = $('#unitName').val();
        var code = $('#unitCode').val();
        $.get('quickSaveUnit',{name: name, code: code}, function(data){
            if (data != false) {
                $('#unitName').val('');
                $('#unitCode').val('');
                var option = $("<option></option>").attr("value", data).text(name);
                $('#slUnit').append(option);
                $("#unitCode").focus();
                $('#text_unit').text('Thêm đơn vị tính thành công !');
            } else {
                $('#text_unit').text('Thêm đơn vị tính không thành công !');
            }
        })
    });

    $('#btnCloseGoal').on('click', function(e){
        $('#text_goal').text('');
    });

    $('#btnCloseUnit').on('click', function(e){
        $('#text_unit').text('');
    });


    getDescriptionByFormula($('#selFomula').val(), $('#lblDescription'));
</script>
@stop