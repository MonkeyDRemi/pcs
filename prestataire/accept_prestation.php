<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$id_commande = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id_commande <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de commande invalide']);
    exit();
}

include '../include/db.php';

$sql = "UPDATE prestation_commande SET id_prestataire = ? WHERE id_commande = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_utilisateur, $id_commande]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'Prestation acceptée avec succès']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'acceptation de la prestation']);
}
