<?php require("verify.php");
#### User has logged in and been verified ####

if (!isset($xwords)) {
  $xwords = '';
}
settype ($xsort, "integer");
if (!$xsort) { $xsort = 3; }
settype ($xperpage, "integer");
if ($xperpage < 5) $xperpage = 10;
if ($xperpage > 50) $xperpage = 50;
?>

<HTML>
<head>
<TITLE>ThreeD - MusicDB Search</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>
<BODY onload="document.forms[0].xwords.focus()">

<B>MUSIC CATALOGUE - QUICK SEARCH</B>

<form action=cdsearch.php method=post>
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

<select name=xsort>
<option value=3<?php if ($xsort == 3) { echo " selected"; } ?>>Most Recent First</option>
<option value=4<?php if ($xsort == 4) { echo " selected"; } ?>>Oldest First</option>
<option value=1<?php if ($xsort == 1) { echo " selected"; } ?>>Artist Alphabetical</option>
<option value=2<?php if ($xsort == 2) { echo " selected"; } ?>>Album Alphabetical</option>
</select>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<b>Matches per Page</b>
<select name=xperpage>
<option value=5<?php if ($xperpage == 5) { echo " selected"; } ?>>5</option>
<option value=10<?php if ($xperpage == 10) { echo " selected"; } ?>>10</option>
<option value=15<?php if ($xperpage == 15) { echo " selected"; } ?>>15</option>
<option value=20<?php if ($xperpage == 20) { echo " selected"; } ?>>20</option>
<option value=30<?php if ($xperpage == 30) { echo " selected"; } ?>>30</option>
<option value=40<?php if ($xperpage == 40) { echo " selected"; } ?>>40</option>
<option value=50<?php if ($xperpage == 50) { echo " selected"; } ?>>50</option>
</select>

</td>
</tr>
</table>


<?php
#echo htmlentities($xwords);

//$xwords = addslashes($xwords); #################

if (isset($xdosearch)) {
	if ($xsort == 4) { $qsort = "cd.arrivaldate"; }
	elseif ($xsort == 3) { $qsort = "cd.arrivaldate"; }
	elseif ($xsort == 2) { $qsort = "UPPER(cd.title), UPPER(cd.artist)"; }
	else { $qsort = "UPPER(cd.artist), UPPER(cd.title)"; }
	
	$query = "SELECT DISTINCT ON ($qsort, cd.id) *, cd.id AS cdidx FROM cd LEFT OUTER JOIN cdtrack ON cd.id = cdtrack.cdid LEFT OUTER JOIN cdcomment ON cd.id = cdcomment.cdid";
	$words = preg_replace("/,/"," ",$xwords);
	$words = preg_replace("/ +/"," ",$words);
	$words = trim($words);
	$words = explode(" ", $words);
	$words_array = [];
	$counting = count($words);

	if ($words[0] == "") $counting = 0;

	if ($counting) {
		for ($i=0;$i<$counting;$i++) {
			if ($i == 0) { $query .= " WHERE"; }
			if ($i > 0) { $query .= " AND"; }

			// Push this word with wildcards onto the parameters array.
			array_push($words_array, "%".stripslashes($words[$i])."%");
			// Add a parameter with the number of this word, starting at 1. 
			$this_word_statement_identifier = "$".($i+1);
			// Build this part of the query.
			$query = $query . " (cd.artist ~~* $this_word_statement_identifier
				OR cd.title ~~* $this_word_statement_identifier
				OR cd.genre ~~* $this_word_statement_identifier
				OR cd.company ~~* $this_word_statement_identifier
				OR cdtrack.tracktitle ~~* $this_word_statement_identifier
				OR cdtrack.trackartist ~~* $this_word_statement_identifier
				OR cdcomment.comment ~~* $this_word_statement_identifier
			)";
		}
	}
	else {
		$query = "SELECT cd.id as cdidx, * FROM cd";
	}
	
	if ($xsort == 3) { 
		$query = $query . " ORDER BY " . $qsort . " DESC, cd.id DESC;"; 
	}
	else { 
		$query = $query . " ORDER BY " . $qsort . ", cd.id;"; 
	}

	$prepared_statement = pg_prepare($db, "simple_cd_query", $query);
	$result = pg_execute($db, "simple_cd_query", $words_array);
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
		echo "<tr><th align=left>Artist</th><th align=left>Album Title</th><th align=center>Date Received</th><th align=center>Action</th></tr>\n";
		for ($i=$start-1;$i<$end;$i++) {
			echo "<TR valign=top bgcolor=#";
			if ($i % 2 == 0) { echo "CCFFCC"; } else { echo "CCCCFF"; }
			echo ">";
			$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
			
			$a = htmlentities(stripslashes($r['artist']));
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			$a = htmlentities(stripslashes($r['title']));
			echo "<td>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			//if ($r[digital] != 'f') {
                        //  echo ' <span style="color: #ff9933;">[DIGITAL]</span>';
                        //}
			echo "</td>\n";
			
			if ($r['arrivaldate'] == "0001-01-01") { $a = ""; }
			else {
				$thedayN = strtotime($r['arrivaldate']);
				$a = date ("d/m/Y", $thedayN);
			}
			echo "<td align=center>";
			if ($a) { echo "$a"; }
			else { echo "&nbsp;"; }
			echo "</td>\n";
			
			echo "<td width=1 align=center>";
			echo "<a HREF=cdshow.php?";
			echo "xref=" . $r['cdidx'] . ">Show<a>";
			
			if ($user['admin'] == "t" || ($user['cdeditor'] == "t" && $r['status'] != 2)) {
				echo "&nbsp;";
				echo "<a HREF=cdedit.php?";
				echo "xref=" . $r['cdidx'] . " target=_blank>Edit<a>";
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
