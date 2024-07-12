<?php
session_start();
include('include/header.php');

$id_utilisateur = $_SESSION['id_utilisateur'];

include '../include/db.php';

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}
$sql = "SELECT * FROM utilisateur WHERE id_utilisateur = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utilisateur);
$stmt->execute();
$result = $stmt->get_result();
$prestataire = $result->fetch_assoc();
$stmt->close();

$conn->close();
?>

<main>
<div class="container">
    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="profil-tab" data-toggle="tab" href="#profil" role="tab" aria-controls="profil" aria-selected="true">Mon Profil</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="interventions-tab" data-toggle="tab" href="#interventions" role="tab" aria-controls="interventions" aria-selected="false">Mes Interventions</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="prestations-tab" data-toggle="tab" href="#prestations" role="tab" aria-controls="prestations" aria-selected="false">Liste Prestations</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="factures-tab" data-toggle="tab" href="#factures" role="tab" aria-controls="factures" aria-selected="false">Factures</a>
        </li>
    </ul>
    <div class="tab-content" id="profileTabsContent">
    
        <div class="tab-pane fade show active" id="profil" role="tabpanel" aria-labelledby="profil-tab">
            <form id="updateProfilForm">
                <h3>Mon Profil</h3>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($prestataire['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe:</label>
                    <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe">
                </div>
                <div class="form-group">
                    <label for="numero_telephone">Numéro de téléphone:</label>
                    <input type="text" class="form-control" id="numero_telephone" name="numero_telephone" value="<?php echo htmlspecialchars($prestataire['numero_telephone']); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </form>
        </div>

    
        <div class="tab-pane fade" id="interventions" role="tabpanel" aria-labelledby="interventions-tab">
            <h3 style="margin-top: 2%;">Mes Interventions</h3>
            <ul id="interventionsList" class="list-group">
               
            </ul>
        </div>

        
        <div class="modal fade" id="updateInterventionModal" tabindex="-1" aria-labelledby="updateInterventionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateInterventionModalLabel">Mettre à jour l'intervention</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="updateInterventionForm">
                            <input type="hidden" id="interventionId" name="interventionId">
                            <div class="mb-3">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="En cours">En cours</option>
                                    <option value="Terminé">Terminé</option>
                                </select>
                            </div>
                            <div class="mb-3" id="nbr_heure_field" style="display:none;">
                                <label for="nbr_heure" class="form-label">Nombre d'heures</label>
                                <input type="number" class="form-control" id="nbr_heure" name="nbr_heure">
                            </div>
                            <div class="mb-3" id="km_field" style="display:none;">
                                <label for="km" class="form-label">Kilométrage</label>
                                <input type="number" class="form-control" id="km" name="km">
                            </div>
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="prestations" role="tabpanel" aria-labelledby="prestations-tab">
            <h3 style="margin-top: 2%;">Liste Prestations</h3>
            <ul id="prestationsList" class="list-group">
               
            </ul>
        </div>
        
        
        <div class="tab-pane fade" id="factures" role="tabpanel" aria-labelledby="factures-tab">
            <h3 style="margin-top: 2%;">Factures</h3>
            <ul id="facturesList" class="list-group">
                
            </ul>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

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
    // Fetch and display interventions
    fetchInterventions();

    // Fetch and display prestations
    fetchPrestations();

    // Fetch and display factures
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
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Erreur lors de la mise à jour du profil : ' + errorThrown);
            }
        });
    });

    
    $('#updateInterventionForm').on('submit', function(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        console.log("Form Data: ", formData); 

        $.ajax({
            url: 'update_intervention.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log("Response: ", response); 
                if (response.success) {
                    alert('Intervention mise à jour avec succès');
                    $('#updateInterventionModal').modal('hide');
                    fetchInterventions();
                } else {
                    alert('Erreur: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Erreur lors de la mise à jour de l\'intervention: ' + errorThrown);
            }
        });
    });

});

