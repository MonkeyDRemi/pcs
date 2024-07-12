<?php
require_once '../include/db.php';

try {

    $whereClauses = [];
    $params = [];

    if (!empty($_GET['city'])) {
        $whereClauses[] = "b.city = :city";
        $params[':city'] = $_GET['city'];
    }

    if (!empty($_GET['type_bien'])) {
        $whereClauses[] = "b.type_bien = :type_bien";
        $params[':type_bien'] = $_GET['type_bien'];
    }

    if (!empty($_GET['prix_min'])) {
        $whereClauses[] = "b.prix >= :prix_min";
        $params[':prix_min'] = $_GET['prix_min'];
    }

    if (!empty($_GET['prix_max'])) {
        $whereClauses[] = "b.prix <= :prix_max";
        $params[':prix_max'] = $_GET['prix_max'];
    }

    $sql = "SELECT b.*, p.url AS photo_url 
            FROM bien b
            LEFT JOIN photo p ON b.id_bien = p.id_bien";
    if (!empty($whereClauses)) {
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $biens = [];
    foreach ($rows as $row) {
        $id_bien = $row['id_bien'];
        if (!isset($biens[$id_bien])) {
            $biens[$id_bien] = [
                'id_bien' => $row['id_bien'],
                'title' => $row['title'],
                'description' => $row['description'],
                'city' => $row['city'],
                'prix' => $row['prix'],
                'photos' => []
            ];
        }
        if (!empty($row['photo_url'])) {
            $biens[$id_bien]['photos'][] = $row['photo_url'];
        }
    }

    echo json_encode(array_values($biens));

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
