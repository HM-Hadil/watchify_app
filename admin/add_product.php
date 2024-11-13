<?php
// Include the database connection configuration
include 'config_connexion.php';

// Initialize variables for error and success messages
$error = '';
$success = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize the form inputs
    $nameProduct = htmlspecialchars(trim($_POST['nameProduct']));
    $description = htmlspecialchars(trim($_POST['description']));
    $price = htmlspecialchars(trim($_POST['price']));
    $stock = htmlspecialchars(trim($_POST['stock']));
    $gender = htmlspecialchars(trim($_POST['gender']));
    $color = htmlspecialchars(trim($_POST['color']));
    $productImage = $_FILES['productImage']; // Image upload field

    // Validate the form inputs
    if (empty($nameProduct) || empty($description) || empty($price) || empty($stock) || empty($gender) || empty($color)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!is_numeric($price) || !is_numeric($stock)) {
        $error = "Le prix et la quantité doivent être des valeurs numériques.";
    } else {
        // Handle image upload
        $imageName = basename($productImage["name"]);
        $imageTmpName = $productImage["tmp_name"];
        $imageSize = $productImage["size"];
        $imageError = $productImage["error"];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        // Allowed image extensions
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        $uploadDir = "uploads/";

        if ($imageError === 0) {
            if (in_array($imageExt, $allowedExts)) {
                if ($imageSize < 5000000) { // Limit file size to 5MB
                    $newImageName = uniqid('', true) . "." . $imageExt; // Generate a unique name for the image
                    $imageDestination = $uploadDir . $newImageName;

                    // Move the uploaded image to the designated folder
                    if (move_uploaded_file($imageTmpName, $imageDestination)) {
                        // Insert the product data into the database
                        $stmt = $conn->prepare("INSERT INTO product (nameProduct, description, price, stock, gender, color, productImage) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssdisss", $nameProduct, $description, $price, $stock, $gender, $color, $newImageName);

                        if ($stmt->execute()) {
                            $success = "Produit ajouté avec succès !";
                        } else {
                            $error = "Erreur lors de l'ajout du produit. Veuillez réessayer.";
                        }
                        $stmt->close();
                    } else {
                        $error = "Une erreur est survenue lors de l'upload de l'image.";
                    }
                } else {
                    $error = "Le fichier image est trop volumineux. Veuillez choisir un fichier de moins de 5 Mo.";
                }
            } else {
                $error = "Le format d'image n'est pas valide. Seuls les formats JPG, JPEG, PNG et GIF sont autorisés.";
            }
        } else {
            $error = "Erreur lors de l'upload de l'image.";
        }
    }

    // Close the database connection
    $conn->close();
}
?>
