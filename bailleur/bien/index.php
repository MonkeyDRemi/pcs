<?php
session_start();
include('../include/header.php');

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../../login/index.php');
    exit();
}

$id_bien = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id_bien) {
    echo "ID du bien non spécifié.";
    exit();
}
?>
<style>
    .btn-delete-photo {
        padding: 0.2rem 0.4rem;
        font-size: 0.8rem;
        width: 20px;
        height: 25px;
        top: 0;
        right: 0;
    }

    .photo-item {
        display: flex;
        flex-direction: row;
        justify-content: space-around;
        align-items: center;
        width: auto;
        height: auto;
        margin-left: 8%;
    }
</style>


<main style="margin-left: 20%; margin-right: 20%;">
    <div id="biens-container" class="my-4">
        <div id="biens-list" class="list-group">
            <!-- Liste des biens -->
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bienId = <?php echo $id_bien; ?>;
    fetch(`mes_biens_verif.php?id=${bienId}`)
        .then(response => response.json())
        .then(data => {
            const biensList = document.getElementById('biens-list');
            if (data.success && data.biens.length > 0) {
                const bien = data.biens[0];
                // Assurez-vous que la propriété `photos` est définie comme un tableau
                if (!bien.photos) {
                    bien.photos = [];
                }

                const bienElement = document.createElement('div');
                bienElement.classList.add('bien-item', 'list-group-item', 'd-flex', 'flex-column', 'mb-3', 'p-3', 'border');
                bienElement.setAttribute('data-id', bien.id_bien);
                bienElement.innerHTML = `
                    <form class="update-bien-form" data-id="${bien.id_bien}">
                        <h3 class="text-center mb-3">${bien.title}</h3>
                        <div class="form-group">
                            <label for="title">Titre:</label>
                            <input type="text" class="form-control" name="title" value="${bien.title}" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea class="form-control" name="description" required>${bien.description}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="address">Adresse:</label>
                            <input type="text" class="form-control" name="address" value="${bien.address}" required>
                        </div>
                        <div class="form-group">
                            <label for="city">Ville:</label>
                            <input type="text" class="form-control" name="city" value="${bien.city}" required>
                        </div>
                        <div class="form-group">
                            <label for="code_postal">Code Postal:</label>
                            <input type="text" class="form-control" name="code_postal" value="${bien.code_postal}" required>
                        </div>
                        <div class="form-group">
                            <label for="type_bien">Type de Bien:</label>
                            <input type="text" class="form-control" name="type_bien" value="${bien.type_bien}" required>
                        </div>
                        <div class="form-group">
                            <label for="prix">Prix:</label>
                            <input type="number" class="form-control" name="prix" value="${bien.prix}" required>
                        </div>
                        <div class="form-group">
                            <label for="duree_location">Durée de Location (en mois):</label>
                            <input type="number" class="form-control" name="duree_location" value="${bien.duree_location}" required>
                        </div>
                        <div class="form-group">
                            <label for="salon">Salon:</label>
                            <input type="number" class="form-control" name="salon" value="${bien.salon}" required>
                        </div>
                        <div class="form-group">
                            <label for="cuisine">Cuisine:</label>
                            <input type="number" class="form-control" name="cuisine" value="${bien.cuisine}" required>
                        </div>
                        <div class="form-group">
                            <label for="salle_de_bain">Salle de Bain:</label>
                            <input type="number" class="form-control" name="salle_de_bain" value="${bien.salle_de_bain}" required>
                        </div>
                        <div class="form-group">
                            <label for="toilette">Toilette:</label>
                            <input type="number" class="form-control" name="toilette" value="${bien.toilette}" required>
                        </div>
                        <div class="form-group">
                            <label for="chambre">Chambre:</label>
                            <input type="number" class="form-control" name="chambre" value="${bien.chambre}" required>
                        </div>
                        <div class="form-group">
                            <label for="nbr_personne_max">Nombre de Personnes Max:</label>
                            <input type="number" class="form-control" name="nbr_personne_max" value="${bien.nbr_personne_max}" required>
                        </div>
                        <div class="form-group">
                            <label for="superficie">Superficie (en m²):</label>
                            <input type="number" class="form-control" name="superficie" value="${bien.superficie}" required>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" name="meuble" ${bien.meuble ? 'checked' : ''}>
                            <label class="form-check-label" for="meuble">Meublé</label>
                        </div>
                        <div class="form-group">
                            <label for="photos">Photos:</label>
                            <input type="file" class="form-control-file" name="photos[]" multiple>
                            <div class="photo-preview-container mt-3">
                                ${bien.photos.map(photo => `
                                    <div class="photo-item" data-photo-id="${photo.id_photo}">
                                        <img src="${photo.url}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                                        <button type="button" class="btn btn-danger btn-sm btn-delete-photo mt-2">X</button>
                                    </div>`).join('')}
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button type="submit" class="btn btn-primary">Modifier le bien</button>
                            <button type="button" class="btn btn-danger btn-delete-bien" data-id="${bien.id_bien}">Supprimer le bien</button>
                            <button type="button" class="btn btn-secondary btn-calendar" data-id="${bien.id_bien}">Calendrier</button>
                        </div>
                    </form>
                `;
                biensList.appendChild(bienElement);

                // Gestion des événements pour les boutons de suppression et de calendrier
                document.querySelectorAll('.btn-delete-bien').forEach(button => {
                    button.addEventListener('click', function() {
                        const bienId = this.getAttribute('data-id');
                        if (confirm('Voulez-vous vraiment supprimer ce bien ?')) {
                            fetch(`delete_bien.php?id=${bienId}`, {
                                method: 'DELETE'
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('Bien supprimé avec succès');
                                    location.reload();
                                } else {
                                    alert('Erreur lors de la suppression du bien');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Une erreur est survenue. Veuillez réessayer plus tard.');
                            });
                        }
                    });
                });

                document.querySelectorAll('.btn-calendar').forEach(button => {
                    button.addEventListener('click', function() {
                        const bienId = this.getAttribute('data-id');
                        window.location.href = `calendrier/calendrier.php?id=${bienId}`;
                    });
                });

                document.querySelectorAll('.update-bien-form').forEach(form => {
                    form.addEventListener('submit', function(event) {
                        event.preventDefault();
                        const bienId = this.getAttribute('data-id');
                        const formData = new FormData(this);
                        fetch(`update_bien.php?id=${bienId}`, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Bien modifié avec succès');
                                location.reload();
                            } else {
                                alert('Erreur lors de la modification du bien');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Une erreur est survenue. Veuillez réessayer plus tard.');
                        });
                    });
                });

                document.querySelectorAll('.btn-delete-photo').forEach(button => {
                    button.addEventListener('click', function() {
                        const photoId = this.parentElement.getAttribute('data-photo-id');
                        fetch(`delete_photos.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ id_photo: photoId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.parentElement.remove();
                            } else {
                                alert('Erreur lors de la suppression de la photo');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Une erreur est survenue. Veuillez réessayer plus tard.');
                        });
                    });
                });
            } else {
                biensList.innerHTML = '<p>Bien non trouvé ou vous n\'avez pas les droits nécessaires pour accéder à ce bien.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const biensList = document.getElementById('biens-list');
            biensList.innerHTML = '<p>Une erreur est survenue. Veuillez réessayer plus tard.</p>';
        });
});
</script>
</body>
</html>
