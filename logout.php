<?php
        //delete signin session
         session_name("timusevals");
         session_start();

         $_SESSION = array();
         if(isset($_COOKIE[session_name()])) {
         setcookie(session_name(), '', time() - 200000, '/');
         }
         session_unset(); 
         session_destroy();

         if(isset($_COOKIE['logged'])) {
         setcookie('logged', '', time() - 200000, '/');
         }
         if(isset($_GET['status']) && $_GET['status']== 'goodbye'){
                header('location: goodbye.php');
         } else {
                header("location: index.php");
         }
         exit;
?>