<?php
if (!isset($_SESSION["user_id"])) {
    $loggedIn = false;
} else {
    $loggedIn = true;
}

$loggedIn = isset($_SESSION["user_id"]);
$isAdmin = ($loggedIn && $_SESSION["user_id"] == 5);

$currentPage = basename(filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_SPECIAL_CHARS));
?>

<!-- NAVBAR -->
<nav>
    <div class="nav-left">
        <a href="search.php"><img src="pictures/logo.png" class="logo" alt="YumGuide Logo"></a>
    </div>
    <a href="search.php" class="<?php echo $currentPage === 'search.php' ? 'active' : ''; ?>">Search</a> 
    <a href="products.php" class="<?php echo $currentPage === 'products.php' ? 'active' : ''; ?>">Products</a>
    
    <?php if ($loggedIn): ?>
        <a href="add_product.php" class="<?php echo $currentPage === 'add_product.php' ? 'active' : ''; ?>">Add New</a>
        <a href="account.php" class="<?php echo $currentPage === 'account.php' ? 'active' : ''; ?>">Account</a>
        
            <?php if ($isAdmin): ?>
            <a href="admin_review.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_review.php' ? 'active' : '' ?>">Admin</a>
            <?php endif; ?>
        
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="index.php">Login</a>
    <?php endif; ?>
</nav>

<style>
    nav {
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        width: 100%;
        height: 70px;
        top: 0;
        left: 0;
        background-color: #4682A9;
        z-index: 1000;
        text-align: center;
    }
    
    .nav-left  {
        height: 100%;
    }
    
    img.logo {
        height: 50px;
    }


    nav a {
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: white;
        text-decoration: none;
        padding: 0px 30px;
        margin: 0;
    }
    nav a.active {
        text-decoration: underline;
        background-color: #91C8E4;
    }
    nav a:hover {
        background-color: #91C8E4;  
    }
</style>


