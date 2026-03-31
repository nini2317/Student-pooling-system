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

$user_id = $_SESSION['user_id'];

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = sanitize($_POST['message']);
    
    if (empty($message)) {
        $error = "Please provide your feedback message!";
    } elseif (strlen($message) < 10) {
        $error = "Feedback message must be at least 10 characters long!";
    } else {
        $submit_sql = "INSERT INTO feedback (user_id, message) VALUES (?, ?)";
        $submit_stmt = $conn->prepare($submit_sql);
        $submit_stmt->bind_param("is", $user_id, $message);
        
        if ($submit_stmt->execute()) {
            $success = "Your feedback has been submitted successfully! Thank you for your input.";
        } else {
            $error = "Failed to submit feedback. Please try again!";
        }
    }
}

// Get user's previous feedback
$feedback_sql = "SELECT f.*, u.name FROM feedback f 
                 JOIN users u ON f.user_id = u.id 
                 WHERE f.user_id = ? 
                 ORDER BY f.created_at DESC 
                 LIMIT 5";
$feedback_stmt = $conn->prepare($feedback_sql);
$feedback_stmt->bind_param("i", $user_id);
$feedback_stmt->execute();
$previous_feedback = $feedback_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback - Polling System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container vh-100 d-flex align-items-center justify-content-center py-4">
        <div class="row w-100">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-comment-dots fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold">Submit Feedback</h2>
                            <p class="text-muted">We value your opinion and suggestions</p>
                        </div>

                        <?php if (isset($error)): ?>
                            <?= alert($error, 'danger') ?>
                        <?php endif; ?>

                        <?php if (isset($success)): ?>
                            <?= alert($success, 'success') ?>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-4">
                                <label for="message" class="form-label">
                                    <i class="fas fa-message me-2"></i>Your Feedback
                                </label>
                                <textarea class="form-control" id="message" name="message" rows="6" 
                                          placeholder="Share your thoughts, suggestions, or report issues..." required></textarea>
                                <div class="form-text">
                                    Your feedback helps us improve the polling system. Be specific and constructive.
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-outline-primary" onclick="setFeedbackType('suggestion')">
                                            <i class="fas fa-lightbulb me-2"></i>Suggestion
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-outline-warning" onclick="setFeedbackType('issue')">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Report Issue
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                                </button>
                            </div>
                        </form>

                        <?php if ($previous_feedback->num_rows > 0): ?>
                            <hr class="my-5">
                            <h4 class="mb-4"><i class="fas fa-history me-2"></i>Your Recent Feedback</h4>
                            <div class="feedback-history">
                                <?php while ($feedback = $previous_feedback->fetch_assoc()): ?>
                                    <div class="card mb-3 border-light">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($feedback['name']) ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i><?= formatDate($feedback['created_at']) ?>
                                                </small>
                                            </div>
                                            <p class="mb-0"><?= htmlspecialchars($feedback['message']) ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        function setFeedbackType(type) {
            const messageField = document.getElementById('message');
            const suggestions = {
                'suggestion': 'I would like to suggest that you consider adding...',
                'issue': 'I encountered an issue while using the system: '
            };
            
            if (messageField.value.trim() === '') {
                messageField.value = suggestions[type];
            } else {
                messageField.value = suggestions[type] + messageField.value;
            }
            messageField.focus();
        }
    </script>
</body>
</html>
