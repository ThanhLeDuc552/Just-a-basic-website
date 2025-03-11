<?php
/**
 * Advanced Search Component
 * Provides powerful search functionality with filtering and pagination
 * using only PHP, HTML, and CSS (no JavaScript)
 */

require_once 'settings.php';
require_once 'functions.inc';

// Default parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
$search_term = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$location = isset($_GET['location']) ? sanitize_input($_GET['location']) : '';
$salary_min = isset($_GET['salary_min']) ? sanitize_input($_GET['salary_min']) : '';
$salary_max = isset($_GET['salary_max']) ? sanitize_input($_GET['salary_max']) : '';
$job_type = isset($_GET['job_type']) ? sanitize_input($_GET['job_type']) : '';
$order_by = isset($_GET['order_by']) ? sanitize_input($_GET['order_by']) : 'date';
$order_dir = isset($_GET['order_dir']) ? sanitize_input($_GET['order_dir']) : 'desc';

// Validate and sanitize order parameters
$allowed_order_by = ['date', 'title', 'salary', 'location'];
$allowed_order_dir = ['asc', 'desc'];

if (!in_array($order_by, $allowed_order_by)) {
    $order_by = 'date';
}
if (!in_array($order_dir, $allowed_order_dir)) {
    $order_dir = 'desc';
}

// Calculate offset
$offset = ($page - 1) * $per_page;

// Build SQL query
$sql_conditions = [];
$sql_params = [];
$param_types = '';

// Add search condition if provided
if (!empty($search_term)) {
    $sql_conditions[] = "(Title LIKE ? OR Description LIKE ? OR Position LIKE ?)";
    $search_param = "%$search_term%";
    $sql_params[] = $search_param;
    $sql_params[] = $search_param;
    $sql_params[] = $search_param;
    $param_types .= 'sss';
}

// Add location filter if provided
if (!empty($location)) {
    $sql_conditions[] = "Location LIKE ?";
    $sql_params[] = "%$location%";
    $param_types .= 's';
}

// Add salary range filter if provided
if (!empty($salary_min)) {
    // Extract numeric value from salary for comparison
    $sql_conditions[] = "CAST(REPLACE(REPLACE(Salary, '$', ''), ',', '') AS DECIMAL(10,2)) >= ?";
    $sql_params[] = floatval($salary_min);
    $param_types .= 'd';
}
if (!empty($salary_max)) {
    // Extract numeric value from salary for comparison
    $sql_conditions[] = "CAST(REPLACE(REPLACE(Salary, '$', ''), ',', '') AS DECIMAL(10,2)) <= ?";
    $sql_params[] = floatval($salary_max);
    $param_types .= 'd';
}

// Add job type filter if provided
if (!empty($job_type)) {
    $sql_conditions[] = "Position LIKE ?";
    $sql_params[] = "%$job_type%";
    $param_types .= 's';
}

// Combine conditions
$where_clause = '';
if (!empty($sql_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $sql_conditions);
}

// Determine ORDER BY based on selected option
switch ($order_by) {
    case 'title':
        $sql_order = "ORDER BY Title " . strtoupper($order_dir);
        break;
    case 'salary':
        $sql_order = "ORDER BY CAST(REPLACE(REPLACE(Salary, '$', ''), ',', '') AS DECIMAL(10,2)) " . strtoupper($order_dir);
        break;
    case 'location':
        $sql_order = "ORDER BY Location " . strtoupper($order_dir);
        break;
    case 'date':
    default:
        $sql_order = "ORDER BY PostedDate " . strtoupper($order_dir);
        break;
}

// Build complete query
$count_sql = "SELECT COUNT(*) as total FROM jobs $where_clause";
$main_sql = "SELECT * FROM jobs $where_clause $sql_order LIMIT ? OFFSET ?";

