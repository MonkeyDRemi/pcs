<?php
session_start();
header('Content-Type: application/json');

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

// Récupérer l'ID du bien depuis les paramètres GET
$id_bien = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_utilisateur = $_SESSION['id_utilisateur'];

// Inclure le fichier de connexion à la base de données
require_once '../../include/db.php';

try {
    // Requête pour récupérer les détails du bien
    $sql = "SELECT id_bien, title, description, address, city, code_postal, type_bien, prix, meuble, duree_location, salon, cuisine, salle_de_bain, toilette, chambre, nbr_personne_max, superficie 
            FROM bien 
            WHERE id_bien = :id_bien AND id_bailleur = :id_utilisateur";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt->execute();
    $bien = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($bien) {
        // Récupérer les photos associées au bien
        $sql_photos = "SELECT url FROM photo WHERE id_bien = :id_bien";
        $stmt_photos = $pdo->prepare($sql_photos);
        $stmt_photos->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
        $stmt_photos->execute();
        $photos = $stmt_photos->fetchAll(PDO::FETCH_ASSOC);
        $bien['photos'] = $photos;

        echo json_encode(['success' => true, 'bien' => $bien]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Bien non trouvé ou non autorisé']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>
