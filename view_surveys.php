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

// Handle survey deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_survey'])) {
    $survey_id = sanitize($_POST['survey_id']);
    
    $delete_sql = "DELETE FROM surveys WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $survey_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "Survey deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete survey. Please try again!";
    }
    
    redirect('view_surveys.php');
}

// Get all surveys with response counts
$surveys_sql = "SELECT s.*, u.name as creator_name, COUNT(sr.id) as response_count
                FROM surveys s 
                LEFT JOIN users u ON s.created_by = u.id 
                LEFT JOIN survey_responses sr ON s.id = sr.survey_id 
                GROUP BY s.id 
                ORDER BY s.created_at DESC";
$surveys = $conn->query($surveys_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Surveys - Admin Dashboard</title>
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
                    <h1 class="h2">All Surveys</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="create_survey.php" class="btn btn-sm btn-success">
                                <i class="fas fa-plus me-2"></i>Create New Survey
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <?= alert($_SESSION['success'], 'success') ?>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <?= alert($_SESSION['error'], 'danger') ?>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if ($surveys->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="surveysTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Responses</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($survey = $surveys->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $survey['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($survey['title']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="text-muted" title="<?= htmlspecialchars($survey['description']) ?>">
                                                <?= substr(htmlspecialchars($survey['description']), 0, 100) ?>...
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?= $survey['response_count'] ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($survey['creator_name']) ?></td>
                                        <td><?= formatDate($survey['created_at']) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="viewSurveyResponses(<?= $survey['id'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="editSurvey(<?= $survey['id'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this survey? All responses will be permanently deleted.')">
                                                    <input type="hidden" name="survey_id" value="<?= $survey['id'] ?>">
                                                    <button type="submit" name="delete_survey" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                        <h4>No surveys created yet</h4>
                        <p class="text-muted">Start by creating your first survey!</p>
                        <a href="create_survey.php" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Create Your First Survey
                        </a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Survey Responses Modal -->
    <div class="modal fade" id="surveyResponsesModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Survey Responses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="surveyResponsesContent">
                    <!-- Responses will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        let currentSurveyId = null;

        function viewSurveyResponses(surveyId) {
            currentSurveyId = surveyId;
            const modal = new bootstrap.Modal(document.getElementById('surveyResponsesModal'));
            document.getElementById('surveyResponsesContent').innerHTML = `
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                    <p>Loading survey responses...</p>
                </div>
            `;
            modal.show();
            
            // Simulate loading responses (in a real app, this would be an AJAX call)
            setTimeout(() => {
                document.getElementById('surveyResponsesContent').innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Survey responses would be displayed here with detailed student feedback and analytics.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Response</th>
                                    <th>Submitted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Sample Student</td>
                                    <td>This is a sample response that would be loaded from the database...</td>
                                    <td>2024-03-05 10:30 AM</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `;
            }, 1000);
        }

        function editSurvey(surveyId) {
            // This would typically redirect to an edit page or open an edit modal
            window.location.href = `edit_survey.php?id=${surveyId}`;
        }
    </script>
</body>
</html>
