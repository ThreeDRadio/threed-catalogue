<?php require("verify.php");
#### User has logged in and been verified ####?>

<HTML>
<head>
<TITLE>ThreeD - File Category List</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
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
	$query = "SELECT * FROM filecat WHERE id = $q$xid$q;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num == 1) {
		$row = pg_Fetch_array($result, 0, PGSQL_ASSOC);
		if ($row[active] == 't') { $newin = 'f'; } else { $newin = 't'; }
		$uquery = "UPDATE filecat SET active = $q$newin$q WHERE id = $q$xid$q;";
		$uresult = pg_query($db, $uquery);
	}
}

echo "<b>FILE CATEGORY LIST</b>";

$query = "SELECT * FROM filecat ORDER by UPPER(name), id;";
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
		echo '<A HREF="filecats.php?xid='.$r['id'].'&togactive=1'.'">'.$a.'</A>';
		echo "</td>\n";
				
		echo "<td align=center>";
		echo "<a HREF=filecatedit.php?";
		echo 'gid=' . $r['id'] . ">Edit<a>";
		echo "</td></TR>\n";

		echo "</TR>\n";
	}
	echo "</TABLE>\n";
}
else { echo "<p><b><font color=red>NO CATEGORIES FOUND</font></b>\n"; }
?>

<p><B><a href=filecatnew.php target=main>CREATE NEW CATEGORY</a></B>

</BODY>
</HTML>
