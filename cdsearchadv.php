<?php require("verify.php");
#### User has logged in and been verified ####

?>

<HTML>
<head>
<TITLE>ThreeD - MusicDB Adv Search</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>
<BODY onload="document.forms[0].xartist.focus()">

<B>MUSIC CATALOGUE - ADVANCED SEARCH</B>

<form action=cdsearchadv.php method=post>
<input type=hidden name=xdosearch value=1>

<p><table border=0 cellspacing=0 cellpadding=5>

<tr bgcolor="#CCCCCC">
<td align=right><b>Artist</b></td>
<td colspan=3><input type=text name=xartist value="<?php echo htmlentities(stripslashes($xartist))?>" size=50 maxlength=100></td>
<td align=right>
<input type=submit name=xbutton value=Search>
</td>
</tr>

<tr bgcolor="#CCCCCC">
<td align=right><b>Album Title</b></td>
<td colspan=4><input type=text name=xalbum value="<?php echo htmlentities(stripslashes($xalbum))?>" size=50 maxlength=100></td>
</tr>

<tr bgcolor="#CCCCCC">
<td align=right><b>Track Title</b></td>
<td colspan=4><input type=text name=xtrack value="<?php echo htmlentities(stripslashes($xtrack))?>" size=50 maxlength=100></td>
</tr>

<tr bgcolor="#CCCCCC">
<td align=right><b>Company</b></td>
<td colspan=4><input type=text name=xcompany value="<?php echo htmlentities(stripslashes($xcompany))?>" size=50 maxlength=100></td>
</tr>

<tr bgcolor="#CCCCCC">
<td align=right><b>Comments</b></td>
<td colspan=4><input type=text name=xcomments value="<?php echo htmlentities(stripslashes($xcomments))?>" size=50 maxlength=100></td>
</tr>

<tr bgcolor="#CCCCCC">
<td align=right><b>Created By</b></td>
<td colspan=4>
<select name="xcreated">
<option value=0>-</option>
<?php
$nquery = "SELECT DISTINCT ON (users.id) users.id, users.username, users.first, users.last
			FROM users, cd WHERE cd.createwho = users.id;";
$nresult = pg_query($db, $nquery);
$nnum = pg_num_rows($nresult);
for ($i=0;$i<$nnum;$i++) {
	$nr = pg_Fetch_array($nresult, $i, PGSQL_ASSOC);
	$id[$i] = $nr[id];
	$a = $nr[first];
	if ($nr[first] && $nr[last]) { $a .= " "; }
	$a .= $nr[last];
	if (!$a) { $a = $nr[username]; }
	$namex[$i] = htmlentities($a);
	$nameU[$i] = strtoupper($a);
}
array_multisort ($nameU, $namex, $id, SORT_ASC, SORT_STRING);
for ($i=0;$i<$nnum;$i++) {
	echo "<option value=" . $id[$i];
	if ($id[$i] == $xcreated) { echo " selected"; }
	echo ">$namex[$i]</option>";
}
?>
</select>
</td></tr>

<tr bgcolor="#CCCCCC">
<td align=right><b>Order By</b></td>
<td colspan=4>
<select name=xsort>
<option value=0<?php if ($xsort == 0) { echo " selected"; } ?>>Artist Alphabetical</option>
<option value=1<?php if ($xsort == 1) { echo " selected"; } ?>>Album Alphabetical</option>
<option value=2<?php if ($xsort == 2) { echo " selected"; } ?>>Most Recent First</option>
<option value=3<?php if ($xsort == 3) { echo " selected"; } ?>>Oldest First</option>
</select>
</td>
</tr>

<tr bgcolor="#CCCCCC">
<td align=center width=20%><b>Compilation</b><br>
<select name=xcompilation>
<option value=0<?php if ($xcompilation == 0) { echo " selected"; } ?>>-</option>
<option value=1<?php if ($xcompilation == 1) { echo " selected"; } ?>>Yes</option>
<option value=2<?php if ($xcompilation == 2) { echo " selected"; } ?>>No</option>
</select>
</td>

