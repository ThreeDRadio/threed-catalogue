<?php require("verify.php");
#### User has logged in and been verified ####


?>

<HTML>
<head>
<TITLE>ThreeD - MusicDB New Entry</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>
<BODY onload="document.forms[0].xwords.focus()">

<?php
if (!$admin && $user[cdeditor] != "t") {
	echo "<p><font color=red><b>You do not have the necessary privilages to do that!</b></font><p>";
	echo "</BODY></HTML>";
	exit;
}

echo "<B>MUSIC CATALOGUE - NEW ENTRY</B>";

echo "<p>You are about to add a new entry to the Catalogue.";
echo "<br>Enter the Album name.";

echo "<p><form action=cdnewentry.php method=post>";
echo "<table border=0 cellspacing=0 cellpadding=4>";
echo "<tr bgcolor=#CCCCCC>";
echo "<td><b>Keywords</b></td>";
$a = htmlentities(stripslashes($xwords));
echo "<td><input type=text name=xwords value=\"$a\" size=50 maxlength=50></td>";
echo "<td align=right>";
echo "\n<input type=submit name=xsearch value=Search>\n";
echo "</td>";
echo "</tr>";
echo "</table>";

if (!$xwords || $xfreedbsearch) { echo "</form>"; }

if ($xwords && !$xfreedbsearch) {
	$query = "SELECT * FROM cd WHERE";
	$words = ereg_replace(","," ",$xwords);
	$words = ereg_replace(" +"," ",$words);
	$words = trim($words);
	#echo "#$words#<p>";
	$words = explode(" ", $words);
	for ($i=0;$i<count($words);$i++) {
		if ($i > 0) { $query .= " AND"; }
		$query = $query . " (artist ~~* $q%$words[$i]%$q OR title ~~* $q%$words[$i]%$q OR genre ~~* $q%$words[$i]%$q OR company ~~* $q%$words[$i]%$q)";
	}
	$query = $query . " ORDER by UPPER(artist), UPPER(title);";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	echo "<p><hr><p><b>$num match";
	if ($num != 1) { echo "es"; }
	
	echo " found in the Catalogue</b>";
	echo "<p>If it is not here try a ";
	echo "<input type=submit name=xfreedbsearch value=\"FreeDB Lookup\"> or";
	echo "</form>";
	echo "<p><form action=cdedit.php method=post>";
	echo "<input type=submit name=xnewentry value=\"Create a New Entry\">";
	echo " with ";
	echo "<input type=text name=xtr value=10 size=4 maxlength=10></td>";
	echo " Tracks.";
	echo "</form>";
	
	echo "<p><HR>";
	if ($num) {
		for ($i=0;$i<$num;$i++) {
			$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
			
			$a = htmlentities($r[artist]);
			$b = htmlentities($r[title]);
			
			if ($i) { echo "<br>"; } else { echo "<p>"; }
			echo "<a HREF=cdshow.php?xref=" . $r[id];
			echo ">$a / $b<a>\n";
		}
	}
}



if ($xfreedbsearch && $xwords) {
	echo "<form action=cdedit.php method=post>";
	$stuff = stripslashes($xwords);
	$stuff = urlencode ($stuff);
	$ch = curl_init ("http://www.gracenote.com/search/?query=$stuff&search_type=album");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
	curl_setopt ($ch, CURLOPT_HEADER, 0);
	if ($webproxy) { curl_setopt ($ch, CURLOPT_PROXY, $webproxy); }
	$aa = curl_exec ($ch);
	curl_close ($ch);
	$aa = ereg_replace("[\n\r]"," ",$aa);
	#echo $aa;
	#preg_match_all ("|<div class=\"item\">.*?(<a href=.*?</a>).*?<div class=\"artist-name\"><strong>Artist\:</strong>(.*?)</div>|", $aa, $out);
	#preg_match_all ("|<a href=\"/search/album_details.php\?tui_id=(.*?)&tui_tag=\">(.*?)</a>.*?<div class=\"artist-name\"><strong>Artist:</strong> (.*?)</div>|", $aa, $out);
	preg_match_all ("|<div class=\"album-image\".*?<a href=\"/search/album_details.php\?tui_id=(.*?)&tui_tag.*?tui_tag=\">(.*?)</a>.*?<strong>Artist:</strong>.*?>(.*?)<|", $aa, $out);
	
	echo "<p><hr><p><b>".count($out[1])." match";
	if (count($out[1]) != 1) { echo "es"; }
	echo " found in the FreeDB database</b>";
	echo "<p>If it is not here you will need to ";
	echo "<input type=submit name=xnewentry value=\"Create a New Entry\">";
	echo " with ";
	echo "<input type=text name=xtr value=10 size=4 maxlength=10></td>";
	echo " Tracks.";
	echo "<p><HR>";
	if (count($out[1])) {
		for ($i=0;$i<count($out[1]);$i++) {
			#echo "\n<tr><td>".$out[1][$i]."</td><td>".$out[2][$i]."</td><td>".$out[3][$i]."</td>";
			if ($i) { echo "<br>"; } else { echo "<p>"; }
			echo "<a href=cddblookup.php?id=".$out[1][$i].">".$out[3][$i]." / ".$out[2][$i]."<a>";
			#echo htmlentities($out[1][$i] . "-" . $out[2][$i] . "-" . $out[3][$i]) . "<p><hr>";
		}
	}
	echo "</form>";
}


?>
</BODY>
</HTML>
