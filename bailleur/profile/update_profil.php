<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$email = $_POST['email'];
$mot_de_passe = !empty($_POST['mot_de_passe']) ? password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT) : null;
$numero_telephone = $_POST['numero_telephone'];
$iban = $_POST['iban'];
$bic = $_POST['bic'];

require_once '../../include/db.php';

try {
    // Préparer la requête SQL
    $sql = "UPDATE utilisateur SET email = :email, numero_telephone = :numero_telephone, iban = :iban, bic = :bic";
    if ($mot_de_passe) {
        $sql .= ", mot_de_passe = :mot_de_passe";
    }
    $sql .= " WHERE id_utilisateur = :id_utilisateur";

    $stmt = $pdo->prepare($sql);
    
    // Lier les paramètres
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':numero_telephone', $numero_telephone);
    $stmt->bindParam(':iban', $iban);
    $stmt->bindParam(':bic', $bic);
    if ($mot_de_passe) {
        $stmt->bindParam(':mot_de_passe', $mot_de_passe);
    }
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);

    // Exécuter la requête
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du profil']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de requête : ' . $e->getMessage()]);
}
?>
