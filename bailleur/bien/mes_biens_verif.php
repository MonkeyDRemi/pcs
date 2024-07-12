<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$id_bien = isset($_GET['id']) ? intval($_GET['id']) : null;

require_once '../../include/db.php';

try {
    $biens = [];

    // Rcupérer les biens spécifiques ou tous les biens de l'utilisateur
    if ($id_bien) {
        $sql = "SELECT id_bien, title, description, address, city, code_postal, type_bien, prix, meuble, duree_location, salon, cuisine, salle_de_bain, toilette, chambre, nbr_personne_max, superficie 
                FROM bien 
                WHERE id_bailleur = :id_utilisateur AND id_bien = :id_bien";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
        $stmt->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
    } else {
        $sql = "SELECT id_bien, title, description, address, city, code_postal, type_bien, prix, meuble, duree_location, salon, cuisine, salle_de_bain, toilette, chambre, nbr_personne_max, superficie 
                FROM bien 
                WHERE id_bailleur = :id_utilisateur";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    }

    $stmt->execute();
    $biens_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($biens_data as $bien) {
        // Récupérer les photos associées à chaque bien
        $sql_photos = "SELECT id_photo, url FROM photo WHERE id_bien = :id_bien";
        $stmt_photos = $pdo->prepare($sql_photos);
        $stmt_photos->bindParam(':id_bien', $bien['id_bien'], PDO::PARAM_INT);
        $stmt_photos->execute();
        $photos = $stmt_photos->fetchAll(PDO::FETCH_ASSOC);
        $bien['photos'] = $photos;
        $biens[] = $bien;
    }

    echo json_encode(['success' => true, 'biens' => $biens]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>
