<?php
$page_title = "Search Books";
require_once '../config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect("login.php");
}

// Initialize variables
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$genre = isset($_GET['genre']) ? sanitize($_GET['genre']) : '';
$books = [];

// Get all genres for filter
$sql = "SELECT DISTINCT genre FROM books ORDER BY genre";
$result = $conn->query($sql);
$genres = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $genres[] = $row['genre'];
    }
}

// Search books
if (!empty($search) || !empty($genre)) {
    $sql = "SELECT * FROM books WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $sql .= " AND (title LIKE ? OR author LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "ss";
    }
    
    if (!empty($genre)) {
        $sql .= " AND genre = ?";
        $params[] = $genre;
        $types .= "s";
    }
    
    $sql .= " ORDER BY title";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
} else {
    // Get all books if no search criteria
    $sql = "SELECT * FROM books ORDER BY title";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Search Books</h1>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search by title or author" name="search" value="<?php echo $search; ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select" name="genre" onchange="this.form.submit()">
                    <option value="">All Genres</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?php echo $g; ?>" <?php echo $genre == $g ? 'selected' : ''; ?>><?php echo $g; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <a href="search.php" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <?php 
            if (!empty($search) || !empty($genre)) {
                echo "Search Results";
                if (!empty($search)) echo " for \"$search\"";
                if (!empty($genre)) echo " in $genre";
            } else {
                echo "All Books";
            }
            ?>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($books)): ?>
            <p class="text-center">No books found.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($books as $book): ?>
                <div class="col-6 col-md-4 col-lg-3 mb-3">
                    <div class="card book-card h-100">
                        <img src="../uploads/books/<?php echo $book['image']; ?>" class="card-img-top book-image" alt="<?php echo $book['title']; ?>" onerror="this.src='../assets/img/book-placeholder.png'">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $book['title']; ?></h6>
                            <p class="card-text">
                                <small class="text-muted"><?php echo $book['author']; ?></small><br>
                                <small class="text-muted"><?php echo $book['genre']; ?></small>
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
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
include '../includes/user_navbar.php';
include '../includes/footer.php'; 
?>
