<!DOCTYPE html>
<html>
<head>
<title>Tea</title>
<?php
$hosts=$_GET['hosts'];
$item_name=$_GET['item_name'];
$graphtype=$_GET['graphtype'];
?>
<body>
<?php
echo '兄弟，稍等...';
?>
<script language="javascript" type="text/javascript">
	var hosts="<?php echo $hosts;?>";
	var item_name="<?php echo $item_name;?>";
	var graphtype="<?php echo $graphtype;?>";
	window.location.href="graph_"+graphtype+".php?period=3600&hosts="+hosts+"&item_name="+item_name;
</script>
</body>
</html>