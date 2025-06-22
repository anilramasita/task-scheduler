<?php

/**
 * Adds a new task to the task list
 */
function addTask(string $task_name): bool {
    $tasks = getAllTasks();
    
    // Check for duplicates
    foreach ($tasks as $task) {
        if (strtolower($task['name']) === strtolower($task_name)) {
            return false;
        }
    }
    
    $newTask = [
        'id' => uniqid(),
        'name' => htmlspecialchars($task_name),
        'completed' => false
    ];
    
    $tasks[] = $newTask;
    file_put_contents(__DIR__ . '/tasks.txt', json_encode($tasks, JSON_PRETTY_PRINT));
    return true;
}

/**
 * Retrieves all tasks from the tasks.txt file
 */
function getAllTasks(): array {
    if (!file_exists(__DIR__ . '/tasks.txt')) {
        return [];
    }
    
    $content = file_get_contents(__DIR__ . '/tasks.txt');
    return json_decode($content, true) ?: [];
}

/**
 * Marks a task as completed or uncompleted
 */
function markTaskAsCompleted(string $task_id, bool $is_completed): bool {
    $tasks = getAllTasks();
    $found = false;
    
    foreach ($tasks as &$task) {
        if ($task['id'] === $task_id) {
            $task['completed'] = $is_completed;
            $found = true;
            break;
        }
    }
    
    if ($found) {
        file_put_contents(__DIR__ . '/tasks.txt', json_encode($tasks, JSON_PRETTY_PRINT));
        return true;
    }
    
    return false;
}

/**
 * Deletes a task from the task list
 */
function deleteTask(string $task_id): bool {
    $tasks = getAllTasks();
    $initialCount = count($tasks);
    
    $tasks = array_filter($tasks, function($task) use ($task_id) {
        return $task['id'] !== $task_id;
    });
    
    if (count($tasks) < $initialCount) {
        file_put_contents(__DIR__ . '/tasks.txt', json_encode(array_values($tasks), JSON_PRETTY_PRINT));
        return true;
    }
    
    return false;
}

/**
 * Generates a 6-digit verification code
 */
function generateVerificationCode(): string {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Subscribe an email address to task notifications
 */
function subscribeEmail(string $email): bool {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Check if already verified
    $subscribers = [];
    if (file_exists(__DIR__ . '/subscribers.txt')) {
        $subscribers = json_decode(file_get_contents(__DIR__ . '/subscribers.txt'), true) ?: [];
    }
    
    if (in_array($email, $subscribers)) {
        return true; // Already subscribed
    }
    
    // Check if pending verification
    $pending = [];
    if (file_exists(__DIR__ . '/pending_subscriptions.txt')) {
        $pending = json_decode(file_get_contents(__DIR__ . '/pending_subscriptions.txt'), true) ?: [];
    }
    
    $code = generateVerificationCode();
    $pending[$email] = [
        'code' => $code,
        'timestamp' => time()
    ];
    
    file_put_contents(__DIR__ . '/pending_subscriptions.txt', json_encode($pending, JSON_PRETTY_PRINT));
    
    // Send verification email
    $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify.php?email=" . urlencode($email) . "&code=$code";
    
    $subject = "Verify subscription to Task Planner";
    $message = '<p>Click the link below to verify your subscription to Task Planner:</p>';
    $message .= '<p><a id="verification-link" href="' . $verificationLink . '">Verify Subscription</a></p>';
    
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($email, $subject, $message, $headers);
}

/**
 * Verifies an email subscription
 */
function verifySubscription(string $email, string $code): bool {
    if (!file_exists(__DIR__ . '/pending_subscriptions.txt')) {
        return false;
    }
    
    $pending = json_decode(file_get_contents(__DIR__ . '/pending_subscriptions.txt'), true);
    
    if (!isset($pending[$email]) || $pending[$email]['code'] !== $code) {
        return false;
    }
    
    // Add to subscribers
    $subscribers = [];
    if (file_exists(__DIR__ . '/subscribers.txt')) {
        $subscribers = json_decode(file_get_contents(__DIR__ . '/subscribers.txt'), true) ?: [];
    }
    
    if (!in_array($email, $subscribers)) {
        $subscribers[] = $email;
        file_put_contents(__DIR__ . '/subscribers.txt', json_encode($subscribers, JSON_PRETTY_PRINT));
    }
    
    // Remove from pending
    unset($pending[$email]);
    file_put_contents(__DIR__ . '/pending_subscriptions.txt', json_encode($pending, JSON_PRETTY_PRINT));
    
    return true;
}

/**
 * Unsubscribes an email from the subscribers list
 */
function unsubscribeEmail(string $email): bool {
    if (!file_exists(__DIR__ . '/subscribers.txt')) {
        return false;
    }
    
    $subscribers = json_decode(file_get_contents(__DIR__ . '/subscribers.txt'), true) ?: [];
    $index = array_search($email, $subscribers);
    
    if ($index !== false) {
        unset($subscribers[$index]);
        file_put_contents(__DIR__ . '/subscribers.txt', json_encode(array_values($subscribers), JSON_PRETTY_PRINT));
        return true;
    }
    
    return false;
}

/**
 * Sends task reminders to all subscribers
 */
function sendTaskReminders(): void {
    if (!file_exists(__DIR__ . '/subscribers.txt')) {
        return;
    }
    
    $subscribers = json_decode(file_get_contents(__DIR__ . '/subscribers.txt'), true) ?: [];
    $tasks = getAllTasks();
    
    $pendingTasks = array_filter($tasks, function($task) {
        return !$task['completed'];
    });
    
    if (empty($pendingTasks)) {
        return;
    }
    
    foreach ($subscribers as $email) {
        sendTaskEmail($email, $pendingTasks);
    }
}

/**
 * Sends a task reminder email to a subscriber
 */
function sendTaskEmail(string $email, array $pending_tasks): bool {
    $subject = 'Task Planner - Pending Tasks Reminder';
    
    $message = '<h2>Pending Tasks Reminder</h2>';
    $message .= '<p>Here are the current pending tasks:</p><ul>';
    
    foreach ($pending_tasks as $task) {
        $message .= '<li>' . htmlspecialchars($task['name']) . '</li>';
    }
    
    $message .= '</ul>';
    
    $unsubscribeLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/unsubscribe.php?email=" . urlencode($email);
    $message .= '<p><a id="unsubscribe-link" href="' . $unsubscribeLink . '">Unsubscribe from notifications</a></p>';
    
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($email, $subject, $message, $headers);
}
