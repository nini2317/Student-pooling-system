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

$admin_id = $_SESSION['user_id'];

// Handle survey creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    
    // Validate inputs
    if (empty($title)) {
        $error = "Survey title is required!";
    } elseif (empty($description)) {
        $error = "Survey description is required!";
    } elseif (strlen($description) < 20) {
        $error = "Survey description must be at least 20 characters long!";
    } else {
        // Create survey
        $survey_sql = "INSERT INTO surveys (title, description, created_by) VALUES (?, ?, ?)";
        $survey_stmt = $conn->prepare($survey_sql);
        $survey_stmt->bind_param("ssi", $title, $description, $admin_id);
        
        if ($survey_stmt->execute()) {
            $_SESSION['success'] = "Survey created successfully!";
            redirect('view_surveys.php');
        } else {
            $error = "Failed to create survey. Please try again!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Survey - Admin Dashboard</title>
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
                            <a class="nav-link active" href="create_survey.php">
                                <i class="fas fa-plus-square me-2"></i>Create Survey
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_surveys.php">
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
                    <h1 class="h2">Create New Survey</h1>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card shadow">
                            <div class="card-body p-5">
                                <div class="text-center mb-4">
                                    <i class="fas fa-clipboard-list fa-3x text-success mb-3"></i>
                                    <h3 class="fw-bold">Create a New Survey</h3>
                                    <p class="text-muted">Gather detailed feedback from students</p>
                                </div>

                                <?php if (isset($error)): ?>
                                    <?= alert($error, 'danger') ?>
                                <?php endif; ?>

                                <form method="POST" id="surveyForm">
                                    <div class="mb-4">
                                        <label for="title" class="form-label">
                                            <i class="fas fa-heading me-2"></i>Survey Title
                                        </label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               placeholder="Enter survey title..." required>
                                        <div class="form-text">Choose a clear and descriptive title for your survey</div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="description" class="form-label">
                                            <i class="fas fa-align-left me-2"></i>Survey Description
                                        </label>
                                        <textarea class="form-control" id="description" name="description" rows="8" 
                                                  placeholder="Provide a detailed description of what this survey is about..." required></textarea>
                                        <div class="form-text">
                                            Explain the purpose of this survey and what kind of feedback you're looking for.
                                            Minimum 20 characters required.
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">
                                            <i class="fas fa-info-circle me-2"></i>Survey Guidelines
                                        </label>
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-lightbulb me-2"></i>Tips for creating effective surveys:</h6>
                                            <ul class="mb-0">
                                                <li>Be clear about the purpose and goals of your survey</li>
                                                <li>Keep your description concise but informative</li>
                                                <li>Consider what specific feedback you need from students</li>
                                                <li>Think about how you'll use the collected responses</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="confirmGuidelines" required>
                                            <label class="form-check-label" for="confirmGuidelines">
                                                I understand that students will provide open-ended responses to this survey
                                            </label>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="dashboard.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check me-2"></i>Create Survey
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        // Character counter for description
        const descriptionField = document.getElementById('description');
        const minLength = 20;
        
        descriptionField.addEventListener('input', function() {
            const currentLength = this.value.length;
            const formText = this.parentElement.querySelector('.form-text');
            
            if (currentLength < minLength) {
                formText.innerHTML = `Explain the purpose of this survey and what kind of feedback you're looking for.<br>
                    <span class="text-danger">Minimum ${minLength} characters required (${currentLength}/${minLength})</span>`;
            } else {
                formText.innerHTML = `Explain the purpose of this survey and what kind of feedback you're looking for.<br>
                    <span class="text-success">${currentLength} characters (minimum met)</span>`;
            }
        });

        // Form validation
        document.getElementById('surveyForm').addEventListener('submit', function(e) {
            const description = document.getElementById('description').value.trim();
            
            if (description.length < minLength) {
                e.preventDefault();
                alert(`Survey description must be at least ${minLength} characters long!`);
                return false;
            }
        });
    </script>
</body>
</html>
