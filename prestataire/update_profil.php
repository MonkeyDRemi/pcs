<?php
session_start();
include('../include/db.php'); // Inclusion du fichier de configuration PDO

header('Content-Type: application/json');

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$email = $_POST['email'];
$mot_de_passe = $_POST['mot_de_passe'];
$numero_telephone = $_POST['numero_telephone'];

try {
    // Préparation de la requête SQL en fonction de la présence ou non d'un nouveau mot de passe
    if (!empty($mot_de_passe)) {
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);
        $sql = "UPDATE utilisateur SET email = ?, mot_de_passe = ?, numero_telephone = ? WHERE id_utilisateur = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $mot_de_passe_hash, $numero_telephone, $id_utilisateur]);
    } else {
        $sql = "UPDATE utilisateur SET email = ?, numero_telephone = ? WHERE id_utilisateur = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $numero_telephone, $id_utilisateur]);
    }

    // Vérification de la réussite de la mise à jour du profil
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucune modification effectuée']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du profil: ' . $e->getMessage()]);
}

?>
