/*
   Licensed to the Apache Software Foundation (ASF) under one or more
   contributor license agreements.  See the NOTICE file distributed with
   this work for additional information regarding copyright ownership.
   The ASF licenses this file to You under the Apache License, Version 2.0
   (the "License"); you may not use this file except in compliance with
   the License.  You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/
var showControllersOnly = false;
var seriesFilter = "";
var filtersOnlySampleSeries = true;

/*
 * Add header in statistics table to group metrics by category
 * format
 *
 */
function summaryTableHeader(header) {
    var newRow = header.insertRow(-1);
    newRow.className = "tablesorter-no-sort";
    var cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 1;
    cell.innerHTML = "Requests";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 3;
    cell.innerHTML = "Executions";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 7;
    cell.innerHTML = "Response Times (ms)";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 1;
    cell.innerHTML = "Throughput";
    newRow.appendChild(cell);

    cell = document.createElement('th');
    cell.setAttribute("data-sorter", false);
    cell.colSpan = 2;
    cell.innerHTML = "Network (KB/sec)";
    newRow.appendChild(cell);
}

/*
 * Populates the table identified by id parameter with the specified data and
 * format
 *
 */
function createTable(table, info, formatter, defaultSorts, seriesIndex, headerCreator) {
    var tableRef = table[0];

    // Create header and populate it with data.titles array
    var header = tableRef.createTHead();

    // Call callback is available
    if(headerCreator) {
        headerCreator(header);
    }

    var newRow = header.insertRow(-1);
    for (var index = 0; index < info.titles.length; index++) {
        var cell = document.createElement('th');
        cell.innerHTML = info.titles[index];
        newRow.appendChild(cell);
    }

    var tBody;

    // Create overall body if defined
    if(info.overall){
        tBody = document.createElement('tbody');
        tBody.className = "tablesorter-no-sort";
        tableRef.appendChild(tBody);
        var newRow = tBody.insertRow(-1);
        var data = info.overall.data;
        for(var index=0;index < data.length; index++){
            var cell = newRow.insertCell(-1);
            cell.innerHTML = formatter ? formatter(index, data[index]): data[index];
        }
    }

    // Create regular body
    tBody = document.createElement('tbody');
    tableRef.appendChild(tBody);

    var regexp;
    if(seriesFilter) {
        regexp = new RegExp(seriesFilter, 'i');
    }
    // Populate body with data.items array
    for(var index=0; index < info.items.length; index++){
        var item = info.items[index];
        if((!regexp || filtersOnlySampleSeries && !info.supportsControllersDiscrimination || regexp.test(item.data[seriesIndex]))
                &&
                (!showControllersOnly || !info.supportsControllersDiscrimination || item.isController)){
            if(item.data.length > 0) {
                var newRow = tBody.insertRow(-1);
                for(var col=0; col < item.data.length; col++){
                    var cell = newRow.insertCell(-1);
                    cell.innerHTML = formatter ? formatter(col, item.data[col]) : item.data[col];
                }
            }
        }
    }

    // Add support of columns sort
    table.tablesorter({sortList : defaultSorts});
}

