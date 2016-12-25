$(document).ready(function () {			
	//----------------------------------------------------------------------------------------------------------
	// DataTable Editable
	//----------------------------------------------------------------------------------------------------------
	function restoreRow(oTable, nRow) {
		var aData = oTable.fnGetData(nRow);
		var jqTds = $('>td', nRow);

		for (var i = 0, iLen = jqTds.length; i < iLen; i++) {
			oTable.fnUpdate(aData[i], nRow, i, false);
		}

		oTable.fnDraw();
	}	

	function editRow(oTable, nRow) {		
	
		var aData = oTable.fnGetData(nRow);
		var jqTds = $('>td', nRow);
		jqTds[0].innerHTML = '<input id="pd_id" type="text" class="form-control" value="' + aData[0] + '" readonly>';
		jqTds[1].innerHTML = '<input id="pd_barcodeid" type="text" class="form-control input-small" value="' + aData[1] + '">';
		jqTds[2].innerHTML = '<input id="pd_quantity" type="text" class="form-control" value="' + aData[2] + '">';			
		jqTds[3].innerHTML = renderCombobox(arrUnit, aData[12], 'table-group-action-input form-control', 'pd_unit_code', 'pd_unit_code', '');
		jqTds[4].innerHTML = renderCombobox(arrCurrency, aData[13], 'table-group-action-input form-control', 'pd_currency_code', 'pd_currency_code', '');
		jqTds[5].innerHTML = '<input id="pd_purchase_price" type="text" class="form-control" value="' + aData[5] + '">';
		jqTds[6].innerHTML = '<input id="pd_sale_price" type="text" class="form-control" value="' + aData[6] + '">';					
		jqTds[7].innerHTML = renderDatetime(aData[7], 'input-group input-small date date-picker', 'dd-mm-yyyy', '+0d', 'pd_apply_date');		
		jqTds[8].innerHTML = '<input id="pd_warranty_label" type="text" class="form-control input-small" value="' + aData[8] + '">';
		jqTds[9].innerHTML = '<input id="pd_warranty_period" type="text" class="form-control input-small" value="' + aData[9] + '">';
		jqTds[10].innerHTML = '<input id="pd_description" type="text" class="form-control input-small" value="' + aData[10] + '">';		
	}

	function saveRow(oTable, nRow) {
		var jqInputs = $('input', nRow);
		var unit_code = $('#pd_unit_code :selected').text();
		var currency_code = $('#pd_currency_code :selected').text();
		
		oTable.fnUpdate(jqInputs[0].value, nRow, 0, false);
		oTable.fnUpdate(jqInputs[1].value, nRow, 1, false);
		oTable.fnUpdate(jqInputs[2].value, nRow, 2, false);
		oTable.fnUpdate(unit_code, nRow, 3, false);
		oTable.fnUpdate(currency_code, nRow, 4, false);
		oTable.fnUpdate(jqInputs[3].value, nRow, 5, false);
		oTable.fnUpdate(jqInputs[4].value, nRow, 6, false);
		oTable.fnUpdate(jqInputs[5].value, nRow, 7, false);
		oTable.fnUpdate(jqInputs[6].value, nRow, 8, false);
		oTable.fnUpdate(jqInputs[7].value, nRow, 9, false);
		oTable.fnUpdate(jqInputs[8].value, nRow,10, false);
		oTable.fnDraw();
	}

	/*function cancelEditRow(oTable, nRow) {
		var jqInputs = $('input', nRow);
		oTable.fnUpdate(jqInputs[0].value, nRow, 0, false);
		oTable.fnUpdate(jqInputs[1].value, nRow, 1, false);
		oTable.fnUpdate(jqInputs[2].value, nRow, 2, false);
		oTable.fnUpdate(jqInputs[3].value, nRow, 3, false);
		oTable.fnUpdate(jqInputs[4].value, nRow, 4, false);
		oTable.fnUpdate(jqInputs[5].value, nRow, 5, false);
		oTable.fnUpdate(jqInputs[6].value, nRow, 6, false);
		oTable.fnUpdate(jqInputs[7].value, nRow, 7, false);
		oTable.fnUpdate(jqInputs[8].value, nRow, 8, false);
		oTable.fnUpdate(jqInputs[9].value, nRow, 9, false);
		oTable.fnUpdate(jqInputs[10].value, nRow,10, false);
		oTable.fnDraw();
	}*/

	var table = $('#tblProductDetail');	
	var oTable = table.dataTable({
		// Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
		// setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js). 
		// So when dropdowns used the scrollable div should be removed. 
		//"dom": "<'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",		
		
        "ajax": {
            headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
            url: path + "/listProductDetail/",
			type: "POST",
			dataType: 'json',
			data: {	
				product_id : $('#product_id').val()
			}
        },
		// "createdRow": function( row, data, dataIndex ) {
			// row.id = "pdid_" + data[0];
			// $('td', row).eq(0).html(dataIndex + 1);			
		// },
        "columns": [
			// {
				// "data": null,
                // "defaultContent": '',
				// "searchable": false,
                // "orderable": false
			// },
			{"data": "0"},  //"product_detail_id"
			{"data": "1"},  //"barcodeid"
			{"data": "2"},  //"quantity"
			{"data": "3"},  //"unit_code"
			{"data": "4"},  //"currency_code"
			{"data": "5"},  //"purchase_price"
			{"data": "6"},  //"sale_price"
			{"data": "7"},  //"apply_date"
			{"data": "8"},  //"warranty_label"
			{"data": "9"},  //"warranty_period"
			{"data": "10"}	//"description"
        ],		

		"lengthMenu": [
			[10, 20],
			[10, 20]
			//[10, 20, -1],
			//[10, 20, "All"] // change per page values here
		],

		// Or you can use remote translation file
		"language": {
			url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Vietnamese.json'
		},

		// set the initial value
		"pageLength": 10,
		
		"order": [
			[0, "asc"]
		] // set first column as a default sort by asc
	});
	
	$('#tblProductDetail tbody').on( 'click', 'tr', function () {
		if (nEditing == null && nNew == false)
		{
			if ( $(this).hasClass('selected') ) {
				$(this).removeClass('selected');
				showControlButton(STATE_NONE);
			}
			else {
				table.$('tr.selected').removeClass('selected');
				$(this).addClass('selected');
				showControlButton(STATE_SELECT);
			}
		}
    } );
	
	var nEditing = null;
	var nNew = false;
	$('#tblProductDetail_new').click(function (e) {
		e.preventDefault();	
		
		var aiNew = oTable.fnAddData(['', '', '', '', '', '', '', '', '', '', '']);
		var nRow = oTable.fnGetNodes(aiNew[0]);
		editRow(oTable, nRow);
		nEditing = nRow;
		nNew = true;
		
		table.$('tr.selected').removeClass('selected');
		showControlButton(STATE_NEW);
		
		//init datepicker after the element is created
		$(".date-picker").datepicker({
			format: "dd-mm-yyyy",
			autoclose: true
		});
	});
	
	$('#tblProductDetail_update').click(function (e) {
		e.preventDefault();
		
		/* Get the row as a parent of the link that was clicked on */
		//var nRow = $(this).parents('tr')[0];
		var nRow = table.$('tr.selected');
		editRow(oTable, nRow);
		nEditing = nRow;
		
		table.$('tr.selected').removeClass('selected');
		showControlButton(STATE_UPDATE);
		
		//init datepicker after the element is created
		$(".date-picker").datepicker({
			format: "dd-mm-yyyy",
			autoclose: true
		});
	});
	
	$('#tblProductDetail_delete').click(function (e) {
		e.preventDefault();
		
		if (confirm("Bạn có muốn xóa dòng này?") == false) {
			return;
		}				
				
		// ajax call delete ProductDetail on server
		var nRow = table.$('tr.selected');
		var dataPost = {id: $($('tr.selected td')[0]).text()};
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: path + '/deleteProductDetail/',
			type: 'POST',
			dataType: 'json',
			data: dataPost,

			success: function (response) {
				dataObj = response;

				if (dataObj.success == true) {
					// delete row at client side in datatable
					oTable.fnDeleteRow(nRow);
					showControlButton(STATE_NONE);
				} else {
					alert("Xóa không thành công!! Lỗi trong quá trình xóa tại server!");
					return;
				}
			},
			error: function (xhr, textStatus, thrownError) {
				alert("Xóa không thành công!!");
				//console.log(thrownError);
				return;
			}
		})
		
	});
	
	$('#tblProductDetail_cancel').click(function (e) {
		e.preventDefault();
		if (nNew) {
			oTable.fnDeleteRow(nEditing);
			nEditing = null;
			nNew = false;
		} else {
			restoreRow(oTable, nEditing);
			nEditing = null;
		}
		
		showControlButton(STATE_NONE);
	});
	
	$('#tblProductDetail_save').click(function (e) {
		e.preventDefault();
				
		if (nNew) {					
			// ajax call create new a ProductDetail
			var dataPost = {
				product_id: $('#product_id').val(),
				barcodeid: $('#pd_barcodeid').val(), 
				quantity: $('#pd_quantity').val(),
				unit_id: $('#pd_unit_code :selected').val(),
				currency_id: $('#pd_currency_code :selected').val(),
				purchase_price: $('#pd_purchase_price').val(),
				sale_price: $('#pd_sale_price').val(),
				apply_date: $('#pd_apply_date').val(),
				warranty_label: $('#pd_warranty_label').val(),
				warranty_period: $('#pd_warranty_period').val(),
				description: $('#pd_description').val()
			};
			
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				url: path + '/addProductDetail/',
				type: 'POST',
				dataType: 'json',
				data: dataPost,	
				success: function(response) {				
					if (response.success == true) {				
						/* save this row to datatable */
						$('#pd_id').val(response.product_detail_id);
						saveRow(oTable, nEditing);
						nNew = false;
						nEditing = null;
						
						showControlButton(STATE_NONE);
					}
					else{
						slideMessageMultiConfig('Cảnh báo', 'Tạo mới không thành công!!', 'warning', 40);
						return;
					}			
				},
				error: function(xhr, textStatus, thrownError) {
					slideMessageMultiConfig('Cảnh báo', 'Tạo mới không thành công!!', 'warning', 40);
					return;
					//console.log(thrownError);
				}
			})
			
		} else if (nEditing != null){
			
			var dataPost = {
				product_detail_id: $('#pd_id').val(), 
				barcodeid: $('#pd_barcodeid').val(), 
				quantity: $('#pd_quantity').val(),
				unit_id: $('#pd_unit_code :selected').val(),
				currency_id: $('#pd_currency_code :selected').val(),
				purchase_price: $('#pd_purchase_price').val(),
				sale_price: $('#pd_sale_price').val(),
				apply_date: $('#pd_apply_date').val(),
				warranty_label: $('#pd_warranty_label').val(),
				warranty_period: $('#pd_warranty_period').val(),
				description: $('#pd_description').val()
			};
			
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				url: path + '/updateProductDetail/',
				type: 'POST',
				dataType: 'json',
				data: dataPost,	
				success: function(response) {				
					if (response.success == true) {				
						/* Editing this row and want to save it */
						saveRow(oTable, nEditing);
						nEditing = null;
						
						showControlButton(STATE_NONE);
					}
					else{
						slideMessageMultiConfig('Cảnh báo', 'Cập nhật không thành công!!', 'warning', 40);
						return;
					}			
				},
				error: function(xhr, textStatus, thrownError) {
					slideMessageMultiConfig('Cảnh báo', 'Cập nhật không thành công!!', 'warning', 40);
					return;
					//console.log(thrownError);
				}
			})
		}		
	});
	//----------------------------------------------------------------------------------------------------------
	// End DataTable Editable
	//----------------------------------------------------------------------------------------------------------
	
	
	$('#btnBack').click(function() {
		document.location.href = path + "/productCategories";

	});
	
	$('#btnUpdate').click(function() {
		// validate dataPost
		if(validate())
		{
			updateProduct();
		}
	});
	
	$('#btnAdd').click(function() {
		// validate dataPost
		if(validate())
		{
			addProduct();
		}
	});
	
	//get list data of combobox
	getAllArrayCombobox();
	showControlButton(STATE_NONE);

	//init Jquery Files Upload
	uploadProductImage();
});

