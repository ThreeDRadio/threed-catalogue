<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - MusicDB Stats</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>
<BODY onload="document.forms[0].xtext.focus()">

<?php
if (!$admin && $user[cdeditor] != "t") {
	echo "<p><font color=red><b>You do not have the necessary privilages to do that!</b></font><p>";
	echo "</BODY></HTML>";
	exit;
}
?>

<B>MUSIC CATALOGUE - STATS</B>

<p><b>Number of Records in Catalogue</b>
<br><table border=1 cellspacing=0 cellpadding=3>
<tr><td>Number of Entries</td>
<td align=right>
<?php
$query = "SELECT count(*) FROM cd";
$result = pg_query($db, $query);
$r = pg_Fetch_array($result, 0, PGSQL_ASSOC);
echo $r[count];
?>
</td></tr>
<tr><td>Number of Tracks</td>
<td align=right>
<?php
$query = "SELECT count(*) FROM cdtrack";
$result = pg_query($db, $query);
$r = pg_Fetch_array($result, 0, PGSQL_ASSOC);
echo $r[count];
?>
</td></tr>
<tr><td>Total Number of Comments</td>
<td align=right>
<?php
$query = "SELECT count(*) FROM cdcomment";
$result = pg_query($db, $query);
$r = pg_Fetch_array($result, 0, PGSQL_ASSOC);
echo $r[count];
?>
</td></tr>
<tr><td>Number of Entries with Comments</td>
<td align=right>
<?php
$query = "SELECT DISTINCT ON (cd.id) count(cd.id) FROM cd, cdcomment WHERE cd.id = cdcomment.cdid GROUP BY cd.id;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
echo $num;
?>
</td></tr>
</table>

<p><b>Number of Entries Created by User</b>
<br><table border=1 cellspacing=0 cellpadding=3>
<tr><td><b>User</b></td><td align=right><b>Number</b></td></tr>
<?php
$query = "SELECT createwho, count(*) FROM cd GROUP BY createwho ORDER BY count(*) DESC";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
for ($i=0;$i<$num;$i++) {
	$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
	$uquery = "SELECT * FROM users WHERE id = $r[createwho];";
	$uresult = pg_query($db, $uquery);
	$ur = pg_Fetch_array($uresult, 0, PGSQL_ASSOC);
	$a = $ur[first];
	if ($ur[first] && $ur[last]) { $a .= " "; }
	$a .= $ur[last];
	if (!$a) { $a = $ur[username]; }
	$a = htmlentities($a);
	echo "<tr><td>$a</td><td align=right>$r[count]</td></tr>";
}
?>
</table>


<p><b>Number of Tracks for Entries</b>
<br><table border=1 cellspacing=0 cellpadding=3>
<tr><td><b>Tracks</b></td><td align=right><b>Number</b></td></tr>
<?php
$query = "SELECT count(cdtrack.cdid) FROM cd LEFT OUTER JOIN cdtrack ON cd.id = cdtrack.cdid
		GROUP BY cd.id;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
for ($i=0;$i<$num;$i++) {
	$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
	$tt[$r[count]]++;
}
ksort($tt);
foreach ($tt as $xt => $xc) {
	echo "<tr><td>$xt</td><td align=right>$xc</td></tr>";
}
?>
</table>

<p><b>Number of Entries Created by Date</b>
<br><table border=1 cellspacing=0 cellpadding=3>
<tr><td><b>Date</b></td><td align=right><b>Number</b></td></tr>
<?php
$query = "SELECT createwhen FROM cd";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
for ($i=0;$i<$num;$i++) {
	$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
	$a = date ("d/m/Y", $r[createwhen]);
	$b = date ("Y/m/d", $r[createwhen]);
	$d[$a]++;
	$c[$b] = $b;
}
array_multisort ($c, SORT_STRING, $d, SORT_STRING);
foreach ($d as $date => $count) {
	echo "<tr><td>$date</td><td align=right>$count</td></tr>";
}
?>
</table>

</BODY>
</HTML>
