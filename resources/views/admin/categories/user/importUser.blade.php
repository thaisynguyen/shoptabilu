<div class="modal fade" id="modalImportUser" tabindex="-1" role="basic" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        {{Form::open()}}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title text-normal text-bold"><?php echo 'Import Người Dùng';?></h4>
            </div>
            <div class="modal-body text-normal">
                <div class="controls">
                    <label class="control-label">Select File</label>
                    <input id="excel-import-goal"
                           accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                           required name="file" type="file" multiple="true"  class="file-loading">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn blue btn-act btn-smooth" id="btnImportUser"><?php echo 'Import';?></button>
                <button type="button" class="btn default btn-act btn-smooth" data-dismiss="modal"><?php echo 'Đóng';?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        {{Form::close()}}
    </div>
    <!-- /.modal-dialog -->
</div>

