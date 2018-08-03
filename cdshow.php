<?php require("verify.php");
header("Content-Type: text/html;charset=UTF-8");

#Set these and make sure the webserver user can read files in them
$mp3lopath = "/data/music/lo";
$mp3hipath = "/data/music/hi";

# Needs the following in httpd.conf for this virtual host
# RewriteEngine  on
# RewriteRule    ^/database/play/(.*)$   /database/stream.php?xname=$1

#### User has logged in and been verified ####?>

<HTML>
<head>
<TITLE>ThreeD - MusicDB Show</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
</head>
<BODY onload="document.forms[0].xwords.focus()">

<?php

function SanitizeFromWord($Text = '') {

	$chars = array(
		145=>'\'',	  // left single quote
		146=>'\'',	  // right single quote
		147=>'"',	  // left double quote
		148=>'"',	  // right double quote
	);
	
	foreach ($chars as $chr=>$replace) {
		$Text = str_replace(chr($chr), $replace, $Text);
	}
	return $Text;
}


settype ($xref, "integer");

$query = "SELECT * FROM cd WHERE id = $q$xref$q;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
# Determine user's IP address to filter out hi MP3 if it's not local
$remoteip = getenv("REMOTE_ADDR");
$bits = explode(".",$remoteip);
if ($bits[0].".".$bits[1].".".$bits[2] == "192.168.3") {$local=1;} else {$local=0;};
if ($user['username'] === 'mmarner') {$local = 1;}
##echo "Your IP address is: $bits[0].$bits[1].$bits[2] and you are in zone $local<p>";
if ($num != 1) {
	echo "<p><b><font color=red>That CD does not exist \"$xref\"</font></b>";
	echo "</BODY>";
	echo "</HTML>";
	exit;
}

$r = pg_Fetch_array($result, 0, PGSQL_ASSOC);

if ($xaddcomment && trim($xcomment)) {

	if (get_magic_quotes_gpc()) {
		$xcomment = stripslashes($xcomment);
	}
	$xcomment = SanitizeFromWord($xcomment);
	$xcomment = pg_escape_string($xcomment);

	$timenow = time();
	$zero = "0";
	$uquery = "INSERT INTO cdcomment (cdid, cdtrackid, comment, createwho, createwhen,
			modifywho, modifywhen)
			VALUES ($q$xref$q, $q$zero$q, $q$xcomment$q,
			$q$cid$q, $q$timenow$q, $q$cid$q, $q$timenow$q);";
	$uresult = pg_query($db, $uquery);
}

echo "<p><TABLE border=0 cellpadding=0 cellspacing=0 bgcolor=#FFFFFF><TR valign=middle><TR>";

echo "<TD valign=middle><B>MUSIC CATALOGUE LOOKUP</B></TD>";

if ($user[admin] == 't' || ($user[cdeditor] == "t" && $r[status] != 2)) {
	echo "<TD valign=middle><form action=cdedit.php method=post target=_Blank accept-charset=\"UTF-8\">";
	echo "<input type=hidden name=xref value=$xref>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=xdoedit value=\"Edit This Entry\">";
	echo "</form></TD>";
}

echo "</TR></TABLE>";

echo "<p><TABLE border=0 cellpadding=4 cellspacing=0 bgcolor=#CCCCFF><TR valign=top><TD>";
echo "<p><TABLE border=1 cellpadding=1 cellspacing=0 bgcolor=#DDDDFF>";

echo "<TR><TD valign=top><b>ID Number</b></TD><TD>";
$a = sprintf("%07.0f", $r[id]);
echo $a;
echo "</TD></TR>";

echo "<TR><TD valign=top><b>Artist</b></TD><TD>";
$a = htmlentities(stripslashes($r[artist]));
if ($a) { echo $a; } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR><TD valign=top><b>Title</b></TD><TD>";
$a = htmlentities(stripslashes($r[title]));
if ($a) { echo $a; } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR><TD valign=top><b>Release&nbsp;Year</b></TD><TD>";
$a = $r[year];
if ($a == 0) { $a = ""; }
if ($a) { echo $a; } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR><TD valign=top><b>Genre</b></TD><TD>";
$a = htmlentities(stripslashes($r[genre]));
if ($a) { echo $a; } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR><TD valign=top><b>Company</b></TD><TD>";
$a = htmlentities(stripslashes($r[company]));
if ($a) { echo $a; } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR><TD valign=top><b>Country</b></TD><TD>";
$a = htmlentities(stripslashes($r[cpa]));
if ($a) { echo $a; } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR><TD valign=top><b>Arrival Date</b></TD><TD>";
if ($r[arrivaldate] == "0001-01-01") { $a = ""; }
else {
	$thedayN = strtotime($r[arrivaldate]);
	$a = date ("d/m/Y", $thedayN);
}
if ($a) { echo $a; } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR><TD valign=top><b>Format</b></TD><TD>";
$b = $r[format];
$a = "Unknown";
if ($b == 1) { $a = "Compact Disc"; }
if ($b == 2) { $a = '7" Vinyl'; }
if ($b == 3) { $a = '12" Vinyl'; }
if ($b == 4) { $a = "Cassette"; }
if ($b == 5) { $a = "Reel"; }
if ($b == 6) { $a = "Minidisc"; }
if ($b == 7) { $a = "MP3"; }
if ($a) { echo htmlentities($a); } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "</TABLE></TD><TD><TABLE border=1 cellpadding=1 cellspacing=0 bgcolor=#DDDDFF>";

