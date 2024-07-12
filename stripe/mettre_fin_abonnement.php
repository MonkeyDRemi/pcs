<?php
session_start();
require_once '../signup/db.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../login/index.php');
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur']; // Use session variable instead
$id_abonnement = $_POST['id_abonnement']; // Get only id_abonnement from POST

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Mettre fin à l'abonnement actif
    $sql = "UPDATE abonnement_commande SET date_fin = NOW() WHERE id_utilisateur = :id_utilisateur AND id_abonnement = :id_abonnement AND date_fin > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt->bindParam(':id_abonnement', $id_abonnement, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header('Location: abonnement.php?success=unsubscribe');
        exit();
    } else {
        header('Location: abonnement.php?error=unsubscribe_failed');
        exit();
    }

} catch (PDOException $e) {
    // Log error
    error_log("Database error: " . $e->getMessage());
    header('Location: abonnement.php?error=' . urlencode('Erreur : ' . $e->getMessage()));
    exit();
}
?>
