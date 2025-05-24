<?php
// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="mobile-navbar d-flex justify-content-around">
    <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="books.php" class="<?php echo $current_page == 'books.php' ? 'active' : ''; ?>">
        <i class="fas fa-book"></i>
        <span>Books</span>
    </a>
    <a href="borrowings.php" class="<?php echo $current_page == 'borrowings.php' ? 'active' : ''; ?>">
        <i class="fas fa-book-reader"></i>
        <span>Borrowings</span>
    </a>
    <a href="users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
        <i class="fas fa-users"></i>
        <span>Users</span>
    </a>
    <a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
        <i class="fas fa-user"></i>
        <span>Profile</span>
    </a>
</nav>
