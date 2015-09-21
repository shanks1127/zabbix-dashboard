#-*- coding: utf-8 -*-
#encoding:utf-8
#!/usr/local/python
###############################
# data:2015-03-02
# writer:shanks
# 从线上公网dns拉配置，然后入到tea.internetdns表
###############################
import MySQLdb,commands
import sys,os

#连接自己的tea库
teadb_host='devopdb.idc5';
teadb_user='tea';
teadb_password='teaniubi123';
teadb_name='tea';
teadb_port=3306;

#先把slave.dangdang.com.zone从10.4.2.43上拽到本地
def get_zonefile():
    get_zone=commands.getoutput('/usr/bin/rsync -av root@10.4.2.43:/var/named/chroot/etc/slave.dangdang.com.zone /usr/local/src/10.4.2.43_slave.dangdang.com.zone')
    #检测zone文件是否下载成功
    if os.path.exists('/usr/local/src/10.4.2.43_slave.dangdang.com.zone'):
        print 'zone file get success'
    else:
        print '/usr/local/src/10.4.2.43_slave.dangdang.com.zone not found!'
        sys.exit(1)

#将zonefile做分析，之后入库
def fenxizonefile():
    #先将默认的zone配置文件格式坐下转换生成新的文件
    redo_zoneconf=commands.getoutput('/bin/egrep -v \';|NS|SOA|\)|TXT|MX\' /usr/local/src/10.4.2.43_slave.dangdang.com.zone|awk \'BEGIN{a=".";OFS="\t"}/\$ORIGIN/{a=$2}{if (a != "." && $3 != ""){$1=$1"."a;print $0;b=$1} else if (a != "." && $1 !~ "\$ORIGIN" ) {print b,$1,$2,$3} else {print $0}}\'|grep -v \'\$ORIGIN\' > /usr/local/src/internet_dns_zone.txt')
    #我擦，本屌不会写啊，还得根据重新生成配置文件，再用一条命令生成insert的sql
    mk_sqlfile=commands.getoutput('/bin/awk \'{print "insert into internetdns_records value(\\"\\",\\""$1"\\",\\""$2"\\",\\""$3"\\");"}\' /usr/local/src/internet_dns_zone.txt > /usr/local/src/internet_dns.sql')

def truncate_internetdns_records():
    #更新前先清空
    teadbconn = MySQLdb.connect(host=teadb_host,user=teadb_user,db=teadb_name,passwd=teadb_password,port=teadb_port,charset="utf8")
    teacursor = teadbconn.cursor()
    tea_truncate_sql="truncate internetdns_records";
    try:
        teacursor.execute(tea_truncate_sql)
        teadbconn.commit()
    except:
        teadbconn.rollback()
    teadbconn.close()

#文件都弄完了，现在该更新到tea.internetdns_records了
def insert_tea():
    #teadbconn = MySQLdb.connect(host=teadb_host,user=teadb_user,db=teadb_name,passwd=teadb_password,port=teadb_port,charset="utf8")
    #teacursor = teadbconn.cursor()
    #tea_insert_sql="source /usr/local/src/internet_dns.sql";
    #try:
    #    teacursor.execute(tea_insert_sql)
    #    teadbconn.commit()
    #except MySQLdb.Error,e:
    #    print 'Mysql Error %d: %s' % (e.args[0], e.args[1])
    #teadbconn.close()
    source_sql=commands.getoutput('/usr/bin/mysql -u'+teadb_user+' -p'+teadb_password+' -h'+teadb_host+' '+teadb_name+' -e "source /usr/local/src/internet_dns.sql"')
	
def _main_():
    get_zonefile()
    fenxizonefile()
    #更新前先清空
    truncate_internetdns_records()
    insert_tea()
    #删掉三个文件
    commands.getoutput('/bin/rm -rf /usr/local/src/internet_dns.sql /usr/local/src/internet_dns_zone.txt /usr/local/src/10.4.2.43_slave.dangdang.com.zone')

_main_()