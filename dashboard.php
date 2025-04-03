<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to Login.php
    header("Location: Login.php");
    exit();
}

// Include the database connection file
include 'db_connect.php';

// Check if the database connection is valid
if (!$connect || $connect->connect_error) {
    die("Database connection failed: " . $connect->connect_error);
}

// Fetch the user's current information
$user_id = $_SESSION['user_id'];
if (!isset($user_id) || !is_numeric($user_id)) {
    die("Invalid user ID");
}

// Initialize variables for success/error messages
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? null;

    // Validate username
    if (empty($username)) {
        $error_message = "Username is required.";
    } else {
        // Handle profile picture upload
        $profile_picture = null;
        $stmt = $connect->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_user = $result->fetch_assoc();
        $stmt->close();

        $profile_picture = $current_user['profile_picture']; // Keep existing picture by default

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            // Create uploads directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = time() . '_' . basename($_FILES['profile_picture']['name']);
            $target_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                $profile_picture = $target_path;
            } else {
                $error_message = "Failed to upload profile picture.";
            }
        }

        // If no errors, update the database
        if (empty($error_message)) {
            $stmt = $connect->prepare("UPDATE users SET username = ?, date_of_birth = ?, profile_picture = ? WHERE user_id = ?");
            if ($stmt === false) {
                die("Prepare failed: " . $connect->error);
            }
            // Bind parameters (date_of_birth can be null)
            $stmt->bind_param("sssi", $username, $date_of_birth, $profile_picture, $user_id);
            if ($stmt->execute()) {
                $success_message = "Profile successfully updated.";
            } else {
                $error_message = "Failed to update profile: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Fetch updated user data after submission
    $stmt = $connect->prepare("SELECT username, email, created_at, profile_picture, date_of_birth FROM users WHERE user_id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $connect->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
} else {
    // Fetch user data on initial page load
    $stmt = $connect->prepare("SELECT username, email, created_at, profile_picture, date_of_birth FROM users WHERE user_id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $connect->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Close the database connection
$connect->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkly Profile</title>
  <link rel="stylesheet" href="checkly.css">
  <style>
    .profile-container {
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
      text-align: left;
    }
    .profile-picture {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 15px;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }
    .update-form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    .update-form label {
      font-weight: bold;
      margin-bottom: 5px;
    }
    .update-form input[type="text"],
    .update-form input[type="email"],
    .update-form input[type="date"],
    .update-form textarea {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }
    .update-form input[type="file"] {
      padding: 5px 0;
    }
    .update-form button {
      background-color: #4CAF50;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      align-self: center;
      margin-top: 10px;
    }
    .update-form .email-note {
      font-size: 0.9rem;
      color: #666;
      margin-top: 5px;
      margin-bottom: 10px;
    }
    .success-message {
      color: green;
      font-size: 1rem;
      margin-top: 15px;
      text-align: center;
    }
    .error-message {
      color: red;
      font-size: 1rem;
      margin-top: 15px;
      text-align: center;
    }
    h2 {
      text-align: center;
      font-size: 2rem;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <header>
    <div class="head">
      <div class="logo">
        <img src="checklylogo.jpg" alt="checklylogo">
      </div>
      <h1>Checkly Profile</h1>
      <div class="sign">
        <button class="logout"><a href="logout.php">Log Out</a></button>
      </div>
    </div>
    <p>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</p>
  </header>
  <main>
    <section class="profile-container">
      <h2>MY PROFILE</h2>
      <!-- Display success or error message -->
      <?php if (!empty($success_message)): ?>
        <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
      <?php endif; ?>
      <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
      <?php endif; ?>
      
      <form id="updateProfileForm" method="POST" enctype="multipart/form-data" class="update-form">
        <!-- Profile Picture Section -->
        <div>
          <label for="profile_picture">Profile Picture:</label><br>
          <!-- Display the current profile picture if it exists, otherwise show a default image -->
          <?php if ($user['profile_picture']): ?>
            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
          <?php else: ?>
            <img src="default_profile.jpg" alt="Default Profile Picture" class="profile-picture">
          <?php endif; ?>
          <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
        </div>

        <!-- Username -->
        <div>
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
        </div>

        <!-- Email -->
        <div>
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
          <p class="email-note">Email cannot be changed</p>
        </div>

        <!-- Date of Birth -->
        <div>
          <label for="date_of_birth">Date of Birth:</label>
          <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $user['date_of_birth'] ? htmlspecialchars($user['date_of_birth']) : ''; ?>">
        </div>

        <!-- Save Changes Button -->
        <button type="submit">SAVE CHANGES</button>
      </form>
    </section>
  </main>
  <footer>
    <h3>Contact Us</h3>
    <p>Email: <a href="mailto:checkly@gmail.com">checkly@gmail.com</a></p>
    <p>Phone: <a href="tel:+254712345678">+254 712345678</a></p>
  </footer>
</body>
</html>