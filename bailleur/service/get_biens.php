<?php
session_start();
header('Content-Type: application/json');

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "esgi";
$dbname = "pcs5";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Requête pour récupérer les biens
$sql = "SELECT id_bien, title, creation FROM bien WHERE id_bailleur = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utilisateur);
$stmt->execute();
$result = $stmt->get_result();

$biens = [];
while ($row = $result->fetch_assoc()) {
    $biens[] = $row;
}

echo json_encode(['success' => true, 'biens' => $biens]);

$stmt->close();
$conn->close();
?>
