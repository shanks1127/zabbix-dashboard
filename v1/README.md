zabbix-dashboard
================
部署方法：  
	1、将dztree目录放在zabbix-web目录。  
	2、进入graph目录，修改config.php  
	3、在页面访问http://localhost/zabbix/dztree/graph/gettree.php  
	4、从左侧的树状图中选择好item（key）之后，拖拽至右侧的空白处，图像展现出来。  
	备注  
1、	如果数据库中监控项比较多，本屌不会用高大上的缓存技术，可以将gettree.php放在计划任务中执行，然后访问index.html  
		例如：15 * * * * php /data/www/dztree/graph/gettree.php > /data/www/dztree/graph/index.html   

原理说明：  
	用ztree+highcharts结合。  
	ztree生成左侧树状图，树状图分为4个目录级别，group-host-application-item；因此只有那些有application名称的item才能在ztree中看到，这里需要注意。  

实现功能：  
	1、ztree生成左侧树状图，树状图分为4个目录级别，group-host-application-item；最后一层目录的下面就是监控项的key_的名称  
		注意： 只有那些有application名称的item才能在ztree中看到，这里需要注意。  
  ![image](https://raw.githubusercontent.com/shanks1127/zabbix-dashboard/master/v1/image/1.jpg)  
  2、支持从树状列表拖拽多个key同时展现数据  
	3、支持环比功能。  
  ![image](https://raw.githubusercontent.com/shanks1127/zabbix-dashboard/master/v1/image/2.jpg)   
  4、可以根据item的id，定制想要的url  
		比如：http://localhost/dztree/graph/graph.php?period=3600&itemids=133903,133905,133909,133911,133915  
  ![image](https://raw.githubusercontent.com/shanks1127/zabbix-dashboard/master/v1/image/3.jpg)   
  
注意事项：  
	1、请使用火狐或者google浏览器效果最好。  
	2、适用于zabbix2.0/2.2/4.0版本  
	3、对于4.0版本，需要将文件gettree.php 的30、63行的groups表换成hstgrp  因为在4.0中groups表改名为hstgrp了
