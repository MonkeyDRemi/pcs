<?php include ('../include/header_accueil.php'); ?>
<main>
    <div class="container">
        <form id="loginForm" method="post" onsubmit="return validateLoginForm()">
            <h2>Connexion</h2>
            <div id="error-message"></div>
            <div class="form-group">
                <label for="type_utilisateur"></label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="type_utilisateur" value="voyageur" required> Voyageur
                    </label>
                    <label>
                        <input type="radio" name="type_utilisateur" value="bailleur" required> Bailleur
                    </label>
                    <label>
                        <input type="radio" name="type_utilisateur" value="prestataire" required> Prestataire
                    </label>
                </div>
            </div>
            <div class="form-group">
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>

            <div class="form-group">
                <input type="password" name="mot_de_passe" id="mot_de_passe" placeholder="Mot de passe" required>
            </div>

            <input type="submit" value="Se connecter">
        </form>
    </div>
</main>
<script src="login.js"></script>
</body>
</html>
