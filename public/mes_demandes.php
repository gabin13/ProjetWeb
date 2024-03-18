<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once '../vendor/autoload.php';
session_start();



use App\Page;

 // Doit afficher des détails sur l'objet PDO si la connexion est réussie


if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit(); 
}

$page = new Page();
$pdo = $page->getPdo();

$page->closePastDueInterventions();

$interventions = [];

// Assuming $_SESSION['user']['Id'] holds the intervenant's ID
if ($_SESSION['user']['Role'] == 'Client') {
    $clientEmail = $_SESSION['user']['Email'];
    $interventions = $page->getInterventionsByClientEmail($clientEmail);
} if ($_SESSION['user']['Role'] == 'Intervenant' && isset($_SESSION['user']['UserID'])) {
    $intervenantId = $_SESSION['user']['UserID']; // Assurez-vous que c'est la bonne clé
    
$interventions = $page->getInterventionsByIntervenant($intervenantId);

}

// Prepare data for the template
$templateData = [
    'session' => $_SESSION,
    'interventions' => $interventions
];



// Display the page with Twig
echo $page->render('mes_demandes.html', $templateData);
 