<?php
session_start();
include('../include/db.php'); // Inclusion du fichier de configuration PDO
include('../include/header.php');

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: ../../login/index.php');
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

try {
    // Vérifier si l'utilisateur a déjà des abonnements actifs
    $sql = "SELECT id_abonnement FROM abonnement_commande WHERE id_utilisateur = ? AND date_fin > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_utilisateur]);
    $user_abonnements = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $user_abonnements[] = $row['id_abonnement'];
    }

    // Récupérer tous les abonnements disponibles
    $sql = "SELECT * FROM abonnement";
    $stmt = $pdo->query($sql);
    $abonnements = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}

?>

<main>
    <div class="container">
        <h2>Choisissez votre abonnement</h2>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php
                if ($_GET['error'] == 'already_subscribed') {
                    echo "Vous avez déjà un abonnement actif.";
                } elseif ($_GET['error'] == 'subscription_failed') {
                    echo "Erreur lors de la souscription à l'abonnement.";
                }
                ?>
            </div>
        <?php elseif (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                Abonnement souscrit avec succès !
            </div>
        <?php endif; ?>
        <div class="row">
            <?php foreach ($abonnements as $abonnement): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($abonnement['type']); ?></h5>
                            <p class="card-text"><strong>Prix:</strong> <?php echo htmlspecialchars($abonnement['montant']); ?> €</p>
                            <p class="card-text"><?php echo htmlspecialchars($abonnement['description']); ?></p>
                            <form action="souscrire_abonnement.php" method="post" style="display:inline;">
                                <input type="hidden" name="id_abonnement" value="<?php echo htmlspecialchars($abonnement['id_abonnement']); ?>">
                                <button type="submit" class="btn btn-primary" <?php echo in_array($abonnement['id_abonnement'], $user_abonnements) ? 'disabled' : ''; ?>>
                                    <?php echo in_array($abonnement['id_abonnement'], $user_abonnements) ? 'Souscrit' : 'Souscrire'; ?>
                                </button>
                            </form>
                            <?php if (in_array($abonnement['id_abonnement'], $user_abonnements)): ?>
                                <form action="mettre_fin_abonnement.php" method="post" style="display:inline;">
                                    <input type="hidden" name="id_utilisateur" value="<?php echo $id_utilisateur; ?>">
                                    <input type="hidden" name="id_abonnement" value="<?php echo htmlspecialchars($abonnement['id_abonnement']); ?>">
                                    <button type="submit" class="btn btn-danger">Mettre fin</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</main>
</body>
</html>
