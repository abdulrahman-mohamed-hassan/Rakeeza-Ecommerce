<!-- excute 1 to 100 using while loop -->
<?php
$i=1;
while($i<=100)
{
    echo"$i<br>";
    $i++;
}
?>


<!-- excute 1 to 100 using for loop -->
<?php
for($i=1;$i<=100;$i++)
{
    echo" $i <br>";
}
?>

<!-- Excute 1 to 100 using do-while -->
<?php
$i=1;
do
{
    echo"$i<br>";
    $i++;
}
while($i<=100);
?>

<!-- Example 1 / print the following 2,4,6,8..../ while, for, do-while-->
<!-- while loops -->
<?php
$i=2;
while($i<=20)
{
    echo"$i<br>";
    $i+2;
}
?>

<!-- for loops -->
<?php
for($i=2;$i<=20;$i+2)
{
    echo" $i <br>";
}
?>

<!-- do-while -->
<?php
$i=2;
do
{
    echo"$i<br>";
    $i+2;
}
while($i<=20);
?>

<!-- now the user enter the last number / using for loops -->
<?php
$m=$_POST["num1"];
for($i=1;$i<=$m;$i++)
{
    echo"$i <br>";
}
?>

<!-- now the user enter both 1st number and the last number /using loops -->
<?php
$x=$_POST["num1"];
$y=$_POST["num2"];
for($i=$x;$i<=$y;$i++)
{
    echo"$i<br>";
}
?>