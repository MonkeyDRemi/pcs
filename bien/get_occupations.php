<?php

$dsn = 'mysql:host=localhost;dbname=pcs5';
$username = 'root';
$password = '';
$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}

$id_bien = isset($_GET['id_bien']) ? intval($_GET['id_bien']) : 0;

if ($id_bien > 0) {
   
    $sql = "SELECT date_debut, date_fin, raison_indispo, status 
            FROM occupation 
            WHERE id_bien = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_bien]);
} else {
    
    http_response_code(400);
    echo json_encode(['error' => 'Aucun ID de bien spécifié']);
    exit;
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
