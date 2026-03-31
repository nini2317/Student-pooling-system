<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../index.php');
}

// Check if user is student
if (isAdmin()) {
    redirect('../admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['poll_id'])) {
    $user_id = $_SESSION['user_id'];
    $poll_id = sanitize($_POST['poll_id']);
    $option_id = sanitize($_POST['option_id']);
    
    // Validate inputs
    if (empty($poll_id) || empty($option_id)) {
        $_SESSION['error'] = "Please select an option to vote!";
        redirect('dashboard.php#polls');
    }
    
    // Check if user has already voted
    if (hasVoted($user_id, $poll_id)) {
        $_SESSION['error'] = "You have already voted in this poll!";
        redirect('dashboard.php#polls');
    }
    
    // Check if poll exists and is not expired
    $poll_check_sql = "SELECT id FROM polls WHERE id = ? AND (expiry_date IS NULL OR expiry_date >= CURDATE() OR expiry_date = '0000-00-00')";
    $poll_check_stmt = $conn->prepare($poll_check_sql);
    $poll_check_stmt->bind_param("i", $poll_id);
    $poll_check_stmt->execute();
    $poll_check_result = $poll_check_stmt->get_result();
    
    if ($poll_check_result->num_rows === 0) {
        $_SESSION['error'] = "This poll is no longer available!";
        redirect('dashboard.php#polls');
    }
    
    // Check if option exists for this poll
    $option_check_sql = "SELECT id FROM poll_options WHERE id = ? AND poll_id = ?";
    $option_check_stmt = $conn->prepare($option_check_sql);
    $option_check_stmt->bind_param("ii", $option_id, $poll_id);
    $option_check_stmt->execute();
    $option_check_result = $option_check_stmt->get_result();
    
    if ($option_check_result->num_rows === 0) {
        $_SESSION['error'] = "Invalid option selected!";
        redirect('dashboard.php#polls');
    }
    
    // Insert vote
    $vote_sql = "INSERT INTO votes (user_id, poll_id, option_id) VALUES (?, ?, ?)";
    $vote_stmt = $conn->prepare($vote_sql);
    $vote_stmt->bind_param("iii", $user_id, $poll_id, $option_id);
    
    if ($vote_stmt->execute()) {
        $_SESSION['success'] = "Your vote has been recorded successfully!";
    } else {
        $_SESSION['error'] = "Failed to record your vote. Please try again!";
    }
    
    redirect('dashboard.php#results');
} else {
    redirect('dashboard.php#polls');
}
?>
