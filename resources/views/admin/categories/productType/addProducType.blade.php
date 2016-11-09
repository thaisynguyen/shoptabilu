<div class="modal fade" id="modalAddProductType" tabindex="-1" role="basic" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        {{Form::open()}}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title text-normal text-bold"><?php echo 'Thêm Loại Sản Phẩm';?></h4>
            </div>
            <div class="modal-body text-normal">
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Loại sản phẩm cha';?> (<span class="input-require">*</span>)</label>
                    <select id="parent-type" class="bs-select form-control">
                        <optgroup label="Picnic">
                            <option>Mustard</option>
                            <option>Ketchup</option>
                            <option>Relish</option>
                        </optgroup>
                        <optgroup label="Camping">
                            <option>Tent</option>
                            <option>Flashlight</option>
                            <option>Toilet Paper</option>
                        </optgroup>
                    </select>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Tên';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-product-type" id="name" required> </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn blue btn-act btn-smooth" id="btnSaveProductType"><?php echo 'Save';?></button>
                <button type="button" class="btn default btn-act btn-smooth" data-dismiss="modal"><?php echo 'Close';?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        {{Form::close()}}
    </div>
    <!-- /.modal-dialog -->
</div>