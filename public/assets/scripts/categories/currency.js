/**
 * Created by uyenttt on 13/12/2016.
 */
// A $( document ).ready() block.
$( document ).ready(function() {
    resetForm();
    showAddCurrency();
    saveCurrency();
    showEditCurrency();
    updateCurrency();
    showDeleteCurrency();
    deleteCurrency();
    pressSaveCurrency();
    reloadDataByCurrency();
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

function showAddCurrency(){
    $(document).on('click', '#btnAddCurrency', function() {
        $('#modalAddCurrency').modal('show');
        focusInput('modalAddCurrency', 'code');
    });
}

function saveCurrency(){
    $(document).on('click', '#btnSaveCurrency', function() {
        var code = $('#code').val()
            , name = $('#name').val()
            , dataPost = {currency_code: code, currency_name: name}
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
                url: path + '/saveCurrency/',
                type: 'POST',
                dataType: 'json',
                data: dataPost,
                success: function(response) {

                    dataObj = response;
                    console.log(dataObj);
                    if (dataObj.success == true) {
                        //$('#addCurrency').modal('hide');
                        $('#main-content').html(dataObj.currency);
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

function showEditCurrency(){

    $(document).on('click', '.td-edit-currency', function() {
        var id = $(this).attr('data-id');
        $('#edit-currency-' + id).modal('show');
        focusInput('edit-currency-' + id, 'code-' + id);
    });

}

function updateCurrency(){

    $(document).on('click', '.btn-edit-currency', function() {
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
            url: path + '/updateCurrency/',
            type: 'POST',
            dataType: 'json',
            data: dataPost,
            success: function(response) {
                dataObj  = response;

                if (dataObj.success == true) {
                    slideMessageMultiConfig('Thông tin', dataObj.alert, 'success', 20);
                    //console.log(dataObj.currency.currency_code);
                    var currency = dataObj.currency;

                    $('#td-code-' + id).html(currency.currency_code);
                    $('#td-name-' + id).html(currency.currency_name);

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



function showDeleteCurrency(){

    $(document).on('click', '.td-delete-currency', function() {
        var id = $(this).attr('data-id');
        $('#modal-standard-delete-'+id).modal('show');
    });

}

function deleteCurrency(){

    $(document).on('click', '.btn-delete-currency', function() {
        var id = $(this).attr('data-id')
            , dataPost = {id: id}
            ;

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: path + '/deleteCurrency/',
            type: 'POST',
            dataType: 'json',
            data: dataPost,
            success: function(response) {

                dataObj = response;
                console.log(dataObj);
                $('.modal-backdrop').remove();
                if (dataObj.success == true) {
                    $('#main-content').html(dataObj.currency);
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

function pressSaveCurrency(){
    $(document).bind('keypress', '.add-data-currency', function(e) {
        if(e.keyCode==13){
            saveCurrency();
        }
    });

}

function reloadDataByCurrency(){
    $(document).on('change keyup', '.select-data', function() {
        var searchVal = $(this).val();
        var Currency = $('#searchValue').val()
            , type = $('select[name=fieldValue]').val()
            , url = KPIS.ApiUrl('kpi_standard/KsCategory/reloadDataByCurrency')
            , dataPost = {Currency: Currency, type: type}
            ;

        var dataJson = $.loadAjax(url, dataPost),
            dataObj  = JSON.parse(dataJson);
        if(dataObj.alert != ''){
            slideMessageMultiConfig(lblWarning, dataObj.alert, 'danger', 40);
        }

        $('#main-content').html(dataObj.contentCurrencyHtml);
        refreshSelectPicker();
    });
}