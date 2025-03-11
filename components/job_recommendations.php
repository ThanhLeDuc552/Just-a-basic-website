<?php
/**
 * Job Recommendations Component
 * Displays personalized job recommendations based on user browsing history
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_id();
    session_start();
}

// Create or get visitor ID for tracking
if (!isset($_SESSION['visitor_id'])) {
    $_SESSION['visitor_id'] = session_id();
}

require_once 'settings.php';
require_once 'functions.inc';

// Track job view if 'track_job' parameter is present in URL
if (isset($_GET['track_job'])) {
    $job_ref = sanitize_input($_GET['track_job']);
    $email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
    
    // Call the track_job_view function to record the view
    if (function_exists('track_job_view')) {
        track_job_view($job_ref, $email, $conn);
    }
    
    // Redirect back to the page without the tracking parameter
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?'); // Remove all query parameters
    
    // Add back other parameters except track_job
    $params = [];
    foreach ($_GET as $key => $value) {
        if ($key !== 'track_job') {
            $params[] = $key . '=' . urlencode($value);
        }
    }
    
    if (!empty($params)) {
        $redirect_url .= '?' . implode('&', $params);
    }
    
    header("Location: " . $redirect_url);
    exit();
}

// Get recommendations based on session ID
$session_id = $_SESSION['visitor_id'];
$recommended_result = get_recommended_jobs($session_id, 3, $conn);
$recommended_jobs = [];

if ($recommended_result) {
    while ($row = mysqli_fetch_assoc($recommended_result)) {
        $recommended_jobs[] = $row;
    }
}

// Only display if we have recommendations
if (count($recommended_jobs) > 0):
?>

<section class="bg-white recommendations">
    <div class="container">
        <div class="section-header">
            <h2>Recommended for You</h2>
            <p>Based on your interests, we think these positions might be a good fit.</p>
        </div>
        
        <div class="recommendation-container">
            <?php foreach ($recommended_jobs as $job): ?>
                <div class="recommendation-card">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($job['Title']); ?></h3>
                        <span class="location-tag"><?php echo htmlspecialchars($job['Location']); ?></span>
                    </div>
                    
                    <div class="card-body">
                        <div class="salary"><?php echo htmlspecialchars($job['Salary']); ?></div>
                        <div class="position-type"><?php echo htmlspecialchars($job['Position']); ?></div>
                        
                        <?php if (isset($job['skills'])): ?>
                        <div class="skills">
                            <h4>Key Skills</h4>
                            <div class="skill-tags">
                                <?php 
                                $skills = explode(', ', $job['skills']);
                                foreach (array_slice($skills, 0, 5) as $skill):
                                ?>
                                    <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer">
                        <?php
                        // Create URLs with tracking parameters
                        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                        $current_url = strtok($current_url, '?'); // Remove existing query parameters
                        
                        // Add section parameter if on dashboard
                        $section_param = '';
                        if (strpos($current_url, 'dashboard.php') !== false) {
                            $section_param = isset($_GET['section']) ? '&section=' . sanitize_input($_GET['section']) : '&section=recommendations';
                        }
                        
                        $detail_url = "page" . substr($job['JobReferenceNumber'], -1) . ".php?track_job=" . urlencode($job['JobReferenceNumber']);
                        $apply_url = "apply.php?job=" . urlencode($job['JobReferenceNumber']) . "&track_job=" . urlencode($job['JobReferenceNumber']);
                        ?>
                        <a href="<?php echo $detail_url; ?>" class="view-job-btn">View Details</a>
                        <a href="<?php echo $apply_url; ?>" class="apply-now-btn">Apply Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php endif; ?>
