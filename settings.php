<?php
    require_once("timerdb.php");
    require_once("functions.php");
    require_once("form_functions.php");

    check_log_in();

    $user_id = $_SESSION['user_id'];
    $user = get_values_by_id("users", $user_id);

    if(isset($_GET['func'])){
        switch ($_GET['func']) {
            case 'change':
                if($_FILES){
                    $errors = array();
                    $directory = "images/" . $user["username"] . ".jpg";
                    $imageFileType = pathinfo(basename($_FILES["pic"]["name"]), PATHINFO_EXTENSION);
                    $type = mime_content_type($_FILES["pic"]["tmp_name"]);
                    if($type != "image/jpeg"){
                        //wrong file type, first check
                        $errors[] = "wrong file type! <br /> please upload either a jpeg or png file!";
                    }
                    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                        && $imageFileType != "gif" ){
                            //wrong file type, 2nd check
                            $errors[] = "wrong file type! <br /> please upload either a jpeg or png file!";
                        }
                    if(empty($errors)){
                        if (move_uploaded_file($_FILES["pic"]["tmp_name"], $directory)) {
                            //Success!!
                            //Record in user db table
                            $query = "UPDATE users SET profile_pic= '{$directory}' WHERE id = {$user_id} ";
                            $chang_pic = mysqli_query($connection, $query);
                            if($chang_pic){
                                //final success
                                header('location: settings.php');
                                exit;
                            } else {    
                                //failure in recording
                                header('location: settings.php');
                            }
                        } else {
                            //Failed
                        }
                    } 
                }
                break;
            
            case 'removepic':
                $query = "UPDATE users SET profile_pic = 'images/generic_person.jpg' WHERE id= {$user_id} ";
                $removepic = mysqli_query($connection, $query);
                if($removepic){
                    //Success!!
                    header('location: settings.php');
                    exit;
                } else {
                    //Failure!!!
                    header('location: settings.php');
                }
                break;
            
            case 'resetall':
                # code...
                /*should loop through each counter, create an update duplicate and
                truncate the originals */
                $directory = "user" . $user_id . "_counters";
                $time = time();
                $query = "SELECT * FROM `{$directory}`";
                $get_counters = mysqli_query($connection, $query);
                while($counters = mysqli_fetch_array($get_counters)){ //the loop
                    $id = $counters['counter_id'];
                    $directory = "counter" . $id . "_details";
                    $directory2 = "counter" . $id . "_update" . $time;
                    $directory3 = "counter" . $id . "_updates";
                    //record time of update
                    $query = "INSERT INTO {$directory3} (type, time) VALUES (\"reset\", {$time})";
                    $record = mysqli_query($connection, $query);
                    //copy table data and structure
                    $query = "CREATE TABLE {$directory2} LIKE {$directory}";
                    $record = mysqli_query($connection, $query);
                    $query = "INSERT {$directory2} SELECT * FROM {$directory}";
                    $record = mysqli_query($connection, $query);
                    //delete data from original table
                    $query = "TRUNCATE TABLE `{$directory}` ";
                    $reset = mysqli_query($connection, $query);
                    //delete data from main table
                    $query = "UPDATE counters SET current_count = 0 WHERE id = {$id}";
                    $reset = mysqli_query($connection, $query);
                }
                header('location: settings.php');
                break;
                
            case 'deleteall':
                $directory = "user" . $user_id . "_counters";
                $query = "SELECT * FROM `{$directory}`";
                $get_counters = mysqli_query($connection, $query);
                while($counters = mysqli_fetch_array($get_counters)){ //The loop
                    //'delete counters by changing status'
                    $id = $counters['counter_id'];
                    change_status($id, $user_id, 4);
                }
                header('location: settings.php');
                break;
                
            case 'deleteacct':
                # code...
                $time = time();
                $username = $user['username'];
                $password = $user['password'];
                $email = $user['email'];
                $query = "INSERT INTO deleted_users (user_id, username, password, email, time_deleted) 
                            VALUES ({$user_id}, '{$username}', '{$password}', '{$email}', {$time})";
                $transfer = mysqli_query($connection, $query);
                if($transfer){
                    $query = "DELETE FROM users WHERE id = {$user_id}";
                    $delete = mysqli_query($connection, $query);
                    if($delete){
                        //Success!
                        header('location: logout.php');
                    } else {
                        //Failed!
                        header('location: settings.php');
                    }
                }
                break;

            case 'changemail':
                if(isset($_POST['newmail']) && !empty($_POST['newmail'])){
                    $email = $_POST['email'];
                    $query = "UPDATE users SET email = '{$email}' WHERE id = {$user_id}";
                    $set_new_mail = mysqli_query($connection, $query);
                    if(mysqli_affected_rows($connection) == 1){
                        //Success!
                        header('location: settings.php');
                    } else {
                        //Failed!!
                        header('location: settings.php');
                    }
                }
                break;

            default:
                # code...
                break;
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta property="og:url"           content="view.php" />
        <meta property="og:type"          content="website" />
        <meta property="og:title"         content="Timer-s" />
        <meta property="og:description"   content="Your description" />
        <meta property="og:image"         content="images/timers.ico" />
         
        <link rel = "stylesheet" type= "text/css" href= "counter.css">
        <link rel= "icon" href= "images/logo.jpg" type="image/x-icon">
        <title> settings </title>
        <script>
            function change_pic(){
                var x = document.getElementById('');
                if(x.hidden = "hidden"){
                    x.hidden= "";
                } else{
                    x.hidden = "hidden";
                }
            }
        </script>
        <noscript> </noscript>
     </head>
     <body>
         <div id= "fullbody">
         <div id= "l_prompt" hidden= "hidden"> <div id= "prompt" >
             <div id= "prompt_header"> Sure? </div>
            <div id= "s_prompt"> </div>
            <div id= "prompt_buttons"> <a id= "prompt_confirm_button" href= "#"> <button class= "prompt_button"> Yes </button> </a>
                <button class= "prompt_button" onclick= "cancel()"> Cancel </button>
            </div>
         </div>  <div id= "backdrop"> </div> </div>
             <?php
                $today= "<div id=\"datebar\"> Today's Date: ";
                $today .= date("d-m-Y");
                $today .= " </div>";
                echo $today;
             ?>
            <div id= "headerbar"> <h1 id= "header"> <a href= "home.php" style= "color: floralwhite"> TIMER-S </a>  </h1> </div>
            <div id= "full_settings"> <div id = "profile_pic_block">
                <?php
                    echo "<img id= \"profile_pic_settings\" src= \"{$user['profile_pic']}\" alt= \"{$user['username']}\" onclick= \"enlarge_profile()\"> <br /> ";
                 ?>
                 <div id= "s_profile_pic_block">
                     
                 <!-- <button class= "image_options" id = "change" onclick= "change_pic()">  Change </button> <button class= "image_options" id = "unchange" onclick= "change_pic2()" hidden= "hidden">  Change </button> -->
                 <span id= "new_pic">
                 <button class= "image_options changepic2"> Change </button> 
                 <form id= "new_pic_form" action= "settings.php?func=change" method= "post" enctype="multipart/form-data">
                        <input class= "changepic" type= "file" name= "pic" onchange= "document.getElementById('new_pic_form').submit()">
                        <input type= "submit"  hidden= "hidden" name= "new_pic" value= "change">
                 </form>
                 </span>
                 <span> <button class= "image_options remove" onclick= "func('remove_pic')"> Remove </button> </span>
                 <script> 
                    function func(func){
                        var a = document.getElementById('prompt_buttons');
                        var x = document.getElementById('l_prompt');
                        var y = document.getElementById('s_prompt');
                        var z = document.getElementById('prompt_confirm_button');
                        if(func === 'remove_pic'){
                            a.style.bottom = "0";
                            y.innerHTML = "This will return your profile pic back to default setting!\n";
                            z.href = "settings.php?func=removepic";
                        }
                        if(func === 'reset_all'){
                            a.style.bottom = "0";
                            y.innerHTML = "This will start all your counters afresh, and cannot be undone once performed!\n";
                            z.href = "settings.php?func=resetall";
                        }
                        if(func === 'reset_acct'){
                            a.style.bottom = "8%";
                            y.innerHTML = "This will delete all your counters and records, and cannot be undone once performed!\n";
                            z.href = "settings.php?func=deleteall";
                        }
                        if(func === 'delete_acct'){
                            a.style.bottom = "0";
                            y.innerHTML = "Do you really want to terminate your usage of timer-s and all it's facilities?\n";
                            z.href = "settings.php?func=deleteacct";
                        }
                        x.hidden = "";
                    }
                    function cancel(){
                        var x = document.getElementById('l_prompt');
                        x.hidden = "hidden";
                    }
                    function change_pic(){
                        var x = document.getElementById('new_pic');
                        var y = document.getElementById('change');
                        var z = document.getElementById('unchange');
                            x.hidden = "";
                            y.hidden= "hidden";
                            z.hidden = "";
                    }
                    function change_pic2(){
                        var x = document.getElementById('new_pic');
                        var y = document.getElementById('change');
                        var z = document.getElementById('unchange');
                            x.hidden = "hidden";
                            y.hidden = "";
                            z.hidden = "hidden";
                    }
                 </script>
                 </div> </div> <br /> <br />
                 <div class= "other_settings"> <div class= "Change_mail"> Change Email </div> <br />
                 <div id= "change_email_block" >
                      <!--  <div id= "change_email_block_header"> Enter new email </div> -->
                        <div id= "s_change_email_block">
                            <form action= <?php echo "\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "?func=changemail\"" ; ?> method= "post">
                                <input type= "email" name= "email" value= "<?php //echo "\"" . $user['email'] . "\""; ?>" placeholder= "Enter new mail" >
                                <input type= "submit" name= "newmail" value= "Replace">
                            </form>
                        </div>
                  </div> <br /> <br />
                 
                 <a onclick= "func('reset_all')"> Reset all counters </a> <br />
                 <a onclick= "func('reset_acct')"> Reset Account (delete all counters) </a> <br />
                 <a onclick= "func('delete_acct')"> Delete Account </a> <br />
                 </div>
            </div>
         </div>
     </body>
</html> 