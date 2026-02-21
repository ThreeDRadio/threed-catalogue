<?php require("verify.php");
#### User has logged in and been verified ####
settype ($xperpage, "integer");
//echo "Date:$xdate";

if ($xdate == "")  $xdate = date("Y-m-d");
if ($xdate2 == "") $xdate2 = date ("Y-m-d");
if ($xperpage < 5) $xperpage = 200;
if ($xperpage > 200) $xperpage = 200;
if ($sdate =="") $sdate="mod";
####Get username
if (!$xname) { $xname = $user['username']; }
$xname = htmlentities($xname);
//echo $xname;
?>
<HTML>
<head>
<TITLE>ThreeD - MusicDB Rip Report</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>
<BODY onload="document.forms[0].xdate.focus()">

<B>MUSIC CATALOGUE - RIP REPORT</B>

<form action=rip_report.php method=post>
<input type=hidden name=xdosearch value=1>

<p><table border=0 cellspacing=0 cellpadding=5>

<tr>
	<td>
	<font color=red>The rip report displays all items ripped on the current day only!&nbsp;</font>
	</td>
	<td></td>
</tr>

<!---
<tr bgcolor="#CCCCCC">
	<td align=left><b>Show items for this user only:&nbsp;</b>
	<input type=text name=xname value="<?php echo "$xname" ?>" size=25 maxlength=50><b>&nbsp;(use * to view all users)</b>
	</td>
	<td></td>
</tr>
<tr bgcolor="#CCCCCC">
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
</table>
--->
<?php
$xwords = addslashes($xwords); #################

$pieces = explode("-", $xdate2); //0=year,1=month,2=day
//echo "Year:",$pieces[0], " Month:",$pieces[1], " Day:",$pieces[2], "<br>";
list_wave_dirs();

function lookup_cdid($cdid,$dbname) {
	//if ($xdosearch) {
		//$qsort = "cd.id";
		//if ($xname!="*") {
			//$where = $where."AND users.username = '$xname' ";
		//}
	//Display record corresponding to wave file directory name
	$query ="SELECT cd.id, cd.artist, cd.title, cd.local, cd.format, users.username, cd.modifywhen FROM cd, users ";
	$query=$query."WHERE cd.id=$cdid ";
	$query = $query."GROUP BY cd.id ORDER BY cd.id;";
	echo "<p>" . htmlentities($query) . "<p>";

	$result = pg_query($db, $query);
	//$num = pg_num_rows($result);
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
		echo "<tr><th align=left>ID Number</th><th align=left>Artist</th><th align=left>Album Title</th><th align=left>$head</th><th align=left>Date</th><th align=left>MP3</th><th align=left>Local</th><th align=left>Format</th><th align=center>Action</th></tr>\n";
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
			
			$a = htmlentities($r[username]);
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			echo "<td>";
			//$a=date("Y-m-d", $a);
			//if ($a) { echo "$a"; }
			//else { echo "&nbsp;"; }
			echo "</td>\n";
			$a = htmlentities($r['id']);
			//$a=wave_files_present($a,$db);
			echo "<td>";
			//if ($a==0) { echo "OK"; }
			//else if ($a==1) { echo "<font color=red>$a track not MP3ed successfully</font>"; }
			//else { echo "<font color=red>$a tracks not MP3ed successfully</font>"; }
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
			echo "<a HREF=cdshow.php?";
			echo "xref=" . $r['id'] . ">Show<a>";
			
			echo "</td></TR>\n";

			echo "</TR>\n";
		}
		echo "</TABLE>\n";
	}
}
//}
?>

</form>
</BODY>
</HTML>

<?php
function list_wave_dirs() {
	//Find the directories in $wavepath
	$wavepath = "/data/wavein";
 	$dirlist = glob("$wavepath/0*", GLOB_ONLYDIR);
	$numdirs = count($dirlist);
	for ($i=1;$i<$numdirs;$i++) {
		$wavpath = $dirlist[$i];
		echo "$wavpath<br>";
		$pieces=explode("/", $wavpath);
		$cdid=$pieces[3];
		lookup_cdid($cdid,$dbname);	
	}
}	

/*
function wave_files_present($cdid,$dbname) {
	//Check whether wave files are present
	$wavepath = "/data/wavein";
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
		$data=strpos($tracktitle[$i],"[data");
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
*/
?>
