<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

include '../include/db.php'; // Inclure le fichier de connexion PDO

$sql = "
    SELECT pc.id_commande, p.titre, pc.debut_prestation, pc.fin_prestation, pc.montant, pc.nbr_heure, pc.km, pc.status 
    FROM prestation_commande pc
    JOIN prestation p ON pc.id_prestation = p.id_prestation
    WHERE pc.id_prestataire = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_utilisateur]);
$interventions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($interventions) {
    echo json_encode(['success' => true, 'interventions' => $interventions]);
} else {
    echo json_encode(['success' => false, 'message' => 'Aucune intervention trouvée']);
}
?>
