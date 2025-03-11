<?php
/**
 * Sanitizes user input to prevent XSS attacks
 *
 * @param string $data User input to sanitize
 * @return string Sanitized data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validates a date of birth to ensure it's between 15 and 80 years old
 *
 * @param string $dob Date of birth in yyyy-mm-dd format
 * @return bool True if valid, false otherwise
 */
function validate_dob($dob) {
    $today = new DateTime();
    $birthdate = new DateTime($dob);
    $age = $birthdate->diff($today)->y;
    return ($age >= 15 && $age <= 80);
}

/**
 * Validates postcode against state
 *
 * @param string $postcode 4-digit postcode
 * @param string $state State abbreviation
 * @return bool True if valid, false otherwise
 */
function validate_postcode_state($postcode, $state) {
    $state_ranges = [
        'NSW' => ['1000', '2999'],
        'ACT' => ['0200', '0299', '2600', '2618', '2900', '2920'],
        'VIC' => ['3000', '3999', '8000', '8999'],
        'QLD' => ['4000', '4999', '9000', '9999'],
        'SA' => ['5000', '5999'],
        'WA' => ['6000', '6999'],
        'TAS' => ['7000', '7999'],
        'NT' => ['0800', '0999']
    ];

    if ($state == 'ACT') {
        return (($postcode >= '0200' && $postcode <= '0299') ||
            ($postcode >= '2600' && $postcode <= '2618') ||
            ($postcode >= '2900' && $postcode <= '2920'));
    } else {
        return ($postcode >= $state_ranges[$state][0] && $postcode <= $state_ranges[$state][1]);
    }
}

/**
 * Validates email format
 *
 * @param string $email Email address to validate
 * @return bool True if valid, false otherwise
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validates phone number format (8-12 digits or spaces)
 *
 * @param string $phone Phone number to validate
 * @return bool True if valid, false otherwise
 */
function validate_phone($phone) {
    // Remove spaces for counting digits
    $digits_only = preg_replace('/\s+/', '', $phone);
    return preg_match('/^[0-9\s]{8,12}$/', $phone) && strlen($digits_only) >= 8 && strlen($digits_only) <= 12;
}

/**
 * Generates a CSRF token and stores it in the session
 *
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates a CSRF token
 *
 * @param string $token Token to validate
 * @return bool True if valid, false otherwise
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Logs a login attempt
 *
 * @param string $username Username attempted
 * @param bool $success Whether login was successful
 * @param object $conn Database connection
 */
function log_login_attempt($username, $success, $conn) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $sql = "INSERT INTO login_attempts (Username, AttemptTime, IPAddress, Success) 
            VALUES (?, NOW(), ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    $success_int = $success ? 1 : 0;
    mysqli_stmt_bind_param($stmt, "ssi", $username, $ip, $success_int);
    mysqli_stmt_execute($stmt);
}

/**
 * Checks if an account should be locked due to failed login attempts
 *
 * @param string $username Username to check
 * @param object $conn Database connection
 * @return bool True if account should be locked, false otherwise
 */
function check_account_lockout($username, $conn) {
    $timeframe = date('Y-m-d H:i:s', strtotime('-15 minutes'));
    $sql = "SELECT COUNT(*) as attempts FROM login_attempts 
            WHERE Username = ? AND Success = 0 AND AttemptTime > ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $timeframe);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return $row['attempts'] >= 3;
}

/**
 * Locks a user account
 *
 * @param string $username Username to lock
 * @param object $conn Database connection
 */
function lock_account($username, $conn) {
    $lock_until = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    $sql = "UPDATE managers SET AccountLocked = 1, LockUntil = ? WHERE Username = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $lock_until, $username);
    mysqli_stmt_execute($stmt);
}

/**
 * Checks if a user account is locked
 *
 * @param string $username Username to check
 * @param object $conn Database connection
 * @return bool True if account is locked, false otherwise
 */
function is_account_locked($username, $conn) {
    $sql = "SELECT AccountLocked, LockUntil FROM managers WHERE Username = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['AccountLocked']) {
            $now = new DateTime();
            $lock_until = new DateTime($row['LockUntil']);

            if ($now < $lock_until) {
                return true;
            } else {
                // Unlock account if lock period has expired
                $sql = "UPDATE managers SET AccountLocked = 0, LockUntil = NULL WHERE Username = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                return false;
            }
        }
    }

    return false;
}

/**
 * Updates the last login time for a user
 *
 * @param string $username Username to update
 * @param object $conn Database connection
 */
function update_last_login($username, $conn) {
    $sql = "UPDATE managers SET LastLogin = NOW() WHERE Username = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
}

/**
 * Generates pagination links
 *
 * @param int $total_records Total number of records
 * @param int $records_per_page Records to display per page
 * @param int $current_page Current page number
 * @param string $url Base URL for pagination links
 * @return string HTML for pagination links
 */
