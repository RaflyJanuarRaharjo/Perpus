<?php
$page_title = "Manage Borrowings";
require_once '../config/init.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect("login.php");
}

// Get all borrowings
$sql = "SELECT b.*, u.name as user_name, bk.title as book_title 
        FROM borrowings b 
        JOIN users u ON b.user_id = u.id 
        JOIN books bk ON b.book_id = bk.id 
        ORDER BY b.borrow_date DESC";
$result = $conn->query($sql);
$borrowings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $borrowings[] = $row;
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Manage Borrowings</h1>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($borrowings)): ?>
            <p class="text-center">No borrowings found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
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
                            <td><?php echo $borrowing['user_name']; ?></td>
                            <td><?php echo $borrowing['book_title']; ?></td>
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

<?php 
include '../includes/admin_navbar.php';
include '../includes/footer.php'; 
?>
