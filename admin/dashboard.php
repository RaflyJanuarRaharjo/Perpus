<?php
$page_title = "Admin Dashboard";
require_once '../config/init.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect("login.php");
}

// Get statistics
// Total books
$sql = "SELECT COUNT(*) as total FROM books";
$result = $conn->query($sql);
$total_books = $result->fetch_assoc()['total'];

// Available books
$sql = "SELECT COUNT(*) as total FROM books WHERE status = 'available'";
$result = $conn->query($sql);
$available_books = $result->fetch_assoc()['total'];

// Borrowed books
$sql = "SELECT COUNT(*) as total FROM books WHERE status = 'borrowed'";
$result = $conn->query($sql);
$borrowed_books = $result->fetch_assoc()['total'];

// Total users
$sql = "SELECT COUNT(*) as total FROM users";
$result = $conn->query($sql);
$total_users = $result->fetch_assoc()['total'];

// Active borrowings
$sql = "SELECT COUNT(*) as total FROM borrowings WHERE status != 'returned'";
$result = $conn->query($sql);
$active_borrowings = $result->fetch_assoc()['total'];

// Recent borrowings
$sql = "SELECT b.*, u.name as user_name, bk.title as book_title 
        FROM borrowings b 
        JOIN users u ON b.user_id = u.id 
        JOIN books bk ON b.book_id = bk.id 
        ORDER BY b.borrow_date DESC LIMIT 5";
$result = $conn->query($sql);
$recent_borrowings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recent_borrowings[] = $row;
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Welcome, <?php echo $_SESSION["admin_name"]; ?></h1>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Books</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_books; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Available Books</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $available_books; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Borrowed Books</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $borrowed_books; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book-reader fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card border-left-danger h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Active Borrowings</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $active_borrowings; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Recent Borrowings</h5>
    </div>
    <div class="card-body">
        <?php if (empty($recent_borrowings)): ?>
            <p class="text-center">No recent borrowings.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Book</th>
                            <th>Borrow Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_borrowings as $borrowing): ?>
                        <tr>
                            <td><?php echo $borrowing['user_name']; ?></td>
                            <td><?php echo $borrowing['book_title']; ?></td>
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
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <a href="borrowings.php" class="btn btn-primary btn-sm">View All Borrowings</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
include '../includes/admin_navbar.php';
include '../includes/footer.php'; 
?>
