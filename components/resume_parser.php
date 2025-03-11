<?php
/**
 * Resume Parser Component
 * Parses uploaded resume to extract skills and match with job requirements
 */

require_once 'settings.php';
require_once 'functions.inc';

/**
 * Process uploaded resume and extract text
 * 
 * @param array $file Uploaded file data ($_FILES['resume'])
 * @return string|false Extracted text content or false on failure
 */
function extract_resume_text($file) {
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Check file type
    $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }

    // Extract text based on file type
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($file_ext === 'txt') {
        // Plain text file
        return file_get_contents($file['tmp_name']);
    } elseif ($file_ext === 'pdf') {
        // Simple text extraction from PDF - not as robust as a real parser
        // but works for basic text extraction
        $content = '';
        
        // Try to use shell command if available
        if (function_exists('shell_exec')) {
            // Using pdftotext if installed
            $content = @shell_exec('pdftotext ' . escapeshellarg($file['tmp_name']) . ' -');
        }
        
        // Fall back to basic PHP-based extraction
        if (empty($content) && extension_loaded('imagick')) {
            try {
                $pdf = new Imagick();
                $pdf->readImage($file['tmp_name']);
                $pdf->setResolution(300, 300);
                $pages = $pdf->getNumberImages();
                
                for ($i = 0; $i < $pages; $i++) {
                    $pdf->setIteratorIndex($i);
                    $content .= $pdf->getImageProperty('text');
                }
                
                $pdf->clear();
                $pdf->destroy();
            } catch (Exception $e) {
                // Fallback to simple text extraction
                $content = '';
            }
        }
        
        // If all methods fail, return simple placeholder message
        if (empty($content)) {
            $content = "Resume uploaded but text extraction failed. Skills must be manually entered.";
        }
        
        return $content;
    } elseif ($file_ext === 'doc' || $file_ext === 'docx') {
        // Simple text extraction from Word - not as robust as a real parser
        $content = '';
        
        // Try to use shell command if available
        if (function_exists('shell_exec') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            // Using antiword for DOC files if installed
            if ($file_ext === 'doc') {
                $content = @shell_exec('antiword ' . escapeshellarg($file['tmp_name']));
            } else {
                // Using docx2txt for DOCX files if installed
                $content = @shell_exec('docx2txt ' . escapeshellarg($file['tmp_name']) . ' -');
            }
        }
        
        // If extraction fails, return placeholder
        if (empty($content)) {
            $content = "Resume uploaded but text extraction failed. Skills must be manually entered.";
        }
        
        return $content;
    }
    
    return false;
}

/**
 * Process resume and return match score
 * 
 * @param array $file Uploaded file data
 * @param string $job_ref Job reference number
 * @param object $conn Database connection
 * @return array Result data with status, score, and matched skills
 */
function process_resume($file, $job_ref, $conn) {
    $result = [
        'status' => false,
        'score' => 0,
        'matched_skills' => [],
        'message' => ''
    ];
    
    // Extract text from resume
    $resume_text = extract_resume_text($file);
    
    if ($resume_text === false) {
        $result['message'] = 'Failed to process resume file. Please check file format.';
        return $result;
    }
    
    // Parse skills from resume text
    $extracted_skills = parse_resume_skills($resume_text, $conn);
    
    if (empty($extracted_skills)) {
        $result['message'] = 'No skills found in resume. Please manually enter your skills.';
        return $result;
    }
    
    // Calculate match score
    $score = calculate_job_match($extracted_skills, $job_ref, $conn);
    
    // Store top skills
    $top_skills = array_slice($extracted_skills, 0, 10, true);
    
    $result['status'] = true;
    $result['score'] = $score;
    $result['matched_skills'] = $top_skills;
    $result['message'] = 'Resume processed successfully.';
    
    return $result;
}

// Variable to store parsing results
$resume_result = null;
$job_ref = '';
$parse_message = '';

// Only process if this is a POST request with resume upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resume'], $_POST['job_reference']) && !empty($_POST['job_reference'])) {
    $job_ref = sanitize_input($_POST['job_reference']);
    
    // Process resume
    $resume_result = process_resume($_FILES['resume'], $job_ref, $conn);
    $parse_message = $resume_result['message'];
    
    // Store the uploaded file if processing was successful
    if ($resume_result['status']) {
        $upload_dir = 'uploads/resumes/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . basename($_FILES['resume']['name']);
        $target_file = $upload_dir . $filename;
        
        // Move file to upload directory
        if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_file)) {
            // Store file location in session for later use
            $_SESSION['resume_file'] = $target_file;
            $_SESSION['resume_skills'] = $resume_result['matched_skills'];
            $_SESSION['resume_score'] = $resume_result['score'];
        }
    }
}

// Create form for resume upload
?>

<!-- Resume Upload Form -->
<div class="resume-upload-container">
    <h2>Resume Upload & Skill Matching</h2>
    
    <?php if (isset($parse_message) && !empty($parse_message)): ?>
    <div class="alert <?php echo isset($resume_result) && $resume_result['status'] ? 'alert-success' : 'alert-warning'; ?>">
        <?php echo htmlspecialchars($parse_message); ?>
    </div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . (isset($_GET['job']) ? '?job=' . urlencode($_GET['job']) : ''); ?>" method="post" enctype="multipart/form-data" class="resume-form">
        <!-- Hidden field for job reference -->
        <input type="hidden" name="job_reference" value="<?php echo htmlspecialchars(isset($_GET['job']) ? $_GET['job'] : $job_ref); ?>">
        
        <div class="form-group">
            <label for="resume">Upload Your Resume:</label>
            <input type="file" name="resume" id="resume" accept=".pdf,.doc,.docx,.txt" required>
            <span class="file-help">Supported formats: PDF, DOC, DOCX, TXT</span>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Upload & Parse Resume</button>
        </div>
    </form>
    
    <?php if (isset($resume_result) && $resume_result['status']): ?>
    <div class="parse-results">
        <h3>Match Score: <?php echo htmlspecialchars($resume_result['score']); ?>%</h3>
        
        <?php if (!empty($resume_result['matched_skills'])): ?>
        <div class="extracted-skills">
            <h4>Skills Found in Your Resume:</h4>
            <div class="skill-tags">
                <?php foreach ($resume_result['matched_skills'] as $skill => $count): ?>
                <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="resume-actions">
            <a href="apply.php?job=<?php echo htmlspecialchars($job_ref); ?>&prefill=skills" class="btn btn-success">Apply with These Skills</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.resume-upload-container {
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.resume-form {
    margin: 15px 0;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.file-help {
    display: block;
    font-size: 0.8em;
    color: #666;
    margin-top: 5px;
}

.form-actions {
    margin-top: 20px;
}

.parse-results {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.extracted-skills {
    margin: 15px 0;
}

.skill-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.skill-tag {
    background-color: #e9f5ff;
    color: #0078d4;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.9em;
}

.resume-actions {
    margin-top: 20px;
}

.alert {
    padding: 10px 15px;
    border-radius: 4px;
    margin-bottom: 15px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
}

.btn {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: background-color 0.3s;
}

.btn-primary {
    background-color: #0078d4;
    color: white;
}

.btn-primary:hover {
    background-color: #005a9e;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-success:hover {
    background-color: #218838;
}
</style>