<td align=center width=20%><b>Demo</b><br>
<select name=xdemo>
<option value=0<?php if ($xdemo == 0) { echo " selected"; } ?>>-</option>
<option value=1<?php if ($xdemo == 1) { echo " selected"; } ?>>Yes</option>
<option value=2<?php if ($xdemo == 2) { echo " selected"; } ?>>No</option>
</select>
</td>

<td align=center width=20%><b>Local</b><br>
<select name=xlocal>
<option value=0<?php if ($xlocal == 0) { echo " selected"; } ?>>-</option>
<option value=1<?php if ($xlocal == 1) { echo " selected"; } ?>>Yes</option>
<option value=2<?php if ($xlocal == 2) { echo " selected"; } ?>>No</option>
</select>
</td>

<td align=center width=20%><b>Female</b><br>
<select name=xfemale>
<option value=0<?php if ($xfemale == 0) { echo " selected"; } ?>>-</option>
<option value=1<?php if ($xfemale == 1) { echo " selected"; } ?>>Yes</option>
<option value=2<?php if ($xfemale == 2) { echo " selected"; } ?>>No</option>
</select>
</td>

<td align=center width=20%><b>Status</b><br>
<select name=xstatus>
<option value=0<?php if ($xstatus == 0) { echo " selected"; } ?>>-</option>
<option value=1<?php if ($xstatus == 1) { echo " selected"; } ?>>Unchecked</option>
<option value=2<?php if ($xstatus == 2) { echo " selected"; } ?>>Incomplete</option>
<option value=3<?php if ($xstatus == 3) { echo " selected"; } ?>>Final</option>
</select>
</td></tr>

</table>

<?php

