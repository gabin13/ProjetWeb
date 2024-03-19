<?php

require_once '../vendor/autoload.php'; // Charge toutes les classes avec Composer
session_start();

use App\Page;

$page = new Page();

// Préparation des données pour le template
$templateData = ['session' => $_SESSION];

if (isset($_SESSION['message'])) {
    $templateData['message'] = $_SESSION['message'];
    unset($_SESSION['message']); // Efface le message après affichage
}

if (isset($_SESSION['error'])) {
    $templateData['error'] = $_SESSION['error'];
    unset($_SESSION['error']); // Efface le message après affichage
}

if (isset($_SESSION['user'])) {
        // Utilisateur admin
        $users = $page->getAllUsers();
        $templateData['users'] = $users;

}

// Affichage du template
// Assurez-vous que 'index.html' est le bon nom de votre fichier de template Twig et qu'il se trouve dans le bon dossier.
echo $page->render('clients.html', $templateData);

