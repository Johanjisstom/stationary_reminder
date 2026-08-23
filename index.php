<?php 

session_start(); 

include("connect.php"); 

$page = isset($_GET['page']) ? $_GET['page'] : 'Home'; 

$login_error = ""; 

if (isset($_POST['login_submit'])) { 

 $User_name = $_POST['User_name']; 

 $Password = $_POST['Password']; 

 $sql = "SELECT * FROM users  

 WHERE User_name = '$User_name'  

AND Password = '$Password'"; 

 $result = mysqli_query($conn, $sql); 

 if (mysqli_num_rows($result) == 1) { 

 $row = mysqli_fetch_assoc($result); 

 $_SESSION['UserID'] = $row['UserID']; 

 $_SESSION['User_name'] = $row['User_name']; 

 header("Location: index.php"); 

 exit(); 

 } else { 

 $login_error = "Invalid username or password."; 

 } 

} 

?> 

 

<!DOCTYPE html> 

<html> 

<head> 

<link rel="stylesheet" href="style.css"> 
<title>Welcome to Stationary Reminder</title>
<link rel="icon" type="images/x-icon" href="/images/books.ico"> 

</head> 

<body> 
<h1>Welcome to the Stationary Reminder.</h1>
</div>
 
<div class="main-layout">  


 <?php if (isset($_SESSION['User_name'])) { ?> 



 <div class="navigation-add">  

 <nav> 

 <a href="index.php?page=home">Home</a>  |

 <a href="index.php?page=add_users"> Add Users</a>  |

 <a href="index.php?page=add_schedule">Add Schedules </a>  |

 <a href="index.php?page=add_teacher"> Add Teachers </a>  |

 <a href="index.php?page=add_program"> Add Programs </a> |

 <a href="index.php?page=logout">Logout</a> 
 
 </nav> 
 </div> 
 <?php } ?> 

<br>
<br>

<nav>

 
      <a href="index.php?page=home">Home</a> | 

 
      <a href="index.php?page=view_users">Users</a> | 
    
 
      <a href="index.php?page=view_schedule">Schedules</a> | 

 
      <a href="index.php?page=view_teacher">Teachers</a> | 

 
      <a href="index.php?page=view_program">Programs</a> | 

      
      <a href="index.php?page=login">Login</a> 


 </nav> 

 <hr> 

 <div class="content"> 

      <?php 

      if (isset($_GET['page'])) { 

      $page = $_GET['page']; 

      if ($page == "add_teacher.php") { 

         include("add_teacher.php");

      } elseif ($page == "add_schedule") { 

         include("add_schedule.php"); 

      } elseif ($page == "add_users") { 

         include("add_users.php"); 

      } elseif ($page == "add_program") { 

         include("add_program.php"); 


       #-----------------------------------------

      } elseif ($page == "view_teacher") { 

       include("view_teacher.php");
         
          } elseif ($page == "view_schedule") { 
         
          include("view_schedule.php"); 
         
          } elseif ($page == "view_program") { 
         
          include("view_program.php"); 
         
          } elseif ($page == "view_users") { 
         
          include("view_users.php"); 

      
         }elseif ($page == "login") { 
             include("login.php"); 


         }elseif ($page == "logout") { 
               include("logout.php"); 
      
      
         } else { 

         include("home.php"); 
      } 
      

      } else { 

      include("home.php"); 

      } 


      
      ?> 

 </div> 
 


 <footer>
  <p>Stationary Reminder</p>
</footer>


</body> 

</html> 

