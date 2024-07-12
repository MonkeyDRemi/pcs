<?php
session_start();
require_once '../include/db.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../../login/index.php');
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$id_abonnement = $_POST['id_abonnement'];
$date_debut = date('Y-m-d');
$date_fin = date('Y-m-d', strtotime('+1 year'));

try {
    $pdo = new PDO('mysql:host=localhost;dbname=pcs5', 'root', 'esgi');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT COUNT(*) FROM abonnement_commande WHERE id_utilisateur = :id_utilisateur AND date_fin > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        header('Location: abonnement.php?error=already_subscribed');
        exit();
    } else {
    
        $sql = "SELECT montant FROM abonnement WHERE id_abonnement = :id_abonnement";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_abonnement', $id_abonnement, PDO::PARAM_INT);
        $stmt->execute();
        $montant = $stmt->fetchColumn();


        $sql = "INSERT INTO abonnement_commande (id_utilisateur, id_abonnement, montant, date_debut, date_fin)
                VALUES (:id_utilisateur, :id_abonnement, :montant, :date_debut, :date_fin)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
        $stmt->bindParam(':id_abonnement', $id_abonnement, PDO::PARAM_INT);
        $stmt->bindParam(':montant', $montant, PDO::PARAM_STR);
        $stmt->bindParam(':date_debut', $date_debut, PDO::PARAM_STR);
        $stmt->bindParam(':date_fin', $date_fin, PDO::PARAM_STR);

        if ($stmt->execute()) {
            header("Location: abonnement.php?success=1");
            exit();
        } else {
            header('Location: abonnement.php?error=subscription_failed');
            exit();
        }
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
