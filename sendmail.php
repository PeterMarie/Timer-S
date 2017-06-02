<?php
    require_once("timerdb.php");
    require_once("functions.php");
    require_once("form_functions.php");
?>
<?php
    check_log_in();

    $user_id = $_SESSION['user_id'];
    $user = get_values_by_id("users", $user_id);

    if(isset($_POST['send'])){
        //Form validation
        $fields = all_prep($_POST);
      $_SESSION = array_merge($_SESSION, $fields);
        $errors = array();

        // check if recipients have been added
        $recipient_count = $_SESSION['recipient_count'];
        if($recipient_count == 0){
            $errors[] = "You must include at least one recipient!";
        }

        if(empty($errors)){
            $user_email = $user['email'];
            $subject = "My achievement on Timer!";
            $headers = 'From: <{$user_email}>' . "/r/n";
            $message = $_POST['message'];

            //send mail
            for ($i=1; $i <= $recipient_count ; $i++) { 
                $a = "recipient" . $i;
                $recipient = $_SESSION[$a];
                $mail = mail($recipient,$subject,$message,$headers);

                if($mail){
                    //SUCCESS!!!
                } else {
                    $errors[] = "Oops! There was an error in sending the mail. <br /> Pls try again!";
                    
                }
            }
        }
    } else {
        
    }
?>
<!DOCTYPE html>
<html>
    <head>

    </head>
    <body>
        <script>
        </script>
        <noscript> </noscript>
        <style>
            #mailbody { width: 40%; border: 1px solid darkgray; padding: 2%;}
            #message {font-size: 115%; text-align: center; padding: 3%;}
            .mail_lines { width: 110%; position: relative; left: -5%;}
            #recipients { border: 0; height: 100px; width: 100%;}
        </style>
            <?php 
                echo "<div id= \"mailbody\"> <form id= \"mailform\" action=  \"sendmail.php?cum={$_GET['cum']}&counter={$_GET['counter']}\" method= \"post\">
                    <iframe id= \"recipients\" src= \"addrecipients.php\"> </iframe>
                    <br /> <hr class= \"mail_lines\"/>";
                $cumulus = $_GET['cum'];
                $cumulus_sec = fmod($cumulus, 60);
                $cumulus = floor($cumulus/60);
                $cumulus_min = fmod($cumulus, 60);
                $cumulus_hr = floor($cumulus/60);
                if(isset($cumulus_hr) && !empty($cumulus_hr)){
                    $cum = $cumulus_hr . " hrs ";
                }
                if((!isset($cumulus_hr) && empty($cumulus_hr)) && (isset($cumulus_min) && !empty($cumulus_min))){
                    $cum = $cumulus_min . " mins";
                } elseif((isset($cumulus_hr) && !empty($cumulus_hr)) && (isset($cumulus_min) && !empty($cumulus_min))){
                    $cum .= $cumulus_min . " mins";
                }
                echo "<input type= \"text\" name= \"message\" value = \"I just hit {$cum} on {$_GET['counter']}
                    Calculated using Timer-s! Join me to record achievements and keep track of your life!\" hidden= \"hidden\" />
                    <div id = \"message\"> I just hit <strong> <span id= \"cum\"> </span> </strong> on {$_GET['counter']} <br />
                    Calculated using Timer-s! <br /> Join me to record achievements and keep track of your life! </div> <br /> <hr class= \"mail_lines\"/>";
            ?>
            <input class= "inputs" type = "submit" name= "send" value= "Share" /> <br />
        </form>
        </div>
        <?php
            echo "<script> var cumulative = " . $_GET['cum'] . ";
            var hours   = Math.floor(cumulative / 3600);
            var minutes = Math.floor((cumulative - (hours * 3600)) / 60);
            var seconds = cumulative - (hours * 3600) - (minutes * 60);
        
            if (hours   < 10) {hours   = \"0\"+hours;}
            if (minutes < 10) {minutes = \"0\"+minutes;}
            if (seconds < 10) {seconds = \"0\"+seconds;}
            var Time= hours+' Hrs '+minutes+ ' Mins';
            var x = document.getElementById(\"cum\");
            x.innerHTML = Time;
            var y = document.getElementById(\"cum2\");
            y.innerHTML = Time;

            function resizeIFrameToFitContent( iFrame ) {
                iFrame.style.height = iFrame.contentWindow.document.body.scrollHeight;
                }

            window.addEventListener('DOMContentReady', function(e) {
                var iFrame = document.getElementById('recipients');
                resizeIFrameToFitContent(iFrame);
            });
            </script>";
        ?>
    </body>
</html>