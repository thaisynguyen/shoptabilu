@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.VIEW_LOG'))
@section('section')

<?php
    use Utils\commonUtils;
    $curpage =  $data->currentPage();
    $arrAction = commonUtils::arrAction();

?>
<div id="wrapper" >
   <div class="row margin-form">
       <form action="{{action('CategoriesController@viewLog')}}" method="GET">
       <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
       <div class="col-sm-12 ">
           <div class="col-xs-12 col-sm-2 form-group">
               <?php if(isset($userName)){?>
                   <input type="text" id="txtByEmpId" name="username" class="form-control" autofocus value="<?php echo $userName ?>">
               <?php } else {?>
                   <input type="text" id="txtByEmpId" name="username" class="form-control" autofocus placeholder="Tên người sửa">
               <?php } ?>
               <input type="hidden" name="ok" id="ok" value="ok">
           </div>
           <div class="col-xs-12 col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right" id="actionLabel">Thao Tác:</div>
           <div class="col-xs-12 col-sm-2">
               <select class="form-control" name="action" id="slAction">

                   <?php foreach($arrAction as $ea){?>
                       <option value="{{ $ea['id'] }}" <?php if(isset($action) && $action == $ea['id']){ echo "selected";} ?>>{{ $ea['name'] }}</option>
                   <?php }?>

               </select>
           </div>
           <div class="col-xs-12 col-sm-1 control-label padding-top-frm padding-right-15 font-label-form text-right" id="fromDateLabel">Từ ngày:</div>
           <div class="col-sm-2 col-xs-12">
               <div class="form-group" >
                   <div class='input-group date fdatepicker' id='appliedDate' >
                       <?php if(isset($fromDate)){?>
                           <input type='text' name="fromDate" id="txtDateFrom" class="form-control" value="<?php echo $fromDate ?>"/>
                       <?php } else {?>
                           <input type='text' name="fromDate" id="txtDateFrom" class="form-control"  />
                       <?php } ?>
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                   </div>
               </div>
           </div>
           <div class="col-sm-1 col-xs-12 control-label padding-top-frm padding-right-15 font-label-form text-right" id="toDateLabel">Đến ngày:</div>
           <div class="col-sm-2 col-xs-12">
               <div class="form-group" >
                   <div class='input-group date fdatepicker' id='appliedDate' >
                       <?php if(isset($toDate)){?>
                           <input type='text' name="toDate" id="txtDateTo" class="form-control" value="<?php echo $toDate ?>"/>
                       <?php } else {?>
                           <input type='text' name="toDate" id="txtDateTo" class="form-control" />
                       <?php } ?>
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                   </div>
               </div>
           </div>
           <div class="col-sm-1 col-xs-12">
               <button type="submit"  class="btn btn-primary btn-save"> &nbsp;<i class="glyphicon glyphicon-search"></i>Tìm</button>
           </div>
       </div>
       </form>
       <div class="col-sm-12 col-xs-12">
                <span class="pull-right pading-right-22">
                    <?php
                    echo $data->setPath(action('CategoriesController@viewLog'))->render();
                    ?>
                </span>
       </div>
      <div class="col-md-12 col-sm-12 col-xs-12">
        <table class="table-common" id="tblLog">
            <thead>
                <tr>
                    <th>STT</th>
                    <th class="col-md-2 col-xs-2">Tên Người Sửa</th>
                    <th class="col-md-1 col-xs-1">Tên Chức Năng</th>
                    <th class="col-md-1 col-xs-1">Thao Tác</th>
                    <th class="col-md-2 col-xs-3">Giá Trị Cũ</th>
                    <th class="col-md-3 col-xs-3">Giá Trị Mới</th>
                    <th class="col-md-3 col-xs-3">Ngày Sửa</th>
                </tr>
            </thead>
            <tbody>

                <?php
                $stt = (($curpage-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                foreach($data as $row){?>
                <tr>
                    <td class="order-column"><?php  echo $stt; $stt++; ?></td>
                    <td><?php echo $row->name ?></td>
                    <td><?php echo $row->function_name ?></td>
                    <td class="order-column">{{ \Utils\commonUtils::renderActionName($row->action); }}</td>
                    <td><?php echo $row->old_value ?></td>
                    <td><p class="break"><?php echo $row->new_value ?></p></td>
                    <td class="order-column">{{ $row->created_date; }}</td>
                </tr>
                <?php
                }
                ?>

            </tbody>
        </table>
          <input type="hidden" class="form-control" id="countLog" value="<?php echo count($data);?>">
          <div id="result" style="color: red; margin-left: -15px;"><H1></H1></div>
          <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                    echo $data->setPath(action('CategoriesController@viewLog'))->render();
                    ?>
                </span>
          </div>
      </div>
   </div>
</div>
@stop
