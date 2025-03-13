<?php
require_once 'settings.php';

// Drop existing tables
$sql_drop_eoi = "DROP TABLE IF EXISTS eoi";
$sql_drop_jobs = "DROP TABLE IF EXISTS jobs";
$sql_drop_managers = "DROP TABLE IF EXISTS managers";
$sql_drop_login_attempts = "DROP TABLE IF EXISTS login_attempts";
if (mysqli_query($conn, $sql_drop_eoi)) {
    echo "EOI table deleted successfully<br>";
} else {
    echo "Error deleting EOI table: " . mysqli_error($conn) . "<br>";
}
if (mysqli_query($conn, $sql_drop_jobs)) {
    echo "Jobs table deleted successfully<br>";
} else {
    echo "Error deleting Jobs table: " . mysqli_error($conn) . "<br>";
}
if (mysqli_query($conn, $sql_drop_managers)) {
    echo "Managers table deleted successfully<br>";
} else {
    echo "Error deleting Managers table: " . mysqli_error($conn) . "<br>";
}
if (mysqli_query($conn, $sql_drop_login_attempts)) {
    echo "Login attempts table deleted successfully<br>";
} else {
    echo "Error deleting Login table: " . mysqli_error($conn) . "<br>";
}

// Create EOI table
$sql_eoi = "CREATE TABLE IF NOT EXISTS eoi (
  EOInumber INT AUTO_INCREMENT PRIMARY KEY,
  JobReferenceNumber VARCHAR(5) NOT NULL,
  FirstName VARCHAR(20) NOT NULL,
  LastName VARCHAR(20) NOT NULL,
  DOB DATE NOT NULL,
  Gender VARCHAR(10) NOT NULL,
  StreetAddress VARCHAR(40) NOT NULL,
  Suburb VARCHAR(40) NOT NULL,
  State ENUM('VIC','NSW','QLD','NT','WA','SA','TAS','ACT') NOT NULL,
  Postcode VARCHAR(4) NOT NULL,
  Email VARCHAR(255) NOT NULL,
  PhoneNumber VARCHAR(12) NOT NULL,
  Skill1 VARCHAR(255),
  Skill2 VARCHAR(255),
  Skill3 VARCHAR(255),
  Skill4 VARCHAR(255),
  OtherSkills TEXT,
  Status ENUM('New','Current','Final') DEFAULT 'New'
)";

// Create Jobs table
$sql_jobs = "CREATE TABLE IF NOT EXISTS jobs (
  JobReferenceNumber VARCHAR(6) PRIMARY KEY,
  Title VARCHAR(100) NOT NULL,
  Description LONGTEXT NOT NULL,
  Responsibilities LONGTEXT NOT NULL, /* ; delimited */
  Essential LONGTEXT NOT NULL, /* ; delimited */
  Preferrable LONGTEXT NOT NULL, /* ; delimited */
  Position VARCHAR(50) NOT NULL,
  Location VARCHAR(100) NOT NULL,
  Salary VARCHAR(50)
)";

// Create Managers table
$sql_managers = "CREATE TABLE IF NOT EXISTS managers (
  ManagerID INT AUTO_INCREMENT PRIMARY KEY,
  Username VARCHAR(50) UNIQUE NOT NULL,
  Password VARCHAR(255) NOT NULL,
  FirstName VARCHAR(50) NOT NULL,
  LastName VARCHAR(50) NOT NULL,
  Email VARCHAR(255) NOT NULL
)";

// Create Login Attempts table
$sql_login_attempts = "CREATE TABLE IF NOT EXISTS login_attempts (
  AttemptID INT AUTO_INCREMENT PRIMARY KEY,
  Username VARCHAR(50) NOT NULL,
  AttemptTime DATETIME NOT NULL,
  IPAddress VARCHAR(45) NOT NULL,
  Success BOOLEAN NOT NULL
)";

// Execute queries
if (mysqli_query($conn, $sql_eoi)) {
    echo "EOI table created successfully<br>";
} else {
    echo "Error creating EOI table: " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_jobs)) {
    echo "Jobs table created successfully<br>";
} else {
    echo "Error creating Jobs table: " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_managers)) {
    echo "Managers table created successfully<br>";
} else {
    echo "Error creating Managers table: " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_login_attempts)) {
    echo "Login Attempts table created successfully<br>";
} else {
    echo "Error creating Login Attempts table: " . mysqli_error($conn) . "<br>";
}

