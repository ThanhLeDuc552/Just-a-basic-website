<?php
$host = "";
$port = "";
$username = ""; 
$password = "";
$database = "";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database, $port);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
