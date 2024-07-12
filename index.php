<?php
    session_start();
    function isUserLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    include('include/header_accueil3.php');
?>

<main style="margin-top: 0 !important;">
    <div class="container">
        <h2 style="text-align: center">Paris Caretaker Services (PCS)</h1><br>
        <p>PCS est une chaîne de conciergeries immobilières spécialisée dans la gestion locative saisonnière, similaire à AirBnB, fondée à Paris en 2018. Nous nous engageons à offrir un service de qualité supérieure, allant de la gestion des réservations à l'entretien complet des biens immobiliers.</p><br><br>
        
        <h4 style="text-align: center">Nos Services</h2>
        <ul>
            <li>Check-in et check-out des clients</li>
            <li>Nettoyage du logement</li>
            <li>Publication des annonces avec photos de qualité</li>
            <li>Service client disponible 24h/24 et 7j/7</li>
            <li>Optimisation des tarifs de location</li>
            <li>Fourniture de linge de maison</li>
            <li>Travaux d’entretien et petites réparations</li>
            <li>Services de plomberie, électricité, et réparation du mobilier</li>
            <li>Transport de et vers l'aéroport</li>
        </ul>
        
    
    <a href="signup/index.php">En savoir plus sur nos services</a>
               
    </div>

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

<script>
    async function fetchBiens() {
        const city = document.getElementById('city').value;
        const type_bien = document.getElementById('type_bien').value;
        const prix_min = document.getElementById('prix_min').value;
        const prix_max = document.getElementById('prix_max').value;

        const params = new URLSearchParams({ city, type_bien, prix_min, prix_max });

        const response = await fetch(`accueil/get_biens.php?${params}`);
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
                        <img src="${photo}" alt="${bien.title}" class="d-block w-100" style="border-radius: 8px;">
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
                    <?php
                        if (!isUserLoggedIn()) {
                            // Si l'utilisateur n'est pas connecté, afficher un message et empêcher la redirection
                            echo 'alert("Connectez-vous avant d\'accéder aux détails du bien ou inscrivez-vous.");';
                        } else {
                            // Si l'utilisateur est connecté, rediriger vers la page des détails du bien
                            echo 'window.location.href = `bien/details_bien.php?id=${bien.id_bien}`;';
                        }
                    ?>
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
