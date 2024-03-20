<?php
require_once '../vendor/autoload.php';
session_start();

use App\Page;

$page = new Page();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $clientEmail = $_POST['clientEmail'];
    $intervenantEmail = $_POST['intervenantEmail'];
    $dateIntervention = $_POST['dateIntervention'];
    $titreIntervention = $_POST['titreIntervention'];
    $description = $_POST['description'];

    $dateInterventionObj = new DateTime($dateIntervention);
    $today = new DateTime();
    
    if ($dateInterventionObj < $today) {
        $_SESSION['error'] = "La date d'intervention ne peut pas être antérieure à aujourd'hui.";
        header('Location: ../index.php');
        exit();
    }


    $urgentInterventionsCount = $page->countUrgentInterventionsWaiting($intervenantEmail);
    if ($urgentInterventionsCount >= 2) {
    $_SESSION['error'] = "Cet intervenant a déjà 2 interventions urgentes en attente.";
    header('Location: index.php');
    exit();
}

    $clientID = $page->getClientIDByEmail($clientEmail);
    

    $clientID = $page->getClientByEmail($clientEmail);
    if (!$clientID) {
        $_SESSION['error'] = "L'email fourni ne correspond à aucun client.";
        header('Location: index.php');
        exit();
    }

$interventionExists = $page->checkInterventionExistsAndNotClosed($clientID, $titreIntervention);

if ($interventionExists) {
    $_SESSION['error'] = "Une intervention non clôturée avec le même titre existe déjà pour ce client.";
    header('Location: ../index.php');
    exit();
}






    // Calculer la différence en jours entre la date d'intervention et la date actuelle
    $dateNow = new DateTime();
    $interventionDate = new DateTime($dateIntervention);
    $interval = $dateNow->diff($interventionDate);
    $daysDifference = (int)$interval->format('%a');

    if ($daysDifference <= 3) {
        $urgenceLevel = 3; // Urgence niveau 3 pour les interventions dans moins de 3 jours
    } elseif ($daysDifference <= 7) {
        $urgenceLevel = 2; // Urgence niveau 2 pour les interventions dans moins d'une semaine
    } elseif ($daysDifference > 14) {
        $urgenceLevel = 1; // Urgence niveau 1 pour les interventions dans plus de deux semaines
    } else {
        $urgenceLevel = 1; // Niveau d'urgence par défaut
    }

    // ID du statut "En attente", à remplacer par la valeur correcte selon votre base de données
    $statusId = 1; // Supposons que 1 soit l'ID pour "En attente"

    // Exemple d'appel de la méthode addIntervention
$success = $page->addIntervention(
    $clientEmail,
    $intervenantEmail,
    $dateIntervention,
    $titreIntervention,
    $description,
    $urgenceLevel,
    $_SESSION['user']['UserID'] // Supposons que l'ID du standardiste est stocké dans la session
);


    if ($success) {
        $_SESSION['message'] = "L'intervention a été ajoutée avec succès.";
    } else {
        $_SESSION['error'] = "Une erreur est survenue lors de l'ajout de l'intervention.";
    }

    header('Location: ../index.php');
    exit();
}
?>
