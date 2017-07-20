<?php
    require_once("timerdb.php");
    require_once("functions.php");
    require_once("form_functions.php");
    
    check_log_in();

    $user_id = $_SESSION['user_id'];
    $user = get_values_by_id("users", $user_id);

    
?>
<?php
    if(isset($_GET["count"])){ //A specific counter is to be displayed
        $counter = $_GET["count"];
        $id = $_GET["countno"];
        $query = "SELECT * FROM counters WHERE id = {$id} ";
        $get_counter_details = mysqli_query($connection, $query);
        $counter_details = mysqli_fetch_array($get_counter_details);
        $table = "counter" . $id . "_details";
        $table2 = "user" . $user_id . "_counters"; 
        if((isset($_GET["end"]) && ($_GET["end"])!="now") || (!isset($_GET["end"]))){
        //check if selected counter is already running if STOP wasnt pushed;
        $running = $counter_details["running"];
        if($running == 1){ //counter is running
            //get time the run started
            $query = "SELECT time_started FROM {$table} ORDER BY id DESC LIMIT 1";
            $get_details = mysqli_query($connection, $query);
            check_connect($get_details);
            $details = mysqli_fetch_array($get_details);
            $time_started = $details["time_started"];
            } else{
            //Wasn't running yet... do nothing!!!
                }
            }
        } elseif(isset($_GET['type']) && ($_GET['type'] == 'hidden')) {
            /*
            $_GET['all'] can be of various values:
            NULL: view only open counters
            hidden: view only hidden counters
            active: view all active counters (open and hidden)
            inactive: view only deactivated counters
            all: view every counter (except those deleted, of course)
            */
            $counter = "All counters";
            $table = "counters";
            $directory = "user" . $user_id . "_counters";
            $query = "SELECT * FROM ";
            $query .= $directory;
            $query .= " WHERE status= ";
            $query .= " 1 ORDER BY timestamp DESC ";
            $get_counters = mysqli_query($connection, $query);
        } elseif(isset($_GET['type']) && ($_GET['type'] == 'active')){
            $counter = "All counters";
            $table = "counters";
            $directory = "user" . $user_id . "_counters";
            $query = "SELECT * FROM ";
            $query .= $directory;
            $query .= " WHERE status < 2 ";
            $query .= " ORDER BY timestamp DESC ";
            $get_counters = mysqli_query($connection, $query);
        } elseif(isset($_GET['type']) && ($_GET['type'] == 'inactive')){
            $counter = "All counters";
            $table = "counters";
            $directory = "user" . $user_id . "_counters";
            $query = "SELECT * FROM ";
            $query .= $directory;
            $query .= " WHERE status= ";
            $query .= " 2 ORDER BY timestamp DESC ";
            $get_counters = mysqli_query($connection, $query);

        } elseif(isset($_GET['type']) && ($_GET['type'] == 'all')){
            $counter = "All counters";
            $table = "counters";
            $directory = "user" . $user_id . "_counters";
            $query = "SELECT * FROM ";
            $query .= $directory;
            $query .= " WHERE status < 4 ";
            $query .= " ORDER BY timestamp DESC ";
            $get_counters = mysqli_query($connection, $query);

        } else {
            $counter = "All counters";
            $table = "counters";
            $directory = "user" . $user_id . "_counters";
            $query = "SELECT * FROM ";
            $query .= $directory;
            $query .= " WHERE status= ";
            $query .= " 0 ORDER BY timestamp DESC ";
            $get_counters = mysqli_query($connection, $query);

        }
