<?php

/**
 * Adds a new task to the task list
 * 
 * @param string $task_name The name of the task to add.
 * @return bool True on success, false on failure.
 */
function addTask($task_name)
{
	$file = __DIR__ . '/tasks.txt';
	$tasks = [];

	if (file_exists($file)) {
		$tasks = json_decode(file_get_contents($file), true) ?? [];
	}

	foreach ($tasks as $task) {
		if (strcasecmp($task['name'], $task_name) === 0) {
			return false; // Duplicate
		}
	}

	$tasks[] = [
		'id' => uniqid(),
		'name' => $task_name,
		'completed' => false
	];

	file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT));
	return true;
}



/**
 * Retrieves all tasks from the tasks.txt file
 * 
 * @return array Array of tasks. -- Format [ id, name, completed ]
 */
function getAllTasks()
{
	$file = __DIR__ . '/tasks.txt';
	if (!file_exists($file)) return [];

	$tasks = json_decode(file_get_contents($file), true);
	return is_array($tasks) ? $tasks : [];
}


/**
 * Marks a task as completed or uncompleted
 * 
 * @param string  $task_id The ID of the task to mark.
 * @param bool $is_completed True to mark as completed, false to mark as uncompleted.
 * @return bool True on success, false on failure
 */
function markTaskAsCompleted($task_id, $is_completed)
{
	$file = __DIR__ . '/tasks.txt';
	$tasks = getAllTasks();

	foreach ($tasks as &$task) {
		if ($task['id'] === $task_id) {
			$task['completed'] = $is_completed;
			break;
		}
	}

	file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT));
}


/**
 * Deletes a task from the task list
 * 
 * @param string $task_id The ID of the task to delete.
 * @return bool True on success, false on failure.
 */
function deleteTask($task_id)
{
	$file = __DIR__ . '/tasks.txt';
	$tasks = getAllTasks();

	$tasks = array_filter($tasks, function ($task) use ($task_id) {
		return $task['id'] !== $task_id;
	});

	file_put_contents($file, json_encode(array_values($tasks), JSON_PRETTY_PRINT));
}


/**
 * Generates a 6-digit verification code
 * 
 * @return string The generated verification code.
 */
function generateVerificationCode()
{
	return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}


/**
 * Subscribe an email address to task notifications.
 *
 * Generates a verification code, stores the pending subscription,
 * and sends a verification email to the subscriber.
 *
 * @param string $email The email address to subscribe.
 * @return bool True if verification email sent successfully, false otherwise.
 */

function safeMail($to, $subject, $message, $headers)
{
	if (!mail($to, $subject, $message, $headers)) {
		$log = "TO: $to\nSUBJECT: $subject\n$headers\n$message\n------\n";
		file_put_contents(__DIR__ . "/email.log", $log, FILE_APPEND);
	}
}

function subscribeEmail($email)
{
	$pendingFile = __DIR__ . '/pending_subscriptions.txt';
	$subscribersFile = __DIR__ . '/subscribers.txt';

	// Load files
	$pending = file_exists($pendingFile) ? json_decode(file_get_contents($pendingFile), true) : [];
	$subscribersContent = file_exists($subscribersFile) ? file_get_contents($subscribersFile) : '[]';
	$subscribers = json_decode($subscribersContent, true);
	if (!is_array($subscribers)) $subscribers = [];


	// Check if already subscribed
	if (in_array($email, $subscribers) || isset($pending[$email])) {
		return false;
	}

	$code = generateVerificationCode();
	$pending[$email] = [
		'code' => $code,
		'timestamp' => time()
	];

	file_put_contents($pendingFile, json_encode($pending, JSON_PRETTY_PRINT));

	// Send verification email
	$verificationLink = "http://localhost:3000/verify.php?email=" . urlencode($email) . "&code=$code";


	$subject = "Verify subscription to Task Planner";
	$message = '
        <p>Click the link below to verify your subscription to Task Planner:</p>
        <p><a id="verification-link" href="' . $verificationLink . '">Verify Subscription</a></p>
    ';
	$headers = "From: no-reply@example.com\r\n";
	$headers .= "Content-type: text/html\r\n";

	safeMail($email, $subject, $message, $headers);


	return true;
}


