<?php
$page_title = "Cart";
require_once '../config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect("login.php");
}

$user_id = $_SESSION["user_id"];
$success_message = "";
$error_message = "";

// Process remove from cart
if (isset($_POST['remove_from_cart']) && isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);
    
    $sql = "DELETE FROM cart WHERE user_id = ? AND book_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $book_id);
    
    if ($stmt->execute()) {
        $success_message = "Book removed from cart successfully.";
    } else {
        $error_message = "Failed to remove book from cart. Please try again.";
    }
}

// Process clear cart
if (isset($_POST['clear_cart'])) {
    $sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $success_message = "Cart cleared successfully.";
    } else {
        $error_message = "Failed to clear cart. Please try again.";
    }
}

// Get cart items
$sql = "SELECT c.*, b.title, b.author, b.image, b.status FROM cart c 
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

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Your Cart</h1>
    <?php if (!empty($cart_items)): ?>
    <form method="POST" onsubmit="return confirm('Are you sure you want to clear your cart?');">
        <button type="submit" name="clear_cart" class="btn btn-danger btn-sm">
            <i class="fas fa-trash"></i> Clear Cart
        </button>
    </form>
    <?php endif; ?>
</div>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($cart_items)): ?>
            <p class="text-center">Your cart is empty.</p>
            <div class="text-center">
                <a href="search.php" class="btn btn-primary">Browse Books</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($cart_items as $item): ?>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="row g-0">
                            <div class="col-4">
                                <img src="../uploads/books/<?php echo $item['image']; ?>" class="img-fluid rounded-start h-100" alt="<?php echo $item['title']; ?>" onerror="this.src='../assets/img/book-placeholder.png'">
                            </div>
                            <div class="col-8">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $item['title']; ?></h5>
                                    <p class="card-text">
                                        <small class="text-muted"><?php echo $item['author']; ?></small><br>
                                        <small class="text-muted">
                                            Status: 
                                            <span class="badge <?php echo $item['status'] == 'available' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
                                        </small>
                                    </p>
                                    <div class="d-flex gap-2">
                                        <a href="book_detail.php?id=<?php echo $item['book_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to remove this book from your cart?');">
                                            <input type="hidden" name="book_id" value="<?php echo $item['book_id']; ?>">
                                            <button type="submit" name="remove_from_cart" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-4">
                <a href="checkout.php" class="btn btn-success">
                    <i class="fas fa-check"></i> Proceed to Checkout
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
include '../includes/user_navbar.php';
include '../includes/footer.php'; 
?>
