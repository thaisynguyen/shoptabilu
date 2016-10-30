$(function() {
    $('delete-post').click(function(){
        var id_value = $('#elemtent').attr('data-id'); // get value post you want delete
        bootbox.dialog({
            title: 'Confirm',
            message: 'Are you sure delete this post',
            className: 'my-class',
            buttons: {
                cancel:{
                    className: 'btn btn-default',
                    label: 'Close'
                },
                success: {
                    className: 'btn btn-success',
                    label: 'Delete',
                    callback: function(){
                        $ajax({
                            data: {id: id_value},
                            dataType: 'json',
                            type: 'post',
                            urL: 'http://localhost/kpi/deleteCompany',
                            success: function(response){
                                if(response.status == 'success'){
                                    // Your action after delete
                                }
                                else {
                                    $(this).dialog('open');
                                }
                            }
                        });
                    }
                }
            }
        });
    });
});