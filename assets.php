<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - Manage Assets</TITLE>
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

settype ($xid, "integer");

if ($togactive && $xid != $cid) {
	$query = "SELECT * FROM bookingthing WHERE id = $q$xid$q;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num == 1) {
		$row = pg_Fetch_array($result, 0, PGSQL_ASSOC);
		if ($row['active'] == 't') { $newin = 'f'; } else { $newin = 't'; }
		$uquery = "UPDATE bookingthing SET active = $q$newin$q WHERE id = $q$xid$q;";
		$uresult = pg_query($db, $uquery);
	}
}

echo "<b>ASSET LIST</b>";

$query = "SELECT * FROM bookingthing ORDER by UPPER(name), id;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
if ($num) {
	echo "<p>\n";
	echo "<p><TABLE border=1 cellpadding=2 cellspacing=0 bgcolor=#CCCCCC>\n";
	echo "<tr><td align=left><b>Name</b></td><td align=center><b>Active</b></td><td align=center><b>Action</b></td></tr>\n";
	for ($i=0;$i<$num;$i++) {
		echo "<TR valign=top bgcolor=#";
		if ($i % 2 == 0) { echo "AAAAFF"; } else { echo "CCCCFF"; }
		echo ">";
		$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
		
		$a = htmlentities($r['name']);
		echo "<td>";
		if ($a) { echo "$a"; }
		else { echo "&nbsp;"; }
		echo "</td>\n";
		
		$a = "no";
		if ($r['active'] == 't') { $a = "<font color=red>yes</font>"; }
		echo "<td align=center>";
		echo '<A HREF="assets.php?xid='.$r['id'].'&togactive=1'.'">'.$a.'</A>';
		echo "</td>\n";
				
		echo "<td align=center>";
		echo "<a HREF=assetedit.php?";
		echo 'gid=' . $r['id'] . ">Edit<a>";
		echo "</td></TR>\n";

		echo "</TR>\n";
	}
	echo "</TABLE>\n";
}
else { echo "<p><b><font color=red>NO ASSETS FOUND</font></b>\n"; }
?>

<p><B><a href=assetnew.php target=main>CREATE NEW ASSET</a></B>

</BODY>
</HTML>
