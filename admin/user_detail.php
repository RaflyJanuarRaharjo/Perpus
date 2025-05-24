<?php
$page_title = "User Detail";
require_once '../config/init.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect("login.php");
}

// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect("users.php");
}

$user_id = intval($_GET['id']);

// Get user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect("users.php");
}

$user = $result->fetch_assoc();

// Get user's borrowings
$sql = "SELECT b.*, bk.title as book_title, bk.author as book_author 
        FROM borrowings b 
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
    <h1 class="h3">User Detail</h1>
    <a href="users.php" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Name</div>
                    <div class="col-md-8"><?php echo $user['name']; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Email</div>
                    <div class="col-md-8"><?php echo $user['email']; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Registered On</div>
                    <div class="col-md-8"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Borrowing History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($borrowings)): ?>
                    <p class="text-center">No borrowing history found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Borrow Date</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrowings as $borrowing): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <h6 class="mb-0"><?php echo $borrowing['book_title']; ?></h6>
                                            <small class="text-muted"><?php echo $borrowing['book_author']; ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?></td>
                                    <td>
                                        <?php echo $borrowing['return_date'] ? date('M d, Y', strtotime($borrowing['return_date'])) : '-'; ?>
                                    </td>
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
                                            <i class="fas fa-eye"></i> View
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
    </div>
</div>

<?php 
include '../includes/admin_navbar.php';
include '../includes/footer.php'; 
?>
