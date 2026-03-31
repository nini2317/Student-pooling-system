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

// Get poll ID from URL
$poll_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($poll_id === 0) {
    $_SESSION['error'] = "Invalid poll ID!";
    redirect('view_polls.php');
}

// Get poll details
$poll_sql = "SELECT * FROM polls WHERE id = ?";
$poll_stmt = $conn->prepare($poll_sql);
$poll_stmt->bind_param("i", $poll_id);
$poll_stmt->execute();
$poll = $poll_stmt->get_result()->fetch_assoc();

if (!$poll) {
    $_SESSION['error'] = "Poll not found!";
    redirect('view_polls.php');
}

// Get poll options
$options_sql = "SELECT * FROM poll_options WHERE poll_id = ?";
$options_stmt = $conn->prepare($options_sql);
$options_stmt->bind_param("i", $poll_id);
$options_stmt->execute();
$options = $options_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle poll update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_poll'])) {
    $title = sanitize($_POST['title']);
    $category = sanitize($_POST['category']);
    $expiry_date = !empty($_POST['expiry_date']) ? sanitize($_POST['expiry_date']) : null;
    
    // Validate inputs
    if (empty($title) || empty($category)) {
        $_SESSION['error'] = "Title and category are required!";
    } else {
        // Update poll
        $update_sql = "UPDATE polls SET title = ?, category = ?, expiry_date = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssi", $title, $category, $expiry_date, $poll_id);
        
        if ($update_stmt->execute()) {
            // Update options
            $option_texts = $_POST['options'];
            $option_ids = $_POST['option_ids'];
            
            foreach ($option_texts as $index => $option_text) {
                if (!empty($option_text) && isset($option_ids[$index])) {
                    $update_option_sql = "UPDATE poll_options SET option_text = ? WHERE id = ?";
                    $update_option_stmt = $conn->prepare($update_option_sql);
                    $update_option_stmt->bind_param("si", $option_text, $option_ids[$index]);
                    $update_option_stmt->execute();
                }
            }
            
            $_SESSION['success'] = "Poll updated successfully!";
            redirect('view_polls.php');
        } else {
            $_SESSION['error'] = "Failed to update poll. Please try again!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Poll - Admin Dashboard</title>
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
                    <h1 class="h2">Edit Poll</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="view_polls.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Polls
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
                        <form method="POST" id="pollForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Poll Title</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= htmlspecialchars($poll['title']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <option value="Academic" <?= $poll['category'] == 'Academic' ? 'selected' : '' ?>>Academic</option>
                                            <option value="Cultural" <?= $poll['category'] == 'Cultural' ? 'selected' : '' ?>>Cultural</option>
                                            <option value="Sports" <?= $poll['category'] == 'Sports' ? 'selected' : '' ?>>Sports</option>
                                            <option value="Campus" <?= $poll['category'] == 'Campus' ? 'selected' : '' ?>>Campus</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date (Optional)</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" 
                                       value="<?= $poll['expiry_date'] && $poll['expiry_date'] != '0000-00-00' ? $poll['expiry_date'] : '' ?>">
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Poll Options</h5>
                            <div id="optionsContainer">
                                <?php foreach ($options as $index => $option): ?>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text"><?= $index + 1 ?></span>
                                        <input type="text" class="form-control poll-option" name="options[]" 
                                               value="<?= htmlspecialchars($option['option_text']) ?>" 
                                               placeholder="Enter option <?= $index + 1 ?>...">
                                        <input type="hidden" name="option_ids[]" value="<?= $option['id'] ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="view_polls.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" name="update_poll" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Poll
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
