<?php ob_start();
require("verify.php");

# Needs the following in httpd.conf for this virtual host
# RewriteEngine  on
# RewriteRule    ^/threedfile/(.*)$   /threed/download.php?xname=$1

header ('Content-Type: text/html');

if (preg_match ("/(.*?)\/(.*)/", $xname, $r)) {
	$file = $r[1];
	$pre = $r[2];
	$post = $r[3];
}
else { die ("wrong format"); }

settype ($file, "integer");
	
#echo "<p>xname=$xname";
#echo "<p>File=$file";
#echo "<p>Pre=$pre";

if (!$pre)  { die ("need a name"); }

$filename = "$filestore$file";

#echo "<p>Path=$filename";

#exit;
#echo "file=$file<p>pre=$pre<p>post=$post<p>";

#if (strtolower($post) == 'doc')     { header ('Content-Type: application/msword'); }
#elseif (strtolower($post) == 'txt') { header ('Content-Type: application/text'); }
#elseif (strtolower($post) == 'pdf') { header ('Content-Type: application/pdf'); }
#elseif (strtolower($post) == 'rtf') { header ('Content-Type: application/rtf'); }
#else { header ('Content-Type: text/html'); die ("disallowed file type: $post");}

$query = "SELECT * FROM file WHERE id = $q$file$q;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
if ($num == 1) { $r = pg_fetch_array($result, 0, PGSQL_ASSOC); }
else {
	echo "<p><font color=red><b>File does not exist!!</b></font><p>";
	exit;
}

if (!$admin && $r[status] != 0) {
	echo "<p><font color=red><b>File has been deleted</b></font><p>";
	exit;
}







header ('Content-Type: application/binary');

if (file_exists ($filename)) {
	$fd = fopen ($filename, "rb");
	$length = filesize ($filename);
	header ("Content-Length: $length");
	$contents = fread ($fd, $length);
	echo $contents;
	fclose ($fd);
}
else { header ('Content-Type: text/html'); die ("file does not exist: $xname"); }

?>
