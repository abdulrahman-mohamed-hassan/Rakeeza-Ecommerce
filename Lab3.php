<!-- Form Handling -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="Lab2.php" Method="post">
        number1:<input type="text" name="Num1"><br><br>
        number2:<input type="text" name="Num2"><br><br>
        <input type="Submit">  &nbsp; &nbsp;<input type="Reset">
</form>
<?php
$x = $_POST["Num1"];
$y = $_POST["Num2"];
$sum = $x + $y; 
echo"<br> <br>The sum of $x and $y is $sum";
?>
<br> <br> <br> <br>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="Lab2.php"> 
        <table>
            <tr><td> First Number: <input type="text" name="num1"></td></tr>
            <tr><td> Second Number: <input type="text" name="num2"></td></tr>
            <tr><td> Third Number: <input type="text" name="num3"></td></tr>
            <tr><td><input type="Submit"></td><td><input type="Reset"></td><tr>
        </table>
    </form>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
    $a = $_GET["num1"];
    $b = $_GET["num2"];
    $c= $_GET["num3"];
    $sum= $a + $b + $c;
    $Product= $a * $b * $c;
    $Average= $a + $b + $c / 3; 
    ?>
    <table cellpadding="5" cellspacing="2">
        <tr><td>Total</td><td>Product</td><td>Average</td>
        <tr><td><?php echo"$sum";?></td><td><?php echo"$Product";?></td><td><?php echo"$Average";?>
</td></tr>
</table>
</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="lab2.php" method="post">
        <p>Enter the number of apples:</p>
        <p> <input type="Text" name="Apples" size="3"> &nbsp; 
        <input type="Submit" Value="Submit Quiry"></p> 
    </form>
    <?php
    $apples= $_POST["Apples"];
    $dozens= (int)($apples/12);
    $Rem= $apples%12;
    echo"<b> $apples apples contain $dozens and $Rem remaining apples"
    ?>
</body>
</html>