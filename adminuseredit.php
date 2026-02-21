<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - Edit Users</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>

<BODY onload="document.forms[0].xmotd.focus();document.forms[0].xmotd.select()">

<?php
if (!$admin) {
	echo "<p><font color=red><b>Must be an Administrator to do that!</b></font><p>";
	echo "</BODY></HTML>";
	exit;
}

settype ($gid, "integer");
$query = "SELECT * FROM users WHERE id = $q$gid$q;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
if ($num != 1) {
	echo "<p><font color=red><b>User does not exist!</b></font><p>";
	echo "</BODY></HTML>";
	exit;
}
$row = pg_fetch_array($result, 0, PGSQL_ASSOC);

if ($xactive != 't') { $xactive = 'f'; }
if ($xadmin != 't') { $xadmin = 'f'; }
if ($xcdeditor != 't') { $xcdeditor = 'f'; }
if ($xadminbook != 't') { $xadminbook = 'f'; }
if ($gid == $cid) { $xactive = 't'; $xadmin = 't'; $xcdeditor = 't'; }
if ($xadmin == 't') { $xcdeditor = 't'; $xadminbook = 't'; }


if ($xupdate && $gid) {
	$error = "";
	$pwshort = "";
	$pwmismatch = "";
	$passwordchanged = "";
	$orig = $xusername;
	$xusername = trim($xusername);
	$xusername = preg_replace("/[^A-Za-z0-9]/","",$xusername);
	if (!$xusername || $xusername != $orig) {
		$error .= "<p><font color=red><b>Not a Valid Username!</b></font>";
	}
	if (!$error) {
		$query = "SELECT * FROM users WHERE username ~~* $q$xusername$q AND id <> $gid;";
		$result = pg_query($db, $query);
		$num = pg_num_rows($result);
		if ($num) {
			$error .= "<p><font color=red><b>That Username Already Exists!</b></font>";
		}
	}
	if (($xpassword1 || $xpassword2) && strlen ($xpassword1) < 4)
		{ $pwshort = 1; $error .= "<p><font color=red><b>Password must be at least 5 characters long - Password Not Changed</b></font>"; }
	if (($xpassword1 || $xpassword2) && $xpassword1 != $xpassword2)
		{ $pwmismatch = 1; $error .= "<p><font color=red><b>Entered passwords do not match - Password Not Changed</b></font>"; }
	$uquery = "UPDATE users SET ";
	if ($xpassword1 && !$pwshort && !$pwmismatch) {
		$xpassword1 = MD5($xpassword1);
		$uquery = $uquery . "password=$q$xpassword1$q, ";
		$cpassword = $xpassword1;
		$passwordchanged = 1;
	}
	if (!$error) { $uquery = $uquery . "username=$q$xusername$q, "; }
	$uquery = $uquery . "
	first=$q$xfirst$q,
	last=$q$xlast$q,
	active=$q$xactive$q,
	admin=$q$xadmin$q,
	cdeditor=$q$xcdeditor$q,
	adminbook=$q$xadminbook$q
	WHERE id = $q$gid$q;";
	$result = pg_query($db, $uquery);
	if (!$error) { header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/users.php"); }
}
?>

<b>Admin Edit User</b>

<p>
<form action=adminuseredit.php method=post>
<input type=hidden name=xupdate value=1>
<input type=hidden name=gid value="<?php echo "$gid"; ?>">
<table border=1 cellspacing=0 cellpadding=2>

<tr bgcolor="#AAAAFF">
<td><b>Username</b></td>
<td><input type=text name=xusername value="<?php $a=htmlentities($row['username']); echo "$a"; ?>" size=30 maxlength=50></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>First Name</b></td>
<td><input type=text name=xfirst value="<?php $a=htmlentities($row['first']); echo "$a"; ?>" size=30 maxlength=50></td>
</tr>

<tr bgcolor="#AAAAFF">
<td><b>Last Name</b></td>
<td><input type=text name=xlast value="<?php $a=htmlentities($row['last']); echo "$a"; ?>" size=30 maxlength=50></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>New Password</b></td>
<td><input type=password name=xpassword1 value="" size=30 maxlength=50>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Retype&nbsp;Password</b></td>
<td><input type=password name=xpassword2 value="" size=30 maxlength=50>
</tr>

<tr bgcolor="#AAAAFF">
<td><b>Active</b></td>
<td>
<input type=radio id=2 name=xactive value=t<?php if ($row['active'] == 't') { echo " checked"; } ?>>Yes</input>
<input type=radio id=2 name=xactive value=f<?php if ($row['active'] != 't') { echo " checked"; } ?>>No</input>
</td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Admin</b></td>
<td>
<input type=radio id=2 name=xadmin value=t<?php if ($row['admin'] == 't') { echo " checked"; } ?>>Yes</input>
<input type=radio id=2 name=xadmin value=f<?php if ($row['admin'] != 't') { echo " checked"; } ?>>No</input>
</td>
</tr>

<tr bgcolor="#AAAAFF">
<td><b>CD Editor</b></td>
<td>
<input type=radio id=2 name=xcdeditor value=t<?php if ($row['cdeditor'] == 't') { echo " checked"; } ?>>Yes</input>
<input type=radio id=2 name=xcdeditor value=f<?php if ($row['cdeditor'] != 't') { echo " checked"; } ?>>No</input>
</td>
</tr>

<tr bgcolor="#AAAAFF">
<td><b>Booking Admin</b></td>
<td>
<input type=radio id=2 name=xadminbook value=t<?php if ($row['adminbook'] == 't') { echo " checked"; } ?>>Yes</input>
<input type=radio id=2 name=xadminbook value=f<?php if ($row['adminbook'] != 't') { echo " checked"; } ?>>No</input>
</td>
</tr>

</table>
<p><input type="submit" name="xbutton" value="Save">

</form>

<?php if ($error) { echo $error; } ?>

</BODY>
</HTML>
