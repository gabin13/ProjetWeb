<?php
session_start();
require_once '../vendor/autoload.php';

use App\Page;

$page = new Page();
$intervenantId = $_SESSION['user']['UserID']; // Assurez-vous que cela correspond à la manière dont vous stockez l'ID de l'utilisateur.
$interventionId = $_POST['interventionId'];
$newStatus = $_POST['status']; // Supposons que '2' représente "En cours"

if ($newStatus == 2 && $page->hasOngoingIntervention($intervenantId)) {
    $_SESSION['error'] = "Vous ne pouvez avoir qu'une intervention en cours à la fois.";
    header('Location: mes_demandes.php');
    exit;
} else {
    if ($page->updateInterventionStatus($interventionId, $newStatus)) {
        // Assurez-vous de nettoyer l'erreur précédente s'il y en avait une
        unset($_SESSION['error']);
        header('Location: mes_demandes.php?message=Statut de l\'intervention mis à jour avec succès.');
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour du statut.";
        header('Location: mes_demandes.php');
    }
}
?>
