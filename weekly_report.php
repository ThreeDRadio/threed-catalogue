<?php require("verify.php");
#### User has logged in and been verified ####
settype ($xperpage, "integer");
if ($xperpage < 5) $xperpage = 100;
if ($xperpage > 100) $xperpage = 100;
if ($xdate == "") $xdate = date("Y-m-d");
?>
<HTML>
<head>
<TITLE>ThreeD - MusicDB Session Report</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>
<BODY onload="document.forms[0].xdate.focus()">

<B>MUSIC CATALOGUE - WEEKLY REPORT</B>

<form action=weekly_report.php method=post>
<input type=hidden name=xdosearch value=1>

<p><table border=0 cellspacing=0 cellpadding=5>

<tr bgcolor="#CCCCCC">
<td align=right><b>Enter start date of week to check (yyyy-mm-dd)</b></td>
<td colspan=3><input type=text name=xdate value="<?php echo htmlentities(stripslashes($xdate))?>" size=50 maxlength=100></td>
</td>
<td align=right>
<input type=submit name=xbutton value=Search>
</td>
</tr>

<b>Matches per Page</b>
<select name=xperpage>
<option value=100<?php if ($xperpage == 100) { echo " selected"; } ?>>100</option>
<option value=75<?php if ($xperpage == 75) { echo " selected"; } ?>>75</option>
<option value=50<?php if ($xperpage == 50) { echo " selected"; } ?>>50</option>
<option value=20<?php if ($xperpage == 20) { echo " selected"; } ?>>20</option>
<option value=10<?php if ($xperpage == 10) { echo " selected"; } ?>>10</option>
<option value=5<?php if ($xperpage == 5) { echo " selected"; } ?>>5</option>
</select>



</tr>
</td>
</tr>
</table>

<?php
$xwords = addslashes($xwords); #################

$pieces = explode("-", $xdate); //0=year,1=month,2=day
//echo "Year:",$pieces[0], " Month:",$pieces[1], " Day:",$pieces[2], "<br>";
$endofweek  = mktime(0, 0, 0, $pieces[1], $pieces[2]+7, $pieces[0]);
$xdate2= date ("Y-m-d", $endofweek);
//echo "<br>xdate2:", $xdate2, ":<br> date  :",$xdate,":<br><br>";

