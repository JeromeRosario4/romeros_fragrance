<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="includes/style/style.css" rel="stylesheet" type="text/css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  <title>Romeros' Fragrance</title>
</head>

<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <!-- Logo and Name -->
        <a class="navbar-brand" href="#">
            <img src="item/images/romeroslogo-circle.png" alt="" class="logo" />
            <span class="ms-2">Romeros' Fragrance</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="../index.php">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Menu
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="../item/index.php">Item</a></li>
                                <li><a class="dropdown-item" href="../admin/admin_orders.php">Orders</a></li>
                                <li><a class="dropdown-item" href="../admin/users.php">Users</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="./user/profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="./user/myorders.php">My Orders</a></li>
                            <?php endif; ?>
                            <!-- Add View Cart option here -->
                            <li><a class="dropdown-item" href="view_cart.php">View Cart</a></li>
                        </ul>
                    <?php endif; ?>
                </li>
            </ul>
            <form action="search.php" method="GET" class="d-flex">
                <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" name="search">
                <button class="btn btn-blue my-2 my-sm-0" type="submit">
                     <i class="fas fa-search"></i>
                </button>


            </form>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="navbar-nav ms-auto">
                    <a href="http://<?= $_SERVER['SERVER_NAME']; ?>/ROMEROS_FRAGRANCE2/user/login.php" class="nav-item nav-link">Login</a>
                </div>
            <?php else: ?>
                <li class="nav-item">
                    <p><?= htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                </li>
                <div class="navbar-nav ms-auto">
                    <a href="http://<?= $_SERVER['SERVER_NAME']; ?>/ROMEROS_FRAGRANCE2/user/logout.php" class="nav-item nav-link">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>