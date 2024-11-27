<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");

// Check if form data was submitted
if (isset($_POST['email'], $_POST['password'], $_POST['confirmPass'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPass = trim($_POST['confirmPass']);

    // Check if passwords match
    if ($password !== $confirmPass) {
        $_SESSION['message'] = 'Passwords do not match';
        header("Location: register.php");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = 'Invalid email format';
        header("Location: register.php");
        exit();
    }

    // Check if email is already in use
    $checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    if ($checkEmail === false) {
        $_SESSION['message'] = 'Database error: ' . $conn->error;
        header("Location: register.php");
        exit();
    }
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();

    if ($checkEmail->num_rows > 0) {
        $_SESSION['message'] = 'Email already in use';
        $checkEmail->close();
        header("Location: register.php");
        exit();
    }
    $checkEmail->close();

    // Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the database without specifying the primary key (auto-increment)
    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    if ($stmt === false) {
        $_SESSION['message'] = 'Database error: ' . $conn->error;
        header("Location: register.php");
        exit();
    }
    $stmt->bind_param("ss", $email, $hashedPassword);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;  // Store user ID in session
        $_SESSION['message'] = 'Registration successful!';
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['message'] = 'Registration error: ' . $stmt->error;
        header("Location: register.php");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // If form fields were not submitted, show an error message
    $_SESSION['message'] = 'Please fill in all fields';
    header("Location: register.php");
    exit();
}
