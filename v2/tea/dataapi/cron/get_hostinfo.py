#-*- coding: utf-8 -*-
#encoding:utf-8
#!/usr/local/python
###############################
# data:2015-01-23
# writer:shanks
# 从多方数据源获取数据，录入到tea中间表
###############################
import MySQLdb,commands
import sys
reload(sys)
sys.setdefaultencoding('GB2312')
#连接李军的opsdb
opsdb_host='10.64.4.220';
opsdb_user='dongchuan';
opsdb_password='dongchuan@123';
opsdb_name='opsdb';
opsdb_port=3306;
#连接自己的tea.hostinfo
teadb_host='devopdb.idc5';
teadb_user='tea';
teadb_password='teaniubi123';
teadb_name='tea';
teadb_port=3306;
#连接zabbix数据库
zdb_host='zabbixrdb.idc3';
zdb_user='readonly';
zdb_password='zabbix@123';
zdb_name='zabbix';
zdb_port=3306;
#连接lvs配置库
lvsdb_host='10.4.2.51';
lvsdb_user='tea';
lvsdb_password='tea@123';
lvsdb_name='lvs_new';
lvsdb_port=3306;
#连接内网dns配置库
dnsdb_host='192.168.195.246';
dnsdb_user='dns';
dnsdb_password='dns@123';
dnsdb_name='internaldns';
dnsdb_port=3306;
#连接董川写的monitordb，获取内外网ip映射关系
dcdb_host='10.64.5.10';
dcdb_user='monitordb';
dcdb_password='shanks@123';
dcdb_name='monitordb';
dcdb_port=3306;
#连接周楠的机器，从上面get下来交换机-ip对应表作分析
zn_devicetracker_report='http://192.168.103.1/devicetracker_report';
zn_core_switch_list='http://192.168.103.1/list-core';

def get_hostinfo_from_opsdb():
#从李军opsdb库获取数据，写入到tea.hostinfo表
    get_hostinfo_sql="select distinct a.sn,i.vendornickname,j.manufacturernickname,a.contractid_id,a.pn,ip,d.cputypename,ilo_ip,a.rackid,g.idcname,h.sysname,b.memvalue,c.staffname,e.appfuncname,date_add(a.startdate, interval 3 year) as maintaindate,f.hdstatusname,a.appname from all_server a,all_memory b,all_employee c,all_cputype d,all_appfunc e,all_hdstatus f,all_idc g,all_opsys h,all_vendor i,all_manufacturer j,all_disktype l where a.memid_id=b.memid and a.staffid_id=c.staffid and a.cputypeid_id=d.cputypeid and a.appfuncid_id=e.appfuncid and a.hdstatusid_id=f.hdstatusid and a.idcid_id=g.idcid and a.sysid_id=h.sysid and a.vendorid_id=i.vendorid and a.manufacturer_id_id=j.manufacturer_id"
	#这条sql的输出效果
    just_help="""
				  sn: CNG847S27X
      vendornickname: 亚康环宇
manufacturernickname: HP
       contractid_id: Js0901-1
                  pn: HPDL580G5
                  ip: 10.255.254.185
         cputypename: 四核_E7420_2.13
              ilo_ip: 
              rackid: B1_442
             idcname: IDC3
             sysname: win2003 64bit
            memvalue: 32GB
           staffname: 郭宇楠
         appfuncname: 测试应用
        maintaindate: 2011-12-03
        hdstatusname: 正式使用
	"""
    opsdbconn = MySQLdb.connect(host=opsdb_host,user=opsdb_user,db=opsdb_name,passwd=opsdb_password,port=opsdb_port,charset="utf8")
    try:
        cursor = opsdbconn.cursor()
        #从opsdb中获取所有主机的基础信息
        cursor.execute(get_hostinfo_sql)
        all_host_info_data = cursor.fetchall()
        for row in all_host_info_data:
            sn = row[0]
            xinghao = row[4]
            ip = row[5]
            iloip = row[7]
            jigui = row[8]
            idc = row[9]
            xitong = row[10]
            neicun = row[11]
            cpu = row[6]
            shiyongren = row[12]
            fenzuyingyong = row[13]
            xiangxiyingyong = row[16]
            hetonghao = row[3]
            weibaoriqi = row[14]
            #将获取到的这些数据，insert到tea.hostinfo
            teadbconn = MySQLdb.connect(host=teadb_host,user=teadb_user,db=teadb_name,passwd=teadb_password,port=teadb_port,charset="utf8")
            teacursor = teadbconn.cursor()
            tea_insert_sql="insert into hostinfo(sn,xinghao,ip,iloip,jigui,idc,xitong,neicun,cpu,shiyongren,fenzuyingyong,xiangxiyingyong,hetonghao,weibaoriqi)value ('"+sn+"','"+xinghao+"','"+ip+"','"+iloip+"','"+jigui+"','"+idc+"','"+xitong+"','"+neicun+"','"+cpu+"','"+shiyongren+"','"+fenzuyingyong+"','"+xiangxiyingyong+"','"+hetonghao+"','"+str(weibaoriqi)+"')"
            try:
                teacursor.execute(tea_insert_sql)
                teadbconn.commit()
            except:
                teadbconn.rollback()
            teadbconn.close()
        cursor.close()
    except MySQLdb.Error,e:
        print 'Mysql Error %d: %s' % (e.args[0], e.args[1])
    opsdbconn.close()

