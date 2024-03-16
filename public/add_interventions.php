<?php
require_once '../vendor/autoload.php'; // Autoload our classes with Composer
use App\Page;

$page = new Page();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté et a le rôle "Standardiste"
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] !== 'Standardiste') {
    header("Location: login.php"); // Redirection vers la page de connexion si non autorisé
    exit;
}

// Charger les emails des intervenants
$intervenants = $page->getIntervenants(); // Supposons que cette méthode renvoie tous les intervenants

echo $page->render('add_intervention.html', ['intervenants' => $intervenants]);