// Count total results
$count_stmt = mysqli_prepare($conn, $count_sql);
if (!empty($param_types)) {
    mysqli_stmt_bind_param($count_stmt, $param_types, ...$sql_params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_row = mysqli_fetch_assoc($count_result);
$total_results = $count_row['total'];

// Calculate total pages
$total_pages = ceil($total_results / $per_page);

// Fetch results
$main_stmt = mysqli_prepare($conn, $main_sql);
if (!empty($sql_params)) {
    // Add pagination parameters
    $sql_params[] = intval($per_page);
    $sql_params[] = intval($offset);
    $param_types .= 'ii';
    
    mysqli_stmt_bind_param($main_stmt, $param_types, ...$sql_params);
} else {
    mysqli_stmt_bind_param($main_stmt, 'ii', $per_page, $offset);
}

mysqli_stmt_execute($main_stmt);
$results = mysqli_stmt_get_result($main_stmt);

// Get unique locations and job types for filter options
$locations_sql = "SELECT DISTINCT Location FROM jobs ORDER BY Location";
$locations_result = mysqli_query($conn, $locations_sql);
$locations = [];
while ($row = mysqli_fetch_assoc($locations_result)) {
    $locations[] = $row['Location'];
}

$job_types_sql = "SELECT DISTINCT Position FROM jobs ORDER BY Position";
$job_types_result = mysqli_query($conn, $job_types_sql);
$job_types = [];
while ($row = mysqli_fetch_assoc($job_types_result)) {
    $job_types[] = $row['Position'];
}

// Save the current search to the user's search history
if (isset($_SESSION['user_email']) && !empty($search_term)) {
    $email = $_SESSION['user_email'];
    $timestamp = date('Y-m-d H:i:s');
    
    $history_sql = "INSERT INTO search_history (UserEmail, SearchTerm, Filters, SearchDate) 
                    VALUES (?, ?, ?, ?)";
    
    // Serialize filters for storage
    $filters = [
        'location' => $location,
        'salary_min' => $salary_min,
        'salary_max' => $salary_max,
        'job_type' => $job_type,
        'order_by' => $order_by,
        'order_dir' => $order_dir
    ];
    $serialized_filters = serialize($filters);
    
    $history_stmt = mysqli_prepare($conn, $history_sql);
    mysqli_stmt_bind_param($history_stmt, 'ssss', $email, $search_term, $serialized_filters, $timestamp);
    mysqli_stmt_execute($history_stmt);
}

// Function to build pagination links
function build_pagination_url($page_num) {
    $params = $_GET;
    $params['page'] = $page_num;
    return '?' . http_build_query($params);
}

// Function to build sorting links
function build_sorting_url($new_order_by, $new_order_dir = null) {
    $params = $_GET;
    $params['order_by'] = $new_order_by;
    
    // If order_by is the same, toggle direction
    if (isset($_GET['order_by']) && $_GET['order_by'] == $new_order_by && !$new_order_dir) {
        $params['order_dir'] = ($_GET['order_dir'] == 'asc') ? 'desc' : 'asc';
    } else {
        $params['order_dir'] = $new_order_dir ?: 'asc';
    }
    
    $params['page'] = 1; // Reset to first page when sorting changes
    return '?' . http_build_query($params);
}

// Function to get sort indicator
function get_sort_indicator($column) {
    global $order_by, $order_dir;
    if ($order_by == $column) {
        return ($order_dir == 'asc') ? '▲' : '▼';
    }
    return '';
}

// Helper function to highlight search terms in text
function highlight_search_term($text, $term) {
    if (empty($term)) {
        return $text;
    }
    
    $highlighted = preg_replace('/(' . preg_quote($term, '/') . ')/i', '<mark>$1</mark>', $text);
    return $highlighted;
}
?>

<div class="advanced-search-container">
    <h2>Advanced Job Search</h2>
    
    <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="search-form">
        <div class="search-row">
            <div class="search-field main-search">
                <label for="search">Keywords</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Job title, skills, or keywords">
            </div>
            
            <div class="search-field">
                <label for="location">Location</label>
                <select id="location" name="location">
                    <option value="">Any Location</option>
                    <?php foreach ($locations as $loc): ?>
                    <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo ($location == $loc) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($loc); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="search-row">
            <div class="search-field">
                <label for="salary_min">Minimum Salary</label>
                <input type="number" id="salary_min" name="salary_min" value="<?php echo htmlspecialchars($salary_min); ?>" placeholder="Min">
            </div>
            
            <div class="search-field">
                <label for="salary_max">Maximum Salary</label>
                <input type="number" id="salary_max" name="salary_max" value="<?php echo htmlspecialchars($salary_max); ?>" placeholder="Max">
            </div>
            
            <div class="search-field">
                <label for="job_type">Job Type</label>
                <select id="job_type" name="job_type">
                    <option value="">Any Type</option>
                    <?php foreach ($job_types as $type): ?>
                    <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($job_type == $type) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($type); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="search-actions">
            <input type="hidden" name="per_page" value="<?php echo $per_page; ?>">
            <input type="hidden" name="order_by" value="<?php echo $order_by; ?>">
            <input type="hidden" name="order_dir" value="<?php echo $order_dir; ?>">
            
            <button type="submit" class="search-button">Search Jobs</button>
            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="reset-button">Reset Filters</a>
        </div>
    </form>
    
    <div class="search-results">
        <div class="results-header">
            <div class="results-count">
                <?php echo $total_results; ?> job<?php echo ($total_results != 1) ? 's' : ''; ?> found
                <?php if (!empty($search_term)): ?>
                    for "<strong><?php echo htmlspecialchars($search_term); ?></strong>"
                <?php endif; ?>
            </div>
            
            <div class="results-options">
                <label for="per_page_select">Show:</label>
                <select id="per_page_select" onchange="location = this.value;">
                    <?php foreach ([10, 25, 50] as $option): ?>
                    <option value="<?php 
                        $params = $_GET;
                        $params['per_page'] = $option;
                        $params['page'] = 1;
                        echo '?' . http_build_query($params);
                    ?>" <?php echo ($per_page == $option) ? 'selected' : ''; ?>>
                        <?php echo $option; ?> per page
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <label for="sort_select">Sort by:</label>
                <select id="sort_select" onchange="location = this.value;">
                    <option value="<?php echo build_sorting_url('date', 'desc'); ?>" <?php echo ($order_by == 'date' && $order_dir == 'desc') ? 'selected' : ''; ?>>
                        Newest First
                    </option>
                    <option value="<?php echo build_sorting_url('date', 'asc'); ?>" <?php echo ($order_by == 'date' && $order_dir == 'asc') ? 'selected' : ''; ?>>
                        Oldest First
                    </option>
                    <option value="<?php echo build_sorting_url('title', 'asc'); ?>" <?php echo ($order_by == 'title' && $order_dir == 'asc') ? 'selected' : ''; ?>>
                        Title (A-Z)
                    </option>
                    <option value="<?php echo build_sorting_url('title', 'desc'); ?>" <?php echo ($order_by == 'title' && $order_dir == 'desc') ? 'selected' : ''; ?>>
                        Title (Z-A)
                    </option>
                    <option value="<?php echo build_sorting_url('salary', 'desc'); ?>" <?php echo ($order_by == 'salary' && $order_dir == 'desc') ? 'selected' : ''; ?>>
                        Salary (High-Low)
                    </option>
                    <option value="<?php echo build_sorting_url('salary', 'asc'); ?>" <?php echo ($order_by == 'salary' && $order_dir == 'asc') ? 'selected' : ''; ?>>
                        Salary (Low-High)
                    </option>
                </select>
            </div>
        </div>
        
        <?php if (mysqli_num_rows($results) > 0): ?>
            <div class="results-list">
                <?php while ($job = mysqli_fetch_assoc($results)): ?>
                    <div class="job-card">
                        <div class="job-header">
                            <h3 class="job-title">
                                <?php echo highlight_search_term(htmlspecialchars($job['Title']), $search_term); ?>
                            </h3>
                            <div class="job-ref">Ref: <?php echo htmlspecialchars($job['JobReferenceNumber']); ?></div>
                        </div>
                        
                        <div class="job-details">
                            <div class="job-info">
                                <div class="job-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo highlight_search_term(htmlspecialchars($job['Location']), $search_term); ?>
                                </div>
                                <div class="job-type">
                                    <i class="fas fa-briefcase"></i>
                                    <?php echo highlight_search_term(htmlspecialchars($job['Position']), $search_term); ?>
                                </div>
                                <div class="job-salary">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <?php echo htmlspecialchars($job['Salary']); ?>
                                </div>
                            </div>
                            
                            <div class="job-description">
                                <?php 
                                    // Truncate description to 200 characters
                                    $desc = strip_tags($job['Description']);
                                    $short_desc = substr($desc, 0, 200) . (strlen($desc) > 200 ? '...' : '');
                                    echo highlight_search_term(htmlspecialchars($short_desc), $search_term);
                                ?>
                            </div>
                        </div>
                        
                        <div class="job-actions">
                            <a href="page<?php echo substr($job['JobReferenceNumber'], -1); ?>.php?track_job=<?php echo urlencode($job['JobReferenceNumber']); ?>" class="view-job-btn">View Details</a>
                            <a href="apply.php?job=<?php echo urlencode($job['JobReferenceNumber']); ?>" class="apply-now-btn">Apply Now</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo build_pagination_url(1); ?>" class="page-link first-page">&laquo; First</a>
                        <a href="<?php echo build_pagination_url($page - 1); ?>" class="page-link prev-page">&lsaquo; Previous</a>
                    <?php endif; ?>
                    
                    <?php
                    // Determine range of page numbers to display
                    $range = 2; // Display 2 pages before and after current page
                    $start_page = max(1, $page - $range);
                    $end_page = min($total_pages, $page + $range);
                    
                    // Always show first page
                    if ($start_page > 1) {
                        echo '<a href="' . build_pagination_url(1) . '" class="page-link">1</a>';
                        if ($start_page > 2) {
                            echo '<span class="ellipsis">...</span>';
                        }
                    }
                    
                    // Display page numbers
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        if ($i == $page) {
                            echo '<span class="page-link current-page">' . $i . '</span>';
                        } else {
                            echo '<a href="' . build_pagination_url($i) . '" class="page-link">' . $i . '</a>';
                        }
                    }
                    
                    // Always show last page
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<span class="ellipsis">...</span>';
                        }
                        echo '<a href="' . build_pagination_url($total_pages) . '" class="page-link">' . $total_pages . '</a>';
                    }
                    ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="<?php echo build_pagination_url($page + 1); ?>" class="page-link next-page">Next &rsaquo;</a>
                        <a href="<?php echo build_pagination_url($total_pages); ?>" class="page-link last-page">Last &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <p>No jobs found matching your search criteria.</p>
                <p>Try broadening your search or <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">reset all filters</a>.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.advanced-search-container {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.search-form {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.search-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}

