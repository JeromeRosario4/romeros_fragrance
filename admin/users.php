<?php
// Database connection and header include
include("../includes/config.php");
include("../includes/adminHeader.php");

// Function to update a user's role
function updateUserRole($conn, $user_id, $new_role) {
    $sql = "UPDATE users SET role = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $new_role, $user_id); // 's' for string, 'i' for integer
    if ($stmt->execute()) {
        echo "User with ID $user_id has been updated to role $new_role.<br>";
    } else {
        echo "Failed to update role for user with ID $user_id.<br>";
    }
}

// Function to deactivate a user (by updating their role to 'inactive')
function deactivateUserByStatus($conn, $user_id) {
    $sql = "UPDATE users SET role = 'inactive' WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id); // 'i' for integer
    if ($stmt->execute()) {
        echo "User with ID $user_id has been marked as inactive.<br>";
    } else {
        echo "Failed to deactivate user with ID $user_id.<br>";
    }
}

// Function to fetch users with their last login information
function fetchUsers($conn) {
    $sql = "SELECT user_id, email, role, last_login FROM users";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result(); // Get the result set
    return $result->fetch_all(MYSQLI_ASSOC); // Fetch all results as an associative array
}

// Example usage: Handle POST requests for actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $user_id = $_POST['user_id'];

    if ($action === 'deactivate') {
        deactivateUserByStatus($conn, $user_id);
    } elseif ($action === 'update_role') {
        $new_role = $_POST['role'];
        updateUserRole($conn, $user_id, $new_role);
    }
}

// Fetch users for display
$users = fetchUsers($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin User Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ffffff, #b3d1f2, #a3a1f7, #c3a6e5); /* Diagonal White, Light Blue, Light Indigo, Light Purple Gradient */
        }

        .container {
            width: 80%;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007bff; /* Blue header */
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        form {
            margin: 20px 0;
            text-align: center;
        }

        input[type="number"], select {
            padding: 6px;
            margin: 5px;
        }

        button {
    padding: 8px 16px;
    background-color: #6f42c1; /* Purple background */
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}

button:hover {
    background-color: #5a2a96; /* Darker purple on hover */
}


        #role-section {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin User Management</h1>

        <h2>Users List</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Last Login</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['user_id']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['last_login'] ?? 'N/A') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2>Manage User Actions</h2>
        <!-- Form for Actions -->
        <form method="post">
            <label for="user_id">User ID:</label>
            <input type="number" id="user_id" name="user_id" required><br>

            <label for="action">Action:</label>
            <select id="action" name="action" required>
                <option value="deactivate">Deactivate User</option>
                <option value="update_role">Update Role</option>
            </select><br>

            <div id="role-section" style="display: none;">
                <label for="role">New Role:</label>
                <select id="role" name="role">
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select><br>
            </div>

            <button type="submit">Submit</button>
        </form>

        <script>
            // Show or hide the role selection field based on the selected action
            document.getElementById('action').addEventListener('change', function () {
                const roleSection = document.getElementById('role-section');
                if (this.value === 'update_role') {
                    roleSection.style.display = 'block';
                } else {
                    roleSection.style.display = 'none';
                }
            });
        </script>
    </div>
</body>
</html>
