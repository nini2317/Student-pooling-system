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

// Handle feedback deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_feedback'])) {
    $feedback_id = sanitize($_POST['feedback_id']);
    
    $delete_sql = "DELETE FROM feedback WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $feedback_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "Feedback deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete feedback. Please try again!";
    }
    
    redirect('manage_feedback.php');
}

// Get all feedback with user information
$feedback_sql = "SELECT f.*, u.name as user_name, u.email as user_email 
                 FROM feedback f 
                 LEFT JOIN users u ON f.user_id = u.id 
                 ORDER BY f.created_at DESC";
$feedback = $conn->query($feedback_sql);

// Get feedback statistics
$total_feedback_sql = "SELECT COUNT(*) as count FROM feedback";
$total_feedback = $conn->query($total_feedback_sql)->fetch_assoc()['count'];

$recent_feedback_sql = "SELECT COUNT(*) as count FROM feedback WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$recent_feedback = $conn->query($recent_feedback_sql)->fetch_assoc()['count'];

$unique_users_sql = "SELECT COUNT(DISTINCT user_id) as count FROM feedback WHERE user_id IS NOT NULL";
$unique_users = $conn->query($unique_users_sql)->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedback - Admin Dashboard</title>
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
                            <a class="nav-link active" href="manage_feedback.php">
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
                    <h1 class="h2">Manage Feedback</h1>
                </div>

                <!-- Feedback Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="stats-card">
                            <i class="fas fa-comment"></i>
                            <h3><?= $total_feedback ?></h3>
                            <p class="mb-0">Total Feedback</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stats-card">
                            <i class="fas fa-clock"></i>
                            <h3><?= $recent_feedback ?></h3>
                            <p class="mb-0">This Week</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stats-card">
                            <i class="fas fa-user-friends"></i>
                            <h3><?= $unique_users ?></h3>
                            <p class="mb-0">Unique Users</p>
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

                <?php if ($feedback->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="feedbackTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $feedback->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $item['id'] ?></td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($item['user_name'] ?: 'Anonymous') ?></strong>
                                                <?php if ($item['user_email']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($item['user_email']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="feedback-message" style="max-width: 400px;">
                                                <?= htmlspecialchars($item['message']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small>
                                                <div><?= formatDate($item['created_at']) ?></div>
                                                <div class="text-muted"><?= formatTime($item['created_at']) ?></div>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="viewFeedbackDetails(<?= $item['id'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="respondToFeedback(<?= $item['id'] ?>)">
                                                    <i class="fas fa-reply"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this feedback?')">
                                                    <input type="hidden" name="feedback_id" value="<?= $item['id'] ?>">
                                                    <button type="submit" name="delete_feedback" class="btn btn-sm btn-outline-danger">
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
                        <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                        <h4>No feedback received yet</h4>
                        <p class="text-muted">Students haven't submitted any feedback yet.</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Feedback Details Modal -->
    <div class="modal fade" id="feedbackDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Feedback Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="feedbackDetailsContent">
                    <!-- Feedback details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="respondToFeedback(currentFeedbackId)">
                        <i class="fas fa-reply me-2"></i>Respond
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Response Modal -->
    <div class="modal fade" id="responseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Respond to Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="send_response.php">
                    <div class="modal-body">
                        <input type="hidden" name="feedback_id" id="responseFeedbackId">
                        <div class="mb-3">
                            <label for="response_message" class="form-label">Your Response</label>
                            <textarea class="form-control" id="response_message" name="response_message" rows="4" 
                                      placeholder="Type your response to the student..." required></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This response will be sent to the student who provided the feedback.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane me-2"></i>Send Response
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        let currentFeedbackId = null;

        function viewFeedbackDetails(feedbackId) {
            currentFeedbackId = feedbackId;
            const modal = new bootstrap.Modal(document.getElementById('feedbackDetailsModal'));
            document.getElementById('feedbackDetailsContent').innerHTML = `
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                    <p>Loading feedback details...</p>
                </div>
            `;
            modal.show();
            
            // Simulate loading details (in a real app, this would be an AJAX call)
            setTimeout(() => {
                document.getElementById('feedbackDetailsContent').innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Full feedback details would be displayed here with complete user information and metadata.
                    </div>
                `;
            }, 1000);
        }

        function respondToFeedback(feedbackId) {
            currentFeedbackId = feedbackId;
            document.getElementById('responseFeedbackId').value = feedbackId;
            document.getElementById('response_message').value = '';
            
            const detailsModal = bootstrap.Modal.getInstance(document.getElementById('feedbackDetailsModal'));
            if (detailsModal) {
                detailsModal.hide();
            }
            
            const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
            responseModal.show();
        }
    </script>
</body>
</html>
