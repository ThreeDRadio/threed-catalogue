<?php
extract($_POST);
$xpassword = MD5($xpassword);

$db = pg_Connect ("host=localhost dbname=threed user=www password=tree");
if (!$db) { echo "Database Failure"; exit; }

if ($xlogin) { # trying to log in
	$q = "'";
	$query = "SELECT * FROM users WHERE username ~~* $q$xusername$q AND password = $q$xpassword$q;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num == 1) {
		$r = pg_fetch_array($result, 0, PGSQL_ASSOC);
		$id = $r['id'];
		$password = $r['password'];
		setcookie("threed_id", $id, 0, "/");
		setcookie("threed_password", $password, 0, "/");
		header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/");
		exit;
	}
}
?>

<HTML>
<HEAD>
<TITLE>ThreeD - Login</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</HEAD>
<BODY onload="document.forms[0].xusername.focus()">

<b>Three D Music Catalogue Login - St Peters</b>
<p>You need a username and password supplied by the Station to log in here.
<p>Note that Cookies must be enabled and accepted for logins to work.

<form method="post" action="login.php">
<p><table border=0 cellspacing=0 cellpadding=8>
<tr bgcolor="#CCCCFF">
<td><b>Username</b></td>
<td><input type=text name=xusername size=20 maxlength=50 value="">
</input></td>
<td></td>
</tr>
<tr bgcolor="#CCCCFF">
<td><b>Password</b></td>
<td><input type="password" name="xpassword" size="20" maxlength="50"></input></td>
<td><input type="submit" name="xlogin" value="login"></input></td>
</tr>
</table>
</form>

<?php if ($xlogin) { echo "<p><b><font color=red><b>Login Failed</b>"; } ?>

</BODY>
</HTML>
