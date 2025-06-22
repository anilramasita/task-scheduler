<?php
require_once 'functions.php';

$email = $_GET['email'] ?? '';
$code = $_GET['code'] ?? '';

if (verifySubscription($email, $code)) {
    header("Location: index.php?success=Email+verification+successful!+You+will+now+receive+task+reminders.");
} else {
    header("Location: index.php?error=Invalid+verification+link+or+email+already+verified.");
}
exit;

<!DOCTYPE html>
<html>
<head>
	
</head>
<body>
    <h2 id="verification-heading">Subscription Verification</h2>
    
    <?php if (verifySubscription($email, $code)): ?>
        <div class="message success">
            Your email <?= htmlspecialchars($email) ?> has been successfully verified!
        </div>
        <a href="index.php" class="action-link">Go to Task Scheduler</a>
    <?php else: ?>
        <div class="message error">
            Invalid verification link or the link has expired.
        </div>
        <a href="index.php" class="action-link">Try Again</a>
    <?php endif; ?>
</body>
</html>
