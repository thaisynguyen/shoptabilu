/**
 * Created by chaunp on 7/8/2016.
 */
// A $( document ).ready() block.
$( document ).ready(function() {
    resetForm();
    showAddProductType();
    saveProductType();
    showEditProductType();
    updateProductType();
    showDeleteProductType();
    deleteProductType();
    pressSaveProductType();
    loadData();
});


function loadData(){
    var dataPost = {id: 0}
        , dataProductType;

    dataProductType = $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: path + '/productTypeTree/',
        type: 'POST',
        dataType: 'json',
        data: dataPost,
        success: function(response) {

            dataProductType = response.data;
            console.log(dataProductType);
            $('#treeProductType').jstree({
                'core' : {
                    'data' : dataProductType
                }
            });
        },
        error: function(xhr, textStatus, thrownError) {

            console.log(thrownError);
        }
    });

}

function resetForm(){
    $('#code').val('');
    $('#name').val('');
}

function focusInput(idPopup, idInput){
    $('#'+idPopup).on('shown.bs.modal', function () {
        $('#'+idInput).focus();
    })
}

function showAddProductType(){
    $(document).on('click', '#btnAddProductType', function() {
        $('#modalAddProductType').modal('show');
        focusInput('modalAddProductType', 'name');
    });
}

function saveProductType(){
    $(document).on('click', '#btnSaveProductType', function() {
        var name = $('#name').val()
            , dataPost = {product_type_name: name}
            ;
        if(name == ''){
            $('#name').focus();
            slideMessageMultiConfig('Cảnh báo', 'Tên không được rỗng', 'warning', 40);
        } else {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: path + '/saveProductType/',
                type: 'POST',
                dataType: 'json',
                data: dataPost,
                success: function(response) {

                    dataObj = response;
                    console.log(dataObj);
                    if (dataObj.success == true) {
                        //$('#main-content').html(dataObj.productType);
                        //$('#treeProductType').jstree(true).settings.core.data = dataObj.productType;
                        //$('#addUnit').modal('hide');
                        //
                        $('#treeProductType').jstree("destroy");
                        loadData();
                        $('#treeProductType').jstree(true).refresh();
                        resetForm();
                        slideMessageMultiConfig('Thông tin', dataObj.alert, 'success', 20);
                        $('#name').focus();
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

function showEditProductType(){

    $(document).on('click', '.td-edit-product-type', function() {
        var id = $(this).attr('data-id');
        console.log(id);
        $('#edit-product-type-' + id).modal('show');
        focusInput('edit-product-type-' + id, 'code-' + id);
    });

}

function updateProductType(){

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
            url: path + '/updateProductType/',
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



function showDeleteProductType(){

    $(document).on('click', '.td-delete-product-type', function() {
        var id = $(this).attr('data-id');
        $('#modal-standard-delete-'+id).modal('show');
    });

}

function deleteProductType(){

    $(document).on('click', '.btn-delete-product-type', function() {
        var id = $(this).attr('data-id')
            , dataPost = {id: id}
            ;

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: path + '/deleteProductType/',
            type: 'POST',
            dataType: 'json',
            data: dataPost,
            success: function(response) {

                dataObj = response;
                console.log(dataObj);
                $('.modal-backdrop').remove();
                if (dataObj.success == true) {
                    $('#main-content').html(dataObj.unit);
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

function pressSaveProductType(){
    $(document).bind('keypress', '.add-data-product-type', function(e) {
        if(e.keyCode==13){
            saveUnit();
        }
    });

}
