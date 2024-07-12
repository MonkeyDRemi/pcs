document.addEventListener("DOMContentLoaded", function() {
    const filterForm = document.querySelector('.filters form');
    const filterSelect = document.getElementById('filter');

    filterForm.addEventListener('submit', function(event) {
        // Vous pouvez ajouter ici des actions à effectuer lors de la soumission du formulaire de filtre
    });

    // Optionnel: Ajouter des fonctionnalités supplémentaires
    // Par exemple, vous pouvez ajouter des événements de survol pour les cartes
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseover', function() {
            this.style.transform = 'scale(1.05)';
        });
        card.addEventListener('mouseout', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
