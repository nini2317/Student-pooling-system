<?php
// Include config file
require_once 'config.php';

// Function to sanitize input
function sanitize($data) {
    global $conn;
    return htmlspecialchars(trim($conn->real_escape_string($data)));
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to display alert messages
function alert($message, $type = 'danger') {
    return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Function to get user data
function getUserData($user_id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Function to check if user has voted on a poll
function hasVoted($user_id, $poll_id) {
    global $conn;
    $sql = "SELECT id FROM votes WHERE user_id = ? AND poll_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $poll_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Function to get poll results
function getPollResults($poll_id) {
    global $conn;
    $sql = "SELECT po.option_text, COUNT(v.id) as votes 
            FROM poll_options po 
            LEFT JOIN votes v ON po.id = v.option_id 
            WHERE po.poll_id = ? 
            GROUP BY po.id, po.option_text 
            ORDER BY po.id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Function to get total votes for a poll
function getTotalVotes($poll_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM votes WHERE poll_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'];
}

// Function to format date
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('M j, Y');
}

// Function to format time
function formatTime($dateString) {
    $date = new DateTime($dateString);
    return $date->format('g:i A');
}
?>
