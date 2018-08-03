<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - Manage Assets</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>
<BODY onload="document.forms[0].xtext.focus()">

<?php
$bquery = "SELECT * FROM bookingthing WHERE active = 't' ORDER by id;";
$bresult = pg_query($db, $bquery);
$bnum = pg_num_rows($bresult);
for ($i=0;$i<$bnum;$i++) {
	$br = pg_Fetch_array($bresult, $i, PGSQL_ASSOC);
	$bthingname[$i] = $br['name'];
	$bthingid[$i] = $br['id'];
}
if (!$xb) { $xb = $bthingid[0]; }
$gquery = "SELECT * FROM bookingthing ORDER by id;";
$gresult = pg_query($db, $gquery);
$gnum = pg_num_rows($gresult);
for ($i=0;$i<$gnum;$i++) {
	$gr = pg_Fetch_array($gresult, $i, PGSQL_ASSOC);
	$bthing[$gr[id]] = $gr['name'];
}

$todayN = mktime (0,0,0,date("m"),date("d"),date("Y"));
$today = date ("Y-m-d", $todayN);


$command = "newdate"; ##
if ($xdodelete) { $command = "delete"; }
if ($xdoupdate) { $command = "update"; } ##
if ($xdonew)    { $command = "create"; } ##
if ($xdocreate) { $command = "create"; } ##
if ($xeditdate) { $command = "editid"; } ##


if ($command == "newdate") {
	$todayN = mktime (0,0,0,date("m"),date("d"),date("Y"));
	$thedayN = strtotime($xnewdate);
	if (!$xnewdate || $thedayN == -1) { $thedayN = $todayN; }
	$thedayN = mktime (0,0,0,date("m", $thedayN),date("d", $thedayN),date("Y", $thedayN));
	$ydate = date ("Y-m-d", $thedayN);
	$xdate = date ("d/m/Y", $thedayN);
	if (!$admin && !$adminbook && $ydate < $today) { $dateerr = 1; }
	$xdateid = "";
	$xtext = "";
	$xwhoid = $cid;
	$xwho = $name[$cid];
	echo "<p><b>ENTER EVENT DETAILS</b>";
}


