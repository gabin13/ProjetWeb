<?php
header('Content-Type: application/json');
session_start();
require_once '../vendor/autoload.php';

use App\Page;

// Log des données reçues
error_log("Received: interventionId=" . $_POST['interventionId'] . ", status=" . $_POST['status']);

if(isset($_POST['interventionId'], $_POST['status'])) {
    $page = new Page();
    $interventionId = $_POST['interventionId'];
    $newStatus = $_POST['status']; // '4' représente "Clôturée"
    
    $result = $page->updateInterventionStatus($interventionId, $newStatus);
    error_log("Update result: " . ($result ? "Success" : "Failure")); // Log du résultat

    if($result) {
        echo json_encode(['success' => true, 'message' => 'Intervention clôturée avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la clôture de l\'intervention.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
}
?>
