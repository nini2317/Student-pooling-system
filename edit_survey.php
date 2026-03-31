<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../index.php');
}

// Check if user is admin
if (!isAdmin()) {
    redirect('../student/dashboard.php');
}

// Get survey ID from URL
$survey_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($survey_id === 0) {
    $_SESSION['error'] = "Invalid survey ID!";
    redirect('view_surveys.php');
}

// Get survey details
$survey_sql = "SELECT * FROM surveys WHERE id = ?";
$survey_stmt = $conn->prepare($survey_sql);
$survey_stmt->bind_param("i", $survey_id);
$survey_stmt->execute();
$survey = $survey_stmt->get_result()->fetch_assoc();

if (!$survey) {
    $_SESSION['error'] = "Survey not found!";
    redirect('view_surveys.php');
}

// Handle survey update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_survey'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    
    // Validate inputs
    if (empty($title)) {
        $_SESSION['error'] = "Title is required!";
    } else {
        // Update survey
        $update_sql = "UPDATE surveys SET title = ?, description = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $title, $description, $survey_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Survey updated successfully!";
            redirect('view_surveys.php');
        } else {
            $_SESSION['error'] = "Failed to update survey. Please try again!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Survey - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <div class="user-avatar mx-auto mb-2">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h6 class="fw-bold"><?= htmlspecialchars($_SESSION['name']) ?></h6>
                        <small class="text-muted">Administrator</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_poll.php">
                                <i class="fas fa-plus-circle me-2"></i>Create Poll
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_polls.php">
                                <i class="fas fa-list me-2"></i>View All Polls
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_survey.php">
                                <i class="fas fa-plus-square me-2"></i>Create Survey
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="view_surveys.php">
                                <i class="fas fa-clipboard-list me-2"></i>View All Surveys
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_users.php">
                                <i class="fas fa-users me-2"></i>Manage Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_feedback.php">
                                <i class="fas fa-comments me-2"></i>Manage Feedback
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit Survey</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="view_surveys.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Surveys
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <?= alert($_SESSION['error'], 'danger') ?>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <?= alert($_SESSION['success'], 'success') ?>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <form method="POST" id="surveyForm">
                            <div class="mb-3">
                                <label for="title" class="form-label">Survey Title</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?= htmlspecialchars($survey['title']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" 
                                          placeholder="Enter survey description..."><?= htmlspecialchars($survey['description']) ?></textarea>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> Survey questions and responses are managed separately. This form only updates the basic survey information.
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="view_surveys.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" name="update_survey" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Survey
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
