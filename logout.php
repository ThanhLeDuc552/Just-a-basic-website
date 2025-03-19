<?php 
session_start();
if (isset($_SESSION['manager_id'])) unset($_SESSION['manager_id']);
if (isset($_SESSION['manager_name'])) unset($_SESSION['manager_name']);
if (isset($_SESSION['username'])) unset($_SESSION['username']);
header("Location: index.php");
?>