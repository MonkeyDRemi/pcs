<?php
session_start();
header('Content-Type: application/json');

require_once '../include/db.php'; // Inclure le fichier de configuration de la connexion PDO

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
    exit;
}

$id_utilisateur = $_SESSION['id_utilisateur'];

try {
    $sql = "SELECT bien.title, occupation.date_debut, occupation.date_fin, occupation.id_occupation
            FROM occupation
            JOIN bien ON occupation.id_bien = bien.id_bien
            WHERE occupation.id_utilisateur = :id_utilisateur";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'reservations' => $reservations]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des réservations: ' . $e->getMessage()]);
}
?>