?>
<?php
    
    if(isset($_GET["startcount"]) && ($_GET["startcount"])=="yes"){
        $query = " UPDATE counters SET running = 1 WHERE id = {$id}";
        $set_running = mysqli_query($connection, $query);
        if(mysqli_affected_rows($connection) == 1){
            $time_started = time();
            $query = " INSERT INTO {$table} ( ";
            $query .= " time_started ) ";
            $query .= " VALUES ({$time_started}) ";
            $save_start = mysqli_query($connection, $query);
            check_connect($save_start);
            $query = "UPDATE {$table2} SET timestamp = {$time_started} WHERE counter_id = {$id}";
            $update = mysqli_query($connection, $query);
            $running = 1;
            header("location: view.php?count={$counter}&&countno={$id}#end");
            exit;
        } else {
            $msg = "<div class= \"msg bad\" style= \"top: 41%;\"> There was an error in starting the counter! </div>";
            $_SESSION['msg'] = $msg;
            header("location: view.php?count={$counter}&&countno={$id}#end");
            exit;
        }
    }
    if(isset($_GET["end"]) && ($_GET["end"])=="now"){
        $query = " UPDATE counters SET running = 0 where id = {$id}"; //end run
        $stop_running = mysqli_query($connection, $query);
        if(mysqli_affected_rows($connection) == 1){ //if run has stopped...
        $time_stopped = time();
        $query = "SELECT * FROM {$table} ORDER BY id DESC LIMIT 1";
        $get_details = mysqli_query($connection, $query);
        check_connect($get_details);
        $details = mysqli_fetch_array($get_details);
        $time_started = $details["time_started"];
        $run_id = $details["id"];

        //get previous cumulative (cum)
        $cum = $counter_details["current_count"];

        //calculate time spent
        $time_spent = $time_stopped - $time_started;
        //calculate cumulative
        $cum = $cum + $time_spent;
        //store values
        $query = " UPDATE {$table} SET ";
        $query .= " time_stopped = {$time_stopped}, 
                        time_spent = {$time_spent}, 
                            cumulative = {$cum} WHERE id = {$run_id} ";
        $store = mysqli_query($connection, $query);
        if(mysqli_affected_rows($connection)==1){
            $query = "UPDATE {$table2} SET timestamp = {$time_stopped} WHERE counter_id = {$id}";
            $update = mysqli_query($connection, $query);
            //success!
            } else{
                //failure!
                $msg = "<div class= \"msg bad\" style= \"top: 41%;\"> There was an error in saving the timer run! </div>";
                $_SESSION['msg'] = $msg;
                header("location: view.php?count={$counter}&&countno={$id}#end");
                exit;
            }
         // store to primary table
         $query = " UPDATE counters SET current_count = {$cum} WHERE id = {$id} ";
         $store2 = mysqli_query($connection, $query);
            if(mysqli_affected_rows($connection)==1){
                //success!
                } else{
                    //failure!
                    $msg = "<div class= \"msg bad\" style= \"top: 41%;\" > There was an error in saving the timer run! </div>";
                    $_SESSION['msg'] = $msg;
                    header("location: view.php?count={$counter}&&countno={$id}#end");
                    exit;
                }  
        } else {
            $msg = "<div class= \"msg bad\" style= \"top: 41%;\"> There was an error in saving the timer run! </div>";
            $_SESSION['msg'] = $msg;
            header("location: view.php?count={$counter}&&countno={$id}#end");
            exit;
        }
        header("location: view.php?count={$counter}&&countno={$id}#end");
        exit;
    }
    if(isset($_GET['setting'])){
        $setting = $_GET['setting'];
        $time = time();
        $query = "UPDATE {$table2} SET timestamp = {$time} WHERE counter_id = {$id}";
        $update = mysqli_query($connection, $query);
        switch ($setting) {
            case 1: //delete last entry
                //get last entry time spent and cumulative
                $directory = "counter" . $id . "_details";
                $query = "SELECT id, time_spent, cumulative FROM {$directory} WHERE deleted = 0 ORDER BY id DESC LIMIT 1 ";
                $get_time = mysqli_query($connection,$query);
                check_connect($get_time);
                $get_times = mysqli_fetch_array($get_time);
                $entry_id = $get_times['id'];
                $time_spent = $get_times['time_spent'];
                $cum = $get_times['cumulative'];
                $_SESSION['cum'] = $cum;
                $_SESSION['entry_id'] = $entry_id;
                //calculate new cumulative
                $cum = $cum - $time_spent;
                //insert new value into counter main table
                $query = "UPDATE counters SET current_count = {$cum} WHERE id = {$id}";
                $new_cum = mysqli_query($connection, $query);
                // 'Delete' entry in counter details table by setting deleted = 1
                $query = "UPDATE {$directory} SET deleted = 1 WHERE id = {$entry_id} ";
                $delete_entry = mysqli_query($connection, $query);
                $msg = "<div class= \"msg good\" style= \"top: 41%;\" > Entry deleted successfully! &nbsp; <a href= \"view.php?count={$counter}&countno={$id}&setting=6\" style= \"\"> Undo </a> </div>";
                $_SESSION['msg'] = $msg;
                // finally, redirect back to fresh page
                header("location: view.php?count={$counter}&&countno={$id}#end");
                exit;
                break;
            
            case 2: //change counter status (between open and hidden)
                //get current status
                $status = $counter_details['status'];
                if($status == 0){
                    change_status($id, $user_id, 1);
                    $msg = "<div class= \"msg good\" style= \"top: 41%;\" > {$counter}'s Status Changed to <strong> Hidden </strong> </div>" ;
                    $_SESSION['msg'] = $msg;
                    header("location: view.php?count={$counter}&&countno={$id}#end");
                    exit; 
                } elseif($status == 1){
                    change_status($id, $user_id, 0);
                    $msg = "<div class= \"msg good\" style= \"top: 41%;\" > {$counter}'s Status Changed to <strong> Open </strong> </div>" ;
                    $_SESSION['msg'] = $msg;
                    header("location: view.php?count={$counter}&&countno={$id}#end");
                    exit;
                } else {
                    $msg = "<div class= \"msg bad\" style= \"top: 41%;\" > Status change failed! </div>" ;
                    $_SESSION['msg'] = $msg;
                    header("location: view.php?count={$counter}&&countno={$id}#end");
                    exit;
                }
                break;
            
            case 3: //Deactivate or Activate Counter (change status between ended/deactivated and open/active)
                //get current status
                $status = $counter_details['status'];
                if(($status == 0) || ($status == 1)){ //if open or hidden, deactivate
                    change_status($id, $user_id, 2);
                    $msg = "<div class= \"msg good\" style= \"top: 41%;\" > {$counter} has been <strong> Deactivated </strong> </div>" ;
                    $_SESSION['msg'] = $msg;
                    header("location: view.php?count={$counter}&&countno={$id}#end");
                    exit;
                } elseif($status == 2){ //if deactivated, reactivate
                    change_status($id, $user_id, 0);
                    $msg = "<div class= \"msg good\" style= \"top: 41%;\" > {$counter} is now <strong> Active </strong> </div>" ;
                    $_SESSION['msg'] = $msg;
                    header("location: view.php?count={$counter}&&countno={$id}#end");
                    exit;
                } else{
                    $msg = "<div class= \"msg bad\" style= \"top: 41%;\" > {$counter}'s active status change failed! </div>" ;
                    $_SESSION['msg'] = $msg;
                    header("location: view.php?count={$counter}&&countno={$id}#end");
                    exit;
                }
                break;
            
            case 4: //Reset counter (truncate tables)
                $_SESSION['time'] = $time;
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
                $msg = "<div class= \"msg good\" style= \"top: 41%;\" > {$counter} reset successfully! &nbsp; <a href= \"view.php?count={$counter}&countno={$id}&setting=7\" style= \"\"> Undo </a> </div>" ;
                $_SESSION['msg'] = $msg;
                header("location: view.php?count={$counter}&&countno={$id}");
                exit;
                break;
            
            case 5: //Delete counter
                //get current status (in case user decides to undo)
                $_SESSION['status'] = $counter_details['status'];
                //'delete counter by changing status'
                change_status($id, $user_id, 4);
                $msg = "<div class= \"msg good\" style= \"top: 41%;\" > {$counter} has been deleted!  &nbsp; <a href= \"view.php?count={$counter}&countno={$id}&setting=8\" style= \"\"> Undo </a> </div>" ;
                $_SESSION['msg'] = $msg;
                header("location: view.php?");
                exit;
                break;
            
            case 6: //Undo delete last entry
                $cum = $_SESSION['cum'];
                $entry_id = $_SESSION['entry_id'];
                $directory = "counter" . $id . "_details";
                //'undelete' from details table
                $query = "UPDATE {$directory} SET deleted = 0 WHERE id = {$entry_id} ";
                $undelete = mysqli_query($connection, $query);
                // set cumulative value back to former
                $query = "UPDATE counters SET current_count = {$cum} WHERE id = {$id} ";
                $set_cum = mysqli_query($connection, $query);
                $msg = "<div class= \"msg good\" style= \"top: 41%;\" > Undo successful </div>" ;
                $_SESSION['msg'] = $msg;
                header("location: view.php?count={$counter}&&countno={$id}");
                break;
            
            case 7: // Undo Reset counter
                $time = $_SESSION['time'];
                $directory = "counter" . $id . "_details";
                $directory2 = "counter" . $id . "_update" . $time;
                $directory3 = "counter" . $id . "_updates";
                //copy old data back to details table
                $query = "INSERT {$directory} SELECT * FROM {$directory2}";
                $record = mysqli_query($connection, $query);
                //delete the update table
                $query = "DROP TABLE `{$directory2}` ";
                $delete = mysqli_query($connection, $query);
                //delete entry in counter updates table
                $query = " DELETE FROM {$directory3} WHERE time = {$time}";
                $delete = mysqli_query($connection, $query);
                //set current_count to former value
                $query = "SELECT cumulative FROM {$directory} ORDER BY id DESC LIMIT 1";
                $get_cum = mysqli_query($connection, $query);
                $read_cum = mysqli_fetch_array($get_cum);
                $cum = $read_cum['cumulative'];
                $query = "UPDATE counters SET current_count = {$cum} WHERE id = {$id}";
                $set_cum = mysqli_query($connection, $query);
                $msg = "<div class= \"msg good\" style= \"top: 41%;\" > Undo successful </div>" ;
                $_SESSION['msg'] = $msg;
                header("location: view.php?count={$counter}&&countno={$id}");
                break;
            
            case 8: // Undo Delete counter
                $status = $_SESSION['status'];
                //'delete counter by changing status'
                change_status($id, $user_id, $status);
                $msg = "<div class= \"msg good\" style= \"top: 41%;\" > Undo successful </div>" ;
                $_SESSION['msg'] = $msg;
                header("location: view.php?count={$counter}&&countno={$id}");
                exit;
                break;
            
            default:
                $msg = "<div class= \"msg bad\" style= \"top: 41%;\" > Counter Settings Error </div>" ;
                $_SESSION['msg'] = $msg;
                header("location: view.php?");
                exit;
                break;
        }
    }

    $query = "SELECT * FROM ";
    $query .= $table;
    if(isset($_GET['countno'])){
        $query .= " WHERE deleted = 0 ";
    }
    $get_table = mysqli_query($connection, $query);
    check_connect($get_table);

