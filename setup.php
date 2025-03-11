<?php
require_once 'settings.php';

// Drop existing tables
$sql_drop_eoi = "DROP TABLE IF EXISTS eoi";
$sql_drop_jobs = "DROP TABLE IF EXISTS jobs";
$sql_drop_managers = "DROP TABLE IF EXISTS managers";
$sql_drop_login_attempts = "DROP TABLE IF EXISTS login_attempts";
$sql_drop_user_preferences = "DROP TABLE IF EXISTS user_preferences";
$sql_drop_job_views = "DROP TABLE IF EXISTS job_views";
$sql_drop_application_timeline = "DROP TABLE IF EXISTS application_timeline";
$sql_drop_resume_data = "DROP TABLE IF EXISTS resume_data";
$sql_drop_skill_keywords = "DROP TABLE IF EXISTS skill_keywords";
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
if (mysqli_query($conn, $sql_drop_user_preferences)) {
    echo "User preferences table deleted successfully<br>";
} else {
    echo "Error deleting User preferences table: " . mysqli_error($conn) . "<br>";
}
if (mysqli_query($conn, $sql_drop_job_views)) {
    echo "Job views table deleted successfully<br>";
} else {
    echo "Error deleting Job views table: " . mysqli_error($conn) . "<br>";
}
if (mysqli_query($conn, $sql_drop_application_timeline)) {
    echo "Application timeline table deleted successfully<br>";
} else {
    echo "Error deleting Application timeline table: " . mysqli_error($conn) . "<br>";
}
if (mysqli_query($conn, $sql_drop_resume_data)) {
    echo "Resume data table deleted successfully<br>";
} else {
    echo "Error deleting Resume data table: " . mysqli_error($conn) . "<br>";
}
if (mysqli_query($conn, $sql_drop_skill_keywords)) {
    echo "Skill keywords table deleted successfully<br>";
} else {
    echo "Error deleting Skill keywords table: " . mysqli_error($conn) . "<br>";
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
  Status ENUM('New','Current','Final') DEFAULT 'New',
  ApplicationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ResumeFile VARCHAR(255),
  MatchScore INT DEFAULT 0,
  SessionID VARCHAR(255)
)";

// Create Jobs table
$sql_jobs = "CREATE TABLE IF NOT EXISTS jobs (
  JobReferenceNumber VARCHAR(5) PRIMARY KEY,
  Title VARCHAR(100) NOT NULL,
  Description TEXT NOT NULL,
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
  Email VARCHAR(255) NOT NULL,
  LastLogin DATETIME,
  AccountLocked BOOLEAN DEFAULT FALSE,
  LockUntil DATETIME
)";

// Create Login Attempts table
$sql_login_attempts = "CREATE TABLE IF NOT EXISTS login_attempts (
  AttemptID INT AUTO_INCREMENT PRIMARY KEY,
  Username VARCHAR(50) NOT NULL,
  AttemptTime DATETIME NOT NULL,
  IPAddress VARCHAR(45) NOT NULL,
  Success BOOLEAN NOT NULL
)";

// Create User Preferences table
$sql_user_preferences = "CREATE TABLE IF NOT EXISTS user_preferences (
  PreferenceID INT AUTO_INCREMENT PRIMARY KEY,
  UserEmail VARCHAR(255) NOT NULL,
  SessionID VARCHAR(255) NOT NULL,
  PreferredLocation VARCHAR(100),
  PreferredSalary VARCHAR(50),
  PreferredJobType VARCHAR(50),
  PreferredSkills TEXT,
  DashboardLayout TEXT,
  Theme VARCHAR(20) DEFAULT 'light',
  LastUpdated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (UserEmail)
)";

// Create Job Views table for tracking user behavior
$sql_job_views = "CREATE TABLE IF NOT EXISTS job_views (
  ViewID INT AUTO_INCREMENT PRIMARY KEY,
  JobReferenceNumber VARCHAR(5) NOT NULL,
  UserEmail VARCHAR(255),
  SessionID VARCHAR(255) NOT NULL,
  ViewTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ViewDuration INT DEFAULT 0,
  DeviceType VARCHAR(50),
  INDEX (JobReferenceNumber),
  INDEX (SessionID)
)";