def get_allip():
    get_all_ip_sql="select ip from hostinfo where ip!=''";
    teadbconn = MySQLdb.connect(host=teadb_host,user=teadb_user,db=teadb_name,passwd=teadb_password,port=teadb_port,charset="utf8")
    teacursor = teadbconn.cursor()
    try:
        teacursor.execute(get_all_ip_sql)
        #把所有主机ip定义为全局变量，其他函数直接调用
        global all_ip
        all_ip = teacursor.fetchall()
    except MySQLdb.Error,e:
        print 'Mysql Error %d: %s' % (e.args[0], e.args[1])
    teadbconn.close()
	
def __switch__():
#从周楠维护的交换机对应表得到ip在哪台交换机、交换机哪个端口上
    get_device_report=commands.getoutput('/usr/bin/wget '+zn_devicetracker_report+' -O /tmp/devicetracker_report');
    get_corelist=commands.getoutput('/usr/bin/wget '+zn_core_switch_list+' -O /tmp/list-core');
    #为每一个在opsdb中存在的ip匹配它在交换机什么位置，并插入tea.hostinfo表
    get_all_ip_sql="select ip from hostinfo where ip!=''";
    teadbconn = MySQLdb.connect(host=teadb_host,user=teadb_user,db=teadb_name,passwd=teadb_password,port=teadb_port,charset="utf8")
    teacursor = teadbconn.cursor()
    for _ip_ in all_ip:
        ipaddr = _ip_[0]
        switch_info='';
        #print ipaddr;
        #先判断devicetracker中查出几行数据，如果switch_port有多行，要过滤掉core的
        switch_hang=commands.getoutput('awk \'{if($4=="'+ipaddr+'"){print $1,$2,$3}}\' /tmp/devicetracker_report|wc -l')
        #从corelist中生成所有核心ip，用于后面的grep
        switch_core_ip=commands.getoutput('awk \'BEGIN{ORS="|"}{print $2}\' /tmp/list-core|sed \'s/|$//g\'');
        if int(switch_hang) > 1:
            #过滤devicetracker_report
            switch_info=commands.getoutput('awk \'{if($4=="'+ipaddr+'"){print $1,$2,$3}}\' /tmp/devicetracker_report|grep -Ev "('+str(switch_core_ip)+')"')
        else:
            switch_info=commands.getoutput('awk \'{if($4=="'+ipaddr+'"){print $1,$2,$3}}\' /tmp/devicetracker_report')
            #print ipaddr+'===='+switch_port;
        switch_name=commands.getoutput('echo "'+switch_info+'"|awk \'{print $1,$2"<br>"}\'')
        switch_port=commands.getoutput('echo "'+switch_info+'"|awk \'{print $3"<br>"}\'')
        if len(switch_info) == 0:
            #print ipaddr+'===='+switch_port;
            switch_port='没有从devicetracker获取到数据';
            switch_name='没有从devicetracker获取到数据';
        #update tea.hostinfo;
        update_switchport_sql="update hostinfo set jiaohuanjiduankou='"+switch_port+"' where ip='"+ipaddr+"'";
        update_switchname_sql="update hostinfo set jiaohuanji='"+switch_name+"' where ip='"+ipaddr+"'";
        try:
            teacursor.execute(update_switchport_sql)
            teacursor.execute(update_switchname_sql)
            teadbconn.commit()
        except:
            teadbconn.rollback()
    teadbconn.close()

