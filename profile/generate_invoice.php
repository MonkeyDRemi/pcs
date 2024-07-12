<?php
require('../fpdf186/fpdf.php');
session_start();
ob_start();
include('../include/db.php'); // Inclusion du fichier de configuration PDO

$id_utilisateur = $_SESSION['id_utilisateur'];
$id_reservation = $_GET['id'];

try {
    // Récupérer les détails de la réservation depuis la base de données
    $sql = "SELECT o.*, b.title, b.prix, u.nom AS hote, u.email AS hote_email
            FROM occupation o
            JOIN bien b ON o.id_bien = b.id_bien
            JOIN utilisateur u ON b.id_bailleur = u.id_utilisateur
            WHERE o.id_occupation = ? AND o.id_utilisateur = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_reservation, $id_utilisateur]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        die("Reservation not found.");
    }

    $checkin = new DateTime($reservation['date_debut']);
    $checkout = new DateTime($reservation['date_fin']);
    $interval = $checkin->diff($checkout);
    $days = $interval->days;

    $price_per_night = $reservation['prix'];
    $total_price = $days * $price_per_night;

    // Générer le PDF de la facture
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    $pdf->Cell(0, 10, 'Invoice', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Reservation ID: ' . $reservation['id_occupation'], 0, 1);
    $pdf->Cell(0, 10, 'Property: ' . $reservation['title'], 0, 1);
    $pdf->Cell(0, 10, 'Host: ' . $reservation['hote'], 0, 1);
    $pdf->Cell(0, 10, 'Host Email: ' . $reservation['hote_email'], 0, 1);
    $pdf->Cell(0, 10, 'Check-in: ' . $reservation['date_debut'], 0, 1);
    $pdf->Cell(0, 10, 'Check-out: ' . $reservation['date_fin'], 0, 1);
    $pdf->Cell(0, 10, 'Total Nights: ' . $days, 0, 1);
    $pdf->Cell(0, 10, 'Price per Night: ' . $price_per_night . ' EUR', 0, 1);
    $pdf->Cell(0, 10, 'Total Price: ' . $total_price . ' EUR', 0, 1);

    $pdf->Output('D', 'Invoice_' . $reservation['id_occupation'] . '.pdf');

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

ob_end_clean();
?>