if ($command == "create") {
	echo "<p><b>ENTER EVENT DETAILS</b>";
	$err = "";
	$tdate = $xdate;
	if (preg_match("|(.*)/(.*)/(.*)|", $xdate, $matches)) { $xdate = $matches[2]."/".$matches[1]."/".$matches[3]; }
	$thedayN = strtotime($xdate);
	if (!$xdate || $thedayN == -1) {
		$err = 1;
		$dateerr = 1;
		$xdate = $tdate;
	}
	else {
		$todayN = mktime (0,0,0,date("m"),date("d"),date("Y"));
		$today = date ("Y-m-d", $todayN);
		$thedayN = mktime (0,0,0,date("m", $thedayN),date("d", $thedayN),date("Y", $thedayN));
		$ydate = date ("Y-m-d", $thedayN);
		$xdate = date ("d/m/Y", $thedayN);
		if (!$admin && !$adminbook && $ydate < $today) { $err = 1; $dateerr = 1; }
	}
	$xtext = trim($xtext);
	if (!$xtext) {
		#echo "<p><b>NO EVENT TEXT</b>";	
		$err = 1;
		$texterr = 1;
	}
	settype ($xwhoid, integer);
	if (!$admin && !$adminbook) { $xwhoid = $cid; }
	if (!$name[$xwhoid]) {
		$err = 1;
		$whoerr = 1;
	}
	$starttime = ereg_replace (" +", "", trim($xstarttime));
	$starthour = "-1"; $startmin = "-1"; $startpm = 0; $startam = 0;
	if (preg_match("|^([0-9:.]*)([Pp][Mm])$|", $starttime, $matches)) { $startpm = 1; $starttime = $matches[1]; }
	if (preg_match("|^([0-9:.]*)([Aa][Mm])$|", $starttime, $matches)) { $startam = 1; $starttime = $matches[1]; }
	if (preg_match("|^([0-9]+)[:.]+([0-9][0-9])$|", $starttime, $matches)) { $starthour = $matches[1]; $startmin = $matches[2]; }
	if (preg_match("|^([0-9])$|", $starttime, $matches)) { $starthour = $matches[1]; $startmin = "0"; }
	if (preg_match("|^([0-9])([0-9])$|", $starttime, $matches)) { $starthour = $matches[1].$matches[2]; $startmin = "0"; }
	if (preg_match("|^([0-9])([0-9])([0-9])$|", $starttime, $matches)) { $starthour = $matches[1]; $startmin = $matches[2].$matches[3]; }
	if (preg_match("|^([0-9])([0-9])([0-9])([0-9])$|", $starttime, $matches)) { $starthour = $matches[1].$matches[2]; $startmin = $matches[3].$matches[4]; }
	if ($startpm && $starthour < 12) { $starthour += 12; }
	if ($startam && $starthour == 12) { $starthour = 0; }
	if (!$startmin) { $startmin = "00"; }
	if ($startmin >= 0 && $startmin <=59 && $starthour >= 0 && $starthour <= 23) { $xstarttime = sprintf("%02.0f", $starthour) . ":" . sprintf("%02.0f", $startmin); }
	else { $starterr = 1; $err = 1;}
	$endtime = ereg_replace (" +", "", trim($xendtime));
	$endhour = "-1"; $endmin = "-1"; $endpm = 0; $endam = 0;
	if (preg_match("|^([0-9:.]*)([Pp][Mm])$|", $endtime, $matches)) { $endpm = 1; $endtime = $matches[1]; }
	if (preg_match("|^([0-9:.]*)([Aa][Mm])$|", $endtime, $matches)) { $endam = 1; $endtime = $matches[1]; }
	if (preg_match("|^([0-9]+)[:.]+([0-9][0-9])$|", $endtime, $matches)) { $endhour = $matches[1]; $endmin = $matches[2]; }
	if (preg_match("|^([0-9])$|", $endtime, $matches)) { $endhour = $matches[1]; $endmin = "0"; }
	if (preg_match("|^([0-9])([0-9])$|", $endtime, $matches)) { $endhour = $matches[1].$matches[2]; $endmin = "0"; }
	if (preg_match("|^([0-9])([0-9])([0-9])$|", $endtime, $matches)) { $endhour = $matches[1]; $endmin = $matches[2].$matches[3]; }
	if (preg_match("|^([0-9])([0-9])([0-9])([0-9])$|", $endtime, $matches)) { $endhour = $matches[1].$matches[2]; $endmin = $matches[3].$matches[4]; }
	if ($endpm && $endhour < 12) { $endhour += 12; }
	if ($endam && $endhour == 12) { $endhour = 0; }
	if (!$endmin) { $endmin = 0; }
	if ($endhour == 0 && $endmin == 0) { $endhour = 24; }
	if ($endmin >= 0 && $endmin <=59 && (($endhour >= 0 && $endhour <= 23) || ($endhour == 24 && $endmin == 0))) {
		$xendtime = sprintf("%02.0f", $endhour) . ":" . sprintf("%02.0f", $endmin);
		if ($xendtime < $xstarttime && !$starterr) { $enderr = 1; $err = 1;}
	}
	else { $enderr = 1; $err = 1;}
	if (!$err) {
		$timenow = time();
		$uquery = "INSERT INTO booking (createwho, createwhen, modifywho, modifywhen, bookedthing, text, date, starttime, endtime, active, parent) VALUES (";
		$uquery = $uquery . "$q$xwhoid$q, $q$timenow$q, $q$cid$q, $q$timenow$q, $q$xb$q, $q$xtext$q, $q$ydate$q, $q$xstarttime$q, $q$xendtime$q, 'y', '0');";
		$result = pg_query($db, $uquery);
		echo "<p><b>EVENT HAS BEEN ADDED - ";
		echo "<a href=bookings.php?xb=$xb&xdate=$xdate>Return to Bookings</b></a>\n";
		header("Location: http://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/bookings.php?xdate=".$xdate.'&xb='.$xb);
	}
}


