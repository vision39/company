<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task-name'])) {
        addTask(trim($_POST['task-name']));
    }
    if (isset($_POST['email'])) {
        subscribeEmail(trim($_POST['email']));
    }
    if (isset($_POST['delete'])) {
        deleteTask($_POST['delete']);
    }
    if (isset($_POST['complete'])) {
        markTaskAsCompleted($_POST['complete'], true);
    }
    if (isset($_POST['uncomplete'])) {
        markTaskAsCompleted($_POST['uncomplete'], false);
    }
}

$tasks = getAllTasks();

// TODO: Implement the task scheduler, email form and logic for email registration.

// In HTML, you can add desired wrapper `<div>` elements or other elements to style the page. Just ensure that the following elements retain their provided IDs.
?>

<!DOCTYPE html>
<html>

<head>
	<title>Task Planner</title>
    <style>
        .completed { text-decoration: line-through; }
    </style>
</head>

<body>

	<!-- Add Task Form -->
	<form method="POST" action="">
		<input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
		<button type="submit" id="add-task">Add Task</button>
	</form>

	<!-- Tasks List -->
	<ul class="tasks-list">
        <?php foreach ($tasks as $task): ?>
            <li class="task-item<?= $task['completed'] ? ' completed' : '' ?>">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="<?= $task['completed'] ? 'uncomplete' : 'complete' ?>" value="<?= $task['id'] ?>">
                    <input type="checkbox" class="task-status" onchange="this.form.submit()" <?= $task['completed'] ? 'checked' : '' ?> >
                </form>
                <?= htmlspecialchars($task['name']) ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete" value="<?= $task['id'] ?>">
                    <button type="submit" class="delete-task">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

	<!-- Subscription Form -->
	<form method="POST">
        <input type="email" name="email" required />
        <button id="submit-email">Submit</button>
    </form>

</body>

</html>