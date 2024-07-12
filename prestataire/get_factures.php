<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

include '../include/db.php'; // Inclure le fichier de connexion à la base de données

$sql = "SELECT f.id_facture, f.url, f.date_creation FROM facture f
        JOIN paiement p ON f.id_paiement = p.id_paiement
        JOIN prestation_commande pc ON p.id_paiement = pc.id_paiement
        WHERE pc.id_utilisateur = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_utilisateur]);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($factures) {
    echo json_encode(['success' => true, 'factures' => $factures]);
} else {
    echo json_encode(['success' => false, 'message' => 'Aucune facture trouvée.']);
}
?>
