<?php
session_start();
require_once '../include/db.php'; 

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../login/index.php");
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$email = isset($_POST['email']) ? $_POST['email'] : '';
$mot_de_passe = isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '';
$numero_telephone = isset($_POST['numero_telephone']) ? $_POST['numero_telephone'] : '';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=pcs4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!empty($mot_de_passe)) {
        $mot_de_passe = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $sql = "UPDATE utilisateur SET email = :email, mot_de_passe = :mot_de_passe, numero_telephone = :numero_telephone WHERE id_utilisateur = :id_utilisateur";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':mot_de_passe', $mot_de_passe, PDO::PARAM_STR);
    } else {
        $sql = "UPDATE utilisateur SET email = :email, numero_telephone = :numero_telephone WHERE id_utilisateur = :id_utilisateur";
        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':numero_telephone', $numero_telephone, PDO::PARAM_STR);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

    $stmt->execute();

    echo json_encode(['message' => 'Profil mis à jour avec succès.']);

} catch (PDOException $e) {
    echo json_encode(['message' => 'Erreur lors de la mise à jour du profil : ' . $e->getMessage()]);
}
?>
