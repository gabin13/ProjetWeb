<?php

require_once '../vendor/autoload.php';
session_start();

use App\Page;

// Vérifie si l'utilisateur est autorisé
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Vérifiez que toutes les données nécessaires sont présentes
if (!isset($_POST['interventionId'], $_POST['description'], $_POST['date'])) {
    $_SESSION['error'] = "Tous les champs nécessaires ne sont pas remplis.";
    header('Location: mes_demandes.php'); // Assurez-vous que c'est le bon chemin de retour
    exit;
}

$page = new Page();

$dateIntervention = new DateTime($_POST['date']);
$today = new DateTime(); // Date d'aujourd'hui

// Vérifie si la date d'intervention est antérieure à aujourd'hui
if ($dateIntervention < $today) {
    $_SESSION['error'] = "La date d'intervention ne peut pas être antérieure à aujourd'hui.";
    header('Location: mes_demandes.php'); // Redirection vers la page de formulaire
    exit();
}

// Calcul de l'urgence en fonction de la nouvelle date
$interval = $today->diff($dateIntervention);
$daysDifference = (int)$interval->format('%a');

if ($daysDifference <= 3) {
    $urgenceLevel = 3; // Urgence niveau 3 pour les interventions dans moins de 3 jours
} elseif ($daysDifference <= 7) {
    $urgenceLevel = 2; // Urgence niveau 2 pour les interventions dans moins d'une semaine
} else {
    $urgenceLevel = 1; // Urgence niveau 1 pour les interventions dans plus de deux semaines
}

// Mise à jour de l'intervention
$result = $page->updateIntervention(
    $_POST['interventionId'],
    $_POST['description'],
    $_POST['date'],
    $urgenceLevel
);

if ($result) {
    $_SESSION['message'] = "L'intervention a été mise à jour avec succès.";
} else {
    $_SESSION['error'] = "Erreur lors de la mise à jour de l'intervention.";
}

header('Location: mes_demandes.php'); // Assurez-vous que c'est le bon chemin
exit;
?>
