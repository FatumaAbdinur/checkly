<?php
// Start the session
session_start();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .success-message {
      color: green;
      font-size: 1rem;
      text-align: center;
      margin-bottom: 15px;
    }
    .error-message {
      color: red;
      font-size: 0.9rem;
      text-align: center;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="login-form">
    <form id="loginForm" action="process_login.php" method="POST">
      <h1>Login</h1>
      <div id="success-message" class="success-message">
        <?php
        // Display the success message from the session, if it exists
        if (isset($_SESSION['success'])) {
            echo htmlspecialchars($_SESSION['success']);
            // Clear the success message after displaying it
            unset($_SESSION['success']);
        }
        ?>
      </div>
      <div class="error-message">
        <?php
        // Display errors from the session, if any
        if (isset($_SESSION['errors'])) {
            foreach ($_SESSION['errors'] as $error) {
                echo htmlspecialchars($error) . "<br>";
            }
            // Clear the errors after displaying them
            unset($_SESSION['errors']);
        }
        ?>
      </div>
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" placeholder="Enter Username"><br>
      <span class="error-message" id="username-error"></span>
      <label for="password">Password:</label>
      <div class="password-container">
        <input type="password" id="password" name="password" placeholder="Enter password"><br><br>
        <span class="toggle-password">👁‍🗨</span>
      </div>
      <span class="error-message" id="password-error"></span><br>
      <button type="submit">Login</button><br>
      <p style="text-align: center;">Don't have an account? <a href="Signup.html">Sign up here</a></p>
    </form>
  </div>
  <script src="Login.js"></script>
</body>
</html>