
    <?php 
    $title = 'About Job';
    $style = 'job.css';
    include_once 'initial_page_settings.inc'
    ?>
    <?php 
        include_once "header.inc";
        include "settings.php";
        include "functions.inc";
        $job_ref = isset($_GET['job_ref']) ? sanitize_input($_GET['job_ref']) : '';
        
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
    ?>
    <div class='container'>
        <div class='job-banner'>
            <h1><?php echo $job['Title']; ?></h1>
            <p>Etech - Redefining Intelligence</p>
        </div>
        <div class='job-content'>
            <div class='main-content'>
                <div class='section'>
                    <h2>Job Description</h2>
                    <p><?php echo $job['Description']; ?></p>
                </div>
                
                <div class='section'>
                    <h2>Responsibilities</h2>
                    <ul>
                    <?php 
                    foreach ($job['Responsibilities'] as $resp) {
                        echo "<li>$resp</li>";
                    }
                    ?>
                    </ul>
                </div>
                
                <div class='section'>
                    <h2>Requirements</h2>
                    <ul>
                    <?php 
                    foreach ($job['Essential'] as $essential) {
                        echo "<li>$essential</li>";
                    }
                    foreach ($job['Preferrable'] as $preferrable) {
                        echo "<li>$preferrable</li>";
                    }
                    ?>
                    </ul>
                </div>
                
                <div class='section'>
                    <h2>Perks</h2>
                    <ul>
                        <li><?php echo $job['Salary']; ?></li>
                        <li>Full remote flexibility</li>
                        <li>Premium health benefits</li>
                        <li>Tech stipend ($2,500)</li>
                    </ul>
                </div>
                <a href="<?php echo isset($_SESSION["manager_id"]) ? "manage.php" : "apply.php?job_ref=" . $job_ref; ?>"><button class='apply-btn'>Apply Now</button></a>
            </div>
            <div class="sidebar">
                <div class="sidebar-card">
                    <div class="section">
                        <h2>At a Glance</h2>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-number">5+</div>
                                <p>Years Exp</p>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">100%</div>
                                <p>Remote</p>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">20</div>
                                <p>Team Size</p>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">Q2</div>
                                <p>Start Date</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php 
    mysqli_close($conn);
    include_once("footer.inc")
    ?>