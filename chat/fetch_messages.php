<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$id_other_user = $_GET['id_other_user'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pcs5";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

$sql = "
    SELECT * FROM message 
    WHERE (id_bailleur = ? AND id_voyageur = ?) 
       OR (id_bailleur = ? AND id_voyageur = ?) 
    ORDER BY date_message ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $id_utilisateur, $id_other_user, $id_other_user, $id_utilisateur);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode(['success' => true, 'messages' => $messages]);

$stmt->close();
$conn->close();
?>