function fetchInterventions() {
    $.ajax({
        url: 'get_interventions.php',
        type: 'GET',
        success: function(response) {
            console.log('Interventions response:', response); 
            if (response.success) {
                var interventionsList = $('#interventionsList');
                interventionsList.empty();
                response.interventions.forEach(function(intervention) {
                    interventionsList.append('<li class="list-group-item">' +
                        '<span>' + intervention.titre + ' - ' + intervention.debut_prestation + ' à ' + intervention.fin_prestation + '</span>' +
                        '<span>' + (intervention.montant !== null ? intervention.montant + ' €' : 'Montant à déterminer') + '</span>' +
                        '<span>' + (intervention.status === 'Terminé' ? 'Terminé' : '<button class="btn btn-primary update-intervention" data-id="' + intervention.id_commande + '">Mettre à jour</button>') + '</span>' +
                        '</li>'
                    );
                });

                
                $('.update-intervention').on('click', function() {
                    var interventionId = $(this).data('id');
                    openUpdateModal(interventionId);
                });
            } else {
                $('#interventionsList').html('<p>Vous n\'avez pas encore d\'interventions.</p>');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('Error fetching interventions:', textStatus, errorThrown); 
            $('#interventionsList').html('<p>Erreur lors de la récupération des interventions.</p>');
        }
    });
}

function fetchPrestations() {
    $.ajax({
        url: 'get_prestations.php',
        type: 'GET',
        success: function(response) {
            console.log('Prestations response:', response); 
            if (response.success) {
                var prestationsList = $('#prestationsList');
                prestationsList.empty();
                response.prestations.forEach(function(prestation) {
                    prestationsList.append('<li class="list-group-item">' +
                        '<span>' + prestation.titre + ' - ' + prestation.montant + ' €</span>' +
                        '<button class="btn btn-primary accept-prestation" data-id="' + prestation.id_commande + '">Accepter</button>' +
                        '</li>'
                    );
                });

            
                $('.accept-prestation').on('click', function() {
                    var prestationId = $(this).data('id');
                    acceptPrestation(prestationId);
                });
            } else {
                $('#prestationsList').html('<p>Aucune prestation disponible pour le moment.</p>');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('Error fetching prestations:', textStatus, errorThrown);
            $('#prestationsList').html('<p>Erreur lors de la récupération des prestations.</p>');
        }
    });
}

function acceptPrestation(prestationId) {
    if (confirm('Voulez-vous vraiment accepter cette prestation?')) {
        $.ajax({
            url: 'accept_prestation.php',
            type: 'POST',
            data: { id: prestationId },
            success: function(response) {
                if (response.success) {
                    alert('Prestation acceptée avec succès');
                    fetchPrestations();
                } else {
                    alert('Erreur: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Erreur lors de l\'acceptation de la prestation: ' + errorThrown);
            }
        });
    }
}

function openUpdateModal(interventionId) {
    
    $.ajax({
        url: 'get_intervention_details.php',
        type: 'GET',
        data: { id: interventionId },
        success: function(response) {
            if (response.success) {
                $('#interventionId').val(response.intervention.id_commande);
                $('#status').val(response.intervention.status);
                $('#nbr_heure').val(response.intervention.nbr_heure);
                $('#km').val(response.intervention.km);

                if (response.intervention.type_tarification === 'heure') {
                    $('#nbr_heure_field').show();
                    $('#km_field').hide();
                } else if (response.intervention.type_tarification === 'kilometre') {
                    $('#km_field').show();
                    $('#nbr_heure_field').hide();
                } else {
                    $('#nbr_heure_field').hide();
                    $('#km_field').hide();
                }

                $('#updateInterventionModal').modal('show');
            } else {
                alert('Erreur: ' + response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('Erreur lors de la récupération des détails de l\'intervention: ' + errorThrown);
        }
    });
}


function fetchFactures() {
    $.ajax({
        url: 'get_factures.php',
        type: 'GET',
        success: function(response) {
            console.log('Factures response:', response); 
            if (response.success) {
                var facturesList = $('#facturesList');
                facturesList.empty();
                response.factures.forEach(function(facture) {
                    facturesList.append('<li class="list-group-item">' +
                        '<span>Facture ' + facture.id_facture + ' - ' + facture.date + '</span>' +
                        '<a href="generate_invoice.php?id=' + facture.id_facture + '" class="btn btn-secondary">Télécharger</a>' +
                        '</li>'
                    );
                });
            } else {
                $('#facturesList').html('<p>Aucune facture disponible pour le moment.</p>');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('Error fetching factures:', textStatus, errorThrown); 
            $('#facturesList').html('<p>Erreur lors de la récupération des factures.</p>');
        }
    });
}
</script>
</main>
</body>
</html>
