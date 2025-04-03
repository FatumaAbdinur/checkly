<?php
// Start a session
session_start();

// Include the database connection file
include 'db_connect.php';

// Initialize variables for form data
$username = $email = $password = "";
$errors = [];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate username
    $username = trim($_POST['username']);
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (!preg_match("/^[A-Za-z]+$/", $username)) {
        $errors[] = "Username should only contain alphabetic characters.";
    }

    // Sanitize and validate email
    $email = trim($_POST['email']);
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } else {
        // Check if email already exists
        $stmt = $connect->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email is already registered.";
        }
        $stmt->close();
    }

    // Sanitize and validate password
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $errors[] = "Password must have at least 8 characters, including uppercase, lowercase, numbers, and special characters.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // If no errors, proceed to insert data into the database
    if (empty($errors)) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and execute the SQL statement
        $stmt = $connect->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            // Store the success message in a session variable
            $_SESSION['success'] = "Congratulations $username! You have successfully signed up.";
            // Redirect to login page
            header("Location: Login.php");
            exit();
        } else {
            $errors[] = "Error: Could not register user. Please try again later.";
        }
        $stmt->close();
    }
}

// Close the database connection
$connect->close();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign Up</title>
  <link rel="stylesheet" type="text/css" href="Styles.css">
  <style>
    .error-message {
      color: red;
      font-size: 0.9rem;
    }
    .success-message {
      color: green;
      font-size: 1rem;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="form login-form">
    <h1>Sign up</h1>
    <?php
    // Display errors if any
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p class='error-message'>$error</p>";
        }
    }
    ?>
    <form id="signupForm" action="signup.php" method="POST">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" placeholder="Enter Username" value="<?php echo htmlspecialchars($username); ?>">
      <span class="error-message" id="username-error"></span><br>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" placeholder="Enter Email" value="<?php echo htmlspecialchars($email); ?>">
      <span class="error-message" id="email-error"></span><br>

      <label for="password">Password:</label>
      <div class="password-container">
        <input type="password" id="password" name="password" placeholder="Enter password">
        <span class="toggle-password">👁‍🗨</span>
      </div>
      <span class="error-message" id="password-error"></span><br>

      <label for="confirm_password">Confirm Password:</label>
      <div class="password-container">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password">
        <span class="toggle-password">👁‍🗨</span>
      </div><br>
      <span class="error-message" id="confirm-password-error"></span>
      <button type="submit">Sign Up</button><br>
      <p style="text-align: center;">Already have an account? <a href="Login.php">Log in here</a></p>
    </form>
  </div>
  <script src="Signup.js"></script>
</body>
</html>