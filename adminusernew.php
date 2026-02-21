<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - New User</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>
<BODY onload="document.forms[0].xusername.focus()">

<?php
if (!$admin) {
	echo "<p><font color=red><b>Must be an Administrator to do that!</b></font><p>";
	echo "</BODY></HTML>";
	exit;
}

$error = 0;
if ($xnew) {
	$orig = $xusername;
	$xusername = trim($xusername);
	$xusername = preg_replace("/[^A-Za-z]/","",$xusername);
	if (!$xusername || $xusername != $orig) {
		$error = "<p><font color=red><b>Not a Valid Username!</b></font>";
	}
	if (!$error) {
		$query = "SELECT * FROM users WHERE username ~~* $q$xusername$q;";
		$result = pg_query($db, $query);
		$num = pg_num_rows($result);
		if ($num) {
			$error = "<p><font color=red><b>That Username Already Exists!</b></font>";
		}
	}

	if (!$error) {
		$uquery = "INSERT INTO users (username, admin) VALUES ($q$xusername$q, 'f') RETURNING id;";
		$uresult = pg_query($db, $uquery);

		if ($uresult && pg_num_rows($uresult) > 0) {
			$id_of_new_row = pg_fetch_row($uresult)[0];
			$kquery = "SELECT id FROM users WHERE id = $q$id_of_new_row$q;";
			$kresult = pg_query($db, $kquery);

			if ($kresult && pg_num_rows($kresult) > 0) { 
				$kr = pg_fetch_array($kresult, 0, PGSQL_ASSOC);
				header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/adminuseredit.php?gid=".$kr['id']);
				exit;
			}
		}

		header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/users.php");
	}
}
?>

<b>Create New User</b>
<p>
<form action=adminusernew.php method=post>
<input type=hidden name=xnew value=1>
<table border=0 cellspacing=0 cellpadding=8>
<tr bgcolor="#CCCCCC">
<td><b>New Username</b></td>
<td><input type=text name=xusername value="<?php $a=htmlentities(stripslashes($orig)); echo $a; ?>" size=30 maxlength=50></td>
<td><input type="submit" name="xbutton" value="Create User"></td>
</tr>
</table>
</form>

<?php if ($error) { echo $error; } ?>

</BODY>
</HTML>
