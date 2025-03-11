<?php
/**
 * Advanced Notifications Component
 * Provides a server-side notification system for users
 * 
 * This component uses PHP sessions and database to track and display
 * notifications without requiring JavaScript.
 */

// Ensure we have a session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'settings.php';
require_once 'functions.inc';

/**
 * Create a new notification for a user
 * 
 * @param string $user_email User's email
 * @param string $type Notification type (info, success, warning, error)
 * @param string $message Notification message
 * @param string $link Optional link for action
 * @param string $link_text Optional text for the link
 * @param object $conn Database connection
 * @return int|bool Notification ID or false on failure
 */
function create_notification($user_email, $type, $message, $link = '', $link_text = '', $conn) {
    $sql = "INSERT INTO user_notifications 
            (UserEmail, Type, Message, Link, LinkText, Created, IsRead) 
            VALUES (?, ?, ?, ?, ?, NOW(), 0)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $user_email, $type, $message, $link, $link_text);
    
    if (mysqli_stmt_execute($stmt)) {
        return mysqli_insert_id($conn);
    }
    
    return false;
}

/**
 * Get unread notifications for a user
 * 
 * @param string $user_email User's email
 * @param object $conn Database connection
 * @param int $limit Maximum number of notifications to retrieve
 * @return array Notifications array
 */
function get_unread_notifications($user_email, $conn, $limit = 5) {
    $sql = "SELECT * FROM user_notifications 
            WHERE UserEmail = ? AND IsRead = 0 
            ORDER BY Created DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $user_email, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
    
    return $notifications;
}

/**
 * Mark a notification as read
 * 
 * @param int $notification_id Notification ID
 * @param string $user_email User's email for verification
 * @param object $conn Database connection
 * @return bool Success status
 */
function mark_notification_read($notification_id, $user_email, $conn) {
    $sql = "UPDATE user_notifications 
            SET IsRead = 1, ReadTimestamp = NOW() 
            WHERE NotificationID = ? AND UserEmail = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $notification_id, $user_email);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Mark all notifications as read for a user
 * 
 * @param string $user_email User's email
 * @param object $conn Database connection
 * @return bool Success status
 */
