<?php

require_once '../vendor/autoload.php';
session_start();

use App\Page;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérez les données soumises
    $commentText = $_POST['CommentText'];
    $interventionID = $_POST['InterventionID'];
    $userID = $_SESSION['user']['UserID']; // Assurez-vous que l'ID de l'utilisateur est bien stocké dans la session

    $page = new Page();
    
    // Enregistrer le commentaire
    $result = $page->addComment($commentText, $interventionID, $userID);

    if ($result) {
        $_SESSION['message'] = "Commentaire ajouté avec succès.";
    } else {
        $_SESSION['error'] = "Vous avez déjà commenté cette intervention ou une erreur est survenue.";
    }

    // Rediriger vers la page précédente ou une page de confirmation
    header('Location: mes_demandes.php');
    exit();
}
?>
