<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/confirmation.css">
    <link rel="stylesheet" href="https://use.typekit.net/ilv8ihq.css">
</head>
<body>
<?php
session_start();
// Debug information (remove in production)
// FIXED: There was a typo in the session variable check
// Old (with typo): if (!isset($_SESSION['applicatapplication_confirmation.phpion_success']) || !$_SESSION['application_success'])
// New (corrected):
if (!isset($_SESSION['application_success']) || $_SESSION['application_success'] !== true) {
    header("Location: index.php");
    exit();
}

$eoi_number = isset($_SESSION['eoi_number']) ? $_SESSION['eoi_number'] : 'Unknown';
?>

<main>
    <section class="confirmation">
        <div class="confirmation-box">
            <div class="confirmation-icon">
                <img src="styles/images/success-icon.svg" alt="Success">
            </div>
            <h2>Application Submitted Successfully</h2>

            <p>Thank you for your interest in joining our team. Your application has been received and is now being processed.</p>

            <div class="confirmation-details">
                <p><strong>Application Reference Number:</strong> EOI-<?php echo htmlspecialchars($eoi_number); ?></p>
                <p>Please keep this reference number for future inquiries about your application status.</p>
            </div>

            <p>What happens next?</p>
            <ol>
                <li>Our HR team will review your application</li>
                <li>If your qualifications match our requirements, we'll contact you for an interview</li>
                <li>You can check your application status by contacting our HR department with your reference number</li>
            </ol>

            <div class="confirmation-actions">
                <a href="index.php" class="btn btn-general">Return to Home</a>
                <a href="jobs.php" class="btn btn-general">Browse More Jobs</a>
            </div>
        </div>
    </section>
</main>
<?php
// Clear session variables after displaying the page
unset($_SESSION['application_success']);
unset($_SESSION['eoi_number']);
?>

</body>
</html>