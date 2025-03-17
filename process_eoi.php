<?php
session_start();
include_once 'settings.php';
include 'functions.inc';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log form submission
error_log("Form submitted: " . print_r($_POST, true));

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize errors array
    $errors = [];

    // Validate and sanitize required fields
    $job_ref = isset($_POST['job_ref']) ? sanitize_input($_POST['job_ref']) : '';
    $first_name = isset($_POST['first_name']) ? sanitize_input($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_input($_POST['last_name']) : '';
    $dob = isset($_POST['dob']) ? sanitize_input($_POST['dob']) : '';
    $gender = isset($_POST['gender']) ? sanitize_input($_POST['gender']) : '';
    $street_address = isset($_POST['street_address']) ? sanitize_input($_POST['street_address']) : '';
    $suburb = isset($_POST['suburb']) ? sanitize_input($_POST['suburb']) : '';
    $state = isset($_POST['state']) ? sanitize_input($_POST['state']) : '';
    $postcode = isset($_POST['postcode']) ? sanitize_input($_POST['postcode']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';

    // Skills (optional fields)
    $skill1 = isset($_POST['skill1']) ? sanitize_input($_POST['skill1']) : '';
    $skill2 = isset($_POST['skill2']) ? sanitize_input($_POST['skill2']) : '';
    $skill3 = isset($_POST['skill3']) ? sanitize_input($_POST['skill3']) : '';
    $skill4 = isset($_POST['skill4']) ? sanitize_input($_POST['skill4']) : '';
    $extra_skills = isset($_POST['extra_skills']) ?
        sanitize_input($_POST['extra_skills']) : '';

    // Field-specific validation
    if (empty($job_ref)) {
        $errors['job_ref'] = "Job reference number is required";
    } elseif (!preg_match("/^[A-Za-z0-9]{5}$/", $job_ref)) {
        $errors['job_ref'] = "Job reference must be 5 alphanumeric characters";
    }

    if (empty($first_name)) {
        $errors['first_name'] = "First name is required";
    } elseif (strlen($first_name) > 20) {
        $errors['first_name'] = "First name must be 20 characters or less";
    }

    if (empty($last_name)) {
        $errors['last_name'] = "Last name is required";
    } elseif (strlen($last_name) > 20) {
        $errors['last_name'] = "Last name must be 20 characters or less";
    }

    if (empty($dob)) {
        $errors['dob'] = "Date of birth is required";
    } else {
        // Validate age between 15 and 80
        $birthdate = new DateTime($dob);
        $today = new DateTime();
        $age = $birthdate->diff($today)->y;

        if ($age < 15 || $age > 80) {
            $errors['dob'] = "Age must be between 15 and 80 years";
        }
    }

    if (empty($gender)) {
        $errors['gender'] = "Gender is required";
    }

    if (empty($street_address)) {
        $errors['street_address'] = "Street address is required";
    } elseif (strlen($street_address) > 40) {
        $errors['street_address'] = "Street address must be 40 characters or less";
    }

    if (empty($suburb)) {
        $errors['suburb'] = "Suburb/Town is required";
    } elseif (strlen($suburb) > 40) {
        $errors['suburb'] = "Suburb/Town must be 40 characters or less";
    }

    if (empty($state)) {
        $errors['state'] = "State is required";
    }

    if (empty($postcode)) {
        $errors['postcode'] = "Postcode is required";
    } elseif (!preg_match("/^[0-9]{4}$/", $postcode)) {
        $errors['postcode'] = "Postcode must be 4 digits";
    } else {
        // Validate postcode against state
        $valid_postcode = false;

        switch ($state) {
            case 'VIC':
                $valid_postcode = (substr($postcode, 0, 1) == '3' || substr($postcode, 0, 1) == '8');
                break;
            case 'NSW':
                $valid_postcode = (substr($postcode, 0, 1) == '1' || substr($postcode, 0, 1) == '2');
                break;
            case 'QLD':
                $valid_postcode = (substr($postcode, 0, 1) == '4' || substr($postcode, 0, 1) == '9');
                break;
            case 'NT':
                $valid_postcode = (substr($postcode, 0, 1) == '0');
                break;
            case 'WA':
                $valid_postcode = (substr($postcode, 0, 1) == '6');
                break;
            case 'SA':
                $valid_postcode = (substr($postcode, 0, 1) == '5');
                break;
            case 'TAS':
                $valid_postcode = (substr($postcode, 0, 1) == '7');
                break;
            case 'ACT':
                $valid_postcode = (substr($postcode, 0, 1) == '0');
                break;
        }

        if (!$valid_postcode) {
            $errors['postcode'] = "Postcode does not match the selected state";
        }
    }

    if (empty($email)) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }

    if (empty($phone)) {
        $errors['phone'] = "Phone number is required";
    } elseif (!preg_match("/^[0-9\s]{8,12}$/", $phone)) {
        $errors['phone'] = "Phone number must be 8-12 digits or spaces";
    }

    $job_check_sql = "SELECT * FROM jobs WHERE JobReferenceNumber = ?";
    $stmt = mysqli_prepare($conn, $job_check_sql);
    mysqli_stmt_bind_param($stmt, "s", $job_ref);
    mysqli_stmt_execute($stmt);
    $job_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($job_result) == 0) {
        $errors['job_ref'] = "Invalid job reference number";
    } 

    // If there are validation errors
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST; // Store form data for repopulation

        error_log("Validation errors found: " . print_r($errors, true));

        header("Location: apply.php" . (!empty($job_ref) ? "?job=" . urlencode($job_ref) : ""));
        exit();
    }

    // If validation passes, insert into database
    try {
        $sql = "INSERT INTO eoi (JobReferenceNumber, FirstName, LastName, DOB, Gender, 
                StreetAddress, Suburb, State, Postcode, Email, PhoneNumber, 
                Skill1, Skill2, Skill3, Skill4, OtherSkills, Status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssssssssss",
            $job_ref, $first_name, $last_name, $dob, $gender,
            $street_address, $suburb, $state, $postcode, $email, $phone,
            $skill1, $skill2, $skill3, $skill4, $extra_skills);

        if (mysqli_stmt_execute($stmt)) {
            // Get the EOI number (auto-incremented ID)
            $eoi_number = mysqli_insert_id($conn);

            // Clear any existing session data that might interfere
            if (isset($_SESSION['form_errors'])) unset($_SESSION['form_errors']);
            if (isset($_SESSION['form_data'])) unset($_SESSION['form_data']);

            // Set session variables for confirmation page - CRITICAL FOR CONFIRMATION PAGE
            $_SESSION['application_success'] = true;
            $_SESSION['eoi_number'] = $eoi_number;

            // Log success and session state
            error_log("Application successful. EOI: $eoi_number");
            error_log("Session after success: " . print_r($_SESSION, true));

            // Close connection before redirect
            mysqli_close($conn);

            // Make sure we're not sending any output before the header
            if (ob_get_length()) ob_clean();

            // Redirect to confirmation page
            header("Location: application_confirmation.php");
            exit();
        } else {
            throw new Exception("Database error: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        error_log("Error in process_eoi.php: " . $e->getMessage());

        // Add the database error as a general error
        $errors['general'][] = "Database error: " . $e->getMessage();

        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;

        // Close connection before redirect
        mysqli_close($conn);

        header("Location: apply.php" . (!empty($job_ref) ? "?job=" . urlencode($job_ref) : ""));
        exit();
    }
} else {
    // Not a POST request, redirect to form
    header("Location: apply.php");
    exit();
}
?>
