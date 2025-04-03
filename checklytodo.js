<<<<<<< HEAD
document.addEventListener('DOMContentLoaded', () => {
    const todoInput = document.getElementById('todo-input');
    const addBtn = document.getElementById('add-btn');
    const todoList = document.getElementById('todo-list');


    addBtn.addEventListener('click', addTask);
    todoInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') addTask();
    });

    function addTask() {
        const taskText = todoInput.value.trim();
        if (taskText === '') return;

        const li = document.createElement('li');
        li.className = 'todo-item';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'todo-checkbox';
        checkbox.addEventListener('change', toggleTask);

        const span = document.createElement('span');
        span.className = 'todo-text';
        span.textContent = taskText;

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'delete-btn';
        deleteBtn.textContent = 'Delete';
        deleteBtn.addEventListener('click', () => {
            li.remove();
        });

        li.appendChild(checkbox);
        li.appendChild(span);
        li.appendChild(deleteBtn);
        todoList.appendChild(li);

        todoInput.value = ''; // Clear input
    }

    function toggleTask(e) {
        const taskText = e.target.nextElementSibling;
        taskText.classList.toggle('completed');
    }
=======
document.addEventListener('DOMContentLoaded', () => {
    const todoInput = document.getElementById('todo-input');
    const todoDescription = document.getElementById('todo-description');
    const todoDueDate = document.getElementById('todo-due-date');
    const todoPriority = document.getElementById('todo-priority');
    const todoCategory = document.getElementById('todo-category');
    const addBtn = document.getElementById('add-btn');
    const todoList = document.getElementById('todo-list');
    const completedList = document.getElementById('completed-list');

    addBtn.addEventListener('click', addTask);
    todoInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') addTask();
    });

    function loadTasks() {
        fetch('checklytodo.php?action=get_tasks')
            .then(response => response.json())
            .then(tasks => {
                todoList.innerHTML = '';
                completedList.innerHTML = '';
                tasks.forEach(task => {
                    createTaskElement(task);
                });
            })
            .catch(error => console.error('Error loading tasks:', error));
    }

    function addTask() {
        const taskData = {
            action: 'add_task',
            title: todoInput.value.trim(),
            description: todoDescription.value.trim(),
            due_date: todoDueDate.value,
            priority: todoPriority.value,
            category_name: todoCategory.value.trim() || null
        };

        if (taskData.title === '') return;

        fetch('checklytodo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(taskData).toString()
        })
        .then(response => response.json())
        .then(task => {
            createTaskElement(task);
            clearInputs();
        })
        .catch(error => console.error('Error adding task:', error));
    }

    function createTaskElement(task) {
        const li = document.createElement('li');
        li.className = 'todo-item';
        li.dataset.taskId = task.task_id;
        if (task.status === 'Completed') {
            li.classList.add('completed');
        }

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'todo-checkbox';
        checkbox.checked = task.status === 'Completed';
        checkbox.addEventListener('change', () => toggleTaskStatus(task.task_id, checkbox.checked, li));

        const taskContent = document.createElement('div');
        taskContent.className = 'task-content';

        const titleSpan = document.createElement('span');
        titleSpan.className = 'todo-text';
        titleSpan.textContent = task.title;
        if (task.status === 'Completed') {
            titleSpan.classList.add('completed');
        }

        const detailsDiv = document.createElement('div');
        detailsDiv.className = 'task-details';
        
        if (task.description) {
            const descSpan = document.createElement('span');
            descSpan.textContent = task.description;
            detailsDiv.appendChild(descSpan);
        }
        
        if (task.due_date) {
            const dueSpan = document.createElement('span');
            dueSpan.textContent = `Due: ${formatDate(task.due_date)}`;
            detailsDiv.appendChild(dueSpan);
        }
        
        const prioritySpan = document.createElement('span');
        prioritySpan.textContent = `Priority: ${task.priority}`;
        prioritySpan.className = `priority-${task.priority.toLowerCase()}`;
        detailsDiv.appendChild(prioritySpan);

        if (task.category_name) {
            const categorySpan = document.createElement('span');
            categorySpan.textContent = `Category: ${task.category_name}`;
            detailsDiv.appendChild(categorySpan);
        }

        taskContent.appendChild(titleSpan);
        taskContent.appendChild(detailsDiv);

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'delete-btn';
        deleteBtn.textContent = 'Delete';
        deleteBtn.addEventListener('click', () => deleteTask(task.task_id, li));

        li.appendChild(checkbox);
        li.appendChild(taskContent);
        li.appendChild(deleteBtn);

        // Append to the appropriate list based on status
        if (task.status === 'Completed') {
            completedList.appendChild(li);
        } else {
            todoList.appendChild(li);
        }
    }

    function toggleTaskStatus(taskId, isChecked, element) {
        const newStatus = isChecked ? 'Completed' : 'Pending';
        fetch('checklytodo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'update_status',
                task_id: taskId,
                status: newStatus
            }).toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const titleSpan = element.querySelector('.todo-text');
                if (newStatus === 'Completed') {
                    titleSpan.classList.add('completed');
                    element.classList.add('completed');
                    completedList.appendChild(element); // Move to completed list
                } else {
                    titleSpan.classList.remove('completed');
                    element.classList.remove('completed');
                    todoList.appendChild(element); // Move back to active list
                }
            }
        })
        .catch(error => {
            console.error('Error updating task status:', error);
            // Revert checkbox state on error
            element.querySelector('.todo-checkbox').checked = !isChecked;
        });
    }

    function deleteTask(taskId, element) {
        if (confirm('Are you sure you want to delete this task?')) {
            fetch('checklytodo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'delete_task',
                    task_id: taskId
                }).toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    element.remove();
                }
            })
            .catch(error => console.error('Error deleting task:', error));
        }
    }

    function clearInputs() {
        todoInput.value = '';
        todoDescription.value = '';
        todoDueDate.value = '';
        todoPriority.value = 'Medium';
        todoCategory.value = '';
    }

    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    // Load tasks when page loads
    loadTasks();
>>>>>>> afd23aa (Checkly files)
});