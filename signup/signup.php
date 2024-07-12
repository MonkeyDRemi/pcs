<?php
require_once '../include/db.php';

function signup($data) {
    global $pdo;

    try {
        $genre = $data['genre'];
        $nom = $data['nom'];
        $prenom = $data['prenom'];
        $email = $data['email'];
        $mot_de_passe = $data['mot_de_passe'];
        $date_naissance = $data['date_naissance'];
        $numero_telephone = $data['numero_telephone'];
        $indicatif_telephonique = $data['indicatif_telephonique'];
        $newsletter = $data['newsletter'];
        $is_prestataire = $data['prestataire'];
        $is_bailleur = $data['bailleur'];

        // Validation des champs
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["error" => "L'email n'est pas valide."]);
            exit();
        }

        if (!preg_match("/^(?=.*[A-Z])(?=.*[!@#$%^&*])(?=.*\d)[A-Za-z\d!@#$%^&*]{8,}$/", $mot_de_passe)) {
            echo json_encode(["error" => "Le mot de passe doit contenir au moins 8 caractères, une majuscule, un caractère spécial et un chiffre."]);
            exit();
        }

        if (!preg_match("/^[A-Za-zÀ-ÿ]+$/", $nom) || !preg_match("/^[A-Za-zÀ-ÿ]+$/", $prenom)) {
            echo json_encode(["error" => "Le nom et le prénom doivent contenir uniquement des lettres."]);
            exit();
        }

        // Vérification si l'email existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(["error" => "Email déjà existant."]);
            exit();
        }

        // Hashage du mot de passe
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

        // Date d'inscription
        $date_inscription = date('Y-m-d H:i:s');

        // Détermination des dates pour prestataire, bailleur et voyageur
        $prestataire_date = $is_prestataire ? $date_inscription : null;
        $bailleur_date = $is_bailleur ? $date_inscription : null;
        $voyageur_date = $date_inscription;

        // Préparation de la requête SQL avec PDO
        $stmt = $pdo->prepare("INSERT INTO utilisateur (genre, nom, prenom, email, mot_de_passe, date_inscription, date_naissance, numero_telephone, indicatif_telephonique, newsletter, prestataire, bailleur, voyageur)
                                VALUES (:genre, :nom, :prenom, :email, :mot_de_passe, :date_inscription, :date_naissance, :numero_telephone, :indicatif_telephonique, :newsletter, :prestataire_date, :bailleur_date, :voyageur_date)");

        // Liaison des paramètres
        $stmt->bindParam(':genre', $genre);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mot_de_passe', $mot_de_passe_hash); 
        $stmt->bindParam(':date_inscription', $date_inscription);
        $stmt->bindParam(':date_naissance', $date_naissance);
        $stmt->bindParam(':numero_telephone', $numero_telephone, PDO::PARAM_STR);
        $stmt->bindParam(':indicatif_telephonique', $indicatif_telephonique, PDO::PARAM_STR);
        $stmt->bindParam(':newsletter', $newsletter, PDO::PARAM_INT);
        $stmt->bindParam(':prestataire_date', $prestataire_date, PDO::PARAM_STR);
        $stmt->bindParam(':bailleur_date', $bailleur_date, PDO::PARAM_STR);
        $stmt->bindParam(':voyageur_date', $voyageur_date, PDO::PARAM_STR);

        // Exécution de la requête
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["error" => "Erreur lors de l'inscription."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erreur de base de données : " . $e->getMessage()]);
    }
}

// Traitement de la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données envoyées en JSON
    $data = json_decode(file_get_contents('php://input'), true);
    signup($data);
} 
?>
