zabbix-dashboard-tea
================
部署方法：  
	1、将tea目录放在zabbix-web目录。  
	2、进入graph目录，修改config.php  
	3、在页面访问http://localhost/zabbix/tea/index.php
	4、从左侧的树状图中选择好主机（ip）之后，拖拽至右侧的空白处，图像展现出来。
		![image](https://raw.githubusercontent.com/shanks1127/zabbix-dashboard/master/v2/image/1.jpg) 
	5、选择监控项和数据展示方式
		![image](https://raw.githubusercontent.com/shanks1127/zabbix-dashboard/master/v2/image/2.jpg)
	6、也可以在新窗口打开，方便收藏，随时查看
		![image](https://raw.githubusercontent.com/shanks1127/zabbix-dashboard/master/v2/image/3.jpg)   

实现功能：  
	1、支持多种图像展示
	2、支持从树状列表拖拽多个ip同时展现数据  
	3、支持环比功能。
	4、可以根据item的id，定制想要的url  
		比如：http://localhost/zabbix/tea/hcgraph/graph_spline.php?period=3600&hosts=192.168.122.100,192.168.122.101&item_name=cpu 
  
  
注意事项：  
	1、请使用火狐或者google浏览器效果最好。  
	2、适用于zabbix2.0/2.2/2.4版本  
