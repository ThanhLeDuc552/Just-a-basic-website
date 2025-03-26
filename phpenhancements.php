    <?php 
    $title = 'PHP Enhancements';
    $style = 'phpenhancements.css';
    include_once 'initial_page_settings.inc';
    ?>
    <?php include_once "header.inc"; ?>
    <div class="container">
    <h1>PHP Project Enhancements Documentation</h1>

    <div class="enhancement">
        <h2>Enhancement 1: Sorting EOI Records</h2>
        
        <div class="enhancement-details">
            <h3>Description</h3>
            <p>Implement a feature allowing managers to sort EOI (Expression of Interest) records based on selected fields.</p>
            
            <h3>PHP Implementation Example</h3>
            <div class="code-snippet">
&lt;?php
// Build the base query
$base_query = "FROM eoi WHERE 1=1";
$params = [];
$types = "";

if (!empty($filter_job)) {
    $base_query .= " AND JobReferenceNumber = ?";
    $params[] = $filter_job;
    $types .= "s";
}

if (!empty($filter_name)) {
    $base_query .= " AND (FirstName LIKE ? OR LastName LIKE ?)";
    $name_param = "%" . $filter_name . "%";
    $params[] = $name_param;
    $params[] = $name_param;
    $types .= "ss";
}

if (!empty($filter_status)) {
    $base_query .= " AND Status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total " . $base_query;
$count_stmt = mysqli_prepare($conn, $count_sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}

mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Ensure page is within valid range
if ($page &lt; 1) $page = 1;
if ($page &gt; $total_pages &amp;&amp; $total_pages &gt; 0) $page = $total_pages;

// Calculate offset for pagination
$offset = ($page - 1) * $records_per_page;

// Build the main query with sorting and pagination
$valid_sort_columns = ['EOInumber', 'JobReferenceNumber', 'FirstName', 'LastName', 'DOB', 'Status'];
$valid_sort_orders = ['ASC', 'DESC'];

if (!in_array($sort_by, $valid_sort_columns)) $sort_by = 'EOInumber';
if (!in_array($sort_order, $valid_sort_orders)) $sort_order = 'DESC';

$main_sql = "SELECT * " . $base_query . " ORDER BY $sort_by $sort_order LIMIT ?, ?";
$main_stmt = mysqli_prepare($conn, $main_sql);

// Add pagination parameters
$params[] = $offset;
$params[] = $records_per_page;
$types .= "ii";

if (!empty($params)) {
    mysqli_stmt_bind_param($main_stmt, $types, ...$params);
}

mysqli_stmt_execute($main_stmt);
$result = mysqli_stmt_get_result($main_stmt);
// Sorting Table Headers
?&gt;

&lt;th&gt;
    &lt;a href="manage.php?job_ref=&lt;?php echo urlencode($filter_job); ?&gt;&amp;applicant_name=&lt;?php echo urlencode($filter_name); ?&gt;&amp;status=&lt;?php echo urlencode($filter_status); ?&gt;&amp;sort=EOInumber&amp;order=&lt;?php echo ($sort_by == 'EOInumber' &amp;&amp; $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?&gt;"&gt;
        EOI #
        &lt;?php if ($sort_by == 'EOInumber'): ?&gt;
        &lt;span class="sort-indicator"&gt;&lt;?php echo ($sort_order == 'ASC') ? '▲' : '▼'; ?&gt;&lt;/span&gt;
        &lt;?php endif; ?&gt;
    &lt;/a&gt;
&lt;/th&gt;
&lt;th&gt;
    &lt;a href="manage.php?job_ref=&lt;?php echo urlencode($filter_job); ?&gt;&amp;applicant_name=&lt;?php echo urlencode($filter_name); ?&gt;&amp;status=&lt;?php echo urlencode($filter_status); ?&gt;&amp;sort=JobReferenceNumber&amp;order=&lt;?php echo ($sort_by == 'JobReferenceNumber' &amp;&amp; $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?&gt;"&gt;
        Job Ref
        &lt;?php if ($sort_by == 'JobReferenceNumber'): ?&gt;
        &lt;span class="sort-indicator"&gt;&lt;?php echo ($sort_order == 'ASC') ? '▲' : '▼'; ?&gt;&lt;/span&gt;
        &lt;?php endif; ?&gt;
    &lt;/a&gt;
&lt;/th&gt;
            </div>
            
            <h3>Key Benefits</h3>
            <ul>
                <li>Flexible sorting of EOI records</li>
                <li>Improves data management efficiency</li>
                <li>Simple user interface for selection</li>
            </ul>
            
            <p><strong>Detailed Implementation Link:</strong> <a href="login.php">Sorting Implementation Details (Account: admin | Password: Admin@123) (Need to login to test)</a></p>
        </div>
    </div>

    <div class="enhancement">
        <h2>Enhancement 2: Manager Registration & Access Control</h2>
        
        <div class="enhancement-details">
            <h3>Description</h3>
            <p>Create a secure manager registration system with server-side validation, unique username checks, and login attempt restrictions.</p>
            
            <h3>PHP Implementation Example</h3>
            <div class="code-snippet">
&lt;?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST["username"]) ? sanitize_input($_POST["username"]) : "";
    $email = isset($_POST["email"]) ? sanitize_input($_POST["email"]) : "";
    $password = isset($_POST["password"]) ? $_POST['password'] : '';

    if (is_account_locked($username, $conn)) {
        $err_msg = "Your account has been temporarily locked due to multiple failed login attempts. Please try again later.";
    } else {
        // Verify credentials
        $sql = "SELECT ManagerID, Username, Password, FirstName, LastName FROM managers WHERE Username = ? OR Email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['Password'])) {
                // Successful login
                $_SESSION['manager_id'] = $row['ManagerID'];
                $_SESSION['manager_name'] = $row['FirstName'] . ' ' . $row['LastName'];
                $_SESSION['username'] = $row['Username'];

                // Log successful login
                log_login_attempt($row["Username"], true, $conn);

                // Update last login time
                update_last_login($row["Username"], $conn);

                header("Location: manage.php");
                exit();
            } else {
                // Failed login - wrong password
                $err_msg = "Invalid username or password";
                log_login_attempt($row["Username"], false, $conn);

                // Check if account should be locked
                if (check_account_lockout($row["Username"], $conn)) {
                    lock_account($row["Username"], $conn);
                    $err_msg = "Your account has been temporarily locked due to multiple failed login attempts. Please try again later.";
                }
            }
        } else {
            // Failed login - username not found
            $err_msg = "Invalid username or password";
        }
    }
}
?&gt;
            </div>
            
            <h3>Key Security Features</h3>
            <ul>
                <li>Server-side username and password validation</li>
                <li>Unique username enforcement</li>
                <li>Automatic account lockout after multiple failed attempts</li>
                <li>Secure password complexity requirements</li>
            </ul>
            
            <p><strong>Detailed Implementation Link:</strong> <a href="login.php">Access Control Implementation Details</a></p>
        </div>
    </div>
    </div>
    <?php include_once "footer.inc"; ?>
