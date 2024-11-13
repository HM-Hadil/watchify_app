<?php
$servername = "localhost:3305";
$username = "root";
$password = "1234"; 
$database = "watchify_bd";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
?>
