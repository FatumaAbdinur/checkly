<?php
// Start the session
session_start();
include "connectdb.php";

// Ensure no output before headers
ob_start();

// Initialize error variables for each field
$username_error = $email_error = $dob_error = $password_error = $confirm_password_error = $general_error = "";

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize and validate input
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);
        $date_of_birth = filter_input(INPUT_POST, 'date_of_birth', FILTER_SANITIZE_STRING);

        // Validate inputs - check for empty fields
        if (empty($username)) {
            $username_error = "Username is required";
        }
        if (empty($email)) {
            $email_error = "Email is required";
        }
        if (empty($password)) {
            $password_error = "Password is required";
        }
        if (empty($confirm_password)) {
            $confirm_password_error = "Confirm Password is required";
        }
        if (empty($date_of_birth)) {
            $dob_error = "Date of Birth is required";
        }

        // If any field is empty, throw an exception to prevent further validation
        if ($username_error || $email_error || $password_error || $confirm_password_error || $dob_error) {
            throw new Exception("Please fill in all required fields");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_error = "Invalid email format";
            throw new Exception("Invalid email format");
        }

        // Validate date of birth format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_of_birth) || !strtotime($date_of_birth)) {
            $dob_error = "Invalid date of birth format. Use YYYY-MM-DD.";
            throw new Exception("Invalid date of birth format");
        }

        // Check if passwords match
        if ($password !== $confirm_password) {
            $confirm_password_error = "Passwords do not match";
            throw new Exception("Passwords do not match");
        }

        // Validate username (alphabetic characters only, as per your JS validation)
        if (!preg_match('/^[A-Za-z]+$/', $username)) {
            $username_error = "Username should only contain alphabetic characters";
            throw new Exception("Username should only contain alphabetic characters");
        }

        // Validate password (at least 8 characters, including uppercase, lowercase, numbers, and special characters)
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            $password_error = "Password must have at least 8 characters, including uppercase, lowercase, numbers, and special characters";
            throw new Exception("Password must have at least 8 characters, including uppercase, lowercase, numbers, and special characters");
        }

        // Check if username or email already exists
        $check_query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $check_stmt = mysqli_prepare($connect, $check_query);
        if ($check_stmt === false) {
            throw new Exception("Failed to prepare check statement: " . mysqli_error($connect));
        }
        mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($result) > 0) {
            $general_error = "Username or email already exists";
            throw new Exception("Username or email already exists");
        }
        mysqli_stmt_close($check_stmt);

        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Set created timestamp
        $created = date('Y-m-d H:i:s');

        // Set profile_picture to NULL since it's not provided during signup
        $profile_picture = NULL;

        // Prepare statement to insert user into the database
        $query = "INSERT INTO users (username, email, password, created, date_of_birth, profile_picture) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connect, $query);
        if ($stmt === false) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($connect));
        }

        // Bind parameters (all strings except profile_picture which can be NULL)
        mysqli_stmt_bind_param($stmt, "ssssss", $username, $email, $hashed_password, $created, $date_of_birth, $profile_picture);

        // Execute the query
        $success = mysqli_stmt_execute($stmt);
        if ($success) {
            // Close statement
            mysqli_stmt_close($stmt);
            // Clear the session to ensure the user isn't logged in
            session_unset(); // Clear all session variables
            session_destroy(); // Destroy the session
            ob_end_clean(); // Clear any output buffer before redirect
            header("Location: Login.php");
            exit();
        } else {
            throw new Exception("Failed to register user: " . mysqli_stmt_error($stmt));
        }

    } catch (Exception $e) {
        // Store the general error message if it's not already set by specific field errors
        if (empty($username_error) && empty($email_error) && empty($dob_error) && empty($password_error) && empty($confirm_password_error) && empty($general_error)) {
            $general_error = $e->getMessage();
        }
        ob_end_clean(); // Clear output buffer
    } finally {
        // Clean up resources
        if (isset($check_stmt) && $check_stmt !== false) {
            mysqli_stmt_close($check_stmt);
        }
        if (isset($stmt) && $stmt !== false) {
            mysqli_stmt_close($stmt);
        }
        mysqli_close($connect);
    }
}
ob_end_flush(); // Flush output buffer if no redirect
?>

<!-- Integrate with the existing signup form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Checkly</title>
    <link rel="stylesheet" href="Styles.css">
    <style>
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-container input[type="password"], .password-container input[type="text"] {
            width: 100%;
            padding-right: 40px;
        }
        .password-container .toggle-password {
            position: absolute;
            right: 10px;
            cursor: pointer;
        }
        .error-message {
            color: red;
            font-size: 0.9rem;
            display: block; /* Ensure the error message takes its own line */
            margin-top: 5px;
        }
        .general-error {
            color: red;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="form login-form">
        <h1>Sign up</h1>
        <!-- Display general errors (e.g., "Username or email already exists") above the form -->
        <?php if (!empty($general_error)): ?>
            <div class="general-error"><?php echo htmlspecialchars($general_error); ?></div>
        <?php endif; ?>
        <form id="signupForm" action="" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter Username" oninput="validateUsername()" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
            <span class="error-message" id="username-error"><?php echo $username_error; ?></span><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter Email" oninput="validateEmail()" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            <span class="error-message" id="email-error"><?php echo $email_error; ?></span><br>

            <label for="date_of_birth">Date of Birth:</label>
            <input type="date" id="date_of_birth" name="date_of_birth" oninput="validateDateOfBirth()" value="<?php echo isset($date_of_birth) ? htmlspecialchars($date_of_birth) : ''; ?>">
            <span class="error-message" id="date-of-birth-error"><?php echo $dob_error; ?></span><br>

            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Enter password" oninput="validatePassword()">
                <span class="toggle-password">👁‍🗨</span>
            </div>
            <span class="error-message" id="password-error"><?php echo $password_error; ?></span><br>

            <label for="confirm_password">Confirm Password:</label>
            <div class="password-container">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" oninput="validateConfirmPassword()">
                <span class="toggle-password">👁‍🗨</span>
            </div>
            <span class="error-message" id="confirm-password-error"><?php echo $confirm_password_error; ?></span><br>

            <button type="submit">Sign Up</button><br>
            <p style="text-align: center;">Already have an account? <a href="Login.php">Log in here</a></p>
        </form>
    </div>
    <script src="Login.js"></script>
    <script>
        function validateDateOfBirth() {
            const dobInput = document.getElementById('date_of_birth').value;
            const dobError = document.getElementById('date-of-birth-error');
            const dobPattern = /^\d{4}-\d{2}-\d{2}$/;
            if (!dobPattern.test(dobInput) || !Date.parse(dobInput)) {
                dobError.textContent = "Please enter a valid date (YYYY-MM-DD)";
            } else {
                dobError.textContent = "";
            }
        }
    </script>
</body>
</html>