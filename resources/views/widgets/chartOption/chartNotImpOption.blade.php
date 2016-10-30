<div class="col-md-12 col-xs-12 margin-top-chart">
    <div class="col-md-1 col-xs-12"><b><u>Biểu đồ:</u></b></div>
    <div class="col-md-4 col-xs-12">
        <input type="radio" name="charts" id="rdColumnChart" onchange="viewColumn();" checked ><label class="padding-left-1-10">Biểu đồ cột</label>
    </div>
    <div class="col-md-3 col-xs-12">
        <input type="radio" name="charts" id="rdLineChart" onchange="viewLine();"><label class="padding-left-1-10">Biểu đồ đường</label>
    </div>
    <div class="col-md-4 col-xs-12">
        <input type="radio" name="charts" id="rdRadarChart" onchange="viewRadar();"><label class="padding-left-1-10">Biểu đồ Radar</label>
    </div>
    <div class="col-md-5"></div>
</div>
<div class="col-md-12 col-xs-12 marg-top-title">
    <div class="col-md-1 col-xs-12"><b><u>Kiểu xem:</u></b></div>
    <div class="col-md-4 col-xs-12" id="divRdMIP">
        <input type="radio" name="view" id="rdMIP" onchange="viewByMIP();"checked><label class="padding-left-1-10">Tháng - Điểm thực hiện - Điểm chuẩn</label>
    </div>
    <div class="col-md-4">
        <input type="radio" name="view" id="rdGBMIP" onchange="viewByGBMIP();"><label class="padding-left-1-10">Mục tiêu - Điểm thực hiện - Điểm chuẩn</label>
    </div>
</div>

