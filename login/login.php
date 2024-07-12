<?php
require_once '../include/db.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['email']) || !isset($input['mot_de_passe']) || !isset($input['type_utilisateur'])) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes']);
            exit();
        }

        $email = $input['email'];
        $mot_de_passe = $input['mot_de_passe'];
        $type_utilisateur = $input['type_utilisateur'];


        $sql = "SELECT id_utilisateur, mot_de_passe, bailleur_accept, bailleur_refus, prestataire_accept, prestataire_refus FROM utilisateur WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Email incorrect']);
            exit();
        }

        if (!password_verify($mot_de_passe, $user['mot_de_passe'])) {
            echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
            exit();
        }

        if (($type_utilisateur == 'bailleur' && $user['bailleur_accept'] != 1) ||
            ($type_utilisateur == 'prestataire' && $user['prestataire_accept'] != 1)) {
            $message = ($user['bailleur_refus'] == 1 || $user['prestataire_refus'] == 1) ? 'Vous êtes bloqué ou vous avez été refusé' : 'Vous êtes en attente de validation';
            echo json_encode(['success' => false, 'message' => $message]);
            exit();
        }

        $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
        $_SESSION['token'] = bin2hex(random_bytes(16));
        $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "UPDATE utilisateur SET token = ?, token_expiration = ? WHERE id_utilisateur = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['token'], $expiration, $user['id_utilisateur']]);

        $redirect_url = '';
        if ($type_utilisateur == 'bailleur') {
            $redirect_url = '../bailleur/profile/index.php';
        } elseif ($type_utilisateur == 'prestataire') {
            $redirect_url = '../prestataire/index.php';
        } elseif ($type_utilisateur == 'voyageur') {
            $redirect_url = '../accueil/index.php';
        }

        echo json_encode(['success' => true, 'redirect_url' => $redirect_url]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => "Erreur de base de données : " . $e->getMessage()]);
        exit();
    }
}
?>
