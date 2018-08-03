<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD Intranet</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
</HEAD>

<frameset cols="130,*" rows="*" border="0" frameborder="0"> 
  <frame src="menu.php" name="menu">
  <frame src="home.php"  name="main">
</frameset>

<noframes>
<body bgcolor="#FFFFFF">
	You must use a frames enabled browser...
</body>
</noframes>

</html>
