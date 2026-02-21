<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - Home</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>
<BODY>

<?php
if ($user['first']) { $a = $user['first']; } else { $a = $user['username']; }
$a = htmlentities($a);
echo "<p><b>Hello $a</b>";

$mquery = "SELECT * FROM notes WHERE type = 'motd';";
$mresult = pg_query($db, $mquery);
$mnum = pg_num_rows($mresult);
if ($mnum == 1) {
	$mr = pg_Fetch_array($mresult, 0, PGSQL_ASSOC);
}

$motd = htmlentities($mr['note']);
$motd = preg_replace("/\n/","<br>",$motd);
echo "<p>$motd";
?>

</BODY>
</HTML>