echo "<TR><TD valign=top><b>Compliation</b></TD><TD>";
$b = $r[compilation];
$a = "";
if ($b == 1) { $a = "No"; }
if ($b == 2) { $a = "Yes"; }
if ($a) { echo htmlentities($a); } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR><TD valign=top><b>Demo</b></TD><TD>";
$b = $r[demo];
$a = "";
if ($b == 1) { $a = "No"; }
if ($b == 2) { $a = "Yes"; }
if ($a) { echo $a; } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR><TD valign=top><b>Local</b></TD><TD>";
$b = $r[local];
$a = "";
if ($b == 1) { $a = "No"; }
if ($b == 2) { $a = "Yes"; }
if ($b == 3) { $a = "Some"; }
if ($a) { echo $a; } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR><TD valign=top><b>Female</b></TD><TD>";
$b = $r[female];
$a = "";
if ($b == 1) { $a = "No"; }
if ($b == 2) { $a = "Yes"; }
if ($b == 3) { $a = "Some"; }
if ($a) { echo $a; } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR><TD valign=top><b>Copies</b></TD><TD>";
$a = $r[copies];
if ($a == "0") { $a = ""; }
if ($a) { echo $a; } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR bgcolor=#DDDDDD><TD valign=top><b>Status</b></TD><TD>";
$b = $r[status];
$a = "Unchecked";
if ($b == 1) { $a = "Incomplete"; }
if ($b == 2) { $a = "Final"; }
if ($a) { echo $a; } else { echo "&nbsp;"; }
echo "</TD></TR>";

echo "<TR bgcolor=#DDDDDD><TD valign=top><b>Created</b></TD><TD>";
if ($r[createwhen]) { $b = date ("d/m/Y", $r[createwhen]); }
else { $b = "Unknown"; }
$uquery = "SELECT * FROM users WHERE id = $q$r[createwho]$q;";
$uresult = pg_query($db, $uquery);
$unum = pg_num_rows($uresult);
if ($unum == 1) {
	$ur = pg_Fetch_array($uresult, 0, PGSQL_ASSOC);
	if ($ur[first] || $ur[last]) { $a = $ur[first] . " " . $ur[last]; }
	else $a = $ur[username];
}
else { $a = "Unknown"; }
echo "$b ($a)</TD></TR>";

echo "<TR bgcolor=#DDDDDD><TD valign=top><b>Modified</b></TD><TD>";
if ($r[modifywhen]) { $b = date ("d/m/Y", $r[modifywhen]); }
else { $b = "Unknown"; }
$uquery = "SELECT * FROM users WHERE id = $q$r[modifywho]$q;";
$uresult = pg_query($db, $uquery);
$unum = pg_num_rows($uresult);
if ($unum == 1) {
	$ur = pg_Fetch_array($uresult, 0, PGSQL_ASSOC);
	if ($ur[first] || $ur[last]) { $a = $ur[first] . " " . $ur[last]; }
	else $a = $ur[username];
}
else { $a = "Unknown"; }
echo "$b ($a)</TD></TR>";

echo "</TD></TR></TABLE></TABLE>";

$query = "SELECT * FROM cdtrack WHERE cdid = $q$xref$q ORDER by tracknum;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);

$cdnum = sprintf("%07.0f", $xref);

