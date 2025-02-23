<?php
require_once("dbconnection.php");

$id=$_GET['id'];

$res=mysqli_query($connect,"delete from tonys where id=$id");

echo "<script>window.location.href='index.php';</script>";
exit;


?>
