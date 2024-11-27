<?php
session_start();
include("../includes/config.php");

// Check if the user is already logged in, if so, redirect to the homepage
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role']; // Capture the role selection

    // Query to check if the user exists based on the email
    $query = "SELECT user_id, email, password, role FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $email, $hashed_password, $role_from_db);

    if ($stmt->num_rows === 1) {
        $stmt->fetch();

        // Check if the role is not inactive
        if ($role_from_db === 'inactive') {
            $message = "Your account is deactivated. Please contact the administrator.";
        } else {
            // Check if the password is correct
            if (password_verify($password, $hashed_password)) {

                // If the user is not an admin but tries to log in as admin, show an error
                if ($role === 'admin' && $role_from_db !== 'admin') {
                    $message = "Access Denied: You are not authorized to log in as an admin.";
                } else {
                    // Update the last login field for the user
                    $sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
                    $update_stmt = $conn->prepare($sql);
                    $update_stmt->bind_param('i', $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();

                    // Start the session and store user data if the login is successful
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $role_from_db;

                    // Check the user's role and redirect accordingly
                    if ($role_from_db === 'admin') {
                        header("Location: ../item/index.php"); // Redirect to item/index.php for admin
                    } else {
                        header("Location: ../index.php"); // Redirect to the home page for non-admin users
                    }
                    exit();
                }
            } else {
                $message = "Wrong password. Please try again.";
            }
        }
    } else {
        $message = "No account found with that email address.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <style>
     body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #ffffff, #b3d1f2, #a3a1f7, #6A1B9A); /* Soft gradient with light blue, indigo, and purple */
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
  box-sizing: border-box; /* Ensures padding doesn't overflow the container */
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

.signup-form input,
.signup-form select {
  width: calc(100% - 24px); /* Adjust width to account for padding */
  padding: 12px;
  font-size: 1rem;
  border-radius: 10px;
  border: 1px solid #ddd;
  background-color: #f9f9f9;
  transition: all 0.3s ease;
  margin: 0 auto; /* Center input fields and select */
  box-sizing: border-box; /* Include padding and border in width calculation */
}

.signup-form input:focus,
.signup-form select:focus {
  border-color: #6A1B9A;
  outline: none;
  background-color: #f0f0f0;
}

.signup-form input::placeholder
{
  color: #aaa;
}
.signup-form select {
  color: #6A1B9A;
}

.signup-form button {
  width: calc(100% - 24px); /* Adjust width to account for padding */
  padding: 14px;
  background-color: #6A1B9A;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1.2rem;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
  margin: 0 auto; /* Center the button */
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
  border-top: 2px solid #ffffff50; /* Optional: Subtle border for a polished look */
}

.footer p {
  margin: 5px 0;
}
  </style>
</head>
<body>

  <h1>ROMEROS Fragrance</h1>

  <div class="container">
    <h2>Login</h2>

    <!-- Display error message if it exists -->
    <?php if (isset($message)): ?>
      <div class="error-message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="signup-form">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      
      <!-- Role dropdown (only admin or user) -->
      <select name="role" required>
        <option value="user">User</option>
        <option value="admin">Admin</option>
      </select>

      <button type="submit">Sign In</button>
    </form>

    <p class="login-link">Not a member? <a href="register.php">Register</a></p>
  </div>

  <div class="footer">
    <p>&copy; 2024 Romeros Fragrance. All rights reserved.</p>
  </div>

</body>
</html>
