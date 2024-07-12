<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../../login/index.php');
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$id_prestation = $_POST['id_prestation'];
$id_bien = $_POST['id_bien'];
$nbr_heure = isset($_POST['nbr_heure']) ? $_POST['nbr_heure'] : null;
$km = isset($_POST['km']) ? $_POST['km'] : null;

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pcs5";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Définir les valeurs par défaut pour les autres colonnes
$id_prestataire = null;
$id_paiement = null;  // Laisser MySQL gérer l'auto-incrémentation
$montant = null;  // Le montant sera calculé plus tard
$evaluation = null;
$url_fiche = null;
$debut_prestation = date('Y-m-d');  // Date de début de la prestation, ici date actuelle
$duree = $nbr_heure ? $nbr_heure : 1;  // Durée de la prestation, à ajuster selon le type
$fin_prestation = date('Y-m-d', strtotime("+$duree day"));  // Date de fin de la prestation
$status = 'En cours';
$fiche_url = null;

// Insertion dans la table prestation_commande
$sql = "INSERT INTO prestation_commande (id_utilisateur, id_prestataire, id_bien, id_prestation, montant, evaluation, url_fiche, debut_prestation, duree, fin_prestation, status, fiche_url, nbr_heure, km) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiidississsii", $id_utilisateur, $id_prestataire, $id_bien, $id_prestation, $montant, $evaluation, $url_fiche, $debut_prestation, $duree, $fin_prestation, $status, $fiche_url, $nbr_heure, $km);

if ($stmt->execute()) {
    echo "<p>Réservation réussie !</p>";
} else {
    echo "<p>Erreur lors de la réservation : " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();
?>

<a href="services.php" class="btn btn-primary">Retour aux services</a>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
