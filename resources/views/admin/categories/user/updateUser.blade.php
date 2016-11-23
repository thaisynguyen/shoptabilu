<?php
?>

<div class="modal fade" id="edit-user-<?php echo $user_id;?>" tabindex="-1" role="basic" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title text-normal text-bold"><?php echo 'Sửa Người Dùng';?></h4>


            </div>
            <div class="modal-body text-normal">
                <div class="form-group has-success">
                    <label class="control-label text-left"><?php echo 'Email';?> (<span class="input-require">*</span>):</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control" id="email-<?php echo $user_id;?>" name="email-<?php echo $user_id;?>" required value="{{$email}}"> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label text-left"><?php echo 'Mã';?> (<span class="input-require">*</span>):</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control" id="code-<?php echo $user_id;?>" name="code-<?php echo $user_id;?>" required value="{{$user_code}}"> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label text-left"><?php echo 'Tên';?> (<span class="input-require">*</span>):</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control" id="name-<?php echo $user_id;?>" name="name-<?php echo $user_id;?>" required value="{{$user_name}}"> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Admin';?> (<span class="input-require">*</span>)</label>
                    <input type="checkbox" value="{{$is_admin}}" <?php echo ($is_admin == 1) ? 'checked' : '';?> class="form-control add-data-user" id="admin-<?php echo $user_id;?>" name="admin-<?php echo $user_id;?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" data-id="{{$user_id}}" class="btn blue btn-act btn-edit-user btn-smooth"><?php echo 'Lưu';?></button>
                <button type="button" class="btn default btn-act btn-smooth" data-dismiss="modal"><?php echo 'Đóng';?></button>
            </div>
        </div>
        <input type="hidden" id="hidden-code-<?php echo $user_code;?>" required value="{{$user_code}}"> </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>