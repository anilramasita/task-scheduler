<?php
require_once 'functions.php';

// TODO: Implement the task scheduler, email form and logic for email registration.

// In HTML, you can add desired wrapper `<div>` elements or other elements to style the page. Just ensure that the following elements retain their provided IDs.
?>
<!DOCTYPE html>
<html>

<head>
	<!-- Implement Header !-->
</head>

<body>

	<!-- Add Task Form -->
	<form method="POST" action="">
		<!-- Implement Form !-->
		<input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
		<button type="submit" id="add-task">Add Task</button>
	</form>

	<!-- Tasks List -->
	<ul id="tasks-list">
		<!-- Implement Tasks List (Your task item must have below
		provided elements you can modify there position, wrap them
		in another container, or add styles but they must contain
		specified classnames and input type )!-->
		<li class="task-item">
			<input type="checkbox" class="task-status">
			<button class="delete-task">Delete</button>
		</li>	
	</ul>

	<!-- Subscription Form -->
	<form method="POST" action="">
		<!-- Implement Form !-->
		<button type="submit" id="submit-email">Subscribe</button>
	</form>

</body>

</html>