function generate_pagination($total_records, $records_per_page, $current_page, $url) {
    $total_pages = ceil($total_records / $records_per_page);

    if ($total_pages <= 1) {
        return '';
    }

    $pagination = '<div class="pagination">';

    // Previous page link
    if ($current_page > 1) {
        $pagination .= '<a href="' . $url . '&page=' . ($current_page - 1) . '">&laquo; Previous</a>';
    } else {
        $pagination .= '<span class="disabled">&laquo; Previous</span>';
    }

    // Page number links
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);

    if ($start_page > 1) {
        $pagination .= '<a href="' . $url . '&page=1">1</a>';
        if ($start_page > 2) {
            $pagination .= '<span class="ellipsis">...</span>';
        }
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            $pagination .= '<span class="current">' . $i . '</span>';
        } else {
            $pagination .= '<a href="' . $url . '&page=' . $i . '">' . $i . '</a>';
        }
    }

    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            $pagination .= '<span class="ellipsis">...</span>';
        }
        $pagination .= '<a href="' . $url . '&page=' . $total_pages . '">' . $total_pages . '</a>';
    }

    // Next page link
    if ($current_page < $total_pages) {
        $pagination .= '<a href="' . $url . '&page=' . ($current_page + 1) . '">Next &raquo;</a>';
    } else {
        $pagination .= '<span class="disabled">Next &raquo;</span>';
    }

    $pagination .= '</div>';

    return $pagination;
}

// Add these functions to your existing functions.inc file

/**
 * Get user preferences from database
 *
 * @param int $manager_id Manager ID
 * @param object $conn Database connection
 * @return array User preferences
 */
function get_user_preferences($manager_id, $conn) {
    $defaults = [
        'theme' => 'light',
        'records_per_page' => 10,
        'default_sort' => 'EOInumber',
        'email_notifications' => 1
    ];

    $sql = "SELECT * FROM manager_preferences WHERE ManagerID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $manager_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return [
            'theme' => $row['Theme'],
            'records_per_page' => $row['RecordsPerPage'],
            'default_sort' => $row['DefaultSort'],
            'email_notifications' => $row['EmailNotifications']
        ];
    }

    return $defaults;
}

/**
 * Log status change in history
 *
 * @param int $eoi_id EOI number
 * @param string $old_status Old status
 * @param string $new_status New status
 * @param int $manager_id Manager ID
 * @param object $conn Database connection
 * @return bool Success status
 */
