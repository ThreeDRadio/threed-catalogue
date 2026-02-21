<?php require("verify.php");
#### User has logged in and been verified ####?>

<HTML>
<head>
<TITLE>ThreeD - Edit File Info</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>

<BODY>

<?php
settype ($xref, "integer");
settype ($xcat, "integer");
$query = "SELECT * FROM file WHERE id = $q$xref$q;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
if ($num == 1) { $r = pg_fetch_array($result, 0, PGSQL_ASSOC); }
else {
	echo "<p><font color=red><b>File does not exist!!</b></font><p>";
	echo "</body></html>";
	exit;
}
if (!$admin && $r[whouploaded] != $cid) {
	echo "<p><font color=red><b>You cannot edit other people's uploads</b></font><p>";
	echo "</body></html>";
	exit;
}

if (!$admin && $r[status] != 0) {
	echo "<p><font color=red><b>That file has been deleted</b></font><p>";
	echo "</body></html>";
	exit;
}


$wasupdated = 0;

if ($xdodelete) {
	$uquery = "UPDATE file SET
	status=1
	WHERE id = $q$xref$q;";
	$result = pg_query($db, $uquery);
	header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/files.php");
	exit;
}


if ($xdoundelete) {
	$uquery = "UPDATE file SET
	status=0
	WHERE id = $q$xref$q;";
	$result = pg_query($db, $uquery);
	header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/files.php");
	exit;
}


if ($xdoreallydelete && $admin) {
	$query = "SELECT * FROM file WHERE id = $q$xref$q;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num != 1) {
		echo "<p><font color=red><b>YIKES!</b></font><p>";
		echo "</BODY></HTML>";
		exit;
	}
	$r = pg_fetch_array($result, 0, PGSQL_ASSOC);
	$delfile = $r['id'];
	unlink ("$filestore$xref");
	$uquery = "DELETE FROM file WHERE id=$q$xref$q;";
	$uresult = pg_query($db, $uquery);
	header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/files.php");
}


if ($xupdate) {
	$xname = trim ($xname);
	$xname = preg_replace ("/[^A-Za-z0-9_.-]/", "-", $xname);
	$xdescription = trim ($xdescription);
	$uquery = "UPDATE file SET
	name=$q$xname$q,
	description=$q$xdescription$q,
	category=$q$xcat$q
	WHERE id = $q$xref$q;";
	$result = pg_query($db, $uquery);
	header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/files.php");
	exit;
}


$query = "SELECT * FROM file WHERE id = $q$xref$q;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
if ($num == 1) { $r = pg_fetch_array($result, 0, PGSQL_ASSOC); }
else {
	echo "<p><font color=red><b>File does not exist!!</b></font><p>";
	exit;
}


echo "<p><b>EDIT FILE INFO</b>";

?>

<p><table border=0 cellspacing=0 cellpadding=8>

<form action=fileedit.php method=post>

<tr bgcolor="#CCCCFF">
<td><b>Name</b></td>
<td><input type=text name=xname value="<?phpPHP $a=htmlentities($r[name]); echo "$a"; ?>" size=50 maxlength=100></td>
</tr>

<tr bgcolor="#CCFFCC" valign=top>
<td><b>Description</b></td>
<td><textarea name="xdescription" rows="7" cols="50"><?php echo htmlentities(stripslashes($r[description]))?></textarea></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Category</b></td>
<td>
<select name="xcat">
<option value=0>[NONE]</option>
<?php
$nquery = "SELECT * FROM filecat ORDER by UPPER(name), id;";
$nresult = pg_query($db, $nquery);
$nnum = pg_num_rows($nresult);
for ($i=0;$i<$nnum;$i++) {
	$nr = pg_Fetch_array($nresult, $i, PGSQL_ASSOC);
	if ($admin || $nr[active] == 't' || $nr['id'] == $r[category]) {
		echo "<option value=" . $nr['id'];
		if ($nr['id'] == $r[category]) { echo " selected"; }
		echo ">".htmlentities($nr[name])."</option>";
	}
}
?>
</select>
</td></tr>






</table>
<input type=hidden name=xref value=<?php=$xref?>>
<p><input type=submit name=xupdate value=Save>
<?php
if ($r[status] == 0) { echo "<input type=submit name=xdodelete value=Delete>"; }
else { echo "<input type=submit name=xdoundelete value=Undelete>"; }


if ($admin) echo "<input type=submit name=xdoreallydelete value=\"Erase Forever\">";


if ($wasupdated == 1) { echo "<font color=green><b> INFO WAS UPDATED</b></font>"; }
?>
</form>


</BODY>
</HTML>
