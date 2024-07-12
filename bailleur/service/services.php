<?php
session_start();
include('../include/header.php');

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../../login/index.php');
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pcs5";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Récupérer les prestations
$sql = "SELECT * FROM prestation";
$result = $conn->query($sql);

$prestations = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $prestations[] = $row;
    }
}

$conn->close();
?>

<main>
<div class="container">
    <h3>Nos Services</h3>
    <div id="servicesList" class="list-group">
        <?php foreach($prestations as $prestation): ?>
            <div class="list-group-item">
                <h5 class="mb-1"><?php echo htmlspecialchars($prestation['titre']); ?></h5>
                <p class="mb-1"><?php echo nl2br(htmlspecialchars($prestation['description'])); ?></p>
                <small>
                    Prix: 
                    <?php 
                    if ($prestation['type_tarification'] == 'heure') {
                        echo htmlspecialchars($prestation['montant']) . '€/h';
                    } elseif ($prestation['type_tarification'] == 'kilometre') {
                        echo htmlspecialchars($prestation['montant']) . '€/km';
                    } else {
                        echo htmlspecialchars($prestation['montant']) . '€';
                    }
                    ?>
                </small>
                <?php if ($prestation['evolution'] == 1): ?>
                    <small> (Le prix évolue en fonction du service rendu)</small>
                <?php endif; ?>
                <br>
                <!-- Bouton pour ouvrir la modale -->
                <button type="button" class="btn btn-primary btn-sm mt-2" data-toggle="modal" data-target="#reserverModal" data-id-prestation="<?php echo $prestation['id_prestation']; ?>" data-titre-prestation="<?php echo htmlspecialchars($prestation['titre']); ?>">Réserver</button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modale pour sélectionner le bien -->
<div class="modal fade" id="reserverModal" tabindex="-1" role="dialog" aria-labelledby="reserverModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reserverModalLabel">Réserver une prestation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" action="reserver_service.php">
        <div class="modal-body">
          <p>Pour quel bien voulez-vous réserver <span id="titrePrestation"></span> ?</p>
          <div class="form-group">
            <label for="id_bien">Sélectionnez un bien :</label>
            <select class="form-control" id="id_bien" name="id_bien" required>
              <!-- Options seront ajoutées dynamiquement via AJAX -->
            </select>
          </div>
          <input type="hidden" id="id_prestation" name="id_prestation">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
          <button type="submit" class="btn btn-primary">Réserver</button>
        </div>
      </form>
    </div>
  </div>
</div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    $('#reserverModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var idPrestation = button.data('id-prestation');
        var titrePrestation = button.data('titre-prestation');
        var modal = $(this);
        modal.find('#id_prestation').val(idPrestation);
        modal.find('#titrePrestation').text(titrePrestation);
        
        // Requête AJAX pour récupérer les biens
        $.ajax({
            url: 'get_biens.php',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    var selectBien = modal.find('#id_bien');
                    selectBien.empty(); // Vider les options existantes
                    response.biens.forEach(function(bien) {
                        selectBien.append('<option value="' + bien.id_bien + '">' + bien.title + '</option>');
                    });
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Erreur lors de la récupération des biens.');
            }
        });
    });
});
</script>
</body>
</html>
