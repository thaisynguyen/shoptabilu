<div class="modal fade" id="modal-standard-delete-<?php echo $unit_id;?>" tabindex="-1" role="basic" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title text-normal text-bold"><?php echo 'Confirm Delete Unit';?></h4>
            </div>
            <div class="modal-body text-normal">
                <div>
                    <?php
                    echo 'Đồng ý xóa'.': <b>'.$unit_name.'</b>?';
                    ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" id="btn-delete-unit-<?php echo $unit_id;?>" data-id="<?php echo $unit_id;?>" class="btn blue btn-act btn-delete-unit btn-smooth" ><?php echo 'Delete';?></button>
                <button type="button" class="btn default btn-act btn-smooth" data-dismiss="modal"><?php echo 'Close';?></button>
            </div>
        </div>

    </div>
    <!-- /.modal-dialog -->
</div>