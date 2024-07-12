<?php include ('../include/header_accueil2.php'); ?>
<script>
    async function fetchBiens() {
        const city = document.getElementById('city').value;
        const type_bien = document.getElementById('type_bien').value;
        const prix_min = document.getElementById('prix_min').value;
        const prix_max = document.getElementById('prix_max').value;

        const params = new URLSearchParams({ city, type_bien, prix_min, prix_max });

        const response = await fetch(`get_biens.php?${params}`);
        const biens = await response.json();

        const biensContainer = document.getElementById('biens');
        biensContainer.innerHTML = '';
        biens.forEach((bien, index) => {
            const bienElement = document.createElement('div');
            bienElement.className = 'bien';
            bienElement.style.cursor = 'pointer';

            let carouselHtml = '';
            if (bien.photos && bien.photos.length > 0) {
                const carouselId = `carousel${index}`;
                const indicators = bien.photos.map((_, i) => `<li data-target="#${carouselId}" data-slide-to="${i}" class="${i === 0 ? 'active' : ''}"></li>`).join('');
                const slides = bien.photos.map((photo, i) => `
                    <div class="carousel-item ${i === 0 ? 'active' : ''}">
                        <img src="../bailleur/img/bien/${photo}" alt="${bien.title}" class="d-block w-100" style="border-radius: 8px;">
                    </div>
                `).join('');

                carouselHtml = `
                    <div id="${carouselId}" class="carousel slide">
                        <ol class="carousel-indicators">
                            ${indicators}
                        </ol>
                        <div class="carousel-inner">
                            ${slides}
                        </div>
                        <a class="carousel-control-prev" href="#${carouselId}" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#${carouselId}" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>
                `;
            }

            bienElement.innerHTML = `
                <h3>${bien.title}</h3>
                ${carouselHtml}
                <p>${bien.description}</p>
                <p><strong>Ville:</strong> ${bien.city}</p>
                <p><strong>Prix:</strong> ${bien.prix} €</p>
            `;

            // Add event listener only to the bienElement, excluding the carousel controls
            bienElement.addEventListener('click', (e) => {
                if (!e.target.closest('.carousel-control-prev') && !e.target.closest('.carousel-control-next')) {
                    window.location.href = `../bien/details_bien.php?id=${bien.id_bien}`;
                }
            });

            biensContainer.appendChild(bienElement);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('filterForm').addEventListener('input', fetchBiens);
        fetchBiens(); // Fetch all biens initially
    });
</script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<style>
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

    /* Chatbot Button */
    #chatbotButton {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #FFA500;
        border: none;
        color: white;
        padding: 15px;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }

    /* Chatbot Modal */
    .modal-content {
        border-radius: 10px;
    }
    .modal-header {
        border-bottom: none;
    }
    .modal-footer {
        border-top: none;
    }
</style>
<main style="margin-top: 0 !important;">
    <form id="filterForm" style="margin-bottom: 2%;">
        <label for="city">Ville:</label>
        <input type="text" id="city" name="city">
        
        <label for="type_bien">Type de bien:</label>
        <input type="text" id="type_bien" name="type_bien">
        
        <label for="prix_min">Prix Min:</label>
        <input type="number" id="prix_min" name="prix_min">
        
        <label for="prix_max">Prix Max:</label>
        <input type="number" id="prix_max" name="prix_max">
    </form>
    <div id="biens"></div>
</main>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function(){
        $('.carousel').carousel({
            interval: false
        });
    });
</script>

<!-- Chatbot Button -->
<button id="chatbotButton">
    <i class="fas fa-comment-alt"></i>
</button>

<!-- Chatbot Modal -->
<div class="modal fade" id="chatbotModal" tabindex="-1" role="dialog" aria-labelledby="chatbotModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chatbotModalLabel">Chatbot</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Chatbot UI -->
                <div id="chatbotUI" style="height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
                    <!-- Messages will be appended here -->
                </div>
                <div class="input-group mt-3">
                    <input type="text" id="chatbotInput" class="form-control" placeholder="Posez votre question...">
                    <div class="input-group-append">
                        <button id="sendChatbotMessage" class="btn btn-primary">Envoyer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Font Awesome for Chatbot Icon -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>

<script>
    $(document).ready(function(){
        $('#chatbotButton').on('click', function() {
            $('#chatbotModal').modal('show');
        });

        $('#sendChatbotMessage').on('click', function() {
            var message = $('#chatbotInput').val();
            if (message.trim() !== '') {
                // Append user's message
                $('#chatbotUI').append('<div class="user-message"><strong>Vous:</strong> ' + message + '</div>');
                $('#chatbotInput').val('');

                // Simulate bot response (this is where you would integrate with an actual chatbot API)
                setTimeout(function() {
                    var botResponse = 'This is a simulated response. Your question was: ' + message;
                    $('#chatbotUI').append('<div class="bot-response"><strong>Bot:</strong> ' + botResponse + '</div>');
                    // Scroll to the bottom of the chatbot UI
                    $('#chatbotUI').scrollTop($('#chatbotUI')[0].scrollHeight);
                }, 1000);
            }
        });
    });
</script>
</body>
</html>