<script>
    // change option to view chart
    function viewLine() {
        showData();
    }

    function viewColumn() {
        showData();
    }

    function viewByMIP() {
        showData();
    }

    function viewRadar() {
        showData();
    }


    function viewByGBMIP() {
        showData();
    }

    function strToJson(str) {
        eval("var x = " + str + ";");
        return JSON.stringify(x);
    }

    // paint chart Radar
    function paintChartIPByMonthGoal(monthSt, ipSt, emp) {
        var radarChartData = '{labels: [';
        radarChartData += monthSt;
        radarChartData += '],datasets: [{ label:"Điểm thực hiện",' +
                'fillColor:"rgba(101,203,236,0.2)", ' +
                'strokeColor:"rgba(101,203,236,1)",' +
                'pointHighlightFill:"#fff", ' +
                'pointHighlightStroke:"rgba(101,203,236,1)",' +
                ' pointColor:"rgba(101,203,236,1)",' +
                ' pointStrokeColor:"#fff",data:[' + ipSt + ' ],},]}';
        var obj = strToJson(radarChartData);
        var objJson = JSON.parse(obj);
        var companyRadar = document.getElementById(emp).getContext("2d");
        var width= $( window ).width();
        if(width < 550){
            new Chart(companyRadar).Radar(objJson, {
                customTooltips: false,
                responsive: true,
                pointLabelFontSize: 6.5,
                multiTooltipTemplate: "<%= datasetLabel %>: <%= formatBigNumber(value) %>"
            });
        } else {
            new Chart(companyRadar).Radar(objJson, {
                customTooltips: false,
                responsive: true,
                multiTooltipTemplate: "<%= datasetLabel %>: <%= formatBigNumber(value) %>"
            });
        }
    }
    function paintChartTVIByMonthGoal(monthSt, tv, i, emp, labelTV, labelI) {
        var radarChartData = '{labels: [';
        radarChartData += monthSt;
        radarChartData += '],datasets: [{' +
                'label:"'+labelTV+'",' +
                'fillColor:"rgba(101,203,236,0.2)",' +
                'strokeColor:"rgba(101,203,236,1)",' +
                'pointHighlightFill:"#fff",' +
                'pointHighlightStroke:"rgba(101,203,236,1)",' +
                'pointColor:"rgba(101,203,236,1)",' +
                'pointStrokeColor:"#fff",' +
                'data:[' + tv + '],' +
                '},{' +
                'label:"'+labelI+'",' +
                'fillColor:"rgba(114,102,186,0.2)",' +
                'strokeColor:"rgba(114,102,186,1)",' +
                'pointHighlightFill:"#fff",' +
                'pointHighlightStroke:"rgba(151,187,205,1)",' +
                'pointColor:"rgba(114,102,186,1)",' +
                'pointStrokeColor:"#fff",' +
                'data:[' + i + '],},]}';
        var obj = strToJson(radarChartData);
        var objJson = JSON.parse(obj);
        var companyRadar = document.getElementById(emp).getContext("2d");
        var width= $( window ).width();
        if(width < 550){
            new Chart(companyRadar).Radar(objJson, {
                customTooltips: false,
                responsive: true,
                pointLabelFontSize: 6.5,
                multiTooltipTemplate: "<%= datasetLabel %>: <%= formatBigNumber(value) %>"
            });
        } else {
            new Chart(companyRadar).Radar(objJson, {
                customTooltips: false,
                responsive: true,
                multiTooltipTemplate: "<%= datasetLabel %>: <%= formatBigNumber(value) %>"
            });
        }
    }

    //paint chart Line
    function paintLineChartIPByMonthGoal(monthSt, ipSt, emp) {
        var radarChartData = '{labels: [';
        radarChartData += monthSt;
        radarChartData += '],datasets: [{ label:"Điểm thực hiện",' +
                'fillColor:"rgba(101,203,236,0.2)", ' +
                'strokeColor:"rgba(101,203,236,1)",' +
                'pointHighlightFill:"#fff", ' +
                'pointHighlightStroke:"rgba(101,203,236,1)",' +
                ' pointColor:"rgba(101,203,236,1)",' +
                ' pointStrokeColor:"#fff",data:[' + ipSt + ' ],},]}';
        var obj = strToJson(radarChartData);
        var objJson = JSON.parse(obj);
        var companyRadar = document.getElementById(emp).getContext("2d");
        var width= $( window ).width();
        if(width < 550){
            new Chart(companyRadar).Line(objJson, {
                customTooltips: false,
                responsive: true,
                pointLabelFontSize: 6.5,
                multiTooltipTemplate: "<%= datasetLabel %>: <%= formatBigNumber(value) %>"
            });
        } else {
            new Chart(companyRadar).Line(objJson, {
                customTooltips: false,
                responsive: true,
                multiTooltipTemplate: "<%= datasetLabel %>: <%= formatBigNumber(value) %>"
            });
        }
    }
    function paintLineChartTVIByMonthGoal(monthSt, tv, i, emp, labelTV, labelI) {
        var radarChartData = '{labels: [';
        radarChartData += monthSt;
        radarChartData += '],datasets: [{' +
                'label:"'+labelTV+'",' +
                'fillColor:"rgba(101,203,236,0.2)",' +
                'strokeColor:"rgba(101,203,236,1)",' +
                'pointHighlightFill:"#fff",' +
                'pointHighlightStroke:"rgba(101,203,236,1)",' +
                'pointColor:"rgba(101,203,236,1)",' +
                'pointStrokeColor:"#fff",' +
                'data:[' + tv + '],' +
                '},{' +
                'label:"'+labelI+'",' +
                'fillColor:"rgba(114,102,186,0.2)",' +
                'strokeColor:"rgba(114,102,186,1)",' +
                'pointHighlightFill:"#fff",' +
                'pointHighlightStroke:"rgba(151,187,205,1)",' +
                'pointColor:"rgba(114,102,186,1)",' +
                'pointStrokeColor:"#fff",' +
                'data:[' + i + '],},]}';
        var obj = strToJson(radarChartData);
        var objJson = JSON.parse(obj);
        var companyRadar = document.getElementById(emp).getContext("2d");
        var width= $( window ).width();
        if(width < 550){
            new Chart(companyRadar).Line(objJson, {
                customTooltips: false,
                responsive: true,
                pointLabelFontSize: 6.5,
                multiTooltipTemplate: "<%= datasetLabel %>: <%= formatBigNumber(value) %>"
            });
        } else {
            new Chart(companyRadar).Line(objJson, {
                customTooltips: false,
                responsive: true,
                multiTooltipTemplate: "<%= datasetLabel %>: <%= formatBigNumber(value) %>"
            });
        }
    }

    // Column chart
    function paintChartColumnIPBM(data,emp, labelIP, labelBM, title){
        var node = document.getElementById("idTable");
        while (node.firstChild) {
            node.removeChild(node.firstChild);
        }
        var html = ' <table id="datatable">'+
                '<thead>'+
                '<tr>'+
                '<th></th>'+
                '<th>'+labelIP+'</th>'+
                '<th>'+labelBM+'</th>'+
                '</tr>'+
                '</thead>'+
                '<tbody>';
        for(var d = 0; d<data.length; d++){
            html += '<tr>' +
                    '<td>'+data[d][0]+'</td>' +
                    '<td>'+formatNumber(data[d][1], 3)+'</td>' +
                    '<td>'+formatNumber(data[d][2], 3)+'</td>' +
                    '</tr>';
        }
        html += '</tbody>' +
                '</table>';

        $('#idTable').append(html);

            $(function () {
                var chart = new Highcharts.Chart({
                    data: {
                        table: 'datatable'
                    },
                    chart: {
                        type: 'column',
                        renderTo: emp
                    },
                    title: {
                        text: title
                    },
                    yAxis: {
                        allowDecimals: false,
                        title: {
                            text: ''
                        }
                    },
                    credits: {
                        enabled: false
                    },
                    tooltip: {
                        formatter: function () {
                            return '<b>' + this.series.name + ': '+ formatBigNumber(this.point.y) + '</b><br/>';
                        }
                    }
                });
            });
    }

</script>