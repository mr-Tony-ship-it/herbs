<?php

require_once("dbconnection.php");

$id=$_GET['id'];
?>

<!DOCTYPE HTML>

<body>

<div>

<form actoin="" method="POST">

Name:<input type="text" name="name"/>
Mark:<input type="text" name="mark"/>

<input type="submit" value="update"/>

<?php 
$name=$_POST['name'];

if(empty($name)){

echo "<font color='red'>Name filed is empty</font>";

echo "<br><a href='javascript:self.history.back()'>Go back</a>";
}else{

mysqli_query($connect,"update tonys set name='$name' where id=$id");

echo "<font color='green'>Updated</font>";

echo "<br><a href=\"index.php\">View result</a>";

}

?>

</body>

</html>
