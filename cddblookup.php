<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - MusicDB FreeDB Lookup</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>
<BODY>

<?php
if (!$admin && $user['cdeditor'] != "t") {
	echo "<p><font color=red><b>You do not have the necessary privilages to do that!</b></font><p>";
	echo "</BODY></HTML>";
	exit;
}

if ($xadd) {
	$xartist = addslashes(urldecode($xartist));
	$xartist = preg_replace ("/ +/", " ", trim($xartist));
	$xtitle = addslashes(urldecode($xtitle));
	$xtitle = preg_replace ("/ +/", " ", trim($xtitle));
	settype ($xnumtracks, "integer"); if ($xnumtracks < 0) { $xnumtracks = 0; }
	settype ($xyear, "integer"); if ($xyear < 1000 || $xyear > 2100) { $xyear = 0; }
	$xgenre = addslashes(urldecode($xgenre));
	$xgenre = preg_replace ("/ +/", " ", trim($xgenre));
	$timenow = time();
	$a = date ("Y-m-d");
	
	$query = "INSERT INTO cd (artist, title, year, genre, arrivaldate,
			createwho, createwhen, modifywho, modifywhen, copies, format)
			VALUES ($q$xartist$q, $q$xtitle$q,
			$q$xyear$q, $q$xgenre$q, '$a', $q$cid$q, $q$timenow$q, $q$cid$q, $q$timenow$q, 1, 1);";
	echo $query;
	$result = pg_query($db, $query);
	$lastoid = pg_last_oid($result);
	$kquery = "SELECT id FROM cd WHERE OID = $q$lastoid$q;";
	$kresult = pg_query($db, $kquery);
	$r = pg_fetch_array($kresult, 0, PGSQL_ASSOC);
	$xref = $r['id'];
	
	
	$trackcount = count($xtrackartist);
	for ($i=0;$i<$trackcount;$i++) {
		$ii = $i+1;
		$tracktitle = addslashes(urldecode($xtracktitle[$i]));
		$trackartist = addslashes(urldecode($xtrackartist[$i]));
		$tracklength = urldecode($xtracklength[$i]);
		
		if (preg_match("|(.*)[:\.;,](.*)[:\.;,](.*)|", $tracklength, $matches)) {
			settype ($matches[1], "integer");
			settype ($matches[2], "integer");
			settype ($matches[3], "integer");
			$seconds = $matches[1] * 60 * 60 + $matches[2] * 60 + $matches[3];
			$tracklength = $seconds;
		}
		elseif (preg_match("|(.*)[:\.;,](.*)|", $tracklength, $matches)) {
			settype ($matches[1], "integer");
			settype ($matches[2], "integer");
			$seconds = $matches[1] * 60 + $matches[2];
			$tracklength = $seconds;
		}
		else { settype ($tracklength, "integer"); }
		
		$tracktitle = preg_replace ("/ +/", " ", trim($tracktitle));
		$trackartist = preg_replace ("/ +/", " ", trim($trackartist));
		
		
		$ttquery = "INSERT INTO cdtrack (cdid, tracknum, tracktitle, trackartist, tracklength)
		VALUES ($xref, $ii,
		$q$tracktitle$q,
		$q$trackartist$q,
		$q$tracklength$q);";
		$ttresult = pg_query($db, $ttquery);
	}
	
	if ($kresult) { header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/cdedit.php?xref=".$xref); }
	
}
?>

<form action=cddblookup.php method=post>
<B>MUSIC CATALOGUE - NEW ENTRY FROM FREEDB</B>
<p>If this is the CD you want then
<input type=submit name=xadd value="Add it to the Database">
<p><hr>

