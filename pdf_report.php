<?php
/**
 * PDF Report Generator
 * Creates detailed PDF reports of job applications and statistics
 * Uses FPDF library for PDF generation (server-side, no JavaScript)
 */

require_once 'settings.php';
require_once 'functions.inc';

// Ensure user is logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

// Check if FPDF is available, if not, we'll include a lightweight version
if (!class_exists('FPDF')) {
    require_once 'lib/fpdf/fpdf.php';
}

// Get report parameters
$report_type = isset($_GET['type']) ? sanitize_input($_GET['type']) : 'applications';
$date_range = isset($_GET['range']) ? sanitize_input($_GET['range']) : 'month';
$specific_job = isset($_GET['job']) ? sanitize_input($_GET['job']) : '';
$email = $_SESSION['user_email'];

// Date ranges
$end_date = date('Y-m-d'); // Today
switch ($date_range) {
    case 'week':
        $start_date = date('Y-m-d', strtotime('-1 week'));
        $range_text = 'Past Week';
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-1 month'));
        $range_text = 'Past Month';
        break;
    case 'quarter':
        $start_date = date('Y-m-d', strtotime('-3 months'));
        $range_text = 'Past Quarter';
        break;
    case 'year':
        $start_date = date('Y-m-d', strtotime('-1 year'));
        $range_text = 'Past Year';
        break;
    default:
        $start_date = date('Y-m-d', strtotime('-1 month'));
        $range_text = 'Past Month';
}

// Generate the report content depending on type
$title = '';
$data = [];

// For preview mode
$preview_mode = isset($_GET['preview']) && $_GET['preview'] === '1';

