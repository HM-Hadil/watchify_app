<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection configuration
include 'config_connexion.php';

// Initialize variables for error messages
$error = '';
$success = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize the form inputs
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Validate the form inputs
    if (empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse e-mail est invalide.";
    } else {
        // Check if the email exists in the database
        $stmt = $conn->prepare("SELECT id, password FROM user WHERE email = ?");
        if (!$stmt) {
            die('Query preparation failed: ' . $conn->error);  // Debugging line
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // If the email exists
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $hashed_password)) {
                // Successful login
                session_start(); // Start a session
                $_SESSION['user_id'] = $id; // Store the user ID in the session
                $_SESSION['user_email'] = $email; // Store the email in the session
                header("Location: userProfile.html"); // Redirect to home page
                exit;
            } else {
                $error = "Mot de passe incorrect.";
            }
        } else {
            $error = "Aucun utilisateur trouvé avec cet email.";
        }

        $stmt->close();
    }

    // Close the database connection
    $conn->close();
}
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
    <!-- Connexion Section -->
    <section class="container my-5">
        <h2 class="text-center mb-4">Se connecter</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <!-- Show error messages -->
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="connexion.php" method="POST">
                    <!-- Email Address -->
                    <div class="form-group mb-3">
                        <label for="email">Adresse Email</label>
                        <input type="email" name="email" class="form-control" id="email" placeholder="Entrez votre adresse email" required>
                    </div>
                    
                    <!-- Password -->
                    <div class="form-group mb-3">
                        <label for="password">Mot de Passe</label>
                        <input type="password" name="password" class="form-control" id="password" placeholder="Entrez votre mot de passe" required>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-block mt-4">Se connecter</button>
                </form>

                <!-- Registration Link -->
                <div class="text-center mt-3">
                    <p>Nouveau chez Watchify ? <a href="inscription.php">Créez un compte ici</a></p>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
