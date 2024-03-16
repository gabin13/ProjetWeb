<?php
require_once '../vendor/autoload.php';
session_start();

use App\Page;

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$page = new Page();
$interventions = [];

// Vérifiez si l'utilisateur est un client
if ($_SESSION['user']['Role'] == 'Client') {
    $clientEmail = $_SESSION['user']['Email'];
    $interventions = $page->getInterventionsByClientEmail($clientEmail);
}

// Préparez les données pour le template
$templateData = [
    'session' => $_SESSION,
    'interventions' => $interventions
];

// Affichez la page avec Twig
echo $page->render('mes_demandes.html', $templateData);
