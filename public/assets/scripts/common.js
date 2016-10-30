function slideMessage(type, message){
    $.notify({
        message: message,
    },{
        allow_dismiss: true,
        type: type,
        placement: {
            from: "top",
            align: "center"
        },
        animate: {
            enter: 'animated fadeInDown',
            exit: 'animated fadeOutUp'
        },
        delay: 3000,
        z_index: 9999,
        timer: 1000
    });
}

function browserName(){
    var Browser = navigator.userAgent;
    if (Browser.indexOf('Trident') >= 0 || Browser.indexOf('MSIE') >= 0){
        Browser = 'MSIE';
    }
    else if (Browser.indexOf('Firefox') >= 0){
        Browser = 'Firefox';
    }
    else if (Browser.indexOf('Chrome') >= 0){
        Browser = 'Chrome';
    }
    else if (Browser.indexOf('Safari') >= 0){
        Browser = 'Safari';
    }
    else if (Browser.indexOf('Opera') >= 0){
        Browser = 'Opera';
    }
    else{
        Browser = 'UNKNOWN';
    }
    return Browser;
}

/**
 * Reload page with parameters
 * @param actionUrl
 * @param actionName
 */
function reloadPageWithParam(actionUrl, actionName, strParam){
    var path = actionUrl;
    var pos = path.indexOf(actionName);
    path = path.substring(0, pos);
    path += actionName +'/';
    var param = strParam.split('/');

    for(var i = 0; i < param.length; i++){
        if(document.getElementById(param[i])){
            var v = document.getElementById(param[i]).value;
            v = (v != 0) ? v : 0;
            path += v + '/';
        }
    }
    window.location.href = path;
}

/**
 * Get url parameter
 * @param sParam
 * @returns {*}
 */
function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for(i = 0; i < sURLVariables.length; i++){
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

/**
 * Reload to sort page
 * @param actionUrl
 * @param sortType
 * @param sortColumn
 */
function reloadToSortPage(sortDirection, sortColumn){

    var pathname = window.location.pathname;
    //console.log(sortColumn);
    $('th').each(function (column){
        if($(this).attr('sort_key') == sortColumn){
            if(sortDirection == '0'){
                $(this).attr('sort_direction', 'asc');
                sortDirection = 'asc';
            } else if(sortDirection == 'asc') {
                $(this).attr('sort_direction', 'desc');
                sortDirection = 'desc';
            } else {
                $(this).attr('sort_direction', 'asc');
                sortDirection = 'asc';
            }
        } else {
            $(this).attr('sort_direction', '0');
        }
    });
    window.location.href = pathname + '?sort=' + sortDirection + '&column=' + sortColumn;
}

/**
 * Set sort dimention on page load
 */
function setSortDimentionOnPageLoad(){
    var sortDirection = getUrlParameter('sort');
    var column = getUrlParameter('column');
    //console.log(column);

    $('th').each(function (){
        if($(this).attr('sort_key') == column){
            $(this).attr('sort_direction', sortDirection);
            if(sortDirection == 'asc'){
                $(this).find('i').removeClass('fa-sort').addClass('fa-sort-desc');
            }else if(sortDirection == 'desc') {
                $(this).find('i').removeClass('fa-sort-desc').addClass('fa-sort-asc');
            }else{
                $(this).find('i').removeClass('fa-sort').addClass('fa-sort');
            }

        } else {
            $(this).attr('sort_direction', '0');
        }
    });
}

function sortOnPageLoad(){
    setSortDimentionOnPageLoad();

    $(function(){
        $('th').each(function (column){
            $(this).addClass('sortable').click(function (){
                reloadToSortPage($(this).attr('sort_direction'), $(this).attr('sort_key'));
            });
        });
    });
}

function btnDeleteMultiRowClick(btnMultiDelete, tblName){
    btnMultiDelete.click(function(){
        var selectedId = [];
        var selectedName = [];
        tblName.find("input:checked").each(function (i, ob) {
            selectedId.push($(this).attr('rowId'));
            selectedName.push('Mã: ' + $(this).attr('rowCode') + ' - Tên: ' + $(this).attr('rowName'));
        });

        if(selectedId.length > 0){
            $('#arrId').val(selectedId);
            $('#arrName').val(selectedName);
            $('#popupDeleteSelected').modal('show');
        } else {
            alert('Vui lòng chọn mục để xóa!');
        }
    });
}

function getDescriptionByFormula(formula, label){
    formula = parseInt(formula);
    switch (formula){
        case 1:
            label.text('Lấy trung bình cộng của kế hoạch/thực hiện bất kỳ của chức danh được quận trưởng phân việc, chịu trách nhiệm việc đó (từ sheet Bảng phân trọng số)');
            break;
        case 2:
            label.text('Lấy dữ liệu từ import/nhập tay');
            break;
        case 3:
            label.text('Lấy trung bình cộng kế hoạch/thực hiện của tất cả các chức danh');
            break;
        case 4:
            label.text('Lấy tổng kế hoạch/thực hiện của nhân viên bán hàng');
            break;
        case 5:
            label.text('Lấy tổng kế hoạch/thực hiện của KAM/AM');
            break;
        case 6:
            label.text('Lấy tổng kế hoạch/thực hiện của chuyên viên khách hàng cá nhân + chuyên viên khách hàng doanh nghiệp');
            break;
        case 7:
            label.text('Lấy tổng kế hoạch/thực hiện của chuyên viên khách hàng cá nhân + cửa hàng trưởng');
            break;
        case 8:
            label.text('Lấy tổng kế hoạch/thực hiện của giao dịch viên');
            break;
        case 9:
            label.text('Lấy tổng kế hoạch/thực hiện của chuyên viên khách hàng cá nhân + chuyên viên khách hàng doanh nghiệp + cửa hàng trưởng');
            break;
    }

}

function fixHeader(tableName, tableHead, tableClone){
    var tableOffset = tableName.offset().top;
    var header = tableHead.clone();
    var fixedHeader = tableClone.append(header);

    $(window).bind("scroll", function() {
        var offset = $(this).scrollTop();

        if (offset >= tableOffset && fixedHeader.is(":hidden")) {
            fixedHeader.show();
        }
        else if (offset < tableOffset) {
            fixedHeader.hide();
        }
    });
}

// formart big number ex: 2500.345678 --> 2,500.35
function formatBigNumber(number){
    var arrNumber = [];
    number = ( Math.round(number * 1000)/1000);
    var numString = number.toString();
    arrNumber = numString.split("");
    var numResult = '';
    var num = arrNumber.length%3;
    var point = 100;
    for(var i=0; i<arrNumber.length; i++){
        if(arrNumber[i] == '.'){
            point = i;
        }
    }
    if((point > 3) || (point == 100 && arrNumber.length > 3)){
        for(var j=0; j<arrNumber.length; j++){
            numResult += arrNumber[j];
            if(point == 0){
                if(((j+1-num) % 3 == 0) && (j < arrNumber.length-1)){
                    numResult += ',';
                }
            } else {
                if(((j+1-num) % 3 == 0) && (j < arrNumber.length-1) && (j<point-1)){
                    numResult += ',';
                }
            }
        }
    } else {
        numResult = numString;
    }

    return numResult;
}

function formatNumber(number, numberAfterDot){
    var result = 0;
    if(numberAfterDot == 0){
        result = Math.round(number);
    } else {
        var n = "1";
        for(var nad=0; nad<numberAfterDot; nad++){
            n +='0';
        }
        n = parseInt(n);
        result = (Math.round(number*n)/n);
    }
    return result;
}
//KEEP CURRENT ACTIVE TAB
$(function() {
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // save the latest tab; use cookies if you like 'em better:
        localStorage.setItem('lastTab', $(this).attr('href'));
    });
    // go to the latest tab, if it exists:
    var lastTab = localStorage.getItem('lastTab');
    if (lastTab) {
        $('[href="' + lastTab + '"]').tab('show');
    }
});

