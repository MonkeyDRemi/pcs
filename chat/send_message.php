<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_utilisateur'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit();
}

$id_sender = $_SESSION['id_utilisateur'];
$id_recipient = $_POST['id_recipient'];
$message = $_POST['message'];
$id_prestataire = $_POST['id_prestataire'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pcs5";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

$sql = "INSERT INTO message (message, date_message, id_bailleur, id_voyageur, id_prestataire) VALUES (?, NOW(), ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("siii", $message, $id_sender, $id_recipient, $id_prestataire);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du message']);
}

$stmt->close();
$conn->close();
?>
