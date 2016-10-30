<div class="row margin-form">
    <div class="col-md-12 col-xs-12 marg-bottom-radio">
        <div class="col-md-3 col-xs-12 marg-bottom">
            <label class="radio-inline" >
                <input type="radio" name="radio" id="rdSelectByMonth"
                       class="marg-top-radio" onchange="timeOptionForMonth();"  checked><b>Tháng</b>
            </label><br>
            <div class="col-md-6 col-xs-5 btnChoose margin-top-8" >
                <input type="hidden" value="1" id="txtRadioChecked"
                       onchange="{{$link}}">
                <select class="form-control" id="slMonthByMonth" onchange="changeShowThisMonth();"
                        onchange="{{$link}}">
                    <?php
                    $arrayMonth = \Utils\commonUtils::defaultMonth();
                    for($i=0; $i< count($arrayMonth); $i++){ ?>
                    <option value="<?php echo $arrayMonth[$i] ?>"><?php echo $arrayMonth[$i] ?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-md-6 col-xs-7 margin-left-5 margin-top-8">
                <select class="form-control" id="slYearByMonth" onchange="changeShowThisYear();"
                        onchange="{{$link}}">
                    <?php
                    $arrYear = $year;
                    if(count($arrYear) > 0){
                        for($i=0; $i< count($arrYear); $i++){ ?>
                        <option value="<?php echo $arrYear[$i] ?>">
                            <?php echo $arrYear[$i] ?>
                        </option>
                        <?php }
                    } else { ?>
                        <option value="-1">
                        </option>
                     <?php }
                    ?>
                </select>
            </div>
        </div>

        <div class="col-md-3 col-xs-12 marg-bottom" id="divCbFromMonth">
            <label class="radio-inline" >
                <input type="radio" name="radio" id="rdSelectByFromMonth"
                       class="marg-top-radio" onchange="timeOptionForFromMonth();" ><b>Từ tháng</b>
            </label><br>
            <div class="col-md-6 col-xs-5 btnChoose margin-top-8" >
                <select class="form-control" id="slMonthByFromMonth" onchange="changeShowFromMonth();"
                        onchange="{{$link}}">
                    <?php
                    $arrayMonth = \Utils\commonUtils::defaultMonth();
                    for($i=0; $i< count($arrayMonth); $i++){ ?>
                    <option value="<?php echo $arrayMonth[$i] ?>"><?php echo $arrayMonth[$i] ?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-md-6 col-xs-7 margin-left-5 margin-top-8">
                <select class="form-control" id="slYearByFromMonth" onchange="changeShowFromYear();"
                        onchange="{{$link}}">
                    <?php
                    $arrYear = $year;
                    if(count($arrYear) > 0){
                    for($i=0; $i< count($arrYear); $i++){ ?>
                    <option value="<?php echo $arrYear[$i] ?>">
                        <?php echo $arrYear[$i] ?>
                    </option>
                    <?php }
                    } else { ?>
                    <option value="-1">
                    </option>
                    <?php }
                    ?>
                </select>
            </div>
        </div>

        <div class="col-md-3 col-xs-12 marg-bottom" id="divCbToMonth" >
            <b class="font-13">Đến tháng</b><br>
            <div class="col-md-6 col-xs-5 btnChoose margin-top-8" id="comboboxTM">
                <select  class="form-control margin-left8" id="slMonthByToMonth" onchange="changeShowToMonth();"
                         onchange="{{$link}}">
                    <?php
                    $arrayMonth = \Utils\commonUtils::defaultMonth();
                    for($i=0; $i< count($arrayMonth); $i++){ ?>
                    <option value="<?php echo $arrayMonth[$i] ?>"><?php echo $arrayMonth[$i] ?></option>
                    <?php }?>
                </select>
            </div>
            <div class="col-md-6 col-xs-7 margin-top-8"  id="divToYear">
                <select class="form-control" id="slYearByToMonth" onchange="changeShowToYear();"
                        onchange="{{$link}}">
                    <?php
                    $arrYear = $year;
                    if(count($arrYear) > 0){
                    for($i=0; $i< count($arrYear); $i++){ ?>
                    <option value="<?php echo $arrYear[$i] ?>">
                        <?php echo $arrYear[$i] ?>
                    </option>
                    <?php }
                    } else { ?>
                    <option value="-1">
                    </option>
                    <?php }
                    ?>
                </select>
            </div>
        </div>
    </div>
</div>

<script>
    function compare(a,b) {
        if (a[1] > b[1])
            return -1;
        if (a[1] < b[1])
            return 1;
        return 0;
    }

    // HANDLER EVENT FOR RADIO BUTTON "Tháng" --> 1
    function timeOptionForMonth() {
        var elem = document.getElementById("txtRadioChecked");
        elem.value = "1";
        document.getElementById("slMonthByMonth").disabled = false;
        document.getElementById("slYearByMonth").disabled = false;
        document.getElementById("slMonthByFromMonth").disabled = true;
        document.getElementById("slYearByFromMonth").disabled = true;
        document.getElementById("slMonthByToMonth").disabled = true;
        document.getElementById("slYearByToMonth").disabled = true;
        showData();
    }

    // HANDLER EVENT FOR RADIO BUTTON "T? tháng" --> 2
    function timeOptionForFromMonth() {
        var elem = document.getElementById("txtRadioChecked");
        elem.value = "2";
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
        var isCheck = document.getElementById("rdSelectByMonth").checked;
        if (isCheck) {
            document.getElementById("slMonthByMonth").disabled = false;
            document.getElementById("slYearByMonth").disabled = false;

            document.getElementById("slMonthByFromMonth").disabled = true;
            document.getElementById("slYearByFromMonth").disabled = true;
            document.getElementById("slMonthByToMonth").disabled = true;
            document.getElementById("slYearByToMonth").disabled = true;
        } else {
            document.getElementById("slMonthByMonth").disabled = true;
            document.getElementById("slYearByMonth").disabled = true;

            document.getElementById("slMonthByFromMonth").disabled = false;
            document.getElementById("slYearByFromMonth").disabled = false;
            document.getElementById("slMonthByToMonth").disabled = false;
            document.getElementById("slYearByToMonth").disabled = false;
        }

        var width = $(window).width();
        if (width < 550) {
            $("#slMonthByToMonth").addClass("margin-left-2");

            $("#slYearByMonth").addClass("width-123");
            $("#slYearByMonth").remove("width-margin");

            $("#slYearByFromMonth").addClass("width-123");
            $("#slYearByFromMonth").remove("width-margin");

            $("#slYearByToMonth").addClass("width-123");
            $("#slYearByToMonth").remove("width-margin");

            $("#divToYear").addClass("margin-left-5");
            $("#divToYear").remove("margin-left-12");
        } else {
            $("#slMonthByToMonth").addClass("margin-left-2");

            $("#slYearByMonth").remove("width-123");
            $("#slYearByMonth").addClass("width-margin");

            $("#slYearByFromMonth").remove("width-123");
            $("#slYearByFromMonth").addClass("width-margin");

            $("#slYearByToMonth").remove("width-123");
            $("#slYearByToMonth").addClass("width-margin");

            $("#divToYear").remove("margin-left-5");
            $("#divToYear").addClass("margin-left-12");
        }

    });

</script>