<?php

if (isset($_POST['signup_submit'])) {

    $User_name = trim($_POST['User_name']);
    $User_email = trim($_POST['User_email']);
    $Password = $_POST['Password'];
    $ConfirmPassword = $_POST['ConfirmPassword'];

    
    if ($Password !== $ConfirmPassword) {

        echo "<p>Passwords do not match.</p>";

    } else {


        $check_sql = "SELECT * FROM users WHERE User_email = ?";

        $check_stmt = $conn->prepare($check_sql);

        if (!$check_stmt) {
            die("Database error: " . $conn->error);
        }

        $check_stmt->bind_param("s", $User_email);
        $check_stmt->execute();

        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {

            echo "<p>This email is already registered.</p>";

        } else {


            $hashed_password = password_hash(
                $Password,
                PASSWORD_DEFAULT
            );


            $sql = "INSERT INTO users
                    (User_name, User_email, Password)
                    VALUES (?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("Database error: " . $conn->error);
            }

            $stmt->bind_param(
                "sss",
                $User_name,
                $User_email,
                $hashed_password
            );

            if ($stmt->execute()) {

                echo "<p>Account created successfully!</p>";

                echo "<p>
                        <a href='index.php?page=login'>
                            Login here
                        </a>
                      </p>";

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