function log_status_change($eoi_id, $old_status, $new_status, $manager_id, $conn) {
    $sql = "INSERT INTO status_history (EOInumber, OldStatus, NewStatus, ChangedBy) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issi", $eoi_id, $old_status, $new_status, $manager_id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Calculate age from date of birth
 *
 * @param string $dob Date of birth in Y-m-d format
 * @return int Age in years
 */
function calculate_age($dob) {
    $birthdate = new DateTime($dob);
    $today = new DateTime();
    $age = $birthdate->diff($today)->y;
    return $age;
}

/**
 * Add or update query parameters in URL
 *
 * @param string $url Base URL
 * @param string $param Parameter name
 * @param string $value Parameter value
 * @return string Updated URL
 */
function add_query_param($url, $param, $value) {
    $query = parse_url($url, PHP_URL_QUERY);

    if ($query) {
        parse_str($query, $query_params);
        $query_params[$param] = $value;
        $url = str_replace("?$query", '?' . http_build_query($query_params), $url);
    } else {
        $url .= '?' . $param . '=' . $value;
    }

    return $url;
}

/**
 * Format date for display
 *
 * @param string $date Date string
 * @param string $format Format string
 * @return string Formatted date
 */
function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Get application status counts
 *
 * @param object $conn Database connection
 * @return array Status counts
 */
function get_status_counts($conn) {
    $sql = "SELECT Status, COUNT(*) as count FROM eoi GROUP BY Status ORDER BY 
        CASE 
            WHEN Status = 'New' THEN 1 
            WHEN Status = 'Current' THEN 2 
            WHEN Status = 'Final' THEN 3 
        END";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Get applications by status
 *
 * @param string $status Status to filter by
 * @param int $limit Maximum number of records
 * @param object $conn Database connection
 * @return array Applications
 */
function get_applications_by_status($status, $limit, $conn) {
    $sql = "SELECT e.*, j.Title as JobTitle FROM eoi e 
            JOIN jobs j ON e.JobReferenceNumber = j.JobReferenceNumber 
            WHERE e.Status = ? 
            ORDER BY e.EOInumber DESC LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Get application details by ID
 *
 * @param int $eoi_id EOI number
 * @param object $conn Database connection
 * @return array|null Application details
 */
function get_application_details($eoi_id, $conn) {
    $sql = "SELECT e.*, j.Title as JobTitle, j.Position 
            FROM eoi e 
            JOIN jobs j ON e.JobReferenceNumber = j.JobReferenceNumber 
            WHERE e.EOInumber = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $eoi_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }

    return null;
}

/**
 * Track job view for recommendation system
 *
 * @param string $job_ref Job reference number
 * @param string $email User email (can be null for anonymous users)
 * @param object $conn Database connection
 * @return bool Success status
 */
function track_job_view($job_ref, $email, $conn) {
    // Create or get session ID
    if (!isset($_SESSION['visitor_id'])) {
        $_SESSION['visitor_id'] = session_id();
    }
    
    $session_id = $_SESSION['visitor_id'];
    $device_type = get_device_type();
    
    $sql = "INSERT INTO job_views (JobReferenceNumber, UserEmail, SessionID, DeviceType) 
            VALUES (?, ?, ?, ?)";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $job_ref, $email, $session_id, $device_type);
    return mysqli_stmt_execute($stmt);
}

/**
 * Update job view duration
 *
 * @param int $view_id View ID to update
 * @param int $duration View duration in seconds
 * @param object $conn Database connection
 * @return bool Success status
 */
function update_view_duration($view_id, $duration, $conn) {
    $sql = "UPDATE job_views SET ViewDuration = ? WHERE ViewID = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $duration, $view_id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Get recommended jobs based on user viewing history
 *
 * @param string $session_id Session ID
 * @param int $limit Maximum number of recommendations
 * @param object $conn Database connection
 * @return array Recommended jobs
 */
function get_recommended_jobs($session_id, $limit = 3, $conn) {
    // Get user's viewing history
    $sql = "SELECT JobReferenceNumber, COUNT(*) as view_count, SUM(ViewDuration) as total_duration 
            FROM job_views 
            WHERE SessionID = ? 
            GROUP BY JobReferenceNumber 
            ORDER BY total_duration DESC";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $viewed_jobs = [];
    $viewed_job_refs = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $viewed_jobs[] = $row;
        $viewed_job_refs[] = $row['JobReferenceNumber'];
    }
    
    // If user has no history, return random jobs
    if (empty($viewed_jobs)) {
        $sql = "SELECT * FROM jobs ORDER BY RAND() LIMIT ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }
    
    // Get related jobs based on similar keywords
    $placeholders = str_repeat('?,', count($viewed_job_refs) - 1) . '?';
    $sql = "SELECT j.*, 
            (SELECT GROUP_CONCAT(sk.Keyword SEPARATOR ', ') 
             FROM skill_keywords sk 
             WHERE sk.JobReferenceNumber = j.JobReferenceNumber) as skills
            FROM jobs j
            WHERE j.JobReferenceNumber NOT IN ($placeholders)
            ORDER BY RAND()
            LIMIT ?";
            
    $params = array_merge($viewed_job_refs, [$limit]);
    $types = str_repeat('s', count($viewed_job_refs)) . 'i';
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    
    return mysqli_stmt_get_result($stmt);
}

/**
 * Parse resume text and extract skills
 *
 * @param string $resume_text Resume content
 * @param object $conn Database connection
 * @return array Extracted skills and their weights
 */
function parse_resume_skills($resume_text, $conn) {
    // Get all possible skills from database
    $sql = "SELECT Keyword FROM skill_keywords GROUP BY Keyword";
    $result = mysqli_query($conn, $sql);
    
    $all_skills = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $all_skills[] = $row['Keyword'];
    }
    
    // Look for skills in resume text
    $found_skills = [];
    foreach ($all_skills as $skill) {
        // Case insensitive search
        if (stripos($resume_text, $skill) !== false) {
            // Count occurrences for weighting
            $count = substr_count(strtolower($resume_text), strtolower($skill));
            $found_skills[$skill] = $count;
        }
    }
    
    // Sort by frequency
    arsort($found_skills);
    
    return $found_skills;
}

/**
 * Calculate job match score based on applicant skills and job requirements
 *
 * @param array $applicant_skills Applicant skills array
 * @param string $job_ref Job reference number
 * @param object $conn Database connection
 * @return int Match score (0-100)
 */
function calculate_job_match($applicant_skills, $job_ref, $conn) {
    // Get job required skills
    $sql = "SELECT Keyword, Weight FROM skill_keywords WHERE JobReferenceNumber = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $job_ref);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $job_skills = [];
    $total_weight = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $job_skills[$row['Keyword']] = $row['Weight'];
        $total_weight += $row['Weight'];
    }
    
    if ($total_weight == 0) {
        return 0; // No skills defined for this job
    }
    
    // Calculate match score
    $match_score = 0;
    foreach ($job_skills as $skill => $weight) {
        if (array_key_exists($skill, $applicant_skills)) {
            $match_score += $weight;
        }
    }
    
    // Convert to percentage
    return round(($match_score / $total_weight) * 100);
}

/**
 * Add application timeline event
 *
 * @param int $eoi_number EOI number
 * @param string $stage Timeline stage
 * @param string $notes Optional notes
 * @param string $next_date Optional next step date (Y-m-d format)
 * @param object $conn Database connection
 * @return bool Success status
 */
function add_timeline_event($eoi_number, $stage, $notes = '', $next_date = null, $conn) {
    $sql = "INSERT INTO application_timeline (EOInumber, Stage, Notes, NextStepDate) 
            VALUES (?, ?, ?, ?)";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $eoi_number, $stage, $notes, $next_date);
    return mysqli_stmt_execute($stmt);
}

/**
 * Get application timeline
 *
 * @param int $eoi_number EOI number
 * @param object $conn Database connection
 * @return array Timeline events
 */
function get_application_timeline($eoi_number, $conn) {
    $sql = "SELECT * FROM application_timeline 
            WHERE EOInumber = ? 
            ORDER BY StageTimestamp ASC";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $eoi_number);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $timeline = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $timeline[] = $row;
    }
    
    return $timeline;
}

