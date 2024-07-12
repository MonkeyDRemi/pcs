<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$id_commande = isset($_GET['id']) ? intval($_GET['id']) : 0;

include '../include/db.php';

$sql = "SELECT id_commande, status, nbr_heure, km, p.type_tarification 
        FROM prestation_commande pc 
        JOIN prestation p ON pc.id_prestation = p.id_prestation 
        WHERE id_commande = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_commande]);
$intervention = $stmt->fetch(PDO::FETCH_ASSOC);

if ($intervention) {
    echo json_encode(['success' => true, 'intervention' => $intervention]);
} else {
    echo json_encode(['success' => false, 'message' => 'Intervention non trouvée']);
}
?>
