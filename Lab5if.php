<!-- backend for if with radio button and dropdownlist -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
    $x=$_POST["gpa"];
    if($x==1)
    echo"<p style=color:green> Excellent </p>";
    elseif($x==2)
    echo"<p style=color:Purple> very good </p>";
    elseif($x==3)
    echo"<p style=color:orange> good </p>";
    elseif($x==4)
    echo"<p style=color:blue> pass </p>";
    elseif($x==5)
    echo"<p style=color:red> fail </p>";
?>
</body>
</html>
<br> <br> <br>
<!-- Backend for if mandatory data and confrim password -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
    $user=$_POST["Username"];
    $pwd=$_POST["Password"];
    $pwd2=$_POST["Password2"];
    if($user=="")
        echo"Invalid Entery, Username is Missing!";
    elseif($pwd=="")
        echo"Invalid Password, Password is Missing!";
    elseif($pwd2=="")
        echo"Invalid Password, Password is not Confirmed!";
    elseif($pwd!=$pwd2)
        echo"Error. Password Mismatch!";
    else
        echo"Successfully Regisered";
    ?>
</body>
</html>