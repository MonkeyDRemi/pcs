<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

if (!isset($_POST['interventionId']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
    exit();
}

$id_commande = $_POST['interventionId'];
$status = $_POST['status'];
$nbr_heure = isset($_POST['nbr_heure']) ? $_POST['nbr_heure'] : null;
$km = isset($_POST['km']) ? $_POST['km'] : null;

include '../include/db.php'; // Inclure le fichier de connexion PDO

// Récupérer le montant et le type de tarification de la prestation associée à la commande
$sql = "SELECT p.montant, p.type_tarification 
        FROM prestation_commande pc 
        JOIN prestation p ON pc.id_prestation = p.id_prestation 
        WHERE pc.id_commande = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_commande]);
$prestation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prestation) {
    echo json_encode(['success' => false, 'message' => 'Prestation non trouvée']);
    exit();
}

$stmt->closeCursor(); // Fermer le curseur pour permettre la réutilisation de la connexion

$montant_base = $prestation['montant'];
$type_tarification = $prestation['type_tarification'];

// Calculer le montant en fonction du type de tarification
if ($type_tarification == 'heure' && $nbr_heure !== null) {
    $montant = $montant_base * $nbr_heure;
} elseif ($type_tarification == 'kilometre' && $km !== null) {
    $montant = $montant_base * $km;
} else {
    $montant = $montant_base;
}

// Mettre à jour la commande avec le nouveau statut, montant, nombre d'heures et kilomètres
$sql = "UPDATE prestation_commande SET status = :status, montant = :montant, nbr_heure = :nbr_heure, km = :km WHERE id_commande = :id_commande";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->bindParam(':montant', $montant, PDO::PARAM_STR);
$stmt->bindParam(':nbr_heure', $nbr_heure, PDO::PARAM_INT);
$stmt->bindParam(':km', $km, PDO::PARAM_INT);
$stmt->bindParam(':id_commande', $id_commande, PDO::PARAM_INT);

try {
    $stmt->execute();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour de l\'intervention: ' . $e->getMessage()]);
}

$stmt->closeCursor();
?>
