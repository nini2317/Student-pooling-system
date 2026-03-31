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

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = sanitize($_POST['user_id']);
        
        // Prevent admin from deleting themselves
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account!";
        } else {
            $delete_sql = "DELETE FROM users WHERE id = ? AND role != 'admin'";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $user_id);
            
            if ($delete_stmt->execute()) {
                $_SESSION['success'] = "User deleted successfully!";
            } else {
                $_SESSION['error'] = "Failed to delete user. You cannot delete admin users.";
            }
        }
        redirect('manage_users.php');
    }
    
    if (isset($_POST['toggle_role'])) {
        $user_id = sanitize($_POST['user_id']);
        $new_role = sanitize($_POST['new_role']);
        
        // Prevent admin from changing their own role
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot change your own role!";
        } else {
            $update_sql = "UPDATE users SET role = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_role, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['success'] = "User role updated successfully!";
            } else {
                $_SESSION['error'] = "Failed to update user role. Please try again!";
            }
        }
        redirect('manage_users.php');
    }
}

// Get all users
$users_sql = "SELECT u.*, 
              (SELECT COUNT(*) FROM polls WHERE created_by = u.id) as polls_created,
              (SELECT COUNT(*) FROM votes WHERE user_id = u.id) as votes_cast,
              (SELECT COUNT(*) FROM survey_responses WHERE user_id = u.id) as surveys_taken
              FROM users u 
              ORDER BY u.created_at DESC";
$users = $conn->query($users_sql);

// Get user statistics
$total_users_sql = "SELECT COUNT(*) as count FROM users";
$total_users = $conn->query($total_users_sql)->fetch_assoc()['count'];

$admin_users_sql = "SELECT COUNT(*) as count FROM users WHERE role = 'admin'";
$admin_users = $conn->query($admin_users_sql)->fetch_assoc()['count'];

$student_users_sql = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
$student_users = $conn->query($student_users_sql)->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
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
                            <a class="nav-link active" href="manage_users.php">
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
                    <h1 class="h2">Manage Users</h1>
                </div>

                <!-- User Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="stats-card">
                            <i class="fas fa-users"></i>
                            <h3><?= $total_users ?></h3>
                            <p class="mb-0">Total Users</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stats-card">
                            <i class="fas fa-user-shield"></i>
                            <h3><?= $admin_users ?></h3>
                            <p class="mb-0">Admin Users</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stats-card">
                            <i class="fas fa-graduation-cap"></i>
                            <h3><?= $student_users ?></h3>
                            <p class="mb-0">Student Users</p>
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

                <?php if ($users->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Activity</th>
                                    <th>Member Since</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-2" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                                    <?= strtoupper(substr($user['name'], 0, 2)) ?>
                                                </div>
                                                <strong><?= htmlspecialchars($user['name']) ?></strong>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                    <span class="badge bg-info ms-2">You</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="badge <?= $user['role'] == 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <div><i class="fas fa-poll me-1"></i><?= $user['polls_created'] ?> polls</div>
                                                <div><i class="fas fa-vote-yea me-1"></i><?= $user['votes_cast'] ?> votes</div>
                                                <div><i class="fas fa-clipboard-list me-1"></i><?= $user['surveys_taken'] ?> surveys</div>
                                            </small>
                                        </td>
                                        <td><?= formatDate($user['created_at']) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                                            onclick="toggleUserRole(<?= $user['id'] ?>, '<?= $user['role'] ?>')">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                    <?php if ($user['role'] != 'admin'): ?>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this user? All their data will be permanently deleted.')">
                                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                            <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                        <i class="fas fa-user-shield"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h4>No users found</h4>
                        <p class="text-muted">There are no users in the system yet.</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Role Change Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change User Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="roleUserId">
                        <div class="mb-3">
                            <label for="new_role" class="form-label">Select New Role</label>
                            <select class="form-select" name="new_role" id="new_role" required>
                                <option value="student">Student</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> Changing a user's role will affect their access to system features.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="toggle_role" class="btn btn-warning">Change Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        function toggleUserRole(userId, currentRole) {
            document.getElementById('roleUserId').value = userId;
            document.getElementById('new_role').value = currentRole === 'admin' ? 'student' : 'admin';
            
            const modal = new bootstrap.Modal(document.getElementById('roleModal'));
            modal.show();
        }
    </script>
</body>
</html>
