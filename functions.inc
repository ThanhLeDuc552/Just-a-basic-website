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
?>