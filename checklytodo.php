<?php
session_start();
require_once 'connectdb.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set default user ID from session
$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_task':
                // Validate that the user_id exists in the users table
                $check_user_query = "SELECT user_id FROM users WHERE user_id = ?";
                $stmt = $connect->prepare($check_user_query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $check_user_result = $stmt->get_result();

                if ($check_user_result->num_rows == 0) {
                    die("Error: User with user_id $user_id does not exist in the users table. Please create a user first.");
                }
                $stmt->close();

                $title = $_POST['title'];
                $description = $_POST['description'];
                $due_date = $_POST['due_date'];
                $priority = $_POST['priority'];
                $category_name = trim($_POST['category_name']); // Get the category name from the text input

                // Insert task using prepared statement
                $query = "INSERT INTO tasks (user_id, title, description, due_date, priority, status) 
                          VALUES (?, ?, ?, ?, ?, 'Pending')";
                $stmt = $connect->prepare($query);
                $stmt->bind_param("issss", $user_id, $title, $description, $due_date, $priority);
                
                if ($stmt->execute()) {
                    $task_id = $connect->insert_id;

                    // Handle category if provided
                    if (!empty($category_name)) {
                        // Check if the category already exists for this user
                        $query = "SELECT category_id FROM categories WHERE user_id = ? AND name = ?";
                        $stmt = $connect->prepare($query);
                        $stmt->bind_param("is", $user_id, $category_name);
                        $stmt->execute();
                        $category_result = $stmt->get_result();

                        if ($category_result->num_rows > 0) {
                            // Category exists, get its ID
                            $category = $category_result->fetch_assoc();
                            $category_id = $category['category_id'];
                        } else {
                            // Category doesn't exist, create it
                            $query = "INSERT INTO categories (user_id, name) VALUES (?, ?)";
                            $stmt = $connect->prepare($query);
                            $stmt->bind_param("is", $user_id, $category_name);
                            $stmt->execute();
                            $category_id = $connect->insert_id;
                        }
                        $stmt->close();

                        // Associate the task with the category
                        $query = "INSERT INTO task_categories (task_id, category_id) VALUES (?, ?)";
                        $stmt = $connect->prepare($query);
                        $stmt->bind_param("ii", $task_id, $category_id);
                        $stmt->execute();
                        $stmt->close();
                    }

                    // Redirect to avoid form resubmission
                    header("Location: checklytodo.php");
                    exit;
                } else {
                    die("Error adding task: " . $connect->error);
                }

            case 'update_status':
                $task_id = (int)$_POST['task_id'];
                $status = $_POST['status'];

                // Update task status using prepared statement
                $query = "UPDATE tasks SET status = ? WHERE task_id = ?";
                $stmt = $connect->prepare($query);
                $stmt->bind_param("si", $status, $task_id);
                
                if ($stmt->execute()) {
                    $stmt->close();
                    header("Location: checklytodo.php");
                    exit;
                } else {
                    die("Error updating task status: " . $connect->error);
                }

            case 'delete_task':
                $task_id = (int)$_POST['task_id'];

                // Delete from task_categories first due to foreign key constraints
                $query = "DELETE FROM task_categories WHERE task_id = ?";
                $stmt = $connect->prepare($query);
                $stmt->bind_param("i", $task_id);
                $stmt->execute();
                $stmt->close();

                // Delete the task
                $query = "DELETE FROM tasks WHERE task_id = ?";
                $stmt = $connect->prepare($query);
                $stmt->bind_param("i", $task_id);
                
                if ($stmt->execute()) {
                    $stmt->close();
                    header("Location: checklytodo.php");
                    exit;
                } else {
                    die("Error deleting task: " . $connect->error);
                }
        }
    }
}

