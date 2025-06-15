<?php
require_once 'functions.php';

$email = $_GET['email'] ?? '';
if ($email) {
    $decodedEmail = base64_decode($email);
    $result = unsubscribeEmail($decodedEmail);
    echo $result ? "✅ You've been unsubscribed successfully." : "❌ Email not found or already unsubscribed.";
} else {
    echo "❌ Invalid unsubscribe link.";
}

?>

<!DOCTYPE html>
<html>
<head>
	<!-- Implement Header ! -->
</head>
<body>
	<!-- Do not modify the ID of the heading -->
	<h2 id="unsubscription-heading">Unsubscribe from Task Updates</h2>
	<!-- Implemention body -->
</body>
</html>