?>
<!DOCTYPE html>
<html>
<head>
  <meta property="og:url"           content="view.php" />
  <meta property="og:type"          content="website" />
  <meta property="og:title"         content="Timer-s" />
  <meta property="og:description"   content="Your description" />
  <meta property="og:image"         content="images/timers.ico" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> 
        <?php
            echo $counter;
        ?>
    </title>
    <!-- <link rel = "stylesheet" type= "text/css" href= "counter.css"> -->
    <link rel = "stylesheet" type= "text/css" href= "style.css">
    <link rel= "icon" href= "images/logo.jpg" type="image/x-icon">
    <!-- <link href='https://fonts.googleapis.com/css?family=Orbitron' rel='stylesheet' type='text/css'> -->
    <script>
        function autoResizeDiv(){
            document.getElementById('fullbody').style.height = window.innerHeight +'px';
            }
        window.onload = autoResizeDiv;
        window.onresize = autoResizeDiv;
        autoResizeDiv();

    </script>
<?php 
    echo "<script> ";
    echo "function tohms(sec_num){
        var hours   = Math.floor(sec_num / 3600);
        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
        var seconds = sec_num - (hours * 3600) - (minutes * 60);
    
        if (hours   < 10) {hours   = \"0\"+hours;}
        if (minutes < 10) {minutes = \"0\"+minutes;}
        if (seconds < 10) {seconds = \"0\"+seconds;}
        var formattedTime= hours+':'+minutes+':'+seconds;
            document.getElementById(\"timer\").innerHTML = formattedTime;
    }";
   if(isset($running) && ($running==1)){
        $current = time() - $time_started;
    } else {
        $current = 0;
    }
    $script = " var current= " . $current . ";";
    $script .= " setInterval(counter";
    $script .= ", 1000);";
    $script .= " function counter(){
            current = current + 1;
            var time = tohms(current);
            }";
    if(isset($running) && ($running==1)){
        echo $script;
    }
       echo "function show_options(){
            var x = document.getElementById('options');
            var y = document.getElementById('profile_pic');
            if(x.style.padding == \"4px\"){
                x.style.padding = \"5px\";
                x.hidden = \"\";
                y.style.right = \"0%\";
                return;
            } else {
                x.style.padding = \"4px\";
                x.hidden = \"true\";
                y.style.right = \"120%\";
                return;
            }
        }";
        echo "</script>";
?>
    <noscript> Please enable Javascript on your browser to run your Counters </noscript>
</head>
<body>
    <div id= "fullbody"> <div class= "row top-body" >
    <a href= "home.php" style= "color: floralwhite" title= "home"> <div class= "col-m-2 top-button mobile-only">
        <h3 class= "header"> <img class= "top-icon" src= "images/home3.png" alt= "H" />  </h3> </div> </a>
    <a href= "home.php" style= "color: floralwhite" title= "home"> <div class= "col-8 col-t-4 no-mobile"> <h3 class= "main-header no-mobile"> TIMER-S </h3>
        </div> </a>
    <a href= "view.php?type=all" style= "color: floralwhite" title= "counters"> <div class= "col-1 col-m-2 col-t-4 top-button"> <h3 class= "header no-mobile"> Counters </h3>
        <h3 class= "header mobile-only"> <img class= "top-icon" src= "images/counters.png" alt= "C" />  </h3> </div> </a>
    <a href= "#" style= "color: floralwhite" title= "New Counter"> <div class= "col-1 col-m-2 col-t-4 top-button"> <h3 class= "header no-mobile"> <img class= "top-icon" src= "images/plus white.png" alt= "" />  New Counter </h3>
        <h3 class= "header mobile-only"> <img class= "top-icon" src= "images/plus white.png" alt= "+" />  </h3> </div> </a>
    <div class= "col-1 col-m-2 col-t-1"> <img src=
    <?php
        echo " \"{$user['profile_pic']}\" ";
    ?>
     alt= "Profile Pic" class= "profile" title= "Settings" /> </div>
    <?php
        $today= "<div class=\"datebar col-1 col-m-4 col-t-3\" title= \"Today\" >";
        $today .= date("d-m-Y");
        $today .= " </div>";
        echo $today;
    ?> 
    </div> <!--end of row 1-->
    <div class= "row top-space"> <div class= "col-12 col-m-12 col-t-12"> </div> </div> <!--compensation space for the header-->
    <?php
        if(isset($_SESSION["msg"]) && !empty($_SESSION["msg"])){
            echo "<div class= \"row msg-row\"> <div class = \"col-12 col-m-12 col-t-12\"> ";
            echo $_SESSION['msg'];
            echo "</div> </div> <!--end of msg row-->";
        }
    ?>
    <div class= "row counter-header">
        <div class= "col-2 col-t-2 no-mobile"> <img src= "images/alarmclock.jpg" alt= "" class= "counter-img"  /> </div> 
        <?php
            echo "<div class= \"col-10 col-m-10 col-t-9 counter-title\"> " . strtoupper($counter) . "</div> ";
        ?>
        <div class= "col-m-2 mobile-only arrow-box "> <div class= "counter-arrow" onclick="show_stats()" title= "Statistics"> <img class= "arrow" id= "down" src= "images/caret down.png" alt= "V" /> <img class= "arrow" id= "up" src= "images/caret up.png" alt= "V" /> </div> </div>
    </div> <!--end of counter header row-->
    <div class= "row "> 
        <div class= "counter-stats" id= "stats">
        <div class = ""> <div id= "s_huge_cum"> </div> </div>
    <?php
        if(isset($_GET['count'])){
            echo "";
            // <div id= \"share_buttons\">";
            // echo "<div id= \"email_share\" > <a href= \"sendmail.php?cum={$counter_details['current_count']}&counter={$counter}\" target= \"_blank\"> <img class= \"email_share_button\" src= \"images/red-email-icon.png\" alt= \"Share\" title= \"Share by email\"> </a> </div> </div>
            echo "<div class = \" l_huge_cum\">" ;
            echo " Current Total";
            echo "</div>";
            }
            if(mysqli_num_rows($get_table)==0 && !isset($_GET["count"])){
                //No counter was selected, seems user has NO counters yet
                echo " <div class =\"specific_table no_table\"> You haven't created any counters yet! </br>
                        Fill the new counter form below to begin! </div>";
                new_counter();
            } elseif(mysqli_num_rows($get_table)==0 && isset($_GET["count"]) && ($running == 0)){
                //Specific counter selected, But hasn't been stated yet
                echo "<div class =\"specific_table no_table\"> You haven't utilized <b> " . $counter . "</b> yet. </br>
                    Click on the 'Start' button to begin! </div>";
            } elseif(mysqli_num_rows($get_table)!=0 && !isset($_GET["count"])){
                //No counter selected, display all
                echo "</div> </div> <div class =\"specific_table\">";
                display_counters($get_counters);
                echo "</div>";

            } elseif(mysqli_num_rows($get_table)!=0 && isset($_GET["count"])) {
                //specific counter selected, has runs to display
                echo "<div class= \"table-header\"> <div class= \"run-no table-header-cell\"> Run No </div> <div class= \"time-started table-header-cell\"> Time Started </div> <div class= \"time-stopped table-header-cell\"> Time Stopped </div> <div class= \"time-spent table-header-cell\"> Time Spent </div> <div class= \"cum table-header-cell\">  Cumulative </div> </div>";
                echo "<div class =\"specific_table\">"; 
                echo "<table class=\"display-table\" style= \"position: relative;\">";
                   /* <tr class= \"header\"> <th> Run No. </th> <th> Time started </th> <th> Time stopped </th>
                    <th> Time spent </th> <th> Cumulative </th> </tr>";*/
                $sn = 0;
                while($show_table = mysqli_fetch_array($get_table)){
                    $sn = $sn + 1;
                    $time_start = date("d-m-Y h:i:s", $show_table["time_started"]);
                    if(($show_table["time_stopped"]) != 0){ //run has been stopped by user
                        $time_stop = date("d-m-Y h:i:s", $show_table["time_stopped"]);
                    } else { //run is still ungoing
                        $time_stop = NULL;
                        /* This is to prevent php from echoing the epoch date as 
                        the time-stopped of an unended run
                        */
                    }
                    $time_spent = $show_table["time_spent"];
                    $time_spent_sec = fmod($time_spent, 60);
                    $time_spent = floor($time_spent/60);
                    $time_spent_min = fmod($time_spent, 60);
                    $time_spent_hr = floor($time_spent/60);
                    $cumulus = $show_table["cumulative"];
                    $cumulus_sec = fmod($cumulus, 60);
                    $cumulus = floor($cumulus/60);
                    $cumulus_min = fmod($cumulus, 60);
                    $cumulus_hr = floor($cumulus/60);

                    $row = "<tr> <td class=\"display_cells run-no\"> " . $sn . " </td> ";
                    $row .= " <td class=\"display_cells time-started\"> " . $time_start . " </td>" ;
                    $row .= " <td class=\"display_cells time-stopped\"> " . $time_stop . " </td> <td class=\"display_cells time-spent\">" ;
                    if(isset($time_spent_hr) && !empty($time_spent_hr)){
                        $row .= $time_spent_hr . " hrs ";
                    }
                    if(isset($time_spent_min) && !empty($time_spent_min)){
                        $row .= $time_spent_min . " mins ";
                    }
                    if(isset($time_spent_sec) && !empty($time_spent_sec)){
                        $row .= $time_spent_sec . " secs ";
                    }
                    $row .= " </td> <td class=\"display_cells cum\"> ";
                    if(isset($cumulus_hr) && !empty($cumulus_hr)){
                        $row .= $cumulus_hr . " hrs ";
                    }
                    if(isset($cumulus_min) && !empty($cumulus_min)){
                        $row .= $cumulus_min . " mins ";
                    }
                    if(isset($cumulus_sec) && !empty($cumulus_sec)){
                        $row .= $cumulus_sec . " secs ";
                    }
                    $row .= " </td> </tr> ";
                    echo $row;
                }
                echo " </table> <div id= \"end\"> </div> </div>";
            }

        echo "</div>"; //end of Counter statistics block
        ?>
    <script>         
            function show_stats() {
                var x = document.getElementById('stats');
                var y = document.getElementById('up');
                var z = document.getElementById('down');
                var a = document.getElementById('top-line');
                if(x.style.display == 'block'){
                    x.style.display = 'none';
                    y.style.display = 'none';
                    z.style.display = 'block';
                    a.style.display= 'block';
                } else {
                    x.style.display = 'block';
                    y.style.display = 'block';
                    z.style.display = 'none';
                    a.style.display= 'none';
                }  
            }
            function show_settings() {
                var x = document.getElementById('settings');
                if(x.style.display == 'block'){
                    x.style.display = 'none';
                } else {
                    x.style.display = 'block';
                }  
            }
    </script>
    <?php
        if(isset($_GET['count'])){
            echo "<script> var cumulative = " . $counter_details['current_count'] . ";
            var hours   = Math.floor(cumulative / 3600);
            var minutes = Math.floor((cumulative - (hours * 3600)) / 60);
            var seconds = cumulative - (hours * 3600) - (minutes * 60);
        
            if (hours   < 10) {hours   = \"0\"+hours;}
            if (minutes < 10) {minutes = \"0\"+minutes;}
            if (seconds < 10) {seconds = \"0\"+seconds;}
            var Time= hours+':'+minutes+':'+seconds;
            var x = document.getElementById(\"s_huge_cum\");
            x.innerHTML = Time;

            
            </script>";
        }
         echo " <hr id= \"top-line\" style =\"position:relative; width:100%; left: 0;\"/>   ";
    ?>
    <div class= "row">
        <div class= "col-m-12 col-7 col-t-6" id= "timer"> 00:00:00 </div> </div>
    </div>
    <?php
        echo "<div class= \"row\">";
        if(isset($_GET["count"])){
            if($counter_details['status'] == 2){
                //counter is inactive, disable both Start and Stop buttons
                $timerbuttons = "<div class= \"col-m-12 col-4 col-t-3\"> <button  class=\"timerbuttons\" style= \"background-color: rgb(100,120,100); color: rgb(50,50,50);\"> ";
                $timerbuttons .= " START </button> </div> <br /> ";
                $timerbuttons .= "<div class= \"col-m-12 col-4 col-t-3\"> <button class=\"timerbuttons\" style= \"background-color: rgb(120,100,100); color: rgb(50,50,50);\" > ";
                $timerbuttons .= " STOP </button> </div> <br /> ";
            } else {
                if(isset($running) && ($running==1)){
                    //disable Start button, enable Stop button
                    $timerbuttons = "<div class= \"col-m-12 col-4 col-t-3\"> <button  class=\"timerbuttons\" style= \"background-color: rgb(100,120,100); color: rgb(50,50,50);\"> ";
                    $timerbuttons .= " START </button> </div> <br /> ";
                    $timerbuttons .= "<div class= \"col-m-12 col-4 col-t-3\"> <a href= \"view.php?count=" . $counter . "&&end=now&&countno=";
                    $timerbuttons .=   $id . "\"> <button class=\"timerbuttons stop\" stop> STOP </button> </a> </div> <br /> ";
                } else {
                    //enable Start button, disable Stop button
                    $timerbuttons = "<div class= \"col-m-12 col-4 col-t-3\"> <a href= \"view.php?count=" . $counter . "&&countno=";
                    $timerbuttons .= $id . "&&startcount=yes\"> <button  class=\"timerbuttons start\"> START </button> </a> </div> <br /> ";
                    $timerbuttons .= "<div class= \"col-m-12 col-4 col-t-3\"> <button class=\"timerbuttons\" style= \"background-color: rgb(120,100,100); color: rgb(50,50,50);\" > ";
                    $timerbuttons .= " STOP </button> </div> <br /> ";
                }
            }
        $timer = " ";
    ?>
    <div class= "row"> 
    <?php
            //display counter settings
        $timer .= "<div class= \"timer-settings-block\" id= \"settings\">";
        $short_timer = trunc($counter,1);
        if(mysqli_num_rows($get_table) !=0){
            //$short_timer = substr($counter,0,12);
            $timer .= "<div style=\"position: relative; width: 100%; text-align: center; font-family: 'times new roman'; font-stretch: condensed; font-weight: 600; padding-bottom: 0.7em; \"> COUNTER SETTINGS </div>";
            if($running != 1){
                $timer .= "<a href= \"view.php?count={$counter}&countno={$id}&setting=1 \" > <button class= \"counter_settings active\"> Delete <strong> {$short_timer}'s </strong> last entry </button> </a> <br />";
                } else {
                    $timer .= "<span> <button class= \"counter_settings inactive\"> Delete <strong> {$short_timer}'s </strong> last entry </button> </span> <br />";
                }
            }
        if($running != 1){
            $timer .=  "<a href= \"view.php?count={$counter}&countno={$id}&setting=2 \" > <button class= \"counter_settings active\"> Change <strong> {$short_timer}'s  </strong> Status </button> </a> <br />";
            if($counter_details['status'] == 2){
                $timer .= "<a href= \"view.php?count={$counter}&countno={$id}&setting=3 \" > <button class= \"counter_settings active\"> Activate <strong> {$short_timer} </strong> </button> </a> <br />";
            } else {
                $timer .= "<a href= \"view.php?count={$counter}&countno={$id}&setting=3 \" > <button class= \"counter_settings active\"> Deactivate <strong> {$short_timer} </strong> </button> </a> <br />";
            }
            $timer .= "<a href= \"view.php?count={$counter}&countno={$id}&setting=4 \" > <button class= \"counter_settings active\"> Reset <strong> {$short_timer} </strong> </button> </a> <br />
                        <a href= \"view.php?count={$counter}&countno={$id}&setting=5\" > <button class= \"counter_settings active\"> Delete <strong> {$short_timer} </strong> </button> </a>";
            } else {
            $timer .=  "<span> <button class= \"counter_settings inactive\" disabled> Change <strong> {$short_timer}'s  </strong> Status </button> </span> <br />
                        <span> <button class= \"counter_settings inactive\" disabled> Deactivate <strong> {$short_timer} </strong> </button> </span> <br />
                        <span> <button class= \"counter_settings inactive\" disabled> Reset <strong> {$short_timer} </strong> </button> </span> <br />
                        <span> <button class= \"counter_settings inactive\" disabled> Delete <strong> {$short_timer} </strong> </button> </span>";
                }
         $timer .= " </div> </div>";
        echo $timerbuttons;
        echo $timer;
        } else { //no specific counter, so display other options instead of timer buttons
            echo "<a href= \"view.php?type=open\" > <button class= \"view_counters active\"> View Open Counters Only </button> </a> <br />
                    <a href= \"view.php?type=hidden\" > <button class= \"view_counters active\"> View Hidden Counters Only </button> </a> <br />
                    <a href= \"view.php?type=active\" > <button class= \"view_counters active\"> View All Active Counters </button> </a> <br />
                    <a href= \"view.php?type=inactive\" > <button class= \"view_counters active\"> View Inactive Counters </button> </a> <br />
                    <a href= \"view.php?type=all\" > <button class= \"view_counters active\"> View All Counters </button> </a> <br /> ";

        }
    ?>
</div>
<div class= "row mobile-only"> <div class= "settings-button-box"> <img class= "settings-button" src= "images/menu3.png" alt= "Settings" onclick= "show_settings()"/> </div> </div>
<hr class= "no-mobile" style= "width: 100%; position: relative;  color: rgb(12,12,82); background-color: rgb(12,12,82)" />
<?php
    $_SESSION['msg'] = array(); //clean out one time msgs if they exist
    include("footer.php");
?>