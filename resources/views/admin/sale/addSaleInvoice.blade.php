<div class="modal fade" id="modalAddSaleInvoice" tabindex="-1" role="basic" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        {{Form::open()}}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title text-normal text-bold"><?php echo 'Add Unit';?></h4>
            </div>
            <div class="modal-body text-normal">
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Barcode';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-unit" id="name" required> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'S? hóa ??n';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-unit" id="code" required> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'S?n ph?m';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-unit" id="name" required> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo '??n v? tính';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-unit" id="name" required> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo '??n giá';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-unit" id="name" required> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'S? l??ng';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-unit" id="name" required> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'Thành ti?n';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-unit" id="name" required> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo '% Gi?m giá';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-unit" id="name" required> </div>
                </div>
                <div class="form-group has-success">
                    <label class="control-label"><?php echo 'T?ng c?ng';?> (<span class="input-require">*</span>)</label>
                    <div class="input-icon right">
                        <input type="text" class="form-control add-data-unit" id="name" required> </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn blue btn-act btn-smooth" id="btnSaveSaleInvoice"><?php echo 'L?u';?></button>
                <button type="button" class="btn default btn-act btn-smooth" data-dismiss="modal"><?php echo '?óng';?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        {{Form::close()}}
    </div>
    <!-- /.modal-dialog -->
</div>