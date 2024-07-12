<?php include ('../include/header_accueil.php'); ?>

<main>
    <div class="container">
        <?php
        // Connexion à la base de données
        $servername = "localhost";
        $username = "root";
        $password = "esgi";
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
                echo '<div id="price" style="display: none;">' . $bien['prix'] . '</div>';

                // Afficher les photos du bien
                $sql_photos = "SELECT url FROM photo WHERE id_bien = ?";
                $stmt_photos = $conn->prepare($sql_photos);
                $stmt_photos->bind_param("i", $id_bien);
                $stmt_photos->execute();
                $result_photos = $stmt_photos->get_result();
                echo '<div id="carouselBien" class="carousel slide" data-ride="carousel">';
                echo '<ol class="carousel-indicators">';
                $photoIndex = 0;
                while ($photo = $result_photos->fetch_assoc()) {
                    echo '<li data-target="#carouselBien" data-slide-to="' . $photoIndex . '" class="' . ($photoIndex === 0 ? 'active' : '') . '"></li>';
                    $photoIndex++;
                }
                echo '</ol>';
                echo '<div class="carousel-inner">';
                $photoIndex = 0;
                $result_photos->data_seek(0); // Reset result set pointer
                while ($photo = $result_photos->fetch_assoc()) {
                    echo '<div class="carousel-item ' . ($photoIndex === 0 ? 'active' : '') . '">';
                    echo '<img src="../bailleur/img/bien/' . $photo['url'] . '" class="d-block w-100" alt="Photo du bien">';
                    echo '</div>';
                    $photoIndex++;
                }
                echo '</div>';
                echo '<a class="carousel-control-prev" href="#carouselBien" role="button" data-slide="prev">';
                echo '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
                echo '<span class="sr-only">Previous</span>';
                echo '</a>';
                echo '<a class="carousel-control-next" href="#carouselBien" role="button" data-slide="next">';
                echo '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
                echo '<span class="sr-only">Next</span>';
                echo '</a>';
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
        <div id="calendar-container">
            <div id="calendar"></div>
            <div id="total-amount" class="total-amount-container">
                <h2>Total : <span id="amount">0</span>€</h2>
                <button id="pay-button" class="pay-button" style="display: none;">Payer</button>
            </div>
        </div>

        <!-- Formulaire de paiement Stripe -->
        <div id="payment-form" class="payment-form" style="display: none;">
            <h2>Informations de paiement</h2>
            <div id="card-element"></div>
            <button id="submit-payment" class="pay-button">Confirmer et Payer</button>
        </div>
    </div>
</main>

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
        color: #333;
        margin: 0;
        padding: 0;
    }
    h1 {
        text-align: center;
        color: #4CAF50;
        margin-top: 20px;
    }
    .container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        width: 2.5rem;
        height: 2.5rem;
    }
    .carousel-control-prev-icon {
        background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns%3d%22http%3a%2f%2fwww.w3.org%2f2000%2fsvg%22 fill%3d%22%23FFA500%22 viewBox%3d%220 0 8 8%22%3e%3cpath d%3d%22M4.5 0L0 4l4.5 4V5h3V3h-3V0z%22%2f%3e%3c%2fsvg%3e');
    }
    .carousel-control-next-icon {
        background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns%3d%22http%3a%2f%2fwww.w3.org%2f2000%2fsvg%22 fill%3d%22%23FFA500%22 viewBox%3d%220 0 8 8%22%3e%3cpath d%3d%22M3.5 0v3H0v2h3.5v3L8 4 3.5 0z%22%2f%3e%3c%2fsvg%3e');
    }
    .photo-gallery img {
        width: 100%;
        height: auto;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    .total-amount-container {
        text-align: center;
        margin-top: 20px;
    }
    .pay-button {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s;
    }
    .pay-button:hover {
        background-color: #45a049;
    }
    .payment-form {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .error-message {
        color: red;
        margin-bottom: 10px;
    }
    .success-message {
        color: green;
        margin-bottom: 10px;
    }
</style>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://js.stripe.com/v3/"></script>
<script>
$(document).ready(function() {
    var stripe = Stripe('pk_test_51PV1HeFXpsoOQTRTmY5Z5l1V9YPHida0LaQJKs44cz0zrLRcsYD4piOASZUNL2wEFn0vRXpSK1W2jGfQpHNJ0uix00eqGlqamQ'); // Replace with your Stripe public key
    var elements = stripe.elements();
    var card = elements.create('card');
    card.mount('#card-element');
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
            var endDate = moment(end).subtract(1, 'days').format('YYYY-MM-DD');
            var days = moment(end).diff(moment(start), 'days');
            var pricePerNight = parseFloat($('#price').text());
            var totalAmount = days * pricePerNight;

            if (totalAmount > 0) {
                $('#amount').text(totalAmount.toFixed(2));
                $('#pay-button').show();
            } else {
                $('#amount').text('0');
                $('#pay-button').hide();
            }

            $('#pay-button').off('click').on('click', function() {
                
                $.ajax({
                    url: 'check_availability.php',
                    type: 'POST',
                    data: {
                        id_bien: <?php echo $id_bien; ?>,
                        date_debut: startDate,
                        date_fin: endDate
                    },
                    success: function(response) {
                        var jsonResponse = JSON.parse(response);
                        if (jsonResponse.error) {
                            if (jsonResponse.error === 'already_reserved') {
                                showMessage('Vous avez déjà une réservation pour ce bien.', 'error');
                            } else if (jsonResponse.error === 'dates_overlap') {
                                showMessage('Les dates sélectionnées se chevauchent avec une réservation existante.', 'error');
                            } else {
                                showMessage(jsonResponse.error, 'error');
                            }
                        } else {
                            $('#payment-form').show();
                            $('#calendar-container').hide();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showMessage('Erreur lors de la vérification des disponibilités.', 'error');
                    }
                });
            });

            $('#submit-payment').off('click').on('click', function() {
                stripe.createPaymentMethod('card', card).then(function(result) {
                    if (result.error) {
                        showMessage(result.error.message, 'error');
                    } else {
                        $.ajax({
                            url: 'create_payment_intent.php',
                            type: 'POST',
                            data: {
                                payment_method: result.paymentMethod.id,
                                amount: totalAmount * 100, 
                                id_bien: <?php echo $id_bien; ?>,
                                date_debut: startDate,
                                date_fin: endDate
                            },
                            success: function(response) {
                                var jsonResponse = JSON.parse(response);
                                if (jsonResponse.error) {
                                    showMessage(jsonResponse.error, 'error');
                                } else if (jsonResponse.client_secret) {
                                    stripe.confirmCardPayment(jsonResponse.client_secret, {
                                        payment_method: result.paymentMethod.id
                                    }).then(function(result) {
                                        if (result.error) {
                                            showMessage(result.error.message, 'error');
                                        } else if (result.paymentIntent.status === 'succeeded') {
                                            showMessage('Paiement réussi!', 'success');
                                            $('#amount').text('0');
                                            $('#pay-button').hide();
                                            $('#payment-form').hide();
                                            $('#calendar-container').show();
                                            $('#calendar').fullCalendar('refetchEvents');
                                           
                                            window.location.href = '../accueil/index.php?message=' + encodeURIComponent('Paiement réussi!');
                                        }
                                    });
                                } else {
                                    
                                    showMessage('Paiement réussi!', 'success');
                                    window.location.href = '../accueil/index.php?message=' + encodeURIComponent('Paiement réussi!');
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                showMessage('Erreur lors de la création du paiement.', 'error');
                            }
                        });
                    }
                });
            });

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
