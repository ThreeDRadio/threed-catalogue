<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0"); ?>

<HTML>

<head>
	<TITLE>ThreeD - MusicDB Edit</TITLE>
	<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
	<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
	<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</head>

<BODY onload="document.forms[0].xtext.focus()">

	<?php
	if (!$admin && $user['cdeditor'] != "t") {
		echo "<p><font color=red><b>You do not have the necessary privileges to do that!</b></font><p>";
		echo "</BODY></HTML>";
		exit;
	}

	echo "<B>MUSIC CATALOGUE - EDIT ENTRY</B>";

	settype($xtr, "integer");
	settype($xref, "integer");

	if ($xref) {
		$query = "SELECT * FROM cd WHERE id = $q$xref$q;";
		$result = pg_query($db, $query);
		$num = pg_num_rows($result);
		if ($num != 1) {
			echo "<p><b>That Entry does not exist - $xref</b>";
			echo "</BODY>";
			echo "</HTML>";
			exit;
		}
		$r = pg_Fetch_array($result, 0, PGSQL_ASSOC);
		$xstatus = $r['status'];
		if ($user['admin'] != "t" && $r['status'] == 2) {
			echo "<p><b>You cannot edit that Entry - $xref</b>";
			echo "</BODY>";
			echo "</HTML>";
			exit;
		}
	}

	if ($xdodelete && $user['admin'] == "t") {
		echo "<form action=cdedit.php method=post>";
		echo "<input type=hidden name=xref value=$xref>";
		echo "<input type=hidden name=xreallydelete value=yes>";
		echo "<p><font color=red><b>Really Delete This Entry?</b></font>";
		echo "&nbsp;&nbsp;&nbsp;<input type=submit name=xdelyes value=\"Yes\">";
		echo "&nbsp;&nbsp;&nbsp;<input type=submit name=xdelno value=\"No\">";
		echo "</form>";
	}

	if ($xdelyes && $user['admin'] == "t") {
		$dquery = "DELETE FROM cdtrack WHERE cdid = $q$xref$q;";
		$dresult = pg_query($db, $dquery);
		$dquery = "DELETE FROM cd WHERE id = $q$xref$q;";
		$dresult = pg_query($db, $dquery);

		echo "<p><b>Entry Deleted</b>";
		echo "</BODY>";
		echo "</HTML>";
		exit;
	}

	if ($xdosave || $xdoswap || $xdocreate) {
		$xartist = preg_replace("/ +/", " ", trim($xartist));
		$xtitle = preg_replace("/ +/", " ", trim($xtitle));
		$xgenre = preg_replace("/ +/", " ", trim($xgenre));
		settype($xnumtracks, "integer");
		if ($xnumtracks < 0) {
			$xnumtracks = 0;
		}
		if (preg_match("|(.*)/(.*)/(.*)|", $xarrivaldate, $matches)) {
			$xarrivaldate = $matches[2] . "/" . $matches[1] . "/" . $matches[3];
		}
		#if (preg_match("|([.|..])-(.*)-(.*)|", $xarrivaldate, $matches)) { $xarrivaldate = $matches[2]."/".$matches[1]."/".$matches[3]; }
		$temp = $xarrivaldate;
		$todayN = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		$thedayN = strtotime($xarrivaldate);
		if ($thedayN == -1 || $thedayN == "") {
			$thedayN = $todayN;
		}
		//	if ($thedayN == -1) { $xarrivaldate = "0001-01-01"; }
		else {
			$xarrivaldate = date("Y-m-d", $thedayN);
		}
		//	if ($temp == "") { $xarrivaldate = "0001-01-01"; }
		settype($xcopies, "integer");
		$xcompany = preg_replace("/ +/", " ", trim($xcompany));
		$xcpa = preg_replace("/ +/", " ", trim($xcpa));
		settype($xcompilation, "integer");
		if ($xcompilation < 0 || $xcompilation > 2) {
			$xcompilation = 0;
		}
		settype($xfemale, "integer");
		if ($xfemale < 0 || $xfemale > 3) {
			$xfemale = 0;
		}
		settype($xlocal, "integer");
		if ($xlocal < 0 || $xlocal > 3) {
			$xlocal = 0;
		}
		settype($xdemo, "integer");
		if ($xdemo < 0 || $xdemo > 2) {
			$xdemo = 0;
		}
		settype($xyear, "integer");
		if ($xyear < 1000 || $xyear > 2100) {
			$xyear = 0;
		}
		settype($xstatus, "integer");
		if ($xstatus < 1 || $xstatus > 2) {
			$xstatus = 0;
		}
		settype($xformat, "integer");
		if ($xformat < 1 || $xformat > 7) {
			$xformat = 0;
		}

		$trackcount = count($xtrackartist);

		for ($i = 0; $i < $trackcount; $i++) {
			if (preg_match("|(.*)[:\.;,](.*)[:\.;,](.*)|", $xtracklength[$i], $matches)) {
				settype($matches[1], "integer");
				settype($matches[2], "integer");
				settype($matches[3], "integer");
				$seconds = $matches[1] * 60 * 60 + $matches[2] * 60 + $matches[3];
				$xtracklength[$i] = $seconds;
			} elseif (preg_match("|(.*)[:\.;,](.*)|", $xtracklength[$i], $matches)) {
				settype($matches[1], "integer");
				settype($matches[2], "integer");
				$seconds = $matches[1] * 60 + $matches[2];
				$xtracklength[$i] = $seconds;
			} else {
				settype($xtracklength[$i], "integer");
			}
		}
	}

	if ($xdocreate) {
		// Define the creation statements for both cd and cdtrack
		$uquery = "INSERT INTO cd (
			artist, 
			title, 
			year, 
			genre, 
			company,
			cpa, 
			arrivaldate, 
			copies, 
			compilation, 
			demo, 
			local, 
			female,
			createwho, 
			createwhen, 
			modifywho, 
			modifywhen, 
			comment, 
			status, 
			format
		) VALUES (
			$1,
			$2,
			$3,
			$4,
			$5,
			$6, 
			$7,
			$8, 
			$9, 
			$10, 
			$11,
			$12, 
			$13, 
			$14, 
			$15, 
			$16, 
			$17, 
			$18, 
			$19
		) 
		RETURNING id;";

		$ttquery = "INSERT INTO cdtrack (
			cdid, 
			tracknum, 
			tracktitle, 
			trackartist, 
			tracklength
		) VALUES (
			$1,
			$2,
			$3,
			$4, 
			$5
		);";

		// Prepare the new insert statements for usage.
		pg_prepare($db, "new_cd_insert", $uquery);
		pg_prepare($db, "new_cd_track_insert", $ttquery);

		$timenow = time();

		// Insert the CD itself
		$cd_inputs_array = [
			$xartist,
			$xtitle,
			$xyear,
			$xgenre,
			$xcompany,
			$xcpa,
			$xarrivaldate,
			$xcopies,
			$xcompilation,
			$xdemo,
			$xlocal,
			$xfemale,
			// When a new CD is added, the creator/creation date and the modifier/modification date are the same.
			$cid,
			$timenow,
			$cid,
			$timenow,
			$xcomment,
			$xstatus,
			$xformat
		];

		$uresult = pg_execute($db, "new_cd_insert", $cd_inputs_array);

		if ($uresult && pg_num_rows($uresult) > 0) {
			$id_of_new_row = pg_fetch_row($uresult)[0];

			for ($i = 0; $i < count($xtrackartist); $i++) {
				$cdtrack_inputs_array = [
					$id_of_new_row,
					$i + 1,
					$xtracktitle[$i],
					$xtrackartist[$i],
					$xtracklength[$i],
				];

				$ttresult = pg_execute($db, "new_cd_track_insert", $cdtrack_inputs_array);
			}

			header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/cdshow.php?xref=" . $id_of_new_row);
		}
	}

	if ($xdosave || $xdoswap) {

		$uquery = "
			UPDATE 
				cd 
			SET
				artist=$1,
				title=$2,
				year=$3,
				genre=$4,
				company=$5,
				cpa=$6,
				arrivaldate=$7,
				copies=$8,
				compilation=$9,
				demo=$10,
				local=$11,
				female=$12,
				comment=$13,
				modifywho=$14,
				modifywhen=$15,
				status=$16,
				format=$17
			WHERE 
				id = $18;
		";

		$tquery = "
			UPDATE 
				cdtrack 
			SET
				tracktitle=$1,
				trackartist=$2,
				tracklength=$3
			WHERE 
				trackid = $4;
		";

		pg_prepare($db, "existing_cd_update", $uquery);
		pg_prepare($db, "existing_cdtrack_update", $tquery);

		$timenow = time();

		// Update the CD
		$cd_inputs_array = [
			$xartist,
			$xtitle,
			$xyear,
			$xgenre,
			$xcompany,
			$xcpa,
			$xarrivaldate,
			$xcopies,
			$xcompilation,
			$xdemo,
			$xlocal,
			$xfemale,
			$xcomment,
			$cid,
			$timenow,
			$xstatus,
			$xformat,
			$xref,
		];

		$uresult = pg_execute($db, "existing_cd_update", $cd_inputs_array);

		if ($uresult) {
			for ($i = 0; $i < count($xtrackartist); $i++) {
				$cdtrack_inputs_array = [
					$xtracktitle[$i],
					$xtrackartist[$i],
					$xtracklength[$i],
					$xtrackid[$i],
				];

				$tresult = pg_execute($db, "existing_cdtrack_update", $cdtrack_inputs_array);
			}
		}

		if ($xdosave) {
			header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/cdshow.php?xref=" . $xref);
		}
	}

	if ($xdoswap) {
		$squery = "SELECT * FROM cdtrack WHERE cdid = $q$xref$q;";
		$sresult = pg_query($db, $squery);

		for ($i = 0; $i < pg_num_rows($sresult); $i++) {
			// Fetch the existing information for this track.
			$sr = pg_fetch_array($sresult, $i, PGSQL_ASSOC);

			// Reuse the cdtrack update query with swapped title/artist parameters.
			$cdtrack_swap_inputs_array = [
				$sr['trackartist'],
				$sr['tracktitle'],
				$sr['tracklength'],
				$sr['trackid'],
			];

			$wresult = pg_execute($db, "existing_cdtrack_update", $cdtrack_swap_inputs_array);
		}
	}

	if ($xref) {
		$query = "SELECT * FROM cd WHERE id = $q$xref$q;";
		$result = pg_query($db, $query);

		if ($result && pg_num_rows($result) != 1) {
			echo "<p><b>PANIC!! - $xref</b>";
			echo "</BODY>";
			echo "</HTML>";
			exit;
		}
		$r = pg_fetch_array($result, 0, PGSQL_ASSOC);
	}

	echo "<form action=cdedit.php method=post>";
	echo "<input type=hidden name=xref value=$xref>";

	if ($xref) {
		echo "<p><input type=submit name=xdosave value=\"Save Changes\">";
		if ($user['admin'] == "t") {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=xdodelete value=\"Delete Entry\">";
		}
	} else {
		echo "<p><input type=submit name=xdocreate value=\"Create Entry\">";
	}


	echo "<p><TABLE border=1 cellpadding=3 cellspacing=0 bgcolor=#CCCCFF>";

	?>
	<tr bgcolor="#CCCCFF">
		<td><b>ID Number</b></td>
		<td><?php $a = sprintf("%07.0f", $r['id']);
		echo "$a"; ?></td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Artist</b></td>
		<td><input type=text name=xartist value="<?php $a = htmlentities(stripslashes($r['artist']));
		echo "$a"; ?>" size=50 maxlength=150></td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Title</b></td>
		<td><input type=text name=xtitle value="<?php $a = htmlentities(stripslashes($r['title']));
		echo "$a"; ?>" size=50 maxlength=150></td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Release Year</b></td>
		<?php
		$a = $r['year'];
		if (!$xref) {
			$a = date("Y");
		}
		if ($a == 0) {
			$a = "";
		}
		?>
		<td><input type=text name=xyear value="<?php echo "$a" ?>" size=6 maxlength=150></td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Genre</b></td>
		<td><input type=text name=xgenre value="<?php $a = htmlentities(stripslashes($r['genre']));
		echo "$a"; ?>" size=50 maxlength=50></td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Company</b></td>
		<td><input type=text name=xcompany value="<?php $a = htmlentities(stripslashes($r['company']));
		echo "$a"; ?>" size=50 maxlength=150></td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Country</b></td>
		<td><input type=text name=xcpa value="<?php $a = htmlentities(stripslashes($r['cpa']));
		echo "$a"; ?>" size=50 maxlength=150></td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Arrival Date</b></td>
		<?php
		if ($r['arrivaldate'] == -1 || $r['arrivaldate'] == "") {
			$thedayN = $todayN;
			$a = date("d/m/Y");
		} else {
			$thedayN = strtotime($r['arrivaldate']);
			$a = date("d/m/Y", $thedayN);
		}
		?>
		<td><input type=text name=xarrivaldate value="<?php echo htmlentities("$a") ?>" size=20 maxlength=150></td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Format</b></td>
		<td>
			<?php
			$a = $r['format'];
			if (!$xref) {
				$a = 1;
			}
			?>
			<select name=xformat>
				<option value=0<?php if ($a == 0) {
					echo " selected";
				} ?>>Unknown</option>
				<option value=1<?php if ($a == 1) {
					echo " selected";
				} ?>>Compact Disc</option>
				<option value=2<?php if ($a == 2) {
					echo " selected";
				} ?>>7" Vinyl</option>
				<option value=3<?php if ($a == 3) {
					echo " selected";
				} ?>>12" Vinyl</option>
				<option value=4<?php if ($a == 4) {
					echo " selected";
				} ?>>Cassette</option>
				<option value=5<?php if ($a == 5) {
					echo " selected";
				} ?>>Reel</option>
				<option value=6<?php if ($a == 6) {
					echo " selected";
				} ?>>Minidisc</option>
				<option value=7<?php if ($a == 7) {
					echo " selected";
				} ?>>MP3</option>
			</select>
		</td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Compilation</b></td>
		<td>
			<input type=radio id=2 name=xcompilation value=1<?php if ($r['compilation'] == 1) {
				echo " checked";
			} ?>>No</input>
			<input type=radio id=2 name=xcompilation value=2<?php if ($r['compilation'] == 2) {
				echo " checked";
			} ?>>Yes</input>
		</td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Demo</b></td>
		<td>
			<input type=radio id=2 name=xdemo value=1<?php if ($r['demo'] == 1) {
				echo " checked";
			} ?>>No</input>
			<input type=radio id=2 name=xdemo value=2<?php if ($r['demo'] == 2) {
				echo " checked";
			} ?>>Yes</input>
		</td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Local</b></td>
		<td>
			<input type=radio id=2 name=xlocal value=1<?php if ($r['local'] == 1) {
				echo " checked";
			} ?>>No</input>
			<input type=radio id=2 name=xlocal value=2<?php if ($r['local'] == 2) {
				echo " checked";
			} ?>>Yes</input>
			<input type=radio id=2 name=xlocal value=3<?php if ($r['local'] == 3) {
				echo " checked";
			} ?>>Some</input>
		</td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Female</b></td>
		<td>
			<input type=radio id=2 name=xfemale value=1<?php if ($r['female'] == 1) {
				echo " checked";
			} ?>>No</input>
			<input type=radio id=2 name=xfemale value=2<?php if ($r['female'] == 2) {
				echo " checked";
			} ?>>Yes</input>
			<input type=radio id=2 name=xfemale value=3<?php if ($r['female'] == 3) {
				echo " checked";
			} ?>>Some</input>
		</td>
	</tr>

	<tr bgcolor="#CCCCFF">
		<td><b>Copies</b></td>
		<?php
		$a = $r['copies'];
		if ($a == "") {
			$a = 1;
		}
		if ($a == 0) {
			$a = "";
		}
		?>
		<td><input type=text name=xcopies value="<?php echo "$a" ?>" size=5 maxlength=150></td>
	</tr>

	<?php
	if ($user['admin'] == "t") {
		echo "<tr bgcolor=#CCCCFF>";
		echo "<td><b>Status</b></td><td>";
		echo "<input type=radio id=2 name=xstatus value=0";
		if ($r['status'] == 0) {
			echo " checked";
		}
		echo ">Unchecked</input>";
		echo "<input type=radio id=2 name=xstatus value=1";
		if ($r['status'] == 1) {
			echo " checked";
		}
		echo ">Incomplete</input>";
		echo "<input type=radio id=2 name=xstatus value=2";
		if ($r['status'] == 2) {
			echo " checked";
		}
		echo ">Final</input>";
		echo "</td></tr>";
	}
	?>

	<tr valign=top bgcolor="#CCCCFF">
		<td><b>Editing<br>Comments</b></td>
		<td>
			<textarea name="xcomment" rows="4"
				cols="50"><?php echo htmlentities(stripslashes($r['comment'])) ?></textarea>
		</td>
		</td>
	</tr>

	<?php
	echo "</TABLE>";

	if ($xref) {
		echo "<p><input type=submit name=xdoswap value=\"Swap Track Artist and Track Title\">";
	}

	if ($xref) {
		$query = "SELECT * FROM cdtrack WHERE cdid = $q$xref$q ORDER by tracknum;";
		$result = pg_query($db, $query);
		$num = pg_num_rows($result);
	} else {
		$num = $xtr;
	}

	echo "<p><TABLE border=1 cellpadding=3 cellspacing=0 bgcolor=#CCFFCC>";
	echo "<TR bgcolor=#AADDAA><TD valign=top><b>Track<b></b></TD><TD valign=top><b>Artist</b></TD><TD valign=top><b>Title</b></TD><TD valign=top><b>Length</b></TD></TR>";

	for ($i = 0; $i < $num; $i++) {
		if ($xref) {
			$r = pg_Fetch_array($result, $i, PGSQL_ASSOC);
		}
		$ii = $i + 1;
		echo "<TR><TD align=center>" . $ii . "</TD>";
		$a = htmlentities(stripslashes($r['trackartist']));
		$b = htmlentities(stripslashes($r['tracktitle']));
		$ttt = $r['tracklength'];
		$min = 0;
		$min = floor($ttt / 60);
		$sec = $ttt % 60;
		$c = sprintf("%1d", $min) . ":" . sprintf("%02d", $sec);
		if ($ttt == 0) {
			$c = "";
		}
		echo "<TD><input type=text name=xtrackartist[] value=\"$a\" size=20 maxlength=150></TD>";
		echo "<TD><input type=text name=xtracktitle[] value=\"$b\" size=40 maxlength=150></TD>";
		echo "<TD><input type=text name=xtracklength[] value=\"$c\" size=6 maxlength=150></TD>";
		echo "<input type=hidden name=xtracknum[] value=\"$r[tracknum]\">";
		echo "<input type=hidden name=xtrackid[] value=\"$r[trackid]\">";
		echo "</TR>";
	}

	echo "</TABLE>";

	if ($xref) {
		echo "<p><input type=submit name=xdosave value=\"Save Changes\">";
		if ($user['admin'] == "t") {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=xdodelete value=\"Delete Entry\">";
		}
	} else {
		echo "<p><input type=submit name=xdocreate value=\"Create Entry\">";
	}

	echo "</form>";
	?>

</BODY>

</HTML>