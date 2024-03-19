<?php
session_start();
require_once '../vendor/autoload.php';
use App\Page;

if(isset($_POST['interventionId'])) {
    $page = new Page();
    $interventionId = $_POST['interventionId'];
    $statusAnnulee = 5; // Assumant que 5 est l'ID pour le statut "Annulée"

    // Utilisation d'une nouvelle méthode ou de la méthode existante avec un statut spécifique pour annuler
    $result = $page->updateInterventionStatus($interventionId, $statusAnnulee);
    
    if($result) {
        echo json_encode(['success' => true, 'message' => 'Intervention annulée avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'annulation de l\'intervention.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Données manquantes pour annuler l\'intervention.']);
}
?>
