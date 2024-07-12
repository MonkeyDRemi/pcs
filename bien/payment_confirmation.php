<?php
require '../stripe/vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51PV1HeFXpsoOQTRTQQKe1nm07d6Mbndq71v4pmCAtuJ5V0nBcQ2DvRQnjvB9gbRdp7TldaHsw5XjpdXrXgSzGYqi00rWOYrcsY'); // Replace with your Stripe secret key

$paymentIntentId = $_GET['payment_intent'];
$paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

if ($paymentIntent->status == 'succeeded') {
    echo 'Payment succeeded!';
} else {
    echo 'Payment failed or is pending confirmation.';
}
?>
