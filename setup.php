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