// Insert sample jobs data
$sample_jobs = [
    ['ICA123', 'Cybersecurity Analyst', 
    'We are seeking a Junior Cyber Security Analyst to join CP SOC. If you want to break into Cyber Security, or seeking to move into a new role, 
    while being part of a high performance team, which is committed to your professional growth, with a mission focused on defending critical national infrastructure, 
    then this is the job for you.', 
    'Analysis of security events from multiple sources including but not limited to events from the Security Information and Event Management tool, network intrusion systems and Host based Intrusion Prevention tools (AV, HIPS, Application Whitelisting).;
    Monitor and assess emerging threats and vulnerabilities to the environment and ensure those requiring action are addressed.;
    Security Incident Management, advice and education and maintaining the currency and health of the deployed security tools.;
    Provide technical administration support for security suite of software and hardware.', 
    'Bachelor’s degree in Cybersecurity or related field.;
    Familiarity with security tools and technologies, such as SIEM, IDS/IPS, antivirus software, cloud technologies and endpoint protection.;
    3+ years of general IT experience which could include Security, Service Desk or Technical Support roles.', 
    'Industry certifications (CISSP, CEH, CompTIA Security+).;
    Experience with forensic analysis and incident response.', 
    'Full-time', 'Da Nang City', '$205.000 - $300.000'],
    ['SE567', 'Software Engineer', 
    'We are looking for you to join our team as a Senior Principal Software Engineer based out of Ho Chi Minh city. 
    As a Software Engineer at ETech you will have a challenging and rewarding opportunity to be a part of our Enterprise-wide digital transformation. 
    Through the use of Model-based Engineering, DevSecOps and Agile practices we continue to evolve how we deliver critical national defense products and 
    capabilities for the warfighter. Our success is grounded in our ability to embrace change, move quickly and continuously drive innovation. 
    The successful candidate will be collaborative, open, transparent, and team-oriented with a focus on team empowerment & shared responsibility, flexibility, 
    continuous learning, and a culture of automation.', 
    'Develop, test, and maintain software applications.;
    Collaborate with our customer, internal NG sites and other engineering disciplines.;
    Debug and resolve software issues.;Design and implement software for quality, robustness, and scale.', 
    'BS degree in a STEM related field (Science, Technology, Engineering and Mathematics) with 8+ years of related experience, 
    Master\'s Degree in a STEM related field with 6+ years of related experience, or PhD in a STEM related field with 3+ years related experience. 
    An additional 4 years of experience can be considered in lieu of degree.;
    Must have experience with C#.;
    Design Patterns and Tech Stack experience with one or more of the following tools: Dependency Injection, MEF, REST API, MVVM, WPF, Unit Test, C#, React OR Multithread applications;
    Experience working in an Agile environment.', 
    '.NET Core, Java, JavaScript, ReactJS, Reduc, CSS;
    Agile Methodologies and Atlassian Tool Suite (Git, Jira, Bitbucket, Confluence);
    Docker, Containers, Terraform, OpenShift, Kubernetes, HELM Charts',  
    'Full-time', 'Ho Chi Minh City', '$100.500 - $250.000'],
];

foreach ($sample_jobs as $job) {
    $sql = "INSERT IGNORE INTO jobs (JobReferenceNumber, Title, Description, Responsibilities, Essential, Preferrable, Position, Location, Salary) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssss", $job[0], $job[1], $job[2], $job[3], $job[4], $job[5], $job[6], $job[7], $job[8]);
    mysqli_stmt_execute($stmt);
}

echo "Sample jobs data inserted successfully<br>";

// Create a default admin user
$default_username = "admin";
$default_password = password_hash("Admin@123", PASSWORD_DEFAULT);
$default_fname = "System";
$default_lname = "Administrator";
$default_email = "admin@example.com";

$sql = "INSERT IGNORE INTO managers (Username, Password, FirstName, LastName, Email) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssss", $default_username, $default_password, $default_fname, $default_lname, $default_email);
mysqli_stmt_execute($stmt);

echo "Default admin user created successfully<br>";

mysqli_close($conn);
?>

