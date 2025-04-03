<?php
session_start();
require_once 'connectdb.php';

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user from the database using username
    $query = "SELECT user_id, username, password FROM users WHERE username = ?";
    $stmt = $connect->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $stmt->close();
            // Redirect to profile page
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
    $stmt->close();
}

// Check for logout message
$logoutMsg = "";
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $logoutMsg = "You have been successfully logged out.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <form method="POST" onsubmit="return validateForm()">
            <h1>Login</h1>
            <?php if (isset($error)): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if (!empty($logoutMsg)): ?>
                <p class="success"><?= htmlspecialchars($logoutMsg) ?></p>
            <?php endif; ?>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter Username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"><br>
            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Enter password"><br><br>
                <span class="toggle-password">👁‍🗨</span>
            </div><br>
            <button type="submit">Login</button><br>
            <p style="text-align: center;">Don't have an account? <a href="Signup.php">Sign up here</a></p>
        </form>
    </div>
    <script src="Login.js"></script>
</body>
</html>