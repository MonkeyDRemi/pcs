<?php
session_start();
header('Content-Type: application/json');

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

// Inclure le fichier de connexion à la base de données
require_once '../../include/db.php';

try {
    // Récupérer les factures de l'utilisateur
    $sql = "SELECT id_facture, date_facture, download_link FROM factures WHERE id_utilisateur = :id_utilisateur";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt->execute();
    $factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les gains générés par les biens réservés
    $sql_biens = "
        SELECT SUM(reservations.montant) AS total_gains_biens
        FROM reservations
        INNER JOIN biens ON reservations.id_bien = biens.id_bien
        WHERE biens.id_proprietaire = :id_utilisateur AND reservations.status = 'confirmed'
    ";
    $stmt_biens = $pdo->prepare($sql_biens);
    $stmt_biens->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt_biens->execute();
    $total_gains_biens = $stmt_biens->fetch(PDO::FETCH_ASSOC)['total_gains_biens'];

    // Récupérer les gains générés par les services réservés
    $sql_services = "
        SELECT SUM(reservations.montant) AS total_gains_services
        FROM reservations
        INNER JOIN services ON reservations.id_service = services.id_service
        WHERE services.id_prestataire = :id_utilisateur AND reservations.status = 'confirmed'
    ";
    $stmt_services = $pdo->prepare($sql_services);
    $stmt_services->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt_services->execute();
    $total_gains_services = $stmt_services->fetch(PDO::FETCH_ASSOC)['total_gains_services'];

    echo json_encode([
        'success' => true,
        'factures' => $factures,
        'total_gains_biens' => $total_gains_biens,
        'total_gains_services' => $total_gains_services
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de requête : ' . $e->getMessage()]);
}
?>
