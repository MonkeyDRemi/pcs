<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$type = $_GET['type'];
$id = $_GET['id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pcs5";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

if ($type == 'bien') {
    $sql = "SELECT id_bailleur AS id_user FROM bien WHERE id_bien = ? AND id_bailleur != ?";
} else if ($type == 'prestation') {
    $sql = "SELECT id_prestataire AS id_user FROM prestation_commande WHERE id_commande = ? AND id_utilisateur = ?";
} else {
    echo json_encode(['success' => false, 'message' => 'Type non valide']);
    exit();
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $id_utilisateur);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
}

$stmt->close();
$conn->close();
?>
