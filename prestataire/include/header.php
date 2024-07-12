<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCS</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container { margin-top: 20px; }
        .nav-tabs { margin-bottom: 20px; }
    </style>
    <script src="verify_session.js"></script>
</head>
<body>
<header>
        <div class="logo">
            <a href="../accueil/index.php"><img src="../img/pcs_logo_web-removebg-preview.png" alt="PCS Logo"></a>
        </div>
        <div class="search-bar">
            <input type="text" placeholder="Recherche...">
        </div>
        <div class="burger-menu" onclick="toggleMenu()">
            ☰
        </div>
    </header>
    <nav id="menu">
        <ul>
            <li><a href="../profile/index.php">Mon Profil</a></li>
            <li><a href="">Changer de Thème</a></li>
            <li><a href="../include/deconnexion.php">Déconnexion</a></li>
        </ul>
    </nav>
    <script>
        function toggleMenu() {
            var menu = document.getElementById('menu');
            menu.classList.toggle('active');
        }
    </script>
    

