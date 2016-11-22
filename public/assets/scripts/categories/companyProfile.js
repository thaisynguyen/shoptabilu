$( document ).ready(function() {
    $(document).on('click', '#btnSave', function() {
            var subject = $('#subject').val()
                , address = $('#address').val()
                , title = $('#title').val()
                , tax_code = $('#tax_code').val()
                , phone_number = $('#phone_number').val()
                , fax = $('#fax').val()
                , website = $('#website').val()
                , image_name = $('#image_name').val()
                , company_id = $('#company_id').val()
                , email = $('#email').val()
                , logo = $('#file').val()
                , dataPost = {subject: subject
                    , address: address
                    , title: title
                    , tax_code: tax_code
                    , phone_number: phone_number
                    , fax: fax
                    , website: website
                    , email: email
                    , logo: logo
                    , company_id: company_id
                }
                ;
            //console.log(logo);

            if(subject == ''){
                $('#subject').focus();
                slideMessageMultiConfig('Cảnh báo', 'Tên không được rỗng', 'warning', 40);
            } else if(address == ''){
                $('#address').focus();
                slideMessageMultiConfig('Cảnh báo', 'Địa chỉ không được rỗng', 'warning', 40);
            } else {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: path + '/saveCompanyProfile/',
                    type: 'POST',
                    dataType: 'json',
                    data: dataPost,
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