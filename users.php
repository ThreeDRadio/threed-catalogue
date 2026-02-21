<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - Users</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>
<BODY>

<?php
if (!$admin) {
	echo "<p><font color=red><b>Must be an Administrator to do that!</b></font><p>";
	echo "</BODY></HTML>";
	exit;
}

settype ($xid, "integer");

if ($togactive && $xid != $cid) {
	$query = "SELECT * FROM users WHERE id = $q$xid$q;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num == 1) {
		$row = pg_Fetch_array($result, 0, PGSQL_ASSOC);
		if ($row[active] == 't') { $newin = 'f'; } else { $newin = 't'; }
		$uquery = "UPDATE users SET active = $q$newin$q WHERE id = $q$xid$q;";
		$uresult = pg_query($db, $uquery);
	}
}

if ($togadmin && $xid != $cid) {
	$query = "SELECT * FROM users WHERE id = $q$xid$q;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num == 1) {
		$row = pg_Fetch_array($result, 0, PGSQL_ASSOC);
		if ($row[admin] == 't') { $admin = 'f'; } else { $admin = 't'; }
		$uquery = "UPDATE users SET admin = $q$admin$q, cdeditor = 't', adminbook = 't' WHERE id = $q$xid$q;";
		$uresult = pg_query($db, $uquery);
	}
}

if ($togcdeditor) {
	$query = "SELECT * FROM users WHERE id = $q$xid$q;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num == 1) {
		$row = pg_Fetch_array($result, 0, PGSQL_ASSOC);
		if ($row[cdeditor] == 't') { $newin = 'f'; } else { $newin = 't'; }
		if ($row[admin] == 't') { $newin = 't'; }
		$uquery = "UPDATE users SET cdeditor = $q$newin$q WHERE id = $q$xid$q;";
		$uresult = pg_query($db, $uquery);
	}
}

if ($togadminbook) {
	$query = "SELECT * FROM users WHERE id = $q$xid$q;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num == 1) {
		$row = pg_Fetch_array($result, 0, PGSQL_ASSOC);
		if ($row[adminbook] == 't') { $newin = 'f'; } else { $newin = 't'; }
		if ($row[admin] == 't') { $newin = 't'; }
		$uquery = "UPDATE users SET adminbook = $q$newin$q WHERE id = $q$xid$q;";
		$uresult = pg_query($db, $uquery);
	}
}


echo "<b>USER LIST</b>";

$query = "SELECT * FROM users";
$query = $query . " ORDER by UPPER(username);";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
if ($num) {
	echo "<p>\n";
	echo "<p><TABLE border=1 cellpadding=2 cellspacing=0 bgcolor=#CCCCCC>\n";
	echo "<tr valign=top><td align=left><b>Username</b></td><td align=left><b>First Name</b></td><td align=left><b>Last Name</b></td><td align=center><b>Active</b></td><td align=center><b>Admin</b></td><td align=center><b>CD Editor</b></td><td align=center><b>Booking<br>Admin</b></td><td align=center><b>Action</b></td></tr>\n";
	for ($i=0;$i<$num;$i++) {
		echo "<TR valign=top bgcolor=#";
		if ($i % 2 == 0) { echo "AAAAFF"; } else { echo "CCCCFF"; }
		echo ">";
		$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
		
		$a = htmlentities($r['username']);
		echo "<td>";
		if ($a) { echo "$a"; }
		else { echo "&nbsp;"; }
		echo "</td>\n";
		
		$a = htmlentities($r['first']);
		echo "<td>";
		if ($a) { echo "$a"; }
		else { echo "&nbsp;"; }
		echo "</td>\n";
		
		$a = htmlentities($r['last']);
		echo "<td>";
		if ($a) { echo "$a"; }
		else { echo "&nbsp;"; }
		echo "</td>\n";
		
		$a = "no";
		if ($r['active'] == 't') { $a = "<font color=red>yes</font>"; }
		echo "<td align=center>";
		echo '<A HREF="users.php?xid='.$r['id'].'&togactive=1'.'">'.$a.'</A>';
		echo "</td>\n";
		
		$a = "no";
		if ($r['admin'] == 't') { $a = "<font color=red>yes</font>"; }
		echo "<td align=center>";
		echo '<A HREF="users.php?xid='.$r['id'].'&togadmin=1'.'">'.$a.'</A>';
		echo "</td>\n";
		
		$a = "no";
		if ($r['cdeditor'] == 't') { $a = "<font color=red>yes</font>"; }
		echo "<td align=center>";
		echo '<A HREF="users.php?xid='.$r['id'].'&togcdeditor=1'.'">'.$a.'</A>';
		echo "</td>\n";
		
		$a = "no";
		if ($r['adminbook'] == 't') { $a = "<font color=red>yes</font>"; }
		echo "<td align=center>";
		echo '<A HREF="users.php?xid='.$r['id'].'&togadminbook=1'.'">'.$a.'</A>';
		echo "</td>\n";
		
		
		echo "<td align=center>";
		echo "<a HREF=adminuseredit.php?";
		echo 'gid=' . $r['id'] . ">Edit<a>";
		echo "</td></TR>\n";

		echo "</TR>\n";
	}
	echo "</TABLE>\n";
}
else { echo "<p><b><font color=red>NO USERS FOUND</font></b>\n"; }

?>

<p><B><a href=adminusernew.php target=main>CREATE NEW USER</a></B>

</BODY>
</HTML>
