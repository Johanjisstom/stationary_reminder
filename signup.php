<?php

if (isset($_POST['signup_submit'])) {

    $Username = $_POST['Username'];
    $Email = $_POST['Email'];
    $Password = $_POST['Password'];
    $ConfirmPassword = $_POST['ConfirmPassword'];


    if ($Password != $ConfirmPassword) {

        echo "<p>Passwords do not match.</p>";

    } else {

       
        $check_sql = "SELECT * FROM Users WHERE Email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $Email);
        $check_stmt->execute();

        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {

            echo "<p>This email is already registered.</p>";

        } else {

     
            $hashed_password = password_hash($Password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO Users (Username, Email, Password)
                    VALUES (?, ?, ?)";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "sss",
                $Username,
                $Email,
                $hashed_password
            );

            if ($stmt->execute()) {

                echo "<p>Account created successfully!</p>";
                echo "<a href='index.php?page=login'>Login here</a>";

            } else {

                echo "<p>There was an error creating your account.</p>";

            }

            $stmt->close();
        }

        $check_stmt->close();
    }
}

?>

<h2>Create an Account</h2>

<form method="POST">

    <label>Username:</label>
    <input
        type="text"
        name="Username"
        required
    >

    <br><br>

    <label>Email:</label>
    <input
        type="email"
        name="Email"
        required
    >

    <br><br>

    <label>Password:</label>
    <input
        type="password"
        name="Password"
        required
    >

    <br><br>

    <label>Confirm Password:</label>
    <input
        type="password"
        name="ConfirmPassword"
        required
    >

    <br><br>

    <input
        type="submit"
        name="signup_submit"
        value="Sign Up"
    >

</form>