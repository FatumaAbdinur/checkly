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
include 'connectdb.php';

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
        if ($stmt === false) {
            die("Prepare failed: " . $connect->error);
        }
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
    $stmt = $connect->prepare("SELECT username, email, created, profile_picture, date_of_birth FROM users WHERE user_id = ?");
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
    $stmt = $connect->prepare("SELECT username, email, created, profile_picture, date_of_birth FROM users WHERE user_id = ?");
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    :root {
      --primary: #307473;      /* Updated to darker teal */
      --secondary: #F8E16C;
      --accent: #2D3047;
      --text: #1A1A1A;
      --light-bg: #FFF8F0;
    }
    
    body {
      color: var(--text);
      background-color: var(--light-bg);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      line-height: 1.6;
    }
    
    /* Navigation Bar */
    nav {
      background-color: var(--primary);
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .nav-brand {
      font-size: 1.8rem;
      font-weight: bold;
      color: white;
      text-decoration: none;
    }
    
    .nav-links {
      display: flex;
      gap: 15px;
    }
    
    .nav-link {
      color: white;
      text-decoration: none;
      font-weight: 600;
      padding: 8px 15px;
      border-radius: 20px;
      transition: all 0.3s ease;
      background: linear-gradient(145deg, var(--primary), #2a5f5e);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    .nav-link:hover {
      background: linear-gradient(145deg, #2a5f5e, var(--primary));
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    
    .nav-link.logout {
      background: linear-gradient(145deg, var(--accent), #1a1c2f);
    }
    
    .nav-link.logout:hover {
      background: linear-gradient(145deg, #1a1c2f, var(--accent));
    }
    
    .welcome-message {
      text-align: center;
      margin: 25px 0;
      font-size: 1.3rem;
      color: var(--text);
      font-weight: 600;
    }
    
    /* Profile Container */
    .profile-container {
      max-width: 600px;
      margin: 0 auto;
      padding: 30px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      margin-bottom: 40px;
    }
    
    h2 {
      color: var(--accent);
      text-align: center;
      font-size: 2rem;
      margin-bottom: 25px;
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
      border: 4px solid var(--secondary);
    }
    
    .update-form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    
    .update-form label {
      font-weight: bold;
      margin-bottom: 5px;
      color: var(--accent);
    }
    
    .update-form input[type="text"],
    .update-form input[type="email"],
    .update-form input[type="date"],
    .update-form textarea {
      width: 100%;
      padding: 10px;
      border: 2px solid #ddd;
      border-radius: 5px;
      box-sizing: border-box;
      transition: all 0.3s ease;
    }
    
    .update-form input[type="text"]:focus,
    .update-form input[type="email"]:focus,
    .update-form input[type="date"]:focus,
    .update-form textarea:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(48, 116, 115, 0.2);
    }
    
    .update-form input[type="file"] {
      padding: 5px 0;
    }
    
    .update-form button {
      background: linear-gradient(145deg, var(--primary), #2a5f5e);
      color: white;
      padding: 12px 25px;
      border: none;
      border-radius: 30px;
      cursor: pointer;
      font-weight: bold;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      margin-top: 20px;
      align-self: center;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    .update-form button:hover {
      background: linear-gradient(145deg, #2a5f5e, var(--primary));
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    
    .update-form .email-note {
      font-size: 0.9rem;
      color: #666;
      margin-top: 5px;
      margin-bottom: 10px;
    }
    
    .success-message {
      color: #28a745;
      background-color: rgba(40,167,69,0.1);
      padding: 10px;
      border-radius: 5px;
      text-align: center;
      margin: 15px 0;
    }
    
    .error-message {
      color: #dc3545;
      background-color: rgba(220,53,69,0.1);
      padding: 10px;
      border-radius: 5px;
      text-align: center;
      margin: 15px 0;
    }
    
    footer {
      background-color: #9DD9D2; /* Updated footer color */
      color: var(--text);
      text-align: center;
      padding: 20px;
      margin-top: 40px;
    }
    
    footer h3 {
      margin-top: 0;
      color: var(--accent);
    }
    
    footer a {
      color: var(--accent);
      text-decoration: none;
    }
    
    footer a:hover {
      color: var(--secondary);
    }
    
    @media (max-width: 768px) {
      nav {
        flex-direction: column;
        text-align: center;
        gap: 10px;
      }
      
      .nav-links {
        margin-top: 10px;
        flex-direction: column;
        gap: 10px;
      }
      
      .profile-container {
        padding: 20px;
        margin: 0 15px 30px;
      }
    }
  </style>
</head>
<body>
  <!-- Navigation Bar -->
  <nav class="navbar">
    <a href="checkly.php" class="nav-brand">Checkly</a>
    <div class="nav-links">
      <a href="checkly.php" class="nav-link">Home</a>
      <a href="checklytodo.php" class="nav-link">To Do</a>
      <a href="dashboard.php" class="nav-link active">Profile</a>
      <a href="logout.php" class="nav-link logout">Logout</a>
    </div>
  </nav>

  <!-- Welcome message -->
  <div class="welcome-message">
    <p>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</p>
  </div>

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