if ($command == "editid") {
	$todayN = mktime (0,0,0,date("m"),date("d"),date("Y"));
	$today = date ("Y-m-d", $todayN);
	$xdateid = $xeditdate;
	settype ($xdateid, integer);
	$yquery = "SELECT * FROM booking WHERE id = $q$xdateid$q;";
	$yresult = pg_query($db, $yquery);
	$ynum = pg_num_rows($yresult);
	if ($ynum != 1) {
		echo "<font color=red><b>That event does not exist</b></font>";
		echo "<br>$yquery<br>$yresult</body></html>";
		exit;
	}
	$yr = pg_Fetch_array($yresult, 0, PGSQL_ASSOC);
	$query = "SELECT * FROM booking WHERE id = $q$xdateid$q;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num) {
		$r = pg_Fetch_array($result, 0, PGSQL_ASSOC);
		$xdate = htmlentities($r[date]);
		$thedayN = strtotime($xdate);
		$thedayN = mktime (0,0,0,date("m", $thedayN),date("d", $thedayN),date("Y", $thedayN));
		$xdate = date ("d/m/Y", $thedayN);
		$xtext = addslashes($r[text]);
		$xbookedthing = addslashes($r[bookedthing]);
		$xb = $r[bookedthing];
		$xstarttime = htmlentities($r[starttime]);
		$xendtime = htmlentities($r[endtime]);
		$xwhoid = $r[createwho];
		$xwho = $name[$xwhoid];
	}
	else {
		echo "<font color=red><b>That event does not exist</b></font>";
		echo "<br>$yquery<br>$yresult</body></html>";
		exit;
	}
	if ($xwhoid != $cid && !$admin && !$adminbook) {
		echo "<font color=red><b>You cannot edit other peoples events</b></font>";
		echo "</body></html>";
		exit;
	}
	if ($r[date] < $today && !$admin && !$adminbook) {
		echo "<font color=red><b>You cannot edit a booking in the past</b></font>";
		echo "</body></html>";
		exit;
	}
	echo "<p><b>EDIT THIS EVENT</b>";
}


