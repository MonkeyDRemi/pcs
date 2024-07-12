<?php
session_start();
header('Content-Type: application/json');

// Inclure le fichier de connexion à la base de données
require_once '../../../include/db.php';

// Vérification de la méthode POST et des paramètres requis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

if (!isset($_POST['date_debut'], $_POST['date_fin'], $_POST['status'], $_POST['id_bien'], $_POST['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit();
}

// Paramètres de la mise à jour
$date_debut = $_POST['date_debut'];
$date_fin = $_POST['date_fin'];
$status = $_POST['status'];
$id_bien = $_POST['id_bien'];
$id_utilisateur = $_POST['id_utilisateur'];

try {
    // Convertir les dates en DateTime pour itération
    $start = new DateTime($date_debut);
    $end = new DateTime($date_fin);
    $end->modify('+0 day'); // Inclure la date de fin

    $interval = new DateInterval('P1D');
    $daterange = new DatePeriod($start, $interval, $end);

    // Préparer la transaction pour les mises à jour atomiques
    $pdo->beginTransaction();

    foreach ($daterange as $date) {
        $current_date = $date->format("Y-m-d");

        if ($status == 'available') {
            // Supprimer l'enregistrement si le statut est "Disponible"
            $sql = "DELETE FROM occupation
                    WHERE id_bien = :id_bien
                    AND date_debut <= :current_date
                    AND date_fin >= :current_date";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_bien' => $id_bien,
                ':current_date' => $current_date
            ]);
        } else {
            // Vérifier si l'enregistrement existe déjà
            $sql = "SELECT COUNT(*) AS nb
                    FROM occupation
                    WHERE id_bien = :id_bien
                    AND date_debut <= :current_date
                    AND date_fin >= :current_date";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_bien' => $id_bien,
                ':current_date' => $current_date
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['nb'] > 0) {
                // Mettre à jour le statut si l'enregistrement existe
                $sql = "UPDATE occupation
                        SET status = :status
                        WHERE id_bien = :id_bien
                        AND date_debut <= :current_date
                        AND date_fin >= :current_date";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':status' => $status,
                    ':id_bien' => $id_bien,
                    ':current_date' => $current_date
                ]);
            } else {
                // Insérer un nouvel enregistrement si l'enregistrement n'existe pas
                $sql = "INSERT INTO occupation (date_debut, date_fin, status, id_bien, id_utilisateur)
                        VALUES (:current_date, :current_date, :status, :id_bien, :id_utilisateur)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':current_date' => $current_date,
                    ':status' => $status,
                    ':id_bien' => $id_bien,
                    ':id_utilisateur' => $id_utilisateur
                ]);
            }
        }
    }

    // Valider la transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Statut des dates mis à jour avec succès.']);

} catch (PDOException $e) {
    // En cas d'erreur, annuler la transaction
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>
