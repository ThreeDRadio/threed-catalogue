<?php require("verify.php");

#Set these and make sure the webserver user can read files in them
$wavepath = "/data/wavein";
$mp3pathhi = "/data/music/hi";

# Needs the following in httpd.conf for this virtual host
# RewriteEngine  on
# RewriteRule    ^/database/play/(.*)$   /database/stream.php?xname=$1

#### User has logged in and been verified ####?>

<HTML>
<head>
<TITLE>ThreeD - MusicDB Track Show</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>
<BODY onload="document.forms[0].xwords.focus()">

<?php
settype ($xref, "integer");

$query = "SELECT * FROM cd WHERE id = $q$xref$q;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);

if ($num != 1) {
	echo "<p><b><font color=red>That CD does not exist \"$xref\"</font></b>";
	echo "</BODY>";
	echo "</HTML>";
	exit;
}

$r = pg_Fetch_array($result, 0, PGSQL_ASSOC);

if ($xaddcomment && trim($xcomment)) {
	$timenow = time();
	$zero = "0";
	$uquery = "INSERT INTO cdcomment (cdid, cdtrackid, comment, createwho, createwhen,
			modifywho, modifywhen)
			VALUES ($q$xref$q, $q$zero$q, $q$xcomment$q,
			$q$cid$q, $q$timenow$q, $q$cid$q, $q$timenow$q);";
	$uresult = pg_query($db, $uquery);
}

echo "<p><TABLE border=0 cellpadding=0 cellspacing=0 bgcolor=#FFFFFF><TR valign=middle><TR>";

echo "<TD valign=middle><B>MP3 FILE CHECK</B></TD>";



echo "</TD></TR></TABLE></TABLE>";

$query = "SELECT * FROM cdtrack WHERE cdid = $q$xref$q ORDER by tracknum;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);

$cdnum = sprintf("%07.0f", $xref);

$isartist = 0;
$istitle = 0;
$islength = 0;
for ($i=0;$i<$num;$i++) {
	$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
	$tracknum[$i] = $r[tracknum];
	$trackartist[$i] = $r[trackartist];
	if ($trackartist[$i]) { $isartist = 1; }
	$tracktitle[$i] = $r[tracktitle];
	if ($tracktitle[$i]) { $istitle = 1; }
	$ttt = $r[tracklength];
	if ($ttt) { $islength = 1; }
	$min = 0;
	$min = floor($ttt/60);
	$sec = $ttt % 60;
	$c = sprintf("%1d", $min) . ":" . sprintf("%02d", $sec);
	if ($ttt == 0) { $c = "&nbsp"; }
	$tracklength[$i] = $c;
	$thetracknum = sprintf("%02.0f", $r[tracknum]);
	if (is_readable("$wavepath/$cdnum/$cdnum-$thetracknum.wav")){
		$filethere[$i] = 1;
		$playname[$i] = "$wavepath/$cdnum/$cdnum-$thetracknum.wav";
	}
	else {
		if (is_readable("$mp3pathhi/$cdnum/$cdnum-$thetracknum.mp3")){
			$filethere[$i] = 1;
			$playname[$i] = "/database/play/$cdnum/$cdnum-$thetracknum.mp3";
		}
		else {
			$filethere[$i] = 0;
			$playname[$i] = "";
		}
	}
}
echo "<p><TABLE border=1 cellpadding=1 cellspacing=0 bgcolor=#CCFFCC>";
echo "<TR bgcolor=#AADDAA><TD valign=top><b>Track</b></TD>";
if ($isartist) { echo "<TD valign=top><b>Artist</b></TD>"; }
if ($istitle) { echo "<TD valign=top><b>Title</b></TD>"; }
if ($islength) { echo "<TD valign=top align=right><b>Length</b></TD>"; }
echo "<TD valign=top align=right><b>MP3</b></TD>";
echo "</TR>";
for ($i=0;$i<$num;$i++) {
	echo "<TR><TD align=center>$tracknum[$i]</TD>";
	if ($isartist) { if ($trackartist[$i]) { echo "<TD valign=top>$trackartist[$i]</TD>";} else { echo "<TD valign=top>&nbsp;</TD>"; } }
	if ($istitle) { if ($tracktitle[$i]) { echo "<TD valign=top>$tracktitle[$i]</TD>";} else { echo "<TD valign=top>&nbsp;</TD>"; } }
	if ($islength) { if ($tracklength[$i]) { echo "<TD valign=top align=right>$tracklength[$i]</TD>";} else { echo "<TD valign=top>&nbsp;</TD>"; } }
	if ($filethere[$i]) { echo "<TD valign=top align=right><A HREF=$playname[$i]>play</A></TD>";} else { echo "<TD valign=top><font color=red>No MP3 file for this track</font></TD>"; }
	echo "</TR>";
}
echo "</TABLE>";

if ($user[admin] == 't' || ($user[cdeditor] == "t" && $r[status] != 2)) {
	echo "<TD valign=middle><form action=../database/cdedit.php method=post target=_Blank>";
	echo "<input type=hidden name=xref value=$xref>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=xdoedit value=\"Edit This Entry\">";
	echo "</form></TD>";
}


?>

</BODY>
</HTML>
