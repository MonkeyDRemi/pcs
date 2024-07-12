<?php
session_start();
header('Content-Type: application/json');

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

// Inclure le fichier de connexion à la base de données
require_once '../../include/db.php';

try {
    // Requête pour récupérer les biens réservés par des voyageurs
    $sql = "
        SELECT b.title, o.date_debut, o.date_fin, u.nom, u.prenom, u.email
        FROM occupation o
        JOIN bien b ON o.id_bien = b.id_bien
        JOIN utilisateur u ON o.id_utilisateur = u.id_utilisateur
        WHERE b.id_bailleur = :id_utilisateur
        ORDER BY o.date_debut DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'reservations' => $reservations]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de requête : ' . $e->getMessage()]);
}
?>
