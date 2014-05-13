<?php
include '../config.php';
$con = mysql_connect("$dbhost","$dbuser","$dbpass");
$end=$_GET['end'];
$flush=$_GET['flush'];
$chain=$_GET['chain'];
$now=time();
if ($flush=='no'){
  $end=$end;
}else {
  $end=$now;
}
$period=$_GET['period'];
$itemid=$_GET['itemid'];
$begin=$end-$period;
if (!$con) {
  die('Could not connect: ' . mysql_error());
}

mysql_select_db("$dbname", $con);
//check the value_type,int is 3,flot is 0.
$value_type = mysql_query("select value_type from items where itemid=$itemid");
while($type = mysql_fetch_array($value_type))
  {
  if ($type['value_type']=='0')
    $table_suffix='';
  elseif ($type['value_type']=='3')
    $table_suffix='_uint';
  }
//check the period,more than 86400(one day),data from trend,else from history.
if ($period > 86400)
  {
  $table_prefix='trends';
  $field_name='value_avg';
  }
else
  {
  $table_prefix='history';
  $field_name='value';
  }
//get the table name.
$table_name = "$table_prefix"."$table_suffix";
//check chain.
if ($chain=='true')
{
  if ($flush=='no')
  {
  $begin_time=$begin;
  $end_time=$end;
  }else{
  $begin_time=$begin-$period;
  $end_time=$end-$period;
  }
  $result = mysql_query("select 1000*(clock+$period),${field_name} from ${table_name} where itemid=$itemid and clock>=$begin_time and clock<=$end_time");
  while($row = mysql_fetch_array($result)) {
    $arr[] = array(
      intval($row["1000*(clock+$period)"]),intval($row["${field_name}"])
    );
  }
}else{
  $begin_time=$begin;
  $end_time=$end;
  $result = mysql_query("select 1000*clock,${field_name} from ${table_name} where itemid=$itemid and clock>=$begin_time and clock<=$end_time");
  while($row = mysql_fetch_array($result)) {
    $arr[] = array(
      intval($row['1000*clock']),intval($row["${field_name}"])
    );
  }
}


$data = json_encode($arr);

echo $data;

mysql_close($con);
?>
