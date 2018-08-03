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
		$uquery = "INSERT INTO file (name, size, description, whouploaded, whenuploaded,
				whomodified, whenmodified, category, status) VALUES (
		$q$xname$q,
		$q$size$q,
		$q$xdescription$q,
		$q$cid$q,
		$q$timenow$q,
		$q$cid$q,
		$q$timenow$q,
		$q$xcategory$q,
		$q$xstatus$q);";
		$uresult = pg_query($db, $uquery);
		$lastoid = pg_last_oid($uresult);
		$kquery = "SELECT id FROM file WHERE OID = $q$lastoid$q;";
		$kresult = pg_query($db, $kquery);
		$kr = pg_fetch_array($kresult, 0, PGSQL_ASSOC);
		$res = move_uploaded_file($_FILES['xuserfile']['tmp_name'], "$filestore$kr[id]");
		echo "<p>##$res##<P>";
		echo "<p>FILE UPLOADED";
		$goto = "Location: http://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/fileedit.php?xref=".$kr[id];
		echo "<p>$goto";
		if ($kresult) { header($goto); }
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
<input type=hidden name=xref value=<?php=$xref?>>
<p><input type=submit name=xupdate value="Upload Now">
</form>


</BODY>
</HTML>
