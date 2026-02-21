<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - New List</TITLE>
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

$error = 0;
if ($xnew) {
	$xname = trim($xname);
	$uquery = "INSERT INTO listthing (name, active) VALUES ($q$xname$q, 't') RETURNING id;";
	$uresult = pg_query($db, $uquery);

	if ($uresult && pg_num_rows($uresult) > 0) { 
		$id_of_new_row = pg_fetch_row($uresult)[0];
		$kquery = "SELECT id FROM listthing WHERE id = $q$id_of_new_row$q;";
		$kresult = pg_query($db, $kquery);

		if ($kresult && pg_num_rows($kresult) > 0) { 
			header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/listsedit.php?gid=".$id_of_new_row);
			exit;
		}
	}

	// Just fall back to lists if any failure happens above.
	header("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/lists.php");
}
?>

<b>Create New List</b>
<p>
<form action=listsnew.php method=post>
<input type=hidden name=xnew value=1>
<table border=0 cellspacing=0 cellpadding=8>
<tr bgcolor="#CCCCCC">
<td><b>New List Name</b></td>
<td><input type=text name=xname value="<?php $a=htmlentities(stripslashes($orig)); echo "$a"; ?>" size=30 maxlength=100></td>
<td><input type="submit" name="xbutton" value="Create"></td>
</tr>
</table>
</form>

</BODY>
</HTML>
