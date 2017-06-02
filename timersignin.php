<?php
    require_once("timerdb.php");
    require_once("functions.php");
    require_once("form_functions.php");

    session_name('timusevals');
    session_start();
    setcookie(session_name(),session_id());

    if(isset($_POST['signin'])){ //user is signing in to Timer
        //start form validation
        $fields = all_prep($_POST);
        $_SESSION = $fields;

        $errors = array();
        $required = array();
        $max_length_fields = array();
        $min_length_fields = array();

        $required[] = 'username';
        $required[] = 'password';
        $errors = array_merge($errors, check_required($required));

       if(empty($errors)){
            //check for the user in the database
            $query = "SELECT * FROM users WHERE username = \"{$fields['username']}\"";
            $get_user = mysqli_query($connection, $query);
            if($get_user){ 
                if(mysqli_num_rows($get_user) > 0) { //username was found
                    $user = mysqli_fetch_array($get_user);
                    if($user['password'] === sha1($fields['password'])){ //password matches, log user in
                        if(isset($fields['remember']) && ($fields['remember']) == "yes"){
                            setcookie("logged", $user['id'], time() + (60 * 60 *24 * 365), '/');
                        }
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['sign_in'] = 1;
                        header("location:home.php");
                        exit;
                    } else { //password doesn't match'
                            $errors[] = "The Username and Password you entered do not match!";
                            $_SESSION['errors'] = $errors;
                            header("location:index.php");
                            exit;
                    }
                } else { //username was not found in database
                        $errors[] = "This username was not found in our database!";
                        $_SESSION['errors'] = $errors;
                        header("location:index.php");
                        exit;
                }
            }
        } else {
                $_SESSION['errors'] = $errors;
                header("location:index.php");
                exit;
        } 

    } elseif(isset($_POST['signup'])){ //a new user is signing up
        //start form validation
        $fields = all_prep($_POST);
        $_SESSION = $fields;

        $errors = array();
        $required = array();
        $max_length_fields = array();
        $min_length_fields = array();

        $required[] = 'username';
        $required[] = 'password';
        $required[] = 'confirm_password';
        $required[] = 'email';
        $errors = array_merge($errors, check_required($required));

        $max_length_fields['username'] = 30;
        $max_length_fields['email'] = 50;
        $errors = array_merge($errors, check_max_length_fields($max_length_fields));

        $min_length_fields['password'] = 6;
        $errors = array_merge($errors, check_min_length_fields($min_length_fields));

        confirm_password($fields['password'], $fields['confirm_password']);

        if(empty($errors)){
            $password = sha1($fields['password']);
            $username = $fields['username'];
            $email = $fields['email'];
            // $location = "";
            $find_username = check_exist("users", "username", $username);
            if($find_username == 1){ //username already exists
                $errors[] = "This username is already taken!";
                $_SESSION['errors'] = $errors;
                header("location:index.php");
                exit;
            } else {
                //perform query
                $query = "INSERT INTO users (username, password, email) 
                            VALUES (\"{$username}\", \"{$password}\", \"{$email}\")";
                $add_user = mysqli_query($connection, $query);
                if($add_user){ //user successfully added, create user counter table and log him/her in
                    $user_id = mysqli_insert_id($connection);
                    $directory = "user" . $user_id . "_counters";
                    $query = "CREATE TABLE {$directory} (id INT(11) NOT NULL auto_increment, counter_id INT(11) NOT NULL, status TINYINT(1) DEFAULT 0, timestamp INT(11), PRIMARY KEY(id)) ENGINE = MYISAM ";
                    $create_counter_table = mysqli_query($connection, $query);
                    $_SESSION = array();
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['sign_in'] = 1;
                    header("location:home.php");
                    exit;
                    }
                }
            } else {
                $_SESSION['errors'] = $errors;
                header("location:index.php");
                exit;
            }
    } else { //if neither signup nor signin has been sent, its a hacker!
        $errors = array("WARNING! Continued attempt to access that page will <br /> lead
        to your being permamently blocked from using Timer-s.com amd her facilities.");
        $_SESSION['errors'] = $errors;
        header("location:index.php");
        exit;
    }
?>