function validate()
{
	if ($.trim($('#product_code').val()) == "")
	{
		$('#product_code').focus();
        slideMessageMultiConfig('Cảnh báo', 'Mã SP không được rỗng', 'warning', 40);
		return false;
    } 
	else if ($.trim($('#product_name').val()) == "")
	{
		$('#product_name').focus();
        slideMessageMultiConfig('Cảnh báo', 'Tên SP không được rỗng', 'warning', 40);
		return false;		
	}	
	
	return true;
}

function updateProduct()
{		
	var dataPost = {
			product_id: $('#product_id').val(), 
			product_code: $('[name="product_code"]').val(), 
			product_type_id: $('#product_type_id :selected').val(),
			product_name: $('[name="product_name"]').val(),
			producer_id: $('#producer_id :selected').val(),
			base_unit_id: $('#base_unit_id :selected').val(),
			barcode: $('[name="barcode"]').val(),
			trademark: $('[name="trademark"]').val(),
			model: $('[name="model"]').val(),
			color: $('[name="color"]').val(),
			weight: $('[name="weight"]').val(),
			length: $('[name="length"]').val(),
			width: $('[name="width"]').val(),
			height: $('[name="height"]').val(),
			short_description: $('[name="short_description"]').val(),
			long_description: $('[name="long_description"]').val()	
	};
	var dataObj = null;	
	
	$.ajax({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		url: path + '/updateProduct/',
		type: 'POST',
		dataType: 'json',
		data: dataPost,	
		success: function(response) {
			dataObj  = response;
			console.log(dataObj);
			if (dataObj.success == true) {				
				//slideMessageMultiConfig('Thông tin', dataObj.alert, 'success', 20);
				document.location.href = path + "/productCategories";
			}
			else{
				slideMessageMultiConfig('Cảnh báo', 'Cập nhật không thành công', 'warning', 40);
			}			
		},
		error: function(xhr, textStatus, thrownError) {
			console.log(thrownError);
		}
	})	
}

