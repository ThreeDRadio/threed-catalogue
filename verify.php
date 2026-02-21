<?php ob_start();
#set_magic_quotes_runtime (1)
extract($_POST);
extract($_GET);
$cid = $_COOKIE["threed_id"];
settype ($cid, "integer");
$cpassword = $_COOKIE["threed_password"];
if (!$cpassword) { $cpassword = "nuffin"; }

############ Put your web proxy here or use "" for no proxy #########
#$webproxy = "casr.adelaide.edu.au:80";
$webproxy = "";
#####################################################################

############ Put the full path to the file store directory ##########
#$filestore = "/Library/WebServer/threedfiles/";
$filestore = "/data/webfiles/";
#####################################################################
# Needs the following in httpd.conf
# Uncomment rewrite module lines
# RewriteEngine  on
# RewriteRule    ^/threedfile/(.*)$   /database/download.php?xname=$1
# 
# Also, need to make sure the (virtual)host configutration contains
# AllowOverride All
# (the default is None & the URL re-writing won't work with that!)
############ Also need these in php.ini #############################
# post_max_size = 110M
# upload_max_filesize = 100M
# max_execution_time = 300
# error_reporting  =  E_ALL & ~E_NOTICE
# display_errors = On
# magic_quotes_gpc = On
#####################################################################

$db = pg_Connect ("host=localhost dbname=threed user=www password=tree");
if (!$db) { echo "Oops - Database Failure - Things really are not working well - sigh"; exit; }

$q = "'";
$t = "t";
$query = "SELECT * FROM users WHERE id = $q$cid$q AND password = $q$cpassword$q AND active = $q$t$q;";
$result = pg_query($db, $query);
$num = pg_num_rows($result);
if ($num != 1) {
	header("Location: https://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) ."/login.php");
	exit;
}

$user = pg_Fetch_array($result, 0, PGSQL_ASSOC);
if ($user["admin"] == "t") { $admin = 1; } else { $admin = 0; }
if ($user["adminbook"] == "t") { $adminbook = 1; } else { $adminbook = 0; }

$uquery = "SELECT * FROM users;";
$uresult = pg_query($db, $uquery);
$numusers = pg_num_rows($uresult);
for ($i=0;$i<$numusers;$i++) {
	$ur = pg_Fetch_array($uresult, $i, PGSQL_ASSOC);
	$a = $ur["first"];
	if ($ur["first"] && $ur["last"]) { $a .= " "; }
	$a .= $ur["last"];
	if (!$a) { $a = $ur["username"]; }
	$name[$ur["id"]] = htmlentities($a);
	$userid[$i] = $ur["id"];
	$namelist[$i] = htmlentities($a);
	$namelistU[$i] = strtoupper(htmlentities($a));
}
array_multisort ($namelistU, $namelist, $userid);
?>
