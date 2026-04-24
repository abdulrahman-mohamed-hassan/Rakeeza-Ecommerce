<!-- if without else -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="Lab3.php" method="post">
    enter a number <input type="text" name="num">
    <input type="Submit" Value="goto">
</form>
<?php 
$mark=$_POST["num"];
if($mark==100)
echo"you got the full mark";
?>
</body>
</html>



<!-- if with else (2 conditions) -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<form action="Lab3.php" method="post">
    enter a number <input type="text" name="num">
    <input type="Submit" Value="goto">
</form>
<?php
$mark=$_POST["num"];
if($mark >=50)
{
    echo "your mark is $mark <br>";
    echo "you passed";
}
else{
    echo "your mark is $mark<br>";
    echo "you failed";
}
?>
</body>
</html>


<!-- if with elseif / else (more than 2 conditions) -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="lab3.php" method="post">
        Enter a number <input type="text" name="num">
        <input type="Submit" value="Goto">
    </form>
    <?php
    $mark=$_POST["num"];
    if($mark>0)
    echo"$mark is Positive";
    elseif($mark<0)
    echo"$mark is Negative";
    else
    echo"$mark is neutral";
?>
</body>
</html>


<!-- Advanced example if with more than 2 conditions -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<body>
    <form action="lab3.php" method="post">
        Enter your GPA <input type="text" name="num">
        <input type="Submit" value="Goto">
    </form>
    <?php
    $x=$_POST["num"];
    if($x>=3.4 && $x<=4.0)
    echo"Excellent";
    elseif($x>=2.8 && $x<=3.39)
    echo"very good";
    elseif($x>=2.4 && $x<=2.79)
    echo"good";
    elseif($x>=2.0 && $x<=2.39)
    echo"pass";
    elseif($x>=0.0 && $x<=1.99)
    echo"failed";
    else
    echo"invalid GPA";
?>
</body>
</html>