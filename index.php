<?php
require_once 'config/init.php';

// Redirect to appropriate page based on login status
if (isLoggedIn()) {
    redirect("user/dashboard.php");
} elseif (isAdminLoggedIn()) {
    redirect("admin/dashboard.php");
} else {
    redirect("user/login.php");
}
?>
