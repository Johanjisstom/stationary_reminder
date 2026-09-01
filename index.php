<?php

session_start();

include("connect.php");

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$login_error = "";




if (isset($_POST['login_submit'])) {

    $User_name = $_POST['User_name'];
    $Password = $_POST['Password'];

    $stmt = $conn->prepare(
        "SELECT * FROM Users WHERE User_name = ?"
    );

    $stmt->bind_param("s", $User_name);

    $stmt->execute();

    $result = $stmt->get_result();

    if (mysqli_num_rows($result) == 1) {

        $row = mysqli_fetch_assoc($result);

        if (password_verify($Password, $row['Password'])) {

            $_SESSION['UserID'] = $row['UserID'];
            $_SESSION['User_name'] = $row['User_name'];

            header("Location: index.php");

            exit();

        } else {

            $login_error = "Invalid username or password.";

        }

    } else {

        $login_error = "Invalid username or password.";

    }

}



?>

<!DOCTYPE html>

<html>

<head>

    <link rel="stylesheet" href="style.css">

    <title>Stationery Reminder</title>

</head>


<body>




<div class="hero_image">

    <h1>Stationery Reminder</h1>

</div>





<?php if (isset($_SESSION['User_name'])) { ?>

    <div class="navigation-add">

        <nav>

            <a href="index.php?page=home">
                Home |
            </a>

            <a href="index.php?page=to_dolists">
                To-Do Lists |
            </a>

            <a href="index.php?page=sticky_notes">
                Sticky Notes |
            </a>

            <a href="index.php?page=stationery_checklist">
                Stationery Checklist |
            </a>

            <a href="index.php?page=search">
                Search
            </a>

            <a href="index.php?page=profile">
                Profile
            </a>

            <a href="index.php?page=logout">
                Logout
            </a>

        </nav>

    </div>


<?php } else { ?>


    <div class="navigation-add">

        <nav>

            <a href="index.php?page=home">
                Home
            </a>

            <a href="index.php?page=login">
                Login
            </a>

            <a href="index.php?page=signup">
                Sign Up
            </a>

        </nav>

    </div>


<?php } ?>


<br>


<div class="main-layout">


    <div class="content">


        <?php


        
        if ($page == "home") {

            include("home.php");


        

        } elseif ($page == "login") {

            include("login.php");



        } elseif ($page == "signup") {

            include("signup.php");


        

        } elseif ($page == "to_dolists") {

            include("to_do_list.php");


        

        } elseif ($page == "sticky_notes") {

            include("sticky_notes.php");


        

        } elseif ($page == "stationery_checklist") {

            include("stationery_checklist.php");


        

        } elseif ($page == "search") {

            include("search.php");



        } elseif ($page == "profile") {

            include("profile.php");




        } elseif ($page == "logout") {

            include("logout.php");


        
        } else {

            include("home.php");

        }


        ?>


    </div>


</div>



<footer>

    <p>Stationery Reminder</p>

    <p>
        Helping students remember their school equipment
        and organise their tasks.
    </p>

</footer>


</body>

</html>