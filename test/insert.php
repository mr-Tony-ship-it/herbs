
<?php 

require_once("dbconnection.php");

//$result=mysqli_query($connect,"insert into tonys ('name','Mark') values()")

?>

<!DOCTYPE HTML>

<html>

<body>

<h4>Insert Your Datas</h4>

<div>

<form action="" method="POST">

Name:<input type="text" name="name" /><br>
Mark:<input type="text" name="mark" /><br>
<br>
<input type="submit" name="sub" value="inert"/>
</form> 

<?php

$name=$_POST['name'];
$mark=$_POST['mark'];

if(empty($name)||empty($mark)||(!ctype_digit($mark))){

if(empty($name)){
echo "<font color='red'>Name field is empty</font>";
}
if(empty($mark)){
echo "<br><font color='red'>Mark field is empty</font>";
}
if(!is_int($mark)){
echo "<p><font color='red'>Mark filed in digit</font></p>";
}

echo "<br><a href='javascript:self.history.back();'>GO back</a>";

}else {

$result=mysqli_query($connect,"insert into tonys(`name`,`Mark`) values('$name','$mark')");

echo "<font color='green'>Data inserted successfuly</font>";

echo "<a href='index.php'>view the result</a>";

}

?>
