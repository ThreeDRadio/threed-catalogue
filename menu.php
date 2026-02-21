<?php require("verify.php");
require_once("config.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - Menu</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>
<BODY bgcolor="#CCCCCC">

<p><a href=home.php target=main><b>Home</b></a>


<p><b>Intranet</b>
<br><a href=setpassword.php target=main>-&nbsp;Set&nbsp;Password</a>


<p><b>Music&nbsp;Catalogue</b>
<br><a href=cdsearch.php target=main>-&nbsp;Quick&nbsp;Search</a>
<br><a href=cdsearchadv.php target=main>-&nbsp;Adv&nbsp;Search</a>
<?php
if ($admin || $user['cdeditor'] == 't') { 
	echo "<br><a href=cdnewentry.php target=main>-&nbsp;New&nbsp;Entry</a>";
	echo "<br><a href=session_report.php target=main>-&nbsp;Session&nbsp;Report</a>";
	echo "<br><a href=cdstats.php target=main>-&nbsp;Stats</a>";
	if ($admin) {
		echo "<br><a href=session_report_tot.php target=main>-&nbsp;User&nbsp;Totals</a>";
	}
}
?><!--<br><br><a href=../ripper.html target=main>-&nbsp;Processing Instructions</a>-->
<?php

echo "<p><b>Bookings</b>";
echo "<br><a href=bookings.php target=main>-&nbsp;Make&nbsp;Booking</a>";
if ($admin) {
	echo "<br><a href=assets.php target=main>-&nbsp;Manage&nbsp;Assets</a>";
}


// echo "<p><b>Lists</b>";
// echo "<br><a href=lists.php target=main>-&nbsp;View&nbsp;Lists</a>";
// if ($admin) {
// 	echo "<br><a href=listsmanage.php target=main>-&nbsp;Manage&nbsp;Lists</a>";
// }


// echo "<p><b>Files</b>";
// echo "<br><a href=files.php target=main>-&nbsp;Search</a>";
// echo "<br><a href=fileupload.php target=main>-&nbsp;Upload</a>";
// if ($admin) {
// 	echo "<br><a href=filecats.php target=main>-&nbsp;Categories</a>";
// }


if ($admin) {
	echo "<p><b>Admin</b>";
	echo "<br><a href=users.php target=main>-&nbsp;Show&nbsp;Users</a>";
	echo "<br><a href=adminusernew.php target=main>-&nbsp;New&nbsp;User</a>";
	echo "<br><a href=adminmotd.php target=main>-&nbsp;Edit&nbsp;MOTD</a>";
}

?>

<p><a href=logout.php target=_parent><b>Logout</b></a>
<p><small><b><?php echo $app_version ?></b></small></p>
</BODY>
</HTML>