/**
 * Tracks job views for the recommendation system
 * 
 * @param string $job_ref Job reference number
 * @param string $email User email (can be null for anonymous users)
 * @param object $conn Database connection
 * @return int|false View ID if successful, false otherwise
 */
function track_job_view($job_ref, $email, $conn) {
    // Create or get session ID
    if (!isset($_SESSION['visitor_id'])) {
        $_SESSION['visitor_id'] = session_id();
    }
    
    $visitor_id = $_SESSION['visitor_id'];
    $view_timestamp = date('Y-m-d H:i:s');
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $device_type = get_device_type();
    
    // Insert view record
    $sql = "INSERT INTO job_views 
            (JobReferenceNumber, VisitorID, Email, ViewTimestamp, DeviceType, UserAgent) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssss", $job_ref, $visitor_id, $email, $view_timestamp, $device_type, $user_agent);
    
    if (mysqli_stmt_execute($stmt)) {
        return mysqli_insert_id($conn);
    }
    
    return false;
}

/**
 * Get device type from user agent
 * 
 * @return string Device type (mobile, tablet, desktop)
 */
function get_device_type() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    if (preg_match('/(android|webos|iphone|ipad|ipod|blackberry|windows phone)/i', $user_agent)) {
        if (preg_match('/(ipad|tablet)/i', $user_agent)) {
            return 'tablet';
        }
        return 'mobile';
    }
    
    return 'desktop';
}

/**
 * Get user preferences from database
 * 
 * @param string $email User email
 * @param object $conn Database connection
 * @return array User preferences
 */
function get_user_preferences($email, $conn) {
    $sql = "SELECT * FROM user_preferences WHERE Email = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row;
    }
    
    // Return default preferences if none found
    return [
        'PreferredLocation' => '',
        'PreferredSalary' => '',
        'PreferredJobType' => '',
        'PreferredSkills' => '',
        'DashboardLayout' => 'default',
        'theme' => 'light'
    ];
}

/**
 * Save user preferences to database
 * 
 * @param string $email User email
 * @param array $preferences Preferences array
 * @param object $conn Database connection
 * @return bool Success status
 */
function save_user_preferences($email, $preferences, $conn) {
    // Prepare values
    $location = $preferences['location'] ?? '';
    $salary = $preferences['salary'] ?? '';
    $job_type = $preferences['job_type'] ?? '';
    $skills = $preferences['skills'] ?? '';
    $layout = $preferences['layout'] ?? 'default';
    $theme = $preferences['theme'] ?? 'light';
    
    // Check if user preferences already exist
    $check_sql = "SELECT COUNT(*) as count FROM user_preferences WHERE Email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $row = mysqli_fetch_assoc($check_result);
    
    if ($row['count'] > 0) {
        // Update existing preferences
        $sql = "UPDATE user_preferences 
                SET PreferredLocation = ?, 
                    PreferredSalary = ?, 
                    PreferredJobType = ?, 
                    PreferredSkills = ?, 
                    DashboardLayout = ?,
                    theme = ?,
                    LastUpdated = NOW()
                WHERE Email = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $location, $salary, $job_type, $skills, $layout, $theme, $email);
        return mysqli_stmt_execute($stmt);
    } else {
        // Insert new preferences
        $sql = "INSERT INTO user_preferences 
                (Email, PreferredLocation, PreferredSalary, PreferredJobType, PreferredSkills, DashboardLayout, theme, LastUpdated) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $email, $location, $salary, $job_type, $skills, $layout, $theme);
        return mysqli_stmt_execute($stmt);
    }
}

?>
