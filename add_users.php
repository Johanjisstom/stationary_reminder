<?php 

include("connect.php"); 

 

if ($_SERVER["REQUEST_METHOD"] == "POST") { 

  

    $User_name = $_POST['User_name']; 

 $User_email = $_POST['User_email']; 

 $Password = $_POST['Password']; 

 $User_role = $_POST['Role'];
  

 if (empty($User_name) || empty($User_email) || empty($Password) || empty($Role)) { 

 echo "Please fill in all fields."; 

 

 } else { 

 

 $sql = "INSERT INTO Users (User_name, User_email, Password, Role) 

 VALUES ('$User_name', '$User_email', '$Password', '$Role')"; 

 

 if (mysqli_query($conn, $sql)) { 

 echo "User has been added successfully!"; 

 } else { 

 echo "Error: " . mysqli_error($conn); 

 } 

 } 

} 

?> 

<!-- 1. USER FORM -->
<h2>User Registration Form</h2>
<form method="POST">
  
  Full Name:<br>
  <input type="text" name="User_name" required><br><br>
  Email:<br>
  <input type="email" name="User_email" required><br><br>
  Password:<br>
  <input type="password" name="Password" required><br><br>
  Role:<br>
  <select name="User_role" required>
    <option value="student">Student</option>
    <option value="teacher">Teacher</option>
  </select><br><br>
  <button type="submit" name="submit">Add User</button>
</form>
<hr>