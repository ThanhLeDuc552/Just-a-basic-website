<?php
/**
 * Application Timeline Component
 * Displays interactive timeline of application progress
 */

require_once 'settings.php';
require_once 'functions.inc';

// Check if EOI number is provided
if (!isset($eoi_number) || empty($eoi_number)) {
    echo '<div class="alert alert-warning">No application ID provided</div>';
    return;
}

// Get application details
$application = get_application_details($eoi_number, $conn);
if (!$application) {
    echo '<div class="alert alert-warning">Application not found</div>';
    return;
}

// Get timeline events
$timeline = get_application_timeline($eoi_number, $conn);

// If timeline is empty, add "Applied" stage automatically
if (empty($timeline)) {
    add_timeline_event($eoi_number, 'Applied', 'Application submitted', null, $conn);
    $timeline = get_application_timeline($eoi_number, $conn);
}

// Define all possible stages and their order
$all_stages = [
    'Applied' => [
        'icon' => 'file-text',
        'color' => 'blue',
        'description' => 'Your application has been submitted successfully.'
    ],
    'Resume Reviewed' => [
        'icon' => 'search',
        'color' => 'purple',
        'description' => 'Our team has reviewed your resume and application details.'
    ],
    'Interview Scheduled' => [
        'icon' => 'calendar',
        'color' => 'orange',
        'description' => 'An interview has been scheduled. Check your email for details.'
    ],
    'Interview Completed' => [
        'icon' => 'users',
        'color' => 'green',
        'description' => 'You\'ve completed the interview process. Our team is evaluating your performance.'
    ],
    'Offer Extended' => [
        'icon' => 'file-contract',
        'color' => 'teal',
        'description' => 'Congratulations! We\'ve extended an offer to you.'
    ],
    'Hired' => [
        'icon' => 'handshake',
        'color' => 'green',
        'description' => 'You\'ve accepted our offer. Welcome to the team!'
    ],
    'Rejected' => [
        'icon' => 'times-circle',
        'color' => 'red',
        'description' => 'We appreciate your interest, but we\'ve decided to move forward with other candidates.'
    ]
];

// Get current stage from timeline
$current_stage = end($timeline)['Stage'];
?>

<div class="application-timeline">
    <h2>Application Status</h2>
    
    <div class="timeline-container">
        <div class="timeline-progress-bar">
            <?php 
            $completed_stages = [];
            foreach ($timeline as $event) {
                $completed_stages[] = $event['Stage'];
            }
            
            $stage_count = count($all_stages);
            $completed_count = count($completed_stages);
            
            // Don't count Rejected in progress calculation
            if ($current_stage !== 'Rejected') {
                $progress = (($completed_count - 1) / ($stage_count - 2)) * 100;
            } else {
                $progress = 0;
            }
            
            // Limit progress to 100%
            $progress = min($progress, 100);
            ?>
            
            <div class="progress-indicator" style="width: <?php echo $progress; ?>%"></div>
        </div>
        
        <div class="timeline-steps">
            <?php foreach ($all_stages as $stage => $details): 
                if ($stage === 'Rejected' && $current_stage !== 'Rejected') {
                    continue; // Skip Rejected stage unless it's current
                }
                
                $is_completed = in_array($stage, $completed_stages);
                $is_current = ($stage === $current_stage);
                
                $status_class = $is_completed ? 'completed' : 'pending';
                if ($is_current) {
                    $status_class = 'current';
                }
                
                // Find event details if this stage exists in timeline
                $event_details = null;
                foreach ($timeline as $event) {
                    if ($event['Stage'] === $stage) {
                        $event_details = $event;
                        break;
                    }
                }
            ?>
            <div class="timeline-step <?php echo $status_class; ?>">
                <div class="step-icon" style="background-color: var(--<?php echo $details['color']; ?>);">
                    <i class="fas fa-<?php echo $details['icon']; ?>"></i>
                </div>
                <div class="step-content">
                    <h3><?php echo htmlspecialchars($stage); ?></h3>
                    <p><?php echo htmlspecialchars($details['description']); ?></p>
                    
                    <?php if ($event_details): ?>
                    <div class="step-details">
                        <span class="step-date"><?php echo date('M d, Y', strtotime($event_details['StageTimestamp'])); ?></span>
                        
                        <?php if (!empty($event_details['Notes'])): ?>
                        <div class="step-notes">
                            <?php echo htmlspecialchars($event_details['Notes']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($event_details['NextStepDate'])): ?>
                        <div class="next-step">
                            <strong>Next Step:</strong> <?php echo date('M d, Y', strtotime($event_details['NextStepDate'])); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
