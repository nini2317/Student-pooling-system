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

$user_name = $_SESSION['name'];

// Get statistics
$total_users_sql = "SELECT COUNT(*) as count FROM users";
$total_users = $conn->query($total_users_sql)->fetch_assoc()['count'];

$total_polls_sql = "SELECT COUNT(*) as count FROM polls";
$total_polls = $conn->query($total_polls_sql)->fetch_assoc()['count'];

$total_surveys_sql = "SELECT COUNT(*) as count FROM surveys";
$total_surveys = $conn->query($total_surveys_sql)->fetch_assoc()['count'];

$total_votes_sql = "SELECT COUNT(*) as count FROM votes";
$total_votes = $conn->query($total_votes_sql)->fetch_assoc()['count'];

$total_feedback_sql = "SELECT COUNT(*) as count FROM feedback";
$total_feedback = $conn->query($total_feedback_sql)->fetch_assoc()['count'];

// Get recent polls
$recent_polls_sql = "SELECT p.*, u.name as creator_name, COUNT(v.id) as vote_count 
                      FROM polls p 
                      LEFT JOIN users u ON p.created_by = u.id 
                      LEFT JOIN votes v ON p.id = v.poll_id 
                      GROUP BY p.id 
                      ORDER BY p.created_at DESC 
                      LIMIT 5";
$recent_polls = $conn->query($recent_polls_sql);

// Get recent surveys
$recent_surveys_sql = "SELECT s.*, u.name as creator_name, COUNT(sr.id) as response_count 
                        FROM surveys s 
                        LEFT JOIN users u ON s.created_by = u.id 
                        LEFT JOIN survey_responses sr ON s.id = sr.survey_id 
                        GROUP BY s.id 
                        ORDER BY s.created_at DESC 
                        LIMIT 5";
$recent_surveys = $conn->query($recent_surveys_sql);

// Get recent feedback
$recent_feedback_sql = "SELECT f.*, u.name as user_name 
                         FROM feedback f 
                         LEFT JOIN users u ON f.user_id = u.id 
                         ORDER BY f.created_at DESC 
                         LIMIT 5";
$recent_feedback = $conn->query($recent_feedback_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Polling System</title>
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
                        <h6 class="fw-bold"><?= htmlspecialchars($user_name) ?></h6>
                        <small class="text-muted">Administrator</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard">
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
                <!-- Mobile Sidebar Toggle -->
                <button class="btn btn-outline-secondary d-md-none mb-3 sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Welcome Section -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Admin Dashboard 👨‍💼</h1>
                </div>

                <!-- Statistics Section -->
                <section id="dashboard" class="fade-in">
                    <div class="row mb-4">
                        <div class="col-md-2 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-users"></i>
                                <h3><?= $total_users ?></h3>
                                <p class="mb-0">Total Users</p>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-poll"></i>
                                <h3><?= $total_polls ?></h3>
                                <p class="mb-0">Total Polls</p>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-clipboard-list"></i>
                                <h3><?= $total_surveys ?></h3>
                                <p class="mb-0">Total Surveys</p>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-vote-yea"></i>
                                <h3><?= $total_votes ?></h3>
                                <p class="mb-0">Total Votes</p>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-comment"></i>
                                <h3><?= $total_feedback ?></h3>
                                <p class="mb-0">Feedback</p>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-chart-line"></i>
                                <h3><?= $total_polls > 0 ? round($total_votes / $total_polls, 1) : 0 ?></h3>
                                <p class="mb-0">Avg Votes/Poll</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Quick Actions -->
                <section class="mb-5 fade-in">
                    <h3 class="mb-4">Quick Actions</h3>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="create_poll.php" class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="min-height: 120px;">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                Create New Poll
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="create_survey.php" class="btn btn-success w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="min-height: 120px;">
                                <i class="fas fa-plus-square fa-2x mb-2"></i>
                                Create Survey
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="manage_users.php" class="btn btn-info w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="min-height: 120px;">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                Manage Users
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="manage_feedback.php" class="btn btn-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="min-height: 120px;">
                                <i class="fas fa-comments fa-2x mb-2"></i>
                                View Feedback
                            </a>
                        </div>
                    </div>
                </section>

                <!-- Recent Activity -->
                <div class="row">
                    <!-- Recent Polls -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-poll me-2"></i>Recent Polls</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($recent_polls->num_rows > 0): ?>
                                    <?php while ($poll = $recent_polls->fetch_assoc()): ?>
                                        <div class="mb-3 pb-3 border-bottom">
                                            <h6 class="mb-1"><?= htmlspecialchars($poll['title']) ?: 'Untitled Poll' ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i><?= htmlspecialchars($poll['creator_name']) ?>
                                                <i class="fas fa-vote-yea ms-2 me-1"></i><?= $poll['vote_count'] ?> votes
                                            </small>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">No polls created yet.</p>
                                <?php endif; ?>
                                <div class="text-center mt-3">
                                    <a href="view_polls.php" class="btn btn-sm btn-outline-primary">View All</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Surveys -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Recent Surveys</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($recent_surveys->num_rows > 0): ?>
                                    <?php while ($survey = $recent_surveys->fetch_assoc()): ?>
                                        <div class="mb-3 pb-3 border-bottom">
                                            <h6 class="mb-1"><?= htmlspecialchars($survey['title']) ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i><?= htmlspecialchars($survey['creator_name']) ?>
                                                <i class="fas fa-reply ms-2 me-1"></i><?= $survey['response_count'] ?> responses
                                            </small>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">No surveys created yet.</p>
                                <?php endif; ?>
                                <div class="text-center mt-3">
                                    <a href="view_surveys.php" class="btn btn-sm btn-outline-success">View All</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Feedback -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Recent Feedback</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($recent_feedback->num_rows > 0): ?>
                                    <?php while ($feedback = $recent_feedback->fetch_assoc()): ?>
                                        <div class="mb-3 pb-3 border-bottom">
                                            <h6 class="mb-1"><?= htmlspecialchars($feedback['user_name']) ?></h6>
                                            <small class="text-muted"><?= substr(htmlspecialchars($feedback['message']), 0, 50) ?>...</small>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted">No feedback received yet.</p>
                                <?php endif; ?>
                                <div class="text-center mt-3">
                                    <a href="manage_feedback.php" class="btn btn-sm btn-outline-warning">View All</a>
                                </div>
                            </div>
                        </div>
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
