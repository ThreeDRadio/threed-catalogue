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
	if (!$admin && $user['cdeditor'] != "t") {
		echo "<p><font color=red><b>You do not have the necessary privileges to do that!</b></font><p>";
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

	if (!$xwords) {
		echo "</form>";
	} else {
		$words = preg_replace("/,/", " ", $xwords);
		$words = preg_replace("/ +/", " ", $words);
		$words = trim($words);
		$words = explode(" ", $words);
		$words_array = [];

		$query = "
			SELECT 
				* 
			FROM 
				cd 
			WHERE";

		for ($i = 0; $i < count($words); $i++) {
			// Push this word with wildcards into the array.
			array_push($words_array, "%" . stripslashes($words[$i]) . "%");
			// Define a corresponding parameter for the word just pushed onto the array.
			$this_word_statement_identifier = "$" . ($i + 1);

			if ($i > 0) {
				$query .= " AND";
			}

			$query = $query . " (
				artist ~~* $this_word_statement_identifier 
				OR title ~~* $this_word_statement_identifier
				OR genre ~~* $this_word_statement_identifier
				OR company ~~* $this_word_statement_identifier
			)";
		}

		$query = $query . " ORDER by UPPER(artist), UPPER(title);";
		pg_prepare($db, "new_cd_exists_query", $query);

		$result = pg_execute($db, "new_cd_exists_query", $words_array);
		$num = pg_num_rows($result);
		echo "<p><hr><p><b>$num match";
		if ($num != 1) {
			echo "es";
		}

		echo " found in the Catalogue</b>";
		echo "</form>";
		echo "<p><form action=cdedit.php method=post>";
		echo "<input type=submit name=xnewentry value=\"Create a New Entry\">";
		echo " with ";
		echo "<input type=text name=xtr value=10 size=4 maxlength=10></td>";
		echo " Tracks.";
		echo "</form>";

		echo "<p><HR>";
		if ($num) {
			for ($i = 0; $i < $num; $i++) {
				$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);

				$a = htmlentities($r['artist']);
				$b = htmlentities($r['title']);

				if ($i) {
					echo "<br>";
				} else {
					echo "<p>";
				}
				echo "<a HREF=cdshow.php?xref=" . $r['id'];
				echo ">$a / $b<a>\n";
			}
		}
	}
	?>
</BODY>

</HTML>