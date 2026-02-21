<?php require("verify.php");
#### User has logged in and been verified ####?>

<HTML>
<head>
<TITLE>ThreeD - Upload File</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>

<BODY>

<?php
echo "<b>FILES - UPLOAD</b>";

if ($check) { # uploading a file
	if (!is_uploaded_file($_FILES['xuserfile']['tmp_name'])) {
		echo "<p><font color=red><b>FILE COULD NOT BE UPLOADED</b></font>";
	}
	else {
		$size = $_FILES['xuserfile']['size'] ;
		$xname = trim ($_FILES['xuserfile']['name']);
		$xname = preg_replace ("/[^A-Za-z0-9_.-]/", "-", $xname);
		$xdescription = "";
		$xcategory = "0";
		$xstatus = "0";
		$timenow = time();
		$uquery = "INSERT INTO file (
			name, 
			size, 
			description, 
			whouploaded, 
			whenuploaded,
			whomodified, 
			whenmodified, 
			category, 
			status
		) VALUES (
			$q$xname$q,
			$q$size$q,
			$q$xdescription$q,
			$q$cid$q,
			$q$timenow$q,
			$q$cid$q,
			$q$timenow$q,
			$q$xcategory$q,
			$q$xstatus$q
		) RETURNING id;";

		$uresult = pg_query($db, $uquery);

		if ($uresult && pg_num_rows($uresult) > 0) { 
			$id_of_new_row = pg_fetch_row($uresult)[0];
			$res = move_uploaded_file($_FILES['xuserfile']['tmp_name'], "$filestore$id_of_new_row");
			echo "<p>##$res##<P>";
			echo "<p>FILE UPLOADED";
			$goto = "Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/fileedit.php?xref=".$id_of_new_row;
			echo "<p>$goto";
			header($goto);
		} else {
			header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/files.php");
		}
	}
}
?>


<form enctype="multipart/form-data" action=fileupload.php?check=1 method=post>
<input type="hidden" name="MAX_FILE_SIZE" value="104857600"> 

<p><table border=1 cellspacing=0 cellpadding=8>

<?php
echo "<tr>";
echo "<td bgcolor=#CCCCCC><b>File to Upload</b></td>";
echo "<td><input name=xuserfile type=file></td>";
echo "</tr>";
?>


</table>
<input type=hidden name=xref value=<?php $xref?>>
<p><input type=submit name=xupdate value="Upload Now">
</form>


</BODY>
</HTML>
