/**
 * Created by uyenttt on 13/11/2016.
 */
// A $( document ).ready() block.
$( document ).ready(function() {
    resetForm();
    showAddProductType();
    saveProductType();
    showEditProductType();
    updateProductType();
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
            //console.log(dataProductType);
            $('#treeProductType').jstree({
                'core' : {
                    'data' : dataProductType
                }
            });
            //console.log(response.option);
            $('#opt').html(response.option);
        },
        error: function(xhr, textStatus, thrownError) {

            console.log(thrownError);
        }
    });

}

function resetForm(){
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
                url: path + '/saveProductType/',
                type: 'POST',
                dataType: 'json',
                data: dataPost,
                success: function(response) {

                    dataObj = response;
                    //console.log(dataObj);
                    if (dataObj.success == true) {
                        resetForm();
                        $('#name').focus();
                        $('#treeProductType').jstree("destroy");
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

function deleteProductType(){

    $(document).on('click', '#btnDeleteProductType', function() {

        var node = $('#treeProductType').jstree("get_selected",true);
        var id = node[0].id
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
                //console.log(dataObj);
                if (dataObj.success == true) {
                    $('#treeProductType').jstree("destroy");
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

function pressSaveProductType(){
    $(document).bind('keypress', '.add-data-product-type', function(e) {
        if(e.keyCode==13){
            saveUnit();
        }
    });

}
