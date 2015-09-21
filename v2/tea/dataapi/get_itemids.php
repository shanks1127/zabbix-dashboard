<?php
//通过post过来的主机组名称+监控名称。获取到该组机器这一监控的所有itemid。
$group_name=$_POST['group_name'];
$item_name=$_POST['item_name'];
//$group_name='应用运维-单品api';
//$group_name=iconv('GB2312','utf-8',$group_name);
$item_name=iconv('GB2312','UTF-8',$item_name);
include '../config/config.php';
$con = mysql_connect("$dbhost","$dbuser","$dbpass");
if (!$con) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db("$dbname", $con);
mysql_query('set names UTF8');
$data = '';
//这里加上一层判断，重新对itemname与itemkey坐下关联
if ($item_name == "cpu") {
	$item_key_linux = 'cpu_use_all';
	$item_key_windows = 'system.cpu.util[all]';
	$all_itemids_sql = mysql_query('select itemid from items where (key_="'.$item_key_linux.'" or key_="'.$item_key_windows.'") and hostid in (select hostid from hosts_groups where groupid=(select groupid from groups where name="'.$group_name.'"));');
}else if ($item_name == "load") {
	$item_key = 'system.cpu.load';
	$all_itemids_sql = mysql_query('select itemid from items where key_="'.$item_key.'" and hostid in (select hostid from hosts_groups where groupid=(select groupid from groups where name="'.$group_name.'"));');
}else if ($item_name == "memfree") {
	$item_key = 'vm.memory.size[pavailable]';
	$all_itemids_sql = mysql_query('select itemid from items where key_="'.$item_key.'" and hostid in (select hostid from hosts_groups where groupid=(select groupid from groups where name="'.$group_name.'"));');
}else if ($item_name == "established") {
	$item_key = 'iptstate.tcp.established';
	$all_itemids_sql = mysql_query('select itemid from items where key_="'.$item_key.'" and hostid in (select hostid from hosts_groups where groupid=(select groupid from groups where name="'.$group_name.'"));');
}else if ($item_name == "timewait") {
	$item_key = 'iptstate.tcp.timewait';
	$all_itemids_sql = mysql_query('select itemid from items where key_="'.$item_key.'" and hostid in (select hostid from hosts_groups where groupid=(select groupid from groups where name="'.$group_name.'"));');
}
while($all_itemids = mysql_fetch_array($all_itemids_sql)) {
    $data .=$all_itemids[0].',' ;
}
mysql_close($con);
$data=rtrim($data,',');
$response=array('item_ids'=>$data);
echo json_encode($data);
?>