function addProduct()
{		
	var dataPost = {
			product_id: $('#product_id').val(), 
			product_code: $('[name="product_code"]').val(), 
			product_type_id: $('#product_type_id :selected').val(),
			product_name: $('[name="product_name"]').val(),
			producer_id: $('#producer_id :selected').val(),
			base_unit_id: $('#base_unit_id :selected').val(),
			barcode: $('[name="barcode"]').val(),
			trademark: $('[name="trademark"]').val(),
			model: $('[name="model"]').val(),
			color: $('[name="color"]').val(),
			weight: $('[name="weight"]').val(),
			length: $('[name="length"]').val(),
			width: $('[name="width"]').val(),
			height: $('[name="height"]').val(),
			short_description: $('[name="short_description"]').val(),
			long_description: $('[name="long_description"]').val()	
	};
	var dataObj = null;	
	
	$.ajax({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		url: path + '/addProduct/',
		type: 'POST',
		dataType: 'json',
		data: dataPost,	
		success: function(response) {
			if (response.success == true) {				
				//slideMessageMultiConfig('Thông tin', response.alert, 'success', 20);
				document.location.href = path + "/productCategories";
			}
			else{
				slideMessageMultiConfig('Cảnh báo', 'Thêm Sản Phẩm mới không thành công!!!', 'warning', 40);
			}			
		},
		error: function(xhr, textStatus, thrownError) {
			console.log(thrownError);
		}
	})	
}

