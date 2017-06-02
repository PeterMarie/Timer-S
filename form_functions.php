<?php
    
    function mysql_prep($value) {
        global $connection;
        $magic_quotes_active = get_magic_quotes_gpc();
        $new_enough_php = function_exists("mysqli_real_escape_string");
        if ($new_enough_php) {
            if ($magic_quotes_active) {$value = stripslashes($value); }
                $value = mysqli_real_escape_string($connection, $value);
            } else {
                if (!$magic_quotes_active) {$value = addslashes($value); }
            }
        return $value;
        }
        
    function check_required($required){ //Ensure required fields havent been left blank
        global $errors;
        global $fields;
        foreach ($required as $field ) {
            if (!isset($fields[$field]) || (empty($fields[$field]))) {
                $errors[$field] = " * Field " . strtoupper($field) . " is empty! <br />";
               }
        }
        return $errors;
    }
    
    function check_max_length_fields($max_length_fields){
        global $errors;
        global $fields;
        foreach ($max_length_fields as $field => $max_length) {
            if ( strlen($fields[$field]) > ($max_length)) {
                $errors[] = strtoupper($field) . " should not contain more than " . $max_length . " characters! <br />"; 
            }
        }
        return $errors;
    }
    
    function check_min_length_fields($min_length_fields){
        global $errors;
        global $fields;
        foreach ($min_length_fields as $field => $min_length) {
            if (strlen($fields[$field]) < ($min_length)) {
                $errors[] = strtoupper($field) . " should contain " . $min_length . " or more characters! <br />"; 
            }
        }
        return $errors;
    }
        
    function check_max_value_fields($max_value_fields){
        global $errors;
        global $fields;
        foreach ($max_value_fields as $field => $max_value) {
            if ($fields[$field] > $max_value) {
                $errors[] = strtoupper($field) . " should not be greater than " . $max_value . " ! <br />"; 
            }
        }
        return $errors;
    }
    
    function check_min_value_fields($min_value_fields){
        global $errors;
        global $fields;
        foreach ($min_value_fields as $field => $min_value) {
            if ($fields[$field] < $min_value) {
                $errors[] = strtoupper($field) . " should not be less than " . $min_value . " ! <br />"; 
            }
        }
        return $errors;
    }    
    
    function confirm_password($password, $password_confirm){
        global $errors;
        if (!empty($password) && !empty($password_confirm) && ($password != $password_confirm)) {
                    $errors[] = " Password fields do not match! <br />
                    Please reenter 'PASSWORD' and 'CONFIRM PASSWORD' <br />" ;
                }
    }
    
    function display_errors($errors){
        foreach ($errors as $error) {
            echo $error;
        }
        return; 
    }
    
    function all_prep($values){
        $returned = array();
        foreach ($values as $key => $value) {
            $value = mysql_prep($value);
            $returned[$key] = $value;
        }
        return $returned;
    }
    
    function check_words($entry){
        global $errors;
        
        foreach ($entry as $value) {
            $checker = array();
            $checker[] = strstr($value, "fuck");
            $checker[] = strstr($value, "algolagnia");
            $checker[] = strstr($value, "asshole");
            $checker[] = strstr($value, "vagina");
            $checker[] = strstr($value, "sperm");
            $checker[] = strstr($value, "BDSM");
            $checker[] = strstr($value, "lesbian");
            $checker[] = strstr($value, "horny");
            $checker[] = strstr($value, "blowjob");
            
            foreach ($checker as $check) {
            if(isset($check) && !empty($check)){
                $errors[] = "The word " . $value . " is NOT allowed on Campus!!! <br /> 
                Please restrain from entering abusive or sexually inappropriate words. <br />";
                $checker = null;
                unset($checker);
                break;
            }
        }
        }
        return $errors;
    }
?>