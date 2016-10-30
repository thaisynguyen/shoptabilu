<ul class="page-sidebar-menu  page-header-fixed " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200" style="padding-top: 20px">
	<!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
	<li class="sidebar-toggler-wrapper hide">
		<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
		<div class="sidebar-toggler"> </div>
		<!-- END SIDEBAR TOGGLER BUTTON -->
	</li>
	<!-- DOC: To remove the search box from the sidebar you just need to completely remove the below "sidebar-search-wrapper" LI element -->
<?php
		$isDashboard = false;
		$isSale = false;
		$isCategories = false;
		$isInventory = false;
		if(Request::is('*adminhome')
		|| Request::is('*statistics')
		|| Request::is('*saleReport')
		|| Request::is('*inventoryReport')){
			$isDashboard = true;
			$isSale = false;
			$isCategories = false;
			$isInventory = false;
		}

		if(Request::is('*saleInvoice')
		|| Request::is('*saleInvoiceList')){
			$isDashboard = false;
			$isSale = true;
			$isCategories = false;
			$isInventory = false;
		}

		if(Request::is('*inventoryInvoice')
		|| Request::is('*inventoryInvoiceList')){
			$isDashboard = false;
			$isSale = false;
			$isCategories = false;
			$isInventory = true;
		}

		if(Request::is('*currency*')
		|| Request::is('*unit*')
		|| Request::is('*product*')
		|| Request::is('*subject*')
		|| Request::is('*users*')
		|| Request::is('*producer*')){
			$isDashboard = false;
			$isSale = false;
			$isCategories = true;
			$isInventory = false;
		}
?>
	<li {{ ($isDashboard == true	? 'class="nav-item start active open"' : 'class="nav-item"') }}>
		<a href="javascript:;" class="nav-link nav-toggle">
			<i class="icon-home"></i>
			<span class="title">QUẢN TRỊ</span>
			<span class="selected"></span>
			<span {{ ($isDashboard == true ? 'class="arrow open"' : 'class="arrow"') }} ></span>
		</a>
		<ul class="sub-menu">
			<li {{(Request::is('*saleReport') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/saleReport')}}" class="nav-link ">
					<i class="icon-bar-chart"></i>
					<span class="title">Báo cáo bán hàng</span>
					<span class="selected"></span>
				</a>
			</li>
			<li {{(Request::is('*inventoryReport') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/inventoryReport')}}" class="nav-link ">
					<i class="icon-bar-chart"></i>
					<span class="title">Báo cáo xuất nhập tồn</span>
					<span class="selected"></span>
				</a>
			</li>
		</ul>
	</li>
	<li {{ ($isSale == true ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
		<a href="javascript:;" class="nav-link nav-toggle">
			<i class="icon-diamond"></i>
			<span class="title">BÁN HÀNG</span>
			<span {{ ($isSale == true ? 'class="arrow open"' : 'class="arrow"') }} ></span>
		</a>
		<ul class="sub-menu">
			<li {{ (   Request::is('*saleInvoice')
                     	|| Request::is('*addSaleInvoice')
                        || Request::is('*updateSaleInvoice*') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/saleInvoice')}}" class="nav-link ">
					<span class="title">Hóa đơn bán hàng</span>
				</a>
			</li>
			<li {{ (   Request::is('*saleInvoiceList') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/saleInvoice')}}" class="nav-link ">
					<span class="title">Danh sách HĐ bán hàng</span>
				</a>
			</li>

		</ul>
	</li>
	<li {{ ($isInventory == true ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
		<a href="javascript:;" class="nav-link nav-toggle">
			<i class="icon-diamond"></i>
			<span class="title">KHO</span>
			<span {{ ($isInventory == true ? 'class="arrow open"' : 'class="arrow"') }} ></span>
		</a>
		<ul class="sub-menu">
			<li {{ (Request::is('*inventoryInvoice') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/saleInvoice')}}" class="nav-link ">
					<span class="title">Phiếu nhập kho</span>
				</a>
			</li>
			<li {{ (Request::is('*inventoryInvoiceList') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/inventoryInvoiceList')}}" class="nav-link ">
					<span class="title">Danh sách phiếu nhập kho</span>
				</a>
			</li>
		</ul>
	</li>
	<li {{( $isCategories == true ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
		<a href="javascript:;" class="nav-link nav-toggle">
			<i class="icon-layers"></i>
			<span class="title">DANH MỤC</span>
			<span {{ ($isCategories == true ? 'class="arrow open"' : 'class="arrow"') }} ></span>
		</a>
		<ul class="sub-menu">
			<li {{ (Request::is('*currencyCategories') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/currencyCategories')}}" class="nav-link ">
					<span class="title">Tiền tệ</span>
				</a>
			</li>
			<li {{(Request::is('*unitCategories') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/unitCategories')}}" class="nav-link nav-toggle">
					<span class="title">Đơn vị tính</span>
					<span class="selected"></span>
				</a>
			</li>
			<li {{(Request::is('*productTypeCategories') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/productTypeCategories')}}" class="nav-link ">
					<span class="title">Loại sản phẩm</span>
				</a>
			</li>
			<li {{(Request::is('*productCategories') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/productCategories')}}" class="nav-link ">
					<span class="title">Sản phẩm</span>
				</a>
			</li>
			<li {{(Request::is('*subjectCategories') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/subjectCategories')}}" class="nav-link ">
					<span class="title">Khách hàng/Nhà cung cấp</span>
				</a>
			</li>
			<li {{(Request::is('*producerCategories') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/producerCategories')}}" class="nav-link ">
					<span class="title">Nhà sản xuất</span>
				</a>
			</li>
			<li {{(Request::is('*companyProfile') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/companyProfile')}}" class="nav-link ">
					<span class="title">Hồ sơ công ty</span>
				</a>
			<li {{(Request::is('*languageTranslator') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/languageTranslator')}}" class="nav-link ">
					<span class="title">Dịch đa ngôn ngữ</span>
				</a>
			</li>
			<li {{(Request::is('*languageTranslatorList') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/languageTranslatorList')}}" class="nav-link ">
					<span class="title">Danh sách dịch đa ngôn ngữ</span>
				</a>
			</li>
			<li {{(Request::is('*userManagement') ? 'class="nav-item start active open"' : 'class="nav-item"') }}>
				<a href="{{url('/userManagement')}}" class="nav-link ">
					<span class="title">Quản lý người dùng</span>
				</a>
			</li>
		</ul>
	</li>

</ul>