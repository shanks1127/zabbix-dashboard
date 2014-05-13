使用方法：
	1、将dztree目录放在zabbix-web目录。
	2、进入graph目录，修改config.php
	3、在页面访问http://localhost/zabbix/dztree/graph/gettree.php
	4、从左侧的树状图中选择好item（key）之后，拖拽至右侧的空白处，图像展现出来。
	
注意事项：
	1、请使用火狐或者google浏览器效果最好。
	2、适用于zabbix2.0版本

备注：
	1、如果数据库中监控项比较多，可以将gettree.php放在计划任务中执行，然后访问index.html
		例如：15 * * * * php /data/www/dztree/graph/gettree.php > /data/www/dztree/graph/index.html
		
原理说明：
	用ztree+highcharts结合。
	ztree生成左侧树状图，树状图分为4个目录级别，group-host-application-item；因此只有那些有application名称的item才能在ztree中看到，这里需要注意。
	