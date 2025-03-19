<?php
$host = "feenix-mariadb.swin.edu.au";
$port = "3306";
$username = "s105548505"; // your username
$password = "011106";  // your password
$database = "s105548505_db"; // your database

// Create connection
$conn = mysqli_connect($host, $username, $password, $database, $port);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
