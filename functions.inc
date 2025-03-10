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
?>
