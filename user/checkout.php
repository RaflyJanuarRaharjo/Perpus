<?php
$page_title = "Checkout";
require_once '../config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect("login.php");
}

$user_id = $_SESSION["user_id"];
$success_message = "";
$error_message = "";

// Get cart items
$sql = "SELECT c.*, b.title, b.author, b.image, b.status, b.id as book_id FROM cart c 
        JOIN books b ON c.book_id = b.id 
        WHERE c.user_id = ? 
        ORDER BY c.added_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
    }
}

// Check if cart is empty
if (empty($cart_items)) {
    redirect("cart.php");
}

// Check if any book is not available
foreach ($cart_items as $item) {
    if ($item['status'] != 'available') {
        $error_message = "Some books in your cart are not available. Please remove them before proceeding.";
        break;
    }
}

// Process checkout
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error_message)) {
    $pickup_location = sanitize($_POST["pickup_location"]);
    
    if (empty($pickup_location)) {
        $error_message = "Pickup location is required.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert borrowings
            foreach ($cart_items as $item) {
                $book_id = $item['book_id'];
                
                // Insert borrowing
                $sql = "INSERT INTO borrowings (user_id, book_id, pickup_location) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iis", $user_id, $book_id, $pickup_location);
                $stmt->execute();
                
                // Update book status
                $sql = "UPDATE books SET status = 'borrowed' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $book_id);
                $stmt->execute();
            }
            
            // Clear cart
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to borrowings page
            redirect("borrowings.php?success=checkout");
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            $error_message = "Checkout failed. Please try again.";
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Checkout</h1>
    <a href="cart.php" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Cart
    </a>
</div>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Books to Borrow</h5>
            </div>
            <div class="card-body">
                <?php foreach ($cart_items as $item): ?>
                <div class="d-flex mb-3 pb-3 border-bottom">
                    <img src="../uploads/books/<?php echo $item['image']; ?>" class="img-thumbnail me-3" alt="<?php echo $item['title']; ?>" style="width: 80px; height: 100px; object-fit: cover;" onerror="this.src='../assets/img/book-placeholder.png'">
                    <div>
                        <h6 class="mb-1"><?php echo $item['title']; ?></h6>
                        <p class="text-muted mb-1"><?php echo $item['author']; ?></p>
                        <span class="badge <?php echo $item['status'] == 'available' ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo ucfirst($item['status']); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Borrowing Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <label for="pickup_location" class="form-label">Pickup Location</label>
                        <textarea class="form-control" id="pickup_location" name="pickup_location" rows="3" required></textarea>
                        <small class="text-muted">Enter the address where you want to pick up the books.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Borrowing Period</label>
                        <p class="text-muted">Books can be borrowed for a maximum of 3 days.</p>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100" <?php echo !empty($error_message) ? 'disabled' : ''; ?>>
                        <i class="fas fa-check"></i> Confirm Borrowing
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
include '../includes/user_navbar.php';
include '../includes/footer.php'; 
?>
