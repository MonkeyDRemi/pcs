<?php include ('../include/header.php'); ?>

<main>
    <div class="custom-container">
        <form action="add_bien_verif.php" method="post" enctype="multipart/form-data" id="add-bien-form">
            <h2 class="form-title">Ajouter un bien</h2>

            <div class="custom-form-group">
                <input type="text" name="title" class="form-input" placeholder="Titre" required>
            </div>
            <div class="custom-form-group">
                <textarea name="description" class="form-input" placeholder="Description" required></textarea>
            </div>
            <div class="custom-form-group">
                <input type="text" name="address" class="form-input" placeholder="Adresse" required>
            </div>
            <div class="custom-form-group">
                <input type="text" name="city" class="form-input" placeholder="Ville" required>
            </div>
            <div class="custom-form-group">
                <input type="text" name="code_postal" class="form-input" placeholder="Code Postal" required>
            </div>
            <div class="custom-form-group">
                <input type="text" name="type_bien" class="form-input" placeholder="Type de bien" required>
            </div>
            <div class="custom-form-group">
                <input type="number" name="prix" class="form-input" placeholder="Prix" step="0.01" required>
            </div>
            <div class="custom-form-group">
                <input type="text" name="duree_location" class="form-input" placeholder="Durée de location" required>
            </div>
            <div class="custom-form-group">
                <input type="number" name="salon" class="form-input" placeholder="Nombre de salons" required>
            </div>
            <div class="custom-form-group">
                <input type="number" name="cuisine" class="form-input" placeholder="Nombre de cuisines" required>
            </div>
            <div class="custom-form-group">
                <input type="number" name="salle_de_bain" class="form-input" placeholder="Nombre de salles de bain" required>
            </div>
            <div class="custom-form-group">
                <input type="number" name="toilette" class="form-input" placeholder="Nombre de toilettes" required>
            </div>
            <div class="custom-form-group">
                <input type="number" name="chambre" class="form-input" placeholder="Nombre de chambres" required>
            </div>
            <div class="custom-form-group">
                <input type="number" name="nbr_personne_max" class="form-input" placeholder="Nombre de personnes max" required>
            </div>
            <div class="custom-form-group">
                <input type="number" name="superficie" class="form-input" placeholder="Superficie (m²)" step="0.01" required>
            </div>
            <div class="custom-form-group">
                <label for="meuble" class="form-label">Meublé</label>
                <input type="checkbox" name="meuble" id="meuble" class="form-checkbox">
            </div>
            <div class="custom-form-group">
                <label for="photos" class="form-label">Photos</label><br><br>
                <input type="file" name="photos[]" id="photos" class="form-input-file" multiple required>
                <div id="photo-preview" class="photo-preview-container"></div>
            </div>
            <input type="submit" class="form-submit" value="Ajouter le bien">
        </form>
    </div>
</main>

<script src="add_bien.js"></script>
</body>
</html>
