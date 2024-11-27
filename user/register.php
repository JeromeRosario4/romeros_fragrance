<?php
session_start();
include('../includes/config.php'); // Include the database connection file

// Initialize error message variable (add this line to prevent the warning)
$error_message = '';

// If the user is already logged in, redirect to the home page
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture form data
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // Confirm password field

    // Check if password and confirm password match
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if the email already exists in the database
        $query = "SELECT email FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email already exists
            $error_message = "This email is already registered! Please use a different email.";
        } else {
            // Hash the password before storing it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Set default role to 'user' (since no role dropdown is provided)
            $role = 'user';

            // Insert the new user into the database (using prepared statements to avoid SQL injection)
            $query = "INSERT INTO users (email, password, role) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $email, $hashed_password, $role);
            $stmt->execute();

            // Check if the insertion was successful
            if ($stmt->affected_rows > 0) {
                // Start a session and store user data
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;

                // Redirect to the user's profile page after successful registration
                header("Location: ../user/profile.php");
                exit();
            } else {
                $error_message = "Registration failed!";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #ffffff, #b3d1f2, #a3a1f7, #6A1B9A);
      color: black;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      overflow: hidden;
      flex-direction: column;
    }

    h1 {
      font-size: 3rem;
      color: #6A1B9A;
      text-align: center;
      margin-bottom: 40px;
    }

    .container {
      width: 100%;
      max-width: 420px;
      background-color: #ffffff;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      text-align: center;
      margin: 0 auto;
      box-sizing: border-box;
    }

    h2 {
      font-size: 1.8rem;
      margin-bottom: 20px;
      color: #333;
    }

    .signup-form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .signup-form input {
      width: calc(100% - 24px);  /* Adjust width to account for padding */
      padding: 12px;
      font-size: 1rem;
      border-radius: 10px;
      border: 1px solid #ddd;
      background-color: #f9f9f9;
      transition: all 0.3s ease;
      margin: 0 auto; /* Center input fields */
      box-sizing: border-box; /* Include padding and border in width calculation */
    }

    .signup-form input:focus {
      border-color: #6A1B9A;
      outline: none;
      background-color: #f0f0f0;
    }

    .signup-form input::placeholder {
      color: #aaa;
    }

    .signup-form button {
      width: calc(100% - 24px);  /* Adjust width to account for padding */
      padding: 14px;
      background-color: #6A1B9A;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1.2rem;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
      margin: 0 auto; /* Center button */
      box-sizing: border-box; /* Include padding and border in width calculation */
    }

    .signup-form button:hover {
      background-color: #8E6BCC;
      transform: scale(1.05);
    }

    .signup-form button:active {
      background-color: #6A1B9A;
      transform: scale(1);
    }

    .error-message {
      color: #FF5722;
      font-size: 1rem;
      margin-bottom: 20px;
    }

    .login-link {
      margin-top: 10px;
      font-size: 1.1rem;
      color: #6A1B9A;
    }

    .login-link a {
      text-decoration: none;
      font-weight: bold;
    }

    .login-link a:hover {
      text-decoration: underline;
    }

    .footer {
      color: black;
      text-align: center;
      padding: 20px;
      font-family: 'Poppins', sans-serif;
      font-size: 0.9rem;
      border-top: 2px solid #ffffff50;
    }

    .footer p {
      margin: 5px 0;
    }
  </style>
</head>
<body>

  <h1>ROMEROS Fragrance</h1>

  <div class="container">
    <h2>Create Account</h2>

    <!-- Display error message if it exists -->
    <?php if ($error_message != ''): ?>
      <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST" class="signup-form">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      <button type="submit">Sign Up</button>
    </form>

    <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
  </div>

  <div class="footer">
    <p>&copy; 2024 Romeros Fragrance. All rights reserved.</p>
  </div>

</body>
</html>



