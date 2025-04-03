<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkly - Energetic To-Do List</title>
    <link rel="stylesheet" href="checklytodo.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-brand">Checkly </div>
        <div class="nav-links">
            <a href="checkly.html" class="nav-link">Home</a>
            <a href="#" class="nav-link logout">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1>Your Tasks</h1>
        <p>Get things done easier!</p>
        
        <div class="input-section">
            <input type="text" id="todo-input" placeholder="Add a new task...">
            <button id="add-btn">Add Task</button>
        </div>
        
        <ul id="todo-list"></ul>
    </div>

    <script src="checklytodo.js"></script>
</body>
</html>