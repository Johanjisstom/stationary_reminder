<?php


require_once("connect.php");

$message = "";

if (isset($_POST['signup_submit'])) {

   
    $User_name = trim($_POST['User_name']);
    $User_email = trim($_POST['User_email']);
    $Password = $_POST['Password'];
    $ConfirmPassword = $_POST['ConfirmPassword'];

    if (
        empty($User_name) ||
        empty($User_email) ||
        empty($Password) ||
        empty($ConfirmPassword)
    ) {

        $message = "Please fill in all fields.";

    }

    
    elseif ($Password !== $ConfirmPassword) {

        $message = "The passwords do not match.";

    }

   
    elseif (!filter_var($User_email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    }

    else {

       
        $check_sql = "SELECT UserID FROM users WHERE User_email = ?";

        $check_stmt = $conn->prepare($check_sql);

        if (!$check_stmt) {
            die("Database error: " . $conn->error);
        }

        $check_stmt->bind_param("s", $User_email);

        $check_stmt->execute();

        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {

            $message = "An account with this email already exists.";

        }

        else {

           
            $hashed_password = password_hash(
                $Password,
                PASSWORD_DEFAULT
            );

            $Role = "student";

          
            $sql = "INSERT INTO users
                    (User_name, User_email, Password, Role)
                    VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("Database error: " . $conn->error);
            }

            $stmt->bind_param(
                "ssss",
                $User_name,
                $User_email,
                $hashed_password,
                $Role
            );

            if ($stmt->execute()) {

                $message = "Account created successfully!";

            }

            else {

                $message = "There was an error creating your account.";

            }

            $stmt->close();
        }

        $check_stmt->close();
    }
}

?>

<h2>Create an Account</h2>

<?php

if (!empty($message)) {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}

?>

<form method="POST" action="">

    <p>
        <label for="User_name">Username:</label><br>

        <input
            type="text"
            id="User_name"
            name="User_name"
            required
        >
    </p>


    <p>
        <label for="User_email">Email:</label><br>

        <input
            type="email"
            id="User_email"
            name="User_email"
            required
        >
    </p>


    <p>
        <label for="Password">Password:</label><br>

        <input
            type="password"
            id="Password"
            name="Password"
            required
        >
    </p>


    <p>
        <label for="ConfirmPassword">Confirm Password:</label><br>

        <input
            type="password"
            id="ConfirmPassword"
            name="ConfirmPassword"
            required
        >
    </p>


    <p>
        <input
            type="submit"
            name="signup_submit"
            value="Sign Up"
        >
    </p>

</form>

<p>
    Already have an account?
    <a href="index.php?page=login">Login here</a>
</p>