<?php
session_start();
header('Content-Type: application/json');

require_once '../../../include/db.php';

$id_bien = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    if ($id_bien > 0) {
        // Récupérer les occupations pour le bien spécifique
        $sql = "SELECT date_debut, date_fin, raison_indispo, status 
                FROM occupation 
                WHERE id_bien = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_bien]);
    } else {
        // Récupérer toutes les occupations si aucun id_bien n'est spécifié
        $sql = "SELECT date_debut, date_fin, raison_indispo, status 
                FROM occupation";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    $occupations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($occupations as $occupation) {
        if ($occupation['status'] == 'blocked') {
            $events[] = [
                'start' => $occupation['date_debut'],
                'end' => $occupation['date_fin'],
                'title' => 'Bloquée',
                'color' => 'red',
                'extendedProps' => [
                    'raison_indispo' => $occupation['raison_indispo']
                ]
            ];
        }
    }

    echo json_encode($events);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?>
