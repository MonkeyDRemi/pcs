document.addEventListener('DOMContentLoaded', function() {
    // Vérifier la session au chargement de la page
    fetch('http://localhost:8000/verify_session') // Assurez-vous que l'URL est correcte
        .then(response => response.json())
        .then(data => {
            if (!data.session_valid) {
                window.location.href = '../accueil/index.php'; // Rediriger si la session n'est pas valide
            } else {
                // Effectuer les autres requêtes AJAX ici, après la vérification de la session
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
});
