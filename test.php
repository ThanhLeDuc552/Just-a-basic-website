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
        
        $responsibilities = [];
        $essential = [];
        $preferable = [];
        
        $job['Responsibilities'] = explode(";", $job['Responsibilities']);
        $job['Essential'] = explode(';', $job['Essential']);
        $job['Preferrable'] = explode(';', $job['Preferrable']);
    ?>
    <div class="container">
        <div class="header">
            <h1>Senior Software Developer</h1>
            <p>Etech - Redefining Intelligence</p>
        </div>
        
        <div class="job-info">
            <div class="main-content">
                <div class="section">
                    <h2>Job Description</h2>
                    <p>We are seeking a Junior Cyber Security Analyst to join CP SOC. If you want to break into Cyber Security, or seeking to move into a new role, while being part of a high performance team, which is committed to your professional growth, with a mission focused on defending critical national infrastructure, then this is the job for you.</p>
                </div>

                <div class="section">
                    <h2>Responsibilities</h2>
                    <ul>
                        <li>Analysis of security events from multiple sources including but not limited to events from the Security Information and Event Management tool, network intrusion systems and Host based Intrusion Prevention tools (AV, HIPS, Application Whitelisting).</li>
                        <li>Monitor and assess emerging threats and vulnerabilities to the environment and ensure those requiring action are addressed</li>
                        <li>Security Incident Management, advice and education and maintaining the currency and health of the deployed security tools</li>
                        <li>Provide technical administration support for security suite of software and hardware</li>
                    </ul>
                </div>

                <div class="section">
                    <h2>Requirements</h2>
                    <ul>
                        <li>Bachelor’s degree in Cybersecurity or related field</li>
                        <li>Familiarity with security tools and technologies, such as SIEM, IDS/IPS, antivirus software, cloud technologies and endpoint protection</li>
                        <li>3+ years of general IT experience which could include Security, Service Desk or Technical Support roles</li>
                        <li>Industry certifications (CISSP, CEH, CompTIA Security+)</li>
                        <li>Experience with forensic analysis and incident response</li>
                    </ul>
                </div>

                <div class="section">
                    <h2>Perks</h2>
                    <ul>
                        <li>$205k+ base salary + equity</li>
                        <li>Full remote flexibility</li>
                        <li>Premium health benefits</li>
                        <li>Tech stipend ($2,500)</li>
                    </ul>
                </div>

                <button class="apply-btn">Apply Now</button>
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
    <?php include_once("footer.inc")?>
</body>
</html>