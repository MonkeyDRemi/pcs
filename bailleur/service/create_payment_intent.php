<?php
require '../../stripe/vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51PV1HeFXpsoOQTRTQQKe1nm07d6Mbndq71v4pmCAtuJ5V0nBcQ2DvRQnjvB9gbRdp7TldaHsw5XjpdXrXgSzGYqi00rWOYrcsY'); // Replace with your Stripe secret key

$paymentMethodId = isset($_POST['payment_method']) ? $_POST['payment_method'] : null;
$id_commande = isset($_POST['id_commande']) ? $_POST['id_commande'] : null;

if (!$paymentMethodId || !$id_commande) {
    echo json_encode(['error' => 'Missing required parameters.']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pcs5";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the service amount
$sql = "SELECT montant FROM prestation_commande WHERE id_commande = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_commande);
$stmt->execute();
$result = $stmt->get_result();
$service = $result->fetch_assoc();

if (!$service) {
    echo json_encode(['error' => 'Service not found.']);
    exit;
}

$amount = $service['montant'] * 100; // Amount in cents

$stmt->close();
$conn->close();

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => 'eur',
        'payment_method' => $paymentMethodId,
        'confirmation_method' => 'manual',
        'confirm' => true,
        'return_url' => 'http://localhost:8000/bailleur/service/payment_confirmation.php', // Replace with your return URL
    ]);

    if ($paymentIntent->status == 'succeeded') {
        savePaymentDetails($amount, $paymentMethodId, $id_commande, true);
    } else {
        echo json_encode(['client_secret' => $paymentIntent->client_secret]);
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    savePaymentDetails($amount, $paymentMethodId, $id_commande, false, $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

function savePaymentDetails($amount, $paymentMethodId, $id_commande, $isValid, $errorReason = null) {
    $servername = "localhost";
    $username = "root";
    $password = "";
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

    // Fetch the id_bien associated with the id_commande
    $sql_bien = "SELECT id_bien FROM prestation_commande WHERE id_commande = ?";
    $stmt_bien = $conn->prepare($sql_bien);
    $stmt_bien->bind_param("i", $id_commande);
    $stmt_bien->execute();
    $result_bien = $stmt_bien->get_result();
    $commande = $result_bien->fetch_assoc();
    $id_bien = $commande['id_bien'];

    $stmt->bind_param("isdi", $paiementValide, $paymentMethodId, $montant, $id_bien);

    if ($stmt->execute()) {
        $id_paiement = $stmt->insert_id; // Get the inserted id_paiement

        // Update prestation_commande with the new id_paiement
        $sql_update = "UPDATE prestation_commande SET id_paiement = ? WHERE id_commande = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $id_paiement, $id_commande);
        $stmt_update->execute();
        $stmt_update->close();

        echo json_encode(['success' => 'Payment details saved successfully.']);
    } else {
        echo json_encode(['error' => 'Error while saving payment details.']);
    }

    $stmt->close();
    $conn->close();
}
?>
