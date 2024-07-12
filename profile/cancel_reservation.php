<?php
session_start();
include('../include/db.php'); 
header('Content-Type: application/json');

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
    exit;
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$id_occupation = $_POST['id'];

try {
    // Vérifier si la réservation existe pour cet utilisateur
    $sql = "SELECT date_debut FROM occupation WHERE id_occupation = ? AND id_utilisateur = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_occupation, $id_utilisateur]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reservation) {
        $date_debut = new DateTime($reservation['date_debut']);
        $one_week_before = new DateTime($reservation['date_debut']);
        $one_week_before->modify('-1 week');
        $now = new DateTime();

        if ($now < $one_week_before) {
            // Si la date actuelle est à plus d'une semaine avant la date de début
            $sql = "DELETE FROM occupation WHERE id_occupation = ? AND id_utilisateur = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$id_occupation, $id_utilisateur])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'annulation de la réservation']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas annuler une réservation moins d\'une semaine avant la date de début']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Réservation non trouvée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}

?>