def get_info_from_zabbix():
#从zabbix中获取主机mac、主机名、dns配置、内核信息（uname -a）	
    zdbconn = MySQLdb.connect(host=zdb_host,user=zdb_user,db=zdb_name,passwd=zdb_password,port=zdb_port,charset="utf8")
    zcursor = zdbconn.cursor()
    for _ip_ in all_ip:	
        ipaddr=_ip_[0]
        z_dnsconf_sql = 'select replace(lastvalue,char(10),"<br>") from items where key_="vfs.file.contents[/etc/resolv.conf,]" and hostid=(select hostid from interface where ip="'+ipaddr+'")';
        z_uname_sql = 'select lastvalue from items where key_="system.uname" and hostid=(select hostid from interface where ip="'+ipaddr+'")';
        z_mac_sql = 'select key_,lastvalue from items where key_ like "system.hw.macaddr%" and lastvalue !="" and hostid=(select hostid from interface where ip="'+ipaddr+'")';
        try:
            zcursor.execute(z_dnsconf_sql)
            _dnsconf_ = zcursor.fetchall()
            if len(_dnsconf_) == 0:
                dnsma='没有从zabbix获取到dns配置信息'
            else:
                dnsma = _dnsconf_[0][0]
        except MySQLdb.Error,e:
            print 'Mysql Error %d: %s' % (e.args[0], e.args[1])
            dnsma = '从zabbix数据库获取数据失败'
        if dnsma == None:
            dnsma='没有从zabbix获取到dns配置信息'
        #dnsma=dnsma.replace('\n','<br>')
		
        try:
            zcursor.execute(z_uname_sql)
            _uname_ = zcursor.fetchall()
            if len(_uname_) == 0:
                uname='没有从zabbix获取到uname信息'
                hostname='没有从zabbix获取到hostname信息'
            else:
                uname=_uname_[0][0]
                if uname == None:
                    uname='没有从zabbix获取到uname信息'
                    hostname='没有从zabbix获取到hostname信息'
                else:
                    hostname=commands.getoutput('echo "'+uname+'"|awk \'{print $2}\'')
        except MySQLdb.Error,e:
            print 'Mysql Error %d: %s' % (e.args[0], e.args[1])
            uname='从zabbix数据库获取数据失败'
            hostname='从zabbix数据库获取数据失败'

        try:
            macaddr=''
            zcursor.execute(z_mac_sql)
            _mac_ = zcursor.fetchall()
            if len(_mac_) == 0:
                macaddr = '没有从zabbix获取到MAC信息'
            else:
                for mac_ in _mac_:
                    key_ = mac_[0]
                    mac = mac_[1]
                    if mac == None:
                        macaddr == '没有从zabbix获取到MAC信息'
                    else:
                        interface = commands.getoutput('echo "'+key_+'"|awk -F\'[\' \'{print $2}\'|awk -F, \'{print $1}\'')
                        macaddr+=interface+'---'+mac+'<br>'
        except MySQLdb.Error,e:
            print 'Mysql Error %d: %s' % (e.args[0], e.args[1])
            macaddr = '从zabbix数据库获取数据失败'

        #更新到tea.hostinfo
        teadbconn = MySQLdb.connect(host=teadb_host,user=teadb_user,db=teadb_name,passwd=teadb_password,port=teadb_port,charset="utf8")
        teacursor = teadbconn.cursor()
        update_dns_sql="update hostinfo set dns='"+dnsma+"' where ip='"+ipaddr+"'"
        update_uname_sql="update hostinfo set uname='"+uname+"' where ip='"+ipaddr+"'"
        update_hostname_sql="update hostinfo set hostname='"+hostname+"' where ip='"+ipaddr+"'"
        update_mac_sql="update hostinfo set mac='"+macaddr+"' where ip='"+ipaddr+"'"
        try:
            teacursor.execute(update_dns_sql)
            teacursor.execute(update_uname_sql)
            teacursor.execute(update_hostname_sql)
            teacursor.execute(update_mac_sql)
            teadbconn.commit()
        except:
            teadbconn.rollback()
        teadbconn.close()
    zdbconn.close()

