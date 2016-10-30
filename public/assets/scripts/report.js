/**
 * Created by thamvtn on 11/12/2015.
 */

function compare(a,b) {
    if (a[1] > b[1])
        return -1;
    if (a[1] < b[1])
        return 1;
    return 0;
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


// HANDLER EVENT FOR RADIO BUTTON "Tháng" --> 1
function timeOptionForMonth() {

    var isCheck = document.getElementById("rdSelectByMonth").checked;
    if (isCheck) {
        document.getElementById("slMonthByMonth").disabled = false;
        document.getElementById("slYearByMonth").disabled = false;

        document.getElementById("slMonthByFromMonth").disabled = true;
        document.getElementById("slYearByFromMonth").disabled = true;
        document.getElementById("slMonthByToMonth").disabled = true;
        document.getElementById("slYearByToMonth").disabled = true;

        showData();
    }
}

// HANDLER EVENT FOR RADIO BUTTON "T? tháng" --> 2
function timeOptionForFromMonth() {
    document.getElementById("slMonthByMonth").disabled = true;
    document.getElementById("slYearByMonth").disabled = true;

    document.getElementById("slMonthByFromMonth").disabled = false;
    document.getElementById("slYearByFromMonth").disabled = false;
    document.getElementById("slMonthByToMonth").disabled = false;
    document.getElementById("slYearByToMonth").disabled = false;

    showData();
}

//handler when click select month --> 3
function changeShowThisMonth(){
    showData();
}

// handle event for select year --> 4
function changeShowThisYear(){
    showData();
}

//handler when click select from month --> 5
function changeShowFromMonth(){
    showData();
}

//handler when click select to month --> 6
function changeShowToMonth(){
    showData();
}

//handler when click select form year --> 7
function changeShowFromYear(){
    showData();

}

//handler when click select to year --> 8
function changeShowToYear(){
    showData();
}

$(document).ready(function () {

    document.getElementById("slMonthByMonth").disabled = false;
    document.getElementById("slYearByMonth").disabled = false;

    document.getElementById("slMonthByFromMonth").disabled = true;
    document.getElementById("slYearByFromMonth").disabled = true;
    document.getElementById("slMonthByToMonth").disabled = true;
    document.getElementById("slYearByToMonth").disabled = true;
});
