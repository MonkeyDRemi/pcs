<?php
// Log the cancellation event
error_log("Payment was cancelled or failed for session_id: " . $_GET['session_id']);

// Redirect to the abonnement page with an error message
header('Location: abonnement.php?error=subscription_failed');
exit();
?>