$isartist = 0;
$istitle = 0;
$islength = 0;
for ($i=0;$i<$num;$i++) {
	$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
	$tracknum[$i] = $r[tracknum];
	$trackartist[$i] = stripslashes($r[trackartist]);
	if ($trackartist[$i]) { $isartist = 1; }
	$tracktitle[$i] = stripslashes($r[tracktitle]);
	if ($tracktitle[$i]) { $istitle = 1; }
	$ttt = $r[tracklength];
	if ($ttt) { $islength = 1; }
	$min = 0;
	$min = floor($ttt/60);
	$sec = $ttt % 60;
	$c = sprintf("%1d", $min) . ":" . sprintf("%02d", $sec);
	if ($ttt == 0) { $c = "&nbsp"; }
	$tracklength[$i] = $c;
	$thetracknum = sprintf("%02.0f", $r[tracknum]);
	if (is_readable("$mp3lopath/$cdnum/$cdnum-$thetracknum.mp3")) $mp3lothere[$i] = 1;
	if ($local == 1) {if (is_readable("$mp3hipath/$cdnum/$cdnum-$thetracknum.mp3")) $mp3hithere[$i] = 1;}
	##if (is_readable("$mp3hipath/$cdnum/$cdnum-$thetracknum.mp3")) $mp3hithere[$i] = 1;
	$playname[$i] = "$cdnum-$thetracknum";
}
echo "<p><TABLE border=1 cellpadding=1 cellspacing=0 bgcolor=#CCFFCC>";
echo "<TR bgcolor=#AADDAA><TD valign=top><b>Track</b></TD>";
if ($isartist) { echo "<TD valign=top><b>Artist</b></TD>"; }
if ($istitle) { echo "<TD valign=top><b>Title</b></TD>"; }
if ($islength) { echo "<TD valign=top align=right><b>Length</b></TD>"; }
if ($mp3lothere) { echo "<TD valign=top align=right><b>LoFi</b></TD>"; }
if ($mp3hithere) { echo "<TD valign=top align=right><b>HiFi</b></TD>"; }
echo "</TR>";
for ($i=0;$i<$num;$i++) {
	echo "<TR><TD align=center>$tracknum[$i]</TD>";
	if ($isartist) { if ($trackartist[$i]) { echo "<TD valign=top>$trackartist[$i]</TD>";} else { echo "<TD valign=top>&nbsp;</TD>"; } }
	if ($istitle) { if ($tracktitle[$i]) { echo "<TD valign=top>$tracktitle[$i]</TD>";} else { echo "<TD valign=top>&nbsp;</TD>"; } }
	if ($islength) { if ($tracklength[$i]) { echo "<TD valign=top align=right>$tracklength[$i]</TD>";} else { echo "<TD valign=top>&nbsp;</TD>"; } }
	if ($mp3lothere) { if ($mp3lothere[$i]) { echo "<TD valign=top align=right><A HREF=/database/play/$playname[$i]-lo.mp3>play</A></TD>";} else { echo "<TD valign=top>&nbsp;</TD>"; } }
	if ($mp3hithere) { if ($mp3hithere[$i]) { echo "<TD valign=top align=right><A HREF=/database/play/$playname[$i]-hi.mp3>play</A></TD>";} else { echo "<TD valign=top>&nbsp;</TD>"; } }
	echo "</TR>";
}
echo "</TABLE>";

print('<h3>Comments Disabled</h3>');
if (false) {
	echo "<p><TABLE border=1 cellpadding=1 cellspacing=0 bgcolor=#FFCCCC>";
	echo "<TR bgcolor=#";
	echo "FFAAAA";
	
	echo "><TD><b>Comments</b></TD></TR>";
	$query = "SELECT * FROM cdcomment WHERE cdid = $q$xref$q ORDER by createwhen;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	for ($i=0;$i<$num;$i++) {
		$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
		$uquery = "SELECT * FROM users WHERE id = $q$r[createwho]$q;";
		$uresult = pg_query($db, $uquery);
		$unum = pg_num_rows($uresult);
		if ($unum == 1) {
			$ur = pg_Fetch_array($uresult, 0, PGSQL_ASSOC);
			$a = htmlentities($ur[first]);
			if ($ur[first] && $ur[last]) { $a .= " "; }
			$a .= htmlentities($ur[last]);
			if (!$a) { $a = htmlentities($ur[username]); }
		}
		else { $a = "Unknown"; }
		if ($r[createwhen]) { $b = date ("d/m/Y", $r[modifywhen]); }
		else { $b = "Unknown"; }
		$c = $r['comment'];
		# $c = preg_replace ("/[\n\r]+/", "\n", trim($r[comment]));
		$c = htmlspecialchars($c);
		$c = nl2br($c);
		echo "<TR bgcolor=#";
		if ($i % 2 == 0) { echo "FFEEEE"; } else { echo "FFDDDD"; }
		echo "><TD>";
		echo "$c <font color=#888888>$a ($b)</font>";
		echo "</TD></TR>";
	}
	
	echo '<form action="cdshow.php" method="post" accept-charset="UTF-8">';
	echo "<TR bgcolor=#";
	if ($i % 2 == 0) { echo "DDDDDD"; } else { echo "DDDDDD"; }
	echo "><TD>";
	echo "<input type=hidden name=xref value=$xref>";
	echo "<input type=hidden name=xaddcomment value=1>";
	echo "<textarea name=xcomment rows=4 cols=60></textarea>";
	echo "<br><input type=submit name=xdoaddcomment value=\"Add My Comment\">";
	echo "</form>";
	echo "</td></form>";
	echo "</TABLE>";
}
?>

</BODY>
</HTML>
