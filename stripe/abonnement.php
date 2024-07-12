<?php
session_start();
include('../include/header_accueil.php');
include('config.php'); // Adjust the path to config.php as necessary

// Verify if the user is logged in
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../login/index.php');
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

// Database connection
$servername = DB_HOST;
$username = DB_USERNAME;
$password = DB_PASSWORD;
$dbname = DB_NAME;

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Récupérer les abonnements de l'utilisateur
$sql = "SELECT id_abonnement FROM abonnement_commande WHERE id_utilisateur = ? AND date_fin > NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utilisateur);
$stmt->execute();
$result = $stmt->get_result();
$user_abonnements = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $user_abonnements[] = $row['id_abonnement'];
    }
}
$stmt->close();

// Récupérer les abonnements disponibles
$sql = "SELECT * FROM abonnement";
$result = $conn->query($sql);
$abonnements = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $abonnements[] = $row;
    }
}

// Initialize the Stripe client
require_once('vendor/autoload.php');
\Stripe\Stripe::setApiKey(STRIPE_API_KEY);

// Create a Checkout Session
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $abonnement_id = $_POST['id_abonnement'];

    // Get abonnement details
    $sql = "SELECT * FROM abonnement WHERE id_abonnement = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Erreur lors de la préparation de la requête : " . $conn->error;
    }
    $stmt->bind_param("i", $abonnement_id);
    if (!$stmt->execute()) {
        echo "Erreur lors de l'exécution de la requête : " . $stmt->error;
    }
    $result = $stmt->get_result();
    $abonnement = $result->fetch_assoc();
    $stmt->close();

    if ($abonnement) {
        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => STRIPE_CURRENCY,
                        'product_data' => [
                            'name' => $abonnement['type'],
                            'description' => $abonnement['description'],
                        ],
                        'unit_amount' => $abonnement['montant'] * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => 'http://localhost:8000/stripe/success.php?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => 'http://localhost:8000/stripe/cancel.php',
            ]);

            header('Location: ' . $session->url);
            exit();
        } catch (Exception $e) {
            echo "Erreur lors de la création de la session Stripe : " . $e->getMessage();
        }
    } else {
        echo "Erreur : abonnement introuvable.";
        header('Location: abonnement.php?error=subscription_failed');
        exit();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisissez votre abonnement</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<main>
<div class="container">
    <h2>Choisissez votre abonnement</h2>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php
            if ($_GET['error'] == 'already_subscribed') {
                echo "Vous avez déjà un abonnement actif.";
            } elseif ($_GET['error'] == 'subscription_failed') {
                echo "Erreur lors de la souscription à l'abonnement.";
            }
            ?>
        </div>
    <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Abonnement souscrit avec succès !
        </div>
    <?php endif; ?>
    <div class="row">
        <?php foreach ($abonnements as $abonnement): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($abonnement['type']); ?></h5>
                        <p class="card-text"><strong>Prix:</strong> <?php echo htmlspecialchars($abonnement['montant']); ?> €</p>
                        <p class="card-text"><?php echo htmlspecialchars($abonnement['description']); ?></p>
                        <form action="abonnement.php" method="post" style="display:inline;">
                            <input type="hidden" name="id_abonnement" value="<?php echo htmlspecialchars($abonnement['id_abonnement']); ?>">
                            <button type="submit" class="btn btn-primary" <?php echo in_array($abonnement['id_abonnement'], $user_abonnements) ? 'disabled' : ''; ?>>
                                <?php echo in_array($abonnement['id_abonnement'], $user_abonnements) ? 'Souscrit' : 'Souscrire'; ?>
                            </button>
                        </form>
                        <?php if (in_array($abonnement['id_abonnement'], $user_abonnements)): ?>
                            <form action="mettre_fin_abonnement.php" method="post" style="display:inline;">
                                <input type="hidden" name="id_utilisateur" value="<?php echo $id_utilisateur; ?>">
                                <input type="hidden" name="id_abonnement" value="<?php echo htmlspecialchars($abonnement['id_abonnement']); ?>">
                                <button type="submit" class="btn btn-danger">Mettre fin</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</main>
</body>
</html>
