<?php
$page_title = "Edit Profile";
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

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize($_POST["name"]);
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Validate input
    if (empty($name)) {
        $error_message = "Name is required";
    } else {
        // Start with basic update
        $sql = "UPDATE users SET name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $name, $user_id);
        
        // If password change is requested
        if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
            // Verify current password
            if (!password_verify($current_password, $user["password"])) {
                $error_message = "Current password is incorrect";
            } elseif (empty($new_password)) {
                $error_message = "New password is required";
            } elseif (strlen($new_password) < 8) {
                $error_message = "New password must be at least 8 characters long";
            } elseif ($new_password !== $confirm_password) {
                $error_message = "New passwords do not match";
            } else {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update with new password
                $sql = "UPDATE users SET name = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $name, $hashed_password, $user_id);
            }
        }
        
        // Execute update if no errors
        if (empty($error_message)) {
            if ($stmt->execute()) {
                // Update session
                $_SESSION["user_name"] = $name;
                
                $success_message = "Profile updated successfully";
                
                // Refresh user data
                $sql = "SELECT * FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $error_message = "Failed to update profile. Please try again.";
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Edit Profile</h1>
    <a href="profile.php" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Profile
    </a>
</div>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Profile Information</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" value="<?php echo $user['email']; ?>" disabled>
                <small class="text-muted">Email cannot be changed.</small>
            </div>
            
            <hr class="my-4">
            
            <h5 class="mb-3">Change Password</h5>
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="current_password" name="current_password">
                <small class="text-muted">Leave blank if you don't want to change your password.</small>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password">
                <small class="text-muted">Password must be at least 8 characters long.</small>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>
</div>

<?php 
include '../includes/user_navbar.php';
include '../includes/footer.php'; 
?>