def chk_internal_dns(searchip):
#判断ip地址在内网dns中的域名，只返回域名信息，不更新tea.hostinfo
    dnsdbconn = MySQLdb.connect(host=dnsdb_host,user=dnsdb_user,db=dnsdb_name,passwd=dnsdb_password,port=dnsdb_port,charset="utf8")
    dnscursor = dnsdbconn.cursor()
    get_dn_sql = 'select host,zone,data from dns_records where data="'+searchip+'"'
    try:
        dnscursor.execute(get_dn_sql)
        _dn_ = dnscursor.fetchall()
        dn='对应域名:'
        if len(_dn_) == 0:
            dn = '没有对应的内网域名'
        else:
            for dn_ in _dn_:
                host=dn_[0]
                zone=dn_[1]
                dn+=host+'.'+zone+';'
    except MySQLdb.Error,e:
        print 'Mysql Error %d: %s' % (e.args[0], e.args[1])
        dn = '从dns配置库获取数据失败'
    dnsdbconn.close()
    return dn

def chkinternetdn(internetip):
#从tea.internetdns_records查ip有没有公网域名
    #这里获取的ip格式是119.254.49.98/32这种的，需要将掩码部分过滤掉
    ip=commands.getoutput('echo '+internetip+'|awk -F/ \'{print $1}\'')
    teadbconn = MySQLdb.connect(host=teadb_host,user=teadb_user,db=teadb_name,passwd=teadb_password,port=teadb_port,charset="utf8")
    teacursor = teadbconn.cursor()
    chkdnsql='select host from internetdns_records where data="'+ip+'"';
    #直接从tea库拿数据，要是tea库挂了，页面都打不开，所以不用try:了
    teacursor.execute(chkdnsql)
    dncount_ = teacursor.fetchall()
    chkdn=''
    if len(dncount_) == 0:
        chkdn='没有对应的公网域名'
    else:
        for _chkdn_ in dncount_:
            chkdn+=_chkdn_[0]+';'
    teadbconn.close()
    return chkdn
	
def chkinternetip(justip):
#从董川的monitordb查当前ip有没有公网映射
    dcdbconn = MySQLdb.connect(host=dcdb_host,user=dcdb_user,db=dcdb_name,passwd=dcdb_password,port=dcdb_port,charset="utf8")
    dccursor = dcdbconn.cursor()
    get_internet_ip_sql='select members from public_ip_map where address_name in (select address_name from private_ip_map where members="'+justip+'")'
    try:
        dccursor.execute(get_internet_ip_sql)
        _dc_ = dccursor.fetchall()
        dc='对应公网地址:'
        if len(_dc_) == 0:
            dc = '没有对应的公网IP'
        else:
            for dc_ in _dc_:
                dc+=dc_[0]+';'
    except MySQLdb.Error,e:
        print 'Mysql Error %d: %s' % (e.args[0], e.args[1])
        print 'chkinternetip'
        dc = '从dc的monitordb库获取数据失败'
    dcdbconn.close()
    return dc
	
