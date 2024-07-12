<?php include ('../include/header_accueil.php'); ?>

<main>
    <div class="container">
        <?php
        // Connexion à la base de données
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "pcs5";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connexion échouée: " . $conn->connect_error);
        }

        // Récupérer les détails du bien
        if (isset($_GET['id'])) {
            $id_bien = $_GET['id'];
            $sql = "SELECT bien.*, utilisateur.nom AS hote FROM bien JOIN utilisateur ON bien.id_bailleur = utilisateur.id_utilisateur WHERE bien.id_bien = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_bien);
            $stmt->execute();
            $result = $stmt->get_result();
            $bien = $result->fetch_assoc();
            
            if ($bien) {
                echo '<h1>' . $bien['title'] . ', ' . $bien['city'] . '</h1>';
                echo '<p>Hôte : ' . $bien['hote'] . '</p>';
                echo '<p>Prix : ' . $bien['prix'] . '€ par nuit</p>';
                echo '<p>Description : ' . $bien['description'] . '</p>';

                // Afficher les photos du bien
                $sql_photos = "SELECT url FROM photo WHERE id_bien = ?";
                $stmt_photos = $conn->prepare($sql_photos);
                $stmt_photos->bind_param("i", $id_bien);
                $stmt_photos->execute();
                $result_photos = $stmt_photos->get_result();
                echo '<div class="photo-gallery">';
                while ($photo = $result_photos->fetch_assoc()) {
                    echo '<img src="' . $photo['url'] . '" alt="Photo du bien">';
                }
                echo '</div>';
            } else {
                echo '<p>Aucun bien trouvé.</p>';
            }
            $stmt->close();
        } else {
            echo '<p>Aucun bien spécifié.</p>';
        }

        $conn->close();
        ?>
        <div id="message-container"></div>
        <div id="calendar"></div>
    </div>
</main>

<script>
$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prev',
            center: 'title',
            right: 'next'
        },
        validRange: {
            start: moment().format('YYYY-MM-DD')
        },
        events: {
            url: 'get_occupations.php',
            type: 'GET',
            data: {
                id_bien: <?php echo $id_bien; ?>
            },
            error: function() {
                showMessage('Erreur lors du chargement des occupations.', 'error');
            }
        },
        selectable: true,
        selectHelper: true,
        select: function(start, end) {
            var startDate = moment(start).format('YYYY-MM-DD');
            var endDate = moment(end).format('YYYY-MM-DD');
            if (confirm("Voulez-vous réserver du " + startDate + " au " + endDate + "?")) {
                $.ajax({
                    url: 'reserver_bien.php',
                    type: 'POST',
                    data: {
                        id_bien: <?php echo $id_bien; ?>,
                        date_debut: startDate,
                        date_fin: endDate
                    },
                    success: function(response) {
                        if (response == 'success') {
                            $('#calendar').fullCalendar('refetchEvents');
                            showMessage('Réservation réussie!', 'success');
                        } else if (response == 'already_reserved') {
                            showMessage('Vous avez déjà une réservation pour ce bien.', 'error');
                        } else {
                            showMessage('Erreur lors de la réservation.', 'error');
                        }
                    }
                });
            }
            $('#calendar').fullCalendar('unselect');
        }
    });

    function showMessage(message, type) {
        var messageContainer = $('#message-container');
        messageContainer.html('<div class="' + type + '-message">' + message + '</div>');
        setTimeout(function() {
            messageContainer.html('');
        }, 5000);
    }
});
</script>
</body>
</html>
