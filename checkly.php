<?php
// Start session to check login status
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkly - Your Ultimate To-Do List</title>
  <!-- Add timestamp to CSS link to prevent caching -->
  <link rel="stylesheet" href="checkly.css?v=<?php echo time(); ?>">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f4f7fa;
      color: #2D3047; /* Deep Blue for text */
    }

    /* Navigation Bar */
    .navbar {
      background-color: #307473; /* Updated to darker teal */
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
      background: linear-gradient(145deg, #307473, #2a5f5e);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    .nav-link:hover {
      background: linear-gradient(145deg, #2a5f5e, #307473);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    
    .nav-link.logout {
      background: linear-gradient(145deg, #2D3047, #1a1c2f);
    }
    
    .nav-link.logout:hover {
      background: linear-gradient(145deg, #1a1c2f, #2D3047);
    }

    /* Hero Section (Organize Your Life) */
    .hero-section {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 60px 20px;
      background-color: white;
      margin: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      border-top: 4px solid #307473;
    }

    .hero-content {
      flex: 1;
      padding: 0 20px;
      text-align: center;
    }

    .hero-content h2 {
      font-size: 2rem;
      color: #2D3047;
      margin-bottom: 20px;
    }

    .hero-content p {
      font-size: 1.1rem;
      color: #555;
      margin-bottom: 20px;
    }

    .hero-image {
      flex: 1;
      padding: 0 20px;
    }

    .hero-image img {
      width: 100%;
      max-width: 400px;
      height: auto;
      display: block;
      margin: 0 auto;
    }

    .getstarted {
      background: linear-gradient(145deg, #307473, #2a5f5e);
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 20px;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .getstarted:hover {
      background: linear-gradient(145deg, #2a5f5e, #307473);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }

    /* Style the <a> tag inside the button to remove underline and match button text */
    .getstarted a {
      color: white;
      text-decoration: none;
      display: block; /* Ensure the link fills the button */
    }

    /* About Us Section */
    .about-us-section {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background-color: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      margin: 20px;
      border-top: 4px solid #F8E16C;
    }

    .about-us-content {
      flex: 1;
      padding: 0 20px;
      text-align: center;
    }

    .about-us-content h2 {
      font-size: 1.8rem;
      color: #2D3047;
      margin-bottom: 20px;
    }

    .about-us-content p {
      font-size: 1rem;
      line-height: 1.6;
      color: #555;
    }

    .about-us-image {
      flex: 1;
      padding: 0 20px;
    }

    .about-us-image img {
      width: 100%;
      max-width: 400px;
      height: auto;
      display: block;
      margin: 0 auto;
    }

    /* Animation Styles */
    .animate-section {
      opacity: 0;
      transform: translateY(50px);
      animation: fadeInUp 0.8s ease-out forwards;
    }

    @keyframes fadeInUp {
      0% {
        opacity: 0;
        transform: translateY(50px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Stagger the animations */
    .hero-section.animate-section {
      animation-delay: 0.2s;
    }

    .about-us-section.animate-section {
      animation-delay: 0.4s;
    }

    footer {
      background-color: #9DD9D2;
      color: #2D3047;
      padding: 30px 20px;
      text-align: center;
    }

    footer h3 {
      font-size: 1.5rem;
      margin-bottom: 10px;
      color: #F8E16C;
    }

    footer p {
      font-size: 0.9rem;
      margin: 5px 0;
    }

    footer a {
      color: #2D3047;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    footer a:hover {
      color: #F8E16C;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .hero-section, .about-us-section {
        flex-direction: column;
        text-align: center;
      }

      .hero-content, .about-us-content {
        padding: 20px;
      }

      .hero-image, .about-us-image {
        padding: 20px;
      }

      .hero-image img, .about-us-image img {
        max-width: 300px;
      }

      .nav-links {
        flex-direction: column;
        gap: 10px;
      }
    }
  </style>
</head>
<body>
  <!-- Navigation Bar -->
  <nav class="navbar">
    <a href="checkly.php" class="nav-brand">Checkly</a>
    <div class="nav-links">
      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- If user is logged in, show navigation links -->
        <a href="checkly.php" class="nav-link active">Home</a>
        <a href="checklytodo.php" class="nav-link">To Do</a>
        <a href="dashboard.php" class="nav-link">Profile</a>
        <a href="logout.php" class="nav-link logout">Logout</a>
      <?php else: ?>
        <!-- If user is not logged in, show signup and login links -->
        <a href="Signup.php" class="nav-link">Sign Up</a>
        <a href="Login.php" class="nav-link">Log In</a>
      <?php endif; ?>
    </div>
  </nav>

  <main>
    <!-- Hero Section (Organize Your Life) -->
    <section class="hero-section animate-section">
      <div class="hero-content">
        <h2>Organize Your Life with Checkly</h2>
        <p>Stay on top of your tasks and never miss a deadline again.</p>
        <?php if (isset($_SESSION['user_id'])): ?>
          <button class="getstarted"><a href="checklytodo.php">My Tasks</a></button>
        <?php else: ?>
          <button class="getstarted"><a href="Signup.php">Get Started</a></button>
        <?php endif; ?>
      </div>
      <div class="hero-image">
        <img src="image2.jpg" alt="Organize Your Life Illustration">
      </div>
    </section>

    <!-- About Us Section -->
    <section class="about-us-section animate-section">
      <div class="about-us-image">
        <img src="image1.jpg" alt="About Us Illustration">
      </div>
      <div class="about-us-content">
        <h2>About Us</h2>
        <p>
          At Checkly, we believe in simplifying your life by helping you manage your tasks efficiently. 
          Our mission is to provide a seamless and intuitive to-do list experience that empowers you 
          to focus on what truly matters. 
          Checkly is here to make your life easier.
        </p>
      </div>
    </section>
  </main>

  <footer>
    <h3>Contact Us</h3>
    <p>Email: <a href="mailto:checkly@gmail.com">checkly@gmail.com</a></p>
    <p>Phone: <a href="tel:+254712345678">+254 712345678</a></p>
  </footer>
</body>
</html>