<?php
require '../stripe/vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51PV1HeFXpsoOQTRTQQKe1nm07d6Mbndq71v4pmCAtuJ5V0nBcQ2DvRQnjvB9gbRdp7TldaHsw5XjpdXrXgSzGYqi00rWOYrcsY'); // Replace with your Stripe secret key

$paymentMethodId = isset($_POST['payment_method']) ? $_POST['payment_method'] : null;
$amount = isset($_POST['amount']) ? $_POST['amount'] : null;
$id_bien = isset($_POST['id_bien']) ? $_POST['id_bien'] : null;
$date_debut = isset($_POST['date_debut']) ? $_POST['date_debut'] : null;
$date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : null;

if (!$paymentMethodId || !$amount || !$id_bien || !$date_debut || !$date_fin) {
    echo json_encode(['error' => 'Missing required parameters.']);
    exit;
}

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => 'eur',
        'payment_method' => $paymentMethodId,
        'confirmation_method' => 'manual',
        'confirm' => true,
        'return_url' => 'http://localhost/bien/payment_confirmation.php', 
    ]);

    if ($paymentIntent->status == 'succeeded') {
        reserveProperty($date_debut, $date_fin, $id_bien);
        savePaymentDetails($amount, $paymentMethodId, $id_bien, true);
    } else {
        echo json_encode(['client_secret' => $paymentIntent->client_secret]);
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    savePaymentDetails($amount, $paymentMethodId, $id_bien, false, $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

function reserveProperty($date_debut, $date_fin, $id_bien) {
    $servername = "localhost";
    $username = "root";
    $password = "esgi";
    $dbname = "pcs5";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $id_utilisateur = $_SESSION['id_utilisateur'];

    $sql = "INSERT INTO occupation (date_debut, date_fin, status, id_bien, id_utilisateur) VALUES (?, ?, 'blocked', ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $date_debut, $date_fin, $id_bien, $id_utilisateur);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Reservation made successfully.']);
    } else {
        echo json_encode(['error' => 'Error while making reservation.']);
    }

    $stmt->close();
    $conn->close();
}

function savePaymentDetails($amount, $paymentMethodId, $id_bien, $isValid, $errorReason = null) {
    $servername = "localhost";
    $username = "root";
    $password = "esgi";
    $dbname = "pcs5";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $id_utilisateur = $_SESSION['id_utilisateur'];

    $sql = "INSERT INTO paiement (date_paiement, paiement_valide, paiement_methode, montant, id_bien) VALUES (NOW(), ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $paiementValide = $isValid ? 1 : 0;
    $montant = $amount / 100; // Convert amount from cents to euros
    $stmt->bind_param("isdi", $paiementValide, $paymentMethodId, $montant, $id_bien);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Payment details saved successfully.']);
    } else {
        echo json_encode(['error' => 'Error while saving payment details.']);
    }

    $stmt->close();
    $conn->close();
}
