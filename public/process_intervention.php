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

    $urgentInterventionsCount = $page->countUrgentInterventionsWaiting($intervenantEmail);
    if ($urgentInterventionsCount >= 2) {
    // L'intervenant a déjà 2 interventions urgentes en attente
    $_SESSION['error'] = "Cet intervenant a déjà 2 interventions urgentes en attente.";
    header('Location: mes_demandes.php');
    exit();
}


    // Vérifier si une intervention avec le même titre existe déjà pour ce client
    $clientID = $page->getClientIDByEmail($clientEmail);

// Vérifier si une intervention avec le même titre et le même ClientID existe déjà
// Vérifier si une intervention non clôturée avec le même titre et le même ClientID existe déjà
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

    $success = $page->addIntervention($clientEmail, $intervenantEmail, $dateIntervention, $titreIntervention, $description, $urgenceLevel, $statusId);

    if ($success) {
        $_SESSION['message'] = "L'intervention a été ajoutée avec succès.";
    } else {
        $_SESSION['error'] = "Une erreur est survenue lors de l'ajout de l'intervention.";
    }

    header('Location: ../index.php');
    exit();
}
?>
