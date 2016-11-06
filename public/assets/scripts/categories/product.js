$(document).ready(function () {
	
    var table = $('#tblProduct').DataTable({
        // Internationalisation. For more info refer to http://datatables.net/manual/i18n
        "language": {
            "aria": {
                "sortAscending": ": activate to sort column ascending",
                "sortDescending": ": activate to sort column descending"
            },
            "emptyTable": "No data available in table",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No entries found",
            "infoFiltered": "(filtered1 from _MAX_ total entries)",
            "lengthMenu": "_MENU_ entries",
            "search": "Search:",
            "zeroRecords": "No matching records found"
        },
		
		//'aoColumnDefs': [
			//{
				//'bSortable': false,
				//'aTargets': [9] /* 1st one, start by the right */
			//}
		//], 

        // Or you can use remote translation file
        //"language": {
        //   url: '//cdn.datatables.net/plug-ins/3cfcc339e89/i18n/Portuguese.json'
        //},

        // setup buttons extentension: http://datatables.net/extensions/buttons/
        buttons: [
            {
                text: 'Them SP Moi',
                action: function ( e, dt, node, config ) {
                    alert( 'Button activated' );
                },
				className: 'btn blue'
            },
			{ extend: 'print', className: 'btn dark btn-outline' },
            { extend: 'pdf', className: 'btn green btn-outline' },
            { extend: 'csv', className: 'btn purple btn-outline ' }			
        ],

        // setup responsive extension: http://datatables.net/extensions/responsive/
        responsive: {
            details: {
                type: 'column',
                target: 'tr'
            }
        },
		
        columnDefs: [ 
			{ 
				className: 'control',
				orderable: false,
				targets:   0
			},
			{				
				orderable: false,
				targets:   -1
			}
		],

        order: [ 1, 'asc' ],

        // pagination control
        "lengthMenu": [
            [5, 10, 15, 20, -1],
            [5, 10, 15, 20, "All"] // change per page values here
        ],
        // set the initial value
        "pageLength": 10,
        "pagingType": 'bootstrap_extended', // pagination type

        "dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable

        // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
        // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js).
        // So when dropdowns used the scrollable div should be removed.
        //"dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
    });
});

function deleteRow(idProduct)
{
    //var id = $(this).attr('data-id')
    //  , dataPost = {id: id}
    //;
    console.log(1111111);
    console.log(path);
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: path + '/deleteProduct/' + idProduct,
        type: 'POST',
        dataType: 'json',
        data: {id: idProduct},

        success: function(response) {
            console.log(22222);
            dataObj = response;
            console.log(dataObj);
            //$('.modal-backdrop').remove();
            if (dataObj.success == true) {
                $('#main-content').html(dataObj.unit);
                //slideMessageMultiConfig(lblSuccess, dataObj.alert, 'success', 40);
            } else {
                //slideMessageMultiConfig(lblWarning, dataObj.alert, 'warning', 40);
            }
        },
        error: function(xhr, textStatus, thrownError) {
            console.log(3333);
            console.log(thrownError);
        }
    });
    //table.row($('id')).remove().draw( false );
}
