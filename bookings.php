<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - Bookings</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>
<BODY>

<?php
$bquery = "SELECT * FROM bookingthing WHERE active = 't' ORDER by UPPER(name), id;";
$bresult = pg_query($db, $bquery);
$bnum = pg_num_rows($bresult);
for ($i=0;$i<$bnum;$i++) {
	$br = pg_Fetch_array($bresult, $i, PGSQL_ASSOC);
	$bthingname[$i] = $br['name'];
	$bthingid[$i] = $br['id'];
}
if (!$xb) { $xb = $bthingid[0]; }

if (!$bnum) {
	echo "<font color=red><b>There are no active assets to book</b></font>";
	echo "</body></html>";
	exit;
}

echo "<form action=bookings.php method=post>";
echo "<input type=hidden name=xb value=$xb>";
echo '<select onChange="window.location=this.options[this.selectedIndex].value">';
for ($i=0;$i<$bnum;$i++) {
	$j = '<option value="bookings.php?xb=' . $bthingid[$i] . '&xdate=' . $xdate . '"';
	if ($bthingid[$i] == $xb) { $j = $j . " selected"; }
	$j = $j . ">" . htmlentities($bthingname[$i]) . "</option>";
	echo "$j";
}
echo '</select>';
$todayN = mktime (0,0,0,date("m"),date("d"),date("Y"));
$todayS = date ("Y-m-d", $todayN);
if (preg_match("|(.*)/(.*)/(.*)|", $xdate, $matches)) { $xdate = $matches[2]."/".$matches[1]."/".$matches[3]; }
$thedayN = strtotime($xdate);
if ($thedayN == -1 || $thedayN == "") { $thedayN = $todayN; }
$thedayN = mktime (0,0,0,date("m", $thedayN),date("d", $thedayN),date("Y", $thedayN));
$thedayS = date ("Y-m-d", $thedayN);
$xdate = $thedayS;
$ddate = date ("j M Y", $thedayN);
$prevS = date ("Y-m-d", mktime (0,0,0,date("m", $thedayN),date("d", $thedayN)-7,date("Y", $thedayN)));
$nextS = date ("Y-m-d", mktime (0,0,0,date("m", $thedayN),date("d", $thedayN)+7,date("Y", $thedayN)));
$mondayoffset = date('w', $thedayN) - 1; if (date('w', $thedayN) == 0) { $mondayoffset = 6; }
$themonN = mktime (0,0,0,date("m", $thedayN),date("d", $thedayN)-$mondayoffset,date("Y", $thedayN));
$Tmondayoffset = date('w', $todayN) - 1; if (date('w', $todayN) == 0) { $Tmondayoffset = 6; }
$TthemonN = mktime (0,0,0,date("m", $todayN),date("d", $todayN)-$Tmondayoffset,date("Y", $todayN));
$week = round(($themonN - $TthemonN)/60/60/24/7); ## cludge warning
?>
<input type=text name=xdate value="<?php $a=htmlentities($ddate); echo "$a"; ?>" size=20 maxlength=60>
</form>

<p><table border=0 width=100% cellpadding=0 cellspacing=0>
<tr>
<td width=33%><b><font color=red>
<?php
if ($week == 0) { echo "This Week"; }
else if ($week == 1) { echo "Next Week"; }
else if ($week > 1) { echo "$week Weeks Ahead"; }
else if ($week == -1) { echo "Last Week"; }
else { $w = -$week; echo "$w Weeks Ago"; }
?>
</font></b></td>
<td width=33% align=right><b>
<A HREF="bookings.php?xb=<?php echo $xb;?>">TODAY</A> |
<A HREF="bookings.php?xdate=<?php echo $prevS; ?>&xb=<?php echo $xb; ?>">PREV</A> |
<A HREF="bookings.php?xdate=<?php echo $nextS; ?>&xb=<?php echo $xb; ?>">NEXT</A>
</b>
</td>
</tr>
</table>

<table border=1 cellspacing=0 cellpadding=1 width=100%>
<tr align=center>
<?php

