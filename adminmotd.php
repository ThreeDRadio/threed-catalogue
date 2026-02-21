<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - Edit MOTD</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>

<BODY>

<?php
#echo "##".htmlentities($xmotd)."##<p>";

#### onload="document.forms[0].xmotd.focus();document.forms[0].xmotd.select()"

if (!$admin) {
	#echo "<HTML><BODY>";
	echo "<p><font color=red><b>Must be an Administrator to do that!</b></font><p>";
	echo "</BODY></HTML>";
	exit;
}

if ($xupdate || $xbutton) {
	#$xmotd = addslashes($xmotd);
	$uquery = "UPDATE notes SET note = $q$xmotd$q WHERE type = 'motd';";
	#echo "##".htmlentities($uquery)."##<p>";

	$result = pg_query($db, $uquery);
	$saved = 1;
	header("Location: home.php");
}

$query = "SELECT * FROM notes WHERE type = 'motd';";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
if ($num == 1) { $r = pg_fetch_array($result, 0, PGSQL_ASSOC); }

?>

<b>Edit Message Of The Day</b>

<form action=adminmotd.php method=post>
<input type=hidden name=xupdate value=1>
<table border=0 cellspacing=0 cellpadding=2>
<tr valign=top bgcolor="#CCCCFF">
<td><textarea name="xmotd" rows="10" cols="60"><?php echo htmlentities($r['note'])?></textarea></td>
</tr>
</table>
<p><input type=submit name=xbutton value=Save>
<?php
if ($saved) { echo "<font color=green><b>SAVED OK</b></font>"; }
?>
</form>

</BODY>
</HTML>
