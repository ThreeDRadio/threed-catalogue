<?php require("verify.php");
#### User has logged in and been verified ####?>

<HTML>
<head>
<TITLE>ThreeD - File Search</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>
<BODY onload="document.forms[0].xwords.focus()">

<form action=files.php method=post>
<input type=hidden name=xdosearch value=1>

<table border=0 cellspacing=0 cellpadding=8>

<tr>
<td colspan=5><B>FILES - SEARCH</B></td>
</tr>

<tr bgcolor="#CCCCCC">
<td><b>Search For</b></td>
<td colspan=4><input type=text name=xwords value="<?php echo htmlentities(stripslashes($xwords))?>" size=50 maxlength=100></td>
</tr>

<tr bgcolor="#CCCCCC">

<td align=right><b>Category</b></td>

<td>
<select name="xcat">
<option value=0>[All Categories]</option>
<?php
$nquery = "SELECT * FROM filecat ORDER by UPPER(name), id;";
$nresult = pg_query($db, $nquery);
$nnum = pg_num_rows($nresult);
for ($i=0;$i<$nnum;$i++) {
	$nr = pg_Fetch_array($nresult, $i, PGSQL_ASSOC);
	$catname[$nr['id']] = htmlentities($nr['name']);
	#if ($nr[active] == 't') {
		echo "<option value=" . $nr['id'];
		if ($nr['id'] == $xcat) { echo " selected"; }
		echo ">".htmlentities($nr['name'])."</option>";
	#}
}
?>
</select>
</td>

<td align=right><b>Order By</b></td>
<td>
<select name=xsort>
<option value=0<?php if ($xsort == 0) { echo " selected"; } ?>>Newest First</option>
<option value=1<?php if ($xsort == 1) { echo " selected"; } ?>>Oldest First</option>
<option value=2<?php if ($xsort == 2) { echo " selected"; } ?>>File Name</option>
</select>
</td>

<td align=right>
<p><input type=submit name=xbutton value=Search>
</td>

</tr>

</table>

<?php
if ($xdosearch) {
	$query = "SELECT * FROM file WHERE";
	$words = preg_replace("/,/"," ",$xwords);
	$words = preg_replace("/ +/"," ",$words);
	$words = trim($words);
	#echo "#$words#<p>";
	$words = explode(" ", $words);
	for ($i=0;$i<count($words);$i++) {
		if ($i > 0) { $query .= " AND"; }
		$query = $query . " (name ~~* $q%$words[$i]%$q OR description ~~* $q%$words[$i]%$q)";
	}
	if ($xcat) { $query .= " AND category = $q$xcat$q"; }
	
	if (!$admin) { $query .= " AND status = '0'"; }
	
	if ($xsort == 2) { $query .= " ORDER by UPPER(name);"; }
	elseif ($xsort == 1) { $query .= " ORDER by whenuploaded;"; }
	else { $query .= " ORDER by whenuploaded DESC;"; }
	#echo htmlentities($query);
	$result = pg_query($db, $query);
	$num = pg_num_rows($result);
	
	echo "<p><table border=0 cellspacing=3 cellpadding=0>\n";
	echo "<tr><td><b>$num match";
	if ($num != 1) { echo "es"; }
	echo " found</b></td>";
	if (!$xmore) { $xend = 0; }
		$start = $xend+1;
		$end = $start+19;
		if ($end > $num) { $end = $num; }
		if ($num > 20) {
			echo "<td><b> - Showing matches $start to $end</b></td>";
		}
		if ($num > $end) { echo "<td> <input type=submit name=xmore value=More></td>"; }
	echo "</tr></table>";

	if ($num) {
		echo "<input type=hidden name=xend value=$end>";
		echo "<p>\n";
		echo "<p><TABLE border=1 cellpadding=2 cellspacing=0 bgcolor=#CCCCCC>\n";
		echo "<tr><th align=left>File Name</th><th align=left>Description</th><th align=right>Size</th><th align=left>Category</th><th align=center>Uploaded</th><th align=center>By</th><th align=center>Action</th></tr>\n";
		for ($i=$start-1;$i<$end;$i++) {
			$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
			if ($admin || $r['status'] == 0) {
				echo "<TR valign=top bgcolor=#";
				if ($r['status'] == 1) { echo "FFCCCC"; }
				elseif ($i % 2 == 0) { echo "CCFFCC"; } else { echo "CCCCFF"; }
				echo ">";
				$filename = preg_replace ("/[^A-Za-z0-9_.-]/", "", $r['name']);
				if (!$filename) { $filename = "UNKNOWN"; }
				echo "<p>" . $urlname . "<p>";
				echo "<td>";
				# Needs the following in httpd.conf for this virtual host
				# RewriteEngine  on
				# RewriteRule    ^/threedfile/(.*)$   /threed/download.php?xname=$1
				if ($filename) { echo "<a HREF=/threedfile/" . $r['id'] . "/" . $filename . ">$filename<a>"; }
				else { echo "NONAME"; }
				echo "</td>\n";
				
				$description = htmlentities($r['description']);
				echo "<td>";
				if ($description) {
					$description = preg_replace("/\n/","<br>",$description);
					echo $description;
				}
				else { echo "&nbsp;"; }
				echo "</td>\n";
				
				$tsize = $r['size'];
				if ($tsize < 1024) { $size = $tsize . "B"; }
				elseif ($tsize < 10240) { $size = round($tsize/1024*10)/10 . "K"; }
				elseif ($tsize < 1048064) { $size = round($tsize/1024) . "K"; }
				elseif ($tsize < 10433332) { $size = round($tsize/1048576*10)/10 . "M"; }
				else { $size = round($tsize/1048576) . "M"; }
				
				echo "<td align=right>";
				if ($size) { echo "$size"; }
				else { echo "&nbsp;"; }
				echo "</td>\n";
				
				echo "<td align=left>";
				if ($r['category']) { echo $catname[$r['category']]; }
				else { echo "&nbsp;"; }
				echo "</td>\n";
				
				$ddate = date ("d/m/Y", $r['whenuploaded']);
				echo "<td>";
				if ($ddate) { echo "$ddate"; }
				else { echo "&nbsp;"; }
				echo "</td>\n";
				
				$who = $name[$r['whouploaded']];
				echo "<td>";
				if ($who) { echo "$who"; }
				else { echo "&nbsp;"; }
				echo "</td>\n";
				
				echo "<td align=center>";
				if ($admin || $r['whouploaded'] == $cid) {
					echo "<a HREF=fileedit.php?";
					echo 'xref=' . $r['id'] . ">Edit<a>";
				}
				else { echo "&nbsp;"; }
				echo "</td>\n";
	
				echo "</TR>\n";
			}
		}
		echo "</TABLE>\n";
	}
}

else { echo "<br>Leave search field blank to find all files"; }


?>

</form>
</BODY>
</HTML>
