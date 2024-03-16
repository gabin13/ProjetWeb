<?php

require_once '../vendor/autoload.php'; // Charge toutes les classes avec Composer

// Démarrer la session PHP pour gérer les connexions
session_start();


use App\Page;

$page = new Page();

// Vérifier si l'utilisateur est connecté et s'il est administrateur
if (isset($_SESSION['user']) && $_SESSION['user']['Role'] == 'Admin') {
    // Si l'utilisateur est un administrateur, récupérer la liste des utilisateurs pour l'affichage
    $users = $page->getAllUsers(); // Assurez-vous que la méthode getAllUsers() est bien définie et retourne les données attendues
    // Afficher la page d'accueil pour les administrateurs avec les données des utilisateurs
    echo $page->render('index.html', ['session' => $_SESSION, 'users' => $users]);
} else {
    // Afficher la page d'accueil normale pour les utilisateurs non administrateurs
    echo $page->render('index.html', ['session' => $_SESSION]);
}
