<?php
session_start();
header('Content-Type: application/json');

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

// Récupérer l'ID du bien à supprimer
$id_bien = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_utilisateur = $_SESSION['id_utilisateur'];

require_once '../../include/db.php';

try {
    // Vérifier que le bien appartient à l'utilisateur
    $sql = "SELECT id_bien FROM bien WHERE id_bien = :id_bien AND id_bailleur = :id_utilisateur";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Supprimer les photos associées
        $sql_photo = "SELECT url FROM photo WHERE id_bien = :id_bien";
        $stmt_photo = $pdo->prepare($sql_photo);
        $stmt_photo->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
        $stmt_photo->execute();
        $photos = $stmt_photo->fetchAll(PDO::FETCH_ASSOC);

        foreach ($photos as $photo) {
            $photoPath = $photo['url'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        // Supprimer les enregistrements de photos dans la base de données
        $sql_delete_photos = "DELETE FROM photo WHERE id_bien = :id_bien";
        $stmt_delete_photos = $pdo->prepare($sql_delete_photos);
        $stmt_delete_photos->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
        $stmt_delete_photos->execute();

        // Supprimer le bien lui-même
        $sql_delete_bien = "DELETE FROM bien WHERE id_bien = :id_bien";
        $stmt_delete_bien = $pdo->prepare($sql_delete_bien);
        $stmt_delete_bien->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
        
        if ($stmt_delete_bien->execute()) {
            echo json_encode(['success' => true, 'message' => 'Bien supprimé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du bien']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Bien non trouvé ou non autorisé']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>
