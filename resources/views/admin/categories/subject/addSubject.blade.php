<div class="modal fade" id="modalAddSubject" tabindex="-1" role="basic" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        {{Form::open()}}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title text-normal text-bold"><?php echo 'Thêm Mới Khách Hàng/Nhà Cung Cấp';?></h4>
            </div>
            <div class="modal-body text-normal">
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Mã';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-subject" id="code" required>
                    </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Tên';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-subject" id="name" required>
                    </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Số điện thoại';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-subject" id="address" required>
                    </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Địa chỉ';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-subject" id="address" required>
                    </div>
                </div>
                <div class="has-success">
                    <label class="control-label"><?php echo 'Khách hàng/Nhà cung cấp';?> (<span class="input-require">*</span>)</label>
                    <div class="md-radio-inline">
                        <div class="md-radio">
                            <input type="radio" id="checkbox2_8" name="radio2" class="md-radiobtn"  checked="">
                            <label for="checkbox2_8" style="color: black">
                                <span></span>
                                <span class="check"></span>
                                <span class="box"></span> Khách hàng </label>
                        </div>
                        <div class="md-radio">
                            <input type="radio" id="checkbox2_9" name="radio2" class="md-radiobtn">
                            <label for="checkbox2_9" style="color: black">
                                <span></span>
                                <span class="check"></span>
                                <span class="box"></span> Nhà cung cấp </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn blue btn-act btn-smooth" id="btnSaveSubject"><?php echo 'Lưu';?></button>
                <button type="button" class="btn default btn-act btn-smooth" data-dismiss="modal"><?php echo 'Đóng';?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        {{Form::close()}}
    </div>
    <!-- /.modal-dialog -->
</div>