if ($command == "update") {
	$todayN = mktime (0,0,0,date("m"),date("d"),date("Y"));
	$today = date ("Y-m-d", $todayN);
	settype ($xdateid, integer);
	$xdateid = $xeditdate;
	$yquery = "SELECT * FROM booking WHERE id = $q$xdateid$q;";
	$yresult = pg_query($db, $yquery);
	$ynum = pg_num_rows($yresult);
	if ($ynum != 1) {
		echo "<font color=red><b>That event does not exist</b></font>";
		echo "<br>1:<br>$todayN<br>$today<br>$yquery<br>$yresult<br>$ynum</body></html>";
		exit;
	}
	$yr = pg_Fetch_array($yresult, 0, PGSQL_ASSOC);
	$theid = $yr[id];
	if ($yr[parent]) { $theid = $yr[parent]; }
	$thestatus = $yr[active];
	$query = "SELECT * FROM booking WHERE id = $q$theid$q OR parent = $q$theid$q ORDER BY id DESC;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num) {
		$r = pg_Fetch_array($result, 0, PGSQL_ASSOC);
		$theid = $r[id];
		#$thestatus = $r[active];
		if ($r[createwho] != $cid && !$admin && !$adminbook) {
			echo "<font color=red><b>You cannot update another persons record</b></font>";
			echo "</body></html>";
			exit;
		}
		if ($r[date] < $today && !$admin && !$adminbook) {
			echo "<font color=red><b>You cannot change a booking in the past</b></font>";
			echo "</body></html>";
			exit;
		}
	}
	else {
		echo "<font color=red><b>That event does not exist</b></font>";
		echo "<br>$yquery<br>$yresult</body></html>";
		exit;
	}
	echo "<p><b>EDIT THIS EVENT</b>";	$err = "";
	$tdate = $xdate;
	if (preg_match("|(.*)/(.*)/(.*)|", $xdate, $matches)) { $xdate = $matches[2]."/".$matches[1]."/".$matches[3]; }
	$thedayN = strtotime($xdate);
	if (!$xdate || $thedayN == -1) {
		$err = 1;
		$dateerr = 1;
		$xdate = $tdate;
	}
	else {
		$thedayN = mktime (0,0,0,date("m", $thedayN),date("d", $thedayN),date("Y", $thedayN));
		$ydate = date ("Y-m-d", $thedayN);
		$xdate = date ("d/m/Y", $thedayN);
		if (!$admin && !$adminbook && $ydate < $today) { $err = 1; $dateerr = 1; }
	}
	$xtext = trim($xtext);
	if (!$xtext) {
		$err = 1;
		$texterr = 1;
	}
	settype ($xwhoid, integer);
	if (!$admin && !$adminbook) { $xwhoid = $cid; }
	if (!$name[$xwhoid]) {
		$err = 1;
		$whoerr = 1;
	}
	$starttime = ereg_replace (" +", "", trim($xstarttime));
	$starthour = "-1"; $startmin = "-1"; $startpm = 0; $startam = 0;
	if (preg_match("|^([0-9:.]*)([Pp][Mm])$|", $starttime, $matches)) { $startpm = 1; $starttime = $matches[1]; }
	if (preg_match("|^([0-9:.]*)([Aa][Mm])$|", $starttime, $matches)) { $startam = 1; $starttime = $matches[1]; }
	if (preg_match("|^([0-9]+)[:.]+([0-9][0-9])$|", $starttime, $matches)) { $starthour = $matches[1]; $startmin = $matches[2]; }
	if (preg_match("|^([0-9])$|", $starttime, $matches)) { $starthour = $matches[1]; $startmin = "0"; }
	if (preg_match("|^([0-9])([0-9])$|", $starttime, $matches)) { $starthour = $matches[1].$matches[2]; $startmin = "0"; }
	if (preg_match("|^([0-9])([0-9])([0-9])$|", $starttime, $matches)) { $starthour = $matches[1]; $startmin = $matches[2].$matches[3]; }
	if (preg_match("|^([0-9])([0-9])([0-9])([0-9])$|", $starttime, $matches)) { $starthour = $matches[1].$matches[2]; $startmin = $matches[3].$matches[4]; }
	if ($startpm && $starthour < 12) { $starthour += 12; }
	if ($startam && $starthour == 12) { $starthour = 0; }
	if (!$startmin) { $startmin = "00"; }
	if ($startmin >= 0 && $startmin <=59 && $starthour >= 0 && $starthour <= 23) { $xstarttime = sprintf("%02.0f", $starthour) . ":" . sprintf("%02.0f", $startmin); }
	else { $starterr = 1; $err = 1;}
	$endtime = ereg_replace (" +", "", trim($xendtime));
	$endhour = "-1"; $endmin = "-1"; $endpm = 0; $endam = 0;
	if (preg_match("|^([0-9:.]*)([Pp][Mm])$|", $endtime, $matches)) { $endpm = 1; $endtime = $matches[1]; }
	if (preg_match("|^([0-9:.]*)([Aa][Mm])$|", $endtime, $matches)) { $endam = 1; $endtime = $matches[1]; }
	if (preg_match("|^([0-9]+)[:.]+([0-9][0-9])$|", $endtime, $matches)) { $endhour = $matches[1]; $endmin = $matches[2]; }
	if (preg_match("|^([0-9])$|", $endtime, $matches)) { $endhour = $matches[1]; $endmin = "0"; }
	if (preg_match("|^([0-9])([0-9])$|", $endtime, $matches)) { $endhour = $matches[1].$matches[2]; $endmin = "0"; }
	if (preg_match("|^([0-9])([0-9])([0-9])$|", $endtime, $matches)) { $endhour = $matches[1]; $endmin = $matches[2].$matches[3]; }
	if (preg_match("|^([0-9])([0-9])([0-9])([0-9])$|", $endtime, $matches)) { $endhour = $matches[1].$matches[2]; $endmin = $matches[3].$matches[4]; }
	if ($endpm && $endhour < 12) { $endhour += 12; }
	if ($endam && $endhour == 12) { $endhour = 0; }
	if (!$endmin) { $endmin = 0; }
	if ($endhour == 0 && $endmin == 0) { $endhour = 24; }
	if ($endmin >= 0 && $endmin <=59 && (($endhour >= 0 && $endhour <= 23) || ($endhour == 24 && $endmin == 0))) {
		$xendtime = sprintf("%02.0f", $endhour) . ":" . sprintf("%02.0f", $endmin);
		if ($xendtime < $xstarttime && !$starterr) { $enderr = 1; $err = 1;}
	}
	else { $enderr = 1; $err = 1;}
	if (!$err) {
		$timenow = time();
		if ($thestatus != 'x') {
			$uquery = "UPDATE booking SET active = 'u' WHERE id=$q$theid$q;";
			$uresult = pg_query($db, $uquery);
		}
		$parent = $r[id];
		if ($r[parent]) { $parent = $r[parent]; }
		$uquery = "INSERT INTO booking (createwho, createwhen, modifywho, modifywhen, bookedthing, text, date, starttime, endtime, active, parent) VALUES (";
		$uquery = $uquery . "$q$xwhoid$q, $q$r[createwhen]$q, $q$cid$q, $q$timenow$q, $q$xb$q, $q$xtext$q, $q$ydate$q, $q$xstarttime$q, $q$xendtime$q, 'y', $q$parent$q);";
		$uresult = pg_query($db, $uquery);
		echo "<p><b>EVENT HAS BEEN UPDATED - ";
		echo "<a href=bookings.php?xid=$xid&xdate=$xdate>Return to Bookings</b></a>\n";
		header("Location: http://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/bookings.php?xdate=".$xdate."&xb=$xb");
	}
	else { $r[active] = $thestatus; }
}


