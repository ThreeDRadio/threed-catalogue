<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - Edit Asset</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>
<BODY onload="document.forms[0].xname.focus()">

<?php
if (!$admin) {
	echo "<p><font color=red><b>Must be an Administrator to do that!</b></font><p>";
	echo "</BODY></HTML>";
	exit;
}

settype ($gid, "integer");
if ($xactive != 't') { $xactive = 'f'; }

$query = "SELECT * FROM bookingthing WHERE id = $q$gid$q;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
if ($num != 1) {
	echo "<p><font color=red><b>Asset does not exist!</b></font><p>";
	echo "</BODY></HTML>";
	exit;
}
$row = pg_fetch_array($result, 0, PGSQL_ASSOC);


if ($xupdate && $gid) {
	$xname = trim($xname);
	$uquery = "UPDATE bookingthing SET name=$q$xname$q, active=$q$xactive$q WHERE id = $q$gid$q;";
	$result = pg_query($db, $uquery);
	header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/assets.php");
	exit;
}


if ($gid) {
	$query = "SELECT * FROM bookingthing WHERE id = $q$gid$q;";
	$result = pg_query($db, $query);
	$row = pg_fetch_array($result, 0, PGSQL_ASSOC);
}
?>

<b>EDIT ASSET</b>

<p>
<form action=assetedit.php method=post>
<input type=hidden name=xupdate value=1>
<input type=hidden name=gid value="<?php echo "$gid"; ?>">
<table border=1 cellspacing=0 cellpadding=2>
<tr bgcolor="#AAAAFF">
<td><b>Name</b></td>
<td><input type=text name=xname value="<?php $a=htmlentities($row['name']); echo "$a"; ?>" size=30 maxlength=100></td>
</tr>
<tr bgcolor="#AAAAFF">
<td><b>Active</b></td>
<td>
<input type=radio id=2 name=xactive value=t<?php if ($row['active'] == 't') { echo " checked"; } ?>>Yes</input>
<input type=radio id=2 name=xactive value=f<?php if ($row['active'] != 't') { echo " checked"; } ?>>No</input>
</td>
</tr>
</table>
<p><input type="submit" name="xbutton" value="Save">
</form>

</BODY>
</HTML>
