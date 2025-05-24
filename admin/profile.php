<?php
$page_title = "Admin Profile";
require_once '../config/init.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect("login.php");
}

$admin_id = $_SESSION["admin_id"];
$success_message = "";
$error_message = "";

// Get admin details
$sql = "SELECT * FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Process logout
if (isset($_POST['logout'])) {
    // Destroy session
    session_destroy();
    redirect("login.php");
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Admin Profile</h1>
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
            <div class="col-md-9"><?php echo $admin['name']; ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3 fw-bold">Email</div>
            <div class="col-md-9"><?php echo $admin['email']; ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3 fw-bold">Admin Since</div>
            <div class="col-md-9"><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></div>
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
include '../includes/admin_navbar.php';
include '../includes/footer.php'; 
?>
