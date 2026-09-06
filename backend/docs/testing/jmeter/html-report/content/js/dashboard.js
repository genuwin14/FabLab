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
    createTable($("#apdexTable"), {"supportsControllersDiscrimination": true, "overall": {"data": [0.6209375, 500, 1500, "Total"], "isController": false}, "titles": ["Apdex", "T (Toleration threshold)", "F (Frustration threshold)", "Label"], "items": [{"data": [0.9, 500, 1500, "GET /login"], "isController": false}, {"data": [0.79, 500, 1500, "POST /login-1"], "isController": false}, {"data": [0.5, 500, 1500, "POST /login-0"], "isController": false}, {"data": [0.613, 500, 1500, "GET /customer/cart"], "isController": false}, {"data": [0.604, 500, 1500, "GET /customer/shop"], "isController": false}, {"data": [0.6, 500, 1500, "GET /customer/orders"], "isController": false}, {"data": [0.581, 500, 1500, "GET /customer/customize"], "isController": false}, {"data": [0.67, 500, 1500, "GET / (landing)"], "isController": false}, {"data": [0.655, 500, 1500, "GET /notifications/poll"], "isController": false}, {"data": [0.32, 500, 1500, "POST /login"], "isController": false}]}, function(index, item){
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
    createTable($("#statisticsTable"), {"supportsControllersDiscrimination": true, "overall": {"data": ["Total", 3200, 0, 0.0, 754.2887500000002, 177, 2222, 700.5, 1229.7000000000003, 1373.9499999999998, 1794.8499999999967, 26.323149564845433, 3770.26861630205, 24.14454905154402], "isController": false}, "titles": ["Label", "#Samples", "FAIL", "Error %", "Average", "Min", "Max", "Median", "90th pct", "95th pct", "99th pct", "Transactions/s", "Received", "Sent"], "items": [{"data": ["GET /login", 50, 0, 0.0, 403.94000000000017, 177, 926, 343.0, 663.4, 856.6499999999996, 926.0, 4.48913629017777, 250.40086584216198, 0.8767844316753457], "isController": false}, {"data": ["POST /login-1", 50, 0, 0.0, 482.4000000000001, 231, 1048, 401.0, 802.1999999999999, 951.5499999999997, 1048.0, 4.221190375685944, 844.4812882545377, 3.8378205466441533], "isController": false}, {"data": ["POST /login-0", 50, 0, 0.0, 856.1400000000002, 448, 1581, 749.5, 1368.6, 1452.4499999999994, 1581.0, 4.09735310989101, 6.570169732852578, 4.401453536015734], "isController": false}, {"data": ["GET /customer/cart", 500, 0, 0.0, 770.9879999999999, 257, 2157, 726.5, 1256.6000000000001, 1345.0, 1667.7200000000012, 4.499923501300478, 666.7533575757337, 4.091239042686274], "isController": false}, {"data": ["GET /customer/shop", 500, 0, 0.0, 777.5299999999999, 237, 1711, 756.0, 1221.9, 1347.8, 1573.99, 4.488692982377391, 897.9973100104586, 4.08102848300132], "isController": false}, {"data": ["GET /customer/orders", 500, 0, 0.0, 799.7800000000004, 227, 2194, 765.0, 1266.8000000000002, 1403.0, 1756.7700000000002, 4.4834605141632515, 1215.2017350571866, 4.08502798800226], "isController": false}, {"data": ["GET /customer/customize", 500, 0, 0.0, 817.9200000000005, 236, 2057, 782.5, 1280.6000000000001, 1444.0, 1887.2300000000007, 4.490547397727783, 710.7827076737842, 4.104640980735551], "isController": false}, {"data": ["GET / (landing)", 500, 0, 0.0, 659.4200000000008, 200, 1942, 598.5, 1085.9000000000003, 1236.5, 1506.91, 4.488410922996822, 412.99088312570694, 4.0237902610459795], "isController": false}, {"data": ["GET /notifications/poll", 500, 0, 0.0, 693.6519999999995, 201, 2048, 639.5, 1139.7, 1325.95, 1874.4100000000005, 4.495553897195673, 5.382442389476807, 4.1092172341554205], "isController": false}, {"data": ["POST /login", 50, 0, 0.0, 1339.0999999999997, 698, 2222, 1289.5, 1965.0, 2102.149999999999, 2222.0, 3.971721344030503, 800.9418254527762, 7.877505907935499], "isController": false}]}, function(index, item){
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
