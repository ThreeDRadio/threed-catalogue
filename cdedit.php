<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - MusicDB Edit</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>
<BODY onload="document.forms[0].xtext.focus()">

<?php
if (!$admin && $user[cdeditor] != "t") {
	echo "<p><font color=red><b>You do not have the necessary privileges to do that!</b></font><p>";
	echo "</BODY></HTML>";
	exit;
}

echo "<B>MUSIC CATALOGUE - EDIT ENTRY</B>";

settype ($xtr, "integer");
settype ($xref, "integer");

if ($xref) {
	$query = "SELECT * FROM cd WHERE id = $q$xref$q;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num != 1) {
		echo "<p><b>That Entry does not exist - $xref</b>";
		echo "</BODY>";
		echo "</HTML>";
		exit;
	}
	$r = pg_Fetch_array($result, 0, PGSQL_ASSOC);
	$xstatus = $r[status];
	if ($user[admin] != "t" && $r[status] == 2) {
		echo "<p><b>You cannot edit that Entry - $xref</b>";
		echo "</BODY>";
		echo "</HTML>";
		exit;
	}
}

if ($xdodelete && $user[admin] == "t") {
	echo "<form action=cdedit.php method=post>";
	echo "<input type=hidden name=xref value=$xref>";
	echo "<input type=hidden name=xreallydelete value=yes>";
	echo "<p><font color=red><b>Really Delete This Entry?</b></font>";
	echo "&nbsp;&nbsp;&nbsp;<input type=submit name=xdelyes value=\"Yes\">";
	echo "&nbsp;&nbsp;&nbsp;<input type=submit name=xdelno value=\"No\">";
	echo "</form>";
}

if ($xdelyes && $user[admin] == "t") {
	$dquery = "DELETE FROM cd WHERE id = $q$xref$q;";
	$dresult = pg_query($db, $dquery);
	$dquery = "DELETE FROM cdtrack WHERE cdid = $q$xref$q;";
	$dresult = pg_query($db, $dquery);
	echo "<p><b>Entry Deleted</b>";
	echo "</BODY>";
	echo "</HTML>";
	exit;
}

if ($xdosave || $xdoswap || $xdocreate) {
	$xartist = preg_replace ("/ +/", " ", trim($xartist));
	$xtitle =  preg_replace ("/ +/", " ", trim($xtitle));
	$xgenre =  preg_replace ("/ +/", " ", trim($xgenre));
	settype ($xnumtracks, "integer"); if ($xnumtracks < 0) { $xnumtracks = 0; }
	if (preg_match("|(.*)/(.*)/(.*)|", $xarrivaldate, $matches)) { $xarrivaldate = $matches[2]."/".$matches[1]."/".$matches[3]; }
	#if (preg_match("|([.|..])-(.*)-(.*)|", $xarrivaldate, $matches)) { $xarrivaldate = $matches[2]."/".$matches[1]."/".$matches[3]; }
	$temp = $xarrivaldate;
	$todayN = mktime (0,0,0,date("m"),date("d"),date("Y"));
	$thedayN = strtotime($xarrivaldate);
	if ($thedayN == -1 || $thedayN == "") { $thedayN = $todayN; }
//	if ($thedayN == -1) { $xarrivaldate = "0001-01-01"; }
	else { $xarrivaldate = date ("Y-m-d", $thedayN); }
//	if ($temp == "") { $xarrivaldate = "0001-01-01"; }
	settype ($xcopies, "integer");
	$xcompany = preg_replace ("/ +/", " ", trim($xcompany));
	$xcpa =     preg_replace ("/ +/", " ", trim($xcpa));
	settype ($xcompilation, "integer"); if ($xcompilation < 0 || $xcompilation > 2) { $xcompilation = 0; }
	settype ($xfemale, "integer"); if ($xfemale < 0 || $xfemale > 3) { $xfemale = 0; }
	settype ($xlocal, "integer"); if ($xlocal < 0 || $xlocal > 3) { $xlocal = 0; }
	settype ($xdemo, "integer"); if ($xdemo < 0 || $xdemo > 2) { $xdemo = 0; }
	settype ($xyear, "integer"); if ($xyear < 1000 || $xyear > 2100) { $xyear = 0; }
	settype ($xstatus, "integer"); if ($xstatus < 1 || $xstatus > 2) { $xstatus = 0; }
	settype ($xformat, "integer"); if ($xformat < 1 || $xformat > 7) { $xformat = 0; }

	$trackcount = count($xtrackartist);
	
	for ($i=0;$i<$trackcount;$i++) {
		if (preg_match("|(.*)[:\.;,](.*)[:\.;,](.*)|", $xtracklength[$i], $matches)) {
			settype ($matches[1], "integer");
			settype ($matches[2], "integer");
			settype ($matches[3], "integer");
			$seconds = $matches[1] * 60 * 60 + $matches[2] * 60 + $matches[3];
			$xtracklength[$i] = $seconds;
		}
		elseif (preg_match("|(.*)[:\.;,](.*)|", $xtracklength[$i], $matches)) {
			settype ($matches[1], "integer");
			settype ($matches[2], "integer");
			$seconds = $matches[1] * 60 + $matches[2];
			$xtracklength[$i] = $seconds;
		}
		else { settype ($xtracklength[$i], "integer"); }
	}
}
//Format text to save in database
if (get_magic_quotes_gpc()) {
		$xartist= stripslashes(pg_escape_string($xartist));
		$xtitle=  stripslashes(pg_escape_string($xtitle));
		$xcompany=stripslashes(pg_escape_string($xcompany));
		$xcpa=    stripslashes(pg_escape_string($xcpa));
		$xgenre=  stripslashes(pg_escape_string($xgenre));
}


