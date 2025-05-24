<?php
// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="mobile-navbar d-flex justify-content-around">
    <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="search.php" class="<?php echo $current_page == 'search.php' ? 'active' : ''; ?>">
        <i class="fas fa-search"></i>
        <span>Search</span>
    </a>
    <a href="cart.php" class="<?php echo $current_page == 'cart.php' ? 'active' : ''; ?>">
        <i class="fas fa-shopping-cart"></i>
        <span>Cart</span>
    </a>
    <a href="borrowings.php" class="<?php echo $current_page == 'borrowings.php' ? 'active' : ''; ?>">
        <i class="fas fa-book-reader"></i>
        <span>Borrowings</span>
    </a>
    <a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
        <i class="fas fa-user"></i>
        <span>Profile</span>
    </a>
</nav>
