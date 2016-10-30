@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.UPDATE') . ' ' . Config::get('constant.GOAL_2'))
@section('section')
@include('alerts.errors')

    <?php
        use Utils\commonUtils;
    ?>
    <form action="{{action('CategoriesController@editGoalLevelTwo')}}" method="post">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <input type="hidden" name="id" value="<?= $goallevelTwo->id;?>"/>
        <div id="wrapper" >
            <div class="row margin-form">
                <div class="col-sm-12">
                    <div class="col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Mục tiêu cấp 1:</div>
                    <div class="col-sm-4 form-group">
                        <select class="form-control combobox-99" id="sel1" name="parent_id" required>
                                <option value="<?php echo $goallevelTwo->parent_id;?>"><?php echo $parent_name->goal_name;?></option>
                            <?php foreach ($goallevelOne as $row) { ?>
                                <option value="<?php echo $row->id;?>"><?php echo $row->goal_name;?></option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="parent_id_hide" value="<?php echo $goallevelTwo->parent_id;?>">
                    </div>
                    <div class="col-sm-6"></div>
                </div>

                <div class="col-sm-12">
                    <div class="col-xs-12 col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Đơn vị tính:</div>
                    <div class="col-xs-9 col-sm-4 form-group">
                        <select class="form-control combobox-99" id="selUnit" name="unit_id" required>
                            <?php foreach ($unit as $row) { ?>
                            <option value="<?php echo $row->id;?>"<?php if($unitId == $row->id){  echo 'selected'; }?>><?php echo $row->unit_name;?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <input type="hidden" class="form-control" id="txtUnitId" name="unit_hide" value="<?php echo $goallevelTwo->unit_id; ?>">
                </div>

                <div class="col-sm-12">
                    <div class="col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Loại mục tiêu:</div>
                    <div class="col-sm-4 form-group">
                        <select class="form-control combobox-99" id="sel1" name="goal_type" required>
                                <option value="<?php echo $goallevelTwo->goal_type;?>"><?php echo commonUtils::renderGoalTypeName($goallevelTwo->goal_type);?></option>
                            <?php foreach ($goalType as $row => $value) { ?>
                                <option value="<?php echo $value['id'];?>" ><?php echo $value['name'];?></option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="goal_type_hide" value="<?php echo $goallevelTwo->goal_type;?>">
                    </div>
                    <div class="col-sm-6">
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class="col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Công thức:</div>
                    <div class="col-sm-4 form-group">
                        <select class="form-control combobox-99" id="selFomula" name="formula" required onchange="getDescriptionByFormula($('#selFomula').val(), $('#lblDescription'))">
                            <option value="<?php echo $goallevelTwo->formula;?>"><?php echo commonUtils::renderFormulaOfGoalType($goallevelTwo->formula);?></option>
                            <?php foreach ($formula as $row => $value) {
                                    if($value['id'] != $goallevelTwo->formula){
                            ?>
                            <option value="<?php echo $value['id'];?>"><?php echo $value['name'];?></option>
                            <?php }} ?>
                        </select>
                        <input type="hidden" name="formula_hide" value="<?php echo $goallevelTwo->formula;?>">
                    </div>
                    <div class="col-sm-6">
                        <label id="lblDescription" class="padding-top-frm font-label-form text-right"></label>
                    </div>
                </div>


                <div class="col-sm-12 height-45-frm">
                    <div class="col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Mã mục tiêu:</div>
                    <div class="col-sm-4 form-group">
                        <input type="text" class="form-control" id="txtGoalCode" name="goal_code" value="<?php echo $goallevelTwo->goal_code; ?>" autofocus required>
                        <input type="hidden" class="form-control" id="txtGoalCode" name="goal_code_hide" value="<?php echo $goallevelTwo->goal_code; ?>" required>
                    </div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-2 text-label padding-top-frm padding-right-15 font-label-form text-right">Tên mục tiêu:</div>
                    <div class="col-sm-4 form-group">
                        <input type="text" class="form-control" id="txtGoalName" name="goal_name" value="<?php echo $goallevelTwo->goal_name; ?>">
                        <input type="hidden" class="form-control" id="txtGoalName" name="goal_name_hide" value="<?php echo $goallevelTwo->goal_name; ?>">
                    </div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-4 form-group">
                        <button type="submit" class="btn btn-primary btn-save"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Lưu&nbsp;&nbsp;</button>
                        <a href="<?= URL::to('goalLevelTwoCategories/0');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
                    </div>
                    <div class="col-sm-6"></div>
                </div>
            </div>
        </div>
    </form>
    <script>
        getDescriptionByFormula($('#selFomula').val(), $('#lblDescription'));
    </script>
@stop