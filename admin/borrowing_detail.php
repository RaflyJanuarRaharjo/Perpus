<?php
$page_title = "Borrowing Detail";
require_once '../config/init.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect("login.php");
}

// Check if borrowing ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect("borrowings.php");
}

$borrowing_id = intval($_GET['id']);
$success_message = "";
$error_message = "";

// Get borrowing details
$sql = "SELECT b.*, u.name as user_name, u.email as user_email, 
        bk.title as book_title, bk.author as book_author, bk.image as book_image 
        FROM borrowings b 
        JOIN users u ON b.user_id = u.id 
        JOIN books bk ON b.book_id = bk.id 
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $borrowing_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect("borrowings.php");
}

$borrowing = $result->fetch_assoc();

// Update the process update status function to handle the canceled status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $status = sanitize($_POST['status']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update borrowing status
        $sql = "UPDATE borrowings SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $borrowing_id);
        $stmt->execute();
        
        // If status is returned or canceled, update book status to available
        if ($status == 'returned' || $status == 'canceled') {
            // Update book status
            $sql = "UPDATE books SET status = 'available' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $borrowing['book_id']);
            $stmt->execute();
            
            // Set return date if status is returned
            if ($status == 'returned') {
                $sql = "UPDATE borrowings SET return_date = CURRENT_TIMESTAMP WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $borrowing_id);
                $stmt->execute();
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        $success_message = "Borrowing status updated successfully.";
        
        // Refresh borrowing data
        $sql = "SELECT b.*, u.name as user_name, u.email as user_email, 
                bk.title as book_title, bk.author as book_author, bk.image as book_image 
                FROM borrowings b 
                JOIN users u ON b.user_id = u.id 
                JOIN books bk ON b.book_id = bk.id 
                WHERE b.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $borrowing_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $borrowing = $result->fetch_assoc();
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $error_message = "Failed to update borrowing status. Please try again.";
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Borrowing Detail</h1>
    <a href="borrowings.php" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Borrowings
    </a>
</div>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Borrowing Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Borrow Date</div>
                    <div class="col-md-8"><?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Return Date</div>
                    <div class="col-md-8">
                        <?php echo $borrowing['return_date'] ? date('M d, Y', strtotime($borrowing['return_date'])) : '-'; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Status</div>
                    <div class="col-md-8">
                        <?php if ($borrowing['status'] == 'pending'): ?>
                            <span class="badge bg-warning">Pending Pickup</span>
                        <?php elseif ($borrowing['status'] == 'picked_up'): ?>
                            <span class="badge bg-info">Borrowed</span>
                        <?php elseif ($borrowing['status'] == 'returned'): ?>
                            <span class="badge bg-success">Returned</span>
                        <?php elseif ($borrowing['status'] == 'canceled'): ?>
                            <span class="badge bg-danger">Canceled</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Pickup Location</div>
                    <div class="col-md-8"><?php echo $borrowing['pickup_location']; ?></div>
                </div>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $borrowing_id; ?>" class="mt-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Update Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="pending" <?php echo $borrowing['status'] == 'pending' ? 'selected' : ''; ?>>Pending Pickup</option>
                            <option value="picked_up" <?php echo $borrowing['status'] == 'picked_up' ? 'selected' : ''; ?>>Borrowed</option>
                            <option value="returned" <?php echo $borrowing['status'] == 'returned' ? 'selected' : ''; ?>>Returned</option>
                            <option value="canceled" <?php echo $borrowing['status'] == 'canceled' ? 'selected' : ''; ?>>Canceled</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Name</div>
                    <div class="col-md-8"><?php echo $borrowing['user_name']; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Email</div>
                    <div class="col-md-8"><?php echo $borrowing['user_email']; ?></div>
                </div>
                <a href="user_detail.php?id=<?php echo $borrowing['user_id']; ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-user"></i> View User Profile
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Book Information</h5>
            </div>
            <div class="card-body">
                <div class="d-flex mb-3">
                    <img src="../uploads/books/<?php echo $borrowing['book_image']; ?>" class="img-thumbnail me-3" alt="<?php echo $borrowing['book_title']; ?>" style="width: 80px; height: 100px; object-fit: cover;" onerror="this.src='../assets/img/book-placeholder.png'">
                    <div>
                        <h6 class="mb-1"><?php echo $borrowing['book_title']; ?></h6>
                        <p class="text-muted mb-0"><?php echo $borrowing['book_author']; ?></p>
                    </div>
                </div>
                <a href="edit_book.php?id=<?php echo $borrowing['book_id']; ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-book"></i> View Book Details
                </a>
            </div>
        </div>
    </div>
</div>

<?php 
include '../includes/admin_navbar.php';
include '../includes/footer.php'; 
?>