function mark_all_notifications_read($user_email, $conn) {
    $sql = "UPDATE user_notifications 
            SET IsRead = 1, ReadTimestamp = NOW() 
            WHERE UserEmail = ? AND IsRead = 0";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $user_email);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Get the count of unread notifications
 * 
 * @param string $user_email User's email
 * @param object $conn Database connection
 * @return int Count of unread notifications
 */
function get_unread_notification_count($user_email, $conn) {
    $sql = "SELECT COUNT(*) as count FROM user_notifications 
            WHERE UserEmail = ? AND IsRead = 0";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $user_email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    return $row['count'];
}

// Check if a notification is being marked as read
if (isset($_GET['mark_read']) && !empty($_GET['mark_read']) && isset($_SESSION['user_email'])) {
    $notification_id = intval($_GET['mark_read']);
    mark_notification_read($notification_id, $_SESSION['user_email'], $conn);
    
    // Redirect back to originating page
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'dashboard.php';
    header("Location: $redirect_url");
    exit();
}

// Check if user is marking all notifications as read
if (isset($_GET['mark_all_read']) && $_GET['mark_all_read'] == '1' && isset($_SESSION['user_email'])) {
    mark_all_notifications_read($_SESSION['user_email'], $conn);
    
    // Redirect back to originating page
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'dashboard.php';
    header("Location: $redirect_url");
    exit();
}

// Only display notifications if user is logged in
if (isset($_SESSION['user_email'])):
    $unread_count = get_unread_notification_count($_SESSION['user_email'], $conn);
    $notifications = get_unread_notifications($_SESSION['user_email'], $conn);
?>

<div class="notifications-container">
    <div class="notifications-header">
        <h3>Notifications <?php if ($unread_count > 0): ?><span class="badge"><?php echo $unread_count; ?></span><?php endif; ?></h3>
        <?php if ($unread_count > 0): ?>
            <a href="?mark_all_read=1" class="mark-all-read">Mark all as read</a>
        <?php endif; ?>
    </div>
    
    <div class="notifications-list">
        <?php if (empty($notifications)): ?>
            <div class="no-notifications">
                <p>You have no new notifications</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item notification-<?php echo htmlspecialchars($notification['Type']); ?>">
                    <div class="notification-icon">
                        <?php if ($notification['Type'] == 'info'): ?>
                            <i class="fas fa-info-circle"></i>
                        <?php elseif ($notification['Type'] == 'success'): ?>
                            <i class="fas fa-check-circle"></i>
                        <?php elseif ($notification['Type'] == 'warning'): ?>
                            <i class="fas fa-exclamation-triangle"></i>
                        <?php elseif ($notification['Type'] == 'error'): ?>
                            <i class="fas fa-times-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="notification-content">
                        <div class="notification-message">
                            <?php echo htmlspecialchars($notification['Message']); ?>
                        </div>
                        <div class="notification-meta">
                            <span class="notification-time">
                                <?php 
                                    $created = new DateTime($notification['Created']);
                                    $now = new DateTime();
                                    $interval = $created->diff($now);
                                    
                                    if ($interval->d > 0) {
                                        echo $interval->d . ' days ago';
                                    } elseif ($interval->h > 0) {
                                        echo $interval->h . ' hours ago';
                                    } elseif ($interval->i > 0) {
                                        echo $interval->i . ' minutes ago';
                                    } else {
                                        echo 'Just now';
                                    }
                                ?>
                            </span>
                            <a href="?mark_read=<?php echo $notification['NotificationID']; ?>" class="mark-read">
                                Mark as read
                            </a>
                        </div>
                        <?php if (!empty($notification['Link'])): ?>
                            <div class="notification-action">
                                <a href="<?php echo htmlspecialchars($notification['Link']); ?>" class="action-link">
                                    <?php echo !empty($notification['LinkText']) ? htmlspecialchars($notification['LinkText']) : 'View Details'; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .notifications-container {
        max-width: 100%;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .notifications-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background-color: #f9f9f9;
        border-bottom: 1px solid #e0e0e0;
    }

    .notifications-header h3 {
        margin: 0;
        font-size: 16px;
        display: flex;
        align-items: center;
    }

    .badge {
        display: inline-block;
        min-width: 20px;
        height: 20px;
        line-height: 20px;
        text-align: center;
        background-color: #e53935;
        color: white;
        border-radius: 10px;
        margin-left: 8px;
        font-size: 12px;
        padding: 0 6px;
    }

    .mark-all-read {
        font-size: 14px;
        color: #2196F3;
        text-decoration: none;
    }

    .mark-all-read:hover {
        text-decoration: underline;
    }

    .notifications-list {
        max-height: 350px;
        overflow-y: auto;
    }

    .notification-item {
        display: flex;
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item:hover {
        background-color: #f5f5f5;
    }

    .notification-icon {
        flex: 0 0 40px;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding-top: 3px;
    }

    .notification-icon i {
        font-size: 18px;
    }

    .notification-info i {
        color: #2196F3;
    }

    .notification-success i {
        color: #4CAF50;
    }

    .notification-warning i {
        color: #FFC107;
    }

    .notification-error i {
        color: #F44336;
    }

    .notification-content {
        flex: 1;
    }

    .notification-message {
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .notification-meta {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #757575;
    }

    .mark-read {
        color: #2196F3;
        text-decoration: none;
    }

    .mark-read:hover {
        text-decoration: underline;
    }

    .notification-action {
        margin-top: 8px;
    }

    .action-link {
        display: inline-block;
        padding: 5px 10px;
        background-color: #2196F3;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 13px;
    }

    .action-link:hover {
        background-color: #1976D2;
    }

    .no-notifications {
        padding: 20px;
        text-align: center;
        color: #757575;
    }
</style>

<?php endif; ?>