// Create Application Timeline table
$sql_application_timeline = "CREATE TABLE IF NOT EXISTS application_timeline (
  TimelineID INT AUTO_INCREMENT PRIMARY KEY,
  EOInumber INT NOT NULL,
  Stage ENUM('Applied', 'Resume Reviewed', 'Interview Scheduled', 'Interview Completed', 'Offer Extended', 'Hired', 'Rejected') NOT NULL,
  StageTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  Notes TEXT,
  NextStepDate DATE,
  FOREIGN KEY (EOInumber) REFERENCES eoi(EOInumber) ON DELETE CASCADE
)";

// Create Resume Data table
$sql_resume_data = "CREATE TABLE IF NOT EXISTS resume_data (
  ResumeID INT AUTO_INCREMENT PRIMARY KEY,
  EOInumber INT NOT NULL,
  ParsedSkills TEXT,
  Education TEXT,
  Experience TEXT,
  ParseTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (EOInumber) REFERENCES eoi(EOInumber) ON DELETE CASCADE
)";

// Create Skill Keywords table
$sql_skill_keywords = "CREATE TABLE IF NOT EXISTS skill_keywords (
  KeywordID INT AUTO_INCREMENT PRIMARY KEY,
  JobReferenceNumber VARCHAR(5) NOT NULL,
  Keyword VARCHAR(50) NOT NULL,
  Weight INT DEFAULT 1,
  FOREIGN KEY (JobReferenceNumber) REFERENCES jobs(JobReferenceNumber) ON DELETE CASCADE,
  UNIQUE KEY (JobReferenceNumber, Keyword)
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

if (mysqli_query($conn, $sql_user_preferences)) {
    echo "User Preferences table created successfully<br>";
} else {
    echo "Error creating User Preferences table: " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_job_views)) {
    echo "Job Views table created successfully<br>";
} else {
    echo "Error creating Job Views table: " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_application_timeline)) {
    echo "Application Timeline table created successfully<br>";
} else {
    echo "Error creating Application Timeline table: " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_resume_data)) {
    echo "Resume Data table created successfully<br>";
} else {
    echo "Error creating Resume Data table: " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_skill_keywords)) {
    echo "Skill Keywords table created successfully<br>";
} else {
    echo "Error creating Skill Keywords table: " . mysqli_error($conn) . "<br>";
}

// Insert sample jobs data
$sample_jobs = [
    ['ICA123', 'Cybersecurity Analyst', 'Experienced web developer needed for complex projects...', 'Full-time', 'Da Nang City', '$205.000 - $300.000'],
    ['SE567', 'Software Engineer', 'Looking for a skilled DBA to manage our database systems...', 'Full-time', 'Ho Chi Minh City', '$100.500 - $250.000'],
    ['AI647', 'AI Engineer', 'Creative designer needed to enhance user experiences...', 'Contract', 'Ha Noi City', '$130.000 - $203.600']
];

foreach ($sample_jobs as $job) {
    $sql = "INSERT IGNORE INTO jobs (JobReferenceNumber, Title, Description, Position, Location, Salary) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssss", $job[0], $job[1], $job[2], $job[3], $job[4], $job[5]);
    mysqli_stmt_execute($stmt);
}

echo "Sample jobs data inserted successfully<br>";

// Insert sample skill keywords
$skill_keywords = [
    ['ICA123', 'Cybersecurity', 5],
    ['ICA123', 'Network Security', 4],
    ['ICA123', 'Penetration Testing', 4],
    ['ICA123', 'Incident Response', 3],
    ['ICA123', 'Security Tools', 3],
    ['SE567', 'JavaScript', 5],
    ['SE567', 'PHP', 5],
    ['SE567', 'MySQL', 4],
    ['SE567', 'HTML', 3],
    ['SE567', 'CSS', 3],
    ['AI647', 'Machine Learning', 5],
    ['AI647', 'Python', 5],
    ['AI647', 'TensorFlow', 4],
    ['AI647', 'Neural Networks', 4],
    ['AI647', 'Computer Vision', 3]
];

foreach ($skill_keywords as $keyword) {
    $sql = "INSERT IGNORE INTO skill_keywords (JobReferenceNumber, Keyword, Weight) 
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $keyword[0], $keyword[1], $keyword[2]);
    mysqli_stmt_execute($stmt);
}

echo "Sample skill keywords inserted successfully<br>";

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
