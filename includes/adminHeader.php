<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="includes/style/style.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <title>ROMEROS FRAGRANCE ADMIN</title>
    <style>
        .navbar {
            background-color: transparent;
        }
        .navbar-brand, .nav-link, .btn {
            color: black !important;
        }
        .navbar-brand:hover, .nav-link:hover, .btn:hover {
            color: #d6bbfc !important; /* Light purple hover */
        }
        .btn {
            border-color: white !important;
        }
        .btn:hover {
            border-color: #d6bbfc !important; /* Light purple hover */
        }
        .btn-outline-light {
            background-color: #6f42c1; /* Purple background */
            color: black; /* Black text/icon */
            border: none; /* Remove border */
        }
        .btn-outline-light:hover {
            background-color: #00008B; /* Blue background on hover */
            color: white; /* White text/icon */
            border: none; /* Keep no border on hover */
        }
        .btn-outline-light i {
            color: black; /* Black icon */
        }
        .btn-outline-light:hover i {
            color: white; /* White icon on hover */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin_dashboard.php">ROMEROS Fragrance Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="../item/index.php">Products</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../admin/admin_orders.php">Orders</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../admin/users.php">Users</a>
        </li>
      </ul>
      <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="d-flex">
        <input class="form-control me-2" type="search" placeholder="Search products..." aria-label="Search" name="search">
        <button class="btn btn-outline-light" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link text-danger" href="http://<?= $_SERVER['SERVER_NAME']; ?>/ROMEROS_FRAGRANCE/user/logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
</body>
</html>
