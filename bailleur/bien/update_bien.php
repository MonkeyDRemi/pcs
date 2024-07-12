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
    $title = $_POST['title'];
    $description = $_POST['description'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $code_postal = $_POST['code_postal'];
    $type_bien = $_POST['type_bien'];
    $prix = $_POST['prix'];
    $meuble = isset($_POST['meuble']) ? 1 : 0;
    $duree_location = $_POST['duree_location'];
    $salon = $_POST['salon'];
    $cuisine = $_POST['cuisine'];
    $salle_de_bain = $_POST['salle_de_bain'];
    $toilette = $_POST['toilette'];
    $chambre = $_POST['chambre'];
    $nbr_personne_max = $_POST['nbr_personne_max'];
    $superficie = $_POST['superficie'];

    $sql = "UPDATE bien SET title = :title, description = :description, address = :address, city = :city, code_postal = :code_postal, type_bien = :type_bien, prix = :prix, meuble = :meuble, duree_location = :duree_location, salon = :salon, cuisine = :cuisine, salle_de_bain = :salle_de_bain, toilette = :toilette, chambre = :chambre, nbr_personne_max = :nbr_personne_max, superficie = :superficie 
            WHERE id_bien = :id_bien AND id_bailleur = :id_utilisateur";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':address', $address, PDO::PARAM_STR);
    $stmt->bindParam(':city', $city, PDO::PARAM_STR);
    $stmt->bindParam(':code_postal', $code_postal, PDO::PARAM_STR);
    $stmt->bindParam(':type_bien', $type_bien, PDO::PARAM_STR);
    $stmt->bindParam(':prix', $prix, PDO::PARAM_INT);
    $stmt->bindParam(':meuble', $meuble, PDO::PARAM_INT);
    $stmt->bindParam(':duree_location', $duree_location, PDO::PARAM_STR);
    $stmt->bindParam(':salon', $salon, PDO::PARAM_INT);
    $stmt->bindParam(':cuisine', $cuisine, PDO::PARAM_INT);
    $stmt->bindParam(':salle_de_bain', $salle_de_bain, PDO::PARAM_INT);
    $stmt->bindParam(':toilette', $toilette, PDO::PARAM_INT);
    $stmt->bindParam(':chambre', $chambre, PDO::PARAM_INT);
    $stmt->bindParam(':nbr_personne_max', $nbr_personne_max, PDO::PARAM_INT);
    $stmt->bindParam(':superficie', $superficie, PDO::PARAM_INT);
    $stmt->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if (!empty($_FILES['photos']['name'][0])) {
            $photo_dir = "../img/bien/";
            if (!is_dir($photo_dir)) {
                mkdir($photo_dir, 0777, true);
            }

            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                $photo_name = $id_bien . "_" . $id_utilisateur . "_" . ($key + 1) . "." . pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
                $photo_path = $photo_dir . $photo_name;

                if (move_uploaded_file($tmp_name, $photo_path)) {
                    $sql_photo = "INSERT INTO photo (url, id_bien) VALUES (:photo_path, :id_bien)";
                    $stmt_photo = $pdo->prepare($sql_photo);
                    $stmt_photo->bindParam(':photo_path', $photo_path, PDO::PARAM_STR);
                    $stmt_photo->bindParam(':id_bien', $id_bien, PDO::PARAM_INT);
                    $stmt_photo->execute();
                }
            }
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>
