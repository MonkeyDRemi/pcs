<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "esgi";
$dbname = "pcs5";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

$id_bien = $_POST['id_bien'];
$date_debut = $_POST['date_debut'];
$date_fin = $_POST['date_fin'];
$id_utilisateur = $_SESSION['id_utilisateur'];

// Vérifier si l'utilisateur a déjà une réservation pour ce bien
$sql_check = "SELECT COUNT(*) AS count FROM occupation WHERE id_bien = ? AND id_utilisateur = ? AND status = 'blocked'";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $id_bien, $id_utilisateur);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$row_check = $result_check->fetch_assoc();

if ($row_check['count'] > 0) {
    echo json_encode(['error' => 'already_reserved']);
} else {
    // Vérifier si les dates de réservation se chevauchent avec une réservation existante
    $sql_overlap = "SELECT COUNT(*) AS count FROM occupation WHERE id_bien = ? AND status = 'blocked' AND ((date_debut <= ? AND date_fin >= ?) OR (date_debut <= ? AND date_fin >= ?))";
    $stmt_overlap = $conn->prepare($sql_overlap);
    $stmt_overlap->bind_param("issss", $id_bien, $date_fin, $date_debut, $date_debut, $date_fin);
    $stmt_overlap->execute();
    $result_overlap = $stmt_overlap->get_result();
    $row_overlap = $result_overlap->fetch_assoc();

    if ($row_overlap['count'] > 0) {
        echo json_encode(['error' => 'dates_overlap']);
    } else {
        $sql = "INSERT INTO occupation (date_debut, date_fin, status, id_bien, id_utilisateur) VALUES (?, ?, 'blocked', ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $date_debut, $date_fin, $id_bien, $id_utilisateur);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Reservation made successfully.']);
        } else {
            echo json_encode(['error' => 'Database error']);
        }

        $stmt->close();
    }

    $stmt_overlap->close();
}

$stmt_check->close();
$conn->close();
?>
