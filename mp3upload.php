<?php require("verify.php");
#### User has logged in and been verified ####
#Check that we have been sent here from cdedit.php with an ID number


# Determine user's IP address to filter out hi MP3 if it's not local
$remoteip = getenv("REMOTE_ADDR");
$bits = explode(".",$remoteip);
####Add this back in when finished!!!
#if ($bits[0].".".$bits[1].".".$bits[2] != "192.168.0") {echo "<b>You can't add MP3 files if you are not local!</b>";; die ;};
echo "";

#Check user has edit rights
if (!$admin && $user[cdeditor] != "t") {
	echo "<p><font color=red><b>You do not have the necessary privileges to do this!</b></font><p>";
	echo "</BODY></HTML>";
	exit;
}

?>

<HTML>
<head>
<TITLE>ThreeD - MusicDB  Upload MP3 files to database</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
</head>
<BODY onload="document.forms[0].xwords.focus()">

<B>MUSIC CATALOGUE - MP3 File Upload</B><p>
<p><b><font color=red>
NB THIS IS ONLY FOR UPLOADING MUSIC THAT IS NOT ON AUDIO CD - CDs SHOULD BE RIPPED USING GRIP!!!!
</font></b><p>
<p>To upload MP3 files to the database:
<p>1. Make sure your files are named correctly - just the track number and .mp3 - eg: 1.mp3 
<p>&nbspLeading zeros will be ignored if you have them (ie 01.mp3 is the same as 1.mp3).
<p>
<p>2. Click the button below with the folder icon and the MP3 file fro track 1.
<p>
<p>3. Click the <b>Upload</b> button, check the ID number is correct and click <b>Yes</b> to confirm.<p>
<p>


<?php
echo "<b>MP3 FILE UPLOAD</b>";

if ($check) { # uploading a file
	if (!is_uploaded_file($_FILES['xuserfile']['tmp_name'])) {
		echo "<p><font color=red><b>FILE COULD NOT BE UPLOADED</b></font>";
	}
	else {
		$size = $_FILES['xuserfile']['size'] ;
		$xname = trim ($_FILES['xuserfile']['name']);
		$xname = preg_replace ("/[^A-Za-z0-9_.-]/", "-", $xname);
		$timenow = time();
		$res = move_uploaded_file($_FILES['xuserfile']['tmp_name'], "$filestore$kr[id]");
		echo "<p>##$res##<P>";
		echo "<p>FILE UPLOADED";
		$goto = "Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/cdedit.php?xref=".$kr['id'];
		echo "<p>$goto";
		if ($kresult) { header($goto); }
	}
}
?>


<form enctype="multipart/form-data" action=mp3upload.php?check=1 method=post>
<input type="hidden" name="MAX_FILE_SIZE" value="104857600"> 

<p><table border=1 cellspacing=0 cellpadding=8>

<?php
echo "<tr>";
echo "<td bgcolor=#CCCCCC><b>Select first MP3 file</b></td>";
echo "<td><input name=xuserfile type=file></td>";
echo "</tr>";
?>


</table>
<input type=hidden name=xref value=<?php=$xref?>>
<p><input type=submit name=xupdate value="Upload Now">
</form>

</BODY>
</HTML> 
