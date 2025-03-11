<?php
/**
 * Track Job View Script
 * Records when a user views a job listing for the recommendation system
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_id();
    session_start();
}

require_once 'settings.php';
require_once 'functions.inc';

// Validate job reference
if (isset($_GET['job']) && !empty($_GET['job'])) {
    $job_ref = sanitize_input($_GET['job']);
    
    // Get user email if logged in, otherwise use null
    $email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
    
    // Record the view
    track_job_view($job_ref, $email, $conn);
    
    // Redirect back to the referring page or to the job listing page
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: page' . substr($job_ref, -1) . '.php');
    }
    exit;
} else {
    // If no job reference, redirect to index
    header('Location: index.php');
    exit;
}
?>
