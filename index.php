<?php
    require_once("timerdb.php");
    require_once("functions.php");
    require_once("form_functions.php");
?>
<?php
    session_name('timusevals');
    session_start();
    setcookie(session_name(),session_id());

    if(isset($_COOKIE['logged']) && !empty($_COOKIE['logged'])){ //remember me is set, auto log in
        $_SESSION['user_id'] = $_COOKIE['logged'];
        $_SESSION['sign_in'] = 1;
        header("location:home.php");
    } elseif(isset($_SESSION['sign_in']) && ($_SESSION['sign_in'] == 1)) { //user hasnt logged out, auto sign in
        $_SESSION['user_id'] = $_SESSION['user_id'];
        $_SESSION['sign_in'] = 1;
        header("location:home.php");
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title> TIMER-S </title>
    <link rel = "stylesheet" type= "text/css" href= "counter.css">
    <link rel= "icon" href= "images/logo.jpg" type="image/x-icon">
    <meta property="og:url"           content="view.php" />
    <meta property="og:type"          content="website" />
    <meta property="og:title"         content="Timer-s" />
    <meta property="og:description"   content="Your description" />
    <meta property="og:image"         content="images/logo.jpg" />

    <script> 
        function show_signup(){
            var x = document.getElementById("signin");
            var y = document.getElementById("signup");
            x.hidden = "true";
            y.hidden = "";
            return;
        }
        function show_signin(){
            var x = document.getElementById("signin");
            var y = document.getElementById("signup");
            x.hidden = "";
            y.hidden = "true";
            return;
        }
        function close_errors(){
            var x = document.getElementById("errors");
            var y = document.getElementById("close_errors");
            x.hidden = "true";
            y.hidden = "true";
            return;s
        }
    </script>
    <noscript> </noscript>
</head>
<body style= "background-image: url('images/time.jpg');"> 
<div id="fb-root"></div>
    <script>
                window.fbAsyncInit = function() {
                    FB.init({
                    appId      : '1832490427017870',
                    xfbml      : true,
                    version    : 'v2.8'
                    });
                    FB.AppEvents.logPageView();
                };

                (function(d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)){ return;}
                    js = d.createElement(s); js.id = id;
                    js.src = "//connect.facebook.net/en_GB/sdk.js";
                    fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));
        </script>
    <?php
        $today= "<div id=\"datebar\"> Today's Date: ";
        $today .= date("d-m-Y");
        $today .= " </div>";
        echo $today;
    ?>
    <div id= "fullbody"> <div id= "headerbar"> <h1 id= "header"> TIMER-S </h1> </div>
    <div class= "msg_rect">
    <?php
        if(isset($message)){
            echo "<div class= \"msg\"> " . $message . "</div>";
        }
        if(isset($_SESSION['errors']) && !empty($_SESSION['errors'])){
            echo "<div id= \"errors\" class= \"error\">";
            echo " <button id= \"close_errors\" onclick= \"close_errors()\" title= \"close\"> X </button> ";
            display_errors($_SESSION['errors']);
            echo "</div>";
        }
    ?>
    </div>
    <div id= "sign_in_box">
       <div id= "signin"  >
        <div class= "headers" >  Sign in to TIMER-S! </div> <br />
        <form action = "timersignin.php" method = "post" >
            <table class= "sign_forms large">
                <tr> <td class= "sign_cells" > Username </td> <td> <input class= "sign_cells"  type= "text" name= "username" value= "" placeholder= "Username" autofocus/> </td> </tr>
                <tr> <td class= "sign_cells" > Password </td> <td> <input class= "sign_cells"  type= "password" name= "password" value= ""/> </td> </tr>
                <tr> <td colspan="2"> <input type= "checkbox" name= "remember" value= "yes"> &nbsp; <small> Keep me signed in </small> </td> </tr>
                <tr> <td class= "sign_cells" colspan="2"> <input class= "sign_cells"  type= "submit" name= "signin" value= "Sign In" /> </td> </tr>
            </table>
        </form> <br />
       <div id= "signupcall" class= "centre"> Haven't signed up yet? <br />
        <button class= "call_button" title= "Sign up" onclick= "show_signup()">  Click Here to Create a FREE account! </button> </div> <br />
    <hr style= "width: 106%; color: rgb(12,12,82); position: relative; left: -3%; height: 2px; background-color: rgb(12,12,82)"/> <br />
    </div>
    <div id= "signup" hidden= "true" >
      <div class= "headers" >  Create a FREE account! </div>
        <form action = "timersignin.php" method = "post">
            <table class= "sign_forms">
                <tr> <td class= "sign_cells" > <label for="username"> Enter your Username </label> </td> <td> <input class= "sign_cells"  type= "text" placeholder = "Username" name= "username" value= ""/> </td> </tr>
                <tr> <td class= "sign_cells" > <label for="email"> Email </label> </td> <td> <input class= "sign_cells"  type= "email" name= "email" placeholder= "Email" value= ""/> </td> </tr>
                <tr> <td class= "sign_cells" > Password </td> <td> <input class= "sign_cells"  type= "password" name= "password" value= ""/> </td> </tr>
                <tr> <td class= "sign_cells" > Confirm Password </td> <td> <input class= "sign_cells"  type= "password" name= "confirm_password" value= ""/> </td> </tr>
                <tr> <td style= "padding-top: 15px;" class= "sign_cells" colspan="2"> <input class= "sign_cells"  type= "submit" name= "signup" value= "Sign Up" /> </td> </tr>
            </table>
        </form> <br />
       <div id= "signupclick" class= "centre"> Already have an account? <br />
        <button class= "call_button" title= "sign in" onclick= "show_signin()">  Click Here to Sign in! </button> </div> 
    <hr class= "timer_lines"/> <br />
    </div> 
    <div
    class="fb-like"
    data-share="true"
    data-width="450"
    data-show-faces="true">
    </div>
        <div id= "other_links"> <a href= "terms.php" target= "_blank"> Terms and Conditions </a> <br />
            <a href= "privacy.php" target= "_blank"> Privacy Policy </a> <br /> <a href= "about.php" target= "_blank"> About TIMER-S </a>  </div>
        </div> </div>
<?php
    $_SESSION['errors'] = array(); //clean out errors in session after displaying them
    include("footer.php");
?>
