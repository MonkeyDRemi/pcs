<?php
session_start();
require_once '../include/db.php';

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

try {
    $sql = "SELECT a.type, ac.date_debut, ac.date_fin FROM abonnement_commande ac
            JOIN abonnement a ON ac.id_abonnement = a.id_abonnement
            WHERE ac.id_utilisateur = :id_utilisateur";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt->execute();
    $abonnements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($abonnements)) {
        echo json_encode(['success' => true, 'abonnements' => $abonnements]);
    } else {
        echo json_encode(['success' => true, 'abonnements' => [], 'message' => 'Vous n\'avez pas encore d\'abonnements.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des abonnements : ' . $e->getMessage()]);
}
?>
