@extends('admin.layouts.admindashboard')
@section('section')
@include('alerts.errors')
@include('alerts.success')

<?php
    use Utils\commonUtils;
    $curpage =  $data->currentPage();
?>
<div class="page-content-wrapper">
    <!-- BEGIN CONTENT BODY -->
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->

        <!-- BEGIN PAGE BAR -->
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <a href="{{url('/adminhome')}}">Trang chủ</a>
                    <i class="fa fa-caret-right"></i>
                </li>
                <li>
                    <a href="#">Danh mục</a>
                    <i class="fa fa-caret-right"></i>
                </li>
                <li>
                    <span>Sản phẩm</span>
                </li>
            </ul>
        </div>
        <!-- END PAGE BAR -->
        </BR>

                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-green">
                            <i class="icon-briefcase font-green"></i>
                            <span class="caption-subject bold uppercase">Danh mục Sản phẩm</span>
                        </div>
                        <div class="tools"> </div>
                    </div>
                    <div class="portlet-body">
                        <table id="tblProduct" class="table table-striped table-bordered table-hover dt-responsive nowrap" width="100%">
                            <thead>
                            <tr>
                                <th></th>
                                <th >Mã SP</th>
                                <th >Tên SP</th>
                                <th >Loại</th>
                                <th >Nhà SX</th>
                                <th >Đơn VT</th>
                                <th >Barcode</th>
                                <th >Giá</th>
                                <th >Loại tiền tệ</th>
								<th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th></th>
                                <td>NK001</td>
                                <td>Nikon D90</td>
                                <td>Máy Ảnh</td>
                                <td>Nikon</td>
                                <td>cái</td>
                                <td>2343241213</td>
                                <td>10,000,000</td>
                                <td>VNĐ</td>
								<td>
									<a href="javascript:;" class="btn btn-icon-only red"><i class="fa fa-trash"></i></a>
									<a href="javascript:;" class="btn btn-icon-only blue"><i class="fa fa-edit"></i></a>
								</td>
                            </tr>
                            <tr>
                                <th></th>								
                                <td>NK002</td>
                                <td>Nikon D92</td>
                                <td>Máy Ảnh 2</td>
                                <td>Nikon 2</td>
                                <td>cái</td>
                                <td>8798779798</td>
                                <td>10,000,000</td>
                                <td>VNĐ</td>
								<td>
									<a href="javascript:;" class="btn btn-icon-only red"><i class="fa fa-trash"></i></a>
									<a href="javascript:;" class="btn btn-icon-only blue"><i class="fa fa-edit"></i></a>
								</td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END EXAMPLE TABLE PORTLET-->

    </div>
    <!-- END CONTENT BODY -->
</div>

<script>
	var initTableProduct = function () 
	{
        var table = $('#tblProduct');

        var oTable = table.dataTable({
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

            // Or you can use remote translation file
            //"language": {
            //   url: '//cdn.datatables.net/plug-ins/3cfcc339e89/i18n/Portuguese.json'
            //},

            // setup buttons extentension: http://datatables.net/extensions/buttons/
            buttons: [
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
            columnDefs: [ {
                className: 'control',
                orderable: false,
                targets:   0
            } ],

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
    }
	initTableProduct();
</script>
@stop