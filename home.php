<?php
    require_once("timerdb.php");
    require_once("functions.php");
    require_once("form_functions.php");

    check_log_in();

    $user_id = $_SESSION['user_id'];
    $user = get_values_by_id("users", $user_id);

    
?>
<?php
    if(isset($_POST['create'])){ //user submitted query for new counter
        $fields = all_prep($_POST);
        $errors = array();
        $required = array();
        $required[] = "title";
        $errors = check_required($required);

        $max_length_fields = array();
        $max_length_fields["title"] = (25);
        $max_length_fields["description"] = (140);
        $errors = array_merge($errors, check_max_length_fields($max_length_fields));

        if(empty($errors)){
            $title = $fields["title"];
            if(isset($fields["description"])){
                $description = $fields["description"];
            }
            $status = $fields["status"];
            $date = date("Ymd");
            $date2 = time();
            $current_count = 0;

            $query = "INSERT INTO counters ";
            $query .= " (title, ";
            if(isset($fields["description"])){
                $query .= " description, " ;
            }
            $query .= " user_id, date_begun, date_begun2, ";
            $query .= " current_count, status, running) ";
            $query .= " VALUES ('{$title}', ";
            if(isset($fields["description"])){
                $query .= " '{$description}', " ;
            }
            $query .= " '{$user_id}', {$date}, {$date2}, ";
            $query .= " {$current_count}, {$status}, 0) ";
            $create = mysqli_query($connection, $query);
            check_connect($create);

            //insert into user's counter table
            $id = mysqli_insert_id($connection);
            $directory = "user" . $user_id . "_counters";
            $query = "INSERT INTO {$directory} (counter_id) VALUES ({$id})";
            $add_counter = mysqli_query($connection, $query);

            //create counter details table
            $directory = "counter" . $id . "_details" ;
            $query = "CREATE TABLE " ;
            $query .= $directory;
            $query .= " (id INT(11) NOT NULL auto_increment, ";
            $query .= " time_started INT(15) NOT NULL, ";
            $query .= " time_stopped INT(15) NOT NULL, time_spent INT(7) NOT NULL, ";
            $query .= " cumulative INT(11) NOT NULL, deleted TINYINT(1) DEFAULT 0, PRIMARY KEY (id)) ENGINE = MYISAM";
            $new_table = mysqli_query($connection, $query);
            check_connect($new_table);

            //create counter updates table
            $directory = "counter" . $id . "_updates";
            $query = "CREATE TABLE {$directory} (id INT(11) NOT NULL auto_increment, type 
                        VARCHAR(10) NOT NULL, time INT(11) NOT NULL, 
                         PRIMARY KEY (id), UNIQUE (time)) ENGINE = MYISAM ";
            $new_table = mysqli_query($connection, $query);
            check_connect($new_table);
            $table = "user" . $user_id . "_counters";
            $time = time();
            $query = "UPDATE {$table} SET timestamp = {$time} WHERE counter_id = {$id}";
            $update = mysqli_query($connection, $query);

            $message = "Counter created successfully!";
        } else {
        }

    }
?>
<!DOCTYPE html>
<html>
<head>
    <title> Home </title>
    <link rel = "stylesheet" type= "text/css" href= "counter.css">
    <link rel= "icon" href= "images/logo.jpg" type="image/x-icon">
    <meta property="og:url"           content="view.php" />
    <meta property="og:type"          content="website" />
    <meta property="og:title"         content="Timer-s" />
    <meta property="og:description"   content="Your description" />
    <meta property="og:image"         content="images/timers.ico" />
    <script>
        function show_options(){
            var x = document.getElementById('options');
            var y = document.getElementById('profile_pic');
            if(x.style.padding == "4px"){
                x.style.padding = "5px";
                x.hidden = "";
                y.style.right = "0%";
                return;
            } else {
                x.style.padding = "4px";
                x.hidden = "true";
                y.style.right = "120%";
                return;
            }
        }
    </script>
    <noscript> </noscript>
</head>
<body style= "background-image: url('images/stopwatch2.jpg');background-repeat: no-repeat; background-size: cover;">
   <!-- <img src= "images/stopwatch.jpg" alt= "stopwatch" id= "backdrop" /> -->
   <div id= "fullbody">
    <?php
        $today= "<div id=\"datebar\"> Today's Date: ";
        $today .= date("d-m-Y");
        $today .= " </div>";
        echo $today;
        
    ?>
    <div id= "profile"> <img src=
    <?php
        echo " \"{$user['profile_pic']}\" ";
    ?>
     alt= "Profile Pic" id= "profile_pic" onclick= "show_options()" />
        <div id= "options" hidden= "true"> <a href= "settings.php" target= "_blank" > Settings </a> <hr style= "width: 120%; position: relative; left: -10%; color: rgb(12,12,82); background-color: rgb(12,12,82)" /> <a href= "logout.php"> Log Out </a> </div> </div>
    <div id= "headerbar"> <h1 id= "header"> <a href= "home.php" style= "color: floralwhite"> TIMER-S </a>  </h1> </div>
    <div class= "msg_rect">
    <?php
        if(isset($message)){
            echo "<div class= \"msg good\" style= \"\"> " . $message . "</div>";
        }
        if(isset($errors) && !empty($errors)){
            echo "<div id= \"error\">";
            display_errors($errors);
            echo " </div>";
        }
    ?>
    </div> <div class= "mid_body"> <div  class ="counter_table_cover">
    <div class= "counter_table">
    <?php
        $directory = "user" . $user_id . "_counters";
        $query = "SELECT * FROM ";
        $query .= $directory;
        $query .= " WHERE status= ";
        $query .= " 0 ORDER BY timestamp DESC LIMIT 4 ";

        $counters= mysqli_query($connection, $query);
        check_connect($counters);
        if(mysqli_num_rows($counters) < 1){
                echo " <div class= \"no_table\" style= \"border-bottom: 1px solid rgba(132, 132, 160, 0.65); border-right: 1px solid rgba(132, 132, 160, 0.65);\"> You do not have any active counters! <br />
                Fill the new counter form below to begin! </div> </div>";
        } else {
            display_counters($counters);
            echo "</div>";
            $view_all_button = " <a class= \"view_all\" href= \"view.php?type=open\" target= \"_parent\"> ";
            $view_all_button .= " View All Counters </a>";
            echo  $view_all_button . "<br /> <br />";
        }
    ?>     </div> </div>
 <hr class= "timer_lines" style= "top: 10%; width: 102%; left: -1%;"/> <div class= "new_counter">
<?php new_counter(); ?>
 </div>
 <hr class= "timer_lines" style= "top: 10%; width: 102%; left: -1%;"/> </div> 
<?php
    include("footer.php");
?>