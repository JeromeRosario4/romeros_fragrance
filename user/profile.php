<?php
include("../includes/header.php");
include("../includes/config.php");

// Define allowed image types and max size
$allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

if (isset($_POST['submit_profile'])) {
    // Profile information
    $lname = trim($_POST['lname'] ?? '');
    $fname = trim($_POST['fname'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $town = trim($_POST['town'] ?? '');
    $zipcode = trim($_POST['zipcode'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $userId = $_SESSION['user_id'];
    $errors = [];

    // Validate required fields
    if (empty($lname) || empty($fname) || empty($address) || empty($town) || empty($zipcode) || empty($phone)) {
        $errors['fields'] = 'Please fill in all required fields.';
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header("Location: profile.php");
        exit;
    }

    // Update or insert profile data
    $checkUser = mysqli_query($conn, "SELECT * FROM customer WHERE user_id = '$userId'");
    if (mysqli_num_rows($checkUser) > 0) {
        $sql = "UPDATE customer SET title=?, lname=?, fname=?, address=?, town=?, zipcode=?, phone=? WHERE user_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssssssi', $title, $lname, $fname, $address, $town, $zipcode, $phone, $userId);
    } else {
        $sql = "INSERT INTO customer (title, lname, fname, address, town, zipcode, phone, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssssssi', $title, $lname, $fname, $address, $town, $zipcode, $phone, $userId);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = 'Profile successfully updated.';
        header("Location: profile.php");
        exit;
    } else {
        $_SESSION['error'] = 'Database error: ' . mysqli_stmt_error($stmt);
        header("Location: profile.php");
        exit;
    }
}

if (isset($_POST['submit_image'])) {
    // Image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $imageType = $_FILES['profile_image']['type'];
        $imageSize = $_FILES['profile_image']['size'];
        $userId = $_SESSION['user_id'];
        $profileImagePath = null;
        $errors = [];

        // Validate image type and size
        if (!in_array($imageType, $allowedImageTypes)) {
            $errors['image'] = 'Only JPG, PNG, and GIF images are allowed.';
        } elseif ($imageSize > $maxFileSize) {
            $errors['image'] = 'The image file size should not exceed 5MB.';
        } else {
            // Process image upload
            $uploadDir = '../user/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid($userId . '_profile_') . '.' . $fileExtension;
            $profileImagePath = 'uploads/' . $newFileName; // Relative path for DB storage
            $uploadPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                $errors['image'] = 'Failed to upload the image.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header("Location: profile.php");
            exit;
        }

        // Update image in the database
        if (!empty($profileImagePath)) {
            $sql = "UPDATE customer SET profile_image=? WHERE user_id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'si', $profileImagePath, $userId);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = 'Profile image successfully updated.';
                header("Location: profile.php");
                exit;
            } else {
                $_SESSION['error'] = 'Database error: ' . mysqli_stmt_error($stmt);
                header("Location: profile.php");
                exit;
            }
        }
    }
}

// Fetch existing user profile
$userId = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT * FROM customer WHERE user_id = '$userId'");
$userData = mysqli_fetch_assoc($query);

// Default profile image
$profileImage = isset($userData['profile_image']) && !empty($userData['profile_image'])
    ? '../user/' . $userData['profile_image']
    : 'http://bootdey.com/img/Content/avatar/avatar1.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ROMEROS Profile</title>
  <link rel="stylesheet" href="../includes/style/style.css"> <!-- Include your stylesheet -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('profileImagePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
  </script>
</head>
<body>
  <header>
    <h1>Profile</h1>
  </header>

  <div class="container-xl px-4 mt-4">
    <?php
    if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success'>{$_SESSION['success']}</div>";
        unset($_SESSION['success']);
    }
    ?>
    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">Profile Picture</div>
                <div class="card-body text-center">
                    <img id="profileImagePreview" class="img-account-profile rounded-circle mb-2" src="<?php echo $profileImage; ?>" alt="Profile Picture">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                        <input class="form-control mt-2" type="file" name="profile_image" id="profileImageInput" accept=".jpg,.jpeg,.png,.gif" onchange="previewImage(event)">
                        <button class="btn btn-primary mt-3" type="submit" name="submit_image">Upload Image</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Account Details</div>
                <div class="card-body">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputFirstName">First name</label>
                                <input class="form-control" id="inputFirstName" type="text" placeholder="Enter your first name" name="fname" value="<?php echo $userData['fname'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputLastName">Last name</label>
                                <input class="form-control" id="inputLastName" type="text" placeholder="Enter your last name" name="lname" value="<?php echo $userData['lname'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputTitle">Title</label>
                                <input class="form-control" id="inputTitle" type="text" placeholder="Enter your title" name="title" value="<?php echo $userData['title'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputPhone">Phone</label>
                                <input class="form-control" id="inputPhone" type="text" placeholder="Enter your phone number" name="phone" value="<?php echo $userData['phone'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-12">
                                <label class="small mb-1" for="inputAddress">Address</label>
                                <input class="form-control" id="inputAddress" type="text" placeholder="Enter your address" name="address" value="<?php echo $userData['address'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputTown">Town</label>
                                <input class="form-control" id="inputTown" type="text" placeholder="Enter your town" name="town" value="<?php echo $userData['town'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputZipcode">Zipcode</label>
                                <input class="form-control" id="inputZipcode" type="text" placeholder="Enter your zipcode" name="zipcode" value="<?php echo $userData['zipcode'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit" name="submit_profile">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
  </div>
</body>
</html>
