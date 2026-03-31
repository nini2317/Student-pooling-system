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

// Handle poll deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_poll'])) {
    $poll_id = sanitize($_POST['poll_id']);
    
    $delete_sql = "DELETE FROM polls WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $poll_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "Poll deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete poll. Please try again!";
    }
    
    redirect('view_polls.php');
}

// Get all polls with vote counts
$polls_sql = "SELECT p.*, u.name as creator_name, COUNT(v.id) as vote_count,
               (SELECT COUNT(*) FROM poll_options WHERE poll_id = p.id) as option_count
               FROM polls p 
               LEFT JOIN users u ON p.created_by = u.id 
               LEFT JOIN votes v ON p.id = v.poll_id 
               GROUP BY p.id 
               ORDER BY p.created_at DESC";
$polls = $conn->query($polls_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Polls - Admin Dashboard</title>
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
                            <a class="nav-link active" href="view_polls.php">
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
                    <h1 class="h2">All Polls</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="create_poll.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-2"></i>Create New Poll
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

                <?php if ($polls->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="pollsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Options</th>
                                    <th>Votes</th>
                                    <th>Created By</th>
                                    <th>Expiry Date</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($poll = $polls->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $poll['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($poll['title']) ?: 'Untitled Poll' ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?= htmlspecialchars($poll['category']) ?></span>
                                        </td>
                                        <td><?= $poll['option_count'] ?></td>
                                        <td>
                                            <span class="badge bg-success"><?= $poll['vote_count'] ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($poll['creator_name']) ?></td>
                                        <td>
                                            <?php if ($poll['expiry_date']): ?>
                                                <?php 
                                                $expiry_date = new DateTime($poll['expiry_date']);
                                                $today = new DateTime();
                                                $is_expired = $expiry_date < $today;
                                                ?>
                                                <span class="badge <?= $is_expired ? 'bg-danger' : 'bg-warning' ?>">
                                                    <?= formatDate($poll['expiry_date']) ?>
                                                    <?= $is_expired ? '(Expired)' : '' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">No expiry</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatDate($poll['created_at']) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="viewPollResults(<?= $poll['id'] ?>)">
                                                    <i class="fas fa-chart-bar"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="editPoll(<?= $poll['id'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this poll?')">
                                                    <input type="hidden" name="poll_id" value="<?= $poll['id'] ?>">
                                                    <button type="submit" name="delete_poll" class="btn btn-sm btn-outline-danger">
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
                        <i class="fas fa-poll fa-4x text-muted mb-3"></i>
                        <h4>No polls created yet</h4>
                        <p class="text-muted">Start by creating your first poll!</p>
                        <a href="create_poll.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Your First Poll
                        </a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Poll Results Modal -->
    <div class="modal fade" id="pollResultsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Poll Results</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="pollResultsContent">
                    <!-- Results will be loaded here -->
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
        function viewPollResults(pollId) {
            // This would typically make an AJAX call to get poll results
            // For now, we'll show a placeholder
            const modal = new bootstrap.Modal(document.getElementById('pollResultsModal'));
            document.getElementById('pollResultsContent').innerHTML = `
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                    <p>Loading poll results...</p>
                </div>
            `;
            modal.show();
            
            // Simulate loading results
            setTimeout(() => {
                document.getElementById('pollResultsContent').innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Poll results would be displayed here with detailed statistics and charts.
                    </div>
                `;
            }, 1000);
        }

        function editPoll(pollId) {
            // This would typically redirect to an edit page or open an edit modal
            window.location.href = `edit_poll.php?id=${pollId}`;
        }
    </script>
</body>
</html>
