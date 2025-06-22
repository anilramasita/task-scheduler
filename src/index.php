<?php
require_once 'functions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task-name'])) {
        addTask($_POST['task-name']);
    } elseif (isset($_POST['email'])) {
        subscribeEmail($_POST['email']);
    } elseif (isset($_POST['task_id'])) {
        if (isset($_POST['completed'])) {
            markTaskAsCompleted($_POST['task_id'], $_POST['completed'] === 'true');
        } elseif (isset($_POST['delete'])) {
            deleteTask($_POST['task_id']);
        }
    }
}

$tasks = getAllTasks();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Task Scheduler</title>
    <style>
        :root {
            --primary-color: #4a6fa5;
            --secondary-color: #166088;
            --accent-color: #4fc3f7;
            --background-color: #f5f7fa;
            --text-color: #333;
            --light-gray: #e0e0e0;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        h1, h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        input[type="text"],
        input[type="email"] {
            flex: 1;
            min-width: 200px;
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: 4px;
            font-size: 16px;
        }
        
        button {
            padding: 12px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: var(--secondary-color);
        }
        
        #submit-email {
            background-color: var(--success-color);
        }
        
        #submit-email:hover {
            background-color: #3d8b40;
        }
        
        .task-list {
            list-style: none;
            margin-top: 20px;
        }
        .task-form {
            display: flex;
            align-items: center;
            flex-grow: 1;
            margin-right: 10px;
        }

        .delete-form {
            display: flex;
            align-items: center;
        }

        .task-form input[type="checkbox"] {
            margin-right: 15px;
        }
        
        .task-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .task-item:hover {
            box-shadow: 0 3px 6px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .task-item.completed {
            opacity: 0.7;
            background-color: #f8f8f8;
        }
        
        .task-status {
            margin-right: 15px;
            transform: scale(1.5);
            accent-color: var(--success-color);
        }
        
        .task-name {
            flex: 1;
            margin-right: 15px;
            font-size: 18px;
        }
        
        .task-item.completed .task-name {
            text-decoration: line-through;
            color: #777;
        }
        
        .delete-task {
            background-color: var(--danger-color);
            padding: 8px 15px;
            margin-left: auto;
        }
        
        .delete-task:hover {
            background-color: #d32f2f;
        }
        
        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: white;
            display: none;
        }
        
        .success {
            background-color: var(--success-color);
        }
        
        .error {
            background-color: var(--danger-color);
        }
        
        @media (max-width: 600px) {
            form {
                flex-direction: column;
                align-items: stretch;
            }
            
            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Task Scheduler</h1>
        
        <!-- Notification Area -->
        <div id="notification" class="notification"></div>
        
        <!-- Task Form -->
        <form method="post" id="task-form">
            <input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
            <button type="submit" id="add-task">Add Task</button>
        </form>
        
        <!-- Task List -->
        <h2>Your Tasks</h2>
        <ul class="task-list">
            <?php foreach ($tasks as $task): ?>
                <li class="task-item <?= $task['completed'] ? 'completed' : '' ?>">
                    <form method="post" class="task-form">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        <input type="hidden" name="completed" value="<?= $task['completed'] ? 'false' : 'true' ?>">
                        <input type="checkbox" class="task-status" <?= $task['completed'] ? 'checked' : '' ?>
                            onchange="this.form.submit()">
                        <span class="task-name"><?= htmlspecialchars($task['name']) ?></span>
                    </form>
                    <form method="post" class="delete-form">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        <input type="hidden" name="delete" value="1">
                        <button type="submit" class="delete-task">Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="container">
        <h2>Email Notifications</h2>
        <form method="post" id="email-form">
            <input type="email" name="email" required placeholder="Enter your email">
            <button type="submit" id="submit-email">Subscribe</button>
        </form>
        <p style="margin-top: 10px; color: #666;">
            You'll receive hourly reminders for pending tasks
        </p>
    </div>

    <script>
        // Show notifications from URL parameters
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const notification = document.getElementById('notification');
            
            if (urlParams.has('success')) {
                notification.textContent = urlParams.get('success');
                notification.className = 'notification success';
                notification.style.display = 'block';
            } else if (urlParams.has('error')) {
                notification.textContent = urlParams.get('error');
                notification.className = 'notification error';
                notification.style.display = 'block';
            }
            
            // Hide notification after 5 seconds
            if (notification.style.display === 'block') {
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>
