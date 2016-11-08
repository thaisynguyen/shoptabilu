$(document).ready(function () {

	//
	// Pipelining function for DataTables. To be used to the `ajax` option of DataTables
	//
	$.fn.dataTable.pipeline = function ( opts ) {
		// Configuration options
		var conf = $.extend( {
			pages: 5,     // number of pages to cache
			url: '',      // script url
			data: null,   // function or object with parameters to send to the server
						  // matching how `ajax.data` works in DataTables
			method: 'GET' // Ajax HTTP method
		}, opts );
	 	//console.log(conf);

		// Private variables for storing the cache
		var cacheLower = -1;
		var cacheUpper = null;
		var cacheLastRequest = null;
		var cacheLastJson = null;
	 
		return function ( request, drawCallback, settings ) {
			var ajax          = false;
			var requestStart  = request.start;
			var drawStart     = request.start;
			var requestLength = request.length;
			var requestEnd    = requestStart + requestLength;
			 
			if ( settings.clearCache ) {
				// API requested that the cache be cleared
				ajax = true;
				settings.clearCache = false;
			}
			else if ( cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper ) {
				// outside cached data - need to make a request
				ajax = true;
			}
			else if ( JSON.stringify( request.order )   !== JSON.stringify( cacheLastRequest.order ) ||
					  JSON.stringify( request.columns ) !== JSON.stringify( cacheLastRequest.columns ) ||
					  JSON.stringify( request.search )  !== JSON.stringify( cacheLastRequest.search )
			) {
				// properties changed (ordering, columns, searching)
				ajax = true;
			}
			 
			// Store the request for checking next time around
			cacheLastRequest = $.extend( true, {}, request );
	 
			if ( ajax ) {
				// Need data from the server
				if ( requestStart < cacheLower ) {
					requestStart = requestStart - (requestLength*(conf.pages-1));
	 
					if ( requestStart < 0 ) {
						requestStart = 0;
					}
				}
				 
				cacheLower = requestStart;
				cacheUpper = requestStart + (requestLength * conf.pages);
	 
				request.start = requestStart;
				request.length = requestLength*conf.pages;
	 
				// Provide the same `data` options as DataTables.
				if ( $.isFunction ( conf.data ) ) {
					// As a function it is executed with the data object as an arg
					// for manipulation. If an object is returned, it is used as the
					// data object to submit
					var d = conf.data( request );
					if ( d ) {
						$.extend( request, d );
					}
				}
				else if ( $.isPlainObject( conf.data ) ) {
					// As an object, the data given extends the default
					$.extend( request, conf.data );
				}
	 
				settings.jqXHR = $.ajax( {
					"type":     conf.method,
					"url":      conf.url,
					"data":     request,
					"dataType": "json",
					"cache":    false,
					"success":  function ( json ) {
						cacheLastJson = $.extend(true, {}, json);
	 
						if ( cacheLower != drawStart ) {
							json.data.splice( 0, drawStart-cacheLower );
						}
						if ( requestLength >= -1 ) {
							json.data.splice( requestLength, json.data.length );
						}
						 
						drawCallback( json );
					}
				} );
			}
			else {
				console.log("ko cache");
				json = $.extend( true, {}, cacheLastJson );
				json.draw = request.draw; // Update the echo for each response
				json.data.splice( 0, requestStart-cacheLower );
				json.data.splice( requestLength, json.data.length );

				console.log(json);

				drawCallback(json);
			}
		}
	};
	 
	// Register an API method that will empty the pipelined data, forcing an Ajax
	// fetch on the next draw (i.e. `table.clearPipeline().draw()`)
	$.fn.dataTable.Api.register( 'clearPipeline()', function () {
		return this.iterator( 'table', function ( settings ) {
			settings.clearCache = true;
		} );
	} );
  
	//
	// DataTables initialisation
	//
	$('#tblProduct').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax": $.fn.dataTable.pipeline( {
            url: path + "/viewProduct/",
            pages: 5 // number of pages to cache
        } ),
		"columns": [
			{"data": "product_id"},
			{"data": "product_name"},
			{"data": "barcode"},
			{"data": "product_type_name"},
			{"data": "producer_name"},
			{"data": "weight"},
			{"data": "color"}
        ]
    } );	
    
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
				slideMessageMultiConfig('Th�ng tin', dataObj.alert, 'success', 20);
				$('#tblProduct').DataTable().row($('#pid_' + idProduct)).remove().draw( false );				
			}
			else {				
				slideMessageMultiConfig('C?nh b�o', 'Xoa kh�ng th�nh c�ng', 'warning', 40);
			}
			return dataObj;
		},
		error: function (xhr, textStatus, thrownError) {
			console.log('333');
			console.log(thrownError);
		}
	})	
}


