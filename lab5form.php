  <!-- Very samll Part to avoid warnings and write the all code in same file Using if -->
  <!-- isset checks the status of the button, whether it's set or not -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<form action="lab5form.php" method="post">
    enter a first number <input type="text" name="num1"><br>
    enter a second number <input type="text" name="num2"><br>
    <input type="Submit" name="btn1" value="ADD"> 
</form>
<?php
if(isset($_POST["btn1"]))
{
$x=$_POST["num1"];
$y=$_POST["num2"];
$sum= $x + $y;
echo" <h3> the sum of $x , $y is $sum </h3>";
}
?>
</body>
</html>

<!-- if i want to add more than 1 operator  -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<form action="lab5form.php" method="post">
    enter a first number <input type="text" name="num1"><br>
    enter a second number <input type="text" name="num2"><br>
    <input type="Submit" name="btn1" value="ADD"> 
    <input type="Submit" name="btn2" value="Multiply">
</form>
<?php
if(isset($_POST["btn1"]))
{
$x=$_POST["num1"];
$y=$_POST["num2"];
$sum= $x + $y;
echo"<h3> the sum of $x , $y is $sum </h3>";
}
elseif(isset($_POST["btn2"]))
{
    $x=$_POST["num1"];
    $y=$_POST["num2"];
    $prod= $x * $y;
    echo"<h3> the Multiplication of $x , $y is $prod </h3>";
}
?>
</body>
</html>