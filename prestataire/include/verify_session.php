<?php
session_start();
require_once '../../include/db.php';

function verify_session($pdo) {
    if (!isset($_SESSION['id_utilisateur']) || !isset($_SESSION['token'])) {
        return false;
    }

    $id_utilisateur = $_SESSION['id_utilisateur'];
    $token = $_SESSION['token'];

    try {
        // Préparer et exécuter la requête SQL
        $sql = "SELECT token_expiration FROM utilisateur WHERE id_utilisateur = :id_utilisateur AND token = :token";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_utilisateur' => $id_utilisateur, ':token' => $token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Vérifier si le token a expiré
            if (strtotime($result['token_expiration']) > time()) {
                return true;
            } else {
                // Token expiré, déconnecter l'utilisateur
                session_destroy();
                return false;
            }
        } else {
            // Token non valide, déconnecter l'utilisateur
            session_destroy();
            return false;
        }
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}

$response = [
    'session_valid' => verify_session($pdo)
];

header('Content-Type: application/json');
echo json_encode($response);
?>
