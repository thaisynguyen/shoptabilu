$( document ).ready(function() {
    //updateData();
    //loadData();
});

$(function() {
    $('save').click(function(){
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
                        //$('#addUnit').modal('hide');
                        $('#main-content').html(dataObj.unit);
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


});

function loadData(){
    var dataPost = {id: 0}
        , dataObj;

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: path + '/loadCompany/',
        type: 'POST',
        dataType: 'json',
        data: dataPost,
        success: function(response) {

            dataObj = response.data;
            $('#main-content').html(response.option);
            slideMessageMultiConfig('Thông tin', dataObj.alert, 'success', 20);
        },
        error: function(xhr, textStatus, thrownError) {

            console.log(thrownError);
        }
    });

}