if ($xdocreate) {
	$timenow = time();
	$uquery = "INSERT INTO cd (artist, title, year, genre, company,
			cpa, arrivaldate, copies, compilation, demo, local, female,
			createwho, createwhen, modifywho, modifywhen, comment, status, format)
			VALUES ($q$xartist$q, $q$xtitle$q, $q$xyear$q,
			$q$xgenre$q, $q$xcompany$q, $q$xcpa$q, $q$xarrivaldate$q,
			$q$xcopies$q, $q$xcompilation$q, $q$xdemo$q, $q$xlocal$q,
			$q$xfemale$q, $q$cid$q, $q$timenow$q, $q$cid$q, $q$timenow$q, $q$xcomment$q, $q$xstatus$q, $q$xformat$q);";
	//echo "Insert query is $uquery";
	//exit;
	$uresult = pg_query($db, $uquery);
	$lastoid = pg_last_oid($uresult);
	$kquery = "SELECT id FROM cd WHERE OID = $q$lastoid$q;";
	$kresult = pg_query($db, $kquery);
	$r = pg_fetch_array($kresult, 0, PGSQL_ASSOC);
	$xref = $r[id];
	$trackcount = count($xtrackartist);
	for ($i=0;$i<$trackcount;$i++) {
//		Format track artist & title to save in database
		$ytrackartist[$i]=pg_escape_string($xtrackartist[$i]);
		$ytracktitle[$i]=pg_escape_string($xtracktitle[$i]);
		$ii = $i+1;
		$ttquery = "INSERT INTO cdtrack (cdid, tracknum, tracktitle, trackartist, tracklength)
		VALUES ($xref, $ii,
		$q$ytracktitle[$i]$q,
		$q$ytrackartist[$i]$q,
		$q$xtracklength[$i]$q);";
		$ttresult = pg_query($db, $ttquery);
	}
	header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/cdshow.php?xref=".$xref);
}

if ($xdosave || $xdoswap) {
	$timenow = time();
	$uquery = "UPDATE cd SET
	artist=$q$xartist$q,
	title=$q$xtitle$q,
	year=$q$xyear$q,
	genre=$q$xgenre$q,
	company=$q$xcompany$q,
	cpa=$q$xcpa$q,
	arrivaldate=$q$xarrivaldate$q,
	copies=$q$xcopies$q,
	compilation=$q$xcompilation$q,
	demo=$q$xdemo$q,
	local=$q$xlocal$q,
	female=$q$xfemale$q,
	comment=$q$xcomment$q,
	modifywho=$q$cid$q,
	modifywhen=$timenow,
	status=$xstatus,
	format=$xformat
	WHERE id = $q$xref$q;";
	$uresult = pg_query($db, $uquery);
	
	$trackcount = count($xtrackartist);
	for ($i=0;$i<$trackcount;$i++) {
		$tquery = "UPDATE cdtrack SET
		tracktitle=$q$xtracktitle[$i]$q,
		trackartist=$q$xtrackartist[$i]$q,
		tracklength=$q$xtracklength[$i]$q
		WHERE trackid = $q$xtrackid[$i]$q;";
		#echo $tquery . "<p>";
		$tresult = pg_query($db, $tquery);
	}
	if ($xdosave) { header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/cdshow.php?xref=".$xref); }
}

if ($xdoswap) {
	$squery = "SELECT * FROM cdtrack WHERE cdid = $q$xref$q;";
	$sresult = pg_query($db, $squery);
	$snum = pg_num_rows($sresult);
	for ($i=0;$i<$snum;$i++) {
		$sr = pg_Fetch_array($sresult, $i, PGSQL_ASSOC);
		$title = addslashes($sr[tracktitle]);
		$artist = addslashes($sr[trackartist]);
		$id = $sr[trackid];
		$wquery = "UPDATE cdtrack SET tracktitle=$q$artist$q,
			trackartist=$q$title$q WHERE trackid = $q$id$q;";
		$wresult = pg_query($db, $wquery);
	}
}

if ($xref) {
	$query = "SELECT * FROM cd WHERE id = $q$xref$q;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num != 1) {
		echo "<p><b>PANIC!! - $xref</b>";
		echo "</BODY>";
		echo "</HTML>";
		exit;
	}
	$r = pg_Fetch_array($result, 0, PGSQL_ASSOC);
}

