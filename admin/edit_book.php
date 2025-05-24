<?php
$page_title = "Edit Book";
require_once '../config/init.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect("login.php");
}

// Check if book ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect("books.php");
}

$book_id = intval($_GET['id']);
$success_message = "";
$error_message = "";

// Get book details
$sql = "SELECT * FROM books WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    redirect("books.php");
}

$book = $result->fetch_assoc();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = sanitize($_POST["title"]);
    $author = sanitize($_POST["author"]);
    $genre = sanitize($_POST["genre"]);
    $description = sanitize($_POST["description"]);
    
    // Validate input
    if (empty($title) || empty($author) || empty($genre) || empty($description)) {
        $error_message = "All fields are required";
    } else {
        // Handle image upload
        $image = $book["image"]; // Keep existing image by default
        
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $upload_result = uploadImage($_FILES["image"], "../uploads/books/");
            
            if ($upload_result["success"]) {
                $image = $upload_result["filename"];
            } else {
                $error_message = $upload_result["message"];
            }
        }
        
        if (empty($error_message)) {
            // Update book
            $sql = "UPDATE books SET title = ?, author = ?, genre = ?, description = ?, image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $title, $author, $genre, $description, $image, $book_id);
            
            if ($stmt->execute()) {
                $success_message = "Book updated successfully.";
                
                // Refresh book data
                $sql = "SELECT * FROM books WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $book_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $book = $result->fetch_assoc();
            } else {
                $error_message = "Failed to update book. Please try again.";
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Edit Book</h1>
    <a href="books.php" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Books
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
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $book_id; ?>" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-3 text-center mb-3">
                    <img src="../uploads/books/<?php echo $book['image']; ?>" class="img-fluid rounded mb-3" alt="<?php echo $book['title']; ?>" style="max-height: 200px;" onerror="this.src='../assets/img/book-placeholder.png'">
                    <div class="mb-3">
                        <label for="image" class="form-label">Change Cover Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg, image/png, image/jpg">
                        <small class="text-muted">Upload a new cover image (optional). Max size: 5MB. Allowed formats: JPG, JPEG, PNG.</small>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo $book['title']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="author" class="form-label">Author</label>
                        <input type="text" class="form-control" id="author" name="author" value="<?php echo $book['author']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="genre" class="form-label">Genre</label>
                        <input type="text" class="form-control" id="genre" name="genre" value="<?php echo $book['genre']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo $book['description']; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <p>
                            <span class="badge <?php echo $book['status'] == 'available' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo ucfirst($book['status']); ?>
                            </span>
                            <?php if ($book['status'] == 'borrowed'): ?>
                                <small class="text-muted ms-2">Status will automatically change when the book is returned.</small>
                            <?php endif; ?>
                        </p>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Book
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php 
include '../includes/admin_navbar.php';
include '../includes/footer.php'; 
?>
