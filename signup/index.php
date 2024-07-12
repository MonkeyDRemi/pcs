<?php include ('../include/header_accueil.php'); ?>

<main>
    <div class="container">
        <form id="signupForm" method="post" onsubmit="return validateForm()">
            <h2>Inscription</h2>
            
            <div class="form-group">
                <label for="genre">Genre</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="genre" value="homme" required> Homme
                    </label>
                    <label>
                        <input type="radio" name="genre" value="femme" required> Femme
                    </label>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group2">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" required>
                </div>

                <div class="form-group2">
                    <label for="prenom">Prénom</label>
                    <input type="text" name="prenom" id="prenom" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="form-group">
                <label for="confirm_email">Confirmer Email</label>
                <input type="email" name="confirm_email" id="confirm_email" required>
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" name="mot_de_passe" id="mot_de_passe" required>
            </div>

            <div class="form-group">
                <label for="confirm_mot_de_passe">Confirmer Mot de passe</label>
                <input type="password" name="confirm_mot_de_passe" id="confirm_mot_de_passe" required>
            </div>

            <div class="form-group">
                <label for="date_naissance">Date de naissance</label>
                <input type="date" name="date_naissance" id="date_naissance" required>
            </div>

            <div class="form-group">
                <label for="numero_telephone">Numéro de téléphone</label>
                <input type="tel" name="numero_telephone" id="numero_telephone" required>
            </div>

            <div class="form-group checkbox-group">
                <label for="newsletter">
                    <input type="checkbox" name="newsletter" id="newsletter"> Souscrire à la newsletter
                </label>
            </div>

            <div class="form-group checkbox-group">
                <label for="prestataire">
                    <input type="checkbox" name="prestataire" id="prestataire"> Je suis prestataire
                </label>
            </div>

            <div class="form-group checkbox-group">
                <label for="bailleur">
                    <input type="checkbox" name="bailleur" id="bailleur"> Je suis bailleur
                </label>
            </div>

            <div class="form-group checkbox-group">
                <label for="conditions">
                    <input type="checkbox" name="conditions" id="conditions" required> J'accepte les conditions de PCS
                </label>
            </div>

            <input type="submit" value="S'inscrire">
        </form>
    </div>
</main>

<script src="signup.js"></script>
</body>
</html>