echo "<form action=cdedit.php method=post>";
echo "<input type=hidden name=xref value=$xref>";

if ($xref) {
	echo "<p><input type=submit name=xdosave value=\"Save Changes\">";
	if ($user[admin] == "t") {
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=xdodelete value=\"Delete Entry\">";
	}
}
else { echo "<p><input type=submit name=xdocreate value=\"Create Entry\">"; }


echo "<p><TABLE border=1 cellpadding=3 cellspacing=0 bgcolor=#CCCCFF>";

?>
<tr bgcolor="#CCCCFF">
<td><b>ID Number</b></td>
<td><?php $a=sprintf("%07.0f", $r[id]); echo "$a"; ?></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Artist</b></td>
<td><input type=text name=xartist value="<?php $a=htmlentities(stripslashes($r[artist])); echo "$a"; ?>" size=50 maxlength=150></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Title</b></td>
<td><input type=text name=xtitle value="<?php $a=htmlentities(stripslashes($r[title])); echo "$a"; ?>" size=50 maxlength=150></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Release Year</b></td>
<?php
$a = $r[year];
if (!$xref) { $a = date("Y"); }
if ($a == 0) { $a = ""; }
?>
<td><input type=text name=xyear value="<?php echo "$a" ?>" size=6 maxlength=150></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Genre</b></td>
<td><input type=text name=xgenre value="<?php $a=htmlentities(stripslashes($r[genre])); echo "$a"; ?>" size=50 maxlength=50></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Company</b></td>
<td><input type=text name=xcompany value="<?php $a=htmlentities(stripslashes($r[company])); echo "$a"; ?>" size=50 maxlength=150></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Country</b></td>
<td><input type=text name=xcpa value="<?php $a=htmlentities(stripslashes($r[cpa])); echo "$a"; ?>" size=50 maxlength=150></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Arrival Date</b></td>
<?php
//if ($r[arrivaldate] == "0001-01-01") { $a = ""; }
if ($r[arrivaldate] == -1 || $r[arrivaldate] == "") {
	$thedayN = $todayN;
	$a = date ("d/m/Y");
}
else {
	$thedayN = strtotime($r[arrivaldate]);
	$a = date ("d/m/Y", $thedayN);
}
?>
<td><input type=text name=xarrivaldate value="<?php echo htmlentities("$a")?>" size=20 maxlength=150></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Format</b></td>
<td>
<?php
$a = $r[format];
if (!$xref) { $a = 1; }
?>
<select name=xformat>
<option value=0<?php if ($a == 0) { echo " selected"; } ?>>Unknown</option>
<option value=1<?php if ($a == 1) { echo " selected"; } ?>>Compact Disc</option>
<option value=2<?php if ($a == 2) { echo " selected"; } ?>>7" Vinyl</option>
<option value=3<?php if ($a == 3) { echo " selected"; } ?>>12" Vinyl</option>
<option value=4<?php if ($a == 4) { echo " selected"; } ?>>Cassette</option>
<option value=5<?php if ($a == 5) { echo " selected"; } ?>>Reel</option>
<option value=6<?php if ($a == 6) { echo " selected"; } ?>>Minidisc</option>
<option value=7<?php if ($a == 7) { echo " selected"; } ?>>MP3</option>
</select>
</td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>Compilation</b></td><td>
<input type=radio id=2 name=xcompilation value=1<?php if ($r[compilation] == 1) { echo " checked"; } ?>>No</input>
<input type=radio id=2 name=xcompilation value=2<?php if ($r[compilation] == 2) { echo " checked"; } ?>>Yes</input>
</td></tr>

<tr bgcolor="#CCCCFF">
<td><b>Demo</b></td><td>
<input type=radio id=2 name=xdemo value=1<?php if ($r[demo] == 1) { echo " checked"; } ?>>No</input>
<input type=radio id=2 name=xdemo value=2<?php if ($r[demo] == 2) { echo " checked"; } ?>>Yes</input>
</td></tr>