if ($xdosearch) {
	if ($xsort == 3) { $qsort = "arrivaldate"; }
	elseif ($xsort == 2) { $qsort = "arrivaldate"; }
	elseif ($xsort == 1) { $qsort = "UPPER(title), UPPER(artist)"; }
	else { $qsort = "UPPER(artist), UPPER(title)"; }
	
	$comments = preg_replace("/,/"," ",$xcomments);
	$comments = preg_replace("/ +/"," ",$comments);
	$comments = trim($comments);
	
	$query = "SELECT DISTINCT ON ($qsort, cd.id) cd.id as theid, * FROM cd, cdtrack";
	if ($comments) { $query .= ", cdcomment"; }
	$query .= " WHERE (cd.id = cdtrack.cdid)";
	if ($comments) { $query .= " AND (cd.id = cdcomment.cdid) AND (cdtrack.cdid = cdcomment.cdid)"; }
	
	$artist = preg_replace("/,/"," ",$xartist);
	$artist = preg_replace("/ +/"," ",$artist);
	$artist = trim($artist);
	if ($artist) {
		$artist = explode(" ", $artist);
		for ($i=0;$i<count($artist);$i++) {
			$query = $query . " AND (artist ~~* $q%$artist[$i]%$q OR trackartist ~~* $q%$artist[$i]%$q)";
		}
	}
	
	$album = preg_replace("/,/"," ",$xalbum);
	$album = preg_replace("/ +/"," ",$album);
	$album = trim($album);
	if (get_magic_quotes_gpc()) {
		$album = stripslashes($album);
	}
	if ($album) {
		$album = explode(" ", $album);
		for ($i=0;$i<count($album);$i++) {
			$album[$i] = pg_escape_string($album[$i]);
			echo $album[$i] . "<br>";
			$query = $query . " AND (title ~~* $q%$album[$i]%$q)";
		}
	}
	
	$track = preg_replace("/,/"," ",$xtrack);
	$track = preg_replace("/ +/"," ",$track);
	$track = trim($track);
	if ($track) {
		$track = explode(" ", $track);
		for ($i=0;$i<count($track);$i++) {
			$query = $query . " AND (tracktitle ~~* $q%$track[$i]%$q)";
		}
	}
	
	$company = preg_replace("/,/"," ",$xcompany);
	$company = preg_replace("/ +/"," ",$company);
	$company = trim($company);
	if ($company) {
		$company = explode(" ", $company);
		for ($i=0;$i<count($company);$i++) {
			$query = $query . " AND (company ~~* $q%$company[$i]%$q)";
		}
	}
	
	if ($comments) {
		$comments = explode(" ", $comments);
		for ($i=0;$i<count($comments);$i++) {
			$query = $query . " AND (cdcomment.comment ~~* $q%$comments[$i]%$q)";
		}
	}
	
	if ($xcompilation == 1) { $query = $query . "AND (compilation = 2)"; }
	if ($xcompilation == 2) { $query = $query . "AND (compilation = 1)"; }
	
	if ($xdemo == 1) { $query = $query . "AND (demo = 2)"; }
	if ($xdemo == 2) { $query = $query . "AND (demo = 1)"; }
	
	if ($xlocal == 1) { $query = $query . "AND (local > 1)"; }
	if ($xlocal == 2) { $query = $query . "AND (local = 1)"; }
	
	if ($xfemale == 1) { $query = $query . "AND (female > 1)"; }
	if ($xfemale == 2) { $query = $query . "AND (female = 1)"; }
	
	if ($xstatus == 1) { $query = $query . "AND (status <> 1 AND status <> 2)"; }
	if ($xstatus == 2) { $query = $query . "AND (status = 1)"; }
	if ($xstatus == 3) { $query = $query . "AND (status = 2)"; }
	
	settype ($xcreated, "integer");
	if ($xcreated) { $query = $query . "AND (cd.createwho = $xcreated)"; }
	
	if ($xsort == 2) { $query = $query . " ORDER BY " . $qsort . " DESC, cd.id DESC;"; }
	else { $query = $query . " ORDER BY " . $qsort . ";"; }
	#echo htmlentities($query);
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	echo "<p><table border=0 cellspacing=0 cellpadding=3>\n";
	echo "<tr><td><b>$num match";
	if ($num != 1) { echo "es"; }
	echo " found</b></td>";
	
	if (!$xmore && !$xless) { $xcursor = 1; }
	if ($xless) { $xcursor = $xcursor - 20; }
	if ($xmore) { $xcursor = $xcursor + 20; }
	$start = $xcursor;
	$end = $start + 19;
	if ($end > $num) { $end = $num; }
	if ($num > 20) {
		echo "<td><b>Showing matches $start to $end</b></td>";
	}
	if ($start > 20) { echo "<td><input type=submit name=xless value=Previous></td>"; }
	if ($num > $end) { echo "<td><input type=submit name=xmore value=Next></td>"; }
	
	
	echo "</tr></table>";
	if ($num) {
		echo "<input type=hidden name=xcursor value=$xcursor>";
		echo "<p>\n";
		echo "<p><TABLE border=1 cellpadding=2 cellspacing=0 bgcolor=#CCCCCC width=100%>\n";
		echo "<tr><th align=left>Artist</th><th align=left>Album Title</th><th align=center>Date Received</th><th align=center>Action</th></tr>\n";
		for ($i=$start-1;$i<$end;$i++) {
			echo "<TR valign=top bgcolor=#";
			if ($i % 2 == 0) { echo "CCFFCC"; } else { echo "CCCCFF"; }
			echo ">";
			$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
			
			$a = htmlentities($r[artist]);
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			$a = htmlentities($r[title]);
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			if ($r[arrivaldate] == "0001-01-01") { $a = ""; }
			else {
				$thedayN = strtotime($r[arrivaldate]);
				$a = date ("d/m/Y", $thedayN);
			}
			echo "<td align=center>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			
			
			echo "<td width=1 align=center>";
			echo "<a HREF=cdshow.php?";
			echo "xref=" . $r[theid] . ">Show<a>";
			
			if ($user[admin] == "t" || ($user[cdeditor] == "t" && $r[status] != 2)) {
				echo "&nbsp;";
				echo "<a HREF=cdedit.php?";
				echo "xref=" . $r[theid] . " target=_blank>Edit<a>";
			}
			
			echo "</td></TR>\n";

			echo "</TR>\n";
		}
		echo "</TABLE>\n";
	}
	#else { echo "<p><b><font color=red>NO MATCHES FOUND</font></b>\n"; }
}

?>

<?php pg_Close ($db); ?>
</form>
</BODY>
</HTML>










