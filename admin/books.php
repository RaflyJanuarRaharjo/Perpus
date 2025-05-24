<?php
$page_title = "Manage Books";
require_once '../config/init.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect("login.php");
}

$success_message = "";
$error_message = "";

// Process delete book
if (isset($_POST['delete_book']) && isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);
    
    // Check if book is borrowed
    $sql = "SELECT status FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    
    if ($book['status'] == 'borrowed') {
        $error_message = "Cannot delete book that is currently borrowed.";
    } else {
        // Delete book
        $sql = "DELETE FROM books WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $book_id);
        
        if ($stmt->execute()) {
            $success_message = "Book deleted successfully.";
        } else {
            $error_message = "Failed to delete book. Please try again.";
        }
    }
}

// Get all books
$sql = "SELECT * FROM books ORDER BY title";
$result = $conn->query($sql);
$books = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Manage Books</h1>
    <a href="add_book.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Book
    </a>
</div>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($books)): ?>
            <p class="text-center">No books available.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Genre</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td>
                                <img src="../uploads/books/<?php echo $book['image']; ?>" class="img-thumbnail" alt="<?php echo $book['title']; ?>" style="width: 50px; height: 70px; object-fit: cover;" onerror="this.src='../assets/img/book-placeholder.png'">
                            </td>
                            <td><?php echo $book['title']; ?></td>
                            <td><?php echo $book['author']; ?></td>
                            <td><?php echo $book['genre']; ?></td>
                            <td>
                                <span class="badge <?php echo $book['status'] == 'available' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo ucfirst($book['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                        <button type="submit" name="delete_book" class="btn btn-sm btn-danger" <?php echo $book['status'] == 'borrowed' ? 'disabled' : ''; ?>>
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
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
