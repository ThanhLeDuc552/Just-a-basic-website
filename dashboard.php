<?php
/**
 * Applicant Dashboard
 * Displays application status, timeline, and personalized job recommendations
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

// Get user applications
$sql = "SELECT e.*, j.Title as JobTitle, j.Location 
        FROM eoi e 
        JOIN jobs j ON e.JobReferenceNumber = j.JobReferenceNumber
        WHERE e.Email = ?
        ORDER BY e.ApplicationDate DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $user_email);
mysqli_stmt_execute($stmt);
$applications_result = mysqli_stmt_get_result($stmt);

// Get user preferences
$preferences = get_user_preferences($user_email, $conn);

// Handle theme changes
if (isset($_POST['theme']) && in_array($_POST['theme'], ['light', 'dark'])) {
    $preferences['theme'] = $_POST['theme'];
    $prefs_array = [
        'location' => $preferences['PreferredLocation'] ?? '',
        'salary' => $preferences['PreferredSalary'] ?? '',
        'job_type' => $preferences['PreferredJobType'] ?? '',
        'skills' => $preferences['PreferredSkills'] ?? '',
        'layout' => $preferences['DashboardLayout'] ?? 'default',
        'theme' => $_POST['theme']
    ];
    save_user_preferences($user_email, $prefs_array, $conn);
}

// Get the EOI to display timeline for
$eoi_number = null;
if (isset($_GET['eoi'])) {
    $eoi_number = (int)$_GET['eoi'];
} else if (mysqli_num_rows($applications_result) > 0) {
    // Default to first application
    mysqli_data_seek($applications_result, 0);
    $first_app = mysqli_fetch_assoc($applications_result);
    $eoi_number = $first_app['EOInumber'];
    // Reset result pointer
    mysqli_data_seek($applications_result, 0);
}

// Page title
$page_title = "Applicant Dashboard";
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($preferences['theme'] ?? 'light'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ETech Applicant Dashboard">
    <title>ETech - <?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://use.typekit.net/ilv8ihq.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/timeline.css">
    <link rel="stylesheet" href="styles/recommendations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include_once 'header.inc'; ?>

    <main class="dashboard-container">
        <div class="dashboard-header">
            <h1><?php echo $page_title; ?></h1>
            <div class="dashboard-controls">
                <form method="post" id="theme-form" class="theme-toggle">
                    <label class="switch">
                        <input type="checkbox" name="theme" value="dark" <?php echo (($preferences['theme'] ?? '') === 'dark') ? 'checked' : ''; ?> onchange="this.form.submit();">
                        <span class="slider round"></span>
                        <span class="toggle-label">Dark Mode</span>
                    </label>
                </form>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="dashboard-sidebar">
                <div class="user-profile">
                    <div class="profile-image">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-info">
                        <h3>Welcome back!</h3>
                        <p><?php echo htmlspecialchars($user_email); ?></p>
                    </div>
                </div>

                <nav class="dashboard-nav">
                    <ul>
                        <?php 
                        // Get active section from URL parameter or default to applications
                        $active_section = isset($_GET['section']) ? sanitize_input($_GET['section']) : 'applications';
                        ?>
                        <li class="<?php echo ($active_section == 'applications') ? 'active' : ''; ?>">
                            <a href="?section=applications"><i class="fas fa-file-alt"></i> My Applications</a>
                        </li>
                        <li class="<?php echo ($active_section == 'recommendations') ? 'active' : ''; ?>">
                            <a href="?section=recommendations"><i class="fas fa-star"></i> Recommended Jobs</a>
                        </li>
                        <li class="<?php echo ($active_section == 'preferences') ? 'active' : ''; ?>">
                            <a href="?section=preferences"><i class="fas fa-cog"></i> Preferences</a>
                        </li>
                    </ul>
                </nav>
            </div>

            <div class="dashboard-main">
                <?php if ($active_section == 'applications'): ?>
                <section id="applications" class="dashboard-section active">
                    <div class="section-header">
                        <h2>My Applications</h2>
                    </div>

                    <?php if (mysqli_num_rows($applications_result) > 0): ?>
                        <div class="applications-list">
                            <?php 
                            // Reset the result pointer
                            mysqli_data_seek($applications_result, 0);
                            while ($app = mysqli_fetch_assoc($applications_result)): 
                            ?>
                                <div class="application-card <?php echo ($app['EOInumber'] == $eoi_number) ? 'active' : ''; ?>">
                                    <div class="app-header">
                                        <a href="?section=applications&eoi=<?php echo $app['EOInumber']; ?>" class="app-title">
                                            <?php echo htmlspecialchars($app['JobTitle']); ?>
                                        </a>
                                        <span class="app-location"><?php echo htmlspecialchars($app['Location']); ?></span>
                                    </div>
                                    <div class="app-details">
                                        <div class="app-reference">
                                            Ref: <?php echo htmlspecialchars($app['JobReferenceNumber']); ?>
                                        </div>
                                        <div class="app-date">
                                            Applied: <?php echo date("M d, Y", strtotime($app['ApplicationDate'])); ?>
                                        </div>
                                        <div class="app-status <?php echo strtolower($app['Status']); ?>">
                                            Status: <?php echo htmlspecialchars($app['Status']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <?php if ($eoi_number): ?>
                            <div class="application-details">
                                <?php 
                                // Include timeline component
                                include_once 'components/application_timeline.php'; 
                                ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-applications">
                            <div class="empty-state">
                                <i class="fas fa-file-alt empty-icon"></i>
                                <h3>No Applications Yet</h3>
                                <p>You haven't submitted any job applications. Browse our job listings and apply today!</p>
                                <a href="jobs.php" class="btn btn-primary">Browse Jobs</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <?php if ($active_section == 'recommendations'): ?>
                <section id="recommendations" class="dashboard-section active">
                    <div class="section-header">
                        <h2>Recommended Jobs</h2>
                    </div>
                    <?php include_once 'components/job_recommendations.php'; ?>
                </section>
                <?php endif; ?>

                <?php if ($active_section == 'preferences'): ?>
                <section id="preferences" class="dashboard-section active">
                    <div class="section-header">
                        <h2>My Preferences</h2>
                    </div>
                    
                    <?php
                    // Display success message if preferences were saved
                    if (isset($_GET['status']) && $_GET['status'] == 'preferences_saved'):
                    ?>
                    <div class="alert alert-success">
                        Your preferences have been saved successfully.
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" action="save_preferences.php" class="preferences-form">
                        <input type="hidden" name="section" value="preferences">
                        <div class="form-group">
                            <label for="location">Preferred Location</label>
                            <select name="location" id="location">
                                <option value="">Any Location</option>
                                <option value="Da Nang City" <?php echo ($preferences['PreferredLocation'] == 'Da Nang City') ? 'selected' : ''; ?>>Da Nang City</option>
                                <option value="Ho Chi Minh City" <?php echo ($preferences['PreferredLocation'] == 'Ho Chi Minh City') ? 'selected' : ''; ?>>Ho Chi Minh City</option>
                                <option value="Ha Noi City" <?php echo ($preferences['PreferredLocation'] == 'Ha Noi City') ? 'selected' : ''; ?>>Ha Noi City</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="job_type">Job Type</label>
                            <select name="job_type" id="job_type">
                                <option value="">Any Type</option>
                                <option value="Full-time" <?php echo ($preferences['PreferredJobType'] == 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                                <option value="Part-time" <?php echo ($preferences['PreferredJobType'] == 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                                <option value="Contract" <?php echo ($preferences['PreferredJobType'] == 'Contract') ? 'selected' : ''; ?>>Contract</option>
                                <option value="Internship" <?php echo ($preferences['PreferredJobType'] == 'Internship') ? 'selected' : ''; ?>>Internship</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="skills">Key Skills (comma separated)</label>
                            <input type="text" name="skills" id="skills" value="<?php echo htmlspecialchars($preferences['PreferredSkills'] ?? ''); ?>" placeholder="e.g. PHP, JavaScript, MySQL">
                        </div>
                        
                        <div class="form-group">
                            <label for="layout">Dashboard Layout</label>
                            <select name="layout" id="layout">
                                <option value="default" <?php echo ($preferences['DashboardLayout'] == 'default') ? 'selected' : ''; ?>>Default</option>
                                <option value="compact" <?php echo ($preferences['DashboardLayout'] == 'compact') ? 'selected' : ''; ?>>Compact</option>
                                <option value="expanded" <?php echo ($preferences['DashboardLayout'] == 'expanded') ? 'selected' : ''; ?>>Expanded</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Preferences</button>
                        </div>
                    </form>
                </section>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include_once 'footer.inc'; ?>
</body>
</html>
