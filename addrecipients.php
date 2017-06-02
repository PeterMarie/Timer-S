<?php
    require_once("timerdb.php");
    require_once("functions.php");
    require_once("form_functions.php");

    session_name('timusevals');
    session_start();
    setcookie(session_name(),session_id());

  if(isset($_GET['remove'])){
    $get_recipient_to_remove = $_GET['recipient'];
    $recipient_to_remove = "recipient" . $get_recipient_to_remove;
    $recipient_count = $_SESSION['recipient_count'];
    for ($i= $get_recipient_to_remove; $i <= $_SESSION['recipient_count']; $i++) { 
        $recipient = "recipient" . $i;
        if($i == $_SESSION['recipient_count']){
            $_SESSION[$recipient] = "";
            unset($_SESSION[$recipient]);
        } else {
            $a = $i + 1;
            $get_new_recipient = "recipient" . $a;
            $new_recipient = $_SESSION[$get_new_recipient];
            $_SESSION[$recipient] = $new_recipient;
        }
    }
    $recipient_count-- ;
    $_SESSION['recipient_count'] = $recipient_count;

  } else {
  if(isset($_POST['send'])){
      $fields = all_prep($_POST);
      $errors = array();
      $required = array();
      $required[] = "recipient";
      $errors = array_merge($errors, check_required($required));
      $recipient_count = $_SESSION['recipient_count'];

      if(!empty($errors)){

      } else {
                $recipient_count++ ;
                $a = "recipient" . $recipient_count;
                $_SESSION[$a] = $fields['recipient'];
                $_SESSION['recipient_count'] = $recipient_count;
                }
  } else {
      if(isset($_SESSION['recipient_count']) && ($_SESSION['recipient_count']) != 0){
          for ($i=1; $i <= $_SESSION['recipient_count']; $i++) {
              $recipient = "recipient" . $i;
              $_SESSION[$recipient] = "";
              unset($_SESSION[$recipient]);
          }
      }
      $_SESSION['recipient_count'] = 0;
      $recipient_count = 0;
    }
  }

?>
<!DOCTYPE html>
<html>
    <head>

    </head>
    <body>
        <style>
            a:link { text-decoration: none;}
            a:active { text-decoration: none;}
            a:visited {text-decoration: none;}
            .remove {color: white; background-color: darkred; position: relative; right: 3px; border-radius: 50%; padding: 1px; margin: 2px;}
            .recipient { border: 1px solid darkgray; background-color: rgba(230,230,230,0.5); border-radius: 20px; padding-right: 2px; padding-left: 5px; padding-top: 3px; padding-bottom: 3px;}
        </style>
        <form action = "addrecipients.php" method = "post">
      <strong> Share to: </strong> <br />
        <?php
            echo "<input type = \"email\" name = \"recipient\"> &nbsp; ";
        echo "<input type = \"submit\" value = \"+\" name = \"send\" /> &nbsp;";
                    if(isset($_POST['send']) || isset($_GET['remove'])){
                        for ($i=1; $i <= $recipient_count; $i++) {
                            $a = "recipient" . $i;
                            echo "&nbsp; <span class= \"recipient\"> <small> " . $_SESSION[$a] . "; </small>
                             <a class= \"remove\" href= \"addrecipients.php?remove=true&recipient={$i}\" title= \"remove\"> - </a> </span>";
                            }
                        } else {
                        }
        ?>
        </form> 
    </body>
    </html>