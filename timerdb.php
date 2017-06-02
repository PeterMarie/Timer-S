<?php
	require_once("functions.php");
	// 1. Create a database connection
	$connection= mysqli_connect(DB_SERVER,DB_USER,DB_PASSWORD);
	
	check_connect($connection);
	
	// 2. Select a database to use 
	$db_select= mysqli_select_db($connection, DB_NAME);
	check_connect($connection);
?>