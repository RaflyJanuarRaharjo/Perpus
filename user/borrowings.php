<?php
$page_title = "My Borrowings";
require_once '../config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect("login.php");
}

$user_id = $_SESSION["user_id"];
$success_message = "";

// Check for success message from URL
if (isset($_GET['success']) && $_GET['success'] == 'checkout') {
    $success_message = "Borrowing successful! You can now pick up your books.";
}

// Get user's borrowings
$sql = "SELECT b.*, bk.title, bk.author, bk.image FROM borrowings b 
        JOIN books bk ON b.book_id = bk.id 
        WHERE b.user_id = ? 
        ORDER BY b.borrow_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$borrowings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $borrowings[] = $row;
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">My Borrowings</h1>
</div>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($borrowings)): ?>
            <p class="text-center">You have no borrowings yet.</p>
            <div class="text-center">
                <a href="search.php" class="btn btn-primary">Browse Books</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Borrow Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrowings as $borrowing): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="../uploads/books/<?php echo $borrowing['image']; ?>" class="img-thumbnail me-2" alt="<?php echo $borrowing['title']; ?>" style="width: 50px; height: 70px; object-fit: cover;" onerror="this.src='../assets/img/book-placeholder.png'">
                                    <div>
                                        <h6 class="mb-0"><?php echo $borrowing['title']; ?></h6>
                                        <small class="text-muted"><?php echo $borrowing['author']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?></td>
                            <td>
                                <?php if ($borrowing['status'] == 'pending'): ?>
                                    <span class="badge bg-warning">Pending Pickup</span>
                                <?php elseif ($borrowing['status'] == 'picked_up'): ?>
                                    <span class="badge bg-info">Borrowed</span>
                                <?php elseif ($borrowing['status'] == 'returned'): ?>
                                    <span class="badge bg-success">Returned</span>
                                <?php elseif ($borrowing['status'] == 'canceled'): ?>
                                    <span class="badge bg-danger">Canceled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="borrowing_detail.php?id=<?php echo $borrowing['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
include '../includes/user_navbar.php';
include '../includes/footer.php'; 
?>
