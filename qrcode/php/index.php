<?php
echo "<form id='form1'  name='form' method='post' action='index.php'>
<input type='text' name='text' id='text' />
<input type='submit' name='generate' id='generate' value='generate' />
</form>";
$output=$_POST['text'];
echo "<img src='qr_img.php?d=$output'>";
?>