<?php
require_once 'functions.php';

$email = $_GET['email'] ?? '';

if (unsubscribeEmail($email)) {
    header("Location: index.php?success=You+have+been+unsubscribed+from+task+reminders.");
} else {
    header("Location: index.php?error=Error+processing+unsubscribe+request.");
}
exit;

<!DOCTYPE html>
<html>
<head>
</head>
<body>
    <h2 id="unsubscription-heading">Unsubscribe from Task Updates</h2>
    
    <div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
        <?= htmlspecialchars($message) ?>
    </div>

    <p>You will no longer receive task reminder emails from our service.</p>
    
    <a href="index.php" class="home-link">Return to Task Scheduler</a>
</body>
</html>
