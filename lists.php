<?php require("verify.php");
#### User has logged in and been verified ####
?>

<HTML>
<head>
<TITLE>ThreeD - Lists</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>
<BODY onload="document.forms[0].xcontent.focus()">

<?php
settype ($xlid, "integer");
settype ($xadd, "integer");
settype ($xcreate, "integer");
settype ($xcursor, "integer");

#$admin = 0;

if ($admin && $xd) {
	settype ($xd, "integer");
	$query = "UPDATE list SET
	active='f' WHERE id = $q$xd$q;";
	$result = pg_query($db, $query);
}

if ($admin && $xud) {
	settype ($xd, "integer");
	$query = "UPDATE list SET
	active='t' WHERE id = $q$xud$q;";
	$result = pg_query($db, $query);
}

$query = "SELECT * FROM listthing WHERE id = '$xlid' AND active = 't';";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
if ($num == 1) {
	$r = pg_Fetch_array($result, 0, PGSQL_ASSOC);
	echo "<b>" . htmlentities($r['name']) . "</b>";
	$a = htmlentities($r['description']);
	$a = preg_replace("/\n/","<br>",$a);
	echo "<p>$a";
	if ($xcreate) {
		$now = time();
		$uquery = "INSERT INTO list (createwho, createwhen, content, active, listid)
				VALUES ('$cid', '$now', '$xcontent', 't', '$xlid');";
		$uresult = pg_query($db, $uquery);
	}
	if ($xadd) {
		echo "<form action=lists.php method=post>";
		echo "<input type=hidden name=xcreate value=1>";
		echo "<input type=hidden name=xlid value=$xlid>";
		echo "<textarea name=xcontent rows=12 cols=50></textarea>";
		echo '<p><input type="submit" name="xdocreate" value="Save">';
		echo "</form>";
	}
	
	
	
	
	else {   ####### DISPLAY PART OF LIST #########
		$query = "SELECT * FROM list WHERE listid = '$xlid'";
		if (!$admin) $query .= " AND active = 't'";
		$query .= "ORDER BY id;";
		$result = pg_query($db, $query);
		$num = pg_num_rows($result);
		
		$lquery = "SELECT messageid FROM listview WHERE who = '$cid' AND listid = '$xlid' ORDER by id;";
		$lresult = pg_query($db, $lquery);
		$lnum = pg_num_rows($lresult);
		$unread = 0;
		if ($lnum == 1) {
			$lr = pg_Fetch_array($lresult, 0, PGSQL_ASSOC);
			$upto = $lr['messageid'];
		}
		else $upto = 0;
		
		$screenlength = 10;
		
		if (!$xcursor) {
			$mquery = "SELECT count(*) FROM list WHERE listid = '$xlid' AND id > '$upto'";
			if (!$admin) $mquery .= " AND active = 't'";
			$mquery .= ";";
			$mresult = pg_query($db, $mquery);
			$mr = pg_Fetch_array($mresult, 0, PGSQL_ASSOC);
			$unread = $mr['count'];
			$xcursor = $num - $unread + 1;
		}
		if ($xcursor < 1) $xcursor = 1;
		if ($xprevious) { $xcursor = $xcursor - $screenlength; }
		if ($xnext) { $xcursor = $xcursor + $screenlength; }
		if ($xcursor < 1) $xcursor = 1;
		if ($xcursor > $num) $xcursor = $num;
		$start = ($xcursor - round(((($xcursor-1)/$screenlength) - floor(($xcursor-1)/$screenlength))*$screenlength));
		$end = $start + $screenlength - 1;
		if ($end > $num) { $end = $num; }
		
		echo "<p><TABLE border=1 cellpadding=4 cellspacing=0 bgcolor=#EEEEEE>";
		echo "<tr>";
		echo "<td><b><A HREF=lists.php?xadd=1&xlid=$xlid>Add Item</A></b></td>";
		#echo "<td><b><A HREF=lists.php?xsearch=1&xlid=$xlid>Search</A></b></td>";
		if ($start > 1) echo "<td><b><A HREF=lists.php?xlid=$xlid&xprevious=1&xcursor=$xcursor>Previous</A></b></td>";
		else echo "<td><b>Previous</b></td>";
		if ($end < $num) echo "<td><b><A HREF=lists.php?xlid=$xlid&xnext=1&xcursor=$xcursor>Next</A></b></td>";
		else echo "<td><b>Next</b></td>";
		echo "<td><b>$start-$end of $num</b></td>";
		echo "</tr>";
		echo "</TABLE>";
		if ($num) {
			echo "<p><TABLE border=1 cellpadding=4 cellspacing=0 bgcolor=#EEEEEE width=100%>";
			for ($i=$start-1;$i<$end;$i++) {
				$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
				$id = $r['id'];
				if ($r['active'] != 't') echo "<tr bgcolor=#FF9999>";
				elseif ($id > $upto) echo "<tr bgcolor=#99FF99>";
				else echo "<tr>";
				echo "<td><TABLE border=0 cellpadding=0 cellspacing=0 width=100%><tr><td>";
				echo "<b><font color=AA0000>";
				echo date ("g:ia j M Y", $r['createwhen']);
				echo " (" . $name[$r['createwho']] . ")";
				echo "</font></b>";
				echo "</td>";
				if ($admin) {
					echo "<td align=right><b>";
					if ($r['active'] == 't')  echo "<A HREF=lists.php?xlid=$xlid&xcursor=$xcursor&xd=$id>Delete</A>";
					else echo "<A HREF=lists.php?xlid=$xlid&xcursor=$xcursor&xud=$id>Undelete</A>";
					echo "</b></td>";
				}
				echo "</tr></table>";
				$a = htmlentities($r['content']);
				$a = preg_replace("/\n/","<br>",$a);
				echo "<p>$a";
				echo "</td></tr>";
			}
			echo "</TABLE>";
			if ($id > $upto) {
				$dquery = "DELETE FROM listview WHERE who = '$cid' AND listid = '$xlid';";
				$dresult = pg_query($db, $dquery);
				$uquery = "INSERT INTO listview (who, listid, messageid)
						VALUES ('$cid', '$xlid', '$id');";
				$uresult = pg_query($db, $uquery);
			}
			echo "<p><TABLE border=1 cellpadding=4 cellspacing=0 bgcolor=#EEEEEE>";
			echo "<tr>";
			echo "<td><b><A HREF=lists.php?xadd=1&xlid=$xlid>Add Item</A></b></td>";
			#echo "<td><b><A HREF=lists.php?xsearch=1&xlid=$xlid>Search</A></b></td>";
			if ($start > 1) echo "<td><b><A HREF=lists.php?xlid=$xlid&xprevious=1&xcursor=$xcursor>Previous</A></b></td>";
			else echo "<td><b>Previous</b></td>";
			if ($end < $num) echo "<td><b><A HREF=lists.php?xlid=$xlid&xnext=1&xcursor=$xcursor>Next</A></b></td>";
			else echo "<td><b>Next</b></td>";
			echo "<td><b>$start-$end of $num</b></td>";
			echo "</tr>";
			echo "</TABLE>";
		}
		else echo "<p><b><font color=red>NO ENTRIES FOUND</font></b>";
	}
}
else { $xlid = ""; $xadd = ""; }