def chkinternetipdn(justip):
#从董川的monitordb查当前ip有没有公网映射
    dcdbconn = MySQLdb.connect(host=dcdb_host,user=dcdb_user,db=dcdb_name,passwd=dcdb_password,port=dcdb_port,charset="utf8")
    dccursor = dcdbconn.cursor()
    get_internet_ip_sql='select members from public_ip_map where address_name in (select address_name from private_ip_map where members="'+justip+'")'
    try:
        dccursor.execute(get_internet_ip_sql)
        _dc_ = dccursor.fetchall()
        dn=''
        if len(_dc_) == 0:
            dn = '没有对应的公网域名'
        else:
            dn = '对应公网域名为:'
            for dc_ in _dc_:
                #查下这个公网ip有没有对应的公网域名
                dn+='{'+dc_[0]+'['+chkinternetdn(dc_[0])+']};'
    except MySQLdb.Error,e:
        print 'Mysql Error %d: %s' % (e.args[0], e.args[1])
        print 'chkinternetipdn'
        dn = '由于没有获取到公网ip，因此未能进行匹配公网域名的步骤'
    dcdbconn.close()
    return dn
	
def get_lbsinfo():
#判断主机是否挂在负载均衡下面，目前包含F5、LVS
    lvsdbconn = MySQLdb.connect(host=lvsdb_host,user=lvsdb_user,db=lvsdb_name,passwd=lvsdb_password,port=lvsdb_port,charset="utf8")
    lvscursor = lvsdbconn.cursor()
    for _ip_ in all_ip:	
        ipaddr=_ip_[0]
        #先检测是不是在lvs中
        chklvssql='select distinct(vip) from think_lvs where realserver="'+ipaddr+'"'
        lvsvip=''
        lvsdn_data=''
        lvsinternet_ip=''
        lvsinternet_dn=''
        try:
            lvscursor.execute(chklvssql)
            vipcount_ = lvscursor.fetchall()
            if len(vipcount_) == 0:
                chklvs='不在lvs中'
                #获取内网域名
                lvsdn_data=ipaddr+chk_internal_dns(ipaddr)
                #获取公网映射ip
                lvsinternet_ip=ipaddr+chkinternetip(ipaddr)
                #获取公网域名
                lvsinternet_dn=ipaddr+chkinternetipdn(ipaddr)
            else:
                for _vip_ in vipcount_:
                    lvsvip+=_vip_[0]+';'
                    lvsdn_data+=ipaddr+'的vip['+_vip_[0]+']'+chk_internal_dns(_vip_[0])+'<br>'
                    lvsinternet_ip+=ipaddr+'的vip['+_vip_[0]+']'+chkinternetip(_vip_[0])+'<br>'
                    lvsinternet_dn+=ipaddr+'的vip['+_vip_[0]+']'+chkinternetipdn(_vip_[0])+'<br>'
                chklvs='在lvs中,虚地址为: '+lvsvip
                #获取内网域名
                lvsdn_data+=ipaddr+chk_internal_dns(ipaddr)
                #获取公网映射ip
                lvsinternet_ip+=ipaddr+chkinternetip(ipaddr)
                #获取公网域名
                lvsinternet_dn=ipaddr+chkinternetipdn(ipaddr)
        except MySQLdb.Error,e:
            print 'Mysql Error %d: %s' % (e.args[0], e.args[1])
            chklvs = '从lvs配置表获取数据失败'
            lvsdn_data = '由于从lvs配置获取数据失败，未能进行匹配dns的操作'
            lvsinternet_ip = '由于从lvs配置获取数据失败，未能进行匹配公网映射ip的操作'
            lvsinternet_dn = '由于从lvs配置获取数据失败，未能进行匹配公网域名的操作'
        #检测是不是在F5中
        chkf5='F5抓取待续'
        teadbconn = MySQLdb.connect(host=teadb_host,user=teadb_user,db=teadb_name,passwd=teadb_password,port=teadb_port,charset="utf8")
        teacursor = teadbconn.cursor()
        chkf5sql='select poolvip from f5poolinfo where poolrs like \'%'+ipaddr+':%\'';
        f5vip=''
        f5dn_data=''
        f5internet_ip=''
        f5internet_dn=''
        #直接从tea库拿数据，要是tea库挂了，页面都打不开，所以不用try:了
        teacursor.execute(chkf5sql)
        f5vipcount_ = teacursor.fetchall()
        if len(f5vipcount_) == 0:
            chkf5='不在F5中'
            #不在F5中，所以只有本机ip需要去关联查询内网域名、公网映射，而这些在上面lvs中已经做过了，所以这里就不需要再做一次了
        else:
            for _f5vip_ in f5vipcount_:
                #这里获取到的vip是带端口的，需要把端口过滤掉
                f5vip0=commands.getoutput('echo '+_f5vip_[0]+'|awk -F: \'{print $1}\'')
                f5vip+=f5vip0+';'
                f5dn_data+=ipaddr+'的vip['+f5vip0+']'+chk_internal_dns(f5vip0)+'<br>'
                f5internet_ip+=ipaddr+'的vip['+f5vip0+']'+chkinternetip(f5vip0)+'<br>'
                f5internet_dn+=ipaddr+'的vip['+f5vip0+']'+chkinternetdn(f5vip0)+'<br>'
            chkf5='在F5中,虚地址为: '+f5vip
        teadbconn.close()
        #######################################################
        #合并lvs和f5的检测结果
        chk_lbs=chkf5+'<br>'+chklvs
        dn_data=f5dn_data+lvsdn_data
        internet_ip=f5internet_ip+lvsinternet_ip
        internet_dn=f5internet_dn+lvsinternet_dn
        #更新到tea.hostinfo
        teadbconn = MySQLdb.connect(host=teadb_host,user=teadb_user,db=teadb_name,passwd=teadb_password,port=teadb_port,charset="utf8")
        teacursor = teadbconn.cursor()
        update_lbs_sql="update hostinfo set fuzaijunheng='"+chk_lbs+"' where ip='"+ipaddr+"'"
        update_neiwangdns_sql="update hostinfo set duiyingyuming='"+dn_data+"' where ip='"+ipaddr+"'"
        update_gongwangip_sql="update hostinfo set duiyinggongwangip='"+internet_ip+"' where ip='"+ipaddr+"'"
        update_gongwangdn_sql="update hostinfo set duiyinggongwangyuming='"+internet_dn+"' where ip='"+ipaddr+"'"
        try:
            teacursor.execute(update_lbs_sql)
            teacursor.execute(update_neiwangdns_sql)
            teacursor.execute(update_gongwangip_sql)
            teacursor.execute(update_gongwangdn_sql)
            teadbconn.commit()
        except:
            teadbconn.rollback()
        teadbconn.close()
    lvsdbconn.close()

def truncate_hostinfo():
#清空tea.hostinfo表
    teadbconn = MySQLdb.connect(host=teadb_host,user=teadb_user,db=teadb_name,passwd=teadb_password,port=teadb_port,charset="utf8")
    teacursor = teadbconn.cursor()
    tea_truncate_sql="truncate hostinfo";
    try:
        teacursor.execute(tea_truncate_sql)
        teadbconn.commit()
    except:
        teadbconn.rollback()
    teadbconn.close()		
	
def main_():
    #顺序不能打乱
    truncate_hostinfo()
    get_hostinfo_from_opsdb()
    get_allip()
    __switch__()
    get_lbsinfo()
    get_info_from_zabbix()
main_()