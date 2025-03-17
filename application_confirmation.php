<?php
session_start();
$page_title = "Application Confirmation";

// Debug information (remove in production)
error_log("Confirmation page loaded. Session: " . print_r($_SESSION, true));

// FIXED: There was a typo in the session variable check
// Old (with typo): if (!isset($_SESSION['applicatapplication_confirmation.phpion_success']) || !$_SESSION['application_success'])
// New (corrected):
if (!isset($_SESSION['application_success']) || $_SESSION['application_success'] !== true) {
    error_log("Redirecting from confirmation page - No success flag in session");
    header("Location: index.php");
    exit();
}

$eoi_number = isset($_SESSION['eoi_number']) ? $_SESSION['eoi_number'] : 'Unknown';

include 'header.inc';
include 'menu.inc';
?>

<main>
    <section class="confirmation">
        <div class="confirmation-box">
            <div class="confirmation-icon">
                <img src="images/success-icon.png" alt="Success">
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
                <a href="index.php" class="btn btn-primary">Return to Home</a>
                <a href="jobs.php" class="btn btn-secondary">Browse More Jobs</a>
            </div>
        </div>
    </section>
</main>

<style>
    /* Enhanced styling for confirmation page */
    .confirmation {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .confirmation-box {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        text-align: center;
    }

    .confirmation-icon {
        margin-bottom: 1.5rem;
    }

    .confirmation-icon img {
        width: 80px;
        height: 80px;
    }

    .confirmation-details {
        background-color: #e8f4fd;
        border-left: 4px solid #3498db;
        padding: 1rem;
        margin: 1.5rem 0;
        text-align: left;
        border-radius: 4px;
    }

    .confirmation h2 {
        color: #2ecc71;
        margin-bottom: 1rem;
    }

    .confirmation ol {
        text-align: left;
        padding-left: 1.5rem;
    }

    .confirmation ol li {
        margin-bottom: 0.5rem;
    }

    .confirmation-actions {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
        gap: 1rem;
    }

    .btn {
        display: inline-block;
        padding: 0.6rem 1.2rem;
        text-decoration: none;
        border-radius: 4px;
        font-weight: bold;
        transition: all 0.3s;
    }

    .btn-primary {
        background-color: #3498db;
        color: white;
    }

    .btn-primary:hover {
        background-color: #2980b9;
    }

    .btn-secondary {
        background-color: #ecf0f1;
        color: #34495e;
    }

    .btn-secondary:hover {
        background-color: #bdc3c7;
    }
</style>

<?php
// Clear session variables after displaying the page
unset($_SESSION['application_success']);
unset($_SESSION['eoi_number']);

include 'footer.inc';
?>
