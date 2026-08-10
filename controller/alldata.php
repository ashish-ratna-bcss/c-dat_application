<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Alldata");
?>

<style>
input[type=text], select {
    width: 30%;
    padding: 3px 10px;
    margin: 4px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 2px;
    box-sizing: border-box;
}

input[type=submit] {
    width: 20%;
    background-color: ORANGE;
    color: white;
    padding: 3px 10px;
    margin: 4px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

input[type=submit]:hover {

    background-color: #45a049;
}

div {
    border-radius: 20px;
    background-color: #f2f2f2;
    padding: 20px;
}
</style>
<body>


<form NAME="PSFORMS" action="alldata_search.php" onsubmit="validateForm()" method="post">
  <div class="form-group">
    <label class="sr-only" for="exampleInputEmail3"></label>
    <input type="TEXT" name="PHONE" placeholder="PHONE" required="REQUIRED">
 <input type="submit" value="Submit">

  </div>



</form>


<?php layout_end(); ?>