if ($command == "delete") {
	$todayN = mktime (0,0,0,date("m"),date("d"),date("Y"));
	$today = date ("Y-m-d", $todayN);
	$err = "";
	settype ($xdateid, integer);
	$query = "SELECT * FROM booking WHERE id = $q$xdateid$q;";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	if ($num) {
		$r = pg_Fetch_array($result, 0, PGSQL_ASSOC);
		$xdateid = $r[id];
		$thestatus = $r[active];
		if ($r[createwho] != $cid && !$admin && !$adminbook) {
			echo "<font color=red><b>You cannot delete another persons record</b></font>";
			echo "</body></html>";
			exit;
		}
		if ($r[date] < $today && !$admin && !$adminbook) {
			echo "<font color=red><b>You cannot delete a booking in the past</b></font>";
			echo "</body></html>";
			exit;
		}
		if ($r[active] != 'y') {
			echo "<font color=red><b>Cannot delete a non active entry</b></font>";
			echo "</body></html>";
			exit;
		}
	}
	else {
		echo "<font color=red><b>That event does not exist</b></font>";
		echo "<br>$yquery<br>$yresult</body></html>";
		exit;
	}
	
	if (!$err) {
		$timenow = time();
		if ($thestatus != 'x') {
			$uquery = "UPDATE booking SET active = 'd' WHERE id=$q$xdateid$q;";
			$uresult = pg_query($db, $uquery);
		}
		$parent = $r[id];
		if ($r[parent]) { $parent = $r[parent]; }
		$uquery = "INSERT INTO booking (createwho, createwhen, modifywho, modifywhen, bookedthing, text, date, starttime, endtime, active, parent) VALUES (";
		$uquery = $uquery . "$q$r[createwho]$q, $q$r[createwhen]$q, $q$cid$q, $q$timenow$q, $q$r[bookedthing]$q, $q$r[text]$q, $q$r[date]$q, $q$r[starttime]$q, $q$r[endtime]$q, 'x', $q$parent$q);";
		$uresult = pg_query($db, $uquery);

		echo "<p><b>EVENT HAS BEEN DELETED - ";
		echo "<a href=bookings.php?xid=$xid&xdate=$xdate>Return to Bookings</b></a>\n";
		header("Location: http://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/bookings.php?xdate=".$xdate."&xb=$xb");
	}
}


$fdateid = htmlentities(stripslashes(trim($xdateid)));
$fdate = htmlentities(stripslashes(trim($xdate)));
$ftext = htmlentities(stripslashes(trim($xtext)));
$fstarttime = htmlentities(stripslashes(trim($xstarttime)));
$fendtime = htmlentities(stripslashes(trim($xendtime)));
if ($xwhoid) { $xwho = $name[$xwhoid]; } else { $xwho = "UNKNOWN"; }
?>

<form action=bookingedit.php method=post name=myform>
<input type=hidden name=xdateid value=<?php=$fdateid?>>

<?php
echo "<input type=hidden name=xb value=$xb>";
?>

<table border=0 cellspacing=0 cellpadding=8>

<?php
echo "<tr bgcolor=#CCCCFF>";
echo "<td><b>Resource</b></td>";
echo '<td><select name=xb>';
for ($i=0;$i<$bnum;$i++) {
	$j = '<option value=' . $bthingid[$i];
	if ($bthingid[$i] == $xb) { $j = $j . " selected"; }
	$j = $j . ">" . $bthingname[$i] . "</option>";
	echo "$j";
}
echo '</select></td></tr>';


if ($admin || $adminbook) {
	echo '<tr bgcolor=#CCCCFF>';
	echo '<td><b>';
	if ($whoerr) { echo "<font color=red>"; }
	echo 'Who For';
	if ($whoerr) { echo "</font>"; }
	echo '</b></td>';
	echo "<td colspan=3><input type=text name=xwho value=\"$xwho\" size=30 maxlength=50 disabled>";
	echo "<input type=hidden name=xwhoid value=\"$xwhoid\">";
	echo '<SCRIPT LANGUAGE="JavaScript">';
	echo 'function popUp(URL) {';
	echo 'day = new Date();';
	echo 'id = day.getTime();';
	echo 'eval("page" + id + " = window.open(URL, \'" + id + "\', \'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=400,height=600,left=400,top=40\');");';
	echo '}';
	echo '</script>';
	echo '<input type=button value="Lookup User" onClick="javascript:popUp(\'userlookup.php\')">';
	echo '</td>';
	echo '</tr>';
}
?>