// Get all tasks for the current user
$user_id = $_SESSION['user_id'];
$query = "SELECT t.*, c.name as category_name 
          FROM tasks t
          LEFT JOIN task_categories tc ON t.task_id = tc.task_id
          LEFT JOIN categories c ON tc.category_id = c.category_id
          WHERE t.user_id = ?
          ORDER BY t.due_date ASC, FIELD(t.priority, 'High', 'Medium', 'Low')";
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tasks_result = $stmt->get_result();
$all_tasks = $tasks_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Separate tasks into active (Pending/In Progress) and completed
$active_tasks = [];
$completed_tasks = [];
foreach ($all_tasks as $task) {
    if ($task['status'] === 'Completed') {
        $completed_tasks[] = $task;
    } else {
        $active_tasks[] = $task;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkly</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Energetic Color Scheme */
        :root {
            --primary: #307473;      /* Updated to darker teal */
            --secondary: #F8E16C;    /* Light Yellow */
            --accent: #2D3047;       /* Deep Blue */
            --text: #1A1A1A;         /* Almost Black */
            --background: #FFF5E6;   /* Soft Warm White */
            --white: #FFFFFF;
            --shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            --low-priority: #4CAF50;  /* Green */
            --medium-priority: #FFC107; /* Amber */
            --high-priority: #F44336;  /* Red */
            --footer-bg: #9DD9D2;    /* Teal for footer */
        }

        /* Base Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background);
            margin: 0;
            padding: 0;
            color: var(--text);
            line-height: 1.6;
        }

        /* Navigation Bar */
        .navbar {
            background-color: var(--primary);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links {
            display: flex;
            gap: 15px; /* Adjusted for better spacing */
        }

        .nav-link {
            color: var(--white);
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

        /* Main Container */
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        h1 {
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 10px;
            text-align: center;
        }

        p {
            color: var(--accent);
            margin-bottom: 20px;
            text-align: center;
        }

        /* Input Section */
        .input-section {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            width: 100%; /* Ensure the container takes full available width */
        }

        #todo-input, #todo-description, #todo-due-date,
        #todo-priority, #todo-category {
            padding: 12px;
            border: 2px solid var(--primary);
            border-radius: 5px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
            box-sizing: border-box; /* Ensure padding/border don’t affect width */
        }

        #todo-input {
            flex: 1 1 100% !important;
            width: 100% !important;
        }

        #todo-description {
            flex: 1 1 100% !important;
            width: 100% !important;
            min-height: 60px;
            resize: vertical;
        }

        #todo-due-date {
            flex: 1;
            min-width: 150px; /* Prevent shrinking too much */
        }

        #todo-priority, #todo-category {
            flex: 1;
            min-width: 150px; /* Prevent shrinking too much */
        }

        #add-btn {
            background: linear-gradient(145deg, var(--primary), #2a5f5e);
            color: var(--white);
            border: none;
            padding: 12px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            flex: 1;
            min-width: 150px; /* Prevent shrinking too much */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        #add-btn:hover {
            background: linear-gradient(145deg, #2a5f5e, var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        h2 {
            margin-top: 20px;
            font-size: 1.5em;
            color: var(--accent);
            text-align: center;
        }

        #completed-list .todo-item.completed {
            opacity: 0.7;
        }

        #completed-list .todo-text.completed {
            text-decoration: line-through;
            color: #888;
        }

        /* Todo List */
        #todo-list, #completed-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .todo-item {
            display: flex;
            align-items: center;
            background-color: var(--white);
            margin: 10px 0;
            padding: 15px;
            border-radius: 5px;
            box-shadow: var(--shadow);
            transition: transform 0.2s, box-shadow 0.2s;
            gap: 10px;
        }

        .todo-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .todo-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .task-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .todo-text {
            font-size: 1.1rem;
            font-weight: bold;
        }

        .task-details {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 0.9rem;
            color: #555;
        }

        .task-details span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .delete-btn {
            background: linear-gradient(145deg, var(--accent), #1a1c2f);
            color: var(--white);
            border: none;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .delete-btn:hover {
            background: linear-gradient(145deg, #1a1c2f, var(--accent));
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .completed {
            text-decoration: line-through;
            opacity: 0.7;
        }

        /* Priority Colors */
        .priority-low {
            color: var(--low-priority);
        }

        .priority-medium {
            color: var(--medium-priority);
        }

        .priority-high {
            color: var(--high-priority);
        }

        /* Footer */
        footer {
            background-color: var(--footer-bg);
            color: var(--text);
            padding: 20px;
            text-align: center;
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

        /* Responsive Design */
        @media (max-width: 600px) {
            .navbar {
                padding: 12px 15px;
            }
            
            .nav-brand {
                font-size: 1.3rem;
            }
            
            .nav-links {
                flex-direction: column;
                gap: 10px;
            }
            
            .input-section {
                flex-direction: column;
            }
            
            #add-btn {
                width: 100%;
            }
            
            .todo-item {
                flex-wrap: wrap;
            }
            
            .delete-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-brand">Checkly</div>
        <div class="nav-links">
            <a href="checkly.php" class="nav-link">Home</a>
            <a href="checklytodo.php" class="nav-link ">To do</a>
            <a href="dashboard.php" class="nav-link">Profile</a>
            <a href="logout.php" class="nav-link logout">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1>Your Tasks</h1>
        <p>Get things done easier!</p>
        
        <!-- Add Task Form -->
        <form method="POST" class="input-section">
            <input type="hidden" name="action" value="add_task">
            <input type="text" name="title" id="todo-input" placeholder="Add a new task..." required>
            <textarea name="description" id="todo-description" placeholder="Description"></textarea>
            <input type="datetime-local" name="due_date" id="todo-due-date">
            <select name="priority" id="todo-priority">
                <option value="Low">Low</option>
                <option value="Medium" selected>Medium</option>
                <option value="High">High</option>
            </select>
            <input type="text" name="category_name" id="todo-category" placeholder="Enter a category (optional)">
            <button type="submit" id="add-btn">Add Task</button>
        </form>
        
        <!-- Active Task List -->
        <h2>Active Tasks</h2>
        <ul id="todo-list">
            <?php foreach ($active_tasks as $task): ?>
                <li class="todo-item">
                    <!-- Task Status Form -->
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="task_id" value="<?= $task['task_id'] ?>">
                        <input type="hidden" name="status" value="Completed">
                        <input type="checkbox" class="todo-checkbox" 
                               onchange="this.form.submit()"
                               <?= $task['status'] === 'Completed' ? 'checked' : '' ?>>
                    </form>
                    
                    <div class="task-content">
                        <span class="todo-text">
                            <?= htmlspecialchars($task['title']) ?>
                        </span>
                        <div class="task-details">
                            <?php if (!empty($task['description'])): ?>
                                <span><?= htmlspecialchars($task['description']) ?></span>
                            <?php endif; ?>
                            
                            <?php if (!empty($task['due_date'])): ?>
                                <span>Due: <?= date('M j, Y g:i A', strtotime($task['due_date'])) ?></span>
                            <?php endif; ?>
                            
                            <span class="priority-<?= strtolower($task['priority']) ?>">
                                Priority: <?= $task['priority'] ?>
                            </span>
                            
                            <?php if (!empty($task['category_name'])): ?>
                                <span>Category: <?= htmlspecialchars($task['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Delete Button -->
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_task">
                        <input type="hidden" name="task_id" value="<?= $task['task_id'] ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Completed Task List -->
        <h2>Completed Tasks</h2>
        <ul id="completed-list">
            <?php foreach ($completed_tasks as $task): ?>
                <li class="todo-item completed">
                    <!-- Uncheck to move back to active tasks -->
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="task_id" value="<?= $task['task_id'] ?>">
                        <input type="hidden" name="status" value="Pending">
                        <input type="checkbox" class="todo-checkbox" 
                               onchange="this.form.submit()"
                               checked>
                    </form>
                    
                    <div class="task-content">
                        <span class="todo-text completed">
                            <?= htmlspecialchars($task['title']) ?>
                        </span>
                        <div class="task-details">
                            <?php if (!empty($task['description'])): ?>
                                <span><?= htmlspecialchars($task['description']) ?></span>
                            <?php endif; ?>
                            
                            <?php if (!empty($task['due_date'])): ?>
                                <span>Due: <?= date('M j, Y g:i A', strtotime($task['due_date'])) ?></span>
                            <?php endif; ?>
                            
                            <span class="priority-<?= strtolower($task['priority']) ?>">
                                Priority: <?= $task['priority'] ?>
                            </span>
                            
                            <?php if (!empty($task['category_name'])): ?>
                                <span>Category: <?= htmlspecialchars($task['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Delete Button -->
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_task">
                        <input type="hidden" name="task_id" value="<?= $task['task_id'] ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Add Footer -->
    <footer>
        <h3>Contact Us</h3>
        <p>Email: <a href="mailto:checkly@gmail.com">checkly@gmail.com</a></p>
        <p>Phone: <a href="tel:+254712345678">+254 712345678</a></p>
    </footer>
</body>
</html>