if ($xdosearch) {
	$qsort = "cd.id";
	$query ="SELECT cd.id, cd.artist, cd.title, users.username, cd.createwhen,
	'1970-01-01 00:00:00 GMT'::timestamp +
	((cd.createwhen::bigint)::text)::interval
	FROM cd, users
	WHERE '1970-01-01 00:00:00 GMT'::timestamp +
	((cd.createwhen::bigint)::text)::interval>=date'$xdate'
	AND  '1970-01-01 00:00:00 GMT'::timestamp +
	((cd.createwhen::bigint)::text)::interval<=date'$xdate2'
	AND cd.createwho = users.id
	GROUP BY cd.createwho, cd.id,cd.artist, cd.title, users.username,cd.createwhen
	ORDER BY cd.id;";
	//AND (cd.createwho = $user OR cd.modifywho = $user)
	//echo "<p>" . htmlentities($query) . "<p>";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	//Check for existance of mp3 files
	echo "<p><table border=0 cellspacing=0 cellpadding=3>\n";
	echo "<tr><td><b>$num match";
	if ($num != 1) { echo "es"; }
	echo " found</b></td>";
	echo '<tr bgcolor="#CCCCCC">';
	echo "<td>If you catalogue a CD with a data track (eg a video etc) and you include that track in the catalogue, make sure you put <b>[data]</b> at the begining of the track title, otherwise this page will show that the CD was not successfully MP3ed.</td></tr>";
	echo "<br> If a CD shows up as <i>Not MP3ed successfully</i>, click on the <i>Show</i> link to see which track(s) are missing."; 
	
	if (!$xmore && !$xless) { $xcursor = 1; }
	if ($xcursor < 1) $xcursor = 1;
	if ($xless) { $xcursor = $xcursor - $xperpage; }
	if ($xmore) { $xcursor = $xcursor + $xperpage; }
	if ($xcursor < 1) $xcursor = 1;
	if ($xcursor > $num) $xcursor = $num;
	$start = $xcursor;
	$end = $start + $xperpage - 1;
	if ($end > $num) { $end = $num; }
	if ($num > $xperpage) {
		echo "<td><b>Showing matches $start to $end</b></td>";
	}
	if ($start > 1) { echo "<td><input type=submit name=xless value=Previous></td>"; }
	if ($num > $end) { echo "<td><input type=submit name=xmore value=Next></td>"; }
	
	
	echo "</tr></table>";
	if ($num) {
		echo "<input type=hidden name=xcursor value=$xcursor>";
		echo "<p>\n";
		echo "<p><TABLE border=1 cellpadding=2 cellspacing=0 bgcolor=#CCCCCC width=100%>\n";
		echo "<tr><th align=left>ID Number</th><th align=left>Artist</th><th align=left>Album Title</th><th align=left>Processed By</th><th align=left>Date</th><th align=left>MP3</th><th align=center>Action</th></tr>\n";
		for ($i=$start-1;$i<$end;$i++) {
			echo "<TR valign=top bgcolor=#";
			if ($i % 2 == 0) { echo "CCFFCC"; } else { echo "CCCCFF"; }
			echo ">";
			$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
			
			$a = htmlentities($r[id]);
			echo "<td>";
			if ($a) { echo sprintf("%07.0f", $a); }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
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
			
			$a = htmlentities($r[username]);
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			$a = htmlentities($r[createwhen]);
			$a=date("Y-m-d", $a);
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			$a = htmlentities($r[id]);
			$a=wave_files_present($a,$db);
			echo "<td>";
			if ($a) { echo "OK"; }
			else { echo "<font color=red>Not MP3ed successfully</font>"; }
			echo "</td>\n";
			
			
			echo "<td width=1 align=center>";
			echo "<a HREF=trackshow.php?";
			echo "xref=" . $r[id] . ">Show<a>";
			
			echo "</td></TR>\n";

			echo "</TR>\n";
		}
		echo "</TABLE>\n";
	}
	#else { echo "<p><b><font color=red>NO MATCHES FOUND</font></b>\n"; }
}
?>

</form>
</BODY>
</HTML>

<?php
function wave_files_present($cdid,$dbname) {
	$wavepath = "/data/wavein";
	$mp3pathhi = "/data/music/hi";
	$trackquery = "SELECT * FROM cdtrack WHERE cdid = $q$cdid$q ORDER by tracknum;";
	$result = pg_query($dbname, $trackquery);
	$num = pg_num_rows($result);
	$cdnum = sprintf("%07.0f", $cdid);
	//echo "<br>$cdid:";
	$wavethere = 0;
	for ($i=0;$i<$num;$i++) {
		$istitle = 0;
		$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
		$tracknum[$i] = $r['tracknum'];
		$trackartist[$i] = $r['trackartist'];
		if ($trackartist[$i]) { $isartist = 1; }
		$tracktitle[$i] = $r['tracktitle'];
		$thetracknum = sprintf("%02.0f", $r['tracknum']);
		$data=strtolower($tracktitle[$i]);
		$data=strpos($tracktitle,"[data");
		if (strpos(strtolower ($tracktitle[$i]),"[data") === false) {
			if (is_readable("$wavepath/$cdnum/$cdnum-$thetracknum.wav")){$wavethere = 1; }
			else { if (is_readable("$mp3pathhi/$cdnum/$cdnum-$thetracknum.mp3")){
				$wavethere = 1; }
				else {$wavethere = 0;}
			}
			$playname[$i] = "$cdnum-$thetracknum";
		}
	}
	return $wavethere;
}
?>
