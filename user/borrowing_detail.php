<?php
$page_title = "Borrowing Detail";
require_once '../config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect("login.php");
}

// Check if borrowing ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect("borrowings.php");
}

$borrowing_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Get borrowing details
$sql = "SELECT b.*, bk.title, bk.author, bk.image, bk.description FROM borrowings b 
        JOIN books bk ON b.book_id = bk.id 
        WHERE b.id = ? AND b.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $borrowing_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect("borrowings.php");
}

$borrowing = $result->fetch_assoc();

// Process confirm pickup
if (isset($_POST['confirm_pickup'])) {
    // Update borrowing status
    $sql = "UPDATE borrowings SET status = 'picked_up' WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $borrowing_id, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "Pickup confirmed successfully.";
        $borrowing['status'] = 'picked_up';
    } else {
        $error_message = "Failed to confirm pickup. Please try again.";
    }
}

// Process confirm return
if (isset($_POST['confirm_return'])) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update borrowing status
        $sql = "UPDATE borrowings SET status = 'returned', return_date = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $borrowing_id, $user_id);
        $stmt->execute();
        
        // Update book status
        $sql = "UPDATE books SET status = 'available' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $borrowing['book_id']);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        $success_message = "Return confirmed successfully.";
        $borrowing['status'] = 'returned';
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $error_message = "Failed to confirm return. Please try again.";
    }
}

// Process cancel pickup
if (isset($_POST['cancel_pickup'])) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update borrowing status to 'canceled'
        $sql = "UPDATE borrowings SET status = 'canceled' WHERE id = ? AND user_id = ? AND status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $borrowing_id, $user_id);
        $stmt->execute();
        
        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            // Update book status back to available
            $sql = "UPDATE books SET status = 'available' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $borrowing['book_id']);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            $success_message = "Pickup canceled successfully. The book is now available for others.";
            $borrowing['status'] = 'canceled';
        } else {
            // No rows affected, probably not in pending status
            $conn->rollback();
            $error_message = "Failed to cancel pickup. You can only cancel pickups that are in 'pending' status.";
        }
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $error_message = "Failed to cancel pickup. Please try again.";
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

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Borrowing Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 text-center mb-3 mb-md-0">
                <img src="../uploads/books/<?php echo $borrowing['image']; ?>" class="img-fluid rounded" alt="<?php echo $borrowing['title']; ?>" style="max-height: 300px;" onerror="this.src='../assets/img/book-placeholder.png'">
            </div>
            <div class="col-md-9">
                <h2 class="h4 mb-3"><?php echo $borrowing['title']; ?></h2>
                
                <div class="mb-3">
                    <p><strong>Author:</strong> <?php echo $borrowing['author']; ?></p>
                    <p><strong>Borrow Date:</strong> <?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?></p>
                    <?php if ($borrowing['return_date']): ?>
                        <p><strong>Return Date:</strong> <?php echo date('M d, Y', strtotime($borrowing['return_date'])); ?></p>
                    <?php endif; ?>
                    <p>
                        <strong>Status:</strong> 
                        <?php if ($borrowing['status'] == 'pending'): ?>
                            <span class="badge bg-warning">Pending Pickup</span>
                        <?php elseif ($borrowing['status'] == 'picked_up'): ?>
                            <span class="badge bg-info">Borrowed</span>
                        <?php elseif ($borrowing['status'] == 'returned'): ?>
                            <span class="badge bg-success">Returned</span>
                        <?php elseif ($borrowing['status'] == 'canceled'): ?>
                            <span class="badge bg-danger">Canceled</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Pickup Location:</strong> <?php echo $borrowing['pickup_location']; ?></p>
                </div>
                
                <div class="mb-3">
                    <h5>Book Description</h5>
                    <p><?php echo nl2br($borrowing['description']); ?></p>
                </div>
                
                <div class="d-flex gap-2">
                    <?php if ($borrowing['status'] == 'pending'): ?>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to confirm pickup?');">
                            <button type="submit" name="confirm_pickup" class="btn btn-success">
                                <i class="fas fa-check"></i> Confirm Pickup
                            </button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this pickup? The book will be available for others.');">
                            <button type="submit" name="cancel_pickup" class="btn btn-danger">
                                <i class="fas fa-times"></i> Cancel Pickup
                            </button>
                        </form>
                    <?php elseif ($borrowing['status'] == 'picked_up'): ?>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to confirm return?');">
                            <button type="submit" name="confirm_return" class="btn btn-primary">
                                <i class="fas fa-undo"></i> Confirm Return
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="book_detail.php?id=<?php echo $borrowing['book_id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-book"></i> View Book
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include '../includes/user_navbar.php';
include '../includes/footer.php'; 
?>
