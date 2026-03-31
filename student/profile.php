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

// Get user data
$user = getUserData($user_id);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($name) || empty($email)) {
        $error = "Name and email are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif ($new_password && $new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } elseif ($new_password && strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long!";
    } else {
        // Check if email is being changed and if it already exists
        if ($email !== $user['email']) {
            $email_check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $email_check_stmt = $conn->prepare($email_check_sql);
            $email_check_stmt->bind_param("si", $email, $user_id);
            $email_check_stmt->execute();
            
            if ($email_check_stmt->get_result()->num_rows > 0) {
                $error = "Email already exists!";
            }
        }
        
        if (!isset($error)) {
            // If changing password, verify current password
            if ($new_password) {
                if (!password_verify($current_password, $user['password'])) {
                    $error = "Current password is incorrect!";
                }
            }
            
            if (!isset($error)) {
                // Update profile
                if ($new_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
                } else {
                    $update_sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ssi", $name, $email, $user_id);
                }
                
                if ($update_stmt->execute()) {
                    // Update session variables
                    $_SESSION['name'] = $name;
                    $_SESSION['email'] = $email;
                    
                    $success = "Profile updated successfully!";
                    // Refresh user data
                    $user = getUserData($user_id);
                } else {
                    $error = "Failed to update profile. Please try again!";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Polling System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100">
            <div class="col-lg-6 mx-auto">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="user-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                                <?= strtoupper(substr($user['name'], 0, 2)) ?>
                            </div>
                            <h2 class="fw-bold">Edit Profile</h2>
                            <p class="text-muted">Update your personal information</p>
                        </div>

                        <?php if (isset($error)): ?>
                            <?= alert($error, 'danger') ?>
                        <?php endif; ?>

                        <?php if (isset($success)): ?>
                            <?= alert($success, 'success') ?>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user me-2"></i>Full Name
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-shield-alt me-2"></i>Account Type
                                </label>
                                <input type="text" class="form-control" value="Student" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar me-2"></i>Member Since
                                </label>
                                <input type="text" class="form-control" 
                                       value="<?= formatDate($user['created_at']) ?>" disabled>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Change Password</h5>
                            <p class="text-muted small">Leave blank if you don't want to change your password</p>

                            <div class="mb-3">
                                <label for="current_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Current Password
                                </label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password">
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">
                                    <i class="fas fa-key me-2"></i>New Password
                                </label>
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" minlength="6">
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-check me-2"></i>Confirm New Password
                                </label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" minlength="6">
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
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
