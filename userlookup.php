<?php require("verify.php");
#### User has logged in and been verified ####

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: 0");?>

<HTML>
<head>
<TITLE>ThreeD - Lookup User</TITLE>
<LINK REL="StyleSheet" HREF="style.css" TYPE="text/css">
<META HTTP-EQUIV="Expires" CONTENT="Fri, Jun 12 1981 08:20:00 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<script type="text/javascript">
function SubForm(name,id){
window.opener.document.forms[0].xwho.value=name;
window.opener.document.forms[0].xwhoid.value=id;
window.close();}
</script>
</head>

<BODY onload="document.forms[0].xsearch.focus()">

<B>LOOKUP USER</B>
<form action=userlookup.php method=post name=myform>
<p><table border=0 cellspacing=0 cellpadding=4>
<tr bgcolor="#CCCCFF">
<td><b>Search For</b></td>
<td colspan=3>
<input type=text name=xsearch value="<?php $a=htmlentities(stripslashes($xsearch)); echo "$a"; ?>" size=30 maxlength=50>
<input type="submit" name="xdoit" value="Search">
</td>
</tr>
</table>
</form>


<?php ######Show Results of the Search #####################
$xsearch = trim($xsearch);
if ($xsearch) {
	$uquery = "SELECT * FROM users WHERE";
	$xsearch = preg_replace("/,/"," ",$xsearch);
	$xsearch = preg_replace("/ +/"," ",$xsearch);
	$xsearch = strtoupper(trim($xsearch));
	$words = explode(" ", $xsearch);
	for ($i=0;$i<$numusers;$i++) {
		$yup = 0;
		for ($j=0;$j<count($words);$j++) {
			if (strstr($namelistU[$i], $words[$j])) { $yup++; }
		}
		if ($yup == count($words)) {
			echo '<br><A HREF="javascript:SubForm(\'';
			echo $namelist[$i];
			echo "','";
			echo $userid[$i];
			echo '\')">';
			echo $namelist[$i];
			echo "</A>";
		}
	}
}
?>

</BODY>
</HTML>
