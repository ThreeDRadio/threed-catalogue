<?php
echo "Removed Slashes: ";
// Remove those slashes
if(get_magic_quotes_gpc())
	echo stripslashes($_POST['question']);
else
	echo $_POST['question'];
	
?>

<form method='post'>
Question: <input type='text' name='question'/><br />
<input type='submit'>

</form>
