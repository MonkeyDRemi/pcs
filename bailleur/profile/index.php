<?php
session_start();
include('../include/header.php');
require_once '../../include/db.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../../login/index.php');
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

try {
    // Récupérer les informations de l'utilisateur
    $sql = "SELECT * FROM utilisateur WHERE id_utilisateur = :id_utilisateur";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_utilisateur' => $id_utilisateur]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Utilisateur non trouvé.");
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<main>
<div class="container">
    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="profil-tab" data-toggle="tab" href="#profil" role="tab" aria-controls="profil" aria-selected="true">Mon Profil</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="biens-tab" data-toggle="tab" href="#biens" role="tab" aria-controls="biens" aria-selected="false">Mes Biens</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="reservations-tab" data-toggle="tab" href="#reservations" role="tab" aria-controls="reservations" aria-selected="false">Biens Reservés</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="services-tab" data-toggle="tab" href="#services" role="tab" aria-controls="services" aria-selected="false">Mes Services</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="factures-tab" data-toggle="tab" href="#factures" role="tab" aria-controls="factures" aria-selected="false">Mes Factures</a>
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
                <h4>Informations Bancaires</h4>
                <div class="form-group">
                    <label for="iban">IBAN:</label>
                    <input type="text" class="form-control" id="iban" name="iban" value="<?php echo htmlspecialchars($user['iban']); ?>">
                </div>
                <div class="form-group">
                    <label for="bic">BIC:</label>
                    <input type="text" class="form-control" id="bic" name="bic" value="<?php echo htmlspecialchars($user['bic']); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </form>
        </div>
        <!-- Mes Biens Section -->
        <div class="tab-pane fade" id="biens" role="tabpanel" aria-labelledby="biens-tab">
            <h3>Mes Biens</h3>
            <div id="messageContainer"></div>
            <ul id="biensList" class="list-group">
                <!-- Biens list will be populated here via AJAX -->
            </ul>
            <div class="mt-3">
                <a href="../bien/add_bien.php" class="btn btn-success">Ajouter un bien</a>
            </div>
        </div>
        
        <!-- Mes Réservations Section -->
        <div class="tab-pane fade" id="reservations" role="tabpanel" aria-labelledby="reservations-tab">
            <h3>Mes Réservations</h3>
            <ul id="reservationsList" class="list-group">
                <!-- Reservations list will be populated here via AJAX -->
            </ul>
        </div>

        <!-- Mes Services Section -->
        <div class="tab-pane fade" id="services" role="tabpanel" aria-labelledby="services-tab">
            <h3>Mes Services</h3>
            <ul id="servicesList" class="list-group mt-3">
                <!-- Services list will be populated here via AJAX -->
            </ul><br>
            <a href="../service/services.php" class="btn btn-success">Réserver un service</a>
        </div>

        <div class="tab-pane fade" id="factures" role="tabpanel" aria-labelledby="factures-tab">
            <h3>Mes Factures</h3>
            <ul id="facturesList" class="list-group mt-3">
                <!-- Factures list will be populated here via AJAX -->
            </ul>
        </div>
    </div>
</div>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://js.stripe.com/v3/"></script>
<script>
$(document).ready(function() {
    // Fetch and display biens
    fetchBiens();

    // Fetch and display reservations
    fetchReservations();
    
    // Fetch and display services
    fetchServices();

    fetchFactures();

    // Handle form submission for updating profile
    $('#updateProfilForm').on('submit', function(event) {
        event.preventDefault();
        $.ajax({
            url: 'update_profil.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert(response.message);
            }
        });
    });

    // Vérifier si un message est stocké dans le localStorage
    var message = localStorage.getItem('message');
    if (message) {
        // Afficher le message
        $('#messageContainer').html('<div class="alert alert-success">' + message + '</div>');
        // Supprimer le message du localStorage
        localStorage.removeItem('message');
    }

    // Activer l'onglet correspondant au fragment d'ancrage
    var hash = window.location.hash;
    if (hash) {
        $('a[href="' + hash + '"]').tab('show');
    }
});

function fetchFactures() {
    $.ajax({
        url: 'get_factures.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                var facturesList = $('#facturesList');
                facturesList.empty();
                response.factures.forEach(function(facture) {
                    facturesList.append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Facture #${facture.id_facture} - ${facture.date_facture}
                            <a href="${facture.download_link}" class="btn btn-primary btn-sm" target="_blank">
                                <i class="fas fa-download"></i> Télécharger
                            </a>
                        </li>
                    `);
                });
            } else {
                $('#facturesList').html('<p>Vous n\'avez pas encore de factures.</p>');
            }
        }
    });
}

function fetchBiens() {
    $.ajax({
        url: 'get_biens.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                var biensList = $('#biensList');
                biensList.empty();
                response.biens.forEach(function(bien) {
                    biensList.append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ${bien.title} - ${bien.creation}
                            <span>
                                <a href="../bien/index.php?id=${bien.id_bien}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-danger btn-sm" onclick="deleteBien(${bien.id_bien})">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </span>
                        </li>
                    `);
                });
            } else {
                $('#biensList').html('<p>Vous n\'avez pas encore de biens.</p>');
            }
        }
    });
}

