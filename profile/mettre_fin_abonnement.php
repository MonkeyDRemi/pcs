<?php
session_start();
require_once '../include/db.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../../login/index.php');
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$id_abonnement = $_POST['id_abonnement'];

try {
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
    header('Location: abonnement.php?error=' . urlencode('Erreur : ' . $e->getMessage()));
    exit();
}
?>