<tr bgcolor="#CCCCFF">
<td><b>
<?php if ($dateerr) { echo "<font color=red>"; } ?>
Date
<?php if ($dateerr) { echo "</font>"; } ?>
</b></td>
<td colspan=3><input type=text name=xdate value="<?php $a=htmlentities(stripslashes($fdate)); echo "$a"; ?>" size=30 maxlength=50></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>
<?php if ($starterr) { echo "<font color=red>"; } ?>
Start Time
<?php if ($starterr) { echo "</font>"; } ?>
</b></td>
<td colspan=3><input type=text name=xstarttime value="<?php $a=htmlentities(stripslashes($fstarttime)); echo "$a"; ?>" size=30 maxlength=50>
<INPUT TYPE=button VALUE="All Day"
	ONCLICK="document.myform.xstarttime.value='00:00';document.myform.xendtime.value='24:00';">
</td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>
<?php if ($enderr) { echo "<font color=red>"; } ?>
End Time
<?php if ($enderr) { echo "</font>"; } ?>
</b></td>
<td colspan=3><input type=text name=xendtime value="<?php $a=htmlentities(stripslashes($fendtime)); echo "$a"; ?>" size=30 maxlength=50></td>
</tr>

<tr bgcolor="#CCCCFF">
<td><b>
<?php if ($texterr) { echo "<font color=red>"; } ?>
Text
<?php if ($texterr) { echo "</font>"; } ?>
</b></td>
<td colspan=3><input type=text name=xtext value="<?php $a=htmlentities(stripslashes($ftext)); echo "$a"; ?>" size=50 maxlength=100></td>
</tr>

</table>

<?php
if ($fdateid) {
	echo '<p><input type=submit name=xdoupdate value=Update> ';
	echo "<input type=submit name=xdonew value=Duplicate> ";
	if ( $r[active] == 'y') {
		echo "<input type=submit name=xdodelete value=Delete>";
	}
}
else {
	echo '<p><input type="submit" name="xdocreate" value="New">';
}
echo "</form>";

if ($admin || $adminbook) {
	if ($xdateid) {
		#settype ($xeditdate, integer);
		$theid = $r[id];
		if ($r[parent]) { $theid = $r[parent]; }
		$wquery = "SELECT * FROM booking WHERE id = $q$theid$q
				OR parent = $theid ORDER by id;";
		$wresult = pg_query($db, $wquery);
		$wnum = pg_num_rows($wresult);
		echo "<p><table border=0 cellspacing=2 cellpadding=2 bgcolor=white>";
		echo "<tr bgcolor=lightgrey>";
		echo "<th align=left>Action</th>";
		echo "<th align=left>Who did it</th>";
		echo "<th align=left>When it was done</th>";
		echo "<th align=left>Resource</th>";
		echo "<th align=left>Who For</th>";
		echo "<th align=left>Date</th>";
		echo "<th align=left>Start Time</th>";
		echo "<th align=left>End Time</th>";
		echo "<th align=left>Text</th>";
		echo "</tr>";
		for ($j=0;$j<$wnum;$j++) {
			$r = pg_Fetch_array($wresult, $j, PGSQL_ASSOC);
			$datetime = date ("d/m/Y h:ia", $r[modifywhen]);
			$status = "Changed";
			if ($j == 0) { $status = "Created"; }
			if ($r[active] == 'x') { $status = "Deleted"; }
			if ($r[active] == 'y') { $status = "Current"; }
			echo '<tr align=left bgcolor=lightgrey>';
			echo "<td>$status</td><td>".$name[$r[modifywho]]."</td><td>$datetime</td>";
			echo "<td>".$bthing[$r[bookedthing]]."</td><td>".$name[$r[createwho]]."</td><td>$r[date]</td>";
			echo "<td>$r[starttime]</td><td>$r[endtime]</td><td>".htmlentities($r[text]);
			echo "</td>";
			echo '</tr>';
		}
		echo "</TABLE>";
	}
}
?>

</BODY>
</HTML>
