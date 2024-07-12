<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
require_once '../../include/db.php';

try {
    // Requête pour récupérer les biens
    $sql = "SELECT id_bien, title, creation FROM bien WHERE id_bailleur = :id_utilisateur";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt->execute();
    $biens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'biens' => $biens]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de requête : ' . $e->getMessage()]);
}
?>
