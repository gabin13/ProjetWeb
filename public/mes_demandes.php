<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';
session_start();

use App\Page;

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit(); 
}

$page = new Page();
$pdo = $page->getPdo();

$page->closePastDueInterventions();

$interventions = [];

// Assuming $_SESSION['user']['UserID'] holds the user's ID
// Assuming $_SESSION['user']['UserID'] holds the user's ID
if ($_SESSION['user']['Role'] == 'Admin') {
    $interventions = $page->getAllInterventions();
} elseif ($_SESSION['user']['Role'] == 'Client') {
    $clientEmail = $_SESSION['user']['Email'];
    $interventions = $page->getInterventionsByClientEmail($clientEmail);
} elseif ($_SESSION['user']['Role'] == 'Intervenant' && isset($_SESSION['user']['UserID'])) {
    $intervenantId = $_SESSION['user']['UserID'];
    $interventions = $page->getInterventionsByIntervenant($intervenantId);
} elseif ($_SESSION['user']['Role'] == 'Standardiste' && isset($_SESSION['user']['UserID'])) {
    $standardisteId = $_SESSION['user']['UserID'];
    $interventions = $page->getInterventionsByStandardiste($standardisteId);
}

// Logic to include comments for each intervention (if needed)
foreach ($interventions as $key => $intervention) {
    $comments = $page->getCommentsByInterventionId($intervention['InterventionID']);
    $interventions[$key]['comments'] = $comments;
}

// Prepare data for the template
$templateData = [
    'session' => $_SESSION,
    'interventions' => $interventions
];

// Display the page with Twig
echo $page->render('mes_demandes.html', $templateData);

