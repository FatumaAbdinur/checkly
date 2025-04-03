<?php
// Start the session
session_start();

// Include the database connection file
include 'db_connect.php';

// Initialize variables
$username = $password = "";
$errors = [];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate username
    $username = trim($_POST['username']);
    if (empty($username)) {
        $errors[] = "Username is required.";
    }

    // Sanitize and validate password
    $password = $_POST['password'];
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // If no validation errors, check the credentials
    if (empty($errors)) {
        // Prepare and execute the SQL statement to find the user
        $stmt = $connect->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a session for the user
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                // Redirect to dashboard.php
                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Invalid username or password.";
            }
        } else {
            $errors[] = "Invalid username or password.";
        }
        $stmt->close();
    }
}

// Close the database connection
$connect->close();

// If there are errors, store them in the session and redirect back to Login.php
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: Login.php");
    exit();
}
?>