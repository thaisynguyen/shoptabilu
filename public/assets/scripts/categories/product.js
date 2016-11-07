$(document).ready(function () {

	var table = $('#tblProduct').DataTable({
		/*"processing": true,
		"serverSide": true,
        "ajax": {
			"headers": {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
            "url": path + "/viewProduct/",
            "type": "GET",
        },*/
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
			{ extend: 'print', className: 'btn dark btn-outline' }
            //{ extend: 'pdf', className: 'btn green btn-outline' },
            //{ extend: 'csv', className: 'btn purple btn-outline ' }			
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
        "pageLength": 5,
        "pagingType": 'bootstrap_extended', // pagination type

        "dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable

        // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
        // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js).
        // So when dropdowns used the scrollable div should be removed.
        //"dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
    });
    
});


$("[data-toggle=confirmation]").confirmation({
	container:"body",	
	btnOkClass:"btn btn-sm btn-success",
	btnCancelClass:"btn btn-sm btn-danger",
	onConfirm:function(event, element) {		
		var id = $(element)[0].getAttribute('data-id');
		//console.log(id);
		deleteProduct(id);
	}
});

function deleteProduct(idProduct)
{	
	var dataPost = {id: idProduct};
	$.ajax({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		url: path + '/deleteProduct/',
		type: 'POST',
		dataType: 'json',
		data: dataPost,

		success: function (response) {
			dataObj = response;

			if (dataObj.success == true) {				
				slideMessageMultiConfig('Thông tin', dataObj.alert, 'success', 20);
				$('#tblProduct').DataTable().row($('#pid_' + idProduct)).remove().draw( false );				
			}
			else {				
				slideMessageMultiConfig('C?nh báo', 'Xoa không thành công', 'warning', 40);
			}
			return dataObj;
		},
		error: function (xhr, textStatus, thrownError) {
			console.log('333');
			console.log(thrownError);
		}
	})	
}


