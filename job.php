<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
		<title>
			Etech: Innovating the Future of Technology
		</title>
		<link rel="stylesheet" href="https://use.typekit.net/ilv8ihq.css">
        <link rel="stylesheet" href="styles/style.css"> <!-- General styling -->
		<link rel="stylesheet" href="styles/jobs.css"> <!-- Job details styling -->
	</head>
<body>
  <!-- Header -->
<?php include_once 'header.inc'; ?>
<main>
    <section class="job-details">
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
        <h1>Cybersecurity Analyst</h1>
        <p>📍 DaNang City</p>
        <p><strong>Salary: $205,000 - $300,000</strong></p>
        <p><strong>Reference:</strong>ICA123</p>
        <p><strong>Reports To:</strong> Chief Information Security Officer</p>
        <p><strong>Job Description:</strong> We are seeking a Junior Cyber Security Analyst to join CP SOC. If you want to break into Cyber Security, or seeking to move into a new role, while being part of a high performance team, which is committed to your professional growth, with a mission focused on defending critical national infrastructure, then this is the job for you.</p>
            <p><strong>Responsibilities:</strong></p>
            <ul>
                <li>Analysis of security events from multiple sources including but not limited to events from the Security Information and Event Management tool, network intrusion systems and Host based Intrusion Prevention tools (AV, HIPS, Application Whitelisting).</li>
                <li>Monitor and assess emerging threats and vulnerabilities to the environment and ensure those requiring action are addressed.</li>
                <li>Security Incident Management, advice and education and maintaining the currency and health of the deployed security tools.</li>
                <li>Provide technical administration support for security suite of software and hardware.</li>
            </ul>
            <p><strong>Requirements:</strong></p>
            <ul>
                <li><strong>Essential:</strong></li>
                <ul>
                    <li>Bachelor’s degree in Cybersecurity or related field.</li>
                    <li>Familiarity with security tools and technologies, such as SIEM, IDS/IPS, antivirus software, cloud technologies and endpoint protection.</li>
                    <li>3+ years of general IT experience which could include Security, Service Desk or Technical Support roles.</li>
                </ul>
                <li><strong>Preferable:</strong></li>
                <ul>
                    <li>Industry certifications (CISSP, CEH, CompTIA Security+).</li>
                    <li>Experience with forensic analysis and incident response.</li>
                </ul>
            </ul>
        </div>
    </section>
	<aside>
    <a href="apply.html" class="cta apply-btn">Apply Now</a>
	</aside>
</main>

</body>
</html>
  