if (!$xlid && !$xadd) {
	echo "<b> ALL LISTS</b>";
	$query = "SELECT * FROM listthing WHERE active = 't' ORDER by id;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num) {
		echo "<p>\n";
		echo "<p><TABLE border=1 cellpadding=4 cellspacing=0 bgcolor=#CCCCCC>";
		for ($i=0;$i<$num;$i++) {
			echo "<TR valign=top bgcolor=#";
			echo "DDDDDD";
			echo "><td>";
			$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
			
			$a = htmlentities($r['name']);
			echo "<b><A HREF=lists.php?xlid=$r[id]>";
			if ($a) { echo "$a"; } else { echo "UNKNOWN"; }
			echo "";
			
			$lquery = "SELECT messageid FROM listview WHERE who = '$cid' AND listid = '$r[id]' ORDER by id;";
			$lresult = pg_query($db, $lquery);
			$lnum = pg_num_rows($lresult);
			$upto = 0;
			$unread = 0;
			if ($lnum == 1) {
				$lr = pg_Fetch_array($lresult, 0, PGSQL_ASSOC);
				$upto = $lr['messageid'];
			}
			$mquery = "SELECT count(*) FROM list WHERE listid = '$r[id]' AND id > '$upto'";
			if (!$admin) $mquery .= " AND active = 't'";
			$mquery .= ";";
			$mresult = pg_query($db, $mquery);
			$mr = pg_Fetch_array($mresult, 0, PGSQL_ASSOC);
			$unread = $mr['count'];
			echo " ($unread unread)";
			
			echo "</A></b><br>";
			$a = htmlentities($r['description']);
			$a = preg_replace("/\n/","<br>",$a);
			echo "$a";
			echo "</td></TR>";
		}
		echo "</TABLE>";
	}
	else { echo "<p><b><font color=red>NO LISTS FOUND</font></b>\n"; }
}
?>

</BODY>
</HTML>
