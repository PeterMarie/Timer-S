<!DOCTYPE html>
<html>
    <head>
        <title> Place advert </title>
    <link rel = "stylesheet" type= "text/css" href= "counter.css">
    <link rel= "icon" href= "images/logo.jpg" type="image/x-icon">
    <meta property="og:url"           content="view.php" />
    <meta property="og:type"          content="website" />
    <meta property="og:title"         content="Timer-s" />
    <meta property="og:description"   content="Your description" />
    <meta property="og:image"         content="images/timers.ico" />
     </head>
     <body>
         <div id= "fullbody">
     <div id= "headerbar">  <a href= "home.php"> <h1 id= "header"> TIMER-S </h1>  </a> </div>
         Enter ad details
         <form action ="placead.php" method= "post" >
             <select name= "type">
             </select>
             <select name= "orientation">
             </select>
             <select name= "duration">
             </select>
             <input type= "email" name= "email" />
             <input type= "submit" name= "send" />
         </form>
         Upload Image
         <form action= "placead.php" method= "post">
             <input type= "file" name= "advert" />
             <input type = "submit" name= "send" />
         </form>
         Enter script
         <form action= "placead.php" method= "post">
             <input type= "text" name= "script" />
             <input type= "submit" name= "send" />
         </form>
         Finish
         <div> <div> Your advert tracking number is </div>
         <div> Amount required </div>
         <div> Bank details: <br />
                Account name: Peter Chukwuamaka Ogwara <br />
                Account number: 2081292633 <br />
                Account type: Savings Account <br />
                Financial Institution: Zenith Bank Plc. <br /> </div>
         </div> </div>
<?php
    include("footer.php");
?>