### DATE HEADER ###
for ($i=0;$i<7;$i++) {
	$thisdayN = mktime (0,0,0,date("m",$themonN),date("d",$themonN)+$i,date("Y",$themonN));
	$datestr[$i] = date("D\<\b\\r\>j M y", $thisdayN);
	$datelink[$i] = date("Y-m-d", $thisdayN);
	$daycolh[$i] = "BBBBFF"; $daycol[$i] = "EEEEFF";
	if ($i==5 || $i==6) { $daycolh[$i] = "EEEE99"; $daycol[$i] = "FFFFCC"; }
	if ($thisdayN == $todayN) { $daycolh[$i] = "FF9999"; }
	if ($thisdayN == $todayN) { $daycol[$i] = "FFDDDD"; }
	echo "<td width=14% bgcolor=#$daycolh[$i]><b>";
	if ($datelink[$i] >= $todayS || $admin || $adminbook) { echo "<A HREF=\"bookingedit.php?xb=$xb&xnewdate=$datelink[$i]\">"; }
	echo "<font color=black>$datestr[$i]</font>";
	if ($datelink[$i] >= $todayS || $admin || $adminbook) { echo "</A>"; }
	echo "</b></td>\n";
}
echo "</tr>";

### BOOKINGS ###
echo "<tr valign=top>";
for ($i=0;$i<7;$i++) {
	echo '<td width=14% ';
	if ($i==0) { echo "height=200 "; }
	echo  'bgcolor=#', $daycol[$i], '>';
	$query = "SELECT * FROM booking WHERE date = $q$datelink[$i]$q
			AND bookedthing = $xb AND active = 'y' ORDER by starttime, endtime, id;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	for ($j=0;$j<$num;$j++) {
		$r = pg_Fetch_array($result, $j, PGSQL_ASSOC);
		$tstart[$j] = $r['starttime'];
		$tend[$j] = $r['endtime'];
		$clash[$j] = 0;
	}
	for ($j=0;$j<$num;$j++) {
		for ($k=0;$k<$num;$k++) {
			if ($tstart[$j] > $tstart[$k] && $tstart[$j] < $tend[$k]) { $clash[$j] = 1; $clash[$k] = 1; }
			if ($tend[$j] > $tstart[$k] && $tend[$j] < $tend[$k]) { $clash[$j] = 1; $clash[$k] = 1; }
		}
	}
	for ($j=0;$j<$num;$j++) {
		$r = pg_Fetch_array($result, $j, PGSQL_ASSOC);
		$col = '#000000';
		echo '<p>';
		if ($admin || $adminbook || ($datelink[$i] >= $todayS && $r['createwho'] == $cid)) { echo '<A HREF="bookingedit.php?xeditdate=', $r['id'], '">'; }
		echo '<font class=small color=';
		if ($clash[$j]) { echo "red"; } else { echo "blue"; }
		echo '>';
		echo htmlentities($r['starttime']) . " - ";
		echo htmlentities($r['endtime']) . "<br>";
		echo "<font class=small color=green>" . $name[$r['createwho']] . "</font><br>";
		echo "<font class=small color=black>" . htmlentities($r['text']) . "</font><br>";
		echo "</font>";
		if ($admin || $adminbook || ($datelink[$i] >= $todayS && $r['createwho'] == $cid)) { echo "</a>"; }
		$parent = $r['id'];
		if ($r['parent']) { $parent = $r['parent']; }
	}
	if (!$num) { echo '&nbsp;'; }
	echo "</td>";
}
echo "</tr>";

echo "<tr align=center>";
for ($i=0;$i<7;$i++) {
	echo "<td width=14% bgcolor=#$daycol[$i]>";
	if ($datelink[$i] >= $todayS || $admin || $adminbook) {
		echo "<A HREF=\"bookingedit.php?xb=$xb&xnewdate=$datelink[$i]\">";
		echo "<font class=small color=blue>Book</font>";
		echo "</A>";
	}
	else { echo "&nbsp;"; }
	echo "</td>\n";
}
echo "</tr>";

if ($admin || $adminbook) {
	echo "<tr valign=top>";
	for ($i=0;$i<7;$i++) {
		echo "<td width=14% bgcolor=white>";
		$wquery = "SELECT * FROM booking WHERE date = $q$datelink[$i]$q
				AND bookedthing = $xb AND active <> 'y' ORDER by modifywhen;";
		$wresult = pg_query($db, $wquery);
		$wnum = pg_num_rows($wresult);
		for ($j=0;$j<$wnum;$j++) {
			$r = pg_Fetch_array($wresult, $j, PGSQL_ASSOC);
			$col = '#000000';
			echo '<p>';
			echo '<A HREF="bookingedit.php?xeditdate=', $r['id'], '">';
			echo '<font class=small color=grey>';
			echo htmlentities($r['starttime']) . " - ";
			echo htmlentities($r['endtime']) . "<br>";
			echo $name[$r['createwho']] . "<br>";
			echo htmlentities($r['text']) . "<br>";
			echo "</font>";
			echo "</a>";
		}
		if (!$wnum) { echo '&nbsp;'; }
		echo "</td>\n";
	}
	echo "</tr>";
}
?>

</table>
</BODY>
</HTML>
