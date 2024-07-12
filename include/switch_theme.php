<?php
session_start(); // Démarrage de la session si ce n'est pas déjà fait

if (isset($_GET['theme'])) {
    $theme = $_GET['theme'];

    // Validation du thème pour éviter les attaques par injection
    $allowedThemes = ['light', 'dark']; // Liste des thèmes autorisés

    if (in_array($theme, $allowedThemes)) {
        $_SESSION['theme'] = $theme; // Enregistrement du thème dans la session
    }
}

// Redirection vers la page précédente ou l'accueil par défaut
$redirect = $_SERVER['HTTP_REFERER'] ?? '../accueil/index.php';
header("Location: $redirect");
exit();
?>
