<?php require("verify.php");
#### User has logged in and been verified ####
settype ($xperpage, "integer");
//echo "Date:$xdate";

if ($xdate == "")  $xdate = date("Y-m-d", strtotime("-7 days"));
if ($xdate2 == "") $xdate2 = date ("Y-m-d", strtotime("-1 days"));
if ($xperpage < 5) $xperpage = 200;
if ($xperpage > 200) $xperpage = 200;
if ($sdate =="") $sdate=cre;

?>
<HTML>
<head>
<TITLE>ThreeD - MusicDB Session Report Totals</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>
<BODY onload="document.forms[0].xdate.focus()">

<B>MUSIC CATALOGUE - SESSION REPORT TOTALS</B>

<form action=session_report_tot.php method=post>
<input type=hidden name=xdosearch value=1>

<p><table border=0 cellspacing=0 cellpadding=5>

<tr>
	<td>
	<font color=red>Shows individual totals for the selected period&nbsp;</font>
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
	<td align=left><b>Date to search:&nbsp;</b>
	<select name=sdate>
	<!--<option value="any"<?php //if ($sdate == "any") { echo " selected"; } ?>>Any Type</option>-->
	<option value="mod"<?php if ($sdate == "mod") { echo " selected"; } ?>>Date Modified</option>
	<option value="cre"<?php if ($sdate == "cre") { echo " selected"; } ?>>Date Created</option>
	<option value="arr"<?php if ($sdate == "arr") { echo " selected"; } ?>>Date Arrived</option>
	</select>
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
		$where="WHERE (cd.$when>=date'$xdate' AND  cd.$when<=date'$enddate')";
	}
	else {
		//use either modifydate or createdate (field type is bigint)
		$where="WHERE ('1970-01-01 00:00:00 GMT'::timestamp +
		((cd.$when::bigint)::text)::interval>=date'$xdate'
		AND  '1970-01-01 00:00:00 GMT'::timestamp +
		((cd.$when::bigint)::text)::interval<=date'$enddate')";
	}
	
	echo "</tr></table>";
}
//echo "WHERE query is:$where:";
?>


</form>
<p><b>Number of Entries Created by User</b>
<br><table border=1 cellspacing=0 cellpadding=3>
<tr><td><b>User</b></td><td align=right><b>Number</b></td></tr>
<?php
//$query = "SELECT createwho, count(*) FROM cd $where GROUP BY createwho, createwhen ORDER BY createwho DESC";
$query = "SELECT createwho, count(*) FROM (SELECT createwho FROM cd $where) AS date_range GROUP BY createwho ORDER BY count(*) ASC;";
/*$query = "SELECT createwho,count(createwho) AS "Items"
FROM (
            SELECT DISTINCT ON(createwho)
             log_date,ip_addr FROM http_log
           ) AS http_counter_tmp      
GROUP BY log_date
ORDER BY log_date DESC;"; */

//echo "SELECT query is:$query:<br>";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
for ($i=0;$i<$num;$i++) {
	$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
	$uquery = "SELECT * FROM users WHERE id = $r[createwho];";
	$uresult = pg_query($db, $uquery);
	$ur = pg_Fetch_array($uresult, 0, PGSQL_ASSOC);
	$a = $ur[first];
	if ($ur[first] && $ur[last]) { $a .= " "; }
	$b = $ur[last];
	$a .= $b[0];
	##$a = $a[0];
	if (!$a) { $a = $ur[username]; }
	$a = strtolower(htmlentities($a));
	echo "<tr><td>$a</td><td align=right>$r[count]</td></tr>";
}
?>
</table>
</BODY>
</HTML>

