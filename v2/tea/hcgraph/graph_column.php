<!doctype html>
<html lang="en">
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
        $('#begintime').datetimepicker({showSecond:true,timeFormat:'hh:mm:ss'});
});
$(function(){
        $('#endtime').datetimepicker({showSecond:true,timeFormat:'hh:mm:ss'});
});
function datetime_to_unix(datetime){
    var tmp_datetime = datetime.replace(/:/g,'-');
    tmp_datetime = tmp_datetime.replace(/ /g,'-');
    var arr = tmp_datetime.split("-");
    var now = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3]-8,arr[4],arr[5]));
    return parseInt(now.getTime()/1000);
}
function goback(){
  history.go(-1);
}
</script>

<meta http-equiv="refresh" content="60";>
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
<button type="submit" onclick="goback()" class="btn btn-warning">重选主机</button>
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

//connection for zabbix mysql database;
//from itemid,get the item_name.
include '../config/config.php';
$con = mysql_connect("$dbhost","$dbuser","$dbpass");
if (!$con) {
  die('Could not connect: ' . mysql_error());
}
mysql_select_db("$dbname", $con);
#定义横坐标，用ip地址
$xvalue_arr=array();
#定义纵坐标
$ydata_arr=array();
#$itemids=$_GET['itemids'];
for ($l=0;$l<$hosts_num;$l++)
{
	#根据host和key_获取到itemid
  $select_itemid = mysql_query("select itemid from items where key_=\"$key_\" and hostid=(select hostid from interface where ip=\"$hosts_arr[$l]\")");
  while($select_itemid_data = mysql_fetch_array($select_itemid))
  {
    $itemid = $select_itemid_data['itemid'];
  }
  $xvalue_arr[] = $hosts_arr[$l];
  $select_item_lastdata = mysql_query("select lastvalue from items where itemid=$itemid");
  while($value_item_data = mysql_fetch_array($select_item_lastdata))
  {
    $item_data = $value_item_data['lastvalue'];
	#重置y_arr
	$y_arr = array();
	$y_arr = array('y'=>round($item_data,2),'color'=>'#77ddff');
  }
  $ydata_arr[] = $y_arr;
}
#根据最后一个itemid，获取监控项的单位
$get_key_name_sql = mysql_query("select units from items where itemid=$itemid");
while($get_key_name = mysql_fetch_array($get_key_name_sql))
{
	$units_name = '"'.$get_key_name['units'].'"';
}

mysql_close($con);
$xvalue_json = json_encode($xvalue_arr);
$ydata_json = json_encode($ydata_arr);
echo <<<eot
  <script>
$(function () {
    var xvalue = $xvalue_json;
	var ydata = $ydata_json;
	var key_name = $key_name;	
	var units_name = '单位: '+$units_name;
    var colors = Highcharts.getOptions().colors,
        categories = xvalue,
        name = 'dangdang.com',
        data = ydata

    function setChart(name, categories, data, color) {
	chart.xAxis[0].setCategories(categories, false);
	chart.series[0].remove(false);
	chart.addSeries({
		name: name,
		data: data,
		color: color || 'white'
	}, false);
	chart.redraw();
    }

    var chart = $('#container').highcharts({
        chart: {
            type: 'column'
        },
		credits: {
			enabled: false
		},
        title: {
            text: '服务器基础监控'
        },
        subtitle: {
            text: key_name
        },
        xAxis: {
            categories: categories,
			labels:	{
				rotation: 90
			}
        },
        yAxis: {
            title: {
                text: units_name
            }
        },
        plotOptions: {
            column: {
                cursor: 'pointer',
                point: {
                    events: {
                        click: function() {
                            var drilldown = this.drilldown;
                            if (drilldown) { // drill down
                                setChart(drilldown.name, drilldown.categories, drilldown.data, drilldown.color);
                            } else { // restore
                                setChart(name, categories, data);
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    color: colors[0],
                    style: {
                        fontWeight: 'bold'
                    },
                    formatter: function() {
                        return this.y;
                    }
                }
            }
        },
        tooltip: {
            formatter: function() {
                var point = this.point,
                    s = this.x +': <b>'+ this.y +'</b><br/>';
                return s;
            }
        },
        series: [{
            name: name,
            data: data,
            color: 'white'
        }],
        exporting: {
            enabled: false
        }
    })
    .highcharts(); // return chart
});			
function new_screen(){
  full_url = location.href;
  window.open(full_url);
}
  </script>
eot;
?>
  <div id="container" style="min-width:300px;height:400px"></div>
</body>
</html>