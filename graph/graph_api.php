<html debug="true">
<head>
<title>Shanks monitor.</title>
<meta http-equiv="Content-Type" content="image/png" />
<script type="text/javascript" src="../exporting-server/phantomjs/jquery.1.9.1.min.js" ></script>
<script type="text/javascript" src="../exporting-server/phantomjs/highcharts.js" ></script>
<script type="text/javascript" src="../js/exporting.js" ></script>
</head>
<body>
<?php
//if endtime is null,endtime=now.
$end=$_GET['end'];
if (strlen($end)>0){
  $end=$end;
} else {
  $end=time();
}
$period=$_GET['period'];
$begin=$end-$period;
$itemids=$_GET['itemids'];

//reset itemids to arr.
$itemids_arr = explode(",",$itemids);
$itemids_num = count($itemids_arr);
$itemids_json = json_encode($itemids_arr);
//connection for zabbix mysql database;
//from itemid,get the item_name.
include 'config.php';
$con = mysql_connect("$dbhost","$dbuser","$dbpass");
if (!$con) {
  die('Could not connect: ' . mysql_error());
}
mysql_select_db("$dbname", $con);
$item_names='target';
for ($l=0;$l<$itemids_num;$l++)
{
  $select_item_name = mysql_query("select itemid,key_ from items where itemid=$itemids_arr[$l]");
  while($value_item_name = mysql_fetch_array($select_item_name))
  {
    $item_name = $value_item_name['key_'];
  }
  $item_names = $item_names . "==,==" . $item_name;
}
$item_names = substr("$item_names",strpos($item_names,"target==,==")+11);
$item_name_arr = explode("==,==",$item_names);
$item_name_json = json_encode($item_name_arr);
mysql_close($con);
echo <<<eot
<script type="text/javascript">
    var itemids_json = $itemids_json;
    var itemids_num = $itemids_num;
    var period = $period;
    var end = $end;
    var item_name_json = $item_name_json;
    var series_value=[];
    var jquery_data = "";
    var myarray=[];
    //var xval=['a','b','c'];
    for (var i=0;i<itemids_num;i++){
        var myhash = { }
        myhash["name"]=item_name_json[i];
        myhash["data_"+i]=myarray;
        series_value.push(myhash);
        itemids_id=itemids_json[i];
        jquery_data+='jQuery.getJSON("api/data.php?itemid='+itemids_id+'&period='+period+'&end='+end+'",null,function(data_'+i+'){chart.series['+i+'].setData(data_'+i+');});'
    }
    //Do not enable UTC, otherwise it will be behind the eight hours time display.
    Highcharts.setOptions({ global: { useUTC: false } });

(function($){
$(function () {
        var chart = new Highcharts.Chart({
        //API HELP : http://api.highcharts.com/highcharts
            chart: {
                type: 'spline', //Using spline, draw the curve is very mellow.
                renderTo: 'container',  //Define a div name.
                margin: [ 50, 50, 100, 50],
                zoomType: 'x', //Mouse ring box, get a smaller time scale data.
                events:{
                    load:getForm  //Data obtained using the function.
                }
            },
            credits:{ //Lower right corner of the authorization.
                enabled: true,
                position: { //The coordinates define authorization.
                    align: 'right',
                    x: -10,
                    y: -1
                },
                href: "http://shanks.blog.51cto.com/",
                style: {
                    color:'blue'
                },
                text: "Zabbix of Shanks"
            },
            title: { //Main title.
                floating: true,
                style:{
                    color:'#3e576a',
                    fontSize:'27px'
                },
                text: 'shanks.com',
            },
            subtitle: {
                floating: true,
                style:{
                    color:'red',
                    fontSize:'14px'
                },
                text: '',
            },
            exporting: {  //Printing and Exporting.
                enabled:true,
                buttons:{
                    printButton:{
                        width:50,
                        symbolSize:20,
                        borderWidth:2,
                        borderRadius:0,
                        hoverBorderColor:'red',
                        height:30,
                        symbolX:25,
                        symbolY:15,
                        x:-200,
                        y:20
                    },
                    exportButton:{
                        width:50,
                        symbolSize:20,
                        borderWidth:2,
                        borderRadius:0,
                        hoverBorderColor:'red',
                        height:30,
                        symbolX:25,
                        symbolY:15,
                        x:-150,
                        y:20
                    },
                },
                filename:'DangDang-monitor',
                type:'image/png',
                width:1600,
                height:1200
            },
            xAxis: {  //Define the x coordinate.
                type: 'datetime',
                tickPixelInterval:180,  //x-coordinate of the interval.
                dateTimeLabelFormats: {
                    second: '%H:%M:%S',
                    minute: '%m-%d %H:%M',
                    hour: '%Y-%m-%d %H',
                    day: '%Y-%m-%d',
                    week: '%e. %b',
                    month: '%Y-%m',
                    year: '%Y'
                },
                labels:{
                  //rotation:90
                  rotation:0
                }
            },
            yAxis: [{ //Define the y coordinate.
                min: 0,
                title: {
                    text: 'value'
                }
            }],
            legend: { //Additional description box below.
                enabled: 'true',
                layout: 'horizontal',
                floating: 'true',
                align: 'center',
                borderColor: '#0F0F0F',
                y:11,
                maxHeight:66,
                width:1127,
                borderWidth: 2
            },
            tooltip: {  //Mouse pointer to the curve, the message format.
                formatter: function() {
                    return '<b>'+ this.series.name +'</b><br/>'+
                    Highcharts.dateFormat('[%b-%e %H:%M]', this.x) +': '+ this.y;
                }
            },
            plotOptions: {  //Detailed control to draw the image.
                enabled: 'true',
                spline:{
                    allowPointSelect :true,
                    marker:{
                        enabled:false,
                    }
                }
            },
            series: series_value  //json data,just as the value.
        });
        function getForm(){
            eval(jquery_data)   //get data from api/data.php.
        }
        $(document).ready(function(){ //Get json data once per minute.
            window.setInterval(getForm,60000);
        });
    });
})(jQuery);
</script>
eot;
?>
<div id="container" style="min-width: 800px; height: 500px; margin: 0 auto"></div>
</body>
</html>
