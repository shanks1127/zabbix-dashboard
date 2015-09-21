<!DOCTYPE html>
<HTML>
<HEAD>
	<title>Tea</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<link href="css/bootstrap-combined.min.css" rel="stylesheet">
	<link rel="stylesheet" href="css/demo.css" type="text/css"> 
	<link rel="stylesheet" href="css/zTreeStyle/zTreeStyle.css" type="text/css">
	<script type="text/javascript" src="js/jquery-2.0.0.min.js"></script>
	<script type="text/javascript" src="js/jquery.ztree.core-3.5.js"></script>
  <script type="text/javascript" src="js/jquery.ztree.exedit-3.5.js"></script>
</HEAD>
<?php
include 'config/config.php';
##cache db.
$expire = 3600;//one hour
#define cache file.
$group_cache_file = 'cache/group_cache_file.txt';

$con = mysql_connect("$dbhost","$dbuser","$dbpass");
if (!$con) {
  die('Could not connect: ' . mysql_error());
}
mysql_select_db("$dbname", $con);
mysql_query('set names UTF8');
$all_group_id = array();
$zabbix[] = array("name"=>"zabbix","isParent"=>true,"id"=>0,"pId"=>0,"open"=>true);
if (file_exists($group_cache_file)&&filemtime($group_cache_file) > (time()-$expire)){
  $all_group_id = unserialize(file_get_contents($group_cache_file));
} else {
//get all group information.
  $all_groups = mysql_query("select groupid from groups where internal=0");
  while($allgroup = mysql_fetch_array($all_groups)) {
    array_push($all_group_id,$allgroup[0]);
  }
  $group_cache_file_output = serialize($all_group_id);
  #$group_cache_file_fp = fopen($group_cache_file,"w");
  #fwrite($group_cache_file_fp,$group_cache_file_output);
  #fclose($group_cache_file_fp);
}
foreach ($all_group_id as $group_id)
{ 
  #if(!is_readable("cache/gid".$group_id))
  #{
  #  is_file("cache/gid".$group_id) or mkdir("cache/gid".$group_id);
  #}
  $group_hostnum_cache_file = 'cache/gid'.$group_id.'/group_hostnum_cache_file.txt';

  if (file_exists($group_hostnum_cache_file)&&filemtime($group_hostnum_cache_file) > (time()-$expire)){
    $group_host_number = unserialize(file_get_contents($group_hostnum_cache_file));
  } else {
    $group_host_num = mysql_query("select count(hostid) from hosts_groups where groupid=$group_id and hostid not in (select hostid from hosts where status!=0)");
    while($group_host_num_ = mysql_fetch_array($group_host_num)) {
      $group_host_number = $group_host_num_['count(hostid)'];
    };
    $group_hostnum_cache_file_output = serialize($group_host_number);
    #$group_hostnum_cache_file_fp = fopen($group_hostnum_cache_file,"w");
    #fwrite($group_hostnum_cache_file_fp,$group_hostnum_cache_file_output);
    #fclose($group_hostnum_cache_file_fp);
  }
  $group_name_cache_file = 'cache/gid'.$group_id.'/group_name_cache_file.txt';
  if (file_exists($group_name_cache_file)&&filemtime($group_name_cache_file) > (time()-$expire)){
    $groupname = unserialize(file_get_contents($group_name_cache_file));
  } else {
    $groupname_sql = mysql_query("select name from groups where internal=0 and groupid=$group_id");
    while($group_name = mysql_fetch_array($groupname_sql)) {
      $groupname = $group_name['name'];
    };
    $group_name_cache_file_output = serialize($groupname);
    #$group_name_cache_file_fp = fopen($group_name_cache_file,"w");
    #fwrite($group_name_cache_file_fp,$group_name_cache_file_output);
    #fclose($group_name_cache_file_fp);
  }
  $name = $groupname . "[" . $group_host_number . "]";
  $group_arr = array("name"=>$name,"isParent"=>true,"pId"=>0,"id"=>$group_id);
  array_push($zabbix,$group_arr);
  //get all host information.
  $host_id_cache_file = 'cache/gid'.$group_id.'/host_id_cache_file.txt';
  $group_host_id = array();
  if (file_exists($host_id_cache_file)&&filemtime($host_id_cache_file) > (time()-$expire)){
    $group_host_id = unserialize(file_get_contents($host_id_cache_file));
  } else {
    $group_host_ids = mysql_query("select hostid from hosts_groups where groupid=$group_id and hostid not in (select hostid from hosts where status!=0)");
    while($group_host_id_ = mysql_fetch_array($group_host_ids)) {
      array_push($group_host_id,$group_host_id_[0]);
    }
    $host_id_cache_file_output = serialize($group_host_id);
    #$host_id_cache_file_fp = fopen($host_id_cache_file,"w");
    #fwrite($host_id_cache_file_fp,$host_id_cache_file_output);
    #fclose($host_id_cache_file_fp);
  }
  foreach ($group_host_id as $host_id)
  {
    #if(!is_readable("cache/gid".$group_id."/hid".$host_id))
    #{
    #  is_file("cache/gid".$group_id."/hid".$host_id) or mkdir("cache/gid".$group_id."/hid".$host_id);
    #}
    $host_name_cache_file = 'cache/gid'.$group_id.'/hid'.$host_id.'/host_name_cache_file.txt';
    if (file_exists($host_name_cache_file)&&filemtime($host_name_cache_file) > (time()-$expire)){
      $hostname = unserialize(file_get_contents($host_name_cache_file));
    } else {
      $host_name_ = mysql_query("select ip from interface where hostid=$host_id");
      while($hostname_ = mysql_fetch_array($host_name_)) {
        $hostname = $hostname_['ip'];
      }
      $host_name_cache_file_output = serialize($hostname);
      #$host_name_cache_file_fp = fopen($host_name_cache_file,"w");
      #fwrite($host_name_cache_file_fp,$host_name_cache_file_output);
      #fclose($host_name_cache_file_fp);
    }
    //Due to the presence of a host belongs to multiple groups of the phenomenon, so the need to do so.
    $grouphost_id = $group_id . $host_id;
    $host_arr = array("name"=>$hostname,"isParent"=>true,"pId"=>$group_id,'id'=>$grouphost_id);
    array_push($zabbix,$host_arr);
  }
}
$datas = json_encode($zabbix);
//echo $dates;
echo <<<eot
<!--  <ul id="treeDemo" class="ztree" style="height:100%"></ul> -->
	<SCRIPT type="text/javascript">
    var url_ids = 'target';
    var MoveTest = {
      errorMsg: "Error!...Please drag it to the correct category!",
      curTarget: null,
      curTmpTarget: null,
      noSel: function() {
        try {
          window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
        } catch(e){}
      },
      dragTree2Dom: function(treeId, treeNodes) {
        return !treeNodes[0].isParent;
      },
      prevTree: function(treeId, treeNodes, targetNode) {
        return !targetNode.isParent && targetNode.parentTId == treeNodes[0].parentTId;
      },
      nextTree: function(treeId, treeNodes, targetNode) {
        return !targetNode.isParent && targetNode.parentTId == treeNodes[0].parentTId;
      },
      innerTree: function(treeId, treeNodes, targetNode) {
        return targetNode!=null && targetNode.isParent && targetNode.tId == treeNodes[0].parentTId;
      },
      dropTree2Dom: function(e, treeId, treeNodes, targetNode, moveType) {
        var domId = "dom_" + treeNodes[0].getParentNode().id;
        if (moveType == null) {
          var zTree = $.fn.zTree.getZTreeObj("treeDemo");
          //zTree.removeNode(treeNodes[0]); //if do not move,should '//' this row!!

          var newDom = $("span[domId=" + treeNodes[0].id + "]");
          if (newDom.length > 0) {
            newDom.removeClass("domBtn_Disabled");
            newDom.addClass("domBtn");
          } else {
            $("#" + domId).append("<span class='domBtn' domId='" + treeNodes[0].id + "'>" + treeNodes[0].id + "</span>");
            url_id=treeNodes[0].name;
            url_ids=url_id+","+url_ids;
            url_ids=url_ids.replace(/,target/,"");
            //Put together a graph of the url.
            var url=document.getElementById("shanks");
            url.innerHTML='<iframe id="myframe" name="myframe" frameborder="0" scrolling="no"  src="cfggraph.php?hosts='+url_ids+'" width="1200" height="620" style="border:solid 1px #eee"></iframe>';
          }
          MoveTest.updateType();
        } else if ( $(e.target).parents(".domBtnDiv").length > 0) {
          alert(MoveTest.errorMsg);
        }
      },
      dom2Tree: function(e, treeId, treeNode) {
        var target = MoveTest.curTarget, tmpTarget = MoveTest.curTmpTarget;
        if (!target) return;
        var zTree = $.fn.zTree.getZTreeObj("treeDemo"), parentNode;
        if (treeNode != null && treeNode.isParent && "dom_" + treeNode.id == target.parent().attr("id")) {
          parentNode = treeNode;
        } else if (treeNode != null && !treeNode.isParent && "dom_" + treeNode.getParentNode().id == target.parent().attr("id")) {
          parentNode = treeNode.getParentNode();
        }

        if (tmpTarget) tmpTarget.remove();
        if (!!parentNode) {
          var nodes = zTree.addNodes(parentNode, {id:target.attr("domId"), name: target.text()});
          zTree.selectNode(nodes[0]);
        } else {
          target.removeClass("domBtn_Disabled");
          target.addClass("domBtn");
          alert(MoveTest.errorMsg);
        }
        MoveTest.curTarget = null;
        MoveTest.curTmpTarget = null;
      },
      updateType: function() {
        var zTree = $.fn.zTree.getZTreeObj("treeDemo"),
        nodes = zTree.getNodes();
        for (var i=0, l=nodes.length; i<l; i++) {
          var num = nodes[i].children ? nodes[i].children.length : 0;
          <!--nodes[i].name = nodes[i].name.replace(/ \(.*\)/gi, "") + " (" + num + ")";-->
          nodes[i].id = nodes[i].id.replace(/ \(.*\)/gi, "") + " (" + num + ")";
          zTree.updateNode(nodes[i]);
        }
      },
      bindDom: function() {
        $(".domBtnDiv").bind("mousedown", MoveTest.bindMouseDown);
      },
      bindMouseDown: function(e) {
        var target = e.target;
        if (target!=null && target.className=="domBtn") {
          var doc = $(document), target = $(target),
          docScrollTop = doc.scrollTop(),
          docScrollLeft = doc.scrollLeft();
          target.addClass("domBtn_Disabled");
          target.removeClass("domBtn");
          curDom = $("<span class='dom_tmp domBtn'>" + target.text() + "</span>");
          curDom.appendTo("body");

          curDom.css({
            "top": (e.clientY + docScrollTop + 3) + "px",
            "left": (e.clientX + docScrollLeft + 3) + "px"
          });
          MoveTest.curTarget = target;
          MoveTest.curTmpTarget = curDom;

          doc.bind("mousemove", MoveTest.bindMouseMove);
          doc.bind("mouseup", MoveTest.bindMouseUp);
          doc.bind("selectstart", MoveTest.docSelect);
        }
        if(e.preventDefault) {
          e.preventDefault();
        }
      },
      bindMouseMove: function(e) {
        MoveTest.noSel();
        var doc = $(document),
        docScrollTop = doc.scrollTop(),
        docScrollLeft = doc.scrollLeft(),
        tmpTarget = MoveTest.curTmpTarget;
        if (tmpTarget) {
          tmpTarget.css({
            "top": (e.clientY + docScrollTop + 3) + "px",
            "left": (e.clientX + docScrollLeft + 3) + "px"
          });
        }
        return false;
      },
      bindMouseUp: function(e) {
        var doc = $(document);
        doc.unbind("mousemove", MoveTest.bindMouseMove);
        doc.unbind("mouseup", MoveTest.bindMouseUp);
        doc.unbind("selectstart", MoveTest.docSelect);

        var target = MoveTest.curTarget, tmpTarget = MoveTest.curTmpTarget;
        if (tmpTarget) tmpTarget.remove();

        if ($(e.target).parents("#treeDemo").length == 0) {
          if (target) {
            target.removeClass("domBtn_Disabled");
            target.addClass("domBtn");
          }
          MoveTest.curTarget = null;
          MoveTest.curTmpTarget = null;
        }
      },
      bindSelect: function() {
        return false;
      }
    };

    var datas = $datas;
		var setting = {
      edit: {
        enable: true,
        showRemoveBtn: false,
        showRenameBtn: false,
        drag: {
          isCopy: true,
          isMove: false
        }
      },
			data: {
        keep: {
          parent: true,
          leaf: true
        },
				simpleData: {
					enable: true
				}
			},
      callback: {
        onDrop: MoveTest.dropTree2Dom,
      },
      view: {
        selectedMulti: false,
        fontCss: getFontCss  
      }
		};

		var zNodes = datas;
        var lastValue = "", nodeList = [], fontCss = {};
		$(document).ready(function(){
			$.fn.zTree.init($("#treeDemo"), setting, zNodes);
            MoveTest.updateType();
            MoveTest.bindDom();
            key = $("#key");
			key.bind("focus", focusKey)
			.bind("blur", blurKey);
		});
        
    function getFontCss(treeId, treeNode) {  
        return (!!treeNode.highlight) ? {color:"#A60000", "font-weight":"bold"} : {color:"#333", "font-weight":"normal"};  
   } 

   function searchNode() {
			var zTree = $.fn.zTree.getZTreeObj("treeDemo");
            var key=$("#key");
			var value = $.trim(key.val());
			var keyType = "";
			keyType = "name";
			if (lastValue === value) return;
			lastValue = value;
			if (value === "") return;
			updateNodes(false);
			nodeList = zTree.getNodesByParamFuzzy(keyType, value);
            expandNode();
            updateNodes(true);

    }
    function updateNodes(highlight) {
        var zTree = $.fn.zTree.getZTreeObj("treeDemo");
		for( var i=0, l=nodeList.length; i<l; i++) {
			nodeList[i].highlight = highlight;
			zTree.updateNode(nodeList[i]);
		}
	}  
    function expandNode(){
        var zTree = $.fn.zTree.getZTreeObj("treeDemo");
        for( var i=0, l=nodeList.length; i<l; i++) {
			zTree.expandNode(nodeList[i], true, false);
		}
    }    
	</SCRIPT>
 </HEAD>

<BODY>
<h1><p class="text-center"><font color="#64A600">Tea 监控系统</font></p></h1>
<div class="content_wrap" >
  <div class="zTreeDemoBackground left" style='position: absolute; left: 11px; top: 36px;'>
	<input type="text" id="key" value="" class="input-medium search-query" /> <button id='search' value='search' onclick="searchNode()" class="btn">查找</button>
    <ul id="treeDemo" class="ztree"></ul>
  </div>
  <div style='position: absolute; left: 300px; top: 36px; height:620px; width:1200px;margin-top:10px;' id="shanks">
    <!--<a href="#" id="shanks"></a>-->
  </div> 
</div>
</BODY>
eot;
?>
</HTML>
