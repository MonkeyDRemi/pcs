document.addEventListener('DOMContentLoaded', function() {
    fetch('verify_session.php')
        .then(response => response.json())
        .then(data => {
            if (!data.session_valid) {
                window.location.href = '../accueil/index.php';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
});
