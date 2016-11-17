/**
 * Created by uyenttt on 13/11/2016.
 */
// A $( document ).ready() block.
$( document ).ready(function() {
    resetForm();
    showAddSubject();
    saveSubject();
    showEditSubject();
    updateSubject();
    showDeleteSubject();
    deleteSubject();
    pressSaveSubject();
    reloadDataBySubject();
});

function resetForm(){
    $('#code').val('');
    $('#name').val('');
    $('#phone').val('');
    $('#address').val('');
}

function focusInput(idPopup, idInput){
    $('#'+idPopup).on('shown.bs.modal', function () {
        $('#'+idInput).focus();
    })
}

function showAddSubject(){
    $(document).on('click', '#btnAddSubject', function() {
        $('#modalAddSubject').modal('show');
        focusInput('modalAddSubject', 'code');
    });
}

function saveSubject(){
    $(document).on('click', '#btnSaveSubject', function() {
        var code = $('#code').val()
            , name = $('#name').val()
            , phone = $('#phone').val()
            , address = $('#address').val()
            , is_customer = ($("input[name='chkSubject']:checked").val() == 1) ? 1 : 0
            , is_supplier = ($("input[name='chkSubject']:checked").val() == 2) ? 1 : 0
            , dataPost = {subject_code: code
                , subject_name: name
                , subject_telephone: phone
                , subject_address: address
                , is_supplier: is_supplier
                , is_customer: is_customer
            }
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
                url: path + '/saveSubject/',
                type: 'POST',
                dataType: 'json',
                data: dataPost,
                success: function(response) {

                    dataObj = response;
                    console.log(dataObj);
                    if (dataObj.success == true) {
                        //$('#addSubject').modal('hide');
                        $('#main-content').html(dataObj.subject);
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

function showEditSubject(){

    $(document).on('click', '.td-edit-subject', function() {
        var id = $(this).attr('data-id')

            , supplier = $(this).attr('supplier-val')
            , customer = $(this).attr('customer-val')
            ;

        if(supplier == 1){
            $('#chkSupplier').attr("checked", true);
        }
        if(customer == 1) {
            $('#chkCustomer').attr("checked", true);
        }
        $('#edit-subject-' + id).modal('show');
        focusInput('edit-subject-' + id, 'code-' + id);
    });

}

function updateSubject(){

    $(document).on('click', '.btn-edit-subject', function() {
        var id = $(this).attr('data-id')
            , code = $('#code-'+id).val()
            , name = $('#name-'+id).val()
            , address = $('#address-'+id).val()
            , phone = $('#phone-'+id).val()
            , is_customer = ($("input[name='chkSubject']:checked").val() == 1) ? 1 : 0
            , is_supplier = ($("input[name='chkSubject']:checked").val() == 2) ? 1 : 0
            , hiddencode = $('#hidden-code-'+id).val()
            , dataPost = {id: id
                        , code: code
                        , name: name
                        , address: address
                        , phone: phone
                        , is_supplier: is_supplier
                        , is_customer: is_customer
                        , hiddencode: hiddencode
                        }
            , dataObj;

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: path + '/updateSubject/',
            type: 'POST',
            dataType: 'json',
            data: dataPost,
            success: function(response) {
                dataObj  = response;

                if (dataObj.success == true) {
                    slideMessageMultiConfig('Thông tin', dataObj.alert, 'success', 20);
                    //console.log(dataObj.subject.subject_code);
                    var subject = dataObj.subject;
                    $('#main-content').html(dataObj.subject);
                    //$('#td-code-' + id).html(subject.subject_code);
                    //$('#td-name-' + id).html(subject.subject_name);
                    //$('#td-customer-' + id).html(subject.is_customer);
                    //$('#td-supplier-' + id).html(subject.is_supplier);
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

function showDeleteSubject(){
    $(document).on('click', '.td-delete-subject', function() {
        var id = $(this).attr('data-id');
        $('#modal-standard-delete-'+id).modal('show');
    });
}

function deleteSubject(){
    $(document).on('click', '.btn-delete-subject', function() {
        var id = $(this).attr('data-id')
            , dataPost = {id: id}
            ;

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: path + '/deleteSubject/',
            type: 'POST',
            dataType: 'json',
            data: dataPost,
            success: function(response) {

                dataObj = response;
                console.log(dataObj);
                $('.modal-backdrop').remove();
                if (dataObj.success == true) {
                    $('#main-content').html(dataObj.subject);
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

function pressSaveSubject(){
    $(document).bind('keypress', '.add-data-subject', function(e) {
        if(e.keyCode==13){
            saveSubject();
        }
    });

}

function reloadDataBySubject(){
    $(document).on('change keyup', '.select-data', function() {
        var searchVal = $(this).val();
        var Subject = $('#searchValue').val()
            , type = $('select[name=fieldValue]').val()
            , url = KPIS.ApiUrl('kpi_standard/KsCategory/reloadDataBySubject')
            , dataPost = {Subject: Subject, type: type}
            ;

        var dataJson = $.loadAjax(url, dataPost),
            dataObj  = JSON.parse(dataJson);
        if(dataObj.alert != ''){
            slideMessageMultiConfig(lblWarning, dataObj.alert, 'danger', 40);
        }

        $('#main-content').html(dataObj.contentSubjectHtml);
        refreshSelectPicker();
    });
}