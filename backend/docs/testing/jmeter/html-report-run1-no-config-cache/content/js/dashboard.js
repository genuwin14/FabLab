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

    var data = {"OkPercent": 95.2130945027795, "KoPercent": 4.7869054972205065};
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
    createTable($("#apdexTable"), {"supportsControllersDiscrimination": true, "overall": {"data": [0.34651019147621986, 500, 1500, "Total"], "isController": false}, "titles": ["Apdex", "T (Toleration threshold)", "F (Frustration threshold)", "Label"], "items": [{"data": [0.5, 500, 1500, "GET /customer/shop-1"], "isController": false}, {"data": [0.4, 500, 1500, "GET /customer/customize-0"], "isController": false}, {"data": [0.322, 500, 1500, "GET /customer/cart"], "isController": false}, {"data": [0.2, 500, 1500, "GET /customer/customize-1"], "isController": false}, {"data": [0.0, 500, 1500, "GET /customer/shop-0"], "isController": false}, {"data": [0.4, 500, 1500, "GET /customer/orders-0"], "isController": false}, {"data": [0.4, 500, 1500, "GET /customer/orders-1"], "isController": false}, {"data": [0.42857142857142855, 500, 1500, "GET /customer/cart-1"], "isController": false}, {"data": [0.287, 500, 1500, "GET /customer/orders"], "isController": false}, {"data": [0.388, 500, 1500, "GET / (landing)"], "isController": false}, {"data": [0.5, 500, 1500, "GET /notifications/poll-1"], "isController": false}, {"data": [0.18, 500, 1500, "POST /login"], "isController": false}, {"data": [0.5, 500, 1500, "GET /notifications/poll-0"], "isController": false}, {"data": [0.74, 500, 1500, "GET /login"], "isController": false}, {"data": [0.72, 500, 1500, "POST /login-1"], "isController": false}, {"data": [0.43, 500, 1500, "POST /login-0"], "isController": false}, {"data": [0.313, 500, 1500, "GET /customer/shop"], "isController": false}, {"data": [0.42857142857142855, 500, 1500, "GET /customer/cart-0"], "isController": false}, {"data": [0.313, 500, 1500, "GET /customer/customize"], "isController": false}, {"data": [0.385, 500, 1500, "GET /notifications/poll"], "isController": false}]}, function(index, item){
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
    createTable($("#statisticsTable"), {"supportsControllersDiscrimination": true, "overall": {"data": ["Total", 3238, 155, 4.7869054972205065, 1337.548795552811, 241, 42275, 1301.0, 1995.2999999999997, 2368.199999999999, 3305.2200000000003, 19.02691268069103, 2609.7335185997913, 17.55036831406452], "isController": false}, "titles": ["Label", "#Samples", "FAIL", "Error %", "Average", "Min", "Max", "Median", "90th pct", "95th pct", "99th pct", "Transactions/s", "Received", "Sent"], "items": [{"data": ["GET /customer/shop-1", 1, 0, 0.0, 684.0, 684, 684, 684.0, 684.0, 684.0, 684.0, 1.461988304093567, 81.54725191885964, 1.3177882858187133], "isController": false}, {"data": ["GET /customer/customize-0", 5, 0, 0.0, 1328.8, 1170, 1639, 1304.0, 1639.0, 1639.0, 1639.0, 0.07548195226521338, 0.11808797610241391, 0.06899522199242161], "isController": false}, {"data": ["GET /customer/cart", 500, 28, 5.6, 1474.660000000001, 330, 42275, 1346.0, 2074.9, 2508.0999999999995, 3513.7000000000003, 3.119346185039616, 445.80254197470214, 2.875409657885707], "isController": false}, {"data": ["GET /customer/customize-1", 5, 0, 0.0, 1506.2, 1207, 1733, 1623.0, 1733.0, 1733.0, 1733.0, 0.07498162950077232, 4.182363992696789, 0.06758598049727815], "isController": false}, {"data": ["GET /customer/shop-0", 1, 0, 0.0, 1968.0, 1968, 1968, 1968.0, 1968.0, 1968.0, 1968.0, 0.508130081300813, 0.794945693597561, 0.46198154852642276], "isController": false}, {"data": ["GET /customer/orders-0", 5, 0, 0.0, 1244.4, 781, 1670, 1198.0, 1670.0, 1670.0, 1670.0, 0.050507601394009796, 0.07902663960301025, 0.04601913291075307], "isController": false}, {"data": ["GET /customer/orders-1", 5, 0, 0.0, 1202.6, 755, 1575, 1226.0, 1575.0, 1575.0, 1575.0, 0.05055611729019211, 2.819935303968655, 0.045569625252780584], "isController": false}, {"data": ["GET /customer/cart-1", 7, 0, 0.0, 1183.142857142857, 767, 1557, 1244.0, 1557.0, 1557.0, 1557.0, 0.07411564157675733, 4.134045996034813, 0.06680540739779982], "isController": false}, {"data": ["GET /customer/orders", 500, 16, 3.2, 1433.4679999999996, 360, 3768, 1446.0, 2058.8, 2433.5499999999997, 3351.4100000000008, 3.128519584532599, 815.469570014313, 2.878696297006007], "isController": false}, {"data": ["GET / (landing)", 500, 23, 4.6, 1229.051999999998, 304, 3392, 1228.5, 1903.9, 2125.0, 2925.8100000000004, 3.1154783193863755, 275.38468003453505, 2.792977633981145], "isController": false}, {"data": ["GET /notifications/poll-1", 1, 0, 0.0, 1208.0, 1208, 1208, 1208.0, 1208.0, 1208.0, 1208.0, 0.8278145695364238, 46.17410621895696, 0.7461648903145696], "isController": false}, {"data": ["POST /login", 50, 0, 0.0, 1726.2, 1015, 2828, 1687.0, 2500.6, 2609.85, 2828.0, 3.8358266206367473, 773.5371415899501, 7.60797252589183], "isController": false}, {"data": ["GET /notifications/poll-0", 1, 0, 0.0, 1220.0, 1220, 1220, 1220.0, 1220.0, 1220.0, 1220.0, 0.819672131147541, 1.2823386270491803, 0.7492315573770492], "isController": false}, {"data": ["GET /login", 50, 0, 0.0, 582.9799999999999, 241, 1366, 514.5, 1020.1, 1203.849999999999, 1366.0, 4.854840275754928, 270.799577022041, 0.9482109913583844], "isController": false}, {"data": ["POST /login-1", 50, 0, 0.0, 594.1599999999999, 348, 1069, 569.0, 956.0, 1043.4999999999998, 1069.0, 4.0859687831984965, 817.4291786691999, 3.714879821443164], "isController": false}, {"data": ["POST /login-0", 50, 0, 0.0, 1131.3399999999995, 609, 2037, 1088.0, 1695.1, 1936.4499999999996, 2037.0, 4.140101018464851, 6.638716672186801, 4.447374140929039], "isController": false}, {"data": ["GET /customer/shop", 500, 22, 4.4, 1396.007999999999, 276, 7823, 1347.0, 2084.7000000000003, 2476.0, 3430.5800000000004, 3.101717731279583, 605.2214122431003, 2.8256103308137046], "isController": false}, {"data": ["GET /customer/cart-0", 7, 0, 0.0, 1188.857142857143, 928, 1886, 1045.0, 1886.0, 1886.0, 1886.0, 0.07405683332980682, 0.11586877591989166, 0.06733096858403334], "isController": false}, {"data": ["GET /customer/customize", 500, 34, 6.8, 1406.541999999999, 295, 9629, 1354.0, 2099.1000000000004, 2706.95, 3381.75, 3.1319293937837465, 472.8768858619227, 2.8910093953966904], "isController": false}, {"data": ["GET /notifications/poll", 500, 32, 6.4, 1222.5800000000002, 265, 3649, 1215.5, 1867.4, 2172.8, 3023.020000000001, 3.1581606872157653, 6.584185214281203, 2.8924495779907784], "isController": false}]}, function(index, item){
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
    createTable($("#errorsTable"), {"supportsControllersDiscrimination": false, "titles": ["Type of error", "Number of errors", "% in errors", "% in all samples"], "items": [{"data": ["500/Internal Server Error", 155, 100.0, 4.7869054972205065], "isController": false}]}, function(index, item){
        switch(index){
            case 2:
            case 3:
                item = item.toFixed(2) + '%';
                break;
        }
        return item;
    }, [[1, 1]]);

        // Create top5 errors by sampler
    createTable($("#top5ErrorsBySamplerTable"), {"supportsControllersDiscrimination": false, "overall": {"data": ["Total", 3238, 155, "500/Internal Server Error", 155, "", "", "", "", "", "", "", ""], "isController": false}, "titles": ["Sample", "#Samples", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors", "Error", "#Errors"], "items": [{"data": [], "isController": false}, {"data": [], "isController": false}, {"data": ["GET /customer/cart", 500, 28, "500/Internal Server Error", 28, "", "", "", "", "", "", "", ""], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": ["GET /customer/orders", 500, 16, "500/Internal Server Error", 16, "", "", "", "", "", "", "", ""], "isController": false}, {"data": ["GET / (landing)", 500, 23, "500/Internal Server Error", 23, "", "", "", "", "", "", "", ""], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": [], "isController": false}, {"data": ["GET /customer/shop", 500, 22, "500/Internal Server Error", 22, "", "", "", "", "", "", "", ""], "isController": false}, {"data": [], "isController": false}, {"data": ["GET /customer/customize", 500, 34, "500/Internal Server Error", 34, "", "", "", "", "", "", "", ""], "isController": false}, {"data": ["GET /notifications/poll", 500, 32, "500/Internal Server Error", 32, "", "", "", "", "", "", "", ""], "isController": false}]}, function(index, item){
        return item;
    }, [[0, 0]], 0);

});