/**
 * Verifies an email subscription
 * 
 * @param string $email The email address to verify.
 * @param string $code The verification code.
 * @return bool True on success, false on failure.
 */
function verifySubscription($email, $code) {
    $pendingFile = __DIR__ . '/pending_subscriptions.txt';
    $subscribersFile = __DIR__ . '/subscribers.txt';

    // Load pending subscriptions safely
    $pendingRaw = file_exists($pendingFile) ? file_get_contents($pendingFile) : '{}';
    $pending = json_decode($pendingRaw, true);
    if (!is_array($pending)) $pending = [];

    // Load subscribers safely
    $subscribersRaw = file_exists($subscribersFile) ? file_get_contents($subscribersFile) : '[]';
    $subscribers = json_decode($subscribersRaw, true);
    if (!is_array($subscribers)) $subscribers = [];

    if (!isset($pending[$email])) return false;
    if ($pending[$email]['code'] !== $code) return false;

    if (!in_array($email, $subscribers)) {
        $subscribers[] = $email;
    }

    unset($pending[$email]);

    file_put_contents($subscribersFile, json_encode($subscribers, JSON_PRETTY_PRINT));
    file_put_contents($pendingFile, json_encode($pending, JSON_PRETTY_PRINT));

    return true;
}




/**
 * Unsubscribes an email from the subscribers list
 * 
 * @param string $email The email address to unsubscribe.
 * @return bool True on success, false on failure.
 */
function unsubscribeEmail($email)
{
	$subscribersFile = __DIR__ . '/subscribers.txt';

	// Load current subscribers
	$subscribers = file_exists($subscribersFile) ? json_decode(file_get_contents($subscribersFile), true) : [];

	// Check if email exists
	if (!in_array($email, $subscribers)) {
		return false;
	}

	// Remove email
	$subscribers = array_filter($subscribers, function ($e) use ($email) {
		return $e !== $email;
	});

	// Save updated list
	file_put_contents($subscribersFile, json_encode(array_values($subscribers), JSON_PRETTY_PRINT));

	return true;
}


/**
 * Sends task reminders to all subscribers
 * Internally calls  sendTaskEmail() for each subscriber
 */
function sendTaskReminders()
{
	$subscribersFile = __DIR__ . '/subscribers.txt';
	$tasksFile = __DIR__ . '/tasks.txt';

	// Load verified subscribers
	$subscribers = file_exists($subscribersFile) ? json_decode(file_get_contents($subscribersFile), true) : [];

	// Load all tasks
	$tasks = file_exists($tasksFile) ? json_decode(file_get_contents($tasksFile), true) : [];

	// Filter pending (incomplete) tasks
	$pendingTasks = array_filter($tasks, function ($task) {
		return !$task['completed'];
	});

	// Send email to each subscriber
	foreach ($subscribers as $email) {
		sendTaskEmail($email, $pendingTasks);
	}
}


/**
 * Sends a task reminder email to a subscriber with pending tasks.
 *
 * @param string $email The email address of the subscriber.
 * @param array $pending_tasks Array of pending tasks to include in the email.
 * @return bool True if email was sent successfully, false otherwise.
 */
function sendTaskEmail($email, $pending_tasks) {
    $subject = "Task Planner - Pending Tasks Reminder";

    $unsubscribeLink = "http://localhost:3000/unsubscribe.php?email=" . base64_encode($email);

    $taskList = '';
    foreach ($pending_tasks as $task) {
        $taskList .= "<li>" . htmlspecialchars($task['name']) . "</li>";
    }

    $message = '
        <h2>Pending Tasks Reminder</h2>
        <p>Here are the current pending tasks:</p>
        <ul>' . $taskList . '</ul>
        <p><a id="unsubscribe-link" href="' . $unsubscribeLink . '">Unsubscribe from notifications</a></p>
    ';

    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-type: text/html\r\n";

    // Instead of sending mail, log to reminder.log
    $log = "TO: $email\nSUBJECT: $subject\n$headers\n$message\n------\n";
    file_put_contents(__DIR__ . "/reminder.log", $log, FILE_APPEND);
}