<?php
// Inclure la configuration de la connexion à la base de données
include 'config_connexion.php';

// Initialiser les variables pour stocker les données du formulaire et les messages d'erreur
$error = '';
$success = '';

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et assainir les données du formulaire
    $fullName = htmlspecialchars(trim($_POST['fullName']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirmPassword = htmlspecialchars(trim($_POST['confirmPassword']));

    // Validation simple
    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse e-mail est invalide.";
    } elseif ($password !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'email est déjà enregistré
        $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Cette adresse e-mail est déjà utilisée.";
        } else {
            // Insérer les données de l'utilisateur dans la base de données
            $stmt = $conn->prepare("INSERT INTO user (fullName, email, password) VALUES (?, ?, ?)");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hacher le mot de passe
            $stmt->bind_param("sss", $fullName, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            } else {
                $error = "Erreur lors de l'inscription. Veuillez réessayer.";
            }
        }
        $stmt->close();
    }
}

// Fermer la connexion à la base de données
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Add Bootstrap or any other stylesheets if needed -->
</head>
<body>
<!-- Section Inscription -->
<section class="container">
    <h2 class="text-center mb-4">Créer un compte</h2>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- Afficher le message d'erreur -->
            <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <!-- Afficher le message de succès -->
            <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form action="inscription.php" method="post">
                <!-- Nom Complet -->
                <div class="form-group mb-3">
                    <label for="fullName">Nom Complet</label>
                    <input type="text" name="fullName" class="form-control" id="fullName" placeholder="Entrez votre nom complet" value="<?= isset($fullName) ? $fullName : '' ?>" required>
                </div>
                
                <!-- Adresse Email -->
                <div class="form-group mb-3">
                    <label for="email">Adresse Email</label>
                    <input type="email" name="email" class="form-control" id="email" placeholder="Entrez votre adresse email" value="<?= isset($email) ? $email : '' ?>" required>
                </div>
                
                <!-- Mot de Passe -->
                <div class="form-group mb-3">
                    <label for="password">Mot de Passe</label>
                    <input type="password" name="password" class="form-control" id="password" placeholder="Créez un mot de passe" required>
                </div>
                
                <!-- Confirmation de Mot de Passe -->
                <div class="form-group mb-3">
                    <label for="confirmPassword">Confirmez le Mot de Passe</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirmez votre mot de passe" required>
                </div>
                
                <!-- Bouton d'inscription -->
                <button type="submit" class="btn btn-primary btn-block mt-4">S'inscrire</button>
            </form>

            <!-- Lien de Connexion -->
            <div class="text-center mt-3">
                <p>Déjà un compte ? <a href="connexion.php">Connectez-vous ici</a></p>
            </div>
        </div>
    </div>
</section>


</body>
</html>
