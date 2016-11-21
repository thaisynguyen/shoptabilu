/**
 * Created by uyenttt on 13/11/2016.
 */
// A $( document ).ready() block.
$( document ).ready(function() {
    resetForm();
    showAddUser();
    saveUser();
    showEditUser();
    updateUser();
    showDeleteUser();
    deleteUser();
    pressSaveUser();
    reloadDataByUser();
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

function showAddUser(){
    $(document).on('click', '#btnAddUser', function() {
        $('#modalAddUser').modal('show');
        focusInput('modalAddUser', 'code');
    });
}

function saveUser(){
    $(document).on('click', '#btnSaveUser', function() {
        var code = $('#code').val()
            , name = $('#name').val()
            , dataPost = {user_code: code, user_name: name}
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
                url: path + '/saveUser/',
                type: 'POST',
                dataType: 'json',
                data: dataPost,
                success: function(response) {

                    dataObj = response;
                    console.log(dataObj);
                    if (dataObj.success == true) {
                        //$('#addUser').modal('hide');
                        $('#main-content').html(dataObj.user);
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

function showEditUser(){

    $(document).on('click', '.td-edit-user', function() {
        var id = $(this).attr('data-id');
        console.log(id);
        $('#edit-user-' + id).modal('show');
        focusInput('edit-user-' + id, 'code-' + id);
    });

}

function updateUser(){

    $(document).on('click', '.btn-edit-user', function() {
        var id = $(this).attr('data-id')
            , code = $('#code-'+id).val()
            , name = $('#name-'+id).val()
            , hiddencode = $('#hidden-code-'+id).val()
            , dataPost = {id: id, code: code, name: name, hiddencode: hiddencode}
            , dataObj;
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: path + '/updateUser/',
            type: 'POST',
            dataType: 'json',
            data: dataPost,
            success: function(response) {
                dataObj  = response;

                if (dataObj.success == true) {
                    slideMessageMultiConfig('Thông tin', dataObj.alert, 'success', 20);
                    //console.log(dataObj.user.user_code);
                    var user = dataObj.user;

                    $('#td-code-' + id).html(user.user_code);
                    $('#td-name-' + id).html(user.user_name);

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



function showDeleteUser(){

    $(document).on('click', '.td-delete-user', function() {
        var id = $(this).attr('data-id');
        $('#modal-standard-delete-'+id).modal('show');
    });

}

function deleteUser(){

    $(document).on('click', '.btn-delete-user', function() {
        var id = $(this).attr('data-id')
            , dataPost = {id: id}
            ;

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: path + '/deleteUser/',
            type: 'POST',
            dataType: 'json',
            data: dataPost,
            success: function(response) {

                dataObj = response;
                console.log(dataObj);
                $('.modal-backdrop').remove();
                if (dataObj.success == true) {
                    $('#main-content').html(dataObj.user);
                    slideMessageMultiConfig(lblSuccess, dataObj.alert, 'success', 40);
                } else {
                    slideMessageMultiConfig(lblWarning, dataObj.alert, 'warning', 40);
                }
            },
            error: function(xhr, textStatus, thrownError) {

                console.log(thrownError);
            }
        });


    });
}

function pressSaveUser(){
    $(document).bind('keypress', '.add-data-user', function(e) {
        if(e.keyCode==13){
            saveUser();
        }
    });

}

function reloadDataByUser(){
    $(document).on('change keyup', '.select-data', function() {
        var searchVal = $(this).val();
        var User = $('#searchValue').val()
            , type = $('select[name=fieldValue]').val()
            , url = KPIS.ApiUrl('kpi_standard/KsCategory/reloadDataByUser')
            , dataPost = {User: User, type: type}
            ;

        var dataJson = $.loadAjax(url, dataPost),
            dataObj  = JSON.parse(dataJson);
        if(dataObj.alert != ''){
            slideMessageMultiConfig(lblWarning, dataObj.alert, 'danger', 40);
        }

        $('#main-content').html(dataObj.contentUserHtml);
        refreshSelectPicker();
    });
}