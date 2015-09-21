<html debug="true">
<head>
<title>Tea</title>
<link href="../css/bootstrap-combined.min.css" rel="stylesheet">
  <script type="text/javascript" src="http://cdn.hcharts.cn/jquery/jquery-1.8.3.min.js"></script>
  <script type="text/javascript" src="http://cdn.hcharts.cn/highcharts/highcharts.js"></script>
  <script type="text/javascript" src="http://cdn.hcharts.cn/highcharts/exporting.js"></script>
  <link rel="stylesheet" type="text/css" href="../css/jquery-ui.css" />
<style type="text/css">
a{color:#007bc4/*#424242*/; text-decoration:none;}
a:hover{text-decoration:underline}
ol,ul{list-style:none}
body{height:100%; font:12px/18px Tahoma, Helvetica, Arial, Verdana, "\5b8b\4f53", sans-serif; color:#51555C;}
img{border:none}
.demo{width:500px; margin:20px auto}
.demo h4{height:32px; line-height:32px; font-size:14px}
.demo h4 span{font-weight:500; font-size:12px}
.demo p{line-height:28px;}
input{width:200px; height:20px; line-height:20px; padding:2px; border:1px solid #d3d3d3}
pre{padding:6px 0 0 0; color:#666; line-height:20px; background:#f7f7f7}

.ui-timepicker-div .ui-widget-header { margin-bottom: 8px;}
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.ui_tpicker_hour_label,.ui_tpicker_minute_label,.ui_tpicker_second_label,.ui_tpicker_millisec_label,.ui_tpicker_time_label{padding-left:20px}
</style>
  <script type="text/javascript" src="../js/jquery-ui.js"></script>
  <script type="text/javascript" src="../js/jquery-ui-slide.min.js"></script>
  <script type="text/javascript" src="../js/timepicker/jquery-ui-timepicker-addon.js"></script>
  <script type="text/javascript">
$(function(){
        $('#begintime').datetimepicker({showSecond:true,dateFormat:'yy-mm-dd',timeFormat:'hh:mm:ss'});
});
$(function(){
        $('#endtime').datetimepicker({showSecond:true,dateFormat:'yy-mm-dd',timeFormat:'hh:mm:ss'});
});
function datetime_to_unix(datetime){
    var tmp_datetime = datetime.replace(/:/g,'-');
    tmp_datetime = tmp_datetime.replace(/ /g,'-');
    var arr = tmp_datetime.split("-");
    var now = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3]-8,arr[4],arr[5]));
    return parseInt(now.getTime()/1000);
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span12">
			<div class="page-header">
				<h1>
					<p class="text-center"><font color="#64A600">Tea 监控系统</font></p>
				</h1>
			</div>
		</div>
	</div>
</div>
<div class="demo" style="min-width: 1000px; margin: 0 auto">
	<button type="submit" onclick="goback()" class="btn btn-warning">重选主机</button>
	<button class="btn disabled btn-primary" type="button">选择时间段</button> <input type="text" class="input-medium search-query" id="begintime" name="begintime" value="begin time"/>  
	<input type="text" class="input-medium search-query"  id="endtime" name="endtime" value="end time"/> 
	<button type="submit" onclick="rload()" class="btn btn-success">查看</button>
	<button type="submit" onclick="new_screen()" class="btn btn-success">新窗口打开</button>
	<button type="submit" onclick="chain_show()" class="btn btn-success">环比数据</button>
</div>
<?php
//if endtime is null,endtime=now.
$end=$_GET['end'];
if (strlen($end)>0){
  $now=$end;
} else {
  $end=time();
}
$period=$_GET['period'];
$begin=$end-$period;
#$itemids=$_GET['itemids'];
$item_name=$_GET['item_name'];
$hosts=$_GET['hosts'];
if ($item_name=="cpu") {
	$key_="cpu_use_all";
	$key_name = '"CPU使用百分比"';
} elseif ($item_name=="load") {
	$key_="system.cpu.load";
	$key_name = '"系统load值"';
} elseif ($item_name=="memfree") {
	$key_="vm.memory.size[pavailable]";
	$key_name = '"内存空闲百分比"';
} elseif ($item_name=="established") {
	$key_="iptstate.tcp.established";
	$key_name = '"tcp.established状态数"';
} elseif ($item_name=="timewait") {
	$key_="iptstate.tcp.timewait";
	$key_name = '"tcp.timewait状态数"';
}

//reset hosts to arr.
$hosts_arr = explode(",",$hosts);
$hosts_num = count($hosts_arr);

//reset itemids to arr.
#$itemids_arr = explode(",",$itemids);
#$itemids_num = count($itemids_arr);
#$itemids_json = json_encode($itemids_arr);
//connection for zabbix mysql database;
include '../config/config.php';
$con = mysql_connect("$dbhost","$dbuser","$dbpass");
if (!$con) {
  die('Could not connect: ' . mysql_error());
}
mysql_select_db("$dbname", $con);
$itemids='target';
#根据主机ip和监控项获取itemid列表
for ($l=0;$l<$hosts_num;$l++)
{
  $select_itemid = mysql_query("select itemid from items where key_=\"$key_\" and hostid=(select hostid from interface where ip=\"$hosts_arr[$l]\")");
  while($select_itemid_data = mysql_fetch_array($select_itemid))
  {
    $itemid = $select_itemid_data['itemid'];
	$itemids = $itemids . "," . $itemid;
  }
}
#根据最后一个itemid，获取监控项的单位
$get_key_name_sql = mysql_query("select units from items where itemid=$itemid");
while($get_key_name = mysql_fetch_array($get_key_name_sql))
{
	$units_name = '"'.$get_key_name['units'].'"';
}
$itemids = substr("$itemids",strpos($itemids,"target,")+7);
$item_id_arr = explode(",",$itemids);
$item_id_json = json_encode($item_id_arr);
$itemids_num = count($item_id_arr);
$item_name_json = json_encode($hosts_arr);
mysql_close($con);
echo <<<eot
<script type="text/javascript">
var chain = 'false';
    function rload(){
        var begintime = document.getElementById("begintime").value;
        var endtime = document.getElementById("endtime").value;
        var flush = "yes";
		var key_name = $key_name;
		var units_name = '单位: '+$units_name;
        if(begintime=="begin time")
        {
          var period = $period;
          if(endtime=="end time")
          {
            var end = $end;
          }
          else
          {
            var end_time = datetime_to_unix(endtime);
            var end = end_time;
			//alert(end_time);
            var flush = 'no';
          }
        }
        else
        {
          var begin_time = datetime_to_unix(begintime);
          if(endtime=="end time")
          {
            var end = $end;
            var period = end - begin_time;
          }
          else
          {
            var end_time = datetime_to_unix(endtime);
            var end = end_time;
            var period = end - begin_time;
            var flush = 'no';
          }
        }
        var itemids_json = $item_id_json;
        var itemids_num = $itemids_num;
        var item_name_json = $item_name_json;
        var series_value=[];
        var jquery_data = "";
        var myarray=[];
        if(chain=="true")
        {
            for (var i=0;i<itemids_num;i++){
                itemids_id=itemids_json[i];
                var myhash = { }
                myhash["name"]=item_name_json[i];
                myhash["data_"+i]=myarray;
                series_value.push(myhash);
                jquery_data+='jQuery.getJSON("api/data.php?itemid='+itemids_id+'&period='+period+'&end='+end+'&flush='+flush+'&chain=false",null,function(data_'+i+'){chart.series['+i+'].setData(data_'+i+');});'
                j=i+1;
                var start = end - period;
                var myhash_old = { }
                myhash_old["name"]="[old]"+item_name_json[i];
                myhash_old["data_"+j]=myarray;
                series_value.push(myhash_old);
                jquery_data+='jQuery.getJSON("api/data.php?itemid='+itemids_id+'&period='+period+'&end='+start+'&flush='+flush+'&chain=true",null,function(data_'+j+'){chart.series['+j+'].setData(data_'+j+');});'
            }
        }
        else
        {
            for (var i=0;i<itemids_num;i++){
                var myhash = { }
                myhash["name"]=item_name_json[i];
                myhash["data_"+i]=myarray;
                series_value.push(myhash);
                itemids_id=itemids_json[i];
                jquery_data+='jQuery.getJSON("api/data.php?itemid='+itemids_id+'&period='+period+'&end='+end+'&flush='+flush+'",null,function(data_'+i+'){chart.series['+i+'].setData(data_'+i+');});'
            }
        }
        //Do not enable UTC, otherwise it will be behind the eight hours time display.
        Highcharts.setOptions({ global: { useUTC: false },colors: ['#DD0000', '#0000CC','#8bbc21', '#0d233a', '#910000', '#1aadce', '#492970','#f28f43', '#77a1e5', '#c42525', '#a6c96a'] });

(function($){
$(function () {
        var chart = new Highcharts.Chart({
        //API HELP : http://api.highcharts.com/highcharts
            chart: {
                type: 'spline', //Using spline, draw the curve is very mellow.
                renderTo: 'container',  //Define a div name.
                margin: [ 80, 100, 90, 70],
                zoomType: 'x', //Mouse ring box, get a smaller time scale data.
                events:{
                    load:getForm  //Data obtained using the function.
                }
            },
            credits:{ //Lower right corner of the authorization.
                enabled: false,
                position: { //The coordinates define authorization.
                    align: 'right',
                    x: -10,
                    y: -1
                },
                href: "http://www.dangdang.com/",
                style: {
                    color:'blue'
                },
                text: "Zabbix of dangdang.com"
            },
            title: { //Main title.
                text: '服务器基础监控'
            },
            subtitle: {
                floating: true,
                style:{
                    color:'blue',
                    fontSize:'14px'
                },
                text: key_name,
            },
            xAxis: {  //Define the x coordinate.
                type: 'datetime',
                tickPixelInterval:180,  //x-coordinate of the interval.
                dateTimeLabelFormats: {
                    second: '%H:%M:%S',
                    minute: '%m-%d %H:%M',
                    hour: '%m-%d %H:%M',
                    day: '%Y-%m-%d',
                    week: '%e. %b',
                    month: '%Y-%m',
                    year: '%Y'
                },
                labels:{
                  rotation:0
                }
            },
            yAxis: [{ //Define the y coordinate.
                min: 0,
                title: {
                text: units_name
				}
            }],
            legend: { //Additional description box below.
                enabled: 'true',
                layout: 'horizontal',
                floating: 'true',
                align: 'center',
                borderColor: '#0F0F0F',
                maxHeight:56,
                y:5,
                borderWidth: 1.5
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
}
function goback(){
  history.go(-1);
}
function new_screen(){
  full_url = location.href;
  window.open(full_url);
}
function chain_show(){
  chain = 'true';
  rload();
}
rload();
</script>
eot;
?>
<div id="container" style="min-width: 400px; height: 500px; margin: 0 auto"></div>
</body>
</html>
