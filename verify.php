<?php
require_once 'functions.php';

$email = isset($_GET['email']) ? urldecode($_GET['email']) : '';
$code = $_GET['code'] ?? '';

if ($email && $code) {
    $result = verifySubscription($email, $code);
    echo $result ? "✅ Subscription verified successfully!" : "❌ Invalid verification link.";
} else {
    echo "❌ Missing email or verification code.";
}

?>

<!DOCTYPE html>
<html>
<head>
	<!-- Implement Header ! -->
</head>
<body>
	<!-- Do not modify the ID of the heading -->
	<h2 id="verification-heading">Subscription Verification</h2>
	<!-- Implemention body -->
</body>
</html>