<?php
if ($id) {
	$url = "https://www.gracenote.com/search/album_details.php?tui_id=".$id;
	$ch = curl_init ($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
	curl_setopt ($ch, CURLOPT_HEADER, 0);
	if ($webproxy) { curl_setopt ($ch, CURLOPT_PROXY, $webproxy); }
	$aa = curl_exec ($ch);
	curl_close ($ch);
	$aa = preg_replace("/[\n\r]/"," ",$aa);
	
	
	
	preg_match ("|<h2>(.*)</h2>|i", $aa, $out);
	$titletemp = $out[1];
	preg_match ("| *(.*) *\/ *(.*) *|", $titletemp, $out1);
	if (count($out1) == 3) { $artist = $out1[1]; $title = $out1[2]; }
	else { $artist = $titletemp; $title = ""; }
	
	preg_match ("|<div class=\"album-name\"><strong>Album</strong> > (.*?)</div>|i", $aa, $out);
	$title = $out[1];
	preg_match ("|<div class=\"artist-name\"><strong>Artist</strong> > <span name=\".*?\"><a.*?>(.*?)</a></span></div>|i", $aa, $out);
	$artist = $out[1];
	preg_match ("|tracks: *(.*)<br>|i", $aa, $out);
	$tracks = $out[1];
	preg_match ("|total time: *(.*)<br>|i", $aa, $out);
	$totaltime = $out[1];
	preg_match ("|<div class=\"year\"><strong>Year of Release</strong> > (.*?)</div>|i", $aa, $out);
	$year = $out[1];
	preg_match ("|genre: *(.*)<br>|i", $aa, $out);
	$genre = $out[1];
	
	preg_match_all ("|<div class=\"track_name\">(.*?)</div>|i", $aa, $tout);
	$tracks = count($tout[1]);
	
	echo "<p><table border=1 cellspacing=0 cellpadding=2>";
	echo "<tr><td>Artist</td><td>$artist</td></tr>";
	echo "<tr><td>Title</td><td>$title</td></tr>";
	echo "<tr><td>Tracks</td><td>$tracks</td></tr>";
	echo "<tr><td>Total Time</td><td>$totaltime</td></tr>";
	echo "<tr><td>Year</td><td>$year</td></tr>";
	echo "<tr><td>Genre</td><td>$genre</td></tr>";
	echo "</table>";
	
	echo "<input type=hidden name=xartist value=".urlencode($artist).">";
	echo "<input type=hidden name=xtitle value=".urlencode($title).">";
	echo "<input type=hidden name=xnumtracks value=".urlencode($tracks).">";
	echo "<input type=hidden name=xyear value=".urlencode($year).">";
	echo "<input type=hidden name=xgenre value=".urlencode($genre).">";
	
	#preg_match_all ("|<tr><td valign=top> *(.*?)\.</td><td valign=top> *(.*?)</td><td><b>(.*?)</b>|i", $aa, $tout);
	
	if (count($tout[1])) {
		echo "\n<p><table border=1 cellspacing=0 cellpadding=2>\n";
		echo "<tr><td><b>Track</b></td><td><b>Length</b></td><td><b>Artist</b></td><td><b>Title</b></td></tr>";
		for ($i=0;$i<count($tout[1]);$i++) {
			#$tracktemp = $tout[3][$i];
			#preg_match ("| *(.*) *\/ *(.*) *|", $tracktemp, $out1);
			#if (count($out1) == 3) { $trackartist = $out1[1]; $tracktitle = $out1[2]; }
			#else { $tracktitle = $tracktemp; $trackartist = ""; }
			#$tracklength = $tout[2][$i];
			$tracktitle = $tout[1][$i];
			echo "\n<tr><td>".($i+1)."</td><td>".$tracklength."</td><td>".$trackartist."</td><td>".$tracktitle."</td>";
			echo "<input type=hidden name=xtrackartist[] value=".urlencode($trackartist).">";
			echo "<input type=hidden name=xtracktitle[] value=".urlencode($tracktitle).">";
			echo "<input type=hidden name=xtracklength[] value=".urlencode($tracklength).">";
		}
		echo "\n</table>\n";
	}
}
?>

</form>
</BODY>
</HTML>
