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
$user_name = $_SESSION['name'];

// Get available polls
$polls_sql = "SELECT p.*, po.option_text 
              FROM polls p 
              LEFT JOIN poll_options po ON p.id = po.poll_id 
              WHERE (p.expiry_date IS NULL OR p.expiry_date >= CURDATE() OR p.expiry_date = '0000-00-00')
              AND p.title != '' 
              AND p.id NOT IN (
                  SELECT poll_id FROM votes WHERE user_id = ?
              )
              GROUP BY p.id 
              ORDER BY p.created_at DESC";
$polls_stmt = $conn->prepare($polls_sql);
$polls_stmt->bind_param("i", $user_id);
$polls_stmt->execute();
$available_polls = $polls_stmt->get_result();

// Get surveys
$surveys_sql = "SELECT s.*, COUNT(sr.id) as response_count 
                FROM surveys s 
                LEFT JOIN survey_responses sr ON s.id = sr.survey_id 
                WHERE s.id NOT IN (
                    SELECT survey_id FROM survey_responses WHERE user_id = ?
                )
                GROUP BY s.id 
                ORDER BY s.created_at DESC";
$surveys_stmt = $conn->prepare($surveys_sql);
$surveys_stmt->bind_param("i", $user_id);
$surveys_stmt->execute();
$available_surveys = $surveys_stmt->get_result();

// Get poll results for polls user has voted on
$voted_polls_sql = "SELECT DISTINCT p.*, po.option_text, COUNT(v.id) as votes 
                   FROM polls p 
                   JOIN votes v ON p.id = v.poll_id 
                   JOIN poll_options po ON v.option_id = po.id 
                   WHERE v.user_id = ? 
                   GROUP BY p.id, po.id 
                   ORDER BY v.voted_at DESC";