function renderGoalType(number) {
    var result = '';
    switch (number) {
        case 1:
            result = 'Càng lớn càng tốt';
            break;
        case 2:
            result = 'Càng nhỏ càng tốt';
            break;
    }
    return result;
}

function clickCheckBox() {
    var valuesCk = $('input:checkbox:checked.ckbExport').map(function () {
        return this.value;
    }).get();
    var result = '';
    if(valuesCk != null || valuesCk != '') {
        for (var p = 0; p < valuesCk.length; p++) {
            var arrName = valuesCk[p].split("/");
            var nameSuccess = valuesCk[p];
            if (arrName.length > 1) {
                nameSuccess = '';
                var arrName = valuesCk[p].split("");
                for (var n = 0; n < arrName.length; n++) {
                    if (arrName[n] == '/') {
                        nameSuccess += '*';
                    } else {
                        nameSuccess += arrName[n];
                    }
                }
            }

            if (p == (valuesCk.length - 1)) {
                result += nameSuccess;
            } else {
                result += nameSuccess + ',';
            }
        }
    }
    var elem = document.getElementById("codeChoose");
    elem.value = result;
}

// set cookie
function setCookie(cname,cvalue) {
    document.cookie = cname+"="+cvalue+"; ";
}

//get value of cookie
function getCookie(cname) {
    var ca = document.cookie.split(';');
    var result = '';
    for(var i=0; i<ca.length; i++) {
        var c = ca[i].split('=');
        var nameCookie = c[0].trim();
        var valueCookie = c[1].trim();
        if(nameCookie == cname){
            result = valueCookie;
            break;
        }
    }
    return result;
}

//loading page
function showStuff(id) {
    document.getElementById(id).style.visibility = 'visible';
}

function stopStuff(id){
    document.getElementById(id).style.visibility = 'hidden';
}

function stopStuffChart(id){
    window.scrollTo(0, 0);
    setTimeout(function () {
        $('#axx').fadeOut('fast');
        document.getElementById(id).style.visibility = 'hidden';
        $('#axx').fadeIn();
    }, 300)
}

function formatDate(date){
    var today = new Date(date);
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!

    var yyyy = today.getFullYear();
    if(dd<10){
        dd='0'+dd
    }
    if(mm<10){
        mm='0'+mm
    }
    var today = dd+'/'+mm+'/'+yyyy;
    return today;
}

//delete child
function delChild(id) {
    var myNode = document.getElementById(id);
    while (myNode.firstChild) {
        myNode.removeChild(myNode.firstChild);
    }
}

function showLoading(id){
    document.getElementById('loadingDelete-'+id).setAttribute('class','loading-delete display');
    document.getElementById('waitingDelete-'+id).setAttribute('class','display');
    document.getElementById('contentDelete-'+id).setAttribute('class','hidden');
    document.getElementById('btnCancel-'+id).disabled = true;

   /* var html = "<span>Dữ liệu đang được xóa và tính toán lại. Vui lòng đợi trong ít phút!..</span>";

    $('contentDelete-'+id).append('');
    $('contentDelete-'+id).append(html);*/

}