$(document).ready(function() {

    // Customize table sorter default options
    $.extend( $.tablesorter.defaults, {
        theme: 'blue',
        cssInfoBlock: "tablesorter-no-sort",
        widthFixed: true,
        widgets: ['zebra']
    });

    var data = {"OkPercent": 100.0, "KoPercent": 0.0};
    var dataset = [
        {
            "label" : "FAIL",
            "data" : data.KoPercent,
            "color" : "#FF6347"
        },
        {
            "label" : "PASS",
            "data" : data.OkPercent,
            "color" : "#9ACD32"
        }];
    $.plot($("#flot-requests-summary"), dataset, {
        series : {
            pie : {
                show : true,
                radius : 1,
                label : {
                    show : true,
                    radius : 3 / 4,
                    formatter : function(label, series) {
                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'
                            + label
                            + '<br/>'
                            + Math.round10(series.percent, -2)
                            + '%</div>';
                    },
                    background : {
                        opacity : 0.5,
                        color : '#000'
                    }
                }
            }
        },
        legend : {
            show : true
        }
    });

    // Creates APDEX table
    createTable($("#apdexTable"), {"supportsControllersDiscrimination": true, "overall": {"data": [0.7321875, 500, 1500, "Total"], "isController": false}, "titles": ["Apdex", "T (Toleration threshold)", "F (Frustration threshold)", "Label"], "items": [{"data": [0.83, 500, 1500, "GET /login"], "isController": false}, {"data": [0.76, 500, 1500, "POST /login-1"], "isController": false}, {"data": [0.57, 500, 1500, "POST /login-0"], "isController": false}, {"data": [0.717, 500, 1500, "GET /customer/cart"], "isController": false}, {"data": [0.695, 500, 1500, "GET /customer/shop"], "isController": false}, {"data": [0.698, 500, 1500, "GET /customer/orders"], "isController": false}, {"data": [0.711, 500, 1500, "GET /customer/customize"], "isController": false}, {"data": [0.815, 500, 1500, "GET / (landing)"], "isController": false}, {"data": [0.804, 500, 1500, "GET /notifications/poll"], "isController": false}, {"data": [0.3, 500, 1500, "POST /login"], "isController": false}]}, function(index, item){
        switch(index){
            case 0:
                item = item.toFixed(3);
                break;
            case 1:
            case 2:
                item = formatDuration(item);
                break;
        }
        return item;
    }, [[0, 0]], 3);

    // Create statistics table
    createTable($("#statisticsTable"), {"supportsControllersDiscrimination": true, "overall": {"data": ["Total", 3200, 0, 0.0, 576.9290625000011, 152, 2847, 519.0, 922.9000000000001, 1064.9499999999998, 1343.9899999999998, 29.89983555090447, 4928.953399179156, 27.425215372252953], "isController": false}, "titles": ["Label", "#Samples", "FAIL", "Error %", "Average", "Min", "Max", "Median", "90th pct", "95th pct", "99th pct", "Transactions/s", "Received", "Sent"], "items": [{"data": ["GET /login", 50, 0, 0.0, 485.21999999999997, 199, 1317, 367.5, 1038.3, 1209.05, 1317.0, 4.603203829865586, 303.8069574548886, 0.8990632480206223], "isController": false}, {"data": ["POST /login-1", 50, 0, 0.0, 557.4, 240, 1536, 459.0, 1074.3, 1180.1499999999999, 1536.0, 4.203800235412813, 996.1569712144778, 3.822009784345048], "isController": false}, {"data": ["POST /login-0", 50, 0, 0.0, 870.1600000000002, 417, 1852, 669.5, 1485.6, 1595.3499999999997, 1852.0, 4.193927193423922, 6.725027784767656, 4.505195227310853], "isController": false}, {"data": ["GET /customer/cart", 500, 0, 0.0, 588.0220000000003, 199, 1360, 553.5, 912.5000000000002, 1028.0, 1168.97, 5.145568122176369, 859.1491979988886, 4.678246017330273], "isController": false}, {"data": ["GET /customer/shop", 500, 0, 0.0, 623.7699999999996, 211, 1282, 574.0, 970.0, 1097.85, 1219.94, 5.16288915276989, 1223.4283338550003, 4.693993946512468], "isController": false}, {"data": ["GET /customer/orders", 500, 0, 0.0, 607.8199999999997, 212, 1275, 582.5, 936.9000000000001, 1058.0, 1197.94, 5.129783523135324, 1640.158149622961, 4.673914088950446], "isController": false}, {"data": ["GET /customer/customize", 500, 0, 0.0, 594.8820000000003, 194, 1424, 569.0, 956.6000000000001, 1071.85, 1290.3600000000006, 5.103498958886212, 885.7262689690677, 4.664917017106929], "isController": false}, {"data": ["GET / (landing)", 500, 0, 0.0, 464.86599999999993, 188, 1103, 429.0, 702.8000000000001, 780.4999999999999, 991.8300000000002, 5.143186306780777, 526.0616902696058, 4.610786161742923], "isController": false}, {"data": ["GET /notifications/poll", 500, 0, 0.0, 478.89399999999983, 152, 1128, 446.0, 767.2000000000003, 836.0, 1047.92, 5.129994049206903, 6.237231124186897, 4.689135185603185], "isController": false}, {"data": ["POST /login", 50, 0, 0.0, 1428.1399999999999, 659, 2847, 1180.0, 2383.6, 2530.4999999999995, 2847.0, 4.060419035244437, 968.6914728662498, 8.053428770099075], "isController": false}]}, function(index, item){
        switch(index){
            // Errors pct
            case 3:
                item = item.toFixed(2) + '%';
                break;
            // Mean
            case 4:
            // Mean
            case 7:
            // Median
            case 8:
            // Percentile 1
            case 9:
            // Percentile 2
            case 10:
            // Percentile 3
            case 11:
            // Throughput
            case 12:
            // Kbytes/s
            case 13:
            // Sent Kbytes/s
                item = item.toFixed(2);
                break;
        }
        return item;
    }, [[0, 0]], 0, summaryTableHeader);

    // Create error table
    createTable($("#errorsTable"), {"supportsControllersDiscrimination": false, "titles": ["Type of error", "Number of errors", "% in errors", "% in all samples"], "items": []}, function(index, item){
        switch(index){
            case 2:
            case 3:
                item = item.toFixed(2) + '%';
                break;
        }
        return item;
    }, [[1, 1]]);

        // Create top5 errors by sampler
    createTable($("#top5ErrorsBySamplerTable"), {"supportsControllersDiscrimination": false, "overall": {"data": ["Total", 3200, 0, "", "", "", "", "", "", "", "", "", ""], "isController": false}, "titles": ["Sample", "#Samples", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors"], "items": [{"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}]}, function(index, item){
        return item;
    }, [[0, 0]], 0);

});
