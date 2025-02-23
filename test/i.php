<?php require_once("dbconnection.php");


$result=mysqli_query($connect,"select * from tonys order by id desc");
?>

<!DOCTYPE HTML>

<html>

<body>

<div>
<a href="insert.php">Add new member</a>
</div>
<br>

<table border=0 width="80%">
<tr bgcolor="#DDDDDD">
<th>Id</th>
<th>Name</th>
<th>Mark</th>
<th>Update</th>
</tr>

<?php

$sql="select * from tonys";


while($row=mysqli_fetch_array($result)){

echo "<tr>";
echo "<td>".$row['id']."</td>";
echo "<td>".$row['name']."</td>";
echo "<td>".$row['Mark']."</td>";
echo "<td><a href=\"edit.php?id=$row[id]\">Edit</a> | <a href=\"delete.php?id=$row[id]\">Delete</a></td>";
echo "</tr>";
}


?>
</table>

</body>
</html>
