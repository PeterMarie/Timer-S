<?php

 // All functions for the campus website

define("DB_SERVER","localhost");
define("DB_USER","root");
define("DB_PASSWORD", "");
define("DB_NAME", "timer");
    
    function check_connect($query) {
        global $connection;
        if(!$query){
            die("errror: " . mysqli_error($connection));
         //  header ("location: error.php");
         //  exit;
        }
   }

    function new_counter(){
        echo "<div> <div style=\"font-size: 110%; font-weight: 600;\"> Open new Counter </div>
                <form action= \"home.php\" method= \"post\"> <table>
                <tr> <td> Title </td> <td> <input type= \"text\" name= \"title\"> </td> </tr>
                <tr> <td> Description </td> <td> <input type= \"text\" name= \"description\"> </td> </tr>
                <tr> <td> Status </td> <td> <select name= \"status\"> <option value= 0> open </option>
                <option value= 1> hidden </option> </select> </td> </tr>
                <tr> <td> <input type= \"submit\" name= \"create\" value= \"Create Counter\" > </td> </tr>
                </table> </form>
            </div>";
            /* option meanings:
                0: OPEN/ACTIVE (visible on home page)
                1: HIDDEN (requires password reentry to view)
                2: ENDED/DEACTIVATED (viewable when viewing all counters, can be reactivated,
                        but not viewable on home screen)
                4: DELETED (Not viewable to user, but still stored in the database)
            */
    }

    function display_counters($counters){
        echo "<table class=\"display_table\"> <tr class= \"header\"> <th > S/N </th> <th> Title </th> 
                <th> Cumulative </th> <th> Date begun </th> <th> Description </th>";
        if(isset($_GET['type'])){
            echo "<th> Status </th> ";
        }
        echo "</tr>";
        $sn = 0;
        if(mysqli_num_rows($counters) == 0){
            echo "<tr> <td colspan= \"6\" class= \"no_table\"> You have no {$_GET['type']} counters! </td> </tr>";
        }
        while ($find_counters = mysqli_fetch_array($counters)){
            $sn = $sn + 1;
            $counter_id = $find_counters['counter_id'];
            $get_counters = get_values_by_id('counters', $counter_id);
            $cumulus = $get_counters["current_count"];
            $cumulus_sec = fmod($cumulus, 60);
            $cumulus = floor($cumulus/60);
            $cumulus_min = fmod($cumulus, 60);
            $cumulus_hr = floor($cumulus/60);
            $row = "<tr class= \"display_rows\"> <td class= \"display_cells\"> ";
            $row .= $sn . "</td> ";
            $row .= "<td class= \"display_cells\"> <a class= \"counterlink\" href =\"view.php?count=" . $get_counters["title"] .
            "&countno=" . $get_counters["id"] . " \" target= \"_blank\"> ";
            $row .= $get_counters["title"] . " </a> </td> <td class= \"display_cells\"> ";
            if(isset($cumulus_hr) && !empty($cumulus_hr)){
                $row .= $cumulus_hr . " hrs ";
            }
            if(isset($cumulus_min) && !empty($cumulus_min)){
                $row .= $cumulus_min . " mins ";
            }
            if(isset($cumulus_sec) && !empty($cumulus_sec)){
                $row .= $cumulus_sec . " secs ";
            }
            $row .= " </td> <td class= \"display_cells\"> " . $get_counters["date_begun"] . "</td> ";
            if(isset($get_counters["description"])){
                $row .= "<td class= \"display_cells\"> " . $get_counters["description"] . "</td> ";
            }
            if(isset($_GET['type'])){
                switch ($get_counters['status']) {
                    case 0:
                        $status = "open";
                        break;
                    
                    case 1:
                        $status = "hidden";
                        break;
                    
                    case 2:
                        $status = "inactive";
                        break;
                    
                    default:
                        # code...
                        break;
                }
                $row .= "<td class= \"display_cells\"> " . $status . "</td> " ;
            }
            $row .= "</tr>";
            echo $row;
            }
        echo "</table>";    
    }     

     function get_values_by_id($table, $id){ //returns array of attributes
         global $connection;
         $query = "SELECT * FROM ";
         $query .= $table;
         $query .= " WHERE id= " . $id;
         $get_values = mysqli_query($connection, $query);
        // check_connect($get_values, "142");
         $value = mysqli_fetch_array($get_values);
         return $value;
     }
     
     function check_log_in(){
        session_name('timusevals');
        session_start();
        setcookie(session_name(),session_id());

        if(isset($_COOKIE['logged']) && !empty($_COOKIE['logged'])){ //remember me is set, auto log in
        $_SESSION['user_id'] = $_COOKIE['logged'];
        $_SESSION['sign_in'] = 1;
        } else {
            if(!isset($_SESSION['sign_in']) && ($_SESSION['sign_in'] != 1)) {
                header("location:index.php");
                exit;
            }
        }
     }

    function trunc($phrase, $max_words) { //Open Source function! Tks guys!!!
        $phrase_array = explode(' ',$phrase);
        if(count($phrase_array) > $max_words && $max_words > 0)
            $phrase = implode(' ',array_slice($phrase_array, 0, $max_words)).'...';
        return $phrase;
    }

    function change_status($counter_id, $user_id, $new_status){
        global $connection;
        $directory = "user" . $user_id . "_counters";
        $query = "UPDATE counters SET status = {$new_status} WHERE id = {$counter_id}";
        $change_status = mysqli_query($connection, $query);
        $query = "UPDATE {$directory} SET status = {$new_status} WHERE counter_id = {$counter_id}";
        $change_status = mysqli_query($connection, $query);
    }
    
     function check_exist($table, $column, $name, $other = NULL, $other_column = NULL){
        global $connection;
        global $return_id;
        $find = 0;
        $get = get_tables($table);
        $count = mysqli_num_rows($get);
        while($array = mysqli_fetch_array($get)){
            $first_id = $array['id'];
            $count = $count + $first_id - 1;
            $next_id = $first_id + 1;
            for($id = $first_id; $id <= $count; $id++){ 
                $array_find = get_values_by_id($table, $id);
                if($name == $array_find[$column]){ //if name already exists in stated column...
                        if(($other != NULL) && ($other_column != NULL)){
                            //if there's another parameter to be checked against
                            if($other == $array_find[$other_column]){
                                $find = 1;
                                $return_id = $id;
                                break;
                            } else {
                                break;
                            }
                        }
                        $find = 1;
                        $return_id = $id;
                        break;
                }
                if($id != $count){
                 for ($i=1; $i < 100 ; $i++) { 
                    $query = " SELECT * FROM {$table} ";
                    $query .= " WHERE id= {$next_id} ";
                    $check_next_exists = mysqli_query($connection, $query);
                     $next_exists = mysqli_num_rows($check_next_exists);
                    if($next_exists == 0) { //the next id was NOT found
                        $id = $id + 1;
                        $count = $count + 1;
                        $next_id = $next_id + 1;
                        } else {
                            break;
                        } 
                    } 
                } 
            }
        }
        return $find;
     }

     function get_tables($table){ //returns my_sqli result
         global $connection;
         $query = "SELECT * FROM ";
         $query .= $table;
         $get_tables = mysqli_query($connection, $query);
         check_connect($get_tables, "142");
      // $tables = mysqli_fetch_array($get_tables);
         return $get_tables;
     }
     
?>