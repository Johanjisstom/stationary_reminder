<h1>Login</h1> 
<p>Please log in to access your account.</p> 
<?php 
if (!empty($login_error)) { 
 echo "<p style='color:red;'>$login_error</p>"; 
} 
?> 
<form method="POST" action="index.php?page=Login" class="login-form"> 
 <label>Username:</label><br> 
 <input type="text" name="User_name" required><br><br> 
 <label>Password:</label><br> 
 <input type="password" name="Password" required><br><br> 
 <input type="submit" name="login_submit" value="Login" class="nav-btn btn-login"> 

</form> 

<br> 