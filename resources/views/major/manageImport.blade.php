@extends('layouts.dashboard')
@section('page_heading','Import File Excel')
@section('section')
@include('alerts.errors')
@include('alerts.success')
@include('alerts.errorsImport')
<?php
    use Utils\commonUtils;
    $listType = CommonUtils::arrMethodImport();
?>
    <form action="{{action('ImportExcelController@postImport')}}" method="POST" class="form-horizontal margin-left-20" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= csrf_token();?>"/>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Loại import: </label>
            <div class="col-sm-3">
                <select class="form-control" id="selTypeImport" name="selTypeImport">
                    <?php foreach ($listType as $key => $value) { ?>
                    <option value="<?php echo $value['id'];?>"><?php echo $value['name'];?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-7">

            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Chọn file: </label>
            <div class="col-sm-3">
                <input id="uploadFile" name="uploadFile" placeholder="Chọn file excel" type="file" class="form-control col-xs-10 col-sm-5" accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>
            </div>
            <div class="col-sm-7">

            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15 font-label-form" for="form-field-1"> Dòng bắt đầu: </label>
            <div class="col-sm-3">
                <input id="startRow" name="startRow" type="number" class="form-control col-xs-10 col-sm-5" value="3"/>
            </div>
            <div class="col-sm-7">

            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label padding-right-15" for="form-field-1"> &nbsp; </label>
            <div class="col-sm-10">
                <button type="submit" class="btn btn-primary btn-save" id="btnImport"> &nbsp;<i class="fa fa-floppy-o"></i> &nbsp;Tải lên&nbsp;&nbsp;</button>
                <a href="<?= URL::to('testUpload');?>"><button type="button" class="btn btn-primary btn-cancel"> <i class="fa fa-reply"></i> Bỏ qua</button></a>
            </div>
        </div>
    </form>
    <script type="text/javascript">
        var value = 0;
       // console.log(value);

        $(document).ready(function() {
            var startRow = $('#startRow').val()
            console.log(startRow);
            $('#uploadFile').change( function()
            {
                $('#btnImport').prop("disabled", false);
            });
            if(value == 0){
                $('#btnImport').prop("disabled", true);
            }
        });
    </script>
@stop