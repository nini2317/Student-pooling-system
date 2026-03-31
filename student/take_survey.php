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

// Get survey ID
$survey_id = isset($_GET['id']) ? sanitize($_GET['id']) : null;

if (!$survey_id) {
    redirect('dashboard.php#surveys');
}

// Get survey details
$survey_sql = "SELECT * FROM surveys WHERE id = ?";
$survey_stmt = $conn->prepare($survey_sql);
$survey_stmt->bind_param("i", $survey_id);
$survey_stmt->execute();
$survey = $survey_stmt->get_result()->fetch_assoc();

if (!$survey) {
    $_SESSION['error'] = "Survey not found!";
    redirect('dashboard.php#surveys');
}

// Check if user has already taken this survey
$check_sql = "SELECT id FROM survey_responses WHERE survey_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $survey_id, $user_id);
$check_stmt->execute();

if ($check_stmt->get_result()->num_rows > 0) {
    $_SESSION['error'] = "You have already taken this survey!";
    redirect('dashboard.php#surveys');
}

// Handle survey submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = sanitize($_POST['response']);
    
    if (empty($response)) {
        $error = "Please provide your response!";
    } else {
        $submit_sql = "INSERT INTO survey_responses (survey_id, user_id, response) VALUES (?, ?, ?)";
        $submit_stmt = $conn->prepare($submit_sql);
        $submit_stmt->bind_param("iis", $survey_id, $user_id, $response);
        
        if ($submit_stmt->execute()) {
            $_SESSION['success'] = "Your survey response has been submitted successfully!";
            redirect('dashboard.php#surveys');
        } else {
            $error = "Failed to submit your response. Please try again!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Survey - Polling System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-clipboard-list fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold">Take Survey</h2>
                        </div>

                        <?php if (isset($error)): ?>
                            <?= alert($error, 'danger') ?>
                        <?php endif; ?>

                        <div class="survey-details mb-4">
                            <h4 class="mb-3"><?= htmlspecialchars($survey['title']) ?></h4>
                            <?php if ($survey['description']): ?>
                                <p class="text-muted"><?= htmlspecialchars($survey['description']) ?></p>
                            <?php endif; ?>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Created on <?= formatDate($survey['created_at']) ?>
                            </small>
                        </div>

                        <form method="POST">
                            <div class="mb-4">
                                <label for="response" class="form-label">
                                    <i class="fas fa-comment me-2"></i>Your Response
                                </label>
                                <textarea class="form-control" id="response" name="response" rows="6" 
                                          placeholder="Please share your thoughts and opinions..." required></textarea>
                                <div class="form-text">
                                    Your feedback is valuable and will help us improve. Please be honest and detailed in your response.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php#surveys" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Surveys
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Response
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
</body>
</html>
