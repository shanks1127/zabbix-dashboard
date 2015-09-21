<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Tea</title>
<?php
$hosts=$_GET['hosts'];
$period=$_GET['period'];
?>
<!-- Le styles -->
<link href="css/bootstrap-combined.min.css" rel="stylesheet">
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
		<script src="js/html5shiv.js"></script>
	<![endif]-->

	<!-- Fav and touch icons -->
	<link rel="shortcut icon" href="img/favicon.png">
	
	<script type="text/javascript" src="js/jquery-2.0.0.min.js"></script>
	<!--[if lt IE 9]>
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<![endif]-->
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui.js"></script>
	<script type="text/javascript" src="js/jquery.ui.touch-punch.min.js"></script>
<script type="text/javascript" src="js/jquery.htmlClean.js"></script>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="ckeditor/config.js"></script>
<script type="text/javascript" src="js/scripts.js"></script>
</head>
<body>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span12">
			<div class="page-header">
				<h4>
					<p class="text-center"><font color="#64A600">监控交互页</font></p>
				</h4>
			</div>
		</div>
	</div>
</div>
<?php
$hosts=$_GET['hosts'];
?>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span12">
			<form role="form"  action="hcgraph/tiaozhuan.php" method="get">
				<div class="form-group span12">
					<label for="name"><h4>主机列表  <span class="label">可以编辑，或者从左侧拖拽过来，主机间以逗号分隔</span></h4></label>
					<textarea class="form-control span12" rows="5" name="hosts" value="<?php echo $hosts;?>"><?php echo $hosts;?></textarea>
				</div>
				<div class="form-group span6">
					<label for="name"><h4>选择监控项</h4></label>
					<label class="checkbox-inline"><input type="radio" name="item_name" value="cpu">CPU使用百分比</label>
					<label class="checkbox-inline"><input type="radio" name="item_name" value="memfree">剩余空闲内存百分比</label>
					<label class="checkbox-inline"><input type="radio" name="item_name" value="load">系统load值（windows不支持）</label>
					<label class="checkbox-inline"><input type="radio" name="item_name" value="established">系统tcp连接数-establish</label>
					<label class="checkbox-inline"><input type="radio" name="item_name" value="timewait">系统tcp连接数-timewait</label>
				</div>
				<div class="form-group">
					<label for="name"><h4>选择数据展示方式</h4></label>
					<label class="checkbox-inline"><input type="radio" name="graphtype" value="column">柱状图（只显示最后一次数据）</label>
					<label class="checkbox-inline"><input type="radio" name="graphtype" value="spline" checked>曲线图（默认显示最近1小时数据）</label>
				</div>
				<div class="form-group span8 text-right">
					<button type='submit' class="btn btn-success">查看数据</button>
				</div>
			</form>
		</div>
	</div>
</div>
</body>
</html>