<?php
// Start the session
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkly - Your Ultimate To-Do List</title>
  <link rel="stylesheet" href="checkly.css">
</head>
<body>
  <header>
    <div class="head">
      <div class="logo">
        <img src="checklylogo.jpg" alt="checklylogo">
      </div>
      <h1>Checkly</h1>
      <div class="sign">
        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- If user is logged in, show Log Out button and link to dashboard -->
          <button class="dashboard"><a href="dashboard.php">Dashboard</a></button>
          <button class="logout"><a href="logout.php">Log Out</a></button>
        <?php else: ?>
          <!-- If user is not logged in, show Sign Up and Log In buttons -->
          <button class="signup"><a href="Signup.html">Sign Up</a></button>
          <button class="login"><a href="Login.php">Log In</a></button>
        <?php endif; ?>
      </div>
    </div>
    <p>Your Ultimate To-Do List App</p>
  </header>
  <main>
    <section class="hero animate-section">
      <h2>Organize Your Life with Checkly</h2>
      <p>Stay on top of your tasks and never miss a deadline again.</p>
      <?php if (isset($_SESSION['user_id'])): ?>
        <button class="getstarted"><a href="dashboard.php">Go to Dashboard</a></button>
      <?php else: ?>
        <button class="getstarted"><a href="Signup.html">Get Started</a></button>
      <?php endif; ?>
    </section>
    <section class="about-us animate-section">
      <h2>About Us</h2>
      <p>
        At Checkly, we believe in simplifying your life by helping you manage your tasks efficiently. 
        Our mission is to provide a seamless and intuitive to-do list experience that empowers you 
        to focus on what truly matters. Whether you're a student, professional, or busy parent, 
        Checkly is here to make your life easier.
      </p>
    </section>
  </main>
  <footer>
    <h3>Contact Us</h3>
    <p>Email: <a href="mailto:checkly@gmail.com">checkly@gmail.com</a></p>
    <p>Phone: <a href="tel:+254712345678">+254 712345678</a></p>
  </footer>
</body>
</html>