<?php require("verify.php");
#### User has logged in and been verified ####
settype ($xperpage, "integer");
//echo "Date:$xdate";

if ($xdate == "")  $xdate = date("Y-m-d");
if ($xdate2 == "") $xdate2 = date ("Y-m-d");
if ($xperpage < 5) $xperpage = 200;
if ($xperpage > 200) $xperpage = 200;
if ($sdate =="") $sdate=mod;
####Get username
if (!$xname) { $xname = $user[username]; }
$xname = htmlentities($xname);
//echo $xname;
?>
<HTML>
<head>
<TITLE>ThreeD - MusicDB Session Report</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>
<BODY onload="document.forms[0].xdate.focus()">

<B>MUSIC CATALOGUE - SESSION REPORT</B>

<form action=session_report2.php method=post>
<input type=hidden name=xdosearch value=1>

<p><table border=0 cellspacing=0 cellpadding=5>

<tr>
	<td>
	<font color=red>The session report now has added functionality - the default settings are the same as before, except it only shows your items - replace your name with * to see everyone's items&nbsp;</font>
	</td>
	<td></td>
</tr>

<tr bgcolor="#CCCCCC">
	<td align=left><b>Enter dates to check&nbsp;(yyyy-mm-dd)&nbsp;From:&nbsp;</b><input type=text name=xdate value="<?php echo htmlentities(stripslashes($xdate))?>" size=25 maxlength=50>
	<b>&nbsp;To:&nbsp;</b><input type=text name=xdate2 value="<?php echo htmlentities(stripslashes($xdate2))?>" size=25 maxlength=50>
	</td>
	<td></td>
</tr>
<tr bgcolor="#CCCCCC">
	<td align=left><b>Show items for this user only:&nbsp;</b>
	<input type=text name=xname value="<?php $a=htmlentities(stripslashes($xname)); echo "$a"; ?>" size=25 maxlength=50><b>&nbsp;(use * to view all users)</b>
	</td>
	<td></td>
</tr>
<tr bgcolor="#CCCCCC">
	<td align=left><b>Date to search:&nbsp;</b>
	<select name=sdate>
	<!--<option value="any"<?php //if ($sdate == "any") { echo " selected"; } ?>>Any Type</option>-->
	<option value="mod"<?php if ($sdate == "mod") { echo " selected"; } ?>>Date Modified</option>
	<option value="cre"<?php if ($sdate == "cre") { echo " selected"; } ?>>Date Created</option>
	<option value="arr"<?php if ($sdate == "arr") { echo " selected"; } ?>>Date Arrived</option>
	</select>
	
	<b>&nbsp;&nbsp;Matches per Page:&nbsp;</b>
	<select name=xperpage>
	<option value=200<?php if ($xperpage == 200) { echo " selected"; } ?>>200</option>
	<option value=100<?php if ($xperpage == 100) { echo " selected"; } ?>>100</option>
	<option value=50<?php if ($xperpage == 50) { echo " selected"; } ?>>50</option>
	<option value=25<?php if ($xperpage == 25) { echo " selected"; } ?>>25</option>
	<option value=10<?php if ($xperpage == 10) { echo " selected"; } ?>>10</option>
	<option value=5<?php if ($xperpage == 5) { echo " selected"; } ?>>5</option>
	</select>
	</td>
	<td align=right><input type=submit name=xbutton value=Search>
	</td>
</tr>
<!--<tr bgcolor="#CCCCCC">
	<td>
	<font color=red>&nbsp;NB Date Arrived will only work if the Arrival Date is filled out correctly for each CD!!&nbsp;</font>
	</td>
	<td></td>
</tr>-->
</table>

<?php
$xwords = addslashes($xwords); #################

$pieces = explode("-", $xdate2); //0=year,1=month,2=day
//echo "Year:",$pieces[0], " Month:",$pieces[1], " Day:",$pieces[2], "<br>";
$nextday = mktime(0, 0, 0, $pieces[1], $pieces[2]+1, $pieces[0]);
$enddate = date ("Y-m-d", $nextday);
//echo "<br>xdate2:", $xdate2, ":<br> date  :",$xdate,":<br><br>";*/

//Type of date to use:
if ($sdate=="any")
	{$when="any";
	echo " using any ";
	$who = "cd.createwho";
	$head = "Created By";}
else if ($sdate=="mod")
	{$when = "modifywhen";
	$who = "cd.modifywho";
	$head = "Modified By";}
else if ($sdate=="cre")
	{$when = "createwhen";
	$who = "cd.createwho";
	$head = "Created By";}
else if ($sdate=="arr")
	{$when = "arrivaldate";
	$who = "cd.createwho";
	$head = "Created By";}
//echo " when is $when ";

if ($xdosearch) {
	$qsort = "cd.id";
	if ($when=="arrivaldate") {
		//Use arrivaldate (field type is date)
		$where="WHERE ((cd.$when>=date'$xdate'
		AND  cd.$when<=date'$enddate')
		AND $who = users.id )";
	}
	else {
		//use either modifydate or createdate (field type is bigint)
		$where="WHERE (('1970-01-01 00:00:00 GMT'::timestamp +
		((cd.$when::bigint)::text)::interval>=date'$xdate'
		AND  '1970-01-01 00:00:00 GMT'::timestamp +
		((cd.$when::bigint)::text)::interval<=date'$enddate')
		AND $who = users.id )";
	}
	if ($xname!="*") {
		$where = $where."AND users.username = '$xname' ";
	}
