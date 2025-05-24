<?php
$page_title = "Dashboard";
require_once '../config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect("login.php");
}

// Get recently added books
$sql = "SELECT * FROM books ORDER BY created_at DESC LIMIT 6";
$result = $conn->query($sql);
$recent_books = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recent_books[] = $row;
    }
}

// Get user's active borrowings
$user_id = $_SESSION["user_id"];
$sql = "SELECT b.*, bk.title, bk.image FROM borrowings b 
        JOIN books bk ON b.book_id = bk.id 
        WHERE b.user_id = ? AND b.status != 'returned' 
        ORDER BY b.borrow_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$active_borrowings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $active_borrowings[] = $row;
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Welcome, <?php echo $_SESSION["user_name"]; ?></h1>
</div>

<?php if (!empty($active_borrowings)): ?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Your Active Borrowings</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($active_borrowings as $borrowing): ?>
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="row g-0">
                        <div class="col-4">
                            <img src="../uploads/books/<?php echo $borrowing['image']; ?>" class="img-fluid rounded-start h-100" alt="<?php echo $borrowing['title']; ?>" onerror="this.src='../assets/img/book-placeholder.png'">
                        </div>
                        <div class="col-8">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $borrowing['title']; ?></h5>
                                <p class="card-text">
                                    <small class="text-muted">
                                        Status: 
                                        <?php if ($borrowing['status'] == 'pending'): ?>
                                            <span class="badge bg-warning">Pending Pickup</span>
                                        <?php elseif ($borrowing['status'] == 'picked_up'): ?>
                                            <span class="badge bg-info">Borrowed</span>
                                        <?php endif; ?>
                                    </small>
                                </p>
                                <a href="borrowing_detail.php?id=<?php echo $borrowing['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Recently Added Books</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php if (empty($recent_books)): ?>
                <div class="col-12">
                    <p class="text-center">No books available.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_books as $book): ?>
                <div class="col-6 col-md-4 col-lg-2 mb-3">
                    <div class="card book-card h-100">
                        <img src="../uploads/books/<?php echo $book['image']; ?>" class="card-img-top book-image" alt="<?php echo $book['title']; ?>" onerror="this.src='../assets/img/book-placeholder.png'">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $book['title']; ?></h6>
                            <p class="card-text">
                                <small class="text-muted"><?php echo $book['author']; ?></small>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge <?php echo $book['status'] == 'available' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo ucfirst($book['status']); ?>
                                </span>
                                <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary">View</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include '../includes/user_navbar.php';
include '../includes/footer.php'; 
?>
