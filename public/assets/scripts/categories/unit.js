/**
 * Created by chaunp on 7/8/2016.
 */
// A $( document ).ready() block.
$( document ).ready(function() {
    resetForm();
    showAddPosition();
    savePosition();
    showEditPosition();
    updatePosition();
    showDeletePosition();
    deletePosition();
    pressSavePosition();
    reloadDataByPosition();
});

function resetForm(){
    $('#code').val('');
    $('#name').val('');
    $('#description').val('');
}

function focusInput(idPopup, idInput){
    $('#'+idPopup).on('shown.bs.modal', function () {
        $('#'+idInput).focus();
    })
}

function showAddPosition(){
    $(document).on('click', '#add-position', function() {
        $('#addPosition').modal('show');
        focusInput('addPosition', 'code');
    });
}

function savePosition(){
    $(document).on('click', '#savePosition', function() {
        var code = $('#code').val()
            , name = $('#name').val()
            , description = $('#description').val()
            , url = KPIS.ApiUrl('kpi_standard/KsCategory/savePosition')
            , dataPost = {code: code, name: name, description: description}
            ;
        if(code == ''){
            $('#code').focus();
            slideMessageMultiConfig(lblWarning, nullCode, 'warning', 40);
        } else if(name == ''){
            $('#name').focus();
            slideMessageMultiConfig(lblWarning, nullName, 'warning', 40);
        } else {
            var dataJson = $.loadAjax(url, dataPost),
                dataObj  = JSON.parse(dataJson);
            if (dataObj.success == true) {
                $('#main-content').html(dataObj.contentPositionHtml);
                //$('#addPosition').modal('hide');
                resetForm();
                slideMessageMultiConfig(lblSuccess, dataObj.alert, 'success', 20);
            }else{
                slideMessageMultiConfig(lblWarning, dataObj.alert, 'warning', 40);
            }
        }

    });


}

function showEditPosition(){

    $(document).on('click', '.td-edit-position', function() {
        var id = $(this).attr('data-id');

        $('#edit-position-'+id).modal('show');
        focusInput('edit-position-'+id, 'code-'+id);
    });

}

function updatePosition(){

    reWriteStatus();
    $(document).on('click', '.btn-edit-position', function() {
        var id = $(this).attr('data-id')
            , code = $('#code-'+id).val()
            , name = $('#name-'+id).val()
            , description = $('#description-'+id).val()
            , status = $('#status-hidden-'+id).val()
            , url = KPIS.ApiUrl('kpi_standard/KsCategory/editPosition')
            , dataPost = {id: id, code: code, name: name, description: description, status: status}
            ;
        var dataJson = $.loadAjax(url, dataPost),
            dataObj  = JSON.parse(dataJson);

        if (dataObj.success == true) {
            slideMessageMultiConfig(lblSuccess, dataObj.alert, 'success', 20);

            var position = dataObj.position['KsPosition']
                , statusStr = (position['status'] == 1) ? active : lock
                ;

            $('#td-code-'+position['id']).html(position['code']);
            $('#td-name-'+position['id']).html(position['name']);
            $('#td-description-'+position['id']).html(position['description']);
            $('#td-status-'+position['id']).html(statusStr);

            //$('#editDepartment-'+id).modal('hide');
        }
    });
}

function reWriteStatus(){

    $(document).on('click', '.status-position', function() {
        var value = $(this).val()
            , id = $(this).attr('data-id')
            ;
        $('#status-hidden-'+id).val(value);
    });
}

function showDeletePosition(){

    $(document).on('click', '.td-delete-position', function() {
        var id = $(this).attr('data-id');
        $('#modal-standard-delete-'+id).modal('show');
    });

}

function deletePosition(){

    $(document).on('click', '.btn-delete-object', function() {
        var id = $(this).attr('data-id')
            , url = KPIS.ApiUrl('kpi_standard/KsCategory/deletePosition')
            , dataPost = {id: id}
            ;

        var dataJson = $.loadAjax(url, dataPost),
            dataObj  = JSON.parse(dataJson);

        $('.modal-backdrop').remove();
        if (dataObj.success == true) {
            $('#main-content').html(dataObj.contentPositionHtml);
            slideMessageMultiConfig(lblSuccess, dataObj.alert, 'success', 40);
        } else {
            slideMessageMultiConfig(lblWarning, dataObj.alert, 'warning', 40);
        }
    });
}

function pressSavePosition(){
    $(document).bind('keypress', '.add-data-position', function(e) {
        if(e.keyCode==13){
            savePosition();
        }
    });

}

function reloadDataByPosition(){
    $(document).on('change keyup', '.select-data', function() {
        var searchVal = $(this).val();
        var position = $('#searchValue').val()
            , type = $('select[name=fieldValue]').val()
            , url = KPIS.ApiUrl('kpi_standard/KsCategory/reloadDataByPosition')
            , dataPost = {position: position, type: type}
            ;

        var dataJson = $.loadAjax(url, dataPost),
            dataObj  = JSON.parse(dataJson);
        if(dataObj.alert != ''){
            slideMessageMultiConfig(lblWarning, dataObj.alert, 'danger', 40);
        }

        $('#main-content').html(dataObj.contentPositionHtml);
        refreshSelectPicker();
    });
}