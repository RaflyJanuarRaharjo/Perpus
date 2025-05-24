<?php
$page_title = "Add Book";
require_once '../config/init.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect("login.php");
}

$success_message = "";
$error_message = "";

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
        $image = "default.jpg"; // Default image
        
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $upload_result = uploadImage($_FILES["image"], "../uploads/books/");
            
            if ($upload_result["success"]) {
                $image = $upload_result["filename"];
            } else {
                $error_message = $upload_result["message"];
            }
        }
        
        if (empty($error_message)) {
            // Insert new book
            $sql = "INSERT INTO books (title, author, genre, description, image) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $title, $author, $genre, $description, $image);
            
            if ($stmt->execute()) {
                $success_message = "Book added successfully.";
                
                // Clear form data
                $title = "";
                $author = "";
                $genre = "";
                $description = "";
            } else {
                $error_message = "Failed to add book. Please try again.";
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Add New Book</h1>
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
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($title) ? $title : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Author</label>
                <input type="text" class="form-control" id="author" name="author" value="<?php echo isset($author) ? $author : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="genre" class="form-label">Genre</label>
                <input type="text" class="form-control" id="genre" name="genre" value="<?php echo isset($genre) ? $genre : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo isset($description) ? $description : ''; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Book Cover Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/jpeg, image/png, image/jpg">
                <small class="text-muted">Upload a cover image for the book (optional). Max size: 5MB. Allowed formats: JPG, JPEG, PNG.</small>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Add Book
            </button>
        </form>
    </div>
</div>

<?php 
include '../includes/admin_navbar.php';
include '../includes/footer.php'; 
?>
