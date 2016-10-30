@extends('layouts.dashboard')
@section('page_heading', Config::get('constant.CATEGORIES') . ' ' . Config::get('constant.GOAL_1'))
@section('section')
@include('alerts.errors')
@include('alerts.success')
<?php
    use Utils\commonUtils;
    $curpage =  $data->currentPage();
    $accessLevel = Session::get('saccess_level');
    $isView = Session::get('sis_view');
?>

    <div id="wrapper" >
        <div class="row margin-form">
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <a role="button" id="addGoal"  class="btn btn-primary change-color" href="<?= URL::to('addGoalLevelOneCategories');?>"><i class="fa fa-plus"></i> Thêm Mới</a>
                    <?php } ?>
                </span>
            </div>
            <div class="col-sm-12">
                <span class="pull-right pading-right-22">
                    <?php
                        echo $data->setPath(action('CategoriesController@goalLevelOneCategories'))->render();
                    ?>
                </span>
            </div>

            <table class="table-common">
                <thead>
                <tr style="text-align: center">
                    <?php if($accessLevel < 2 && $isView == 0) {?>
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-1">Mã</th>
                    <th class="col-sm-8">Tên</th>
                    <th class="col-sm-1"></th>
                    <th class="col-sm-1"></th>
                    <?php } else { ?>
                    <th class="col-sm-1">STT</th>
                    <th class="col-sm-3">Mã</th>
                     <th class="col-sm-8">Tên</th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>

                    <?php
                        $i=0;
                        $stt = (($curpage-1) * CommonUtils::ITEM_PER_PAGE_DEFAULT) + 1;
                        foreach($data as $row){
                            $i++;
                            if($i%2==0):
                    ?>
                        <tr class="background-color-smoke">
                            <?php else: ?>
                        <tr>
                            <?php endif; ?>

                            <td class="order-column"><?php  echo $stt; $stt++; ?></td>
                            <td><?php echo $row->goal_code;?></td>
                            <td><?php echo $row->goal_name;?></td>
                            <?php if($accessLevel < 2 && $isView == 0) {?>
                            <td class="table-icon text-center">
                                <a href="<?php echo 'updateGoalLevelOne/'.$row->id;?>" role="button" title="Cập nhật">
                                    <i class="fa fa-pencil-square-o"></i>
                                </a>
                            </td>
                            <td class="table-icon text-center" >
                                <a role="button" href="#" data-target=".dialog-delete-goal-<?php echo $row->id; ?>" data-toggle="modal">
                                    <i class="fa fa-trash" title="Xóa" ></i>
                                </a>
                                @include('popup.confirmDelete', array('rowId' => $row->id
                                                      , 'data' => $row->id
                                                      , 'strName' => $row->goal_name
                                                      , 'actionName'  => 'CategoriesController@deleteGoalLevelOne'
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
                        echo $data->setPath(action('CategoriesController@goalLevelOneCategories'))->render();
                    ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Modal Dialog -->


    <script>
        $(document).ready(function() {
            $('#confirmDelete').on('show.bs.modal', function (e) {
                $message = $(e.relatedTarget).attr('data-message');
                $(this).find('.modal-body p').text($message);
                $title = $(e.relatedTarget).attr('data-title');
                $(this).find('.modal-title').text($title);

                // Pass form reference to modal for submission on yes/ok
                var form = $(e.relatedTarget).closest('form');
                $(this).find('.modal-footer #confirm').data('form', form);
            });

            <!-- Form confirm (yes/ok) handler, submits form -->
            $('#confirmDelete').find('.modal-footer #confirm').on('click', function(){
                $(this).data('form').submit();
            });
        });
    </script>

@stop