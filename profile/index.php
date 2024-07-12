<?php
session_start();
include('../include/header.php');
require_once '../include/db.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../../login/index.php');
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

try {
    // Récupérer les informations de l'utilisateur
    $sql = "SELECT * FROM utilisateur WHERE id_utilisateur = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_utilisateur]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer l'abonnement actif de l'utilisateur s'il existe
    $sql = "SELECT a.type, ac.date_debut, ac.date_fin FROM abonnement_commande ac
            JOIN abonnement a ON ac.id_abonnement = a.id_abonnement
            WHERE ac.id_utilisateur = ? AND ac.date_fin > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_utilisateur]);
    $active_subscription = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}

$pdo = null; // Fermer la connexion PDO, car nous avons fini avec les requêtes

?>

<main>
    <div class="container">
        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="profil-tab" data-toggle="tab" href="#profil" role="tab" aria-controls="profil" aria-selected="true">Mon Profil</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="reservations-tab" data-toggle="tab" href="#reservations" role="tab" aria-controls="reservations" aria-selected="false">Mes Réservations</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="abonnement-tab" data-toggle="tab" href="#abonnement" role="tab" aria-controls="abonnement" aria-selected="false">Mes Abonnements</a>
            </li>
        </ul>
        <div class="tab-content" id="profileTabsContent">
            <!-- Mon Profil Section -->
            <div class="tab-pane fade show active" id="profil" role="tabpanel" aria-labelledby="profil-tab">
                <form id="updateProfilForm">
                    <h3>Mon Profil</h3>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="mot_de_passe">Mot de passe:</label>
                        <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe">
                    </div>
                    <div class="form-group">
                        <label for="numero_telephone">Numéro de téléphone:</label>
                        <input type="text" class="form-control" id="numero_telephone" name="numero_telephone" value="<?php echo htmlspecialchars($user['numero_telephone']); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </form>
            </div>

            <!-- Mes Réservations Section -->
            <div class="tab-pane fade" id="reservations" role="tabpanel" aria-labelledby="reservations-tab">
                <h3>Mes Réservations</h3>
                <ul id="reservationsList" class="list-group">
                    <!-- Reservations list will be populated here via AJAX -->
                </ul>
            </div>
            
            <!-- Mes Abonnements Section -->
            <div class="tab-pane fade" id="abonnement" role="tabpanel" aria-labelledby="abonnement-tab">
                <h3>Mes Abonnements</h3>
                <ul id="abonnementList" class="list-group">
                    <?php if ($active_subscription): ?>
                        <li class="list-group-item">Abonnement <?php echo htmlspecialchars($active_subscription['type']); ?>
                            <span style="color: red;"> Date de fin - <?php echo htmlspecialchars($active_subscription['date_fin']); ?></span>
                        </li>
                    <?php else: ?>
                        <li class="list-group-item">Vous n'avez pas encore d'abonnement actif.</li>
                    <?php endif; ?>
                </ul>
                <a href="abonnement.php"><br><button class="btn btn-primary"><?php echo isset($active_subscription) ? 'Gérer mon abonnement' : 'Je veux un abonnement'; ?></button></a>
            </div>
        </div>
    </div>

    <!-- Add Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Add custom styles for the list items -->
    <style>
    .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    </style>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        // Handle form submission for updating profile
        $('#updateProfilForm').on('submit', function(event) {
            event.preventDefault();
            $.ajax({
                url: 'update_profil.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    alert(response.message);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Erreur lors de la mise à jour du profil : ' + errorThrown);
                }
            });
        });

        // Fetch and display reservations
        fetchReservations();

        // Fetch and display abonnements
        fetchAbonnements();
    });

    function fetchReservations() {
        $.ajax({
            url: 'get_reservations.php',
            type: 'GET',
            success: function(response) {
                console.log('Reservations response:', response); // Debugging line
                if (response.success) {
                    var reservationsList = $('#reservationsList');
                    reservationsList.empty();
                    response.reservations.forEach(function(reservation) {
                        var startDate = new Date(reservation.date_debut);
                        var oneWeekBeforeStartDate = new Date(startDate);
                        oneWeekBeforeStartDate.setDate(startDate.getDate() - 7);
                        var now = new Date();

                        var cancelButton = '';
                        if (now < oneWeekBeforeStartDate) {
                            cancelButton = '<button class="btn btn-danger cancel-reservation" data-id="' + reservation.id_occupation + '">Annuler</button>';
                        } else {
                            cancelButton = '<span class="text-danger">Reservation non annulable</span>';
                        }

                        var downloadLink = '<a href="generate_invoice.php?id=' + reservation.id_occupation + '" class="btn btn-secondary">Télécharger la facture</a>';

                        reservationsList.append('<li class="list-group-item d-flex justify-content-between align-items-center">' + 
                            '<span>' + reservation.title + ' - ' + reservation.date_debut + ' à ' + reservation.date_fin + '</span>' +
                            '<span>' + downloadLink + ' ' + cancelButton + '</span>' +
                            '</li>'
                        );
                    });

                    // Attach click event handler to cancel buttons
                    $('.cancel-reservation').on('click', function() {
                        var reservationId = $(this).data('id');
                        cancelReservation(reservationId);
                    });
                } else {
                    $('#reservationsList').html('<p>Vous n\'avez pas encore de réservations.</p>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('Error fetching reservations:', textStatus, errorThrown); // Debugging line
                $('#reservationsList').html('<p>Erreur lors de la récupération des réservations.</p>');
            }
        });
    }


    function cancelReservation(reservationId) {
        if (confirm('Voulez-vous vraiment annuler cette réservation?')) {
            $.ajax({
                url: 'cancel_reservation.php',
                type: 'POST',
                data: { id: reservationId },
                success: function(response) {
                    if (response.success) {
                        alert('Réservation annulée avec succès');
                        fetchReservations();
                    } else {
                        alert('Erreur: ' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Erreur lors de l\'annulation de la réservation: ' + errorThrown);
                }
            });
        }
    }

    function fetchAbonnements() {
        $.ajax({
            url: 'get_abonnements.php',
            type: 'GET',
            success: function(response) {
                console.log(response); // Debugging line
                
                // Parse the JSON response
                const data = JSON.parse(response);
                
                if (data.success) {
                    var abonnementList = $('#abonnementList');
                    abonnementList.empty();
                    if (data.abonnements.length > 0) {
                        data.abonnements.forEach(function(abonnement) {
                            abonnementList.append('<li class="list-group-item">' +
                                                  'Abonnement ' + abonnement.type +
                                                  '<span style="color: red;"> Date de fin - ' + abonnement.date_fin + '</span>' +
                                                  '</li>');
                        });
                    } else {
                        abonnementList.html('<p>Vous n\'avez pas encore d\'abonnements.</p>');
                    }
                } else {
                    $('#abonnementList').html('<p>Vous n\'avez pas encore d\'abonnements.</p>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('Error: ' + textStatus + ' - ' + errorThrown); // Debugging line
                $('#abonnementList').html('<p>Erreur lors de la récupération des abonnements.</p>');
            }
        });
    }
    </script>
</main>
</body>
</html>