var arrUnit, arrCurrency;
function getAllArrayCombobox()
{		
	$.ajax({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		url: path + '/getAllArrayCombobox/',
		type: 'POST',
		dataType: 'json',
		success: function(response) {
			arrUnit = response.arrUnit;
			arrCurrency = response.arrCurrency;				
		},
		error: function(xhr, textStatus, thrownError) {
			console.log(thrownError);
		}
	})	
}

function renderCombobox(data, selectedID, classname, id, name, startValue)
{
	var result = '';
	result +=  '<select  class="'+ classname + '" id="'+id+'" name="'+name+'">';
	
	if (startValue != "")	
		result +=  '<option value="">'+startValue+'</option>';
		
	for (var i=0; i<data.length; i++)
	{
		if (data[i]['key'] == selectedID)
			result += '<option value="'+data[i]['key']+'" selected>'+data[i]['value']+'</option>';
		else
			result += '<option value="'+data[i]['key']+'">'+data[i]['value']+'</option>';
	}
	result += '</select>';
	
	return result;
}

function renderDatetime(data, classname, format, startdate, id)
{
	var result = '';
		
	result += '<div class="'+classname+'" data-date-format="'+format+'" data-date-start-date="'+startdate+'">';
	result += 	'<input type="text" id="'+id+'" class="form-control" value="'+data+'" readonly>';
	result +=	'<span class="input-group-btn">';
	result +=		'<button class="btn default" type="button">';
	result +=			'<i class="fa fa-calendar"></i>';
	result +=		'</button>';
	result +=	'</span>';
	result += '</div>';		
	
	return result;
}

