<?php
error_reporting(E_ERROR);

$cephpool="cephstorage";

$debugnow=1;
###################################
$keyimg=$argv[1];
$keyimg=str_replace("\n","",$keyimg);$keyimg=str_replace("\r","",$keyimg);
$keyimg=str_replace("\t","",$keyimg);$keyimg=str_replace("\0","",$keyimg);

if($keyimg=="")
{
print "Please Provide the image name example : vm-100-disk-0 ";
}
if($keyimg!="")
{
$drimg="livedr-".$keyimg;
$cmdx="rbd rm ".$cephpool."/".$keyimg." 2>/dev/null | grep -v \"SNAPID\" | sort -rn |awk '{print $2}'";
if($debugnow==1){print "\n# REMOVE OLD CHILD IMAGE - IF ANY ".$cephpool."/".$keyimg."  \n".$cmdx." \n";}
$cmdxout=`$cmdx`;
$cmdx="rbd snap ls ".$cephpool."/".$drimg." 2>/dev/null | grep -v \"SNAPID\" | grep \"yes\"| sort -rn |awk '{print $2}'";
if($debugnow==1){print "\n#GET PROTECT  SNAPSHOT LIST - IF ANY TO REMOVE \n".$cmdx." \n";}
$cmdxout=`$cmdx`;
$snaplist=array();
$snaplist=explode("\n",$cmdxout);
for($i=0;$i<sizeof($snaplist);$i++)
{
if($snaplist[$i]!="" )
{
$cmdx="rbd snap unprotect ".$cephpool."/".$drimg."@".$snaplist[$i]." 2>/dev/null | grep -v \"SNAPID\" | sort -rn |awk '{print $2}'";
if($debugnow==1){print "\n# UNPROTECTING SNAPSHOTS USED IN CLONE - FOR LATTER REMOVAL \n".$cmdx." \n";}
$cmdxout=`$cmdx`;
}
}
$cmdx="rbd snap ls ".$cephpool."/".$drimg." 2>/dev/null | grep -v \"SNAPID\" | sort -rn |awk '{print $2}'";
if($debugnow==1){print "\n#WORK ON GET LATEST SNAPSHOT \n".$cmdx." \n";}
$cmdxout=`$cmdx`;
$snaplist=array();
$snaplist=explode("\n",$cmdxout);
$latestid=$snaplist[0];
print "\nLATEST SNAPSHOT ID: $latestid";
$cmdx="rbd snap protect ".$cephpool."/".$drimg."@".$latestid." 2>/dev/null | grep -v \"SNAPID\" | sort -rn |awk '{print $2}'";
if($debugnow==1){print "\n#PROTECTING LATEST SNAPSHOT FOR USE IN CLONE \n".$cmdx." \n";}
$cmdxout=`$cmdx`;
$cmdx="rbd clone ".$cephpool."/".$drimg."@".$latestid." ".$cephpool."/".$keyimg." 2>/dev/null ";
if($debugnow==1){print "\n#WORK ON MAKING CLONE  as  ".$keyimg."\n".$cmdx." \n";}
$cmdxout=`$cmdx`;

/// if for keyimg over
}
print "\n";

?>
