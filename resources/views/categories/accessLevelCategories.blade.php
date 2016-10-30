@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.CATEGORIES') . ' ' . Config::get('constant.ACCESS_LEVEL'))
@section('section')
@include('alerts.success')
<?php
    use Utils\commonUtils;
    $curpage =  $data->currentPage();
    $accessLevel = Session::get('saccess_level');
    $isView = Session::get('sis_view');
?>

    <div id="wrapper" >
        <div class="row margin-form">
            <!--<div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php if( $accessLevel < 2 && $isView == 0) {?>
                        <a role="button" id="addGoal"  class="btn btn-primary change-color" href="<?= URL::to('addAccessLevel');?>"><i class="fa fa-plus"></i> Thêm Mới</a>
                    <?php } ?>
                </span>
            </div>-->
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                        echo $data->setPath(action('CategoriesController@accessLevelCategories'))->render();
                    ?>
                </span>
            </div>

            <table class="table-common">
                <thead>
                <tr >
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-2" sort_key="access_level_code">Mã Mức Truy Cập<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-5" sort_key="access_level_name">Tên Mức Truy Cập<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-2" sort_key="level">Mức Truy Cập<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-1"></th>
                    <th class="col-sm-1"></th>
                    <?php } else {?>
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-2" sort_key="access_level_code">Mã Mức Truy Cập<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-7" sort_key="access_level_name">Tên Mức Truy Cập<i class="fa pull-right unsort fa-sort"></i></th>
                    <th class="col-sm-2" sort_key="level">Mức Truy Cập<i class="fa pull-right unsort fa-sort"></i></th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                    $i=0;
                    $stt = (($curpage-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                    foreach($data as $row){
                        $i++;
                ?>
                <?php if($i % 2 == 0):?>
                <tr class="background-color-smoke">
                <?php else:?>
                <tr>
                    <?php endif; ?>
                    <td class="text-center "><?php  echo $stt; $stt++; ?></td>
                    <td class="text-center"><?php echo $row->access_level_code ; ?></td>
                    <td><?php echo $row->access_level_name ; ?></td>
                    <td class="text-center"><?php echo $row->level ; ?></td>
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <td class="table-icon text-center">
                        <a href="<?php echo 'updateAccessLevel/'.$row->id ?>" role="button" title="Cập nhật">
                            <i class="fa fa-pencil-square-o"></i>
                        </a>
                    </td>
                    <td class="table-icon text-center" >
                        <a role="button" href="#" data-target=".dialog-delete-goal-<?php echo $row->id; ?>" data-toggle="modal">
                            <i class="fa fa-trash" title="Xóa" ></i>
                        </a>
                        @include('popup.confirmDelete', array('rowId' => $row->id
                                                        , 'data' => $row->id
                                                      , 'strName' => $row->access_level_name
                                                      , 'actionName'  => 'CategoriesController@deleteAccessLevel'
                                                      ))
                    </td>
                    <?php }?>
                </tr>
                <?php } ?>
                </tbody>
            </table>
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                        echo $data->setPath(action('CategoriesController@accessLevelCategories'))->render();
                    ?>
                </span>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            sortOnPageLoad();
        });
    </script>
@stop