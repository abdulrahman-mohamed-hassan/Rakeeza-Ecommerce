<?php
$num1 = 10;
$num2 = 4;
$sum = $num1 + $num2;
$minus = $num1 - $num2;
$product = $num1 * $num2;
$divide = ($num2 != 0) ? ($num1 / $num2) : 'undefined';
$modulus = ($num2 != 0) ? ($num1 % $num2) : 'undefined';
$average = ($num1 + $num2) / 2;

// rectangle
$width = 4;
$height = 6;
$area = $width * $height;
$perimeter = 2 * ($width + $height);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Web Programming - Assignment 1</title>
<style>
body {
  font-family: Arial, sans-serif;
  background: #f7f7f7;
  margin: 0;
  padding: 20px;
}
h1, h2 { color: #222; }

.card {
  background: #fff;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,.06);
  margin-bottom: 16px;
}
table {
  border-collapse: collapse;
  width: 100%;
  max-width: 500px;
}
th, td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: left;
}
th { background: #f0f0f0; }

.section-title {
  background-color: #ffcccc;
  padding: 5px 10px;
  font-size: 18px;
  font-weight: bold;
}

input[type="text"],
input[type="email"],
input[type="tel"],
input[type="date"],
select {
  width: 200px;
  padding: 4px;
  margin: 3px 0;
}

button, input[type="submit"], input[type="reset"] {
  margin-top: 10px;
  padding: 6px 12px;
  cursor: pointer;
}
form {
  background: #fff;
  padding: 16px;
  border: 1px solid #ccc;
  margin-bottom: 20px;
  max-width: 600px;
}
</style>
</head>
<body>

<h1>Web Programming — Assignment 1</h1>

<!-- Q1 -->
<div class="card">
  <h2>1) Please fill in your Request</h2>
  <form>
    <p><strong>Please fill in your Request</strong></p>

    <label>Full Name:</label>
    <input type="text" name="fullname"><br><br>

    <label>Phone:</label>
    <input type="tel" name="phone"><br><br>

    <label>Email:</label>
    <input type="email" name="email"><br><br>

    <label>Pickup Date:</label>
    <input type="date" name="pickup_date"><br><br>

    <label>Pickup Place:</label>
    <select name="pickup_place">
      <option>Nasr City</option>
      <option>Heliopolis</option>
      <option>Maadi</option>
    </select><br><br>

    <label>Dropoff Place:</label>
    <input type="text" name="dropoff"><br><br>

    <fieldset style="width:250px;">
      <legend>Which taxi do you require:</legend>
      <input type="radio" name="taxi" value="Car"> Car<br>
      <input type="radio" name="taxi" value="Van"> Van<br>
      <input type="radio" name="taxi" value="Tuk Tuk"> Tuk Tuk<br>
    </fieldset><br>

    <fieldset style="width:250px;">
      <legend>Extras:</legend>
      <input type="checkbox" name="extra1" value="Baby Seat"> Baby Seat<br>
      <input type="checkbox" name="extra2" value="Wheelchair Access"> Wheelchair Access<br>
      <input type="checkbox" name="extra3" value="Stock Tip"> Stock Tip<br>
    </fieldset><br>

    <input type="submit" value="send your request">
  </form>
</div>

<!-- Q2 -->
<div class="card">
  <h2>2) Tell us about you</h2>
  <form>
    <p><strong>Tell us about you</strong></p>

    <div class="section-title">Personal <span style="font-size:12px;">Your information is important</span></div>
    <label>Name:</label> <input type="text" name="name"><br>
    <label>Telephone:</label> <input type="tel" name="telephone"><br>
    <label>Gender:</label>
    <input type="radio" name="gender" value="Male"> Male
    <input type="radio" name="gender" value="Female"> Female<br>
    <label>Nationality</label>
    <select name="nationality">
      <option>Egyptian</option>
      <option>Not Egyptian</option>
    </select><br><br>
    

    <div class="section-title">Address <span style="font-size:12px;">Your information is important</span></div>
    <label>Address:</label> <input type="text" name="address"><br>
    <label>City:</label> <input type="text" name="city"><br>
    <label>State:</label> <input type="text" name="state">
    <label>Zip code:</label> <input type="text" name="zip"><br><br>

    <div class="section-title">Hobbies <span style="font-size:12px;">Your information is important</span></div>
    <label>I like:</label><br>
    <input type="checkbox" name="like1" value="Reading"> Reading
    <input type="checkbox" name="like2" value="Writing"> Writing
    <input type="checkbox" name="like3" value="Travelling"> Travelling<br><br>

    <label>Sports:</label>
    <select name="sports">
      <option>Tennis</option>
      <option>Football</option>
      <option>Swimming</option>
      <option>Handball</option>
    </select><br><br>

    <input type="submit" value="Submit Query">
    <input type="reset" value="Reset">
  </form>
</div>

<!-- Q3 -->
<div class="card">
  <h2>3) PHP program: Operations between <?= $num1 ?> and <?= $num2 ?></h2>
  <table>
    <tr><th>Operation</th><th>Expression</th><th>Result</th></tr>
    <tr><td>Sum</td><td><?= "$num1 + $num2" ?></td><td><?= $sum ?></td></tr>
    <tr><td>Minus</td><td><?= "$num1 - $num2" ?></td><td><?= $minus ?></td></tr>
    <tr><td>Product</td><td><?= "$num1 × $num2" ?></td><td><?= $product ?></td></tr>
    <tr><td>Divide</td><td><?= "$num1 ÷ $num2" ?></td><td><?= is_numeric($divide)?round($divide,4):$divide ?></td></tr>
    <tr><td>Modulus</td><td><?= "$num1 % $num2" ?></td><td><?= $modulus ?></td></tr>
    <tr><td>Average</td><td>(<?= "$num1 + $num2" ?>)/2</td><td><?= $average ?></td></tr>
  </table>
</div>

<!-- Q4 -->
<div class="card">
  <h2>4) Area and Perimeter of a Rectangle</h2>
  <p>Width = <?= $width ?> cm, Height = <?= $height ?> cm</p>
  <ul>
    <li>Area = <?= "$width × $height = $area cm²" ?></li>
    <li>Perimeter = <?= "2 × ($width + $height) = $perimeter cm" ?></li>
  </ul>
</div>

</body>
</html>
