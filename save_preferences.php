<?php
/**
 * User Preferences Handler
 * Saves user preferences from the dashboard
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'settings.php';
require_once 'functions.inc';

// Redirect if not logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php?redirect=dashboard.php");
    exit();
}

$user_email = $_SESSION['user_email'];

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $location = isset($_POST['location']) ? sanitize_input($_POST['location']) : '';
    $job_type = isset($_POST['job_type']) ? sanitize_input($_POST['job_type']) : '';
    $skills = isset($_POST['skills']) ? sanitize_input($_POST['skills']) : '';
    $layout = isset($_POST['layout']) ? sanitize_input($_POST['layout']) : 'default';
    $theme = isset($_POST['theme']) ? sanitize_input($_POST['theme']) : 'light';
    
    // Get current preferences to preserve theme if not changed
    $current_prefs = get_user_preferences($user_email, $conn);
    if (!isset($_POST['theme']) && isset($current_prefs['theme'])) {
        $theme = $current_prefs['theme'];
    }
    
    // Create preferences array
    $preferences = [
        'location' => $location,
        'salary' => isset($_POST['salary']) ? sanitize_input($_POST['salary']) : '',
        'job_type' => $job_type,
        'skills' => $skills,
        'layout' => $layout,
        'theme' => $theme
    ];
    
    // Save preferences
    $result = save_user_preferences($user_email, $preferences, $conn);
    
    // Redirect back to dashboard with success/error message
    if ($result) {
        header("Location: dashboard.php?status=preferences_saved");
    } else {
        header("Location: dashboard.php?status=preferences_error");
    }
    exit();
} else {
    // If not a POST request, redirect to dashboard
    header("Location: dashboard.php");
    exit();
}
?>