// If we're actually generating the PDF
if (!$preview_mode) {
    // Create PDF document
    class JobPortalPDF extends FPDF {
        // Page header
        function Header() {
            global $title;
            
            // Logo
            $this->Image('images/logo.png', 10, 6, 30);
            // Arial bold 15
            $this->SetFont('Arial', 'B', 15);
            // Move to the right
            $this->Cell(80);
            // Title
            $this->Cell(30, 10, $title, 0, 0, 'C');
            // Line break
            $this->Ln(20);
        }
        
        // Page footer
        function Footer() {
            // Position at 1.5 cm from bottom
            $this->SetY(-15);
            // Arial italic 8
            $this->SetFont('Arial', 'I', 8);
            // Page number
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
            // Generation date
            $this->Cell(0, 10, 'Generated on ' . date('Y-m-d H:i:s'), 0, 0, 'R');
        }
    }
    
    // Initialize PDF
    $pdf = new JobPortalPDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    
    // Function to add application details to PDF
    function add_application_details($pdf, $application) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Application #' . $application['EOInumber'], 0, 1);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(40, 8, 'Job Reference:', 0);
        $pdf->Cell(0, 8, $application['JobReferenceNumber'], 0, 1);
        
        $pdf->Cell(40, 8, 'Applicant:', 0);
        $pdf->Cell(0, 8, $application['FirstName'] . ' ' . $application['LastName'], 0, 1);
        
        $pdf->Cell(40, 8, 'Email:', 0);
        $pdf->Cell(0, 8, $application['Email'], 0, 1);
        
        $pdf->Cell(40, 8, 'Application Date:', 0);
        $pdf->Cell(0, 8, date('F j, Y', strtotime($application['ApplicationDate'])), 0, 1);
        
        $pdf->Cell(40, 8, 'Status:', 0);
        $pdf->Cell(0, 8, $application['Status'], 0, 1);
        
        // Add skills
        $pdf->Cell(40, 8, 'Skills:', 0);
        $skills = [];
        if (!empty($application['Skill1'])) $skills[] = $application['Skill1'];
        if (!empty($application['Skill2'])) $skills[] = $application['Skill2'];
        if (!empty($application['Skill3'])) $skills[] = $application['Skill3'];
        if (!empty($application['Skill4'])) $skills[] = $application['Skill4'];
        
        $pdf->MultiCell(0, 8, implode(", ", $skills), 0, 'L');
        
        // Add a line between applications
        $pdf->Ln(5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);
    }
    
    // Function to add a simple chart/graph
    function add_simple_chart($pdf, $data, $title) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, $title, 0, 1, 'C');
        
        $pdf->SetFont('Arial', '', 10);
        
        // Calculate total for percentages
        $total = array_sum(array_values($data));
        
        // Set starting coordinates
        $x = 40;
        $y = $pdf->GetY() + 10;
        $max_bar_width = 120;
        $bar_height = 8;
        $spacing = 16;
        
        foreach ($data as $label => $value) {
            // Skip if no data
            if ($value == 0) continue;
            
            // Calculate bar width based on percentage
            $percentage = ($value / $total) * 100;
            $bar_width = ($value / $total) * $max_bar_width;
            
            // Draw label
            $pdf->SetXY($x - 30, $y);
            $pdf->Cell(25, $bar_height, $label, 0, 0, 'R');
            
            // Draw bar
            $pdf->SetFillColor(55, 140, 230);
            $pdf->Rect($x, $y, $bar_width, $bar_height, 'F');
            
            // Draw value and percentage
            $pdf->SetXY($x + $max_bar_width + 5, $y);
            $pdf->Cell(30, $bar_height, $value . ' (' . round($percentage, 1) . '%)', 0, 0, 'L');
            
            // Move down for next bar
            $y += $spacing;
        }
        
        // Move below the chart
        $pdf->SetY($y + 10);
    }
    
    // Generate different types of reports
    switch ($report_type) {
        case 'applications':
            $title = 'Application Report - ' . $range_text;
            
            // Get all applications within date range
            $sql = "SELECT e.*, j.Title as JobTitle 
                   FROM eoi e 
                   JOIN jobs j ON e.JobReferenceNumber = j.JobReferenceNumber 
                   WHERE e.ApplicationDate BETWEEN ? AND ? ";
            
            // Add job filter if specified
            if (!empty($specific_job)) {
                $sql .= " AND e.JobReferenceNumber = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sss", $start_date, $end_date, $specific_job);
            } else {
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
            }
            
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            // Add summary
            $application_count = mysqli_num_rows($result);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Applications Summary: ' . $range_text, 0, 1);
            
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, 'Total Applications: ' . $application_count, 0, 1);
            
            // Add date range info
            $pdf->Cell(0, 10, 'Period: ' . date('F j, Y', strtotime($start_date)) . ' to ' . date('F j, Y', strtotime($end_date)), 0, 1);
            
            $pdf->Ln(5);
            
            // Get status breakdown
            $status_counts = ['New' => 0, 'Current' => 0, 'Final' => 0];
            
            // Job breakdown
            $job_counts = [];
            
            // Reset result pointer
            mysqli_data_seek($result, 0);
            
            while ($row = mysqli_fetch_assoc($result)) {
                // Count statuses
                $status_counts[$row['Status']]++;
                
                // Count job references
                $jobRef = $row['JobReferenceNumber'];
                $jobTitle = $row['JobTitle'];
                $job_key = "$jobRef: $jobTitle";
                
                if (!isset($job_counts[$job_key])) {
                    $job_counts[$job_key] = 0;
                }
                $job_counts[$job_key]++;
            }
            
            // Add status chart
            add_simple_chart($pdf, $status_counts, 'Applications by Status');
            
            $pdf->Ln(5);
            
            // Add job breakdown chart
            add_simple_chart($pdf, $job_counts, 'Applications by Job');
            
            $pdf->Ln(10);
            
            // Add application details
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Application Details', 0, 1);
            
            // Reset result pointer
            mysqli_data_seek($result, 0);
            
            while ($application = mysqli_fetch_assoc($result)) {
                // Check if we need a new page
                if ($pdf->GetY() > 250) {
                    $pdf->AddPage();
                }
                
                add_application_details($pdf, $application);
            }
            break;
            
        case 'activity':
            $title = 'User Activity Report - ' . $range_text;
            
            // Get job view statistics
            $sql = "SELECT jv.*, j.Title as JobTitle
                   FROM job_views jv
                   JOIN jobs j ON jv.JobReferenceNumber = j.JobReferenceNumber
                   WHERE jv.ViewTimestamp BETWEEN ? AND ?";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            // Activity summary
            $view_count = mysqli_num_rows($result);
            
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Activity Summary: ' . $range_text, 0, 1);
            
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, 'Total Job Views: ' . $view_count, 0, 1);
            
            // Add date range info
            $pdf->Cell(0, 10, 'Period: ' . date('F j, Y', strtotime($start_date)) . ' to ' . date('F j, Y', strtotime($end_date)), 0, 1);
            
            $pdf->Ln(5);
            
            // Prepare data for charts
            $job_view_counts = [];
            $device_counts = ['desktop' => 0, 'mobile' => 0, 'tablet' => 0];
            $hourly_views = array_fill(0, 24, 0);
            
            while ($row = mysqli_fetch_assoc($result)) {
                // Job views
                $jobRef = $row['JobReferenceNumber'];
                $jobTitle = $row['JobTitle'];
                $job_key = "$jobRef: $jobTitle";
                
                if (!isset($job_view_counts[$job_key])) {
                    $job_view_counts[$job_key] = 0;
                }
                $job_view_counts[$job_key]++;
                
                // Device types
                $device = $row['DeviceType'];
                if (isset($device_counts[$device])) {
                    $device_counts[$device]++;
                }
                
                // Hour of day (for hourly distribution)
                $hour = (int)date('G', strtotime($row['ViewTimestamp']));
                $hourly_views[$hour]++;
            }
            
            // Sort job views by count (descending)
            arsort($job_view_counts);
            
            // Only show top 10 jobs
            $top_jobs = array_slice($job_view_counts, 0, 10, true);
            
            // Add job views chart
            add_simple_chart($pdf, $top_jobs, 'Most Viewed Jobs');
            
            $pdf->Ln(5);
            
            // Add device type chart
            add_simple_chart($pdf, $device_counts, 'Job Views by Device Type');
            
            $pdf->Ln(5);
            
            // Create hourly distribution table
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Hourly View Distribution', 0, 1, 'C');
            
            $pdf->SetFont('Arial', 'B', 10);
            
            // Row for hours 0-11 (AM)
            $pdf->Cell(20, 8, 'Hour', 1, 0, 'C');
            for ($i = 0; $i < 12; $i++) {
                $hour_display = $i == 0 ? '12 AM' : $i . ' AM';
                $pdf->Cell(13, 8, $hour_display, 1, 0, 'C');
            }
            $pdf->Ln();
            
            // Row for counts
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(20, 8, 'Views', 1, 0, 'C');
            for ($i = 0; $i < 12; $i++) {
                $pdf->Cell(13, 8, $hourly_views[$i], 1, 0, 'C');
            }
            $pdf->Ln();
            
            // Row for hours 12-23 (PM)
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(20, 8, 'Hour', 1, 0, 'C');
            for ($i = 12; $i < 24; $i++) {
                $hour_display = $i == 12 ? '12 PM' : ($i - 12) . ' PM';
                $pdf->Cell(13, 8, $hour_display, 1, 0, 'C');
            }
            $pdf->Ln();
            
            // Row for counts
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(20, 8, 'Views', 1, 0, 'C');
            for ($i = 12; $i < 24; $i++) {
                $pdf->Cell(13, 8, $hourly_views[$i], 1, 0, 'C');
            }
            break;
    }
    
    // Output PDF
    $pdf_filename = 'report_' . $report_type . '_' . date('Ymd') . '.pdf';
    $pdf->Output('D', $pdf_filename);
    exit;
} else {
    // Just show a preview of what will be in the report
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Report Preview</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 20px;
                color: #333;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: #fff;
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #0078d4;
                margin-top: 0;
            }
            .preview-box {
                background: #f9f9f9;
                border: 1px solid #ddd;
                padding: 15px;
                margin: 15px 0;
                border-radius: 5px;
            }
            .btn {
                display: inline-block;
                background: #0078d4;
                color: #fff;
                padding: 10px 15px;
                text-decoration: none;
                border-radius: 4px;
                margin-top: 15px;
            }
            .btn:hover {
                background: #005a9e;
            }
            .options {
                margin: 20px 0;
                padding: 15px;
                background: #f5f5f5;
                border-radius: 5px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            select, input {
                width: 100%;
                padding: 8px;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Report Preview</h1>
            
            <div class="options">
                <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <label for="type">Report Type:</label>
                    <select id="type" name="type">
                        <option value="applications" <?php echo $report_type == 'applications' ? 'selected' : ''; ?>>Job Applications</option>
                        <option value="activity" <?php echo $report_type == 'activity' ? 'selected' : ''; ?>>User Activity</option>
                    </select>
                    
                    <label for="range">Date Range:</label>
                    <select id="range" name="range">
                        <option value="week" <?php echo $date_range == 'week' ? 'selected' : ''; ?>>Past Week</option>
                        <option value="month" <?php echo $date_range == 'month' ? 'selected' : ''; ?>>Past Month</option>
                        <option value="quarter" <?php echo $date_range == 'quarter' ? 'selected' : ''; ?>>Past Quarter</option>
                        <option value="year" <?php echo $date_range == 'year' ? 'selected' : ''; ?>>Past Year</option>
                    </select>
                    
                    <label for="job">Specific Job (optional):</label>
                    <select id="job" name="job">
                        <option value="">All Jobs</option>
                        <?php
                        $jobs_sql = "SELECT JobReferenceNumber, Title FROM jobs ORDER BY Title";
                        $jobs_result = mysqli_query($conn, $jobs_sql);
                        while ($job = mysqli_fetch_assoc($jobs_result)) {
                            $selected = ($specific_job == $job['JobReferenceNumber']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($job['JobReferenceNumber']) . '" ' . $selected . '>' . 
                                htmlspecialchars($job['JobReferenceNumber'] . ': ' . $job['Title']) . '</option>';
                        }
                        ?>
                    </select>
                    
                    <input type="hidden" name="preview" value="1">
                    <button type="submit" class="btn">Update Preview</button>
                </form>
            </div>
            
            <div class="preview-box">
                <h2>This Report Will Include:</h2>
                
                <?php if ($report_type == 'applications'): ?>
                    <h3>Applications Report (<?php echo $range_text; ?>)</h3>
                    <ul>
                        <li>Summary of all applications from <?php echo date('F j, Y', strtotime($start_date)); ?> to <?php echo date('F j, Y', strtotime($end_date)); ?></li>
                        <li>Total application count with breakdown by status</li>
                        <li>Chart showing applications by job</li>
                        <li>Detailed list of all applications with applicant information</li>
                    </ul>
                <?php elseif ($report_type == 'activity'): ?>
                    <h3>User Activity Report (<?php echo $range_text; ?>)</h3>
                    <ul>
                        <li>Summary of all job views from <?php echo date('F j, Y', strtotime($start_date)); ?> to <?php echo date('F j, Y', strtotime($end_date)); ?></li>
                        <li>Chart of most viewed jobs</li>
                        <li>Breakdown of views by device type (desktop, mobile, tablet)</li>
                        <li>Hourly distribution of job views</li>
                    </ul>
                <?php endif; ?>
            </div>
            
            <p>Click the button below to generate and download the full PDF report:</p>
            
            <a href="<?php 
                $params = $_GET;
                unset($params['preview']);
                echo $_SERVER['PHP_SELF'] . '?' . http_build_query($params); 
            ?>" class="btn">Generate PDF Report</a>
        </div>
    </body>
    </html>
    <?php
}
?>