//modified here---------->
//	$query ="SELECT cd.id, cd.artist, cd.title, cd.local, cd.format, users.username, cd.$when FROM cd, users ";
	$query ="SELECT cd.id, cd.artist, cd.title, cd.local, cd.format, users.username, cd.arrivaldate, ('1970-01-01 00:00:00 GMT'::timestamp + ((cd.createwhen::bigint)::text)::interval), cd.modifywhen FROM cd, users ";
	$query=$query.$where;
//	$query = $query."GROUP BY $who, cd.id, cd.artist, cd.title, cd.local, cd.format, users.username, cd.$when ORDER BY cd.id;";
	$query = $query."GROUP BY $who, cd.id, cd.artist, cd.title, cd.local, cd.format, users.username, cd.$when, cd.arrivaldate, cd.createwhen, cd.modifywhen ORDER BY cd.id;";
	echo "<p>" . htmlentities($query) . "<p>";

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
//		echo "<tr><th align=left>ID Number</th><th align=left>Artist</th><th align=left>Album Title</th><th align=left>$head</th><th align=left>Date</th><th align=left>MP3</th><th align=left>Local</th><th align=left>Format</th><th align=center>Action</th></tr>\n";
		echo "<tr><th align=left>ID Number</th><th align=left>Artist</th><th align=left>Album Title</th><th align=left>$head</th><th align=left>Date</th><th align=left>ArrivalDate</th><th align=left>CreateDate</th><th align=left>ModifyDate</th><th align=left>MP3</th><th align=left>Local</th><th align=left>Format</th><th align=center>Action</th></tr>\n";
		for ($i=$start-1;$i<$end;$i++) {
			echo "<TR valign=top bgcolor=#";
			if ($i % 2 == 0) { echo "CCFFCC"; } else { echo "CCCCFF"; }
			echo ">";
			$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
			
			$a = htmlentities($r['id']);
			echo "<td>";
			if ($a) { echo sprintf("%07.0f", $a); }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			$a = htmlentities($r['artist']);
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			$a = htmlentities($r['title']);
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			$a = htmlentities($r['username']);
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
//selected date
			$a = htmlentities($r[$when]);
			if ($when!=arrivaldate) {$a=date("Y-m-d", $a);}
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";

//arrival
			$a = htmlentities($r[arrivaldate]);
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
//create
			$a = htmlentities($r[$when]);
			$a=date("Y-m-d", $a);
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
//modify
			$a = htmlentities($r[$when]);
			$a=date("Y-m-d", $a);
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			$a = htmlentities($r['id']);
			$a=wave_files_present($a,$db);
			echo "<td>";
			if ($a==0) { echo "OK"; }
			else if ($a==1) { echo "<font color=red>$a track not MP3ed successfully</font>"; }
			else { echo "<font color=red>$a tracks not MP3ed successfully</font>"; }
			echo "</td>\n";
			
			
			$a = htmlentities($r[local]);
			echo "<td>";
			if ($a) {
				if ($a==2 or $a==3) { echo "L"; }
				else { echo "&nbsp;"; }
			}
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			$a = htmlentities($r[format]);
			echo "<td>";
			if ($a) {
				if ($a==1) { echo "CD"; }
				if ($a == 2) { echo '7" Vinyl'; }
				if ($a == 3) { echo '12" Vinyl'; }
				if ($a == 4) { echo "Cassette"; }
				if ($a == 5) { echo "Reel"; }
				if ($a == 6) { echo "Minidisc"; }
				if ($a == 7) { echo "MP3"; }
				else { echo "&nbsp;"; }
			}
			else { echo "&nbsp;"; }
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
	//Check wither wave or mp3 files are present
	$wavepath = "/data/wavein";
	$mp3pathhi = "/data/music/hi";
	$mp3pathlo = "/data/music/lo";
	$trackquery = "SELECT * FROM cdtrack WHERE cdid = $q$cdid$q ORDER by tracknum;";
	$result = pg_query($dbname, $trackquery);
	$num = pg_num_rows($result);
	$cdnum = sprintf("%07.0f", $cdid);
	//echo "<br>$cdid:";
	$wavethere = 0;
	for ($i=0;$i<$num;$i++) {
		$istitle = 0;
		$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
		$tracknum[$i] = $r[tracknum];
		$trackartist[$i] = $r[trackartist];
		if ($trackartist[$i]) { $isartist = 1; }
		$tracktitle[$i] = $r[tracktitle];
		$thetracknum = sprintf("%02.0f", $r[tracknum]);
		$data=strtolower($tracktitle[$i]);
		$data=strpos($tracktitle,"[data");
		if (strpos(strtolower ($tracktitle[$i]),"[data") === false) {
			if (is_readable("$wavepath/$cdnum/$cdnum-$thetracknum.wav")) {}
			else if ((is_readable("$mp3pathhi/$cdnum/$cdnum-$thetracknum.mp3"))
				and (is_readable("$mp3pathlo/$cdnum/$cdnum-$thetracknum.mp3"))) {}
			else {$wavethere = $wavethere + 1;}
			$playname[$i] = "$cdnum-$thetracknum";
		}
	}
	return $wavethere;
}
?>
