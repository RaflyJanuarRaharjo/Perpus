<?php
$page_title = "Profile";
require_once '../config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect("login.php");
}

$user_id = $_SESSION["user_id"];
$success_message = "";
$error_message = "";

// Get user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Process logout
if (isset($_POST['logout'])) {
    // Destroy session
    session_destroy();
    redirect("login.php");
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">My Profile</h1>
</div>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Profile Information</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3 fw-bold">Name</div>
            <div class="col-md-9"><?php echo $user['name']; ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3 fw-bold">Email</div>
            <div class="col-md-9"><?php echo $user['email']; ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3 fw-bold">Member Since</div>
            <div class="col-md-9"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
        </div>
        
        <div class="d-flex gap-2 mt-4">
            <a href="edit_profile.php" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
            <form method="POST" onsubmit="return confirm('Are you sure you want to logout?');">
                <button type="submit" name="logout" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>

<?php 
include '../includes/user_navbar.php';
include '../includes/footer.php'; 
?>
