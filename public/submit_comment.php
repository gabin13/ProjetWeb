<?php
require_once '../vendor/autoload.php'; // Ajustez le chemin selon votre configuration
session_start();

use App\Page;

// Vérifiez que l'utilisateur est connecté et récupérez son ID depuis la session
if (!isset($_SESSION['user'])) {
    die("Vous devez être connecté pour poster un commentaire.");
}
$UserId = $_SESSION['user']['UserID']; // Ajustez selon la structure de votre session

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['CommentText']) && !empty($_POST['InterventionID'])) {
    $CommentText = $_POST['CommentText'];
    $InterventionId = $_POST['InterventionID'];

    $page = new Page(); // Assurez-vous d'avoir une instance de votre classe de gestion de la base de données
    if ($page->addComment($CommentText, $UserId, $InterventionId)) {
        $_SESSION['message'] = "Commentaire ajouté avec succès.";
    } else {
        $_SESSION['error'] = "Une erreur est survenue lors de l'ajout du commentaire.";
    }
} else {
    $_SESSION['error'] = "Informations manquantes.";
}

header("Location: mes_demandes.php"); // Ajustez cette redirection
exit();
?>
