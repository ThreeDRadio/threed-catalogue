<?php require("verify.php");
#### User has logged in and been verified ####

settype ($xperpage, "integer");
$xperpage = 1000;
?>

<HTML>
<head>
<TITLE>ThreeD - Generate New Releases List</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>
<BODY onload="document.forms[0].xwords.focus()">

<B>MUSIC CATALOGUE - GENERATE NEW RELEASES LIST</B>

<form action=list_new_releases.php method=post>
<input type=hidden name=xdosearch value=1>

<p><table border=0 cellspacing=0 cellpadding=5>
<tr bgcolor="#CCCCCC">
<td><b>Search For</b></td>
<td><input type=text name=xwords value='<?php echo htmlentities(stripslashes($xwords))?>' size=50 maxlength=100 onOpen="document.myform.myfield.focus();document.myform.myfield.select()"></td>


<td align=right>
<p><input type=submit name=xbutton value=Search>
</td>
</tr>
<tr bgcolor="#CCCCCC">
<td><b>Order</b></td>
<td colspan=2>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

</td>
</tr>
</table>


<?php
#echo htmlentities($xwords);

//$xwords = addslashes($xwords); #################

if ($xdosearch) {
	//Sort order is most recent first
	$query = "SELECT artist,title,arrivaldate,local,demo,cpa,female FROM cd WHERE arrivaldate>(CURRENT_DATE-60) ORDER BY artist ASC;";
	#echo "<p>" . htmlentities($query) . "<p>";
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	echo "<p><table border=0 cellspacing=0 cellpadding=3>\n";
	echo "<tr><td><b>$num match";
	if ($num != 1) { echo "es"; }
	echo " found</b></td>";
	
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
		echo "<tr><th align=left>Artist</th><th align=left>Album Title</th><th align=center>Date Received</th><th align=center>Quotas</th></tr>\n";
		for ($i=$start-1;$i<$end;$i++) {
			echo "<TR valign=top bgcolor=#";
			if ($i % 2 == 0) { echo "CCFFCC"; } else { echo "CCCCFF"; }
			echo ">";
			$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
			
			$a = htmlentities(stripslashes($r[artist]));
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			$a = htmlentities(stripslashes($r[title]));
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			if ($r[arrivaldate] == "0001-01-01") { $a = ""; }
			else {
				$thedayN = strtotime($r[arrivaldate]);
				$a = date ("d/m/Y", $thedayN);
			}
			echo "<td align=center>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			echo "<td width=1 align=center>";
			echo "&nbsp;";
			
			if ($r[local] == "1") {
				echo "L&nbsp;";
			}
			else {
				echo "&nbsp&nbsp;";
			}
			if ($r[demo] == "1") {
				echo "D&nbsp;";
			}
			else {
				echo "&nbsp&nbsp;";
			}
			if ($r[australian] == "1") {
				echo "A&nbsp;";
			}
			else {
				echo "&nbsp&nbsp;";
			}
			if ($r[female] == "1") {
				echo "F&nbsp;";
			}
			else {
				echo "&nbsp&nbsp;";
			}

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
