document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("addBienForm");
    const photosInput = document.getElementById("photos");
    const maxPhotos = 10;

    form.addEventListener("submit", function(event) {
        event.preventDefault(); // Empêche le formulaire de se soumettre automatiquement

        // Validation du nombre de photos
        if (photosInput.files.length > maxPhotos) {
            alert(`Vous ne pouvez télécharger que ${maxPhotos} photos maximum.`);
            return;
        }

        // Validation des champs obligatoires
        const requiredFields = form.querySelectorAll("input[required], textarea[required], select[required]");
        for (let field of requiredFields) {
            if (field.value.trim() === "") {
                alert(`Le champ ${field.placeholder || field.options[field.selectedIndex].text} est obligatoire.`);
                return;
            }
        }

        // Récupérer les valeurs du formulaire
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        // Envoyer les données à add_bien.php via fetch
        fetch('add_bien.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = '../../login/index.php'; // Rediriger vers la page de login
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue. Veuillez réessayer.');
        });
    });

    // Ajout d'un aperçu des photos téléchargées
    photosInput.addEventListener("change", function() {
        const preview = document.getElementById("photo-preview");
        preview.innerHTML = "";

        for (let file of photosInput.files) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement("img");
                img.src = e.target.result;
                img.classList.add("photo-preview");
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
});
