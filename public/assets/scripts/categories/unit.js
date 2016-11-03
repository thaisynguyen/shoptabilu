/**
 * Created by chaunp on 7/8/2016.
 */
// A $( document ).ready() block.
$( document ).ready(function() {
    resetForm();
    showAddUnit();
    saveUnit();
    showEditUnit();
    updateUnit();
    showDeleteUnit();
    deleteUnit();
    pressSaveUnit();
    reloadDataByUnit();
});

function resetForm(){
    $('#code').val('');
    $('#name').val('');
}

function focusInput(idPopup, idInput){
    $('#'+idPopup).on('shown.bs.modal', function () {
        $('#'+idInput).focus();
    })
}

function showAddUnit(){
    $(document).on('click', '#btnAddUnit', function() {
        $('#modalAddUnit').modal('show');
        focusInput('modalAddUnit', 'code');
    });
}

function saveUnit(){
    $(document).on('click', '#btnSaveUnit', function() {
        var code = $('#code').val()
            , name = $('#name').val()
            , dataPost = {unit_code: code, unit_name: name}
            ;
        if(code == ''){
            $('#code').focus();
            slideMessageMultiConfig('Cảnh báo', 'Mã không được rỗng', 'warning', 40);
        } else if(name == ''){
            $('#name').focus();
            slideMessageMultiConfig('Cảnh báo', 'Tên không được rỗng', 'warning', 40);
        } else {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: path + '/saveUnit/',
                type: 'POST',
                dataType: 'json',
                data: dataPost,
                success: function(response) {

                    dataObj = response;
                    console.log(dataObj);
                    if (dataObj.success == true) {
                        //$('#main-content').html(dataObj.contentUnitHtml);
                        //$('#addUnit').modal('hide');

                        resetForm();
                        slideMessageMultiConfig('Thông tin', dataObj.alert, 'success', 20);
                        $('#code').focus();
                    }else{
                        slideMessageMultiConfig('Thông tin', dataObj.alert, 'warning', 40);
                    }
                    return dataObj;
                },
                error: function(xhr, textStatus, thrownError) {

                    console.log(thrownError);
                }
            });

        }

    });


}

function showEditUnit(){

    $(document).on('click', '.td-edit-unit', function() {
        var id = $(this).attr('data-id');
        console.log(id);
        $('#edit-unit-' + id).modal('show');
        focusInput('edit-unit-' + id, 'code-' + id);
    });

}

function updateUnit(){

    $(document).on('click', '.btn-edit-unit', function() {
        var id = $(this).attr('data-id')
            , code = $('#code-'+id).val()
            , name = $('#name-'+id).val()
            , hiddencode = $('#hidden-code-'+id).val()
            , dataPost = {id: id, code: code, name: name, hiddencode: hiddencode}
            ;
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: path + '/updateUnit/',
            type: 'POST',
            dataType: 'json',
            data: dataPost,
            success: function(response) {
                dataObj  = response;

                if (dataObj.success == true) {
                    slideMessageMultiConfig('Thông tin', dataObj.alert, 'success', 20);
                    console.log(dataObj.unit.unit_code);
                    var unit = dataObj.unit;

                    $('#code-' + id).html(unit.unit_code);
                    $('#name-' + id).html(unit.unit_name);

                    //$('#editDepartment-'+id).modal('hide');
                }
                else{
                    slideMessageMultiConfig('Cảnh báo', 'Cập nhật không thành công', 'warning', 40);
                }
                return dataObj;
            },
            error: function(xhr, textStatus, thrownError) {

                console.log(thrownError);
            }
        });


    });
}



function showDeleteUnit(){

    $(document).on('click', '.td-delete-unit', function() {
        var id = $(this).attr('data-id');
        $('#modal-standard-delete-'+id).modal('show');
    });

}

function deleteUnit(){

    $(document).on('click', '.btn-delete-object', function() {
        var id = $(this).attr('data-id')
            , url = KPIS.ApiUrl('kpi_standard/KsCategory/deleteUnit')
            , dataPost = {id: id}
            ;

        var dataJson = $.loadAjax(url, dataPost),
            dataObj  = JSON.parse(dataJson);

        $('.modal-backdrop').remove();
        if (dataObj.success == true) {
            $('#main-content').html(dataObj.contentUnitHtml);
            slideMessageMultiConfig(lblSuccess, dataObj.alert, 'success', 40);
        } else {
            slideMessageMultiConfig(lblWarning, dataObj.alert, 'warning', 40);
        }
    });
}

function pressSaveUnit(){
    $(document).bind('keypress', '.add-data-Unit', function(e) {
        if(e.keyCode==13){
            saveUnit();
        }
    });

}

function reloadDataByUnit(){
    $(document).on('change keyup', '.select-data', function() {
        var searchVal = $(this).val();
        var Unit = $('#searchValue').val()
            , type = $('select[name=fieldValue]').val()
            , url = KPIS.ApiUrl('kpi_standard/KsCategory/reloadDataByUnit')
            , dataPost = {Unit: Unit, type: type}
            ;

        var dataJson = $.loadAjax(url, dataPost),
            dataObj  = JSON.parse(dataJson);
        if(dataObj.alert != ''){
            slideMessageMultiConfig(lblWarning, dataObj.alert, 'danger', 40);
        }

        $('#main-content').html(dataObj.contentUnitHtml);
        refreshSelectPicker();
    });
}