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
    addNewRow();
    getProductByBarcode();
    selectProductCode();
    selectProductName();
    amount();
});


function loadData(){

}

function resetForm(){
    $('#customer').val('');
    $('#discount-rate').val('');
    $('#discount-amount').val('');
    $('#total-invoice').val('');
    var fullDate = new Date();
    var twoDigitMonth = fullDate.getMonth() + 1 + "";
    if(twoDigitMonth.length == 1)
        twoDigitMonth = "0" + twoDigitMonth;
    var twoDigitDate = fullDate.getDate() + "";
    if(twoDigitDate.length == 1)
        twoDigitDate = "0" + twoDigitDate;
    var currentDate = twoDigitDate + "/" + twoDigitMonth + "/" + fullDate.getFullYear();
    $('#sales-invoice-date').val(currentDate);
    getLastSaleInvoiceId();
}

function focusInput(idPopup, idInput){
    $('#'+idPopup).on('shown.bs.modal', function () {
        $('#'+idInput).focus();
    })
}

function showAddSaleInvoice(){
    $(document).on('click', '#btnAddSaleInvoice', function() {
        $('#modalAddSaleInvoice').modal('show');
        focusInput('modalAddSaleInvoice', 'sales-serial-number');

    });
}

function addNewRow(){
    $(document).on('click', '#btnAddDetail', function() {
        var $tr = $('#added-product-table tr:last');

        var order = parseInt($tr.attr('order')) + 1;
        var $clone = $tr.clone();
        //change row id
        $clone.attr('id', 'row-item' + order);
        $clone.attr('order', order);
        //change combo product_code id
        $clone.find('select.product_code').attr('id', 'product_code' + order);
        $clone.find('select.product_name').attr('id', 'product_name' + order);

        $clone.find(':text').val('');
        $clone.find('#quantity').val(1);
        $tr.after($clone);

    });

}

function getLastSaleInvoiceId(){
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: path + '/getLastSaleInvoiceId/',
        type: 'POST',
        dataType: 'json',
        success: function(response) {

            dataObj = response;
            //console.log(dataObj);
            if (dataObj.success == true) {

                $('#sales-serial-number').val('BH/' + $('#sales-invoice-date').val() + '/' + parseInt(dataObj.sale_invoice_id) + 1);
            }
        },
        error: function(xhr, textStatus, thrownError) {
            console.log(thrownError);
        }
    });
}

var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

function getProductByBarcode(){
    $("#barcode").bind("keyup change", function(e) {
        delay(function(){
            var barcode = $('#barcode').val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: path + '/getProductByBarcode/',
                type: 'POST',
                dataType: 'json',
                data: {barcode: barcode},
                success: function(response) {

                    dataObj = response;
                    console.log(dataObj);
                    if (dataObj.success == true) {

                        var $tr = $('#added-product-table tr:last');
                        var order = parseInt($tr.attr('order')) + 1;
                        var $clone = $tr.clone();
                        $clone.attr('id', 'row-item' + order);
                        $clone.attr('order', order);
                        $clone.find(':text').val('');

                        $clone.find('td:first').html(order);
                        $clone.find('#product_id').val(dataObj.product[0].product_id);
                        $clone.find('#product_code').val(dataObj.product[0].product_id);
                        $clone.find('#product_name').val(dataObj.product[0].product_id);
                        $clone.find('#price').val(dataObj.product[0].sale_price);
                        $clone.find('#quantity').val(1);

                        $tr.after($clone);


                        $('#barcode').val('');
                        $('#barcode').focus();
                    }
                },
                error: function(xhr, textStatus, thrownError) {
                    console.log(thrownError);
                }
            });
        }, 1000 );
    })


}

function selectProductCode(){

    $('#added-product-table').on('change', '.product_code', function(event) {
        // how to get value of that particular dropdown selected
        var select = $(event.target);
        var productId = select.val();

        var selectName = select.parent('td').next('td').find('select');
        selectName.val(productId);

        //// how to get id of parent tr
        //var tr = $select.closest('tr')[0];
        //var id = tr.id;
        //var className = tr.className;

    });
}

function selectProductName(){

    $('#added-product-table').on('change', '.product_name', function(event) {
        // how to get value of that particular dropdown selected
        var select = $(event.target);
        var productId = select.val();

        var selectCode = select.parent('td').prev('td').find('select');
        selectCode.val(productId);

        //// how to get id of parent tr
        //var tr = $select.closest('tr')[0];
        //var id = tr.id;
        //var className = tr.className;

    });
}

function amount(){

    $('#added-product-table').on('change', '#price', function(event) {
        // how to get value of that particular dropdown selected
        var txtPrice = $(event.target);
        var price = txtPrice.val();

        var txtQuantity = txtPrice.parent('td').next('td').find('input');
        var quantity = txtQuantity.val();
        var txtAmount = txtQuantity.parent('td').next('td').find('input');
        var amount = parseFloat(price) * parseFloat(quantity);
        txtAmount.val(amount);

        //// how to get id of parent tr
        //var tr = $select.closest('tr')[0];
        //var id = tr.id;
        //var className = tr.className;

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
