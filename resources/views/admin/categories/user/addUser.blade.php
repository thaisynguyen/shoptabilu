<div class="modal fade" id="modalAddUser" tabindex="-1" role="basic" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        {{Form::open()}}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title text-normal text-bold"><?php echo 'Thêm Mới Người Dùng';?></h4>
            </div>
            <div class="modal-body text-normal">
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Email';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-user" id="email" name="email" required> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Mã';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-user" id="code" name="code" required> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Tên';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-user" id="name" name="name" required> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Admin';?> (<span class="input-require">*</span>)</label>
                    <input type="checkbox" class="form-control add-data-user" id="is_admin" name="is_admin" required>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn blue btn-act btn-smooth" id="btnSaveUser"><?php echo 'Lưu';?></button>
                <button type="button" class="btn default btn-act btn-smooth" data-dismiss="modal"><?php echo 'Đóng';?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        {{Form::close()}}
    </div>
    <!-- /.modal-dialog -->
</div>