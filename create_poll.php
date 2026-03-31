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

// Handle poll creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $category = sanitize($_POST['category']);
    $expiry_date = !empty($_POST['expiry_date']) ? sanitize($_POST['expiry_date']) : null;
    $options = $_POST['options'];
    
    // Validate inputs
    if (empty($title)) {
        $error = "Poll title is required!";
    } elseif (empty($category)) {
        $error = "Please select a category!";
    } elseif (empty($options) || count(array_filter($options)) < 2) {
        $error = "Please provide at least 2 options!";
    } else {
        // Create poll
        $poll_sql = "INSERT INTO polls (title, category, created_by, expiry_date) VALUES (?, ?, ?, ?)";
        $poll_stmt = $conn->prepare($poll_sql);
        $poll_stmt->bind_param("ssis", $title, $category, $admin_id, $expiry_date);
        
        if ($poll_stmt->execute()) {
            $poll_id = $conn->insert_id;
            
            // Add poll options
            $option_sql = "INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)";
            $option_stmt = $conn->prepare($option_sql);
            
            $success_count = 0;
            foreach ($options as $option) {
                $option = trim($option);
                if (!empty($option)) {
                    $option_stmt->bind_param("is", $poll_id, $option);
                    if ($option_stmt->execute()) {
                        $success_count++;
                    }
                }
            }
            
            if ($success_count >= 2) {
                $_SESSION['success'] = "Poll created successfully with $success_count options!";
                redirect('view_polls.php');
            } else {
                $error = "Failed to add poll options. Please try again!";
            }
        } else {
            $error = "Failed to create poll. Please try again!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Poll - Admin Dashboard</title>
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
                            <a class="nav-link active" href="create_poll.php">
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Create New Poll</h1>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>

                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card shadow">
                            <div class="card-body p-5">
                                <div class="text-center mb-4">
                                    <i class="fas fa-poll fa-3x text-primary mb-3"></i>
                                    <h3 class="fw-bold">Create a New Poll</h3>
                                    <p class="text-muted">Engage students with interactive polls</p>
                                </div>

                                <?php if (isset($error)): ?>
                                    <?= alert($error, 'danger') ?>
                                <?php endif; ?>

                                <form method="POST" id="pollForm">
                                    <div class="mb-4">
                                        <label for="title" class="form-label">
                                            <i class="fas fa-heading me-2"></i>Poll Title
                                        </label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               placeholder="Enter your poll question..." required>
                                    </div>

                                    <div class="mb-4">
                                        <label for="category" class="form-label">
                                            <i class="fas fa-tag me-2"></i>Category
                                        </label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Select a category...</option>
                                            <option value="Academic">Academic</option>
                                            <option value="Cultural">Cultural</option>
                                            <option value="Sports">Sports</option>
                                            <option value="Campus">Campus</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label for="expiry_date" class="form-label">
                                            <i class="fas fa-calendar me-2"></i>Expiry Date (Optional)
                                        </label>
                                        <input type="date" class="form-control" id="expiry_date" name="expiry_date" 
                                               min="<?= date('Y-m-d') ?>">
                                        <div class="form-text">Leave empty if poll should not expire</div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">
                                            <i class="fas fa-list me-2"></i>Poll Options
                                        </label>
                                        <div id="optionsContainer">
                                            <div class="input-group mb-2">
                                                <span class="input-group-text">1</span>
                                                <input type="text" class="form-control poll-option" name="options[]" 
                                                       placeholder="Enter option 1..." required>
                                                <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)" disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <div class="input-group mb-2">
                                                <span class="input-group-text">2</span>
                                                <input type="text" class="form-control poll-option" name="options[]" 
                                                       placeholder="Enter option 2..." required>
                                                <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)" disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addOption()">
                                            <i class="fas fa-plus me-2"></i>Add Option
                                        </button>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="dashboard.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-check me-2"></i>Create Poll
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
        let optionCount = 2;

        function addOption() {
            optionCount++;
            const optionsContainer = document.getElementById('optionsContainer');
            const optionDiv = document.createElement('div');
            optionDiv.className = 'input-group mb-2';
            optionDiv.innerHTML = `
                <span class="input-group-text">${optionCount}</span>
                <input type="text" class="form-control poll-option" name="options[]" 
                       placeholder="Enter option ${optionCount}...">
                <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            optionsContainer.appendChild(optionDiv);
            updateRemoveButtons();
        }

        function removeOption(button) {
            button.parentElement.remove();
            updateOptionNumbers();
            updateRemoveButtons();
        }

        function updateOptionNumbers() {
            const options = document.querySelectorAll('#optionsContainer .input-group');
            options.forEach((option, index) => {
                option.querySelector('.input-group-text').textContent = index + 1;
                option.querySelector('input').placeholder = `Enter option ${index + 1}...`;
            });
            optionCount = options.length;
        }

        function updateRemoveButtons() {
            const removeButtons = document.querySelectorAll('#optionsContainer .btn-outline-danger');
            const canRemove = removeButtons.length > 2;
            removeButtons.forEach(button => {
                button.disabled = !canRemove;
            });
        }

        // Form validation
        document.getElementById('pollForm').addEventListener('submit', function(e) {
            const options = document.querySelectorAll('.poll-option');
            const filledOptions = Array.from(options).filter(input => input.value.trim() !== '');
            
            if (filledOptions.length < 2) {
                e.preventDefault();
                alert('Please provide at least 2 options for the poll!');
                return false;
            }
        });
    </script>
</body>
</html>
