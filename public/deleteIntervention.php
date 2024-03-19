<?php

require_once '../vendor/autoload.php';
session_start();

use App\Page;

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['Role'], ['Admin', 'Standardiste'])) {
    header('Location: login.php');
    exit;
}

$page = new Page();

if (isset($_POST['interventionId'])) {
    $interventionId = $_POST['interventionId'];
    $result = $page->deleteIntervention($interventionId);

    if ($result) {
        $_SESSION['message'] = 'Intervention supprimée avec succès.';
    } else {
        $_SESSION['error'] = 'Erreur lors de la suppression de l\'intervention.';
    }
}

header('Location: mes_demandes.php'); // Remplacer 'retour.php' par le nom de votre page de retour.
exit;
?>