var STATE_NONE 		= 0;
var STATE_NEW 		= 1;
var STATE_UPDATE 	= 2;
var STATE_DELETE 	= 3;
var STATE_CANCEL 	= 4;
var STATE_SAVE 		= 5;
var STATE_SELECT	= 6;
function showControlButton(state)
{
	switch (state)	
	{
		case STATE_NONE:
		{
			$("#tblProductDetail_new").removeClass('display-none');
			
			$("#tblProductDetail_update").addClass('display-none');
			$("#tblProductDetail_delete").addClass('display-none');
			$("#tblProductDetail_cancel").addClass('display-none');
			$("#tblProductDetail_save").addClass('display-none');
			break;
		}		
		case STATE_NEW:
		{
			$("#tblProductDetail_cancel").removeClass('display-none');
			$("#tblProductDetail_save").removeClass('display-none');
			
			$("#tblProductDetail_new").addClass('display-none');			
			$("#tblProductDetail_update").addClass('display-none');
			$("#tblProductDetail_delete").addClass('display-none');
		
			break;
		}
		case STATE_UPDATE:
		{
			$("#tblProductDetail_cancel").removeClass('display-none');
			$("#tblProductDetail_save").removeClass('display-none');
			
			$("#tblProductDetail_new").addClass('display-none');			
			$("#tblProductDetail_update").addClass('display-none');
			$("#tblProductDetail_delete").addClass('display-none');
		
			break;
		}
		case STATE_SELECT:
		{
			$("#tblProductDetail_new").removeClass('display-none');
			$("#tblProductDetail_update").removeClass('display-none');			
			$("#tblProductDetail_delete").removeClass('display-none');
			
			$("#tblProductDetail_cancel").addClass('display-none');
			$("#tblProductDetail_save").addClass('display-none');
			break;
		}
		case STATE_DELETE:
		{
			$("#tblProductDetail_new").addClass('display-none');
			$("#tblProductDetail_update").addClass('display-none');
			$("#tblProductDetail_delete").addClass('display-none');
			$("#tblProductDetail_cancel").addClass('display-none');
			$("#tblProductDetail_save").addClass('display-none');
			break;
		}
		case STATE_SAVE:
		{
			$("#tblProductDetail_new").addClass('display-none');
			$("#tblProductDetail_update").addClass('display-none');
			$("#tblProductDetail_delete").addClass('display-none');
			$("#tblProductDetail_cancel").addClass('display-none');
			$("#tblProductDetail_save").addClass('display-none');
			break;
		}
	}
}

function uploadProductImage () {
	// Initialize the jQuery File Upload widget:
	$('#fileupload').fileupload({
		disableImageResize: false,
		autoUpload: false,
		disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
		maxFileSize: 5000000,
		acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
	});

	// Enable iframe cross-domain access via redirect option:
	$('#fileupload').fileupload(
		'option',
		'redirect',
		window.location.href.replace(
			/\/[^\/]*$/,
			'/cors/result.html?%s'
		)
	);

	// Upload server status check for browsers with CORS support:
	if ($.support.cors) {
		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: 'HEAD'
		}).fail(function () {
			$('<div class="alert alert-danger"/>')
				.text('Upload server currently unavailable - ' +
				new Date())
				.appendTo('#fileupload');
		});
	}

	// Load & display existing files:
	$('#fileupload').addClass('fileupload-processing');
	$.ajax({
		// Uncomment the following to send cross-domain cookies:
		//xhrFields: {withCredentials: true},
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		url: $('#fileupload').attr("action"),
		dataType: 'json',
		context: $('#fileupload')[0]
	}).always(function () {
		$(this).removeClass('fileupload-processing');
	}).done(function (result) {
		$(this).fileupload('option', 'done')
			.call(this, $.Event('done'), {result: result});
	});
}