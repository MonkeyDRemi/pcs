<?php
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../../login/index.php');
    exit();
}

require_once '../../include/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_utilisateur = $_SESSION['id_utilisateur'];

    // Vérifier si l'utilisateur est prestataire
    $sql = "SELECT prestataire_accept FROM utilisateur WHERE id_utilisateur = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_utilisateur]);
    $prestataire_accept = $stmt->fetchColumn();
    $stmt->closeCursor();

    if ($prestataire_accept) {
        die("Vous ne pouvez pas ajouter un bien car vous êtes prestataire.");
    }

    // Récupérer les données du formulaire
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

    try {
        // Début de la transaction
        $pdo->beginTransaction();

        // Insérer le bien dans la table 'bien'
        $sql = "INSERT INTO bien (title, description, address, city, code_postal, type_bien, prix, meuble, duree_location, salon, cuisine, salle_de_bain, toilette, chambre, nbr_personne_max, superficie, id_bailleur)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $description, $address, $city, $code_postal, $type_bien, $prix, $meuble, $duree_location, $salon, $cuisine, $salle_de_bain, $toilette, $chambre, $nbr_personne_max, $superficie, $id_utilisateur]);
        $id_bien = $pdo->lastInsertId();

        // Répertoire pour les photos
        $photo_dir = "../img/bien/";
        if (!is_dir($photo_dir)) {
            mkdir($photo_dir, 0777, true);
        }

        // Uploader et insérer les photos dans la table 'photo'
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            $photo_name = $id_bien . "_" . $id_utilisateur . "_" . ($key + 1) . "." . pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
            $photo_path = $photo_dir . $photo_name;

            if (move_uploaded_file($tmp_name, $photo_path)) {
                $sql = "INSERT INTO photo (url, id_bien) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$photo_path, $id_bien]);
            }
        }

        // Valider la transaction
        $pdo->commit();

        $_SESSION['message'] = "Bien ajouté avec succès!";
        header("Location: ../profile/index.php");
        exit();

    } catch (PDOException $e) {
        // En cas d'erreur, annuler la transaction
        $pdo->rollBack();
        echo "Erreur : " . $e->getMessage();
    }
}
?>
