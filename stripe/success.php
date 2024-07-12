<?php
session_start();
include('../include/header.php');
include('config.php'); // Adjust the path to config.php as necessary

// Verify if the user is logged in
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../login/index.php');
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

// Database connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the session ID from the Stripe Checkout Session
$session_id = $_GET['session_id'];

require_once('vendor/autoload.php');

\Stripe\Stripe::setApiKey(STRIPE_API_KEY);

try {
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    $customer = \Stripe\Customer::retrieve($session->customer);

    // Payment details
    $payment_intent = $session->payment_intent;
    $amount_total = $session->amount_total / 100; // Stripe amount is in cents
    $currency = $session->currency;
    $payment_status = $session->payment_status;

    // Ensure the payment was successful
    if ($payment_status === 'paid') {

        // Insert subscription details into the abonnement_commande table
        $stmt = $conn->prepare("INSERT INTO abonnement_commande (id_utilisateur, id_abonnement, montant, date_debut, date_fin) VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))");
        $id_abonnement = 1; // Example id_abonnement, adjust as needed
        $stmt->bind_param("iid", $id_utilisateur, $id_abonnement, $amount_total);
        $stmt->execute();
        $stmt->close();


        // Redirect to the abonnement page with a success message
        header('Location: abonnement.php?success=1');
        exit();
    } else {
        header('Location: abonnement.php?error=subscription_failed');
        exit();
    }
} catch (Exception $e) {
    header('Location: abonnement.php?error=subscription_failed');
    exit();
}

$conn->close();
?>
