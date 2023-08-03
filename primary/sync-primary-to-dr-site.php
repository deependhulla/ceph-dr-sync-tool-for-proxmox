<?php
error_reporting(E_ERROR);
$debugnow=1;

$cephpoolec=1;
$cephpoolname="ec21";
$drserversship="192.168.30.251";
$drserversshport="22";


$keeplastsnapshot=3;
#$debugnow=1;

#######
$keyimg=$argv[1];
$keyimg=str_replace("\n","",$keyimg);
$keyimg=str_replace("\r","",$keyimg);
$keyimg=str_replace("\t","",$keyimg);
$keyimg=str_replace("\0","",$keyimg);

if($keyimg=="")
{
print "Please Provide the image name example : vm-100-disk-0 ";
}
if($keyimg!="")
{
$cephpool=$cephpoolname;
$cephdata='';
if($cephpoolec==1)
{

$cephdata=$cephpoolname.'-data';
$cephpool=$cephpoolname.'-metadata';
}
###################################
#$drimg="livedr-".$keyimg;
$drimg=$keyimg;
$cmdx="ssh -p ".$drserversshport." ".$drserversship." rbd ls ".$cephpool." | grep ".$drimg."";
if($debugnow==1){print "\n#CHECK IF IMG THERE ON REMOTE DR SERVER \n".$cmdx."\n";}
$cmdxout=`$cmdx`;$cmdxout=str_replace("\n","",$cmdxout);$cmdxout=str_replace("\r","",$cmdxout);

if($cmdxout=="")
{
$cmdx="ssh -p ".$drserversshport." ".$drserversship." rbd create ".$cephpool."/".$drimg." -s 1";
if($cephpoolec==1)
{
##rbd create livedr-vm-102-disk-0 -s 1 --data-pool ec21-data --pool ec21-metadata
$cmdx="ssh -p ".$drserversshport." ".$drserversship." rbd create ".$drimg." -s 1 --data-pool ".$cephdata." --pool ".$cephpool."";
}
if($debugnow==1){print "\n#CREATING FIRST TIME IMG ON REMOTE DR SERVER \n".$cmdx."\n";}

$cmdxout=`$cmdx`;$cmdxout=str_replace("\n","",$cmdxout);$cmdxout=str_replace("\r","",$cmdxout);
}

$curtime=date('Y-m-d_h-i-s')."__".microtime(true);
#create CURRENT's snapshot on SOURCEPOOL

$cmdx="rbd snap ls ".$cephpool."/".$keyimg." | grep ".$curtime."";
if($debugnow==1){print "\n#CHECK IF BY MISTAKE CURTIME SNAPSHOT ON SOURCE IS THERE \n".$cmdx." \n";}
$cmdxout=`$cmdx`;$cmdxout=str_replace("\n","",$cmdxout);$cmdxout=str_replace("\r","",$cmdxout);

if($cmdxout=="")
{
$cmdx="rbd snap create ".$cephpool."/".$keyimg."@".$curtime."";
if($debugnow==1){print "\n#CREATE SNAPSHOT ON SOURCE  \n".$cmdx." \n";}
$cmdxout=`$cmdx`;$cmdxout=str_replace("\n","",$cmdxout);$cmdxout=str_replace("\r","",$cmdxout);

}


$cmdx="ssh -p ".$drserversshport." ".$drserversship."  rbd snap ls ".$cephpool."/".$drimg." 2>/dev/null | grep -v \"SNAPID\" | sort -rn | head -n 1 |awk '{print $2}'";
if($debugnow==1){print "\n#CHECK IF SNAPSHOT ON REMOTE DR IS THERE AND GET LAST SNAPSNOT-IMAGE-NAME\n".$cmdx." \n";}
$cmdxout=`$cmdx`;$cmdxout=str_replace("\n","",$cmdxout);$cmdxout=str_replace("\r","",$cmdxout);
$lastsnapid=$cmdxout;

if($lastsnapid=="")
{
$cmdx="rbd export-diff ".$cephpool."/".$keyimg."@".$curtime." - | ssh -p ".$drserversshport." ".$drserversship." rbd import-diff - ".$cephpool."/".$drimg."  ";
if($debugnow==1){print "\n#TRANSFER THE FIRST SNAPSHOT TO REMOTE DR \n".$cmdx." \n";}
system($cmdx);

}

if($lastsnapid!="")
{
$lastsnapid=$cmdxout;
$cmdx="rbd export-diff --from-snap ".$lastsnapid." ".$cephpool."/".$keyimg."@".$curtime." - | ssh -p ".$drserversshport." ".$drserversship." rbd import-diff - ".$cephpool."/".$drimg."  ";
if($debugnow==1){print "\n#TRANSFER THE LATEST SNAPSHOT TO REMOTE DR FROM LASTSNAPSHOT IT HAD ON DR \n".$cmdx." \n";}
system($cmdx);
}



$cmdx="ssh -p ".$drserversshport." ".$drserversship."  rbd snap ls ".$cephpool."/".$drimg." 2>/dev/null | grep -v \"SNAPID\" | sort -rn |awk '{print $2}'";
if($debugnow==1){print "\n#CHECK IF SNAPSHOT ON REMOTE DR IS THERE AND GET LAST SNAPSNOT-IMAGE-NAME FOR VERIFY AND WORK ON CLEAN UP OF OLD SNAPSHOT IF ANY (KEEP $keeplastsnapshot)\n".$cmdx." \n";}
$cmdxout=`$cmdx`;
$snaplist=array();
$snaplist=explode("\n",$cmdxout);
$lastcopy=0;
if($snaplist[sizeof($snaplist)-1] == $curx){$lastcopy=1; }

for($i=1;$i<sizeof($snaplist);$i++)
{
if($snaplist[$i]!="" && $lastcopy ==1)
{
## looks like last sync was good we can delete old
$todelnow=0;
if($keeplastsnapshot < $i ){$todelnow=1;}
if($todelnow==1)
{
$cmdx="ssh -p ".$drserversshport." ".$drserversship."  rbd snap remove ".$cephpool."/".$drimg."@".$snaplist[$i]." 2>/dev/null ";
if($debugnow==1){print "\n#REMOVE OLD SNAPSHOT ON REMOTE DR  \n".$cmdx." \n";}
system($cmdx);
}
}
}



$cmdx="rbd snap ls ".$cephpool."/".$keyimg." 2>/dev/null | grep -v \"SNAPID\" | sort -rn |awk '{print $2}'";
if($debugnow==1){print "\n#WORK ON CLEAN UP OF OLD SNAPSHOT IF ANY ON LOCAL (KEEP $keeplastsnapshot)\n".$cmdx." \n";}
$cmdxout=`$cmdx`;
$snaplist=array();
$snaplist=explode("\n",$cmdxout);

for($i=1;$i<sizeof($snaplist);$i++)
{
if($snaplist[$i]!="" && $lastcopy ==1)
{
## looks like last sync was good we can delete old
$todelnow=0;
if($keeplastsnapshot < $i ){$todelnow=1;}
if($todelnow==1)
{
$cmdx=" rbd snap remove ".$cephpool."/".$keyimg."@".$snaplist[$i]." 2>/dev/null ";
if($debugnow==1){print "\n#REMOVE OLD SNAPSHOT ON REMOTE DR  \n".$cmdx." \n";}
system($cmdx);
}
}
}

$cmdx="rbd du '".$cephpool."/".$keyimg."' ";
if($debugnow==1){print "\n#RDB DU INFO LOCAL \n".$cmdx." \n";}
$cmdxout=`$cmdx`;
print $cmdxout;


$cmdx="ssh -p ".$drserversshport." ".$drserversship."  rbd du '".$cephpool."/".$keyimg."' ";
if($debugnow==1){print "\n#RDB DU INFO REMOTE \n".$cmdx." \n";}
$cmdxout=`$cmdx`;
print $cmdxout;
//// if key there
}

print "\n";
?>