.search-field {
    flex: 1;
    min-width: 180px;
}

.main-search {
    flex: 2;
}

.search-field label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    font-size: 14px;
    color: #333;
}

.search-field input,
.search-field select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.search-actions {
    display: flex;
    justify-content: flex-start;
    gap: 10px;
    margin-top: 20px;
}

.search-button {
    background-color: #0078d4;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.2s;
}

.search-button:hover {
    background-color: #005a9e;
}

.reset-button {
    background-color: #f0f0f0;
    color: #333;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.2s;
}

.reset-button:hover {
    background-color: #e0e0e0;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.results-count {
    font-size: 16px;
    color: #555;
}

.results-options {
    display: flex;
    align-items: center;
    gap: 10px;
}

.results-options label {
    font-size: 14px;
    color: #555;
}

.results-options select {
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.job-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 20px;
    margin-bottom: 20px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.job-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.job-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.job-title {
    font-size: 18px;
    margin: 0;
    color: #0078d4;
}

.job-ref {
    font-size: 12px;
    color: #777;
    background-color: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
}

.job-details {
    margin-bottom: 20px;
}

.job-info {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}

.job-location, .job-type, .job-salary {
    font-size: 14px;
    color: #555;
    display: flex;
    align-items: center;
}

.job-info i {
    margin-right: 5px;
    color: #777;
}

.job-description {
    font-size: 14px;
    line-height: 1.5;
    color: #444;
}

.job-actions {
    display: flex;
    gap: 10px;
}

.view-job-btn, .apply-now-btn {
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: bold;
    transition: background-color 0.2s;
}

.view-job-btn {
    background-color: #f0f0f0;
    color: #333;
}

.view-job-btn:hover {
    background-color: #e0e0e0;
}

.apply-now-btn {
    background-color: #0078d4;
    color: white;
}

.apply-now-btn:hover {
    background-color: #005a9e;
}

.no-results {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    color: #555;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.page-link {
    display: inline-block;
    padding: 8px 12px;
    background-color: #f0f0f0;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.page-link:hover {
    background-color: #e0e0e0;
}

.current-page {
    background-color: #0078d4;
    color: white;
}

.ellipsis {
    display: inline-block;
    padding: 8px 12px;
    color: #777;
}

mark {
    background-color: #ffeb3b;
    padding: 0 2px;
}

@media (max-width: 768px) {
    .search-row {
        flex-direction: column;
    }
    
    .results-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .job-header {
        flex-direction: column;
    }
    
    .job-ref {
        margin-top: 5px;
    }
}
</style>
