<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Opportunity - Senior Software Developer</title>
    <link rel="stylesheet" href="https://use.typekit.net/ilv8ihq.css">
    <link rel="stylesheet" href="styles/job.css">
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <?php include_once("header.inc")?>
    <?php 
        session_start();
        include "settings.php";
        include "functions.inc";
        $job_ref = isset($_GET['job-ref']) ? sanitize_input($_GET['job-ref']) : '';
        
        if (empty($job_ref)) {
            header("Location: jobs.php");
            exit();
        }

        $sql = "SELECT * FROM jobs WHERE JobReferenceNumber = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $job_ref);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Check if job exists
        if (mysqli_num_rows($result) == 0) {
            header("Location: jobs.php");
            exit();
        }

        $job = mysqli_fetch_assoc($result); 
        
        $job['Responsibilities'] = explode(";", $job['Responsibilities']);
        $job['Essential'] = explode(';', $job['Essential']);
        $job['Preferrable'] = explode(';', $job['Preferrable']);

        echo "<div class='container'>
                <div class='header'>
                    <h1>" . $job['Title'] . "</h1>
                    <p>Etech - Redefining Intelligence</p>
                </div>
                <div class='job-info'>
                    <div class='main-content'>
                        <div class='section'>
                            <h2>Job Description</h2>
                            <p>" . $job['Description'] . "</p>
                        </div>
                        
                        <div class='section'>
                            <h2>Responsibilities</h2>
                            <ul>";
                            foreach ($job['Responsibilities'] as $resp) {
                                echo "<li>$resp</li>";
                            }
                            echo "</ul>
                        </div>
                        
                        <div class='section'>
                            <h2>Requirements</h2>
                                <ul>";
                                foreach ($job['Essential'] as $essential) {
                                    echo "<li>$essential</li>";
                                }
                                foreach ($job['Preferrable'] as $preferrable) {
                                    echo "<li>$preferrable</li>";
                                }
                                echo "</ul>
                        </div>
                        
                        <div class='section'>
                            <h2>Perks</h2>
                            <ul>
                                <li>" . $job['Salary'] . "</li>
                                <li>Full remote flexibility</li>
                                <li>Premium health benefits</li>
                                <li>Tech stipend (\$2,500)</li>
                            </ul>
                        </div>
                        
                        <a href=\"apply.php?job-ref=" . $job_ref . "\"<button class='apply-btn'>Apply Now</button></a>
                    </div>
                    
                    <div class=\"sidebar\">
                        <div class=\"sidebar-card\">
                            <div class=\"section\">
                                <h2>At a Glance</h2>
                                <div class=\"stats-grid\">
                                    <div class=\"stat-item\">
                                        <div class=\"stat-number\">5+</div>
                                        <p>Years Exp</p>
                                    </div>
                                    <div class=\"stat-item\">
                                        <div class=\"stat-number\">100%</div>
                                        <p>Remote</p>
                                    </div>
                                    <div class=\"stat-item\">
                                        <div class=\"stat-number\">20</div>
                                        <p>Team Size</p>
                                    </div>
                                    <div class=\"stat-item\">
                                        <div class=\"stat-number\">Q2</div>
                                        <p>Start Date</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class=\"sidebar-card\">
                            <div class=\"section\">
                                <h2>At a Glance</h2>
                                <div class=\"stats-grid\">
                                    <div class=\"stat-item\">
                                        <div class=\"stat-number\">5+</div>
                                        <p>Years Exp</p>
                                    </div>
                                    <div class=\"stat-item\">
                                        <div class=\"stat-number\">100%</div>
                                        <p>Remote</p>
                                    </div>
                                    <div class=\"stat-item\">
                                        <div class=\"stat-number\">20</div>
                                        <p>Team Size</p>
                                    </div>
                                    <div class=\"stat-item\">
                                        <div class=\"stat-number\">Q2</div>
                                        <p>Start Date</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        mysqli_close($conn);
    ?>
    <?php include_once("footer.inc")?>
</body>
</html>