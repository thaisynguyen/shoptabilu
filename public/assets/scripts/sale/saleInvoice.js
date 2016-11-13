/**
 * Created by uyenttt on 13/11/2016.
 */
// A $( document ).ready() block.
$( document ).ready(function() {
    resetForm();
    showAddSaleInvoice();
    saveSaleInvoice();
    showEditSaleInvoice();
    updateSaleInvoice();
    deleteSaleInvoice();
    pressSaveSaleInvoice();
    loadData();
});


function loadData(){

}

function resetForm(){
    $('#name').val('');
}

function focusInput(idPopup, idInput){
    $('#'+idPopup).on('shown.bs.modal', function () {
        $('#'+idInput).focus();
    })
}

function showAddSaleInvoice(){
    $(document).on('click', '#btnAddSaleInvoice', function() {
        $('#modalAddSaleInvoice').modal('show');
        focusInput('modalAddSaleInvoice', 'name');

    });
}

function saveSaleInvoice(){
    $(document).on('click', '#btnSaveSaleInvoice', function() {
        var parentId = $('#parent_id').val()
            , name = $('#name').val()
            , dataPost = {parent_id: parentId, product_type_name: name}
            ;
        console.log(parentId);
        if(name == ''){
            $('#name').focus();
            slideMessageMultiConfig('Cảnh báo', 'Tên không được rỗng', 'warning', 40);
        } else {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: path + '/saveSaleInvoice/',
                type: 'POST',
                dataType: 'json',
                data: dataPost,
                success: function(response) {

                    dataObj = response;
                    //console.log(dataObj);
                    if (dataObj.success == true) {
                        resetForm();
                        $('#name').focus();
                        $('#treeSaleInvoice').jstree("destroy");
                        loadData();
                        slideMessageMultiConfig('Thông tin', dataObj.alert, 'success', 20);
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

function showEditSaleInvoice(){

    $(document).on('click', '.td-edit-product-type', function() {
        var id = $(this).attr('data-id');
        console.log(id);
        $('#edit-product-type-' + id).modal('show');
        focusInput('edit-product-type-' + id, 'code-' + id);
    });

}

function updateSaleInvoice(){

    $(document).on('click', '.btn-edit-product-type', function() {
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
            url: path + '/updateSaleInvoice/',
            type: 'POST',
            dataType: 'json',
            data: dataPost,
            success: function(response) {
                dataObj  = response;

                if (dataObj.success == true) {
                    slideMessageMultiConfig('Thông tin', dataObj.alert, 'success', 20);


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

function deleteSaleInvoice(){

    $(document).on('click', '#btnDeleteSaleInvoice', function() {

        var node = $('#treeSaleInvoice').jstree("get_selected",true);
        var id = node[0].id
            , dataPost = {id: id}
            ;

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: path + '/deleteSaleInvoice/',
            type: 'POST',
            dataType: 'json',
            data: dataPost,
            success: function(response) {
                dataObj = response;
                //console.log(dataObj);
                if (dataObj.success == true) {
                    $('#treeSaleInvoice').jstree("destroy");
                    loadData();
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

function pressSaveSaleInvoice(){
    $(document).bind('keypress', '.add-data-product-type', function(e) {
        if(e.keyCode==13){
            saveUnit();
        }
    });

}