function fetchReservations() {
    $.ajax({
        url: 'get_reservations.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                var reservationsList = $('#reservationsList');
                reservationsList.empty();
                response.reservations.forEach(function(reservation) {
                    reservationsList.append(`
                        <li class="list-group-item">
                            ${reservation.title} - du ${reservation.date_debut} au ${reservation.date_fin} par ${reservation.nom} ${reservation.prenom} (${reservation.email})
                        </li>
                    `);
                });
            } else {
                $('#reservationsList').html('<p>Vous n\'avez pas encore de réservations.</p>');
            }
        }
    });
}

function fetchServices() {
    $.ajax({
        url: '../service/get_services.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                var servicesList = $('#servicesList');
                servicesList.empty();
                response.services.forEach(function(service) {
                    servicesList.append(`
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ${service.titre} - ${service.debut_prestation}
                            <span>${service.montant !== null ? service.montant + ' €' : 'Montant à déterminer'}</span>
                            ${service.id_paiement > 0 ? '<span class="button button-success">Déjà Payé</span>' : '<button class="btn btn-primary pay-service" data-id="${service.id_commande}">Payer</button>'}
                        </li>
                    `);
                });

                // Attach click event handler to pay buttons
                $('.pay-service').on('click', function() {
                    var serviceId = $(this).data('id');
                    openPaymentForm(serviceId);
                });
            } else {
                $('#servicesList').html('<p>Vous n\'avez pas encore de services réservés.</p>');
            }
        }
    });
}

function openPaymentForm(serviceId) {
    if (!serviceId) {
        alert('Service ID is undefined.');
        return;
    }

    var stripe = Stripe('pk_test_51PV1HeFXpsoOQTRTmY5Z5l1V9YPHida0LaQJKs44cz0zrLRcsYD4piOASZUNL2wEFn0vRXpSK1W2jGfQpHNJ0uix00eqGlqamQ'); // Replace with your Stripe public key
    var elements = stripe.elements();
    var card = elements.create('card');
    card.mount('#card-element');

    $('#paymentModal').modal('show');

    $('#submit-payment').off('click').on('click', function() {
        stripe.createPaymentMethod('card', card).then(function(result) {
            if (result.error) {
                alert(result.error.message);
            } else {
                $.ajax({
                    url: '../service/create_payment_intent.php',
                    type: 'POST',
                    data: {
                        payment_method: result.paymentMethod.id,
                        id_commande: serviceId
                    },
                    success: function(response) {
                        var jsonResponse = JSON.parse(response);
                        if (jsonResponse.error) {
                            alert(jsonResponse.error);
                        } else if (jsonResponse.client_secret) {
                            stripe.confirmCardPayment(jsonResponse.client_secret, {
                                payment_method: result.paymentMethod.id
                            }).then(function(result) {
                                if (result.error) {
                                    alert(result.error.message);
                                } else if (result.paymentIntent.status === 'succeeded') {
                                    alert('Paiement réussi!');
                                    $('#paymentModal').modal('hide');
                                    fetchServices();
                                }
                            });
                        }
                    }
                });
            }
        });
    });
}

function deleteBien(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce bien ?')) {
        $.ajax({
            url: '../bien/delete_bien.php?id=' + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    // Stocker le message dans le localStorage
                    localStorage.setItem('message', response.message);
                    // Rediriger vers la page des biens
                    window.location.href = 'index.php#biens';
                } else {
                    alert(response.message);
                }
            }
        });
    }
}
</script>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Informations de paiement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="card-element"></div>
                <button id="submit-payment" class="btn btn-primary mt-3">Confirmer et Payer</button>
            </div>
        </div>
    </div>
</div>

<!-- Chat Modal -->
<div class="modal fade" id="chatModal" tabindex="-1" role="dialog" aria-labelledby="chatModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chatModalLabel">Chat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="chatMessages" style="height: 300px; overflow-y: scroll;"></div>
                <div class="form-group">
                    <label for="messageInput">Message:</label>
                    <textarea class="form-control" id="messageInput"></textarea>
                </div>
                <button id="sendMessage" class="btn btn-primary">Envoyer</button>
            </div>
        </div>
    </div>
</div>

</main>
</body>
</html>
