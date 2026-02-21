<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - Password</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>
<BODY onload="document.forms[0].xpassword0.focus()">

<?php
if ($xupdate) {
	$error = "";
	$pwshort = "";
	$pwmismatch = "";
	$passwordchanged = "";
	$xpassword0 = MD5($xpassword0);
	if ($xpassword0 != $user['password']) { $error .= "<p><font color=red><b>Old password is not correct</b></font>"; }
	if ($xpassword1 != $xpassword2) { $error .= "<p><font color=red><b>New passwords do not match</b></font>"; }
	if (strlen ($xpassword1) < 5 || strlen ($xpassword2) < 5) { $error .= "<p><font color=red><b>New password is too short (5 characters min)</b></font>"; }
	if (!$error) {
		$xpassword1 = MD5($xpassword1);
		$uquery = "UPDATE users SET password=$q$xpassword1$q WHERE id = $q$cid$q";
		$cpassword = $xpassword1;
		$passwordchanged = 1;
		$result = pg_query($db, $uquery);
		setcookie("threed_password", $cpassword, 0, "/");
	}
}
?>

<b>Change Your Password</b>
<p>

<form action=setpassword.php method=post>
<input type=hidden name=xupdate value=1>
<input type=hidden name=gid value="<?php echo "$gid"; ?>">
<table border=1 cellspacing=0 cellpadding=2>

<tr bgcolor="#CCCCFF">
<td><b>Old Password</b></td>
<td><input type=password name=xpassword0 value="" size=30 maxlength=50>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>New Password</b></td>
<td><input type=password name=xpassword1 value="" size=30 maxlength=50>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Retype&nbsp;Password</b></td>
<td><input type=password name=xpassword2 value="" size=30 maxlength=50>
</tr>

</table>
<p><input type="submit" name="xbutton" value="Save">

</form>

<?php
if ($error) { echo $error . "<p><font color=red><b>YOUR PASSWORD HAS NOT BEEN CHANGED</b></font>"; }
if ($passwordchanged) { echo "<font color=green><b>YOUR PASSWORD HAS BEEN CHANGED</b>"; }
?>

</BODY>
</HTML>
