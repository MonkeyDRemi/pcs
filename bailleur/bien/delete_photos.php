<?php
session_start();
header('Content-Type: application/json');

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

// Récupérer les données JSON de la requête
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier si l'ID de la photo est fourni dans les données
if (!isset($data['id_photo'])) {
    echo json_encode(['success' => false, 'message' => 'ID de la photo non fourni']);
    exit();
}

$id_photo = $data['id_photo'];
$id_utilisateur = $_SESSION['id_utilisateur'];

require_once '../../include/db.php';

try {
    // Vérifier que la photo appartient à un bien de l'utilisateur
    $sql = "SELECT p.url FROM photo p JOIN bien b ON p.id_bien = b.id_bien WHERE p.id_photo = :id_photo AND b.id_bailleur = :id_utilisateur";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_photo', $id_photo, PDO::PARAM_INT);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $photoPath = $row['url'];
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }
        
        // Supprimer la photo de la base de données
        $sql_delete_photo = "DELETE FROM photo WHERE id_photo = :id_photo";
        $stmt_delete_photo = $pdo->prepare($sql_delete_photo);
        $stmt_delete_photo->bindParam(':id_photo', $id_photo, PDO::PARAM_INT);
        
        if ($stmt_delete_photo->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de la photo']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Photo non trouvée ou non autorisée']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>
