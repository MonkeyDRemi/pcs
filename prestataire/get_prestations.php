<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

include '../include/db.php'; // Inclure le fichier de connexion PDO

$sql = "SELECT pc.id_commande, pc.debut_prestation, pc.fin_prestation, p.titre, p.montant 
        FROM prestation_commande pc
        JOIN prestation p ON pc.id_prestation = p.id_prestation
        WHERE pc.id_prestataire IS NULL";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$prestations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'prestations' => $prestations]);
?>
