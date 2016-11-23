$( document ).ready(function() {
    $(document).on('click', '#btnSave', function() {


            if(subject == ''){
                $('#subject').focus();
                slideMessageMultiConfig('Cảnh báo', 'Tên không được rỗng', 'warning', 40);
            } else if(address == ''){
                $('#address').focus();
                slideMessageMultiConfig('Cảnh báo', 'Địa chỉ không được rỗng', 'warning', 40);
            } else {
                console.log(1);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: path + '/saveCompanyProfile/',
                    type: 'POST',
                    dataType: 'json',
                    data: new FormData($("#frmCompany")[0]),
                    async: false,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(response) {

                        dataObj = response;
                        console.log(dataObj);
                        if (dataObj.success == true) {
                            //$('#addUnit').modal('hide');
                            $('#main-content').html(dataObj.company);
                            slideMessageMultiConfig('Thông tin', dataObj.alert, 'success', 20);
                            $('#subject').focus();
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