$voted_polls_stmt = $conn->prepare($voted_polls_sql);
$voted_polls_stmt->bind_param("i", $user_id);
$voted_polls_stmt->execute();
$voted_polls = $voted_polls_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Polling System</title>
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
                            <?= strtoupper(substr($user_name, 0, 2)) ?>
                        </div>
                        <h6 class="fw-bold"><?= htmlspecialchars($user_name) ?></h6>
                        <small class="text-muted">Student</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#polls">
                                <i class="fas fa-poll me-2"></i>Participate in Polls
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#surveys">
                                <i class="fas fa-clipboard-list me-2"></i>Take Surveys
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#results">
                                <i class="fas fa-chart-bar me-2"></i>View Results
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user me-2"></i>Edit Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="feedback.php">
                                <i class="fas fa-comment me-2"></i>Submit Feedback
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
                    <h1 class="h2">Welcome, <?= htmlspecialchars($user_name) ?>! 👋</h1>
                </div>

                <!-- Dashboard Section -->
                <section id="dashboard" class="fade-in">
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-poll"></i>
                                <h3><?= $available_polls->num_rows ?></h3>
                                <p class="mb-0">Available Polls</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-clipboard-list"></i>
                                <h3><?= $available_surveys->num_rows ?></h3>
                                <p class="mb-0">Available Surveys</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-check-circle"></i>
                                <h3><?= $voted_polls->num_rows ?></h3>
                                <p class="mb-0">Completed Polls</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-trophy"></i>
                                <h3>Active</h3>
                                <p class="mb-0">Your Status</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Available Polls Section -->
                <section id="polls" class="mb-5 fade-in">
                    <h3 class="mb-4"><i class="fas fa-poll me-2"></i>Available Polls</h3>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <?= alert($_SESSION['error'], 'danger') ?>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <?= alert($_SESSION['success'], 'success') ?>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <?php if ($available_polls->num_rows > 0): ?>
                        <?php while ($poll = $available_polls->fetch_assoc()): ?>
                            <?php if (!empty($poll['title'])): // Skip polls with empty titles ?>
                            <div class="poll-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="mb-1"><?= htmlspecialchars($poll['title']) ?></h5>
                                        <span class="badge bg-primary"><?= htmlspecialchars($poll['category']) ?></span>
                                        <?php if ($poll['expiry_date'] && $poll['expiry_date'] != '0000-00-00'): ?>
                                            <small class="text-muted ms-2">Expires: <?= formatDate($poll['expiry_date']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <form method="POST" action="vote_poll.php" class="vote-form">
                                    <input type="hidden" name="poll_id" value="<?= $poll['id'] ?>">
                                    <div class="poll-options">
                                        <?php
                                        $options_sql = "SELECT * FROM poll_options WHERE poll_id = ?";
                                        $options_stmt = $conn->prepare($options_sql);
                                        $options_stmt->bind_param("i", $poll['id']);
                                        $options_stmt->execute();
                                        $options = $options_stmt->get_result();
                                        
                                        while ($option = $options->fetch_assoc()):
                                        ?>
                                            <div class="poll-option" onclick="selectPollOption(<?= $option['id'] ?>)">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="option_id" 
                                                           value="<?= $option['id'] ?>" id="option-<?= $option['id'] ?>">
                                                    <label class="form-check-label w-100" for="option-<?= $option['id'] ?>">
                                                        <?= htmlspecialchars($option['option_text']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-3">
                                        <i class="fas fa-vote-yea me-2"></i>Vote
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No available polls at the moment. Check back later!
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Available Surveys Section -->
                <section id="surveys" class="mb-5 fade-in">
                    <h3 class="mb-4"><i class="fas fa-clipboard-list me-2"></i>Available Surveys</h3>
                    <?php if ($available_surveys->num_rows > 0): ?>
                        <?php while ($survey = $available_surveys->fetch_assoc()): ?>
                            <div class="poll-card">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="mb-1"><?= htmlspecialchars($survey['title']) ?></h5>
                                        <p class="text-muted mb-2"><?= htmlspecialchars($survey['description']) ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i><?= $survey['response_count'] ?> responses
                                        </small>
                                    </div>
                                </div>
                                <a href="take_survey.php?id=<?= $survey['id'] ?>" class="btn btn-success">
                                    <i class="fas fa-edit me-2"></i>Take Survey
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No available surveys at the moment. Check back later!
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Results Section -->
                <section id="results" class="mb-5 fade-in">
                    <h3 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Poll Results</h3>
                    <?php if ($voted_polls->num_rows > 0): ?>
                        <?php 
                        $current_poll_id = null;
                        while ($poll = $voted_polls->fetch_assoc()): 
                            if ($current_poll_id != $poll['id']):
                                if ($current_poll_id !== null) echo '</div></div>';
                                $current_poll_id = $poll['id'];
                        ?>
                            <div class="poll-card mb-3">
                                <h5 class="mb-3"><?= htmlspecialchars($poll['title']) ?></h5>
                                <div class="poll-results">
                        <?php endif; ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span><?= htmlspecialchars($poll['option_text']) ?></span>
                                            <span><?= $poll['votes'] ?> votes</span>
                                        </div>
                                        <?php 
                                        $total_votes = getTotalVotes($poll['id']);
                                        $percentage = $total_votes > 0 ? ($poll['votes'] / $total_votes) * 100 : 0;
                                        ?>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?= number_format($percentage, 1) ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= number_format($percentage, 1) ?>%</small>
                                    </div>
                        <?php 
                        endwhile; 
                        if ($current_poll_id !== null) echo '</div></div>';
                        ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>You haven't participated in any polls yet. Start voting to see results here!
                        </div>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script>
        // Smooth scrolling for navigation links
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.sidebar .nav-link[href^="#"]');
            const sections = document.querySelectorAll('section[id]');
            
            // Smooth scrolling
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    const targetSection = document.getElementById(targetId);
                    
                    if (targetSection) {
                        // Update active state
                        navLinks.forEach(l => l.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Smooth scroll to section
                        targetSection.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        
                        // Close mobile sidebar if open
                        const sidebar = document.querySelector('.sidebar');
                        if (window.innerWidth <= 768 && sidebar.classList.contains('show')) {
                            sidebar.classList.remove('show');
                        }
                    }
                });
            });
            
            // Update active state on scroll
            function updateActiveSection() {
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.clientHeight;
                    if (window.pageYOffset >= sectionTop - 100) {
                        current = section.getAttribute('id');
                    }
                });
                
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + current) {
                        link.classList.add('active');
                    }
                });
            }
            
            // Listen for scroll events
            window.addEventListener('scroll', updateActiveSection);
            
            // Set initial active state
            updateActiveSection();
        });
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
