document.addEventListener("DOMContentLoaded", function () {
    var input = document.querySelector("#numero_telephone");
    var phoneInput = window.intlTelInput(input, {
        initialCountry: "fr",
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
    });

    window.validateForm = function () {
        var genre = document.querySelector('input[name="genre"]:checked').value;
        var nom = document.getElementById("nom").value;
        var prenom = document.getElementById("prenom").value;
        var email = document.getElementById("email").value;
        var confirmEmail = document.getElementById("confirm_email").value;
        var password = document.getElementById("mot_de_passe").value;
        var confirmPassword = document.getElementById("confirm_mot_de_passe").value;
        var dateNaissance = document.getElementById("date_naissance").value;
        var numeroTelephone = document.getElementById("numero_telephone").value;
        var newsletter = document.getElementById("newsletter").checked;
        var prestataire = document.getElementById("prestataire").checked;
        var bailleur = document.getElementById("bailleur").checked;
        var conditions = document.getElementById("conditions").checked;

        if (email !== confirmEmail) {
            alert("Les emails ne correspondent pas.");
            return false;
        }


        if (password !== confirmPassword) {
            alert("Les mots de passe ne correspondent pas.");
            return false;
        }

        var passwordPattern = /^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.*\d)[A-Za-z\d!@#$%^&*]{8,}$/;
        if (!passwordPattern.test(password)) {
            alert("Le mot de passe doit contenir au moins 8 caractères, une majuscule, un caractère spécial et un chiffre.");
            return false;
        }

        var namePattern = /^[A-Za-zÀ-ÿ]+$/;
        if (!namePattern.test(nom) || !namePattern.test(prenom)) {
            alert("Le nom et le prénom doivent contenir uniquement des lettres.");
            return false;
        }


        var today = new Date();
        var birthDate = new Date(dateNaissance);
        var age = today.getFullYear() - birthDate.getFullYear();
        var monthDifference = today.getMonth() - birthDate.getMonth();
        if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        if (age < 18) {
            alert("Vous devez avoir au moins 18 ans pour vous inscrire.");
            return false;
        }
        if (!phoneInput.isValidNumber()) {
            alert("Le numéro de téléphone est incorrect.");
            return false;
        }

        if (prestataire && bailleur) {
            alert("Veuillez sélectionner soit 'Je suis prestataire' soit 'Je suis bailleur', mais pas les deux.");
            return false;
        }


        if (!conditions) {
            alert("Vous devez accepter les conditions de PCS.");
            return false;
        }

    
        var formData = {
            genre: genre,
            nom: nom,
            prenom: prenom,
            email: email,
            mot_de_passe: password,
            date_naissance: dateNaissance,
            numero_telephone: numeroTelephone,
            indicatif_telephonique: phoneInput.getSelectedCountryData().dialCode,
            newsletter: newsletter ? 1 : 0,
            prestataire: prestataire ? 1 : 0,
            bailleur: bailleur ? 1 : 0
        };

        fetch('http://localhost:8000/signup', { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest' 
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
            
                window.location.href = '../login/index.php';
            } else {
                alert(data.error); 
            }
        })
        .catch(error => {
            console.error('Erreur lors de la soumission du formulaire :', error);
        });

        return false; 
    }
});