<tr bgcolor="#CCCCFF">
<td><b>Local</b></td><td>
<input type=radio id=2 name=xlocal value=1<?php if ($r[local] == 1) { echo " checked"; } ?>>No</input>
<input type=radio id=2 name=xlocal value=2<?php if ($r[local] == 2) { echo " checked"; } ?>>Yes</input>
<input type=radio id=2 name=xlocal value=3<?php if ($r[local] == 3) { echo " checked"; } ?>>Some</input>
</td></tr>

<tr bgcolor="#CCCCFF">
<td><b>Female</b></td><td>
<input type=radio id=2 name=xfemale value=1<?php if ($r[female] == 1) { echo " checked"; } ?>>No</input>
<input type=radio id=2 name=xfemale value=2<?php if ($r[female] == 2) { echo " checked"; } ?>>Yes</input>
<input type=radio id=2 name=xfemale value=3<?php if ($r[female] == 3) { echo " checked"; } ?>>Some</input>
</td></tr>

<tr bgcolor="#CCCCFF">
<td><b>Copies</b></td>
<?php
$a=$r[copies];
if ($a == "") { $a = 1; }
if ($a == 0) { $a = ""; }
?>
<td><input type=text name=xcopies value="<?php echo "$a" ?>" size=5 maxlength=150></td>
</tr>

<?php
if ($user[admin] == "t") {
	echo "<tr bgcolor=#CCCCFF>";
	echo "<td><b>Status</b></td><td>";
	echo "<input type=radio id=2 name=xstatus value=0";
	if ($r[status] == 0) { echo " checked"; }
	echo ">Unchecked</input>";
	echo "<input type=radio id=2 name=xstatus value=1";
	if ($r[status] == 1) { echo " checked"; }
	echo ">Incomplete</input>";
	echo "<input type=radio id=2 name=xstatus value=2";
	if ($r[status] == 2) { echo " checked"; }
	echo ">Final</input>";
	echo "</td></tr>";
}
?>

<tr valign=top bgcolor="#CCCCFF">
<td><b>Editing<br>Comments</b></td><td>
<textarea name="xcomment" rows="4" cols="50"><?php echo htmlentities(stripslashes($r[comment]))?></textarea></td>
</td></tr>

<?php
echo "</TABLE>";

if ($xref) { echo "<p><input type=submit name=xdoswap value=\"Swap Track Artist and Track Title\">"; }

if ($xref) {
	$query = "SELECT * FROM cdtrack WHERE cdid = $q$xref$q ORDER by tracknum;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
}
else { $num = $xtr; }

echo "<p><TABLE border=1 cellpadding=3 cellspacing=0 bgcolor=#CCFFCC>";
echo "<TR bgcolor=#AADDAA><TD valign=top><b>Track<b></b></TD><TD valign=top><b>Artist</b></TD><TD valign=top><b>Title</b></TD><TD valign=top><b>Length</b></TD></TR>";

for ($i=0;$i<$num;$i++) {
	if ($xref) { $r = pg_Fetch_array($result, $i, PGSQL_ASSOC); }
	$ii = $i+1;
	echo "<TR><TD align=center>" . $ii . "</TD>";
	$a = htmlentities(stripslashes($r[trackartist]));
	$b = htmlentities(stripslashes($r[tracktitle]));
	$ttt = $r[tracklength];
	$min = 0;
	$min = floor($ttt/60);
	$sec = $ttt % 60;
	$c = sprintf("%1d", $min) . ":" . sprintf("%02d", $sec);
	if ($ttt == 0) { $c = ""; }
	echo "<TD><input type=text name=xtrackartist[] value=\"$a\" size=20 maxlength=150></TD>";
	echo "<TD><input type=text name=xtracktitle[] value=\"$b\" size=40 maxlength=150></TD>";
	echo "<TD><input type=text name=xtracklength[] value=\"$c\" size=6 maxlength=150></TD>";
	echo "<input type=hidden name=xtracknum[] value=\"$r[tracknum]\">";
	echo "<input type=hidden name=xtrackid[] value=\"$r[trackid]\">";
	echo "</TR>";
}

echo "</TABLE>";

if ($xref) {
	echo "<p><input type=submit name=xdosave value=\"Save Changes\">";
	if ($user[admin] == "t") {
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=xdodelete value=\"Delete Entry\">";
	}
}
else { echo "<p><input type=submit name=xdocreate value=\"Create Entry\">"; }

echo "</form>";
?>

</BODY>
</HTML>
